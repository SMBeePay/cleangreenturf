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
- **First live deployment succeeded**: pushed to Hostinger staging
  (`blueviolet-beaver-954302.hostingersite.com`) via the Git integration.
  Verified live: all 36 routes 200 with exactly one H1 each, static assets
  (CSS/fonts/logo/JS) loading, sitemap.xml/robots.txt serving, real 404s,
  and `config/`/`vendor/` correctly blocked with 403.
- **Post-deploy visual feedback fixes**: owner reviewed the live staging
  site and flagged the top utility bar (redundant with the footer, removed
  entirely), Austin being advertised sitewide (de-emphasized the same way
  California was — nav/trust-bar/footer, page kept live), and a real hero
  layout bug (an unconstrained image height was blowing the hero section
  well past the fold with no visible content). Hero rebuilt: capped height
  sitewide, and the homepage hero now runs headline+CTA next to the
  lead-capture form (hoisted up from lower on the page, not duplicated) so
  what the business does and a way to convert are visible without
  scrolling. See `docs/audit-findings.md` "Austin de-emphasized" and "Top
  bar removed, hero rebuilt."
- **Round 2 owner feedback on the deployed hero**: the two-column
  form-in-hero approach was rejected on sight — owner wanted the classic
  pattern (headline on the image with a gradient, CTA buttons, no
  embedded form) matching every other page. Also surfaced a real bug:
  `style.css` had no cache-busting parameter, so Hostinger's front end was
  serving the pre-fix stylesheet after deploy, making the previous fix
  look like it hadn't shipped at all — fixed with a `filemtime()`-based
  `?v=` query string on the stylesheet link. Also added a "Turf Cleaning ·
  Installation · Repair" line under the homepage hero tagline, since the
  business is expanding into installation/repair — copy only, no new
  routes/pages yet (still Phase 2, requirement #34). See
  `docs/audit-findings.md` "Hero form reverted, cache-busting added,
  services teaser."
- **Austin/California fully hidden from footer (round 3)**: owner asked to
  hide all mention of both from the footer, not just de-emphasize them.
  Removed the two footer columns entirely (`includes/footer.php`) and
  adjusted the footer grid from 5 columns to 3 so it still fills evenly.
  Flagged (not blocked on): `/austin-tx`, `/ca-turf-cleaning-service-areas`,
  and the 10 CA city pages now have zero internal links anywhere on the
  site — reachable only via `sitemap.xml`. Still live, still indexed, just
  worth knowing that's the current tradeoff. See `docs/audit-findings.md`
  "Austin and California fully hidden from footer."
- **Hero image was actually collapsed to zero height (real bug, found
  after wrongly blaming cache)**: owner correctly pushed back after a hard
  refresh, cache clear, and incognito window all still showed no image —
  it was never a caching issue, and my earlier "verified, it's rendering"
  screenshot was a misread of a flat gradient. Real bug: `align-items: end`
  on the `.page-hero` grid container let `.page-hero__media` (a grid item
  with no explicit height) collapse to `height: 0`, since its only child
  was `position: absolute` and contributed no height — the image's
  `height: 100%` then resolved against that zero-height box. Fixed by
  making `.page-hero__media` `position: absolute; inset: 0` directly
  against `.page-hero` instead of relying on grid stretch. Verified with
  actual computed box dimensions in a headless browser, not just a
  screenshot. See `docs/audit-findings.md` "Hero image was actually
  collapsed to zero height."
- **Turf installation estimate scheduler (new capability, owner-directed)**:
  the business is expanding into installation, not just cleaning. Built a
  fully custom (not Acuity-embedded, per owner's explicit choice)
  day/time booking system: `/schedule-turf-installation-estimate` (public
  booking page, vanilla-JS calendar, no external library),
  `/reschedule?token=...` (reschedule/cancel via a random unguessable
  token sent only in emails/texts, `noindex,nofollow`), `/admin/` (simple
  password-gated dashboard to see bookings), and
  `bin/send-reminders.php` (day-before SMS reminder, meant to run via a
  Hostinger cron job — hPanel setup is a manual step, not something this
  project can do itself). Storage is SQLite at `data/scheduler.sqlite`
  (zero setup on shared hosting, same philosophy as the mail() fallback)
  — blocked from direct web access in `.htaccess` + its own deny-all
  `.htaccess`, and gitignored (customer PII) with one explicit exception
  so that `.htaccess` file itself still ships. Booking confirmations and
  owner notifications work today via the same SMTP/mail() pattern as the
  quote form; the day-before **text** needs real Twilio credentials
  before it sends (see "Not done yet"). Double-booking is prevented
  server-side (every slot re-validated against live availability at
  request time), verified end-to-end locally: book, double-book-rejected,
  reschedule, cancel, double-cancel-rejected. See
  `docs/audit-findings.md` "Turf installation estimate scheduler."
- **Turf installation sales page (new page, owner-directed)**: the
  homepage's Turf Installation card was linking straight to the booking
  scheduler with no education in between. Built `/turf-installation`
  (`content/pages/turf-installation.php`) — a long-form page covering the
  install process (base prep, drainage/grading, seaming/infill), a photo
  gallery of real installation work, and CTAs onward to the scheduler.
  Homepage card and one `/about` mention now link here instead of straight
  to the scheduler. Of the 10 newly uploaded photos: 1 excluded for a
  visible competing-company watermark, 2 more dropped from the gallery for
  distinctive background elements (Halloween decor, a mountain skyline),
  and 1 cropped to remove visible palm trees — 6 photos used in the
  gallery plus 1 as the page hero. Route added, sitemap regenerated
  (39 URLs). See `docs/audit-findings.md` "Turf installation sales page"
  for the full writeup.
- **Turf repair page + Services nav dropdown (new page + nav, owner-
  directed)**: owner asked whether a "Services" nav tab was needed to
  house Cleaning/Installation/Repair, then asked for a real repair page
  too (a good business point came up: many installers don't want to come
  back out for one-off repair jobs — being the company that does is a
  real differentiator, so the page leads with that). Built `/turf-repair`
  (`content/pages/turf-repair.php`) — why-us copy, a 3-card process
  overview, common-issues list, numbered repair process, an honest
  "Repair or Replace?" section, CTAs to the quote form. No repair photos
  exist yet, so it uses the same plain-gradient hero as `/about` (no
  leading image). Added a "Services" dropdown to primary nav (Home |
  **Services** | Texas Service Areas | About | Contact) linking Turf
  Cleaning/Installation/Repair; the "Services" label itself has no
  destination page so it's a non-link span, dropdown-only. Homepage's
  repair card and the `/about` repair mention now link here. Route added,
  sitemap regenerated (40 URLs). See `docs/audit-findings.md` "Turf
  repair page added, Services nav dropdown added" for the full writeup.
- **Zoho CRM integration (new capability, owner-directed)**: owner signed
  up for Zoho CRM and asked for the site's forms to feed it, with
  installation-scheduler bookings landing in a Turf Installation pipeline
  and cleaning leads in a Cleaning pipeline. Inspected the actual Zoho
  org rather than guessing: it already has three fully-built Deals
  pipelines (Turf Installation, Turf Cleaning, Turf Repair) with real
  stages and custom fields (`Estimate_Scheduled`, `Cleaning_Status`,
  `Service_Line`, etc.) — none of that was created by this change, only
  written to. Built `includes/zoho-crm.php` (plain-curl Zoho v8 REST
  client, no SDK, same philosophy as vendored PHPMailer/Twilio-via-curl)
  and `config/zoho.php`, wired into `forms/handle-quote.php` (Turf
  Cleaning or Turf Repair Deal, based on a new service dropdown) and
  `scheduler/book.php` (Turf Installation Deal with the real booked
  date/time). Every push is best-effort — a Zoho outage never blocks the
  lead email. Also split the Google Ads landing page onto its own form
  (`includes/quote-form-ga.php`, hardcoded to Cleaning, no dropdown) per
  owner request, keeping the shared form's new 3-option dropdown
  (Cleaning / Repair / Both, defaulting Cleaning) off the paid-traffic
  page. **Quote form live and verified; scheduler fix pushed, not yet
  re-tested.** Real credentials configured, and four real bugs found via
  actual production testing (none caught by local testing, since all
  four needed a live Zoho org to surface):
  1. `Contact_Name` doesn't support Zoho's inline auto-create-by-name
     shorthand on this org's Deals layout (unlike `Account_Name`) — fixed
     by having `includes/zoho-crm.php` upsert real Account/Contact
     records via `zoho_push_lead()` before creating the Deal.
  2. That fix needed broader OAuth scope (`deals.ALL,accounts.ALL,
     contacts.ALL`, not just `deals.CREATE`) — a second Self Client grant.
  3. A stale cached access token from before the scope upgrade masked
     that the new scope had taken effect — fixed by clearing
     `data/zoho-token-cache.json`.
  4. The Turf Installation pipeline is secretly still Zoho's original
     default "Standard" pipeline (renamed for display only) — its real
     `Pipeline` field value is `"Standard (Standard)"`, not `"Turf
     Installation"`. Fixed with named `ZOHO_PIPELINE_*` constants instead
     of hardcoded display strings.
  Confirmed end-to-end for the **quote form**: a real submission created
  "Andrew Neal — Turf Cleaning Quote" in the Turf Cleaning pipeline with
  both Account Name and Contact properly linked (not blank). The
  **scheduler booking** hit bug #4 above on its first real test — the fix
  is pushed but not yet re-verified with a second live booking. Also
  added `data/zoho-debug.log` since Hostinger's hPanel didn't have an
  easy-to-find error log — every push logs success or failure there. See
  `docs/audit-findings.md` "Zoho CRM integration" for the full
  pipeline/stage/field reference, all four bugs and their fixes, and
  remaining judgment calls (no clean "Website"/"Google Ads" value exists
  in Zoho's `Lead_Channel` field yet, so that's left unset rather than
  mismapped; "Both Cleaning & Repair" submissions are filed under Turf
  Repair, not Cleaning).

**Not done yet:**
- **SMTP credentials**: confirmed still not done via a live test — the
  quote form's owner-notification email silently failed to arrive once
  during Zoho testing, and the emails that did arrive showed Gmail's
  "via srv569.main-hosting.eu" tag, meaning they went out through PHP's
  unreliable `mail()` fallback, not real SMTP (`SMTP_PASSWORD` was still
  blank in `.env`). `andrew@cleangreenturf.com` is confirmed Google
  Workspace, so this is a Gmail app password, not a Hostinger mailbox — see
  `.env.example` for the exact steps (turn on 2-Step Verification, generate
  an app password at https://myaccount.google.com/apppasswords). Add it to
  the same `.env` file the Zoho credentials now live in on the server.
- Deployment to Hostinger: staging deploy is live and verified, including
  a real end-to-end test of the quote form and scheduler on live
  infrastructure (see Zoho CRM integration, above) — the remaining gap is
  just the SMTP password (previous bullet). Domain cutover (pointing
  cleangreenturf.com at this hosting) has not happened yet.
- **Scheduler follow-ups**: (1) Twilio credentials for the day-before SMS
  reminder — see `.env.example`'s `TWILIO_*` section for the ~5-minute
  signup; without it the scheduler still fully works (booking, email
  confirmation, reschedule, cancel), it just skips the text. (2) A daily
  Hostinger cron job needs to be set up in hPanel to actually run
  `bin/send-reminders.php` — see that file's header comment for the exact
  command. (3) Now linked from the homepage's Turf Installation card and
  the `/about` installation paragraph via the new `/turf-installation`
  page (see below) rather than directly, plus still reachable by direct
  URL; still no primary-nav entry. Owner mentioned having ad copy for a
  turf-installation landing page, which would naturally link to this
  scheduler once shared. (4) `ADMIN_PASSWORD` needs to be set in `.env`
  before `/admin/appointments.php` is usable.
- Phase 2 (new Repair/Installation pages, deeper location-page strategy,
  breadcrumb schema) — intentionally deferred per requirement #34.
- Old-vs-new comparison (#29) against the
  *live* new site once it's actually deployed somewhere reachable.
- **Google Search Console verification — confirmed unaffected.** Owner
  checked GSC directly: verification method is "Domain name provider" (a
  DNS TXT record at the registrar/DNS host), not an HTML tag or file
  served by the website. This migration — including the eventual hosting
  cutover — has no effect on it. No action needed.
