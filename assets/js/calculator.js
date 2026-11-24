/*
 * Turf cost calculator (/turf-installation/cost-calculator). Rates come from
 * data-price-low / data-price-high (server-rendered from site-facts).
 * Logic per the install SEO plan:
 *   open/easy access          -> low to midpoint
 *   some slope/curves/access  -> midpoint to high
 *   not sure                  -> full range
 * Result = area x rate, rounded to the nearest $100. Putting greens get no
 * range (they can run above it), just the quote form.
 */
(function () {
    var root = document.querySelector('[data-calculator]');
    if (!root) return;
    var low = parseFloat(root.getAttribute('data-price-low'));
    var high = parseFloat(root.getAttribute('data-price-high'));
    var mid = (low + high) / 2;

    var area = document.getElementById('calc-area');
    var len = document.getElementById('calc-length');
    var wid = document.getElementById('calc-width');
    var use = document.getElementById('calc-use');
    var site = document.getElementById('calc-site');
    var city = document.getElementById('calc-city');
    var errorEl = root.querySelector('[data-calc-error]');
    var resultEl = root.querySelector('[data-calc-result]');
    var rangeEl = root.querySelector('[data-calc-range]');

    function syncFromDimensions() {
        var l = parseFloat(len.value), w = parseFloat(wid.value);
        if (l > 0 && w > 0) area.value = Math.round(l * w);
    }
    len.addEventListener('input', syncFromDimensions);
    wid.addEventListener('input', syncFromDimensions);

    function money(n) {
        return '$' + (Math.round(n / 100) * 100).toLocaleString('en-US');
    }
    function setField(name, value) {
        var input = root.querySelector('[data-calc-field="' + name + '"]');
        if (input) input.value = value;
    }

    root.querySelector('[data-calc-run]').addEventListener('click', function () {
        var sqft = Math.round(parseFloat(area.value));
        if (!(sqft > 0)) {
            errorEl.textContent = 'Enter your approximate square footage (or length and width).';
            errorEl.hidden = false;
            area.focus();
            return;
        }
        errorEl.hidden = true;

        var text;
        if (use.value === 'Putting green') {
            text = 'Putting greens are priced per project and can run above our standard $' + low + ' to $' + high +
                ' per square foot range, depending on size, shape and contour. Send us the details below for a real number.';
        } else {
            var from = low, to = high;
            if (site.value === 'easy') { to = mid; }
            else if (site.value === 'moderate') { from = mid; }
            text = money(sqft * from) + ' to ' + money(sqft * to);
        }
        rangeEl.textContent = text;

        setField('city', city.value);
        setField('area', sqft);
        setField('use', use.value);
        setField('site', site.options[site.selectedIndex].text);
        setField('result', text);

        resultEl.hidden = false;
        resultEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (window.cgtTrack) {
            window.cgtTrack('calculator_complete', { sqft: sqft, turf_use: use.value, site: site.value, city: city.value });
        }
    });
})();
