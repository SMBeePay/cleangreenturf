<?php
/**
 * Lead-form handler. Replaces the old Hostinger-Website-Builder-proprietary
 * form backend (see docs/business-info.md) with a plain PHP mail handler
 * that sends to andrew@cleangreenturf.com, per explicit owner instruction.
 *
 * Production note: PHP's mail() relies on the host's mail transport and is
 * frequently marked as spam without a properly configured sender domain.
 * Once Hostinger SMTP credentials are available, swap the mail() call below
 * for SMTP (e.g. PHPMailer) using credentials from environment variables —
 * never hard-code them here. See docs/migration-requirements.md #19.
 */

declare(strict_types=1);

$businessInfo = require __DIR__ . '/../config/business-info.php';

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
$services = array_map('strval', (array)($_POST['services'] ?? []));
$notes = trim((string)($_POST['notes'] ?? ''));

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

$to = $businessInfo['email'];
$subject = 'New Quote Request — ' . $name;

$body = "New turf cleaning quote request from cleangreenturf.com\n\n";
$body .= "Name: $name\n";
$body .= "Phone: $phone\n";
$body .= "Email: $email\n";
$body .= "Address: $address\n";
$body .= "Approx. turf size: " . ($turfSize !== '' ? $turfSize : 'Not provided') . "\n";
$body .= "Services requested: " . (count($services) ? implode(', ', $services) : 'Not specified') . "\n";
$body .= "Notes: " . ($notes !== '' ? $notes : 'None') . "\n";

$headers = [
    'From: ' . $businessInfo['name'] . ' Website <no-reply@cleangreenturf.com>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'Content-Type: text/plain; charset=UTF-8',
];

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    error_log('Quote form mail() failed for submission from ' . $email);
    redirect_with_error('send_failed');
}

header('Location: /dfw-turf-cleaning-request-success');
exit;
