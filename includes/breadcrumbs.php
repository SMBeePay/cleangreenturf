<?php
/**
 * Breadcrumb trail for a route: [[label, path], ...], starting at Home.
 * Routes can declare 'breadcrumb' => [['Turf Installation', '/turf-installation'], ...]
 * (everything after Home, ending with the current page). Otherwise the
 * trail is Home > this page, labelled with the page's H1.
 */
declare(strict_types=1);

function page_breadcrumbs(array $route, string $path, string $heading): array {
    $trail = [['Home', '/']];
    foreach ($route['breadcrumb'] ?? [[$heading, $path]] as $crumb) {
        $trail[] = $crumb;
    }
    return $trail;
}

/** Visible breadcrumb nav, only for routes that declare a trail. */
function render_breadcrumbs(array $route, string $path, string $heading): void {
    if (empty($route['breadcrumb'])) {
        return;
    }
    $trail = page_breadcrumbs($route, $path, $heading);
    $last = count($trail) - 1;
    echo '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol>';
    foreach ($trail as $i => [$label, $href]) {
        echo $i === $last
            ? '<li aria-current="page">' . htmlspecialchars($label) . '</li>'
            : '<li><a href="' . htmlspecialchars($href) . '">' . htmlspecialchars($label) . '</a></li>';
    }
    echo '</ol></nav>';
}
