<?php
/**
 * City-page duplicate check (install SEO plan, Phase 3 publishing standard:
 * no two city pages above 60% text overlap).
 *
 *   php bin/check-city-overlap.php
 *
 * Renders each /turf-installation/{city} page through index.php, takes the
 * visible text of <main>, and splits it into 5-word shingles. Overlap for
 * a pair = the share of page A's shingles that also appear on page B,
 * taken in whichever direction is higher (containment, stricter than
 * Jaccard). Exits 1 if any pair is above the limit.
 */
declare(strict_types=1);

const OVERLAP_LIMIT = 0.60;
$root = realpath(__DIR__ . '/..');
$cities = array_keys(require $root . '/config/install-cities.php');

function render_path(string $root, string $path): string {
    $code = '$_SERVER["REQUEST_URI"]=' . var_export($path, true) . ';$_SERVER["REQUEST_METHOD"]="GET";chdir(' . var_export($root, true) . ');require "index.php";';
    return (string)shell_exec('php -r ' . escapeshellarg($code) . ' 2>/dev/null');
}

function shingles(string $html): array {
    $start = strpos($html, '<main');
    $end = strpos($html, '</main>');
    $main = $start !== false && $end !== false ? substr($html, $start, $end - $start) : $html;
    $main = preg_replace('#<(script|style|form)\b.*?</\1>#is', ' ', $main);
    $text = strtolower(html_entity_decode(strip_tags($main), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $words = preg_split('/[^a-z0-9$]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $set = [];
    for ($i = 0; $i + 5 <= count($words); $i++) {
        $set[implode(' ', array_slice($words, $i, 5))] = true;
    }
    return $set;
}

$sets = [];
foreach ($cities as $slug) {
    $sets[$slug] = shingles(render_path($root, '/turf-installation/' . $slug));
    if (count($sets[$slug]) < 50) {
        fwrite(STDERR, "Could not render /turf-installation/$slug\n");
        exit(2);
    }
}

$worst = 0.0;
$rows = [];
for ($i = 0; $i < count($cities); $i++) {
    for ($j = $i + 1; $j < count($cities); $j++) {
        [$a, $b] = [$sets[$cities[$i]], $sets[$cities[$j]]];
        $shared = count(array_intersect_key($a, $b));
        $overlap = max($shared / count($a), $shared / count($b));
        $worst = max($worst, $overlap);
        $rows[] = [$overlap, $cities[$i] . ' vs ' . $cities[$j]];
    }
}
rsort($rows);
foreach ($rows as [$overlap, $pair]) {
    printf("%5.1f%%  %s%s\n", $overlap * 100, $pair, $overlap > OVERLAP_LIMIT ? '  <-- OVER LIMIT' : '');
}
printf("\nHighest overlap: %.1f%% (limit %d%%). %s\n", $worst * 100, OVERLAP_LIMIT * 100, $worst > OVERLAP_LIMIT ? 'FAIL' : 'PASS');
exit($worst > OVERLAP_LIMIT ? 1 : 0);
