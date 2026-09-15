<?php
/**
 * Calendar/booking widget, shared by the booking page and the reschedule
 * page. Expects $businessInfo and $schedulerConfig already in scope
 * (both are, via templates/page.php's shared require scope), plus:
 *   $mode     — 'book' (default) or 'reschedule'
 *   $token    — reschedule mode only
 *   $existing — reschedule mode only: the appointment row from
 *               scheduler_get_by_token(), to prefill the contact fields
 */
$mode = $mode ?? 'book';
$token = $token ?? '';
$existing = $existing ?? null;
$phoneDisplay = $businessInfo['regions']['tx']['phone_display'];
?>
<div class="scheduler" data-scheduler data-mode="<?= htmlspecialchars($mode) ?>" data-token="<?= htmlspecialchars($token) ?>" data-phone="<?= htmlspecialchars($phoneDisplay) ?>">

    <div data-scheduler-booking>
        <?php if ($mode === 'reschedule' && $existing): ?>
        <div class="scheduler__current">
            <p><?= icon('clock') ?> <strong>Current appointment:</strong> <?= htmlspecialchars(scheduler_format_display($existing['slot_start'], $schedulerConfig)) ?></p>
            <button type="button" class="btn btn--outline btn--small" data-scheduler-cancel>Cancel This Appointment</button>
        </div>
        <?php endif; ?>

        <p data-scheduler-error class="scheduler__error" hidden></p>

        <div class="scheduler__layout">
            <div class="scheduler-cal" data-scheduler-calendar aria-live="polite"></div>
            <div class="scheduler-slots" data-scheduler-slots hidden aria-live="polite"></div>
        </div>

        <form class="scheduler-form" data-scheduler-form hidden>
            <p class="scheduler-form__selected">Selected time: <strong data-selected-slot-label></strong></p>
            <input type="hidden" name="slot_start" value="">
            <div class="quote-form__row">
                <label for="sch-name">Name*</label>
                <input type="text" id="sch-name" name="name" required autocomplete="name" value="<?= htmlspecialchars($existing['name'] ?? '') ?>">
            </div>
            <div class="quote-form__row">
                <label for="sch-phone">Phone*</label>
                <input type="tel" id="sch-phone" name="phone" required autocomplete="tel" value="<?= htmlspecialchars($existing['phone'] ?? '') ?>">
            </div>
            <div class="quote-form__row">
                <label for="sch-email">Email*</label>
                <input type="email" id="sch-email" name="email" required autocomplete="email" value="<?= htmlspecialchars($existing['email'] ?? '') ?>">
            </div>
            <div class="quote-form__row">
                <label for="sch-address">Property Address*</label>
                <input type="text" id="sch-address" name="address" required autocomplete="street-address" value="<?= htmlspecialchars($existing['address'] ?? '') ?>">
            </div>
            <div class="quote-form__row">
                <label for="sch-notes">Anything we should know before we come out?</label>
                <textarea id="sch-notes" name="notes" rows="3"><?= htmlspecialchars($existing['notes'] ?? '') ?></textarea>
            </div>
            <div class="scheduler-consent">
                <label>
                    <input type="checkbox" name="sms_opt_in" value="1" <?= !empty($existing['sms_opt_in']) ? 'checked' : '' ?>>
                    <span>Yes, text me a reminder the day before my appointment. Message frequency: 1 message per scheduled appointment. Msg &amp; data rates may apply. Reply STOP to opt out, HELP for help. Consent isn&rsquo;t required to book &mdash; you&rsquo;ll get an email either way.</span>
                </label>
            </div>
            <div class="quote-form__hp" aria-hidden="true">
                <label for="sch-website">Leave this field blank</label>
                <input type="text" id="sch-website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" class="btn btn--primary btn--block"><?= $mode === 'reschedule' ? 'Confirm New Time' : 'Confirm My Estimate' ?></button>
        </form>
    </div>

    <div class="scheduler__confirm" data-scheduler-confirm hidden>
        <?= icon('check-circle', 'scheduler__confirm-icon') ?>
        <h3><?= $mode === 'reschedule' ? "You&rsquo;re Rebooked!" : "You&rsquo;re Booked!" ?></h3>
        <p>We&rsquo;ll see you <strong data-confirm-when></strong>.</p>
        <p data-confirm-sms-line>A confirmation has been sent to your email, and we&rsquo;ll text you a reminder the day before with a link to reschedule if anything changes.</p>
    </div>

    <?php if ($mode === 'reschedule'): ?>
    <div class="scheduler__cancelled" data-scheduler-cancelled hidden>
        <?= icon('check-circle', 'scheduler__confirm-icon') ?>
        <h3>Appointment Cancelled</h3>
        <p>Your estimate has been cancelled. Changed your mind? <a href="/schedule-turf-installation-estimate">Book a new time</a> any time.</p>
    </div>
    <?php endif; ?>

    <noscript><p class="scheduler__noscript">Please enable JavaScript to use the scheduler, or call us at <?= htmlspecialchars($phoneDisplay) ?> to book your free estimate.</p></noscript>
</div>
<script src="/assets/js/scheduler.js" defer></script>
