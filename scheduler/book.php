<?php
/**
 * Books a new turf-installation estimate appointment. Re-validates the
 * submitted slot against live availability server-side (never trusts the
 * client) to prevent double-booking, then emails a confirmation to the
 * customer and a notification to the business — same SMTP/mail() pattern
 * as forms/handle-quote.php.
 *
 * Also pushes the booking into Zoho CRM's Turf Installation pipeline (see
 * includes/zoho-crm.php and docs/audit-findings.md "Zoho CRM integration")
 * with the real scheduled date/time in the Estimate_Scheduled field — this
 * is the one lead source where we actually know the appointment time up
 * front, unlike the quote form's placeholder Closing_Date. Best-effort,
 * same as the quote form: a CRM failure never blocks the booking itself.
 */
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/scheduler.php';
require_once __DIR__ . '/../includes/zoho-crm.php';

$businessInfo = require __DIR__ . '/../config/business-info.php';
$mailConfig = require __DIR__ . '/../config/mail.php';
$schedulerConfig = require __DIR__ . '/../config/scheduler.php';
$zohoConfig = require __DIR__ . '/../config/zoho.php';

function scheduler_json_fail(string $error, int $code = 400): never {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    scheduler_json_fail('method_not_allowed', 405);
}

// Honeypot — bots fill every field, real users never see this one.
if (!empty($_POST['website'])) {
    scheduler_json_fail('rejected');
}

$name = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$address = trim((string)($_POST['address'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));
$slotStart = trim((string)($_POST['slot_start'] ?? ''));
$smsOptIn = !empty($_POST['sms_opt_in']);

if ($name === '' || $phone === '' || $email === '' || $address === '' || $slotStart === '') {
    scheduler_json_fail('missing_fields');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    scheduler_json_fail('invalid_email');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $slotStart)) {
    scheduler_json_fail('invalid_slot');
}

function scheduler_clean_line(string $value): string {
    return trim(preg_replace('/[\r\n]+/', ' ', $value));
}
$name = scheduler_clean_line($name);
$phone = scheduler_clean_line($phone);
$address = scheduler_clean_line($address);

if (!scheduler_slot_is_valid_and_open($slotStart, $schedulerConfig)) {
    scheduler_json_fail('slot_unavailable', 409);
}

$result = scheduler_create_appointment([
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'address' => $address,
    'notes' => $notes,
    'slot_start' => $slotStart,
    'sms_opt_in' => $smsOptIn,
]);

$prettyWhen = scheduler_format_display($slotStart, $schedulerConfig);
$rescheduleUrl = $businessInfo['domain'] . '/reschedule?token=' . $result['reschedule_token'];
$phoneDisplay = $businessInfo['regions']['tx']['phone_display'];
$reminderLine = $smsOptIn
    ? "We'll text you a reminder the day before."
    : "You didn't opt in to text reminders, so we won't text you — but you can always come back to this link if plans change:";

$customerBody = "Hi $name,\n\n"
    . "You're confirmed for a free in-person turf installation estimate:\n\n"
    . "$prettyWhen\n$address\n\n"
    . "$reminderLine Need to change your appointment? Use this link any time:\n"
    . "$rescheduleUrl\n\n"
    . "See you then!\n{$businessInfo['name']}\n$phoneDisplay\n";

send_transactional_email(
    $mailConfig,
    $email,
    $name,
    'Your Turf Installation Estimate is Confirmed — ' . $prettyWhen,
    $customerBody
);

$ownerBody = "New turf installation estimate booked online.\n\n"
    . "Name: $name\nPhone: $phone\nEmail: $email\nAddress: $address\nWhen: $prettyWhen\n"
    . 'Notes: ' . ($notes !== '' ? $notes : 'None') . "\n\n"
    . 'Manage: ' . $businessInfo['domain'] . "/admin/index.php\n";

send_transactional_email(
    $mailConfig,
    $businessInfo['email'],
    $businessInfo['name'],
    'New Estimate Booked — ' . $name . ' — ' . $prettyWhen,
    $ownerBody,
    $email,
    $name
);

$slotDateTime = new DateTime($slotStart, new DateTimeZone($schedulerConfig['timezone']));
zoho_create_deal($zohoConfig, [
    'Deal_Name' => "$name — Turf Installation Estimate ($prettyWhen)",
    'Pipeline' => 'Turf Installation',
    'Stage' => ZOHO_STAGE_INSTALLATION_NEW,
    'Account_Name' => ['name' => $name],
    'Contact_Name' => ['name' => $name],
    'Closing_Date' => $slotDateTime->format('Y-m-d'),
    'Estimate_Scheduled' => $slotDateTime->format('Y-m-d\TH:i:sP'),
    'Description' => "Phone: $phone\nEmail: $email\nAddress: $address\nScheduled: $prettyWhen\n"
        . 'SMS reminder opt-in: ' . ($smsOptIn ? 'Yes' : 'No') . "\n"
        . 'Notes: ' . ($notes !== '' ? $notes : 'None') . "\n"
        . 'Reschedule/cancel link: ' . $rescheduleUrl,
]);

echo json_encode(['success' => true, 'when' => $prettyWhen, 'reschedule_url' => $rescheduleUrl, 'sms_opt_in' => $smsOptIn]);
