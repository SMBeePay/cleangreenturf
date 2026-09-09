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
// normalize: strip trailing slash (except root) so /about/ and /about match
if ($requestPath !== '/' && str_ends_with($requestPath, '/')) {
    $requestPath = rtrim($requestPath, '/');
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
?><!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/includes/seo-head.php'; ?>
</head>
<body>
<?php require __DIR__ . '/includes/header.php'; ?>
<main id="main-content">
<?php require __DIR__ . '/templates/' . $route['template'] . '.php'; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
<?php require __DIR__ . '/includes/mobile-cta-bar.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
