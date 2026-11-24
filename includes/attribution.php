<?php
/**
 * Lead attribution (T-01). assets/js/main.js keeps a 90-day first-touch
 * cookie and a last-touch cookie (UTMs, external referrer, landing page)
 * and copies them into hidden inputs on every lead form at submit time.
 * This file reads those inputs back on the server, so every lead email and
 * Zoho Deal says where the visitor actually came from (e.g. ChatGPT, which
 * appends utm_source=chatgpt.com to links it cites).
 *
 * Everything here is visitor-supplied, so it is length-capped, stripped of
 * newlines, and only ever used as plain text.
 */
declare(strict_types=1);

const ATTRIBUTION_FIELDS = [
    'utm_source', 'utm_medium', 'utm_campaign', 'referrer', 'landing_page',
    'first_touch_source', 'first_touch_landing_page', 'ga_client_id',
];

function attribution_from_post(): array {
    $out = [];
    foreach (ATTRIBUTION_FIELDS as $field) {
        $value = trim(preg_replace('/[\r\n\t]+/', ' ', (string)($_POST[$field] ?? '')));
        $out[$field] = mb_substr($value, 0, 300);
    }
    // Server clock, not the browser's: the hidden field is display-only.
    $out['submitted_at'] = date('c');
    return $out;
}

/** "chatgpt.com / ai-assistant", or the referrer host, or "(direct)". */
function attribution_source_label(array $a): string {
    if ($a['utm_source'] !== '') {
        return $a['utm_source'] . ($a['utm_medium'] !== '' ? ' / ' . $a['utm_medium'] : '');
    }
    if ($a['referrer'] !== '') {
        $host = parse_url($a['referrer'], PHP_URL_HOST);
        return ($host ?: $a['referrer']) . ' / referral';
    }
    return '(direct or unknown)';
}

/** Plain-text block appended to owner emails and Zoho Descriptions. */
function attribution_text_block(array $a): string {
    $lines = [
        'Source this visit: ' . attribution_source_label($a),
        'Campaign: ' . ($a['utm_campaign'] !== '' ? $a['utm_campaign'] : '-'),
        'Referrer: ' . ($a['referrer'] !== '' ? $a['referrer'] : '-'),
        'Landing page this visit: ' . ($a['landing_page'] !== '' ? $a['landing_page'] : '-'),
        'First touch source: ' . ($a['first_touch_source'] !== '' ? $a['first_touch_source'] : '-'),
        'First touch landing page: ' . ($a['first_touch_landing_page'] !== '' ? $a['first_touch_landing_page'] : '-'),
        'GA client ID: ' . ($a['ga_client_id'] !== '' ? $a['ga_client_id'] : '-'),
        'Submitted: ' . $a['submitted_at'],
    ];
    return "--- Lead source ---\n" . implode("\n", $lines) . "\n";
}

/**
 * Dedicated Zoho Deal fields. These four don't exist in Zoho yet (Andrew is
 * adding them), and a create call with an unknown field name is a risk we
 * don't need, so they're only sent once ZOHO_ATTRIBUTION_FIELDS=1 is set in
 * .env. The same data always goes into the Deal Description regardless.
 */
function attribution_zoho_fields(array $a, array $zohoConfig): array {
    if (empty($zohoConfig['attribution_fields'])) {
        return [];
    }
    return array_filter([
        'Lead_Source_Detail' => attribution_source_label($a),
        'First_Touch_Source' => $a['first_touch_source'],
        'Landing_Page' => $a['landing_page'],
        'GA_Client_ID' => $a['ga_client_id'],
    ], fn($v) => $v !== '');
}

/**
 * Zoho Lead_Source picklist value. Only "Website" and "Google Ads" are
 * mapped: the picklist has no AI-assistant option, and guessing would put
 * leads in the wrong bucket. The exact source goes in Lead_Source_Detail.
 */
function attribution_lead_source(array $a, bool $refererIsAdsLandingPage): string {
    $isAds = $refererIsAdsLandingPage
        || (stripos($a['utm_source'], 'google') !== false && in_array(strtolower($a['utm_medium']), ['cpc', 'ppc', 'paid'], true));
    return $isAds ? ZOHO_LEAD_SOURCE_GOOGLE_ADS : ZOHO_LEAD_SOURCE_WEBSITE;
}
