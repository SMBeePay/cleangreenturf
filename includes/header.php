<?php
/** Sitewide visible header: logo, navigation, and a persistent phone CTA. */
$primaryPhone = $businessInfo['regions']['tx']['phone_display'];
$primaryPhoneTel = $businessInfo['regions']['tx']['phone_e164'];
?>
<header class="site-header">
    <div class="site-header__inner">
        <a href="/" class="site-header__logo" aria-label="<?= htmlspecialchars($businessInfo['name']) ?> — Home">
            <img src="/assets/images/logo.png" alt="<?= htmlspecialchars($businessInfo['name']) ?>" width="55" height="38">
            <span><?= htmlspecialchars($businessInfo['name']) ?></span>
        </a>
        <?php require __DIR__ . '/navigation.php'; ?>
        <a class="site-header__phone" href="tel:<?= htmlspecialchars($primaryPhoneTel) ?>">
            <?= htmlspecialchars($primaryPhone) ?>
        </a>
        <button type="button" class="site-header__menu-toggle" aria-expanded="false" aria-controls="mobile-nav" aria-label="Menu">☰</button>
    </div>
</header>
