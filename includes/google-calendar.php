<?php
/**
 * Minimal Google Calendar v3 REST client — plain curl + PHP's built-in
 * openssl_sign() for the service-account JWT, no SDK/Composer, matching
 * this project's existing Zoho/Twilio/PHPMailer pattern. Pushes booked
 * turf-installation estimates onto the owner's dedicated Google Calendar
 * so bookings show up there without checking /admin.
 *
 * Auth: a Google service account, authorized via a signed JWT exchanged
 * for a short-lived OAuth access token (the standard "JWT Bearer" flow
 * for server-to-server access) — no interactive login, no refresh token
 * to manage. The target calendar itself must be shared with the service
 * account's email address (Make changes to events permission) in Google
 * Calendar's own sharing settings — this code only ever has access to
 * whatever calendar the owner explicitly shares with it. See
 * .env.example for the full one-time setup.
 *
 * Every public function here is best-effort and never throws: a Google
 * outage or missing/bad credentials must never block a booking,
 * reschedule, or cancellation from completing. Callers get a bool (or
 * null for create) back and should log-and-continue on failure.
 */
declare(strict_types=1);

const GCAL_TOKEN_CACHE_FILE = __DIR__ . '/../data/gcal-token-cache.json';
const GCAL_DEBUG_LOG_FILE = __DIR__ . '/../data/gcal-debug.log';

/** Same "own debug log, since Hostinger's error log is hard to find" pattern as includes/zoho-crm.php. */
function gcal_log(string $message): void {
    error_log($message);
    @file_put_contents(GCAL_DEBUG_LOG_FILE, '[' . date('c') . '] ' . $message . "\n", FILE_APPEND | LOCK_EX);
}

function gcal_base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function gcal_get_access_token(array $config): ?string {
    if (empty($config['client_email']) || empty($config['private_key']) || empty($config['calendar_id'])) {
        return null; // Not configured yet — caller treats this as "skip calendar push."
    }

    if (is_readable(GCAL_TOKEN_CACHE_FILE)) {
        $cached = json_decode((string)file_get_contents(GCAL_TOKEN_CACHE_FILE), true);
        if (is_array($cached) && !empty($cached['access_token']) && !empty($cached['expires_at']) && $cached['expires_at'] > time() + 60) {
            return $cached['access_token'];
        }
    }

    $now = time();
    $header = gcal_base64url_encode((string)json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claim = gcal_base64url_encode((string)json_encode([
        'iss' => $config['client_email'],
        'scope' => 'https://www.googleapis.com/auth/calendar',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600,
    ]));
    $signingInput = "$header.$claim";

    $privateKey = openssl_pkey_get_private($config['private_key']);
    if ($privateKey === false) {
        gcal_log('Google Calendar: could not parse service account private key: ' . openssl_error_string());
        return null;
    }
    $signed = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    if (!$signed) {
        gcal_log('Google Calendar: JWT signing failed: ' . openssl_error_string());
        return null;
    }
    $jwt = $signingInput . '.' . gcal_base64url_encode($signature);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]),
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        gcal_log("Google Calendar: token exchange failed (HTTP $httpCode, curl error: \"$curlError\"): $response");
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        gcal_log('Google Calendar: token response missing access_token: ' . $response);
        return null;
    }

    @file_put_contents(GCAL_TOKEN_CACHE_FILE, json_encode([
        'access_token' => $data['access_token'],
        'expires_at' => time() + (int)($data['expires_in'] ?? 3600),
    ]), LOCK_EX);

    return $data['access_token'];
}

/** Creates a calendar event. Returns its Google event id, or null on failure/not-configured. */
function gcal_create_event(array $config, array $eventFields): ?string {
    try {
        $accessToken = gcal_get_access_token($config);
        if ($accessToken === null) {
            return null;
        }

        $ch = curl_init('https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($config['calendar_id']) . '/events');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($eventFields, JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 300) {
            gcal_log("Google Calendar: create event failed (HTTP $httpCode, curl error: \"$curlError\"): $response");
            return null;
        }

        $eventId = json_decode($response, true)['id'] ?? null;
        if (empty($eventId)) {
            gcal_log('Google Calendar: create event response missing id: ' . $response);
            return null;
        }

        gcal_log('Google Calendar: created event ' . $eventId);
        return (string)$eventId;
    } catch (\Throwable $e) {
        gcal_log('Google Calendar: create event exception: ' . $e->getMessage());
        return null;
    }
}

/** Updates an existing calendar event's fields (e.g. new start/end after a reschedule). */
function gcal_update_event(array $config, string $eventId, array $eventFields): bool {
    try {
        $accessToken = gcal_get_access_token($config);
        if ($accessToken === null) {
            return false;
        }

        $ch = curl_init('https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($config['calendar_id']) . '/events/' . rawurlencode($eventId));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($eventFields, JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 300) {
            gcal_log("Google Calendar: update event $eventId failed (HTTP $httpCode, curl error: \"$curlError\"): $response");
            return false;
        }

        gcal_log("Google Calendar: updated event $eventId");
        return true;
    } catch (\Throwable $e) {
        gcal_log("Google Calendar: update event $eventId exception: " . $e->getMessage());
        return false;
    }
}

/** Deletes a calendar event (e.g. after a cancellation). A 404/410 (already gone) counts as success. */
function gcal_delete_event(array $config, string $eventId): bool {
    try {
        $accessToken = gcal_get_access_token($config);
        if ($accessToken === null) {
            return false;
        }

        $ch = curl_init('https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($config['calendar_id']) . '/events/' . rawurlencode($eventId));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || ($httpCode >= 300 && $httpCode !== 404 && $httpCode !== 410)) {
            gcal_log("Google Calendar: delete event $eventId failed (HTTP $httpCode, curl error: \"$curlError\"): $response");
            return false;
        }

        gcal_log("Google Calendar: deleted event $eventId (HTTP $httpCode)");
        return true;
    } catch (\Throwable $e) {
        gcal_log("Google Calendar: delete event $eventId exception: " . $e->getMessage());
        return false;
    }
}
