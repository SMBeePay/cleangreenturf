<?php
/**
 * Turf-installation estimate scheduler: SQLite storage + availability
 * logic, shared by scheduler/*.php (public booking endpoints),
 * admin/appointments.php, and bin/send-reminders.php.
 *
 * SQLite (not MySQL) on purpose: zero setup on Hostinger shared hosting —
 * no separate database credentials to configure before this can go live,
 * same "works out of the box" philosophy as the mail() fallback in
 * config/mail.php. The database file lives in /data, which is blocked
 * from direct web access in .htaccess/dev_router.php and has its own
 * deny-all .htaccess (it contains customer names/phone/email/address).
 */
declare(strict_types=1);

function scheduler_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $pdo = new PDO('sqlite:' . $dir . '/scheduler.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NOT NULL,
        address TEXT NOT NULL,
        notes TEXT,
        slot_start TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'confirmed',
        reschedule_token TEXT NOT NULL UNIQUE,
        reminder_sent_at TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_appointments_slot_start ON appointments(slot_start)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_appointments_token ON appointments(reschedule_token)');
    return $pdo;
}

/** @return string[] slot_start values already booked in [$fromYmdHis, $toYmdHis]. */
function scheduler_booked_slot_starts(string $fromYmdHis, string $toYmdHis, ?string $excludeToken = null): array {
    $pdo = scheduler_db();
    $sql = "SELECT slot_start FROM appointments WHERE status = 'confirmed' AND slot_start >= ? AND slot_start <= ?";
    $params = [$fromYmdHis, $toYmdHis];
    if ($excludeToken !== null) {
        $sql .= ' AND reschedule_token != ?';
        $params[] = $excludeToken;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Open, bookable slots for one calendar date. Re-derives everything from
 * config + the DB every time (business hours, lead time, booking window,
 * blackout dates, already-booked slots) — this is also what validates a
 * submitted booking server-side, so it must never trust client input.
 *
 * @return array<int, array{value:string,label:string}>
 */
function scheduler_slots_for_date(string $dateYmd, array $config, ?string $excludeToken = null): array {
    $tz = new DateTimeZone($config['timezone']);
    $date = DateTime::createFromFormat('Y-m-d', $dateYmd, $tz);
    if (!$date) {
        return [];
    }
    $date->setTime(0, 0, 0);

    $now = new DateTime('now', $tz);
    $today = (clone $now)->setTime(0, 0, 0);
    $maxDate = (clone $today)->modify('+' . (int)$config['booking_window_days'] . ' days');

    if ($date < $today || $date > $maxDate) {
        return [];
    }
    if (in_array($dateYmd, $config['blackout_dates'], true)) {
        return [];
    }

    $weekday = (int)$date->format('w');
    if (!isset($config['hours'][$weekday])) {
        return [];
    }
    [$openStr, $closeStr] = $config['hours'][$weekday];
    $open = DateTime::createFromFormat('Y-m-d H:i', $dateYmd . ' ' . $openStr, $tz);
    $close = DateTime::createFromFormat('Y-m-d H:i', $dateYmd . ' ' . $closeStr, $tz);
    if (!$open || !$close) {
        return [];
    }
    $slotMinutes = (int)$config['slot_minutes'];
    $earliestBookable = (clone $now)->modify('+' . (int)$config['lead_time_hours'] . ' hours');

    $booked = array_flip(scheduler_booked_slot_starts($dateYmd . ' 00:00:00', $dateYmd . ' 23:59:59', $excludeToken));

    $slots = [];
    $cursor = clone $open;
    while (($cursor->getTimestamp() + $slotMinutes * 60) <= $close->getTimestamp()) {
        if ($cursor >= $earliestBookable) {
            $value = $cursor->format('Y-m-d H:i:s');
            if (!isset($booked[$value])) {
                $slots[] = ['value' => $value, 'label' => $cursor->format('g:i A')];
            }
        }
        $cursor->modify('+' . $slotMinutes . ' minutes');
    }
    return $slots;
}

/** @return array<string,int> date (Y-m-d) => number of open slots, for calendar rendering. */
function scheduler_days_with_availability(string $monthYm, array $config): array {
    $tz = new DateTimeZone($config['timezone']);
    $first = DateTime::createFromFormat('Y-m-d', $monthYm . '-01', $tz);
    if (!$first) {
        return [];
    }
    $daysInMonth = (int)$first->format('t');
    $result = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dateYmd = $first->format('Y-m') . '-' . str_pad((string)$d, 2, '0', STR_PAD_LEFT);
        $count = count(scheduler_slots_for_date($dateYmd, $config));
        if ($count > 0) {
            $result[$dateYmd] = $count;
        }
    }
    return $result;
}

/** Server-side re-validation that a submitted slot is real and still open. */
function scheduler_slot_is_valid_and_open(string $slotStartYmdHis, array $config, ?string $excludeToken = null): bool {
    $parts = explode(' ', $slotStartYmdHis);
    if (count($parts) !== 2) {
        return false;
    }
    foreach (scheduler_slots_for_date($parts[0], $config, $excludeToken) as $slot) {
        if ($slot['value'] === $slotStartYmdHis) {
            return true;
        }
    }
    return false;
}

function scheduler_format_display(string $slotStartYmdHis, array $config): string {
    $tz = new DateTimeZone($config['timezone']);
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $slotStartYmdHis, $tz);
    return $dt ? $dt->format('l, F j \a\t g:i A') : $slotStartYmdHis;
}

