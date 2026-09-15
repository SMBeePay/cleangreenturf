<?php
/**
 * Twilio SMS config, read from environment variables — same .env pattern as
 * config/mail.php (which this reuses for load_dotenv_once()/env(), so .env
 * only needs to be parsed once per request). Used only for the day-before
 * appointment-reminder text (bin/send-reminders.php). Until real Twilio
 * credentials are set, reminders are simply skipped and logged — same
 * graceful-degradation approach as the SMTP fallback.
 *
 * Setup (get these from https://console.twilio.com):
 *   1. Sign up for Twilio (or use an existing account) and buy/verify a
 *      phone number capable of sending SMS.
 *   2. Copy the Account SID and Auth Token from the Twilio Console dashboard.
 *   3. Add them to .env (see .env.example) along with the Twilio number to
 *      send from.
 */

require_once __DIR__ . '/mail.php';

return [
    'account_sid' => env('TWILIO_ACCOUNT_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),
    'from_number' => env('TWILIO_FROM_NUMBER'),
];
