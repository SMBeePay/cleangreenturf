<?php
/**
 * The site's lead-capture form. The original site's form posted to a
 * Hostinger-Website-Builder-only endpoint that can't be reused (see
 * docs/business-info.md) — this is a from-scratch replacement submitting
 * to forms/handle-quote.php, which emails andrew@cleangreenturf.com per
 * the owner's explicit instruction. Fields match a real notification email
 * from the live form (see docs/audit-findings.md "Real form fields
 * confirmed") — Name, Phone, Email, Full Address, Approx Size of Turf
 * Area, and a cleaning-frequency field the first rebuild had guessed wrong
 * as a services checklist.
 *
 * Shared by Home and Contact (the Google Ads landing page has its own copy,
 * includes/quote-form-ga.php, without this dropdown — see
 * docs/audit-findings.md "Zoho CRM integration"). The service dropdown
 * below routes the submission into Zoho CRM's Turf Cleaning or Turf Repair
 * pipeline in forms/handle-quote.php; it defaults to Turf Cleaning since
 * that's most of the site's lead volume today.
 */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/validation.php';
$quoteFormError = trim((string)($_GET['error'] ?? ''));
?>
<!-- quote-form:start -->
<div class="quote-form-wrap" id="free-quote">
    <div class="quote-form-wrap__head">
        <h3>Get Your Free Quote</h3>
        <p>Fast response, no obligation. We usually reply same-day.</p>
    </div>
    <?php if ($quoteFormError !== ''): ?>
    <p class="quote-form__error" role="alert"><?= htmlspecialchars(quote_form_error_message($quoteFormError), ENT_QUOTES) ?></p>
    <?php endif; ?>
    <form class="quote-form" action="/forms/handle-quote.php" method="post" novalidate>
        <div class="quote-form__row">
            <label for="qf-name">Name*</label>
            <input type="text" id="qf-name" name="name" required autocomplete="name">
        </div>
        <div class="quote-form__row">
            <label for="qf-service">What do you need?*</label>
            <select id="qf-service" name="service" required>
                <option value="cleaning" selected>Turf Cleaning</option>
                <option value="repair">Turf Repair</option>
                <option value="cleaning_repair">Both Cleaning &amp; Repair</option>
            </select>
        </div>
        <div class="quote-form__row">
            <label for="qf-phone">Phone*</label>
            <input type="tel" id="qf-phone" name="phone" required autocomplete="tel">
        </div>
        <div class="quote-form__row">
            <label for="qf-email">Email*</label>
            <input type="email" id="qf-email" name="email" required autocomplete="email">
        </div>
        <div class="quote-form__row">
            <label for="qf-address">Street Address*</label>
            <input type="text" id="qf-address" name="address" required autocomplete="street-address">
        </div>
        <div class="quote-form__row">
            <label for="qf-city">City*</label>
            <input type="text" id="qf-city" name="city" required autocomplete="address-level2">
        </div>
        <div class="quote-form__row">
            <label for="qf-size">Approx. Size of Your Turf Area*</label>
            <select id="qf-size" name="turf_size" required>
                <option value="">Select one</option>
                <option value="Less than 500 sq ft">Less than 500 sq ft</option>
                <option value="500-1000 sq ft">500–1,000 sq ft</option>
                <option value="1000-1500 sq ft">1,000–1,500 sq ft</option>
                <option value="1500-2500 sq ft">1,500–2,500 sq ft</option>
                <option value="2500+ sq ft">2,500+ sq ft</option>
                <option value="Not sure">Not sure</option>
            </select>
        </div>
        <div class="quote-form__row">
            <label for="qf-frequency">How Frequently Would You Like Your Turf Cleaned?*</label>
            <select id="qf-frequency" name="frequency" required>
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
            <label for="qf-notes">Any additional notes we should know about?</label>
            <textarea id="qf-notes" name="notes" rows="4"></textarea>
        </div>
        <!-- honeypot spam trap, hidden via CSS — real users never fill this in -->
        <div class="quote-form__hp" aria-hidden="true">
            <label for="qf-website">Leave this field blank</label>
            <input type="text" id="qf-website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <button type="submit" class="btn btn--primary btn--block">Get My Free Quote</button>
        <p class="quote-form__trust"><?= icon('shield-check') ?> No spam. Your info is only used to send your quote.</p>
    </form>
</div>
<!-- quote-form:end -->