/** @return array{id:int, reschedule_token:string} */
function scheduler_create_appointment(array $data): array {
    $pdo = scheduler_db();
    $token = bin2hex(random_bytes(16));
    $now = gmdate('Y-m-d\TH:i:s\Z');
    $stmt = $pdo->prepare('INSERT INTO appointments (name, phone, email, address, notes, slot_start, status, reschedule_token, created_at, updated_at)
        VALUES (:name, :phone, :email, :address, :notes, :slot_start, \'confirmed\', :token, :now, :now)');
    $stmt->execute([
        ':name' => $data['name'],
        ':phone' => $data['phone'],
        ':email' => $data['email'],
        ':address' => $data['address'],
        ':notes' => $data['notes'] ?? '',
        ':slot_start' => $data['slot_start'],
        ':token' => $token,
        ':now' => $now,
    ]);
    return ['id' => (int)$pdo->lastInsertId(), 'reschedule_token' => $token];
}

function scheduler_get_by_token(string $token): ?array {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare('SELECT * FROM appointments WHERE reschedule_token = ?');
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function scheduler_reschedule(string $token, string $newSlotStart): bool {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare("UPDATE appointments SET slot_start = ?, status = 'confirmed', reminder_sent_at = NULL, updated_at = ? WHERE reschedule_token = ?");
    return $stmt->execute([$newSlotStart, gmdate('Y-m-d\TH:i:s\Z'), $token]);
}

function scheduler_cancel(string $token): bool {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled', updated_at = ? WHERE reschedule_token = ?");
    return $stmt->execute([gmdate('Y-m-d\TH:i:s\Z'), $token]);
}

/** @return array[] confirmed appointments for $dateYmd that haven't had a reminder sent yet. */
function scheduler_appointments_needing_reminder(string $dateYmd): array {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE status = 'confirmed' AND date(slot_start) = ? AND reminder_sent_at IS NULL");
    $stmt->execute([$dateYmd]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function scheduler_mark_reminder_sent(int $id): void {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare('UPDATE appointments SET reminder_sent_at = ? WHERE id = ?');
    $stmt->execute([gmdate('Y-m-d\TH:i:s\Z'), $id]);
}

/** @return array[] appointments for the admin list view. */
function scheduler_list_for_admin(string $filter): array {
    $pdo = scheduler_db();
    if ($filter === 'cancelled') {
        $stmt = $pdo->query("SELECT * FROM appointments WHERE status = 'cancelled' ORDER BY slot_start DESC");
    } elseif ($filter === 'all') {
        $stmt = $pdo->query('SELECT * FROM appointments ORDER BY slot_start DESC');
    } else {
        $now = (new DateTime('now'))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE status = 'confirmed' AND slot_start >= ? ORDER BY slot_start ASC");
        $stmt->execute([$now]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
