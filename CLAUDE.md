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
  page. **Both lead paths live and fully verified in the real Zoho org.**
  Real credentials configured, and several real bugs found via actual
  production testing (none caught by local testing, since all needed a
  live Zoho org to surface):
  1. `Contact_Name` doesn't support Zoho's inline auto-create-by-name
     shorthand on this org's Deals layout (unlike `Account_Name`) — fixed
     by having `includes/zoho-crm.php` upsert real Account/Contact
     records via `zoho_push_lead()` before creating the Deal.
  2. That fix needed broader OAuth scope (`deals.ALL,accounts.ALL,
     contacts.ALL`, not just `deals.CREATE`) — a second Self Client grant.
  3. A stale cached access token from before the scope upgrade masked
     that the new scope had taken effect — fixed by clearing
     `data/zoho-token-cache.json`.
  4. **The real one, found on the third scheduler attempt after two wrong
     fixes**: the Turf Installation pipeline's `Pipeline`/`Stage` fields
     were being sent using the field-metadata endpoint's legacy
     `actual_value` strings (`"Standard (Standard)"`, `"Qualification"`)
     — an artifact from when this pipeline was renamed from Zoho's
     original default "Standard" pipeline, not what create/read calls
     actually use. Confirmed by reading real existing Deal records
     directly: they store plain display text (`"Turf Installation"`,
     `"Won – Installed"`, etc.). Fixed `ZOHO_PIPELINE_INSTALLATION` →
     `'Turf Installation'` and `ZOHO_STAGE_INSTALLATION_NEW` →
     `'New Lead'`. No Zoho-side change needed — this was a code-side
     misreading of Zoho's own metadata API, not a data problem.
  Confirmed end-to-end for the **quote form**: a real submission created
  "Andrew Neal — Turf Cleaning Quote" in the Turf Cleaning pipeline with
  both Account Name and Contact properly linked (not blank). The
  **scheduler booking** took three real attempts to get right (bug #4
  above, in its two wrong forms then its correct one) — the corrected fix
  was then verified with a real live booking: "Andrew Neal — Turf
  Installation Estimate" landed in the Turf Installation pipeline at
  Stage "New Lead" with Account Name and Contact both correctly linked.
  Both pipelines confirmed working from real website traffic. Also added
  `data/zoho-debug.log` since Hostinger's hPanel didn't have an
  easy-to-find error log — every push logs success or failure there. See
  `docs/audit-findings.md` "Zoho CRM integration" for the full trail,
  including the two wrong fixes and why they seemed plausible, and
  remaining judgment calls (no clean "Website"/"Google Ads" value exists
  in Zoho's `Lead_Channel` field yet, so that's left unset rather than
  mismapped; "Both Cleaning & Repair" submissions are filed under Turf
  Repair, not Cleaning).

- **SMTP credentials added, real bug found and fixed**: owner added the
  Gmail app password to `.env` — confirmed live, mail now routes through
  Gmail's real SMTP relay (the "via srv569.main-hosting.eu" tag on
  delivered mail, a symptom of the `mail()` fallback, is gone). This
  surfaced a real, previously-latent bug: email subjects with an em dash
  rendered as mojibake (`New Quote Request â€" Andrew Neal TEST`) because
  neither `forms/handle-quote.php` nor `includes/mailer.php` ever set
  PHPMailer's `CharSet` (defaulted to `iso-8859-1`, mismatching the
  actual UTF-8 bytes). Invisible until now because the `mail()` fallback
  already declared UTF-8 correctly — real SMTP creds were the first time
  PHPMailer's own charset handling was ever exercised. Fixed with one
  line (`$mailer->CharSet = PHPMailer::CHARSET_UTF8;`) in both files —
  `includes/mailer.php` is shared by the scheduler's booking,
  reschedule, and cancellation emails too, so one fix covers all of
  them. Verified locally via `preSend()`/`getSentMIMEMessage()`
  reproduction (exact same PHPMailer code path); not yet re-verified
  with another live send. See `docs/audit-findings.md` "SMTP
  credentials added; found and fixed a real mojibake bug."
