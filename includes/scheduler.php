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

require_once __DIR__ . '/google-calendar.php';

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
        sms_opt_in INTEGER NOT NULL DEFAULT 0,
        reschedule_token TEXT NOT NULL UNIQUE,
        reminder_sent_at TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_appointments_slot_start ON appointments(slot_start)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_appointments_token ON appointments(reschedule_token)');

    // Lightweight migration: sms_opt_in was added after this table may have
    // already been created (e.g. on a live site with real bookings) — add
    // it if it's missing instead of assuming a fresh CREATE TABLE ran.
    $columns = $pdo->query('PRAGMA table_info(appointments)')->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('sms_opt_in', $columns, true)) {
        $pdo->exec('ALTER TABLE appointments ADD COLUMN sms_opt_in INTEGER NOT NULL DEFAULT 0');
    }
    // Same pattern: gcal_event_id (Google Calendar sync) added later too.
    if (!in_array('gcal_event_id', $columns, true)) {
        $pdo->exec('ALTER TABLE appointments ADD COLUMN gcal_event_id TEXT');
    }

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

/** @param array<int, array{start: DateTime, end: DateTime}> $busyPeriods */
function scheduler_overlaps_busy_period(DateTime $slotStart, DateTime $slotEnd, array $busyPeriods): bool {
    foreach ($busyPeriods as $busy) {
        if ($slotStart < $busy['end'] && $slotEnd > $busy['start']) {
            return true;
        }
    }
    return false;
}

/**
 * Open, bookable slots for one calendar date. Re-derives everything from
 * config + the DB every time (business hours, lead time, booking window,
 * blackout dates, already-booked slots) — this is also what validates a
 * submitted booking server-side, so it must never trust client input.
 *
 * $busyPeriods (see gcal_get_busy_periods()) additionally excludes any
 * slot that overlaps existing time already blocked on a synced Google
 * Calendar — pre-fetched by the caller (once per date or once per whole
 * month, see scheduler_slot_is_valid_and_open()/
 * scheduler_days_with_availability() below) rather than fetched in here,
 * so this function doesn't make its own network call every time it runs.
 *
 * @param array<int, array{start: DateTime, end: DateTime}> $busyPeriods
 * @return array<int, array{value:string,label:string}>
 */
function scheduler_slots_for_date(string $dateYmd, array $config, ?string $excludeToken = null, array $busyPeriods = []): array {
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
            $slotEnd = (clone $cursor)->modify('+' . $slotMinutes . ' minutes');
            if (!isset($booked[$value]) && !scheduler_overlaps_busy_period($cursor, $slotEnd, $busyPeriods)) {
                $slots[] = ['value' => $value, 'label' => $cursor->format('g:i A')];
            }
        }
        $cursor->modify('+' . $slotMinutes . ' minutes');
    }
    return $slots;
}

/** @return array<string,int> date (Y-m-d) => number of open slots, for calendar rendering. */
function scheduler_days_with_availability(string $monthYm, array $config, array $gcalConfig): array {
    $tz = new DateTimeZone($config['timezone']);
    $first = DateTime::createFromFormat('Y-m-d', $monthYm . '-01', $tz);
    if (!$first) {
        return [];
    }
    $daysInMonth = (int)$first->format('t');
    // One freeBusy call for the whole month, not one per day — reused
    // across every scheduler_slots_for_date() call in the loop below.
    $rangeStart = (clone $first)->setTime(0, 0, 0);
    $rangeEnd = (clone $rangeStart)->modify('+' . $daysInMonth . ' days');
    $busyPeriods = gcal_get_busy_periods($gcalConfig, $rangeStart, $rangeEnd);

    $result = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dateYmd = $first->format('Y-m') . '-' . str_pad((string)$d, 2, '0', STR_PAD_LEFT);
        $count = count(scheduler_slots_for_date($dateYmd, $config, null, $busyPeriods));
        if ($count > 0) {
            $result[$dateYmd] = $count;
        }
    }
    return $result;
}

