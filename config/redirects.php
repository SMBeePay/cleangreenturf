<?php
/**
 * 301 redirect map. See docs/audit-findings.md finding #10 — these are
 * URLs that were indexed by search engines from an earlier site iteration,
 * already 404 on the live production site today (not caused by this
 * migration), and aren't linked anywhere on the current site. Redirecting
 * them recovers any residual link equity/impressions.
 *
 * '/artificial-turf-repair' is intentionally NOT redirected yet — there is
 * no real Repair page to send it to (that's Phase 2, requirement #24).
 * Redirecting it to the homepage now would violate "never redirect to the
 * homepage merely because an equivalent page wasn't recreated" (#3). Add it
 * here once a real /artificial-turf-repair-equivalent page exists.
 */
return [
    '/tx-turf-cleaning-service-areas' => '/texas-service-areas',
    '/service-area-dallas' => '/dallas-tx-turf-cleaning',
    '/artificial-turf-cleaning' => '/',
];
