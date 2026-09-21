<?php
/**
 * Reschedules or cancels an existing appointment, identified by its
 * reschedule_token (sent in the confirmation email and reminder text —
 * never guessable, 32 hex chars from random_bytes(16)). Same server-side
 * re-validation and notification pattern as scheduler/book.php.
 *
 * Also keeps the owner's Google Calendar event in sync (see
 * includes/google-calendar.php): a reschedule moves the existing event to
 * its new time instead of leaving a stale one, and a cancellation deletes
 * it outright. Best-effort, same as scheduler/book.php's initial push — a
 * Google failure here never blocks the reschedule/cancel itself. Only
 * appointments with a stored gcal_event_id are touched (bookings made
 * before Google Calendar sync was configured won't have one).
 */
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/scheduler.php';
require_once __DIR__ . '/../includes/google-calendar.php';

$businessInfo = require __DIR__ . '/../config/business-info.php';
$mailConfig = require __DIR__ . '/../config/mail.php';
$schedulerConfig = require __DIR__ . '/../config/scheduler.php';
$gcalConfig = require __DIR__ . '/../config/google-calendar.php';

function scheduler_json_fail(string $error, int $code = 400): never {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    scheduler_json_fail('method_not_allowed', 405);
}

$token = trim((string)($_POST['token'] ?? ''));
$action = trim((string)($_POST['action'] ?? 'reschedule'));
$slotStart = trim((string)($_POST['slot_start'] ?? ''));
$smsOptIn = !empty($_POST['sms_opt_in']);

if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
    scheduler_json_fail('invalid_token');
}

$appt = scheduler_get_by_token($token);
if (!$appt) {
    scheduler_json_fail('not_found', 404);
}
if ($appt['status'] === 'cancelled') {
    scheduler_json_fail('already_cancelled');
}

if ($action === 'cancel') {
    scheduler_cancel($token);

    if (!empty($appt['gcal_event_id'])) {
        gcal_delete_event($gcalConfig, $appt['gcal_event_id']);
    }

    send_transactional_email(
        $mailConfig,
        $appt['email'],
        $appt['name'],
        'Your Estimate Has Been Cancelled',
        "Hi {$appt['name']},\n\nYour turf installation estimate has been cancelled as requested. "
            . "If that was a mistake, or you'd like to rebook, just visit:\n"
            . $businessInfo['domain'] . "/schedule-turf-installation-estimate\n\n{$businessInfo['name']}\n"
    );
    send_transactional_email(
        $mailConfig,
        $businessInfo['email'],
        $businessInfo['name'],
        'Estimate Cancelled — ' . $appt['name'],
        "{$appt['name']} cancelled their estimate that was scheduled for "
            . scheduler_format_display($appt['slot_start'], $schedulerConfig) . ".\n"
    );

    echo json_encode(['success' => true, 'cancelled' => true]);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $slotStart)) {
    scheduler_json_fail('invalid_slot');
}
if (!scheduler_slot_is_valid_and_open($slotStart, $schedulerConfig, $gcalConfig, $token)) {
    scheduler_json_fail('slot_unavailable', 409);
}

scheduler_reschedule($token, $slotStart, $smsOptIn);
$prettyWhen = scheduler_format_display($slotStart, $schedulerConfig);

if (!empty($appt['gcal_event_id'])) {
    $tz = new DateTimeZone($schedulerConfig['timezone']);
    $newStart = new DateTime($slotStart, $tz);
    $newEnd = (clone $newStart)->modify('+' . (int)$schedulerConfig['slot_minutes'] . ' minutes');
    gcal_update_event($gcalConfig, $appt['gcal_event_id'], [
        'start' => ['dateTime' => $newStart->format('c'), 'timeZone' => $schedulerConfig['timezone']],
        'end' => ['dateTime' => $newEnd->format('c'), 'timeZone' => $schedulerConfig['timezone']],
    ]);
}

send_transactional_email(
    $mailConfig,
    $appt['email'],
    $appt['name'],
    'Your Estimate Has Been Rescheduled — ' . $prettyWhen,
    "Hi {$appt['name']},\n\nYour turf installation estimate has been moved to:\n\n"
        . "$prettyWhen\n{$appt['address']}\n\n"
        . "Need to change it again? Use this link any time:\n"
        . $businessInfo['domain'] . '/reschedule?token=' . $token . "\n\n{$businessInfo['name']}\n"
);
send_transactional_email(
    $mailConfig,
    $businessInfo['email'],
    $businessInfo['name'],
    'Estimate Rescheduled — ' . $appt['name'],
    "{$appt['name']} moved their estimate to $prettyWhen.\n"
);

echo json_encode(['success' => true, 'when' => $prettyWhen, 'sms_opt_in' => $smsOptIn]);
