<?php
/**
 * Google Ads landing-page form — a separate copy of includes/quote-form.php
 * per owner request ("the google ads landing page should have its own
 * unique form"), so a PPC visitor never sees the Cleaning/Repair/Both
 * dropdown the shared form now has (extra friction on a paid-traffic page
 * that already commits to cleaning specifically) and so future edits to
 * the shared form can't accidentally change this one's conversion path.
 * Submits to the same forms/handle-quote.php handler as the shared form —
 * `service` is a fixed hidden field here instead of a visible dropdown.
 */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/validation.php';
$quoteFormError = trim((string)($_GET['error'] ?? ''));
?>
<!-- quote-form-ga:start -->
<div class="quote-form-wrap" id="lander-free-quote">
    <div class="quote-form-wrap__head">
        <h3>Get Your Free Turf Cleaning Quote</h3>
        <p>Fast response, no obligation. We usually reply same-day.</p>
    </div>
    <?php if ($quoteFormError !== ''): ?>
    <p class="quote-form__error" role="alert"><?= htmlspecialchars(quote_form_error_message($quoteFormError), ENT_QUOTES) ?></p>
    <?php endif; ?>
    <form class="quote-form" action="/forms/handle-quote.php" method="post" novalidate>
        <input type="hidden" name="service" value="cleaning">
        <div class="quote-form__row">
            <label for="qfga-name">Name*</label>
            <input type="text" id="qfga-name" name="name" required autocomplete="name">
        </div>
        <div class="quote-form__row">
            <label for="qfga-phone">Phone*</label>
            <input type="tel" id="qfga-phone" name="phone" required autocomplete="tel">
        </div>
        <div class="quote-form__row">
            <label for="qfga-email">Email*</label>
            <input type="email" id="qfga-email" name="email" required autocomplete="email">
        </div>
        <div class="quote-form__row">
            <label for="qfga-address">Full Address*</label>
            <input type="text" id="qfga-address" name="address" required autocomplete="street-address">
        </div>
        <div class="quote-form__row">
            <label for="qfga-size">Approx. Size of Your Turf Area*</label>
            <select id="qfga-size" name="turf_size" required>
                <option value="">Select one</option>
                <option value="Less than 500 sq ft">Less than 500 sq ft</option>
                <option value="500-1000 sq ft">500–1,000 sq ft</option>
                <option value="1000-2000 sq ft">1,000–2,000 sq ft</option>
                <option value="2000+ sq ft">2,000+ sq ft</option>
                <option value="Not sure">Not sure</option>
            </select>
        </div>
        <div class="quote-form__row">
            <label for="qfga-frequency">How Frequently Would You Like Your Turf Cleaned?*</label>
            <select id="qfga-frequency" name="frequency" required>
                <option value="">Select one</option>
                <option value="One-Time">One-Time</option>
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Bi-Annual">Bi-Annual</option>
                <option value="Annual">Annual</option>
                <option value="Not sure">Not sure</option>
            </select>
        </div>
        <div class="quote-form__row">
            <label for="qfga-notes">Any additional notes we should know about?</label>
            <textarea id="qfga-notes" name="notes" rows="4"></textarea>
        </div>
        <!-- honeypot spam trap, hidden via CSS — real users never fill this in -->
        <div class="quote-form__hp" aria-hidden="true">
            <label for="qfga-website">Leave this field blank</label>
            <input type="text" id="qfga-website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <button type="submit" class="btn btn--primary btn--block">Get My Free Quote</button>
        <p class="quote-form__trust"><?= icon('shield-check') ?> No spam. Your info is only used to send your quote.</p>
    </form>
</div>
<!-- quote-form-ga:end -->
