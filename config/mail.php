<?php
/**
 * SMTP configuration, read from environment variables — never hard-coded,
 * never committed. Two ways to supply them on Hostinger:
 *
 *   1. hPanel > Advanced > PHP Configuration (or similar) — real server
 *      environment variables. Preferred where available.
 *   2. A `.env` file at the repo root (same directory as index.php),
 *      KEY=VALUE per line, `#` comments allowed. This file is in
 *      .gitignore — it must NEVER be committed. Copy .env.example to
 *      .env on the server and fill in real values.
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
    'from_email' => env('SMTP_FROM_EMAIL', 'no-reply@cleangreenturf.com'),
    'from_name' => env('SMTP_FROM_NAME', 'Clean Green Turf Website'),
];
