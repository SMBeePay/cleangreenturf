<?php
/**
 * Which conversion point a page's CTAs (hero, bottom banner, mobile bar)
 * should send people to. Cleaning pages keep the homepage quote form;
 * install pages ('cta' => 'installation' in config/routes.php) go to the
 * estimate scheduler, with the spec's standard CTA wording.
 */
declare(strict_types=1);

function page_cta(array $route): array {
    if (($route['cta'] ?? '') === 'installation') {
        return [
            'href' => fact_raw('estimate_url'),
            'label' => 'Get a free turf installation quote',
            'short' => 'Free Quote',
            'banner_title' => 'Thinking About Turf for Your Yard?',
            'banner_text' => 'Send your address and a rough size. We\'ll come back with a real price range, usually the same day.',
        ];
    }
    return [
        'href' => '/#free-quote',
        'label' => 'Get a Free Quote',
        'short' => 'Free Quote',
        'banner_title' => 'Ready for Turf That Looks Brand New?',
        'banner_text' => 'Free, no-obligation quotes — pet-safe, eco-friendly, and built for your climate.',
    ];
}

/** Inline [CTA] block from the install spec: button + supporting line. */
function render_cta(array $route, string $location = 'inline_cta'): void {
    $cta = page_cta($route);
    echo '<div class="cta-block" data-cta-location="' . htmlspecialchars($location) . '">'
        . '<a href="' . htmlspecialchars($cta['href']) . '" class="btn btn--primary">' . htmlspecialchars($cta['label']) . '</a>'
        . '<p class="cta-block__sub">' . htmlspecialchars($cta['banner_text']) . '</p>'
        . '</div>';
}
