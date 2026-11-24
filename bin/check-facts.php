<?php
/**
 * Lists every unconfirmed fact (config/site-facts.php) and the pages that
 * use it. This site has no build step, so this is the "build-time warning"
 * from the install SEO plan: run it before promoting install pages.
 *
 *   php bin/check-facts.php
 *
 * Never fails: unconfirmed facts with a default still render that default;
 * placeholders (no value yet) hide whatever block uses them.
 */
declare(strict_types=1);

$facts = require __DIR__ . '/../config/site-facts.php';
$files = array_merge(glob(__DIR__ . '/../content/pages/*.php'), glob(__DIR__ . '/../includes/*.php'));
$usage = [];
foreach ($files as $file) {
    // Ignore comments, so docblock examples don't count as usage.
    $code = '';
    foreach (token_get_all((string)file_get_contents($file)) as $tok) {
        $code .= is_array($tok) ? (in_array($tok[0], [T_COMMENT, T_DOC_COMMENT], true) ? '' : $tok[1]) : $tok;
    }
    preg_match_all("/fact(?:_raw|_ready|_lc)?\\('([a-z_]+)'\\)/", $code, $m);
    foreach (array_unique($m[1]) as $id) {
        $usage[$id][] = basename(dirname($file)) . '/' . basename($file);
    }
}

$warnings = 0;
foreach ($facts as $id => $fact) {
    if (!empty($fact['confirmed'])) {
        continue;
    }
    $warnings++;
    $where = $usage[$id] ?? [];
    $state = $fact['value'] === null ? 'PLACEHOLDER (blocks using it are hidden)' : 'default shown: "' . $fact['value'] . '"';
    echo "WARNING unconfirmed fact '$id' - $state\n";
    if (isset($fact['needed'])) {
        echo "        needed from Andrew: {$fact['needed']}\n";
    }
    echo '        used in: ' . ($where ? implode(', ', $where) : '(not used on any page yet)') . "\n";
}
foreach (array_diff(array_keys($usage), array_keys($facts)) as $unknown) {
    echo "ERROR page uses unknown fact '$unknown' in " . implode(', ', $usage[$unknown]) . "\n";
}
echo $warnings ? "\n$warnings unconfirmed fact(s).\n" : "All facts confirmed.\n";
