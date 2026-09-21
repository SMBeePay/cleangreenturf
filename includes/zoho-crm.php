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

// Deals.Pipeline value for each pipeline. IMPORTANT: use the plain
// display text for both Pipeline and Stage on every pipeline, including
// Turf Installation — verified by reading real existing Deal records
// directly (getRecords), which store Pipeline as "Turf Installation" and
// Stage as e.g. "Won – Installed", the same text shown in the CRM UI.
// The field-metadata endpoint's `pick_list_values[].actual_value` (e.g.
// "Standard (Standard)" for Pipeline, "Qualification" for the New Lead
// stage) is a legacy/reporting artifact left over from when this
// pipeline was renamed from Zoho's original default "Standard" pipeline
// — NOT what create/read record calls actually use. Two earlier attempts
// at this file used those legacy `actual_value` strings and were
// rejected by the API both times; see docs/audit-findings.md "Zoho CRM
// integration" for the full trail.
const ZOHO_PIPELINE_INSTALLATION = 'Turf Installation';
const ZOHO_PIPELINE_CLEANING = 'Turf Cleaning';
const ZOHO_PIPELINE_REPAIR = 'Turf Repair';

// The Deals module's only layout — set explicitly on every create
// request rather than left to auto-resolve.
const ZOHO_DEALS_LAYOUT_ID = '7612959000000091023';

const ZOHO_STAGE_INSTALLATION_NEW = 'New Lead';
const ZOHO_STAGE_CLEANING_NEW = 'New Cleaning Inquiry';
const ZOHO_STAGE_REPAIR_NEW = 'New Repair Inquiry';

// Lead_Channel, Lead_Source, and Service_Line (Deals module) — same
// renamed-picklist situation as Pipeline/Stage above: getFields' picklist
// metadata shows these options' `actual_value` no longer matches what's
// displayed in the CRM UI (e.g. Lead_Channel's "Quote Form" option has
// actual_value "Thumbtack" left over from before it was renamed). Use the
// current display text below, confirmed directly against getFields —
// NOT the legacy actual_value, per the Pipeline/Stage lesson.
const ZOHO_LEAD_CHANNEL_QUOTE_FORM = 'Quote Form';
const ZOHO_LEAD_CHANNEL_SCHEDULER = 'Scheduler';
const ZOHO_LEAD_SOURCE_WEBSITE = 'Website';
const ZOHO_LEAD_SOURCE_GOOGLE_ADS = 'Google Ads';

const ZOHO_SERVICE_LINE_INSTALLATION = 'Turf Installation';
const ZOHO_SERVICE_LINE_CLEANING = 'Turf Cleaning';
const ZOHO_SERVICE_LINE_REPAIR = 'Turf Repair';

/**
 * Best-effort "landing page" for a lead: the page the form/booking widget
 * was actually submitted from (the HTTP Referer header on the request),
 * not true first-touch attribution — this project has no click-tracking
 * infrastructure beyond that. Falls back to the given path on the site's
 * own domain if no Referer is present (e.g. a stripped Referrer-Policy).
 */
function zoho_landing_page_url(array $businessInfo, string $fallbackPath): string {
    $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
    return $referer !== '' ? $referer : rtrim($businessInfo['domain'], '/') . $fallbackPath;
}

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
 * Upserts a record into $module (dedup on $duplicateCheckFields) and
 * returns its real Zoho id, or null on failure. Used instead of the
 * Deals API's inline "give me a name and I'll create the record" lookup
 * shorthand — that shorthand isn't supported for Contact_Name on this
 * org's Deals layout (confirmed via a live MANDATORY_NOT_FOUND rejection
 * on Contact_Name.id — see docs/audit-findings.md "Zoho CRM integration"),
 * so every Deal now links to a real Account/Contact id instead of relying
 * on it. Requires OAuth scope for the target module (Accounts/Contacts),
 * not just Deals — see .env.example.
 */
function zoho_upsert_record(array $zohoConfig, string $module, array $fields, array $duplicateCheckFields): ?string {
    try {
        $accessToken = zoho_get_access_token($zohoConfig);
        if ($accessToken === null) {
            return null;
        }

        $ch = curl_init(rtrim($zohoConfig['api_domain'], '/') . '/crm/v8/' . $module . '/upsert');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Zoho-oauthtoken ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'data' => [$fields],
                'duplicate_check_fields' => $duplicateCheckFields,
            ], JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 300) {
            zoho_log("Zoho CRM: upsert $module failed (HTTP $httpCode, curl error: \"$curlError\"): $response");
            return null;
        }

        $record = json_decode($response, true)['data'][0] ?? null;
        $id = $record['details']['id'] ?? null;
        if (($record['status'] ?? null) !== 'success' || empty($id)) {
            zoho_log("Zoho CRM: upsert $module rejected: $response");
            return null;
        }

        return (string)$id;
    } catch (\Throwable $e) {
        zoho_log("Zoho CRM: upsert $module exception: " . $e->getMessage());
        return null;
    }
}

function zoho_upsert_account(array $zohoConfig, string $accountName): ?string {
    return zoho_upsert_record($zohoConfig, 'Accounts', ['Account_Name' => $accountName], ['Account_Name']);
}

function zoho_upsert_contact(array $zohoConfig, string $fullName, string $email, string $phone): ?string {
    $parts = preg_split('/\s+/', trim($fullName), 2);
    return zoho_upsert_record($zohoConfig, 'Contacts', [
        'First_Name' => $parts[0] ?? $fullName,
        'Last_Name' => $parts[1] ?? ($parts[0] ?? $fullName),
        'Email' => $email,
        'Phone' => $phone,
    ], ['Email']);
}

/**
 * Full lead-to-Deal push: upserts the Account and Contact first, then
 * creates the Deal linked to their real ids. $dealFields should contain
 * everything EXCEPT Account_Name/Contact_Name (Deal_Name, Pipeline,
 * Stage, Closing_Date, Description, etc.) — this adds those two.
 * Account_Name is required on this org's Deals layout, so a failed
 * Account upsert aborts the whole push (returns false); Contact_Name is
 * optional, so a failed Contact upsert just omits it rather than
 * blocking the Deal.
 */
function zoho_push_lead(array $zohoConfig, string $name, string $email, string $phone, array $dealFields): bool {
    $accountId = zoho_upsert_account($zohoConfig, $name);
    if ($accountId === null) {
        zoho_log('Zoho CRM: aborting Deal creation, Account upsert failed for ' . $name);
        return false;
    }
    $dealFields['Account_Name'] = ['id' => $accountId];

    $contactId = zoho_upsert_contact($zohoConfig, $name, $email, $phone);
    if ($contactId !== null) {
        $dealFields['Contact_Name'] = ['id' => $contactId];
    }

    return zoho_create_deal($zohoConfig, $dealFields);
}

/**
 * Creates a Deal record. $fields should already contain Deal_Name, Stage,
 * Pipeline, Account_Name, Closing_Date, etc. Returns true on success,
 * false on any failure — never throws (see file doc). Prefer
 * zoho_push_lead() over calling this directly — it handles the
 * Account/Contact linking Zoho's Deals API doesn't do inline.
 */
function zoho_create_deal(array $zohoConfig, array $fields): bool {
    $fields['Layout'] ??= ['id' => ZOHO_DEALS_LAYOUT_ID];
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
