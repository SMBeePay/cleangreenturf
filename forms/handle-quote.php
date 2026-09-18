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
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../includes/zoho-crm.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

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

$to = $businessInfo['email'];
$subject = 'New Quote Request — ' . $name;

$body = "New turf cleaning quote request from cleangreenturf.com\n\n";
$body .= "Name: $name\n";
$body .= "Phone: $phone\n";
$body .= "Email: $email\n";
$body .= "Full Address: $address\n";
$body .= "Approx Size of Turf Area: " . ($turfSize !== '' ? $turfSize : 'Not provided') . "\n";
$body .= "How Frequently Would You Like Your Turf Cleaned?: " . ($frequency !== '' ? $frequency : 'Not specified') . "\n";
$body .= "Any additional notes we should know about?: " . ($notes !== '' ? $notes : 'None') . "\n";

$sent = false;

if (!empty($mailConfig['host']) && !empty($mailConfig['username']) && !empty($mailConfig['password'])) {
    // SMTP path — reliable delivery, won't land in spam as easily as mail().
    $mailer = new PHPMailer(true);
    try {
        $mailer->isSMTP();
        $mailer->Host = $mailConfig['host'];
        $mailer->Port = $mailConfig['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $mailConfig['username'];
        $mailer->Password = $mailConfig['password'];
        if ($mailConfig['encryption'] === 'ssl') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($mailConfig['encryption'] === 'tls') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mailer->SMTPAutoTLS = false;
        }

        $mailer->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mailer->addAddress($to);
        $mailer->addReplyTo($email, $name);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->isHTML(false);

        $sent = $mailer->send();
    } catch (PHPMailerException $e) {
        error_log('Quote form SMTP send failed: ' . $mailer->ErrorInfo);
        $sent = false;
    }
} else {
    // No SMTP configured yet — fall back to mail().
    $headers = [
        'From: ' . $mailConfig['from_name'] . ' <' . $mailConfig['from_email'] . '>',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    $sent = mail($to, $subject, $body, implode("\r\n", $headers));
}

if (!$sent) {
    error_log('Quote form submission failed to send for ' . $email);
    redirect_with_error('send_failed');
}

// CRM push happens after the email is confirmed sent, and never blocks the
// redirect — see the file doc and includes/zoho-crm.php.
[$firstName, $lastName] = zoho_split_name($name);
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
    zoho_create_deal($zohoConfig, [
        'Deal_Name' => $dealName,
        'Pipeline' => 'Turf Repair',
        'Stage' => ZOHO_STAGE_REPAIR_NEW,
        'Account_Name' => ['name' => $name],
        'Contact_Name' => ['First_Name' => $firstName, 'Last_Name' => $lastName !== '' ? $lastName : $firstName],
        'Closing_Date' => date('Y-m-d', strtotime('+14 days')),
        'Description' => ($service === 'cleaning_repair' ? "Also wants routine cleaning.\n\n" : '') . $dealDetails,
    ]);
} else {
    zoho_create_deal($zohoConfig, [
        'Deal_Name' => "$name — Turf Cleaning Quote",
        'Pipeline' => 'Turf Cleaning',
        'Stage' => ZOHO_STAGE_CLEANING_NEW,
        'Cleaning_Status' => 'New Inquiry',
        'Account_Name' => ['name' => $name],
        'Contact_Name' => ['First_Name' => $firstName, 'Last_Name' => $lastName !== '' ? $lastName : $firstName],
        'Closing_Date' => date('Y-m-d', strtotime('+14 days')),
        'Description' => $dealDetails,
    ]);
}

header('Location: /dfw-turf-cleaning-request-success');
exit;
