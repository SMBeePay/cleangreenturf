<?php
/**
 * Reusable bottom-of-page conversion banner. Included generically by
 * templates/page.php and templates/article.php (skipped on pages that are
 * already the conversion point — see the $skipCtaBanner list there).
 */
require_once __DIR__ . '/icons.php';
$tx = $businessInfo['regions']['tx'];
?>
<div class="cta-banner">
    <div class="cta-banner__text">
        <h2>Ready for Turf That Looks Brand New?</h2>
        <p>Free, no-obligation quotes — pet-safe, eco-friendly, and built for your climate.</p>
    </div>
    <div class="cta-banner__actions">
        <a href="tel:<?= htmlspecialchars($tx['phone_e164']) ?>" class="btn btn--ghost"><?= icon('phone') ?> <?= htmlspecialchars($tx['phone_display']) ?></a>
        <a href="/#free-quote" class="btn btn--primary">Get My Free Quote</a>
    </div>
</div>
