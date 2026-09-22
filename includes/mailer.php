<?php
/**
 * Shared plain-text email sender for every lead path (quote form,
 * scheduler booking/reschedule/cancellation). Tries real SMTP first when
 * configured, and — critically — always falls back to PHP's built-in
 * mail() if SMTP fails for ANY reason (not just "no credentials set"):
 * a transient SMTP hiccup (a Gmail throttle, a network blip) used to mean
 * no email went out at all, since the old fallback only ever triggered
 * when SMTP wasn't configured in the first place. Two independent
 * delivery paths per email is cheap insurance against losing a real lead
 * to one bad SMTP connection.
 */
declare(strict_types=1);

require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function send_transactional_email(
    array $mailConfig,
    string $toEmail,
    string $toName,
    string $subject,
    string $body,
    ?string $replyToEmail = null,
    ?string $replyToName = null
): bool {
    if (!empty($mailConfig['host']) && !empty($mailConfig['username']) && !empty($mailConfig['password'])) {
        $mailer = new PHPMailer(true);
        try {
            $mailer->CharSet = PHPMailer::CHARSET_UTF8;
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
            $mailer->addAddress($toEmail, $toName);
            if ($replyToEmail !== null) {
                $mailer->addReplyTo($replyToEmail, $replyToName ?? '');
            }
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->isHTML(false);

            if ($mailer->send()) {
                return true;
            }
            error_log('Transactional email SMTP send failed, falling back to mail(): ' . $mailer->ErrorInfo);
        } catch (PHPMailerException $e) {
            error_log('Transactional email SMTP send threw, falling back to mail(): ' . $mailer->ErrorInfo);
        }
        // Don't return false here — fall through to the mail() attempt
        // below instead of giving up after one failed delivery path.
    }

    $headers = [
        'From: ' . $mailConfig['from_name'] . ' <' . $mailConfig['from_email'] . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    if ($replyToEmail !== null) {
        $headers[] = 'Reply-To: ' . ($replyToName ?? '') . ' <' . $replyToEmail . '>';
    }
    return mail($toEmail, $subject, $body, implode("\r\n", $headers));
}

/**
 * True last resort: called only when a CRITICAL notification (a new lead
 * or booking) failed through BOTH delivery paths above. Writes the full
 * message to a local log file so the raw lead details are never silently
 * lost even in a total email outage — same "own debug log, since
 * Hostinger's error log is hard to find" convention as
 * data/zoho-debug.log and data/gcal-debug.log. This is a safety net to
 * check manually if a lead ever seems to be missing, not a real-time
 * alert — there's no SMS/push here, since the whole point is that email
 * itself has already failed twice.
 */
function record_failed_lead_email(string $context, string $subject, string $body): void {
    error_log("URGENT: transactional email failed via both SMTP and mail() for [$context]: $subject");
    $entry = '[' . date('c') . "] FAILED TO EMAIL — $context\nSubject: $subject\n\n$body\n" . str_repeat('-', 60) . "\n\n";
    @file_put_contents(__DIR__ . '/../data/failed-leads.log', $entry, FILE_APPEND | LOCK_EX);
}
