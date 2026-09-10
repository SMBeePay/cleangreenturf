<?php
/**
 * Sitewide footer. Reproduces the live site's NAP, service-area lists, and
 * internal links. See docs/business-info.md for where each value came from.
 */
require_once __DIR__ . '/icons.php';
$tx = $businessInfo['regions']['tx'];

function slugify_city($city) {
    return strtolower(str_replace([' ', "'"], ['-', ''], $city));
}
?>
<footer class="site-footer">
    <div class="site-footer__top">
        <div class="site-footer__about">
            <h2><?= htmlspecialchars($businessInfo['name']) ?></h2>
            <p>Proudly serving the Dallas Fort-Worth metroplex and surrounding areas.</p>

            <div class="site-footer__nap"><?= icon('mail') ?> <a href="mailto:<?= htmlspecialchars($businessInfo['email']) ?>"><?= htmlspecialchars($businessInfo['email']) ?></a></div>
            <div class="site-footer__nap"><?= icon('pin') ?> <span><?= htmlspecialchars($tx['address']['street']) ?>, <?= htmlspecialchars($tx['address']['city']) ?>, <?= htmlspecialchars($tx['address']['state']) ?> <?= htmlspecialchars($tx['address']['zip']) ?></span></div>
            <div class="site-footer__nap"><?= icon('phone') ?> <span><a href="tel:<?= htmlspecialchars($tx['phone_e164']) ?>"><?= htmlspecialchars($tx['phone_display']) ?></a></span></div>

            <div class="site-footer__social">
                <a href="<?= htmlspecialchars($businessInfo['social']['facebook']) ?>" aria-label="Facebook"><?= icon('facebook') ?></a>
                <a href="<?= htmlspecialchars($businessInfo['social']['instagram']) ?>" aria-label="Instagram"><?= icon('instagram') ?></a>
            </div>
        </div>

        <div class="site-footer__areas">
            <h3>DFW Service Areas</h3>
            <ul>
                <?php foreach ($businessInfo['service_areas']['dfw'] as $city):
                    $slug = slugify_city($city) . '-tx-turf-cleaning';
                    $hasPage = isset($routes['/' . $slug]);
                ?>
                <li><?php if ($hasPage): ?><a href="/<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($city) ?>, TX</a><?php else: ?><?= htmlspecialchars($city) ?>, TX<?php endif; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="site-footer__areas">
            <h3>Austin Service Areas</h3>
            <ul>
                <?php foreach ($businessInfo['service_areas']['austin'] as $city): ?>
                <li><?= htmlspecialchars($city) ?>, TX</li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="site-footer__areas">
            <h3>California</h3>
            <ul>
                <?php foreach ($businessInfo['service_areas']['ca'] as $city):
                    $slug = slugify_city($city) . '-ca-turf-cleaning';
                    $hasPage = isset($routes['/' . $slug]);
                ?>
                <li><?php if ($hasPage): ?><a href="/<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($city) ?>, CA</a><?php else: ?><?= htmlspecialchars($city) ?>, CA<?php endif; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="site-footer__areas">
            <h3>Turf Cleaning Resources</h3>
            <ul>
                <li><a href="/how-to-clean-indoor-turf">How to Clean Indoor Turf</a></li>
                <li><a href="/turf-sports-field-maintenance">Sports Field Maintenance</a></li>
                <li><a href="/blog-post">Seasonal Cleaning Schedule</a></li>
                <li><a href="/blog-post1">Pet Turf Odor Guide</a></li>
                <li><a href="/blog-post2">Turf Infill 101</a></li>
                <li><a href="/blog-post3">DIY vs. Professional Cleaning</a></li>
                <li><a href="/blog-post4">Climate &amp; Turf Maintenance</a></li>
            </ul>
        </div>
    </div>

    <div class="site-footer__bottom">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($businessInfo['name']) ?>. All rights reserved.</p>
    </div>
</footer>
