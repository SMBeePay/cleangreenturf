<?php
/**
 * Public read-only availability endpoint for the calendar widget
 * (assets/js/scheduler.js). Two modes:
 *   ?month=YYYY-MM        -> { days: { "YYYY-MM-DD": openSlotCount, ... } }
 *   ?date=YYYY-MM-DD[&token=...] -> { date, slots: [{value,label}, ...] }
 * `token` (reschedule mode only) excludes that appointment's own current
 * slot from the "already booked" check.
 */
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/scheduler.php';
$schedulerConfig = require __DIR__ . '/../config/scheduler.php';
$gcalConfig = require __DIR__ . '/../config/google-calendar.php';

$date = isset($_GET['date']) ? trim((string)$_GET['date']) : null;
$month = isset($_GET['month']) ? trim((string)$_GET['month']) : null;
$token = isset($_GET['token']) ? trim((string)$_GET['token']) : null;

if ($date !== null) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_date']);
        exit;
    }
    $tz = new DateTimeZone($schedulerConfig['timezone']);
    $dayStart = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00', $tz);
    $busyPeriods = $dayStart ? gcal_get_busy_periods($gcalConfig, $dayStart, (clone $dayStart)->modify('+1 day')) : [];
    echo json_encode(['date' => $date, 'slots' => scheduler_slots_for_date($date, $schedulerConfig, $token, $busyPeriods)]);
    exit;
}

if ($month !== null) {
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_month']);
        exit;
    }
    echo json_encode(['month' => $month, 'days' => scheduler_days_with_availability($month, $schedulerConfig, $gcalConfig)]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'missing_date_or_month']);
