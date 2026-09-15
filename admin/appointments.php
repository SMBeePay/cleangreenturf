<?php
/**
 * Internal appointments dashboard — the "how does Andrew see the
 * calendar" piece. Session-gated by admin/index.php's login.
 */
declare(strict_types=1);
session_start();

if (empty($_SESSION['scheduler_admin'])) {
    header('Location: /admin/index.php');
    exit;
}

require_once __DIR__ . '/../includes/scheduler.php';
require_once __DIR__ . '/../includes/icons.php';
$schedulerConfig = require __DIR__ . '/../config/scheduler.php';

$filter = in_array($_GET['filter'] ?? '', ['all', 'cancelled'], true) ? $_GET['filter'] : 'upcoming';
$appointments = scheduler_list_for_admin($filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Estimate Appointments | Clean Green Turf Admin</title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
body { padding: var(--space-6) 1.5rem; max-width: 1100px; margin: 0 auto; }
table { width: 100%; border-collapse: collapse; background: var(--paper); box-shadow: var(--shadow-sm); font-size: var(--step--1); }
th, td { text-align: left; padding: 0.7em 1em; border-bottom: 1px solid var(--border); }
th { font-family: var(--font-display); text-transform: uppercase; letter-spacing: 0.04em; background: var(--cream-deep); }
.status-cancelled { color: var(--ink-faint); }
.status-cancelled td { text-decoration: line-through; }
.admin-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-3); }
.admin-filters a { margin-right: 1.2em; font-family: var(--font-display); text-transform: uppercase; font-size: var(--step--1); color: var(--ink-soft); }
.admin-filters a.active { color: var(--forest-950); font-weight: 700; }
.admin-empty { background: var(--paper); border: 1px solid var(--border); border-radius: var(--radius); padding: var(--space-5); text-align: center; color: var(--ink-faint); }
</style>
</head>
<body>
<div class="admin-top">
    <h1 style="font-size: var(--step-2); margin: 0;">Estimate Appointments</h1>
    <a href="/admin/logout.php" class="btn btn--outline">Log Out</a>
</div>
<div class="admin-filters">
    <a href="?filter=upcoming" class="<?= $filter === 'upcoming' ? 'active' : '' ?>">Upcoming</a>
    <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
    <a href="?filter=cancelled" class="<?= $filter === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
</div>
<?php if (!$appointments): ?>
<p class="admin-empty">No appointments to show.</p>
<?php else: ?>
<table>
<thead><tr><th>When</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Notes</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($appointments as $a): ?>
<tr class="<?= $a['status'] === 'cancelled' ? 'status-cancelled' : '' ?>">
    <td><?= htmlspecialchars(scheduler_format_display($a['slot_start'], $schedulerConfig)) ?></td>
    <td><?= htmlspecialchars($a['name']) ?></td>
    <td><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $a['phone'])) ?>"><?= htmlspecialchars($a['phone']) ?></a></td>
    <td><a href="mailto:<?= htmlspecialchars($a['email']) ?>"><?= htmlspecialchars($a['email']) ?></a></td>
    <td><?= htmlspecialchars($a['address']) ?></td>
    <td><?= htmlspecialchars($a['notes'] !== '' ? $a['notes'] : '—') ?></td>
    <td><?= htmlspecialchars(ucfirst($a['status'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</body>
</html>
