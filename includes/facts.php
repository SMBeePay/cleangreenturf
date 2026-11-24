<?php
/**
 * Resolver for config/site-facts.php. See that file for the rules.
 *
 *   fact('timeline_typical')      -> escaped text ('' if it's a placeholder)
 *   fact_ready('warranty_text')   -> false while it's still a placeholder,
 *                                    so wrap whole sentences/blocks in it
 *   fact_raw('price_low_per_sqft')-> unescaped value (numbers, schema)
 *
 * Every lookup is recorded so bin/check-facts.php (and a debug HTML
 * comment) can list which unconfirmed facts each page actually uses.
 */
declare(strict_types=1);

function site_facts(): array {
    static $facts = null;
    if ($facts === null) {
        $facts = require __DIR__ . '/../config/site-facts.php';
    }
    return $facts;
}

function fact_entry(string $id): array {
    $facts = site_facts();
    if (!isset($facts[$id])) {
        throw new InvalidArgumentException("Unknown site fact: $id");
    }
    $GLOBALS['factsUsed'][$id] = true;
    return $facts[$id];
}

function fact_raw(string $id): mixed {
    return fact_entry($id)['value'];
}

function fact_ready(string $id): bool {
    $value = fact_entry($id)['value'];
    return $value !== null && $value !== '';
}

function fact(string $id): string {
    $value = fact_entry($id)['value'];
    return $value === null ? '' : htmlspecialchars((string)$value, ENT_QUOTES);
}

/** Lowercase-first variant for mid-sentence use of a fact written sentence-case. */
function fact_lc(string $id): string {
    $text = fact($id);
    return $text === '' ? '' : mb_strtolower(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

/** Installed price for an area, rounded to the nearest $100, formatted "$9,000". */
function fact_price_for(int $sqft, string $which): string {
    $rate = (float)fact_raw($which === 'high' ? 'price_high_per_sqft' : 'price_low_per_sqft');
    return '$' . number_format((int)(round($sqft * $rate / 100) * 100));
}

/** Unconfirmed facts used on the current page (for the debug comment). */
function facts_unconfirmed_used(): array {
    $out = [];
    foreach (array_keys($GLOBALS['factsUsed'] ?? []) as $id) {
        if (empty(site_facts()[$id]['confirmed'])) {
            $out[] = $id;
        }
    }
    return $out;
}
