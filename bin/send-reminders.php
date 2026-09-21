<?php
/**
 * Sends the day-before SMS reminder (with a reschedule link) for
 * turf-installation estimate appointments that are 23-25 hours out. Meant
 * to run roughly HOURLY via a Hostinger cron job (hPanel > Advanced > Cron
 * Jobs), e.g.:
 *
 *   php /home/USERNAME/domains/cleangreenturf.com/public_html/bin/send-reminders.php
 *
 * Deliberately not a once-a-day job at a fixed clock time — this
 * scheduler's minimum booking lead time is exactly 24 hours
 * (config/scheduler.php's lead_time_hours), so a booking made shortly
 * after that day's run, for what the run considered "tomorrow," would
 * never be picked up again (see scheduler_appointments_needing_reminder()
 * in includes/scheduler.php for the full explanation). Running hourly and
 * matching on a rolling 23-25-hours-out window instead means every
 * appointment passes through the window exactly once no matter when it
 * was booked, and reminders land at a consistent ~24 hours before the
 * actual appointment time — not, say, 15 hours before an 8am slot because
 * the cron happened to run at 5pm the day before. Safe to run this often:
 * reminder_sent_at is set immediately after a successful send, so repeat
 * runs within the window never double-text anyone. Requires TWILIO_*
 * credentials in .env — see .env.example. Also safe to run with no Twilio
 * credentials configured yet: it logs and skips instead of failing, same
 * graceful-degradation approach as the SMTP fallback.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/scheduler.php';
require_once __DIR__ . '/../includes/sms.php';

$businessInfo = require __DIR__ . '/../config/business-info.php';
$schedulerConfig = require __DIR__ . '/../config/scheduler.php';
$smsConfig = require __DIR__ . '/../config/sms.php';

$appointments = scheduler_appointments_needing_reminder($schedulerConfig);

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
