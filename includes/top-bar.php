<?php
/** Thin utility bar above the header: email + social links. */
require_once __DIR__ . '/icons.php';
?>
<div class="top-bar">
    <div class="container">
        <div class="top-bar__links">
            <a class="top-bar__item" href="mailto:<?= htmlspecialchars($businessInfo['email']) ?>"><?= icon('mail') ?> <?= htmlspecialchars($businessInfo['email']) ?></a>
            <span class="top-bar__item"><?= icon('pin') ?> DFW, TX &amp; Bay Area, CA</span>
        </div>
        <div class="top-bar__socials">
            <a href="<?= htmlspecialchars($businessInfo['social']['facebook']) ?>" aria-label="Facebook"><?= icon('facebook') ?></a>
            <a href="<?= htmlspecialchars($businessInfo['social']['instagram']) ?>" aria-label="Instagram"><?= icon('instagram') ?></a>
        </div>
    </div>
</div>
