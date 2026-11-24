<?php
/**
 * Front controller. Every request is routed here by .htaccess (except real
 * files/directories and /forms/*.php). Looks up the request path in
 * config/routes.php and renders the matching template inside the shared
 * header/footer shell. Unknown paths get a real HTTP 404.
 */
declare(strict_types=1);

$businessInfo = require __DIR__ . '/config/business-info.php';
$routes = require __DIR__ . '/config/routes.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
// One URL per page: /about/ 301s to /about (no-trailing-slash is the
// canonical convention sitewide), keeping any query string.
if ($requestPath !== '/' && str_ends_with($requestPath, '/')) {
    $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    header('Location: ' . $businessInfo['domain'] . rtrim($requestPath, '/') . ($query ? '?' . $query : ''), true, 301);
    exit;
}
$currentPath = $requestPath;

$redirects = require __DIR__ . '/config/redirects.php';
if (isset($redirects[$currentPath])) {
    header('Location: ' . $businessInfo['domain'] . $redirects[$currentPath], true, 301);
    exit;
}

$route = $routes[$currentPath] ?? null;

if ($route === null) {
    http_response_code(404);
    $route = [
        'slug' => null,
        'template' => '404',
        'title' => 'Page Not Found | ' . $businessInfo['name'],
        'description' => 'The page you requested could not be found.',
        'robots' => 'noindex, follow',
        'schema_types' => [],
    ];
}

// Render the page body first so the <head> can use what it produced: the
// hero image (preload), the H1 (breadcrumb schema) and any FAQ block
// (FAQPage schema). Templates set $heroImg / $heroH1; includes/faq.php
// fills $GLOBALS['pageFaq'].
require_once __DIR__ . '/includes/facts.php';
$heroImg = null;
$heroH1 = null;
ob_start();
require __DIR__ . '/templates/' . $route['template'] . '.php';
$mainHtml = ob_get_clean();
?><!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/includes/seo-head.php'; ?>
</head>
<body>
<?php require __DIR__ . '/includes/header.php'; ?>
<main id="main-content">
<?= $mainHtml ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
<?php require __DIR__ . '/includes/mobile-cta-bar.php'; ?>
<script src="/assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>
</body>
</html>
