<?php
/** Article/blog template — pulls the H1 into a proper article header band. */
ob_start();
require __DIR__ . '/../content/pages/' . $route['slug'] . '.php';
$content = ob_get_clean();

$heroH1 = null;
if (preg_match('/^\s*<h1[^>]*>(.*?)<\/h1>/is', $content, $m)) {
    $heroH1 = $m[1];
    $content = substr($content, strlen($m[0]));
}
?>
<div class="container">
    <div class="article-hero">
        <?php if (!empty($route['date_published'])): ?>
        <p class="article-hero__meta">Published <time datetime="<?= htmlspecialchars($route['date_published']) ?>">
            <?= htmlspecialchars(date('F j, Y', strtotime($route['date_published']))) ?>
        </time></p>
        <?php endif; ?>
        <?php if ($heroH1 !== null): ?><h1><?= $heroH1 ?></h1><?php endif; ?>
    </div>
</div>
<article class="prose">
<?= $content ?>
<?php require __DIR__ . '/../includes/cta-banner.php'; ?>
</article>
