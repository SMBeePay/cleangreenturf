<?php
/** Fixed bottom bar on mobile — the highest-converting pattern for home-service lead gen. */
require_once __DIR__ . '/icons.php';
$tx = $businessInfo['regions']['tx'];
?>
<div class="mobile-cta-bar">
    <div class="mobile-cta-bar__inner">
        <a href="tel:<?= htmlspecialchars($tx['phone_e164']) ?>" class="btn btn--outline"><?= icon('phone') ?> Call Now</a>
        <a href="/#free-quote" class="btn btn--primary">Free Quote</a>
    </div>
</div>