/** Server-side re-validation that a submitted slot is real and still open — including against synced Google Calendar conflicts. */
function scheduler_slot_is_valid_and_open(string $slotStartYmdHis, array $config, array $gcalConfig, ?string $excludeToken = null): bool {
    $parts = explode(' ', $slotStartYmdHis);
    if (count($parts) !== 2) {
        return false;
    }
    $tz = new DateTimeZone($config['timezone']);
    $dayStart = DateTime::createFromFormat('Y-m-d H:i:s', $parts[0] . ' 00:00:00', $tz);
    if (!$dayStart) {
        return false;
    }
    $dayEnd = (clone $dayStart)->modify('+1 day');
    $busyPeriods = gcal_get_busy_periods($gcalConfig, $dayStart, $dayEnd);
    foreach (scheduler_slots_for_date($parts[0], $config, $excludeToken, $busyPeriods) as $slot) {
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
    $stmt = $pdo->prepare('INSERT INTO appointments (name, phone, email, address, notes, slot_start, status, sms_opt_in, reschedule_token, created_at, updated_at)
        VALUES (:name, :phone, :email, :address, :notes, :slot_start, \'confirmed\', :sms_opt_in, :token, :now, :now)');
    $stmt->execute([
        ':name' => $data['name'],
        ':phone' => $data['phone'],
        ':email' => $data['email'],
        ':address' => $data['address'],
        ':notes' => $data['notes'] ?? '',
        ':slot_start' => $data['slot_start'],
        ':sms_opt_in' => !empty($data['sms_opt_in']) ? 1 : 0,
        ':token' => $token,
        ':now' => $now,
    ]);
    return ['id' => (int)$pdo->lastInsertId(), 'reschedule_token' => $token];
}

/** Records the Google Calendar event id created for a booking, for later update/delete on reschedule/cancel. */
function scheduler_set_gcal_event_id(int $id, string $eventId): void {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare('UPDATE appointments SET gcal_event_id = ? WHERE id = ?');
    $stmt->execute([$eventId, $id]);
}

function scheduler_get_by_token(string $token): ?array {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare('SELECT * FROM appointments WHERE reschedule_token = ?');
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function scheduler_reschedule(string $token, string $newSlotStart, bool $smsOptIn): bool {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare("UPDATE appointments SET slot_start = ?, status = 'confirmed', sms_opt_in = ?, reminder_sent_at = NULL, updated_at = ? WHERE reschedule_token = ?");
    return $stmt->execute([$newSlotStart, $smsOptIn ? 1 : 0, gmdate('Y-m-d\TH:i:s\Z'), $token]);
}

function scheduler_cancel(string $token): bool {
    $pdo = scheduler_db();
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled', updated_at = ? WHERE reschedule_token = ?");
    return $stmt->execute([gmdate('Y-m-d\TH:i:s\Z'), $token]);
}

// Reminder window, in hours-before-appointment: wider than the
// recommended hourly cron interval so one delayed/missed run can't skip
// anyone. See scheduler_appointments_needing_reminder() below for why this
// replaced a fixed "run once daily, find tomorrow's appointments" check.
const SCHEDULER_REMINDER_WINDOW_MIN_HOURS = 23;
const SCHEDULER_REMINDER_WINDOW_MAX_HOURS = 25;

/**
 * Confirmed, SMS-opted-in appointments that are 23-25 hours out and
 * haven't had a reminder sent yet — call this roughly hourly (see
 * bin/send-reminders.php), not once a day at a fixed time.
 *
 * A fixed daily run had a real gap: this scheduler's minimum booking lead
 * time is exactly 24 hours (config/scheduler.php's lead_time_hours), so
 * any booking made after that day's run, for a slot the run would have
 * called "tomorrow," was never picked up again — by the next day's run,
 * date(slot_start) was "today," not "tomorrow," and it fell through
 * permanently. A rolling hours-until-appointment window instead of a
 * calendar-date match means every appointment passes through the window
 * exactly once regardless of when it was booked, and reminders land at a
 * consistent ~24 hours before the actual appointment time rather than a
 * fixed clock time that's a poor fit for both an 8am and a 4pm slot.
 */
function scheduler_appointments_needing_reminder(array $config): array {
    $pdo = scheduler_db();
    $tz = new DateTimeZone($config['timezone']);
    $now = new DateTime('now', $tz);
    $windowStart = (clone $now)->modify('+' . SCHEDULER_REMINDER_WINDOW_MIN_HOURS . ' hours')->format('Y-m-d H:i:s');
    $windowEnd = (clone $now)->modify('+' . SCHEDULER_REMINDER_WINDOW_MAX_HOURS . ' hours')->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE status = 'confirmed' AND sms_opt_in = 1 AND slot_start >= ? AND slot_start <= ? AND reminder_sent_at IS NULL");
    $stmt->execute([$windowStart, $windowEnd]);
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
