<?php
/**
 * /guides index (install SEO plan: "If /guides does not exist, create an
 * index page listing all guides"). Lists config/install-guides.php with
 * each guide's meta description as its one-line summary.
 */
require __DIR__ . '/../../includes/install-page.php';
$guides = require __DIR__ . '/../../config/install-guides.php';
$allRoutes = require __DIR__ . '/../../config/routes.php';
?>
<h1>Artificial Turf Installation Guides</h1>

<p>Straight answers to the questions DFW homeowners ask before they install artificial turf: cost, heat, dogs, HOAs, timelines and what a good install includes.</p>

<div class="guide-list">
<?php foreach ($guides as $path => $label): if (!route_exists($path)) { continue; } ?>
    <div class="card guide-list__item">
        <h2><a href="<?= htmlspecialchars($path) ?>"><?= htmlspecialchars($label) ?></a></h2>
        <p><?= htmlspecialchars($allRoutes[$path]['description']) ?></p>
    </div>
<?php endforeach; ?>
</div>

<p>Looking for prices? See <?= maybe_link('/turf-installation/cost', 'artificial turf cost in DFW') ?> or the <?= maybe_link('/turf-installation/cost-calculator', 'cost calculator') ?>.</p>