- **Customer confirmation email added to the quote form**: owner asked
  for an auto-reply "thanks for reaching out" email to the customer,
  plus a check that the old Hostinger-era address-truncation bug isn't
  present here. `forms/handle-quote.php` was refactored to use the
  shared `includes/mailer.php` helper (dropping its own duplicated
  PHPMailer/`mail()` code, so it now inherits the charset fix above
  automatically) and sends a second, best-effort email to the customer
  echoing back what they submitted (address, size, frequency, notes,
  service type) with a "we usually reply same-day" line. Owner
  notification stays the one send that blocks the response; the
  customer email is logged-on-failure, never blocks the success
  redirect. Address-truncation concern investigated and ruled out: no
  `maxlength` on the address input, no fixed-width CSS, no backend
  `substr()` anywhere in the request path — verified with a real test
  submission arriving complete in both email bodies. See
  `docs/audit-findings.md` "Customer confirmation email added;
  address-truncation concern investigated (no bug found)."
- **Zoho lead-attribution fields populated**: owner noticed `Lead_Source`,
  `Lead_Channel`, `Landing_Page_URL`, and `Service_Line` were coming
  through null on both test Deals. Checked real field metadata first
  (same discipline as the earlier Pipeline/Stage bug) and found the exact
  same renamed-picklist trap: `Lead_Channel`'s "Quote Form"/"Scheduler"
  display options have unrelated legacy `actual_value`s ("Thumbtack"/
  "Referral"). Confirmed live against the two real test Deals that the
  API wants the current display text, then wired
  `Lead_Channel` ("Quote Form" / "Scheduler"), `Lead_Source` ("Website" or
  "Google Ads", inferred from the Referer header), and `Landing_Page_URL`
  (also from Referer, via a new shared `zoho_landing_page_url()` helper)
  into both `forms/handle-quote.php` and `scheduler/book.php`.
  `Service_Line` found a real data gap: the picklist only had "Turf
  Installation" and "Turf Cleaning", no "Turf Repair" — left unset (not
  mismapped) for repair-only and cleaning+repair quote submissions,
  flagged to the owner as a possible Zoho-side picklist addition. Owner
  added the "Turf Repair" option same-day; confirmed its actual_value
  matches its display text (no renaming-artifact trap this time) and
  wired `ZOHO_SERVICE_LINE_REPAIR` into the repair branch — all three
  service lines now populate correctly, no more gap. See
  `docs/audit-findings.md` "Zoho CRM lead-attribution fields."
- **Scheduler reminder timing fixed**: owner had Twilio credentials added
  and then flagged two real problems with the original design: a fixed
  daily cron run gives wildly inconsistent notice (~15h for an 8am
  appointment vs ~23h for a 4pm one), and — the real bug — since this
  scheduler's minimum booking lead time is exactly 24 hours, anyone who
  booked shortly after that day's run for what it called "tomorrow" was
  never picked up again and got no reminder at all. Replaced the
  once-daily "find tomorrow's date" check with an hourly rolling window
  (`scheduler_appointments_needing_reminder()` now matches appointments
  23-25 hours out, not a calendar date) — every appointment passes
  through that window exactly once regardless of when it was booked, and
  reminders land at a consistent ~24 hours before the actual appointment
  time. `bin/send-reminders.php` and `.env.example` updated to say the
  Hostinger cron job should run hourly, not daily. Verified locally with
  a direct query test (in-window, out-of-window, already-reminded, and
  both boundary cases all resolved correctly) and the full 40-route
  regression check. See `docs/audit-findings.md` "Scheduler reminder
  timing fixed."
- **Business hours corrected**: owner confirmed Mon-Fri 9am-4pm, no
  Saturday. `config/scheduler.php`'s `hours` array updated (Saturday's
  key removed entirely rather than emptied, matching how an absent
  weekday already makes Sunday unbookable). Verified locally: Saturday
  now returns 0 slots, weekdays return 7 hourly slots 9am through a
  3pm-start/4pm-end last slot. Full route regression re-run — no
  regressions. See `docs/audit-findings.md` "Business hours corrected."
