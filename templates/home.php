<?php
/**
 * Homepage template. Extracts the leading hero block (image, H1, tagline,
 * rating line, CTA) from content/pages/__home__.php to render a proper
 * hero band, then continues with the rest of the page content unchanged.
 * The quote form (rendered further down __home__.php, same copy/fields as
 * always) is also hoisted up into the hero as a lead-capture card next to
 * the headline, so it's visible without scrolling — it isn't duplicated,
 * just moved earlier on the page.
 */
require_once __DIR__ . '/../includes/icons.php';

ob_start();
require __DIR__ . '/../content/pages/' . $route['slug'] . '.php';
$content = ob_get_clean();

$heroImg = null;
$heroH1 = null;
$heroTagline = null;
if (preg_match(
    '/^\s*(<img\b[^>]*>)\s*<h1[^>]*>(.*?)<\/h1>\s*(?:<h4[^>]*>(.*?)<\/h4>\s*)?<p>.*?<\/p>\s*<p>.*?<\/p>\s*/is',
    $content,
    $m
)) {
    $heroImg = $m[1];
    $heroH1 = $m[2];
    $heroTagline = $m[3] ?? null;
    $content = substr($content, strlen($m[0]));
}

$heroQuoteForm = null;
if (preg_match('/<!-- quote-form:start -->(.*?)<!-- quote-form:end -->/s', $content, $qm)) {
    $heroQuoteForm = $qm[1];
    $content = str_replace($qm[0], '', $content);
}
?>
<?php if ($heroH1 !== null): ?>
<section class="page-hero page-hero--home">
    <div class="page-hero__media"><?= $heroImg ?></div>
    <div class="page-hero__inner">
        <div class="page-hero__text">
            <span class="page-hero__eyebrow"><?= icon('star') ?> 5.0 Rated &middot; 100+ 5-Star Reviews</span>
            <h1><?= $heroH1 ?></h1>
            <?php if ($heroTagline): ?><p class="page-hero__sub"><?= $heroTagline ?></p><?php endif; ?>
            <div class="page-hero__ctas">
                <a href="/#free-quote" class="btn btn--primary">Get My Free Quote</a>
                <a href="tel:<?= htmlspecialchars($businessInfo['regions']['tx']['phone_e164']) ?>" class="btn btn--ghost">
                    <?= icon('phone') ?> <?= htmlspecialchars($businessInfo['regions']['tx']['phone_display']) ?>
                </a>
            </div>
        </div>
        <?php if ($heroQuoteForm): ?>
        <div class="page-hero__form"><?= $heroQuoteForm ?></div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<div class="prose">
<?= $content ?>
</div>
