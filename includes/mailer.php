<?php
/**
 * Shared plain-text email sender for the scheduler (confirmation/reschedule/
 * cancellation notices). Same SMTP-with-mail()-fallback pattern as
 * forms/handle-quote.php, pulled out here so scheduler/book.php,
 * scheduler/reschedule.php, and future callers don't each re-implement it.
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

            return $mailer->send();
        } catch (PHPMailerException $e) {
            error_log('Scheduler email send failed: ' . $mailer->ErrorInfo);
            return false;
        }
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
