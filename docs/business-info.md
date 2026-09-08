# Clean Green Turf — Business Info Reference (source of truth)

Captured from the live production site (footer, meta, and page content) on 2026-09-08.
Centralize these in `/includes/business-info.php` (or equivalent config) — do not
hard-code independently per page/template.

## Identity

- Business name: **Clean Green Turf**
- Primary domain: `https://cleangreenturf.com` (no `www`, no trailing slash on any indexed URL)
- Contact email: `andrew@cleangreenturf.com`
- Copyright line: "© 2025. All rights reserved." (footer — will need year update)

## Service regions & NAP

Clean Green Turf operates as (at least) two regional business units under one brand:

| Region | Address | Phone |
|---|---|---|
| Texas / DFW | 11900 Presario Road, McKinney, TX 75071 | (469) 796-0034 / 469-796-0034 |
| California / Bay Area | 2709 Holly Oak Ct, Brentwood, CA 94513 | 925-238-3178 |

⚠️ **Discrepancy found:** `/about` also displays the phone number **925-378-3506**
once, embedded in body copy, which matches neither official number above. Likely
a typo of 925-238-3178. Needs your confirmation before we carry a number forward
(see open questions).

Austin, TX is also served (per `/austin-tx` and search-indexed copy referencing a
"North Austin service center") but has no dedicated regional phone/address shown
in the crawled footer — it appears to route to the TX number.

## Service area city lists (as shown in the sitewide footer)

**DFW service areas:** Addison, Allen, Celina, Dallas, Frisco, McKinney, Plano,
Prosper, Richardson, The Colony, Rockwall, Royse City — TX

**Austin service areas:** Austin, Round Rock, Cedar Park, Pflugerville,
Georgetown, San Marcos, Kyle, Buda, Leander, Lakeway, Hutto — TX

**CA service areas:** Antioch, Berkeley, Brentwood, Clayton, Concord, Danville,
Discovery Bay, Dublin, Fremont, Livermore, Oakland, Walnut Creek, San Ramon — CA

Note: Only a subset of these cities currently have a dedicated landing page (see
`url-inventory.md`). Cities listed in the footer without a page (e.g. Royse City,
Oakland, Walnut Creek, San Ramon, and all the Austin-suburb cities) are an input
to Phase 2 location-page-strategy planning, not something to build automatically.

## Tracking / analytics (must be preserved, not duplicated)

- **Google Analytics 4**: measurement ID `G-SWCYCC0DG8`, loaded via `gtag.js`
  sitewide, injected through the builder's global "site meta" config (same
  snippet on every page — confirmed identical across all 36 crawled pages).
- **HubSpot**: portal/tracking script `//js-na2.hs-scripts.com/244728809.js`
  ("HubSpot Embed Code"), also sitewide. Portal ID `244728809`. Unclear yet
  whether HubSpot is used only for analytics/chat or also receives form
  submissions/CRM records — needs your confirmation.
- **No Meta/Facebook Pixel** found in any crawled page.
- **No Google Search Console verification meta tag** found in the crawled
  HTML — GSC ownership may be verified via DNS or Search Console's own file
  instead; confirm before launch (requirement #20).

## Lead form backend (important architectural finding)

The `/contact`, `/dfw-turf-cleaning-request-ga`, and homepage quote forms have
**no visible `action`/`method`** in the HTML — they're hydrated client-side by
the site builder's JS bundle. Tracing the bundle
(`/_astro-*/Page.*.js`) shows the generic submission target is
`https://builder-backend.hostinger.com/u1/data/v3/post/` — a **Hostinger
Website Builder-proprietary endpoint**, not something we can call from a
site we host ourselves.

**This means the current form backend cannot be migrated as-is — it must be
rebuilt.** We'll need a PHP-based form handler (SMTP mail, e.g. PHPMailer, or
a hosted form service) that collects the same fields and delivers leads the
same way you currently receive them. See open questions below for what "the
same way" means today.

## Form fields (as found on `/contact` and `/dfw-turf-cleaning-request-ga`)

- Name (text)
- Phone (text)
- Email (text)
- Full Address (text)
- Several checkboxes (service-interest options — the live HTML uses opaque
  builder-generated IDs, not readable labels; we'll need to view the rendered
  page to capture the actual checkbox labels before rebuilding this form)
- Additional notes (textarea)

## Favicon

- Source: `https://assets.zyrosite.com/.../gmail-cgt-logo-mv0W0DQRp9hvyOw9.png`
  (16x16, 32x32, 192x192, apple-touch-icon 180x180) — needs to be downloaded
  and self-hosted since `assets.zyrosite.com` is a Hostinger-builder CDN that
  won't exist once we're off the builder.

## Images

All images are served from `assets.zyrosite.com` / `cdn.zyrosite.com`
(Hostinger builder CDN) via on-the-fly resizing (`/cdn-cgi/image/...` paths).
These will need to be downloaded and self-hosted (or re-sourced) as part of
the rebuild — none of the current image URLs will keep working once the
builder backend goes away, regardless of what we do with the page URLs
themselves.
