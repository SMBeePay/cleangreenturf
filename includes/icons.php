<?php
/**
 * Minimal inline SVG icon set — no external icon font/CDN dependency.
 * Usage: icon('phone', 'class-name')
 */
function icon(string $name, string $class = ''): string {
    $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class) . '"' : '';
    $icons = [
        'phone' => '<path d="M4 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v4a2 2 0 0 1-2 2C9.16 21 3 14.84 3 6a2 2 0 0 1 1-2Z"/>',
        'mail' => '<path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/><path d="m3.5 6 8.5 6 8.5-6"/>',
        'pin' => '<path d="M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'star' => '<path d="m12 3 2.6 5.9 6.4.6-4.8 4.3 1.4 6.3L12 16.9 6.4 20.1l1.4-6.3-4.8-4.3 6.4-.6L12 3Z"/>',
        'leaf' => '<path d="M20 4S12 4 7 9s-3 12-3 12 8 2 13-3 3-14 3-14Z"/><path d="M4 20 12 12"/>',
        'shield-check' => '<path d="M12 3 4 6v6c0 5 3.5 7.7 8 9 4.5-1.3 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.2 2.2 4.8-4.8"/>',
        'facebook' => '<path d="M14 9h2V6h-2c-1.7 0-3 1.3-3 3v2H9v3h2v6h3v-6h2.2l.8-3H14V9.5c0-.3.2-.5.5-.5Z"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.7"/><circle cx="17.2" cy="6.8" r="1"/>',
        'quote' => '<path d="M7 8c-2 1-3 3-3 5.5S6 18 8.5 18 13 16 13 13.5c0-1.9-1.2-3.3-3-3.5.2-1.5 1.3-2.7 3-3.5L11.5 5C9 5.8 7.5 6.8 7 8Z"/><path d="M16 8c-2 1-3 3-3 5.5S18 18 20.5 18 25 16 25 13.5" transform="translate(-4)"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'sparkle' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'debris' => '<path d="M4 20h16"/><path d="M6 20v-4a6 6 0 0 1 12 0v4"/><path d="M9 10 7 6M15 10l2-4M12 9V4"/>',
        'brush' => '<path d="M14 4 20 10 11 19l-5 1 1-5Z"/><path d="M4 21c1-3 2-4 4-4"/>',
        'droplet' => '<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z"/>',
    ];
    $paths = $icons[$name] ?? '';
    return '<svg' . $classAttr . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}
