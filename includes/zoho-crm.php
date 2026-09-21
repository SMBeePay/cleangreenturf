<?php
/**
 * Minimal Zoho CRM v8 REST client — plain curl, no SDK/Composer, matching
 * this project's existing PHPMailer/Twilio-via-curl pattern. Pushes new
 * website leads into Zoho CRM's Deals module, into whichever of the three
 * existing pipelines matches the service requested.
 *
 * The Turf Installation / Turf Cleaning / Turf Repair pipelines, their
 * Stage values, and the custom fields referenced below (Estimate_Scheduled,
 * Cleaning_Status, etc.) already existed in the Zoho org — this code does
 * not create them. See docs/audit-findings.md "Zoho CRM integration" for
 * the full field/value reference this was built against.
 *
 * Every public function here is best-effort and never throws: a Zoho
 * outage, a bad token, or a missing .env value must never block a lead's
 * confirmation/notification email from sending. Callers just get a bool
 * back and should log-and-continue on false, not fail the request.
 */
declare(strict_types=1);

const ZOHO_TOKEN_CACHE_FILE = __DIR__ . '/../data/zoho-token-cache.json';
const ZOHO_DEBUG_LOG_FILE = __DIR__ . '/../data/zoho-debug.log';

/**
 * Writes to both PHP's normal error_log() (in case the host's log viewer
 * does surface it) and a dedicated file under data/ — same directory as
 * the scheduler's SQLite database, already blocked from direct web access
 * by data/.htaccess and gitignored. Added because Hostinger's error log
 * location isn't always easy to find from hPanel; this guarantees a place
 * to look regardless of hosting panel layout.
 */
function zoho_log(string $message): void {
    error_log($message);
    @file_put_contents(ZOHO_DEBUG_LOG_FILE, '[' . date('c') . '] ' . $message . "\n", FILE_APPEND | LOCK_EX);
}

// Deals.Stage "actual_value" for each pipeline's first/new stage. The Turf
// Installation pipeline reused Zoho's original default stage system field,
// so its underlying value ("Qualification") doesn't match what the CRM UI
// displays ("New Lead") — Turf Cleaning and Turf Repair use plain custom
// stage values that match their display text exactly.
const ZOHO_STAGE_INSTALLATION_NEW = 'Qualification'; // displays as "New Lead"
const ZOHO_STAGE_CLEANING_NEW = 'New Cleaning Inquiry';
const ZOHO_STAGE_REPAIR_NEW = 'New Repair Inquiry';

function zoho_get_access_token(array $zohoConfig): ?string {
    if (empty($zohoConfig['client_id']) || empty($zohoConfig['client_secret']) || empty($zohoConfig['refresh_token'])) {
        return null; // Not configured yet — caller treats this as "skip CRM push."
    }

    if (is_readable(ZOHO_TOKEN_CACHE_FILE)) {
        $cached = json_decode((string)file_get_contents(ZOHO_TOKEN_CACHE_FILE), true);
        if (is_array($cached) && !empty($cached['access_token']) && !empty($cached['expires_at']) && $cached['expires_at'] > time() + 60) {
            return $cached['access_token'];
        }
    }

    $ch = curl_init(rtrim($zohoConfig['accounts_domain'], '/') . '/oauth/v2/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $zohoConfig['client_id'],
            'client_secret' => $zohoConfig['client_secret'],
            'refresh_token' => $zohoConfig['refresh_token'],
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        zoho_log('Zoho CRM: token refresh failed (HTTP ' . $httpCode . ', curl error: "' . $curlError . '"): ' . $response);
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        zoho_log('Zoho CRM: token refresh response missing access_token: ' . $response);
        return null;
    }

    @file_put_contents(ZOHO_TOKEN_CACHE_FILE, json_encode([
        'access_token' => $data['access_token'],
        'expires_at' => time() + (int)($data['expires_in'] ?? 3600),
    ]), LOCK_EX);

    return $data['access_token'];
}

/**
 * Creates a Deal record. $fields should already contain Deal_Name, Stage,
 * Pipeline, Account_Name, Closing_Date, etc. Returns true on success,
 * false on any failure — never throws (see file doc).
 */
function zoho_create_deal(array $zohoConfig, array $fields): bool {
    try {
        $accessToken = zoho_get_access_token($zohoConfig);
        if ($accessToken === null) {
            return false;
        }

        $ch = curl_init(rtrim($zohoConfig['api_domain'], '/') . '/crm/v8/Deals');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Zoho-oauthtoken ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode(['data' => [$fields]], JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 300) {
            zoho_log('Zoho CRM: create Deal failed (HTTP ' . $httpCode . ', curl error: "' . $curlError . '"): ' . $response);
            return false;
        }

        $status = json_decode($response, true)['data'][0]['status'] ?? null;
        if ($status !== 'success') {
            zoho_log('Zoho CRM: create Deal rejected: ' . $response);
            return false;
        }

        zoho_log('Zoho CRM: create Deal succeeded: ' . $response);
        return true;
    } catch (\Throwable $e) {
        zoho_log('Zoho CRM: create Deal exception: ' . $e->getMessage());
        return false;
    }
}
