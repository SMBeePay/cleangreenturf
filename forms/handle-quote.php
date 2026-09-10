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
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$businessInfo = require __DIR__ . '/../config/business-info.php';
$mailConfig = require __DIR__ . '/../config/mail.php';

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

header('Location: /dfw-turf-cleaning-request-success');
exit;
