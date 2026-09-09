<?php
/** Sitewide header: top utility bar, logo, navigation, phone CTA. */
require_once __DIR__ . '/icons.php';
$primaryPhone = $businessInfo['regions']['tx']['phone_display'];
$primaryPhoneTel = $businessInfo['regions']['tx']['phone_e164'];
?>
<?php require __DIR__ . '/top-bar.php'; ?>
<header class="site-header">
    <div class="site-header__inner">
        <a href="/" class="site-header__logo" aria-label="<?= htmlspecialchars($businessInfo['name']) ?> — Home">
            <img src="/assets/images/logo.png" alt="<?= htmlspecialchars($businessInfo['name']) ?>" width="44" height="30">
            <span><?= htmlspecialchars($businessInfo['name']) ?></span>
        </a>
        <button type="button" class="site-header__menu-toggle" aria-expanded="false" aria-controls="mobile-nav" aria-label="Toggle menu">
            <?= icon('menu', 'icon-menu') ?><?= icon('close', 'icon-close') ?>
        </button>
        <?php require __DIR__ . '/navigation.php'; ?>
        <div class="site-header__cta">
            <a class="site-header__phone" href="tel:<?= htmlspecialchars($primaryPhoneTel) ?>">
                <?= icon('phone') ?>
                <span>
                    <small>Call for a free quote</small>
                    <?= htmlspecialchars($primaryPhone) ?>
                </span>
            </a>
            <a href="/#free-quote" class="btn btn--primary">Free Quote</a>
        </div>
    </div>
</header>
<?php require __DIR__ . '/trust-bar.php'; ?>
