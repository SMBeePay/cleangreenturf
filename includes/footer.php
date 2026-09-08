<?php
/**
 * Sitewide footer. Reproduces the live site's NAP, service-area lists, and
 * internal links. See docs/business-info.md for where each value came from.
 */
$tx = $businessInfo['regions']['tx'];
$ca = $businessInfo['regions']['ca'];

function slugify_city($city) {
    return strtolower(str_replace([' ', "'"], ['-', ''], $city));
}
?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__about">
            <h2><?= htmlspecialchars($businessInfo['name']) ?></h2>
            <p>Proudly serving the <strong>Dallas Fort-Worth</strong> metroplex and surrounding areas, and the <strong>California Bay Area</strong>.</p>
            <p>
                <a href="mailto:<?= htmlspecialchars($businessInfo['email']) ?>"><?= htmlspecialchars($businessInfo['email']) ?></a><br>
                <?= htmlspecialchars($tx['address']['street']) ?>, <?= htmlspecialchars($tx['address']['city']) ?>, <?= htmlspecialchars($tx['address']['state']) ?> <?= htmlspecialchars($tx['address']['zip']) ?><br>
                <?= htmlspecialchars($ca['address']['street']) ?>, <?= htmlspecialchars($ca['address']['city']) ?>, <?= htmlspecialchars($ca['address']['state']) ?> <?= htmlspecialchars($ca['address']['zip']) ?>
            </p>
            <p>
                TX Phone: <a href="tel:<?= htmlspecialchars($tx['phone_e164']) ?>"><?= htmlspecialchars($tx['phone_display']) ?></a><br>
                CA Phone: <a href="tel:<?= htmlspecialchars($ca['phone_e164']) ?>"><?= htmlspecialchars($ca['phone_display']) ?></a>
            </p>
            <p class="site-footer__social">
                <a href="<?= htmlspecialchars($businessInfo['social']['facebook']) ?>">Facebook</a>
                <a href="<?= htmlspecialchars($businessInfo['social']['instagram']) ?>">Instagram</a>
            </p>
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
            <h3>CA Service Areas</h3>
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
                <li><a href="/blog-post4">Climate & Turf Maintenance</a></li>
            </ul>
        </div>
    </div>

    <div class="site-footer__bottom">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($businessInfo['name']) ?>. All rights reserved.</p>
    </div>
</footer>
