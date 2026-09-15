<?php
/**
 * Sends a single SMS via Twilio's REST API using plain curl — no SDK, no
 * Composer, consistent with the rest of this project. Returns false (and
 * logs) if Twilio isn't configured yet or the API call fails; callers
 * should treat this as best-effort, same as the mail() fallback for email.
 */
declare(strict_types=1);

function send_sms(array $smsConfig, string $toE164, string $body): bool {
    if (empty($smsConfig['account_sid']) || empty($smsConfig['auth_token']) || empty($smsConfig['from_number'])) {
        error_log('SMS not sent (Twilio not configured yet): would have sent to ' . $toE164);
        return false;
    }

    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($smsConfig['account_sid']) . '/Messages.json';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_USERPWD => $smsConfig['account_sid'] . ':' . $smsConfig['auth_token'],
        CURLOPT_POSTFIELDS => http_build_query([
            'To' => $toE164,
            'From' => $smsConfig['from_number'],
            'Body' => $body,
        ]),
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode >= 300) {
        error_log('Twilio SMS send failed (HTTP ' . $httpCode . '): ' . ($curlError ?: $response));
        return false;
    }
    return true;
}

/** Normalizes a US phone number to E.164 (+1XXXXXXXXXX); null if it doesn't look like one. */
function normalize_us_phone_e164(string $phone): ?string {
    $digits = preg_replace('/\D+/', '', $phone);
    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }
    if (strlen($digits) === 11 && $digits[0] === '1') {
        return '+' . $digits;
    }
    return null;
}
