<?php
/** Fixed bottom bar on mobile — the highest-converting pattern for home-service lead gen. */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/page-cta.php';
$mobileCta = page_cta($route);
$tx = $businessInfo['regions']['tx'];
?>
<div class="mobile-cta-bar">
    <div class="mobile-cta-bar__inner">
        <a href="tel:<?= htmlspecialchars($tx['phone_e164']) ?>" class="btn btn--outline"><?= icon('phone') ?> Call Now</a>
        <a href="<?= htmlspecialchars($route['mobile_cta_href'] ?? $mobileCta['href']) ?>" class="btn btn--primary"><?= htmlspecialchars($mobileCta['short']) ?></a>
    </div>
</div>
