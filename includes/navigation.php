<?php
/**
 * Sitewide navigation. Matches the live site's nav structure exactly for
 * Phase 1 (Home | Texas Service Areas dropdown | About | Contact | Free
 * Quote). CA pages are linked from the footer (see footer.php), matching
 * current behavior. See docs/audit-findings.md for the note on eventually
 * giving CA/Austin more nav visibility as a Phase 2 improvement.
 */
$navTxCities = [
    '/addison-tx-turf-cleaning' => 'Addison, TX',
    '/allen-tx-turf-cleaning' => 'Allen, TX',
    '/austin-tx' => 'Austin, TX',
    '/celina-tx-turf-cleaning' => 'Celina, TX',
    '/dallas-tx-turf-cleaning' => 'Dallas, TX',
    '/frisco-tx-turf-cleaning' => 'Frisco, TX',
    '/mckinney-tx-turf-cleaning' => 'McKinney, TX',
    '/plano-tx-turf-cleaning' => 'Plano, TX',
    '/prosper-tx-turf-cleaning' => 'Prosper, TX',
    '/richardson-tx-turf-cleaning' => 'Richardson, TX',
    '/the-colony-tx-turf-cleaning' => 'The Colony, TX',
    '/rockwall-tx-turf-cleaning' => 'Rockwall, TX',
];
?>
<nav class="site-nav" aria-label="Primary">
    <ul class="site-nav__list">
        <li><a href="/"<?= $currentPath === '/' ? ' aria-current="page"' : '' ?>>Home</a></li>
        <li class="site-nav__has-dropdown">
            <a href="/texas-service-areas"<?= $currentPath === '/texas-service-areas' ? ' aria-current="page"' : '' ?>>Texas Service Areas</a>
            <ul class="site-nav__dropdown">
                <?php foreach ($navTxCities as $href => $label): ?>
                <li><a href="<?= htmlspecialchars($href) ?>"<?= $currentPath === $href ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </li>
        <li><a href="/ca-turf-cleaning-service-areas"<?= $currentPath === '/ca-turf-cleaning-service-areas' ? ' aria-current="page"' : '' ?>>California Service Areas</a></li>
        <li><a href="/about"<?= $currentPath === '/about' ? ' aria-current="page"' : '' ?>>About</a></li>
        <li><a href="/contact"<?= $currentPath === '/contact' ? ' aria-current="page"' : '' ?>>Contact</a></li>
    </ul>
</nav>
