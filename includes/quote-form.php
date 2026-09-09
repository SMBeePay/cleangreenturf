<?php
/**
 * The site's lead-capture form. The original site's form posted to a
 * Hostinger-Website-Builder-only endpoint that can't be reused (see
 * docs/business-info.md) — this is a from-scratch replacement collecting
 * the same fields, submitting to forms/handle-quote.php, which emails
 * andrew@cleangreenturf.com per the owner's explicit instruction.
 */
require_once __DIR__ . '/icons.php';
?>
<div class="quote-form-wrap" id="free-quote">
    <div class="quote-form-wrap__head">
        <h3>Get Your Free Turf Cleaning Quote</h3>
        <p>Fast response, no obligation. We usually reply same-day.</p>
    </div>
    <form class="quote-form" action="/forms/handle-quote.php" method="post" novalidate>
        <div class="quote-form__row">
            <label for="qf-name">Name*</label>
            <input type="text" id="qf-name" name="name" required autocomplete="name">
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
            <label for="qf-address">Full Address*</label>
            <input type="text" id="qf-address" name="address" required autocomplete="street-address">
        </div>
        <div class="quote-form__row">
            <label for="qf-size">Approx. Size of Your Turf Area*</label>
            <select id="qf-size" name="turf_size" required>
                <option value="">Select one</option>
                <option value="Less than 500 sq ft">Less than 500 sq ft</option>
                <option value="500-1500 sq ft">500–1,500 sq ft</option>
                <option value="1500-3000 sq ft">1,500–3,000 sq ft</option>
                <option value="3000+ sq ft">3,000+ sq ft</option>
                <option value="Not sure">Not sure</option>
            </select>
        </div>
        <div class="quote-form__row quote-form__row--checkboxes">
            <span class="quote-form__group-label">What do you need? (check all that apply)</span>
            <label><input type="checkbox" name="services[]" value="Turf Cleaning"> Turf Cleaning</label>
            <label><input type="checkbox" name="services[]" value="Pet Odor Removal"> Pet Odor Removal</label>
            <label><input type="checkbox" name="services[]" value="Infill Replenishment"> Infill Replenishment</label>
            <label><input type="checkbox" name="services[]" value="Commercial / Sports Field"> Commercial / Sports Field</label>
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
