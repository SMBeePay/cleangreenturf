<?php
/**
 * New page — turf installation estimate scheduler. Not a migrated page
 * (no live-site equivalent); the business is expanding beyond cleaning
 * into installation (see docs/audit-findings.md "Turf installation
 * estimate scheduler"). Wrapped in a plain <div> rather than starting
 * with a bare <h1> so templates/page.php's hero-extraction regex doesn't
 * fire — this page doesn't need the generic hero-band-with-cleaning-quote
 * CTA every other page gets, since the calendar itself is the CTA.
 */
require_once __DIR__ . '/../../includes/scheduler.php';
$schedulerConfig = require __DIR__ . '/../../config/scheduler.php';
?>
<div class="scheduler-hero">
    <div class="scheduler-hero__intro">
        <h1>Schedule Your Free In-Person Turf Installation Estimate</h1>
        <p class="scheduler-page-header__sub">Pick a day and time that works for you, and one of our installers will come take a look, measure your space, and walk you through options and pricing — no obligation.</p>
    </div>

    <div class="scheduler-hero__widget">
        <?php
        $mode = 'book';
        require __DIR__ . '/../../includes/scheduler-widget.php';
        ?>
    </div>

    <ul class="scheduler-trust">
        <li><?= icon('check-circle') ?> Free, no-obligation visit</li>
        <li><?= icon('clock') ?> Usually 30&ndash;45 minutes on site</li>
        <li><?= icon('shield-check') ?> We&rsquo;ll text you a reminder the day before, with an easy link to reschedule</li>
    </ul>
</div>
