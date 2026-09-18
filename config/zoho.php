<?php
/**
 * Zoho CRM API configuration, read from environment variables — never
 * hard-coded, never committed. Same .env / real-server-env-var pattern as
 * config/mail.php and config/sms.php; see .env.example for setup steps.
 *
 * Zoho CRM already has three Deals pipelines set up (Turf Installation,
 * Turf Cleaning, Turf Repair — created by the owner, not by this code) —
 * see docs/audit-findings.md "Zoho CRM integration" for the exact
 * Pipeline/Stage values includes/zoho-crm.php submits against.
 */

if (!function_exists('load_dotenv_once')) {
    function load_dotenv_once(string $path): void {
        static $loaded = false;
        if ($loaded || !is_readable($path)) {
            return;
        }
        $loaded = true;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (strlen($value) >= 2 && $value[0] === $value[-1] && in_array($value[0], ['"', "'"], true)) {
                $value = substr($value, 1, -1);
            }
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
        }
    }
}

load_dotenv_once(__DIR__ . '/../.env');

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string {
        $value = getenv($key);
        return $value === false || $value === '' ? $default : $value;
    }
}

return [
    'client_id' => env('ZOHO_CLIENT_ID'),
    'client_secret' => env('ZOHO_CLIENT_SECRET'),
    'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
    // US data center defaults — see .env.example if the org is on a
    // different Zoho data center (.eu, .in, .com.au, etc).
    'accounts_domain' => env('ZOHO_ACCOUNTS_DOMAIN', 'https://accounts.zoho.com'),
    'api_domain' => env('ZOHO_API_DOMAIN', 'https://www.zohoapis.com'),
];
