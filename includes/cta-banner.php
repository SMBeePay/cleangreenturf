<?php
/**
 * Reusable bottom-of-page conversion banner. Included generically by
 * templates/page.php and templates/article.php (skipped on pages that are
 * already the conversion point — see the $skipCtaBanner list there).
 */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/page-cta.php';
$bannerCta = page_cta($route);
$tx = $businessInfo['regions']['tx'];
?>
<div class="cta-banner">
    <div class="cta-banner__text">
        <h2><?= htmlspecialchars($bannerCta['banner_title']) ?></h2>
        <p><?= htmlspecialchars($bannerCta['banner_text']) ?></p>
    </div>
    <div class="cta-banner__actions">
        <a href="tel:<?= htmlspecialchars($tx['phone_e164']) ?>" class="btn btn--ghost"><?= icon('phone') ?> <?= htmlspecialchars($tx['phone_display']) ?></a>
        <a href="<?= htmlspecialchars($bannerCta['href']) ?>" class="btn btn--primary"><?= ($route['cta'] ?? '') === 'installation' ? htmlspecialchars($bannerCta['label']) : 'Get My Free Quote' ?></a>
    </div>
</div>