- **Google Calendar sync (new capability, owner-directed)**: owner
  created a dedicated Google Calendar and wants every booked turf
  installation estimate synced to it. Built
  `includes/google-calendar.php` (plain-curl Calendar v3 REST client,
  same no-SDK philosophy as Zoho/Twilio/PHPMailer) + `config/
  google-calendar.php`, using a Google service-account JWT-Bearer flow
  (PHP's built-in `openssl_sign()` for RS256 — no JWT library) so the
  service account only ever sees the one calendar the owner explicitly
  shares with it. Wired into `scheduler/book.php` (creates the event,
  stores the returned event id in a new `gcal_event_id` column) and
  `scheduler/reschedule.php` (moves the event on reschedule, deletes it
  on cancel — both no-op harmlessly if no event id was ever stored).
  Best-effort throughout, logged to `data/gcal-debug.log` like Zoho's
  own debug log. Verified without real credentials yet: a throwaway
  RSA keypair's JWT was accepted and correctly parsed by Google's real
  token endpoint (failed only on "account not found" for the fake
  email — confirms the signing/encoding is correct), and a full local
  book → reschedule → cancel cycle ran clean with Google Calendar left
  unconfigured (today's real state). Owner still needs to complete the
  Cloud Console / service-account setup and add credentials to `.env` —
  see `.env.example`'s new Google Calendar section for the full
  walkthrough, and `docs/audit-findings.md` "Google Calendar sync" for
  the complete writeup.
- **Dynamic availability against Google Calendar**: owner immediately
  followed up asking that availability also reflect anything already on
  their calendar(s) — not just other website bookings. Added
  `gcal_get_busy_periods()` (uses Google's `freeBusy` API, one call
  checks multiple calendars at once) and a `GOOGLE_BUSY_CALENDAR_IDS` env
  var (defaults to just the booking calendar if unset; owner can add a
  personal or second business calendar, shared at "see only free/busy"
  permission — no full event-detail access needed for those). Wired
  through `scheduler_slots_for_date()` (now excludes any slot overlapping
  a busy period), `scheduler_days_with_availability()` (fetches busy
  periods once per month, not once per day), and
  `scheduler_slot_is_valid_and_open()` (the actual server-side booking
  validation, so this isn't just a display-layer filter — a conflicting
  slot is rejected even if someone bypasses the widget). Fails open like
  every other integration here: a Google outage degrades to today's
  DB-only availability rather than blocking bookings entirely, logged to
  `data/gcal-debug.log`. Verified with a direct query test (a fake
  11am-1pm busy period correctly excluded exactly the overlapping slots,
  including the back-to-back boundary case), the `freeBusy` request
  structure confirmed against Google's real API with a throwaway keypair,
  both `scheduler/availability.php` modes smoke-tested with Google
  Calendar unconfigured, and the full route regression. See
  `docs/audit-findings.md` "Dynamic availability against Google
  Calendar."
- **Google Calendar: switched to domain-wide delegation, then verified
  live end-to-end.** Directly sharing the calendar with the service
  account's own email hit a real wall: Google Workspace treats that as
  *external* sharing (different domain than cleangreenturf.com), and the
  org's external-sharing policy capped it at read-only no matter what
  permission was picked — even after the owner (also the Workspace
  admin) raised that policy to its most permissive setting and waited
  well past Google's own "a few minutes" estimate. Switched to **domain-
  wide delegation** instead (Google's own recommended pattern for a
  service account acting on behalf of a Workspace user): a new
  `GOOGLE_IMPERSONATE_EMAIL` config makes the service account act AS the
  owner via a JWT `sub` claim, which sidesteps the external-sharing
  policy entirely and automatically grants access to every calendar he
  owns — no per-calendar sharing needed at all. Owner authorized the
  service account's Client ID in the Workspace Admin console (Security >
  API controls > Domain-wide Delegation) with the `calendar` scope.
  **Verified live end-to-end against the real Turf Install Estimates
  calendar**: created a real test event, confirmed `freeBusy` correctly
  reported it as busy (UTC times matched the booked Central-time hour
  exactly), updated it to a new time, and deleted it — no leftover test
  data. Both Google Calendar sync and dynamic availability are now fully
  working, closing out the two items above. See `docs/audit-findings.md`
  "Google Calendar: switched to domain-wide delegation" and "...verified
  live end-to-end."
- **Lead-notification failsafe added**: owner asked whether a Zoho (or
  other) outage could ever cost him a lead — specifically whether he's
  guaranteed at least an email for every quote-form submission and
  scheduler booking. Audit found two real gaps: `send_transactional_email()`
  only fell back to plain `mail()` when SMTP was unconfigured, not when
  SMTP was configured but genuinely failed at runtime; and
  `scheduler/book.php` never checked the owner-notification email's
  result at all (unlike the quote form, which already does). Fixed with
  three layers: (1) `mail()` fallback now fires on any SMTP failure, not
  just "unconfigured" — one shared function, covers every caller; (2)
  the scheduler now checks and logs a failed owner notification instead
  of staying silent, without rejecting the booking itself (it's already
  safely in the database); (3) a new `record_failed_lead_email()` writes
  full lead details to `data/failed-leads.log` as a true last resort when
  BOTH delivery paths fail. Honest caveat found while testing: PHP's
  `mail()` almost always returns `true` even when real delivery fails
  downstream, so layer 3 mainly catches total local mail-transport
  misconfiguration, not bounces or spam-foldering — a real
  delivery-tracking service would be needed to catch those, which is a
  bigger change than asked for here. Verified: a simulated SMTP failure
  was caught by the `mail()` fallback and actually delivered; a
  simulated total failure (both paths broken) correctly triggered the
  last-resort log; normal happy-path quote-form and booking flows still
  send both their emails with no regression; full route regression
  passed. See `docs/audit-findings.md` "Lead-notification failsafe:
  automatic mail() fallback + last-resort log."

**Not done yet:**
- Domain cutover (pointing cleangreenturf.com at this Hostinger
  hosting) has not happened yet — staging deploy is otherwise fully
  live and verified (quote form, scheduler, Zoho CRM, and now SMTP all
  confirmed working end-to-end on real infrastructure).
- **Scheduler follow-ups**: (1) Twilio credentials added to `.env` by the
  owner — not yet verified with a real live send (see "Scheduler reminder
  timing fixed" above for the query-level local test; the actual Twilio
  API call still needs a real test booking on live infrastructure). (2)
  ~~An **hourly** Hostinger cron job needs to be set up~~ **Done** — owner
  created it in hPanel (Advanced > Cron Jobs): `php
  public_html/bin/send-reminders.php`, "Once an hour (0 * * * *)". Not
  yet confirmed actually firing/sending a real text — that's the next
  step, alongside (1) above. (3) Now linked from the homepage's Turf Installation card and
  the `/about` installation paragraph via the new `/turf-installation`
  page (see below) rather than directly, plus still reachable by direct
  URL; still no primary-nav entry. Owner mentioned having ad copy for a
  turf-installation landing page, which would naturally link to this
  scheduler once shared. (4) `ADMIN_PASSWORD` needs to be set in `.env`
  before `/admin/appointments.php` is usable. (5) Google Calendar sync
  and dynamic availability are both built AND verified live (see above,
  "Google Calendar: switched to domain-wide delegation... verified live
  end-to-end") but only against a local `.env` for testing — the live
  Hostinger server's `.env` still needs the same four values added:
  `GOOGLE_SERVICE_ACCOUNT_EMAIL`, `GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY`,
  `GOOGLE_CALENDAR_ID`, and `GOOGLE_IMPERSONATE_EMAIL`
  (`andrew@cleangreenturf.com`) — see `.env.example` for the full
  values/walkthrough. Until then, the live site's bookings work exactly
  as before: no calendar event created, and availability only reflects
  other website bookings. Optionally also `GOOGLE_BUSY_CALENDAR_IDS` if
  other calendars beyond the booking one should block availability too —
  not needed for the two calendars the owner already owns (impersonation
  already covers those).
- Phase 2 (new Repair/Installation pages, deeper location-page strategy,
  breadcrumb schema) — intentionally deferred per requirement #34.
- Old-vs-new comparison (#29) against the
  *live* new site once it's actually deployed somewhere reachable.
- **Google Search Console verification — confirmed unaffected.** Owner
  checked GSC directly: verification method is "Domain name provider" (a
  DNS TXT record at the registrar/DNS host), not an HTML tag or file
  served by the website. This migration — including the eventual hosting
  cutover — has no effect on it. No action needed.
