<?php
/** /turf-installation/areas (install SEO plan, Phase 3). Spec copy; city list from config/install-cities.php. */
require __DIR__ . '/../../includes/install-page.php';
?>
<h1>Where We Install Artificial Turf in DFW</h1>

<p>We install artificial turf across the East DFW cluster, and we'll quote other DFW addresses. Installed pricing is about <?= $priceRange ?> per square foot everywhere we work. Pick your city for local notes on soil, HOAs and what to expect.</p>
<ul>
    <?php foreach ($installCities as $slug => $city): ?>
    <li><?= maybe_link('/turf-installation/' . $slug, $city['name']) ?></li>
    <?php endforeach; ?>
</ul>

<h2>Not on the list?</h2>
<p>Send us your address with the <a href="<?= $estimateUrl ?>">quote form</a>. If it's within reach of our crews, we'll quote it. If it isn't, we'll say so.</p>
<?php render_cta($route); ?>
