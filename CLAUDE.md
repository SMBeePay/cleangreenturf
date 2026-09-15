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
  backend, sending to `andrew@cleangreenturf.com`. Sends via SMTP
  (PHPMailer, vendored in `vendor/phpmailer/` — no Composer needed) when
  `config/mail.php` finds credentials (real env vars or a local `.env`,
  see `.env.example`); falls back to PHP `mail()` if none are set yet.
  **Still needs a real Gmail app password** (andrew@cleangreenturf.com is
  Google Workspace — see "Not done yet" below).
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
- **Design v2/v3**: reworked typography/color twice against owner feedback
  — see `docs/audit-findings.md` "Design v2" and "Design v3" for the
  reference-site analysis and the logo-color-sampling that drove the final
  palette.
- **California service discontinued**: removed CA from schema, footer NAP,
  nav, trust bar, and `/about`; kept the 10 CA city pages + overview page
  live and footer-linked for SEO per explicit instruction. See
  `docs/audit-findings.md` "California service discontinued."
- **Pre-launch crawl** (#28) run against the local build: fixed 3 images
  missing alt text and one real orphan page (`/ca-turf-cleaning-service-areas`
  lost its only internal link when the CA nav item was removed — now
  reachable again via the footer's "California" heading). Everything else
  (titles, descriptions, canonicals, H1s, internal links, robots meta)
  came back clean. `/dfw-turf-cleaning-request-ga` and
  `/dfw-turf-cleaning-request-success` are orphans by design (a PPC landing
  page and a post-submit thank-you page respectively aren't meant to be
  linked from navigation).
- **SMTP scaffolding** built (`config/mail.php`, `vendor/phpmailer/`,
  `.env.example`) — just needs real credentials, see "Not done yet."

**Not done yet:**
- **SMTP credentials**: `andrew@cleangreenturf.com` is confirmed Google
  Workspace, so this is a Gmail app password, not a Hostinger mailbox — see
  `.env.example` for the exact steps (turn on 2-Step Verification, generate
  an app password at https://myaccount.google.com/apppasswords). Create
  `.env` from `.env.example` on the server with that password. Never
  commit `.env`. If deploying via Hostinger's Git integration, this file
  needs to be placed on the server directly (it isn't in the repo) —
  confirm a redeploy doesn't wipe it.
- Deployment to Hostinger: in progress. A new (non-Website-Builder) website
  was created in hPanel and connected to this GitHub repo via Git
  integration, but no deploy has run yet — blocked on a Hostinger-wide
  hPanel outage (acknowledged by Hostinger on X, "no data will be lost and
  your websites are not affected," tied to their recent hPanel UI
  redesign). The site itself (temporary `*.hostingersite.com` URL) is up
  and reachable throughout — only the panel UI is affected. Once hPanel is
  usable again: confirm the Git branch is set to
  `claude/cleangreenturf-seo-rebuild-usnvul` (nothing is on `main` yet),
  confirm the deploy path is the site's document root, and click Deploy.
- Phase 2 (new Repair/Installation pages, deeper location-page strategy,
  breadcrumb schema) — intentionally deferred per requirement #34.
- Old-vs-new comparison (#29) against the
  *live* new site once it's actually deployed somewhere reachable.
- **Google Search Console verification — confirmed unaffected.** Owner
  checked GSC directly: verification method is "Domain name provider" (a
  DNS TXT record at the registrar/DNS host), not an HTML tag or file
  served by the website. This migration — including the eventual hosting
  cutover — has no effect on it. No action needed.
