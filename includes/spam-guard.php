<?php
/**
 * Extra spam defenses for the quote forms, layered on top of the existing
 * honeypot field. Added after a run of vendor solicitations ("this isn't a
 * service request... I built a preview of a new site for you") came
 * through the quote form — these get past a honeypot because they're
 * filled field-by-field by browser automation / AI, not dumb POST bots.
 *
 * Two layers, with deliberately different consequences:
 *
 *  1. Timing token (hard block). The form carries a signed timestamp of
 *     when it was rendered. A submission that arrives faster than any
 *     human could fill the form, or with a forged/tampered token, is
 *     dropped (logged to data/spam-blocked.log, visitor sees the normal
 *     success page so the bot learns nothing). A MISSING token is not
 *     blocked — Hostinger can serve a cached copy of a page from before
 *     this field existed, and a real lead must never be lost to that.
 *
 *  2. Content signals (soft flag). Links in the notes or sales-pitch
 *     phrasing. These are NOT dropped — a real customer could
 *     conceivably paste a link. Instead the owner still gets the email,
 *     with a "[Possible Spam]" subject prefix (easy to filter in Gmail),
 *     but no Zoho Deal is created and no auto-reply is sent to the
 *     submitter's address.
 */
declare(strict_types=1);

const SPAM_MIN_FILL_SECONDS = 4;

/** Per-install HMAC key, auto-generated into data/ (gitignored, web-blocked). */
function spam_guard_key(): string {
    $path = __DIR__ . '/../data/form-secret.key';
    $key = @file_get_contents($path);
    if (is_string($key) && strlen($key) >= 32) {
        return $key;
    }
    $key = bin2hex(random_bytes(32));
    @file_put_contents($path, $key, LOCK_EX);
    return $key;
}

/** Hidden input to drop into each quote form. */
function spam_guard_field(): string {
    $ts = (string)time();
    $sig = hash_hmac('sha256', $ts, spam_guard_key());
    return '<input type="hidden" name="form_ts" value="' . htmlspecialchars("$ts.$sig", ENT_QUOTES) . '">';
}

/**
 * 'ok' | 'missing' | 'invalid' | 'too_fast'.
 * Only 'invalid' and 'too_fast' should block.
 */
function spam_guard_timing_status(string $token): string {
    if ($token === '') {
        return 'missing';
    }
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return 'invalid';
    }
    [$ts, $sig] = $parts;
    if (!hash_equals(hash_hmac('sha256', $ts, spam_guard_key()), $sig)) {
        return 'invalid';
    }
    if (time() - (int)$ts < SPAM_MIN_FILL_SECONDS) {
        return 'too_fast';
    }
    return 'ok';
}

/** Reasons a submission looks like a sales pitch rather than a customer; empty = looks fine. */
function spam_guard_content_signals(string $notes, string $name, string $email): array {
    $signals = [];
    $text = strtolower($notes . ' ' . $name);

    if (preg_match('~https?://|www\.|\b[a-z0-9-]+\.(com|net|io|co|site|app|online|xyz)/~i', $notes)) {
        $signals[] = 'link in notes';
    }

    $phrases = [
        "isn't a service request", 'is not a service request', 'not a service request',
        'not looking for', 'new site for', 'new website', 'website design', 'web design',
        'redesign', 'preview of', 'mockup', 'put this live', 'seo', 'search ranking',
        'first page of google', 'page 1 of google', 'google ranking', 'more leads',
        'lead generation', 'marketing agency', 'digital marketing', 'backlink',
        'guest post', 'business loan', 'funding', 'merchant cash', 'virtual assistant',
        'outsourc', 'partnership opportunity', 'collaborat', 'unsubscribe', 'opt out',
    ];
    foreach ($phrases as $phrase) {
        if (str_contains($text, $phrase)) {
            $signals[] = "phrase: \"$phrase\"";
        }
    }

    if (preg_match('/\b(sites?|seo|media|marketing|digital|agency|web|solutions)\b/i', $name)) {
        $signals[] = 'business-sounding name';
    }

    return $signals;
}

function spam_guard_log(string $file, string $reason, array $post): void {
    $entry = '[' . date('c') . "] $reason | IP " . ($_SERVER['REMOTE_ADDR'] ?? '?') . "\n";
    foreach (['name', 'email', 'phone', 'address', 'city', 'notes'] as $field) {
        $entry .= "  $field: " . str_replace(["\r", "\n"], ' ', (string)($post[$field] ?? '')) . "\n";
    }
    @file_put_contents(__DIR__ . '/../data/' . $file, $entry . "\n", FILE_APPEND | LOCK_EX);
}
