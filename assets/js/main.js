(function () {
    var toggle = document.querySelector('.site-header__menu-toggle');
    var header = document.querySelector('.site-header');
    if (!toggle || !header) return;
    toggle.addEventListener('click', function () {
        var isOpen = header.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
})();

/*
 * Lead attribution (T-01). Two first-party cookies, 90 days each:
 *   cgt_ft  first touch, written once and never overwritten
 *   cgt_lt  last touch, overwritten whenever a visit arrives with UTMs or
 *           from another site (a plain internal page view leaves it alone)
 * Each holds source/medium/campaign, the external referrer and the landing
 * page. They're copied into hidden inputs on every lead form at submit time
 * and read back by includes/attribution.php.
 */
(function () {
    var DAYS = 90;

    function readCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        if (!m) return null;
        try { return JSON.parse(decodeURIComponent(m[1])); } catch (e) { return null; }
    }
    function writeCookie(name, value) {
        var expires = new Date(Date.now() + DAYS * 864e5).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(JSON.stringify(value)) +
            '; expires=' + expires + '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }
    function cap(s) { return (s || '').slice(0, 300); }

    var params = new URLSearchParams(location.search);
    var ref = document.referrer || '';
    var externalRef = '';
    try { if (ref && new URL(ref).hostname !== location.hostname) externalRef = ref; } catch (e) {}

    var touch = {
        s: cap(params.get('utm_source')),
        m: cap(params.get('utm_medium')),
        c: cap(params.get('utm_campaign')),
        r: cap(externalRef),
        lp: cap(location.pathname + location.search),
        t: new Date().toISOString()
    };
    var isNewTouch = touch.s || touch.m || touch.c || touch.r;

    if (!readCookie('cgt_ft')) writeCookie('cgt_ft', touch);
    if (isNewTouch || !readCookie('cgt_lt')) writeCookie('cgt_lt', touch);

    function sourceOf(t) {
        if (!t) return '';
        if (t.s) return t.s + (t.m ? ' / ' + t.m : '');
        if (t.r) { try { return new URL(t.r).hostname + ' / referral'; } catch (e) { return t.r; } }
        return '(direct)';
    }
    function gaClientId() {
        var m = document.cookie.match(/(?:^|; )_ga=GA\d\.\d\.([^;]+)/);
        return m ? m[1] : '';
    }

    function fill(form) {
        var ft = readCookie('cgt_ft');
        var lt = readCookie('cgt_lt') || {};
        var values = {
            utm_source: lt.s || '',
            utm_medium: lt.m || '',
            utm_campaign: lt.c || '',
            referrer: lt.r || '',
            landing_page: lt.lp || '',
            first_touch_source: sourceOf(ft),
            first_touch_landing_page: ft ? ft.lp : '',
            ga_client_id: gaClientId(),
            submitted_at: new Date().toISOString()
        };
        Object.keys(values).forEach(function (name) {
            var input = form.querySelector('input[type=hidden][name="' + name + '"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                form.appendChild(input);
            }
            input.value = values[name];
        });
    }

    // Capture phase, so the fields are in place before any submit handler
    // (e.g. the scheduler's fetch + FormData) reads the form.
    document.addEventListener('submit', function (e) {
        if (e.target && e.target.matches && e.target.matches('[data-lead-form]')) fill(e.target);
    }, true);
    window.cgtFillAttribution = fill;
})();

/*
 * GA4 events (T-02): phone_click, cta_click (with cta_location),
 * estimate_form_start, estimate_form_submit. Success pages mark themselves
 * with <span data-ga-event="estimate_form_submit" ...> so the submit event
 * only fires once the server has accepted the lead.
 */
(function () {
    function track(name, params) {
        if (typeof window.gtag === 'function') window.gtag('event', name, params || {});
    }
    window.cgtTrack = track;

    function ctaLocation(el) {
        var marked = el.closest('[data-cta-location]');
        if (marked) return marked.getAttribute('data-cta-location');
        var map = [
            ['.page-hero', 'hero'], ['.mobile-cta-bar', 'mobile_bar'], ['.cta-banner', 'cta_banner'],
            ['.site-header', 'header'], ['.site-footer', 'footer'], ['.cta-block', 'inline_cta'], ['.prose', 'content']
        ];
        for (var i = 0; i < map.length; i++) if (el.closest(map[i][0])) return map[i][1];
        return 'other';
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('a') : null;
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href.indexOf('tel:') === 0) {
            track('phone_click', { cta_location: ctaLocation(a), page_path: location.pathname });
        } else if (a.classList.contains('btn')) {
            track('cta_click', { cta_location: ctaLocation(a), link_url: href, page_path: location.pathname });
        }
    });

    var started = false;
    document.addEventListener('focusin', function (e) {
        if (started) return;
        var form = e.target.closest ? e.target.closest('[data-lead-form]') : null;
        if (!form) return;
        started = true;
        track('estimate_form_start', { form_type: form.getAttribute('data-lead-form'), page_path: location.pathname });
    });

    document.querySelectorAll('[data-ga-event]').forEach(function (el) {
        var name = el.getAttribute('data-ga-event');
        var key = 'cgt_ev_' + name + location.pathname + location.search;
        try { if (sessionStorage.getItem(key)) return; sessionStorage.setItem(key, '1'); } catch (e) {}
        track(name, { form_type: el.getAttribute('data-form-type') || '' });
    });
})();
