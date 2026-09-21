<?php
/**
 * Lead-form handler. Replaces the old Hostinger-Website-Builder-proprietary
 * form backend (see docs/business-info.md) with a plain PHP mail handler
 * that sends to andrew@cleangreenturf.com, per explicit owner instruction.
 *
 * Sends via SMTP (PHPMailer, vendored in vendor/phpmailer/ — no Composer
 * needed) when config/mail.php finds SMTP credentials (env vars or a local
 * .env file, see .env.example). Falls back to PHP's mail() otherwise,
 * which works but is not production-reliable for deliverability — see
 * docs/migration-requirements.md #19.
 *
 * Also pushes the lead into Zoho CRM (Deals module, Turf Cleaning or Turf
 * Repair pipeline per the `service` field — see includes/zoho-crm.php and
 * docs/audit-findings.md "Zoho CRM integration"). The CRM push is
 * best-effort: if Zoho isn't configured yet or the API call fails, the
 * email still sends and the customer still sees the success page — CRM
 * sync failures are logged, never surfaced to the visitor.
 *
 * Sends two emails: the owner notification (blocking — if this fails the
 * visitor sees an error and can retry, since a lost lead notification is
 * the one failure mode that actually matters) and a customer-facing
 * "thanks for reaching out" confirmation (best-effort, like the Zoho
 * push — a failure here is logged but never blocks the success redirect).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/zoho-crm.php';

$businessInfo = require __DIR__ . '/../config/business-info.php';
$mailConfig = require __DIR__ . '/../config/mail.php';
$zohoConfig = require __DIR__ . '/../config/zoho.php';

function redirect_with_error(string $reason): never {
    header('Location: /contact?error=' . urlencode($reason));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact');
    exit;
}

// Honeypot — bots fill every field, real users never see this one.
if (!empty($_POST['website'])) {
    // Silently "succeed" so bots don't learn the honeypot worked.
    header('Location: /dfw-turf-cleaning-request-success');
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$address = trim((string)($_POST['address'] ?? ''));
$turfSize = trim((string)($_POST['turf_size'] ?? ''));
$frequency = trim((string)($_POST['frequency'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));
$service = (string)($_POST['service'] ?? 'cleaning');
if (!in_array($service, ['cleaning', 'repair', 'cleaning_repair'], true)) {
    $service = 'cleaning';
}

if ($name === '' || $phone === '' || $email === '' || $address === '') {
    redirect_with_error('missing_fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('invalid_email');
}

// Strip anything that could be used for header injection via a crafted field.
function clean_line(string $value): string {
    return trim(preg_replace('/[\r\n]+/', ' ', $value));
}

$name = clean_line($name);
$phone = clean_line($phone);
$address = clean_line($address);

$subject = 'New Quote Request — ' . $name;

$body = "New turf cleaning quote request from cleangreenturf.com\n\n";
$body .= "Name: $name\n";
$body .= "Phone: $phone\n";
$body .= "Email: $email\n";
$body .= "Full Address: $address\n";
$body .= "Approx Size of Turf Area: " . ($turfSize !== '' ? $turfSize : 'Not provided') . "\n";
$body .= "How Frequently Would You Like Your Turf Cleaned?: " . ($frequency !== '' ? $frequency : 'Not specified') . "\n";
$body .= "Any additional notes we should know about?: " . ($notes !== '' ? $notes : 'None') . "\n";

$sent = send_transactional_email($mailConfig, $businessInfo['email'], $businessInfo['name'], $subject, $body, $email, $name);

if (!$sent) {
    error_log('Quote form submission failed to send for ' . $email);
    redirect_with_error('send_failed');
}

// Customer-facing "thanks for reaching out" confirmation — best-effort,
// same as the Zoho push below: never blocks the redirect, just logged
// on failure. The owner notification above is the one that must succeed.
$serviceLabel = match ($service) {
    'repair' => 'turf repair',
    'cleaning_repair' => 'turf cleaning and repair',
    default => 'turf cleaning',
};
$phoneDisplay = $businessInfo['regions']['tx']['phone_display'];
$customerBody = "Hi $name,\n\n"
    . "Thank you for reaching out to Clean Green Turf for a free $serviceLabel quote! Here's what you submitted:\n\n"
    . "Address: $address\n"
    . ($turfSize !== '' ? "Approx Size: $turfSize\n" : '')
    . ($service !== 'repair' && $frequency !== '' ? "Cleaning Frequency: $frequency\n" : '')
    . ($notes !== '' ? "Notes: $notes\n" : '')
    . "\nWe usually reply same-day with your quote. If anything above needs correcting, just reply to this email or give us a call.\n\n"
    . "Talk soon,\n{$businessInfo['name']}\n$phoneDisplay\n";

if (!send_transactional_email($mailConfig, $email, $name, 'Thanks for Reaching Out to Clean Green Turf!', $customerBody)) {
    error_log('Quote form customer confirmation failed to send for ' . $email);
}

// CRM push happens after the email is confirmed sent, and never blocks the
// redirect — see the file doc and includes/zoho-crm.php.
$landingPageUrl = zoho_landing_page_url($businessInfo, '/contact');
// The Google Ads landing page and the regular quote form both post here —
// distinguish them by which page the submission came from, since there's
// no separate hidden field marking the GA form.
$leadSource = str_contains($landingPageUrl, '/dfw-turf-cleaning-request-ga')
    ? ZOHO_LEAD_SOURCE_GOOGLE_ADS
    : ZOHO_LEAD_SOURCE_WEBSITE;

$dealDetails = "Phone: $phone\nEmail: $email\nAddress: $address\n"
    . 'Approx Size of Turf Area: ' . ($turfSize !== '' ? $turfSize : 'Not provided') . "\n"
    . 'Cleaning Frequency: ' . ($frequency !== '' ? $frequency : 'Not specified') . "\n"
    . 'Notes: ' . ($notes !== '' ? $notes : 'None') . "\n"
    . 'Source: ' . ($_SERVER['HTTP_REFERER'] ?? 'cleangreenturf.com quote form');

if ($service === 'repair' || $service === 'cleaning_repair') {
    // "Cleaning + Repair" is filed under Turf Repair, not Turf Cleaning —
    // repair needs a specific follow-up and is lower-volume, so it's less
    // likely to get lost there than in the high-volume cleaning pipeline.
    // The Description below still flags that cleaning was also requested.
    // See docs/audit-findings.md "Zoho CRM integration" if this default
    // should be flipped.
    $dealName = $service === 'cleaning_repair'
        ? "$name — Turf Repair + Cleaning Quote"
        : "$name — Turf Repair Quote";
    zoho_push_lead($zohoConfig, $name, $email, $phone, [
        'Deal_Name' => $dealName,
        'Pipeline' => ZOHO_PIPELINE_REPAIR,
        'Stage' => ZOHO_STAGE_REPAIR_NEW,
        'Closing_Date' => date('Y-m-d', strtotime('+14 days')),
        'Description' => ($service === 'cleaning_repair' ? "Also wants routine cleaning.\n\n" : '') . $dealDetails,
        'Lead_Channel' => ZOHO_LEAD_CHANNEL_QUOTE_FORM,
        'Lead_Source' => $leadSource,
        'Landing_Page_URL' => $landingPageUrl,
        'Service_Line' => ZOHO_SERVICE_LINE_REPAIR,
    ]);
} else {
    zoho_push_lead($zohoConfig, $name, $email, $phone, [
        'Deal_Name' => "$name — Turf Cleaning Quote",
        'Pipeline' => ZOHO_PIPELINE_CLEANING,
        'Stage' => ZOHO_STAGE_CLEANING_NEW,
        'Cleaning_Status' => 'New Inquiry',
        'Closing_Date' => date('Y-m-d', strtotime('+14 days')),
        'Description' => $dealDetails,
        'Lead_Channel' => ZOHO_LEAD_CHANNEL_QUOTE_FORM,
        'Lead_Source' => $leadSource,
        'Landing_Page_URL' => $landingPageUrl,
        'Service_Line' => ZOHO_SERVICE_LINE_CLEANING,
    ]);
}

header('Location: /dfw-turf-cleaning-request-success');
exit;
