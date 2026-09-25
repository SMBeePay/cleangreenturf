<?php
/**
 * Shared input-quality checks for the site's lead forms (quote form +
 * scheduler booking) — both collect a free-text street address and both
 * need the same "is this actually an address, not spam" bar, plus the
 * same human-readable error copy so a rejected submission tells the
 * visitor why instead of silently bouncing them back to an empty form.
 *
 * Prompted by a real lead that got through with "n/a" as the address —
 * see docs/audit-findings.md "Address validation added to quote form and
 * scheduler."
 */
declare(strict_types=1);

/**
 * Rejects obvious non-addresses ("n/a", "test", "asdf", a bare zip)
 * without being so strict it blocks real ones. Requires at least one
 * letter and a minimum length, and — since almost every real service
 * address includes a house/unit number — a digit, unless the text is
 * long enough to plausibly be a fully spelled-out address without one.
 */
function looks_like_real_address(string $address): bool {
    $normalized = strtolower(trim($address));
    $stripped = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]/', '', $normalized)) ?? '');

    $junkPhrases = [
        'na', 'n a', 'none', 'no', 'nil', 'nope', 'test', 'testing',
        'asdf', 'xx', 'xxx', 'tbd', 'unknown', 'address', 'my address',
        'idk', 'notapplicable', 'not applicable', 'no address', 'null',
        'undefined', '123', '123 test',
    ];
    if ($stripped === '' || in_array($stripped, $junkPhrases, true)) {
        return false;
    }
    if (preg_match('/^(.)\1*$/', $stripped)) {
        return false; // a single repeated character, e.g. "aaaa" or "1111"
    }
    if (!preg_match('/[a-zA-Z]/', $address)) {
        return false;
    }

    $length = mb_strlen($stripped);
    if ($length < 6) {
        return false;
    }

    $hasDigit = (bool)preg_match('/\d/', $address);
    if (!$hasDigit && $length < 15) {
        return false;
    }

    return true;
}

/**
 * Same idea as looks_like_real_address() but for the separate City field —
 * added after two same-day leads came through with only a street address
 * ("1333 Windflower Drive") and no city, which the business needs to know
 * since it serves many different DFW-area cities (plus a few in CA). A
 * city name is letters/spaces/hyphens/apostrophes, never digits.
 */
function looks_like_real_city(string $city): bool {
    $normalized = strtolower(trim($city));
    $stripped = trim(preg_replace('/\s+/', ' ', preg_replace("/[^a-z' -]/", '', $normalized)) ?? '');

    $junkPhrases = [
        'na', 'n a', 'none', 'no', 'nil', 'nope', 'test', 'testing',
        'asdf', 'xx', 'xxx', 'tbd', 'unknown', 'city', 'my city', 'idk',
        'notapplicable', 'not applicable', 'null', 'undefined',
    ];
    if ($stripped === '' || in_array($stripped, $junkPhrases, true)) {
        return false;
    }
    if (preg_match('/^(.)\1*$/', $stripped)) {
        return false; // a single repeated character
    }
    if (!preg_match('/[a-zA-Z]/', $city)) {
        return false;
    }
    if (mb_strlen($stripped) < 2) {
        return false;
    }
    if (preg_match('/\d/', $city)) {
        return false; // a city name has no digits — a street address doesn't belong here
    }

    return true;
}

/** Human-readable copy for error codes forms/handle-quote.php and scheduler/book.php can return. */
function quote_form_error_message(string $code): string {
    $messages = [
        'missing_fields' => 'Please fill in all the required fields.',
        'invalid_email' => 'Please enter a valid email address.',
        'invalid_address' => 'Please enter your real street address — we need it to schedule on-site service.',
        'invalid_city' => 'Please enter the city your property is in.',
        'send_failed' => 'Something went wrong sending your request. Please call us instead.',
    ];
    return $messages[$code] ?? 'Something went wrong. Please try again or call us.';
}
