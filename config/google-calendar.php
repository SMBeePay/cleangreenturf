<?php
/**
 * Google Calendar sync config, read from environment variables — same
 * .env pattern as config/mail.php/config/zoho.php. Used to (1) create/
 * update/delete calendar events for booked turf-installation estimates on
 * the owner's dedicated Google Calendar, so bookings show up there
 * directly instead of only in /admin, and (2) check one or more calendars
 * for existing busy time so the booking widget never offers a slot that
 * conflicts with something already on the owner's calendar(s). Until real
 * credentials are set, both are simply skipped and logged — same
 * graceful-degradation approach as the SMTP/Zoho/Twilio integrations.
 *
 * See .env.example for the full one-time setup (Cloud Console project,
 * service account, sharing the target calendar(s) with it).
 */

require_once __DIR__ . '/mail.php';

$rawPrivateKey = env('GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY');
$calendarId = env('GOOGLE_CALENDAR_ID');
$busyIdsRaw = env('GOOGLE_BUSY_CALENDAR_IDS');
$impersonateEmail = env('GOOGLE_IMPERSONATE_EMAIL');

// Which calendars to check for existing conflicts before offering a slot.
// Defaults to just the booking calendar itself (GOOGLE_CALENDAR_ID) if
// GOOGLE_BUSY_CALENDAR_IDS isn't set, so at minimum, appointments booked
// directly onto that calendar (or manually added there) still block
// availability. Set GOOGLE_BUSY_CALENDAR_IDS to check others too (a
// personal calendar, a second business calendar, etc.) — comma-separated,
// each shared with the service account (see .env.example).
$busyCalendarIds = $busyIdsRaw !== null
    ? array_values(array_filter(array_map('trim', explode(',', $busyIdsRaw))))
    : array_values(array_filter([$calendarId]));

return [
    'client_email' => env('GOOGLE_SERVICE_ACCOUNT_EMAIL'),
    // The downloaded service-account JSON key's private_key field already
    // contains literal \n escape sequences (it's a JSON string) — paste it
    // into .env exactly as it appears in that field (one line, quoted).
    // .env only supports single-line values, so this converts those
    // literal \n sequences back into the real newlines PEM needs.
    'private_key' => $rawPrivateKey !== null ? str_replace('\\n', "\n", $rawPrivateKey) : null,
    'calendar_id' => $calendarId,
    'busy_calendar_ids' => $busyCalendarIds,
    // If set (via Google Workspace domain-wide delegation — see
    // .env.example), the service account acts AS this Workspace user
    // instead of as itself, so it gets that user's own access to every
    // calendar they own or have been shared, with no per-calendar
    // sharing needed. Preferred over calendar-by-calendar sharing, which
    // some Workspace "external sharing" org policies block or restrict
    // to read-only for a non-domain principal like a service account.
    'impersonate_email' => $impersonateEmail,
];
