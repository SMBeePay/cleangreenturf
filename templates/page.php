<?php
/**
 * Generic page template — used for every migrated static/location page.
 * Generically upgrades the visual presentation without touching any
 * migrated copy: pulls the leading <img> + <h1> (if present) out of the
 * content and renders them as a proper hero band, then wraps the rest in
 * a styled `.prose` container. Falls back cleanly if a page has no leading
 * image (e.g. /about) or no leading image+h1 pattern at all.
 */
require_once __DIR__ . '/../includes/icons.php';

ob_start();
require __DIR__ . '/../content/pages/' . $route['slug'] . '.php';
$content = ob_get_clean();

$heroImg = null;
$heroH1 = null;
if (preg_match('/^\s*(?:(<img\b[^>]*>)\s*)?<h1[^>]*>(.*?)<\/h1>/is', $content, $m)) {
    $heroImg = $m[1] ?: null;
    $heroH1 = $m[2];
    $content = substr($content, strlen($m[0]));
}
?>
<?php if ($heroH1 !== null): ?>
<section class="page-hero<?= $heroImg ? '' : ' page-hero--plain' ?>">
    <?php if ($heroImg): ?>
    <div class="page-hero__media"><?= $heroImg ?></div>
    <?php endif; ?>
    <div class="page-hero__text">
        <span class="page-hero__eyebrow"><?= icon('leaf') ?> Clean Green Turf</span>
        <h1><?= $heroH1 ?></h1>
        <div class="page-hero__ctas">
            <a href="/#free-quote" class="btn btn--primary">Get a Free Quote</a>
            <a href="tel:<?= htmlspecialchars($businessInfo['regions']['tx']['phone_e164']) ?>" class="btn btn--ghost">
                <?= icon('phone') ?> <?= htmlspecialchars($businessInfo['regions']['tx']['phone_display']) ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="prose">
<?= $content ?>
</div>

<?php
$skipCtaBanner = ['contact', 'dfw-turf-cleaning-request-success', 'dfw-turf-cleaning-request-ga'];
if (!in_array($route['slug'], $skipCtaBanner, true)):
    require __DIR__ . '/../includes/cta-banner.php';
endif;
