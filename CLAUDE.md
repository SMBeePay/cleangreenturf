# Clean Green Turf — Website Migration

## What this project is

Migrating cleangreenturf.com off Hostinger Horizons (AI site builder) to a
custom PHP/HTML/CSS/JS site, version-controlled in this GitHub repo, hosted
on Hostinger. This is a **migration**, not a redesign-from-scratch: the live
site is the source of truth for URLs, content, metadata, headings, internal
links, images, schema, tracking, and forms.

**Full requirements are non-negotiable and live in
[`docs/migration-requirements.md`](docs/migration-requirements.md). Read it
before making any structural, URL, or content-removal decision.**

## The rule that overrides convenience

Preservation is the default. If a change would delete a page, change a URL,
consolidate pages, remove substantial copy/links/schema, or change
canonicalization/heading targeting — stop and flag it instead of just doing
it because it's cleaner. See requirement #33 in the doc above.

## Two-phase approach

1. **Preserve** — reproduce the existing site's URLs/content/SEO/tracking in
   the new PHP architecture exactly. Nothing new yet.
2. **Improve** — only after Phase 1 is verified against the migration
   inventory: performance, UX, conversion, new Repair/Installation pages,
   location-page strategy, schema upgrades, internal linking.

## Key artifacts to maintain as work proceeds

- `docs/url-inventory.md` (or .csv) — every existing URL: title, H1, meta
  description, canonical, migration action (default: KEEP URL UNCHANGED).
- `docs/redirect-map.md` — any old→new 301s, each with a documented reason.
- `/includes/` — shared PHP (header, footer, navigation, cta, schema,
  business-info config) so NAP/tracking IDs/etc. exist in exactly one place.
- `/sitemap.xml`, `/robots.txt` — canonical, indexable URLs only.

## Local testing

`dev_router.php` emulates the `.htaccess` rewrite rules for PHP's built-in
server, since `php -S` doesn't read `.htaccess`. It is dev-only, not used in
production (Apache + `.htaccess` handles routing there):

```
php -S localhost:8000 dev_router.php
```

Regenerate `sitemap.xml` after adding/removing a route: `php bin/generate-sitemap.php`.

## Architecture constraints

- No `.php` extensions exposed in public URLs (route/rewrite via `.htaccess`).
- Minimal JS, no heavy framework unless there's a real functional need.
- Force HTTPS, one canonical host (confirm www vs non-www from the live
  site before assuming), no duplicate-access paths.
- Never commit secrets (SMTP creds, API keys, Hostinger creds). Use a
  `.gitignore` and a config mechanism kept out of git for anything sensitive.

## Current status (as of the initial rebuild commit)

Phase 1 (preserve) is largely built and passing local smoke tests:

- All 36 live URLs inventoried (`docs/url-inventory.md`), backed up
  (`docs/site-backup-2026-09-08/`), and re-implemented in the new PHP
  architecture at the exact same URLs (`config/routes.php`).
- Front-controller routing (`index.php` + `.htaccess`) with no `.php` in any
  public URL, HTTPS + non-www enforced, real 404s, a 301 map for stale
  pre-existing indexed URLs (`config/redirects.php`).
- Content migrated per-page into `content/pages/*.php`, preserving H1s,
  body copy, internal links, and images (self-hosted under
  `assets/images/`, converted to WebP). Known bugs found in the audit were
  fixed (see `docs/audit-findings.md`) — mismatched titles, duplicate brand
  suffixes, duplicate meta descriptions, the About page's missing H1 and
  phone-number typo, and the 5 placeholder blog posts (rewritten with real
  content per owner decision).
- GA4 (`G-SWCYCC0DG8`) and HubSpot tracking preserved sitewide via
  `includes/tracking.php`. Added (didn't have before): Organization +
  LocalBusiness JSON-LD via `includes/schema.php`.
- New lead-capture form (`includes/quote-form.php` +
  `forms/handle-quote.php`) replacing the old Hostinger-proprietary form
  backend — currently uses PHP `mail()` to `andrew@cleangreenturf.com`;
  needs SMTP credentials to harden deliverability before launch (see
  requirement #19 and the TODO in `forms/handle-quote.php`).
- `sitemap.xml` / `robots.txt` regenerated for the new architecture
  (`bin/generate-sitemap.php`).
- **Full design overhaul** (per explicit owner request, after the initial
  plain-stylesheet rebuild): self-hosted Plus Jakarta Sans, a real
  green/amber design system, a generic hero-band extraction (leading
  image+H1 pulled out of migrated content via regex in
  `templates/page.php`/`home.php`/`article.php` — no per-page rewrites
  needed), sitewide trust bar + top bar, inline SVG icons
  (`includes/icons.php`), a reusable bottom CTA banner, and a mobile sticky
  call/quote bar. See `docs/audit-findings.md` "Design overhaul" section.
- While rewiring the homepage's form, found and fixed a real gap: `/contact`
  and the Google-Ads landing page `/dfw-turf-cleaning-request-ga` had **no
  working form at all** after the initial migration pass (only the homepage
  got one first time round). Both now have the real quote form.

**Not done yet:**
- Deployment to Hostinger isn't wired up (decided: Hostinger's Git
  integration; needs the user to actually connect the repo in hPanel and
  confirm the deploy path matches this repo's root-as-webroot layout).
- SMTP for the form handler (currently PHP `mail()`, not production-hardened).
- Phase 2 (new Repair/Installation pages, deeper location-page strategy,
  breadcrumb schema) — intentionally deferred per requirement #34.
- Full pre-launch crawl (#28) and old-vs-new comparison (#29) against the
  *live* new site once it's actually deployed somewhere reachable.
- Google Search Console verification carryover (no verification meta tag
  was found on the live site — needs to be confirmed via DNS or GSC directly).
