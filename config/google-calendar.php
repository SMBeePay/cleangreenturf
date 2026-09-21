<?php
/**
 * Google Calendar sync config, read from environment variables — same
 * .env pattern as config/mail.php/config/zoho.php. Used to create/update/
 * delete calendar events for booked turf-installation estimates on the
 * owner's dedicated Google Calendar, so bookings show up there directly
 * instead of only in /admin. Until real credentials are set, the calendar
 * push is simply skipped and logged — same graceful-degradation approach
 * as the SMTP/Zoho/Twilio integrations.
 *
 * See .env.example for the full one-time setup (Cloud Console project,
 * service account, sharing the target calendar with it).
 */

require_once __DIR__ . '/mail.php';

$rawPrivateKey = env('GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY');

return [
    'client_email' => env('GOOGLE_SERVICE_ACCOUNT_EMAIL'),
    // The downloaded service-account JSON key's private_key field already
    // contains literal \n escape sequences (it's a JSON string) — paste it
    // into .env exactly as it appears in that field (one line, quoted).
    // .env only supports single-line values, so this converts those
    // literal \n sequences back into the real newlines PEM needs.
    'private_key' => $rawPrivateKey !== null ? str_replace('\\n', "\n", $rawPrivateKey) : null,
    'calendar_id' => env('GOOGLE_CALENDAR_ID'),
];
