<?php
/**
 * Short turf-installation quote form for the /turf-installation hub (install
 * SEO plan T-12): name, phone, email, city, rough size, optional yard
 * photos, message. Posts to forms/handle-install-quote.php, which emails
 * the owner (photos attached), creates a Turf Installation Deal in Zoho,
 * and sends the visitor on to the estimate scheduler.
 *
 * Email is required (unlike the spec's minimal list) because the Zoho
 * Contact upsert and the customer confirmation both need it.
 */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/spam-guard.php';
$installFormError = trim((string)($_GET['error'] ?? ''));
?>
<div class="quote-form-wrap install-quote" id="install-quote">
    <div class="quote-form-wrap__head">
        <h2>Get a Free Turf Installation Quote</h2>
        <p>Send your city and a rough size. We'll come back with a real price range.</p>
    </div>
    <?php if ($installFormError !== ''): ?>
    <p class="quote-form__error" role="alert"><?= htmlspecialchars(quote_form_error_message($installFormError), ENT_QUOTES) ?></p>
    <?php endif; ?>
    <form class="quote-form" action="/forms/handle-install-quote.php" method="post" enctype="multipart/form-data" novalidate data-lead-form="install_quote">
        <div class="quote-form__row">
            <label for="iq-name">Name*</label>
            <input type="text" id="iq-name" name="name" required autocomplete="name">
        </div>
        <div class="quote-form__row">
            <label for="iq-phone">Phone*</label>
            <input type="tel" id="iq-phone" name="phone" required autocomplete="tel">
        </div>
        <div class="quote-form__row">
            <label for="iq-email">Email*</label>
            <input type="email" id="iq-email" name="email" required autocomplete="email">
        </div>
        <div class="quote-form__row">
            <label for="iq-city">City*</label>
            <input type="text" id="iq-city" name="city" required autocomplete="address-level2">
        </div>
        <div class="quote-form__row">
            <label for="iq-size">Approx. Square Footage*</label>
            <select id="iq-size" name="turf_size" required>
                <option value="">Select one</option>
                <option value="Under 300 sq ft">Under 300 sq ft</option>
                <option value="300-500 sq ft">300&ndash;500 sq ft</option>
                <option value="500-1000 sq ft">500&ndash;1,000 sq ft</option>
                <option value="1000-1500 sq ft">1,000&ndash;1,500 sq ft</option>
                <option value="1500-2500 sq ft">1,500&ndash;2,500 sq ft</option>
                <option value="2500+ sq ft">2,500+ sq ft</option>
                <option value="Not sure">Not sure</option>
            </select>
        </div>
        <div class="quote-form__row">
            <label for="iq-photos">Photos of the yard (optional, up to 3)</label>
            <input type="file" id="iq-photos" name="photos[]" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" multiple>
        </div>
        <div class="quote-form__row">
            <label for="iq-notes">Anything else? (pets, slope, HOA, timing)</label>
            <textarea id="iq-notes" name="notes" rows="3"></textarea>
        </div>
        <?= spam_guard_field() ?>
        <div class="quote-form__hp" aria-hidden="true">
            <label for="iq-website">Leave this field blank</label>
            <input type="text" id="iq-website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <button type="submit" class="btn btn--primary btn--block">Get My Free Quote</button>
        <p class="quote-form__trust"><?= icon('shield-check') ?> No spam. Your info is only used to send your quote.</p>
    </form>
</div>
