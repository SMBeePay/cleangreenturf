<?php
/**
 * New page — lets a customer reschedule or cancel their turf-installation
 * estimate via the token link sent in their confirmation email/reminder
 * text. noindex,nofollow (see config/routes.php) since it's a private
 * per-customer link, not a page anyone should land on from search.
 */
require_once __DIR__ . '/../../includes/scheduler.php';
$schedulerConfig = require __DIR__ . '/../../config/scheduler.php';

$token = trim((string)($_GET['token'] ?? ''));
$existing = ($token !== '' && preg_match('/^[a-f0-9]{32}$/', $token)) ? scheduler_get_by_token($token) : null;
?>
<div class="scheduler-page-header">
    <h1>Reschedule Your Estimate</h1>
    <?php if (!$existing): ?>
    <p class="scheduler-page-header__sub">We couldn&rsquo;t find that appointment. The link may have expired or been mistyped &mdash; please call us at <a href="tel:<?= htmlspecialchars($businessInfo['regions']['tx']['phone_e164']) ?>"><?= htmlspecialchars($businessInfo['regions']['tx']['phone_display']) ?></a> and we&rsquo;ll sort it out, or <a href="/schedule-turf-installation-estimate">book a new estimate</a>.</p>
    <?php elseif ($existing['status'] === 'cancelled'): ?>
    <p class="scheduler-page-header__sub">This appointment has already been cancelled. Want to get back on the schedule? <a href="/schedule-turf-installation-estimate">Book a new estimate</a>.</p>
    <?php else: ?>
    <p class="scheduler-page-header__sub">Pick a new day and time below, or cancel if you no longer need the visit.</p>
    <?php endif; ?>
</div>

<?php if ($existing && $existing['status'] !== 'cancelled'):
    $mode = 'reschedule';
    require __DIR__ . '/../../includes/scheduler-widget.php';
endif; ?>
