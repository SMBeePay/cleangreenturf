<?php
/**
 * SMTP configuration, read from environment variables — never hard-coded,
 * never committed. andrew@cleangreenturf.com is a Google Workspace
 * address, so this sends via Gmail's SMTP relay (smtp.gmail.com) using
 * that same mailbox with an app password — see .env.example for the
 * two-minute setup (turn on 2-Step Verification, generate an app password
 * at https://myaccount.google.com/apppasswords).
 *
 * Two ways to supply the values on Hostinger:
 *   1. hPanel > Advanced > PHP Configuration (or similar) — real server
 *      environment variables. Preferred where available.
 *   2. A `.env` file at the repo root (same directory as index.php),
 *      KEY=VALUE per line, `#` comments allowed. This file is in
 *      .gitignore — it must NEVER be committed. Copy .env.example to
 *      .env on the server and fill in real values. If deploying via
 *      Hostinger's Git integration, this file has to be placed on the
 *      server directly (e.g. via File Manager) since it isn't in the
 *      repo — confirm a redeploy doesn't wipe it before relying on that.
 *
 * If none of SMTP_HOST/SMTP_USERNAME/SMTP_PASSWORD are set, mail sending
 * falls back to PHP's mail() (see forms/handle-quote.php) — functional but
 * not production-reliable for deliverability.
 */

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
        // strip matching surrounding quotes
        if (strlen($value) >= 2 && $value[0] === $value[-1] && in_array($value[0], ['"', "'"], true)) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv("$key=$value");
        }
    }
}

load_dotenv_once(__DIR__ . '/../.env');

function env(string $key, ?string $default = null): ?string {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

return [
    'host' => env('SMTP_HOST'),
    'port' => (int)env('SMTP_PORT', '587'),
    'username' => env('SMTP_USERNAME'),
    'password' => env('SMTP_PASSWORD'),
    'encryption' => env('SMTP_ENCRYPTION', 'tls'), // 'tls', 'ssl', or '' for none
    // Gmail's SMTP relay requires From to match the authenticated account
    // (or a verified alias) — default it to the same address as the
    // username rather than an unrelated no-reply@ that Gmail would reject.
    'from_email' => env('SMTP_FROM_EMAIL', env('SMTP_USERNAME', 'andrew@cleangreenturf.com')),
    'from_name' => env('SMTP_FROM_NAME', 'Clean Green Turf Website'),
];
