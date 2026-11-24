<?php
/**
 * /turf-installation/cost-calculator (install SEO plan, Phase 2). Client-side
 * calculator (assets/js/calculator.js), no third-party widget. Rates are
 * handed to the script from config/site-facts.php via data attributes, so
 * nothing is hard-coded in JS. The lead form after the result posts to
 * forms/handle-install-quote.php with form_source=calculator.
 *
 * Putting greens: the owner said (Nov 24) they may run above the published
 * range, so choosing "Putting green" shows no range, just the quote form.
 */
require __DIR__ . '/../../includes/install-page.php';
require_once __DIR__ . '/../../includes/spam-guard.php';
require_once __DIR__ . '/../../includes/validation.php';
$calcError = trim((string)($_GET['error'] ?? ''));
?>
<h1>Artificial Turf Cost Calculator</h1>

<p>Enter your yard size and get a planning range based on our installed pricing of <?= $priceRange ?> per square foot. It takes about a minute. If you'd rather talk to a person, <?= quote_link() ?>.</p>

<?php if ($calcError !== ''): ?>
<p class="quote-form__error" role="alert" id="calc-error"><?= htmlspecialchars(quote_form_error_message($calcError), ENT_QUOTES) ?> Run the calculator again and resend, or <?= quote_link('request a quote here') ?>.</p>
<?php endif; ?>
<div class="calculator" data-calculator data-price-low="<?= (float)$priceLow ?>" data-price-high="<?= (float)$priceHigh ?>">
    <div class="calculator__inputs">
        <div class="quote-form__row">
            <label for="calc-area">Approximate area (square feet)*</label>
            <input type="number" id="calc-area" min="1" step="1" inputmode="numeric" placeholder="e.g. 800">
            <p class="calculator__helper">Don't know it? Multiply length by width:
                <input type="number" id="calc-length" min="0" step="1" inputmode="numeric" aria-label="Length in feet" placeholder="length"> &times;
                <input type="number" id="calc-width" min="0" step="1" inputmode="numeric" aria-label="Width in feet" placeholder="width"> ft
            </p>
        </div>
        <div class="quote-form__row">
            <label for="calc-use">Turf use</label>
            <select id="calc-use">
                <option value="Backyard lawn">Backyard lawn</option>
                <option value="Pet area">Pet area</option>
                <option value="Putting green">Putting green</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="quote-form__row">
            <label for="calc-site">Site</label>
            <select id="calc-site">
                <option value="easy">Open and easy access</option>
                <option value="moderate">Some slope, curves or tight access</option>
                <option value="unsure">Not sure</option>
            </select>
        </div>
        <div class="quote-form__row">
            <label for="calc-city">City</label>
            <select id="calc-city">
                <?php foreach ($installCities as $city): ?>
                <option value="<?= htmlspecialchars($city['name']) ?>"><?= htmlspecialchars($city['name']) ?></option>
                <?php endforeach; ?>
                <option value="Other DFW">Other DFW</option>
            </select>
        </div>
        <button type="button" class="btn btn--primary btn--block" data-calc-run>Show My Price Range</button>
        <p class="quote-form__error" data-calc-error hidden role="alert"></p>
    </div>

    <div class="calculator__result" data-calc-result hidden aria-live="polite">
        <p class="calculator__range" data-calc-range></p>
        <p class="calculator__note" data-calc-note>This is a planning range, not a quote. Final price depends on the turf you pick, the installation type and what's under your current yard.</p>

        <form class="quote-form" action="/forms/handle-install-quote.php" method="post" novalidate data-lead-form="calculator">
            <h2 class="calculator__form-title">Send me an exact quote</h2>
            <div class="quote-form__row">
                <label for="calc-name">Name*</label>
                <input type="text" id="calc-name" name="name" required autocomplete="name">
            </div>
            <div class="quote-form__row">
                <label for="calc-phone">Phone*</label>
                <input type="tel" id="calc-phone" name="phone" required autocomplete="tel">
            </div>
            <div class="quote-form__row">
                <label for="calc-email">Email (optional)</label>
                <input type="email" id="calc-email" name="email" autocomplete="email">
            </div>
            <input type="hidden" name="form_source" value="calculator">
            <input type="hidden" name="city" data-calc-field="city">
            <input type="hidden" name="calc_area" data-calc-field="area">
            <input type="hidden" name="calc_use" data-calc-field="use">
            <input type="hidden" name="calc_site" data-calc-field="site">
            <input type="hidden" name="calc_result" data-calc-field="result">
            <?= spam_guard_field() ?>
            <div class="quote-form__hp" aria-hidden="true">
                <label for="calc-website">Leave this field blank</label>
                <input type="text" id="calc-website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" class="btn btn--primary btn--block">Send Me an Exact Quote</button>
        </form>
    </div>
    <noscript><p>The calculator needs JavaScript. You can still <?= quote_link() ?> or call us.</p></noscript>
</div>

<p><strong>What the range doesn't know yet:</strong> which turf you want, what's under your yard now, how water moves across it and how we get material in. That's why the quote comes after a quick look at your address.</p>

<script src="/assets/js/calculator.js?v=<?= filemtime(__DIR__ . '/../../assets/js/calculator.js') ?>" defer></script>
