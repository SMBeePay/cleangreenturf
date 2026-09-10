<?php
// Local-only dev router to emulate the .htaccess rewrite rules for PHP's
// built-in server (php -S). Not used in production (Apache/.htaccess is).
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(config|content|includes|templates|bin|docs|vendor)(/|$)#', $path)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}
$fullPath = __DIR__ . $path;
if ($path !== '/' && file_exists($fullPath) && !is_dir($fullPath)) {
    return false; // serve the requested resource as-is
}
require __DIR__ . '/index.php';
