<?php
/**
 * Sends the day-before SMS reminder (with a reschedule link) for tomorrow's
 * turf-installation estimate appointments. Meant to run once a day via a
 * Hostinger cron job (hPanel > Advanced > Cron Jobs), e.g.:
 *
 *   php /home/USERNAME/domains/cleangreenturf.com/public_html/bin/send-reminders.php
 *
 * Run it in the early evening so "tomorrow" reminders land at a reasonable
 * hour. Requires TWILIO_* credentials in .env — see .env.example. Safe to
 * run with no Twilio credentials configured yet: it logs and skips instead
 * of failing, same graceful-degradation approach as the SMTP fallback.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/scheduler.php';
require_once __DIR__ . '/../includes/sms.php';

$businessInfo = require __DIR__ . '/../config/business-info.php';
$schedulerConfig = require __DIR__ . '/../config/scheduler.php';
$smsConfig = require __DIR__ . '/../config/sms.php';

$tz = new DateTimeZone($schedulerConfig['timezone']);
$tomorrow = (new DateTime('now', $tz))->modify('+1 day')->format('Y-m-d');

$appointments = scheduler_appointments_needing_reminder($tomorrow);

$sent = 0;
$skipped = 0;

foreach ($appointments as $appt) {
    $phoneE164 = normalize_us_phone_e164($appt['phone']);
    if ($phoneE164 === null) {
        echo "Skipping appointment #{$appt['id']}: unrecognized phone format ({$appt['phone']})\n";
        $skipped++;
        continue;
    }

    $when = scheduler_format_display($appt['slot_start'], $schedulerConfig);
    $rescheduleUrl = $businessInfo['domain'] . '/reschedule?token=' . $appt['reschedule_token'];
    $body = 'Reminder: your free turf installation estimate with ' . $businessInfo['name']
        . " is tomorrow, $when at {$appt['address']}. Need to change it? $rescheduleUrl";

    if (send_sms($smsConfig, $phoneE164, $body)) {
        scheduler_mark_reminder_sent((int)$appt['id']);
        $sent++;
        echo "Sent reminder for appointment #{$appt['id']} ($phoneE164)\n";
    } else {
        $skipped++;
        echo "Failed/skipped reminder for appointment #{$appt['id']} ($phoneE164)\n";
    }
}

echo "Done. Sent: $sent, Skipped: $skipped\n";
