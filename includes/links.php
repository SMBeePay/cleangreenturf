<?php
/**
 * Links to pages that may not exist yet. The install plan ships in phases
 * (cost page, city pages, guides come later), and content written now
 * links ahead to them. Until a route exists in config/routes.php these
 * render as plain text (or the caller hides the sentence via
 * route_exists()), so no phase ever ships a dead internal link. They turn
 * into real links automatically when the page's route is added.
 */
declare(strict_types=1);

function route_exists(string $path): bool {
    static $routes = null;
    $routes ??= require __DIR__ . '/../config/routes.php';
    return isset($routes[$path]);
}

/** <a> if the route exists, otherwise the escaped text alone. */
function maybe_link(string $path, string $text): string {
    $safe = htmlspecialchars($text, ENT_QUOTES);
    return route_exists($path) ? '<a href="' . htmlspecialchars($path) . '">' . $safe . '</a>' : $safe;
}
