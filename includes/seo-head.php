<?php
/**
 * Per-page <head> SEO output: title, meta description, canonical, robots,
 * Open Graph, favicon, tracking. $route and $businessInfo must be set by
 * index.php before this is included.
 */
$canonicalUrl = $businessInfo['domain'] . ($currentPath === '/' ? '/' : $currentPath);
$robots = $route['robots'] ?? 'index, follow';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($route['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($route['description'] ?? '') ?>">
<meta name="robots" content="<?= htmlspecialchars($robots) ?>">
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= htmlspecialchars($businessInfo['name']) ?>">
<meta property="og:title" content="<?= htmlspecialchars($route['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($route['description'] ?? '') ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">

<link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon-16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="/assets/images/favicon-192.png">
<link rel="apple-touch-icon" href="/assets/images/favicon-180.png">

<link rel="stylesheet" href="/assets/css/style.css">

<?php require __DIR__ . '/schema.php'; ?>
<?php require __DIR__ . '/tracking.php'; ?>
