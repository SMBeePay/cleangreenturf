# Pre-Migration Audit Findings — Flagged for Review

Per migration requirement #33 ("ask before destroying SEO value"), these are
existing issues/oddities found on the live production site during the initial
crawl. Each needed a decision before or during the rebuild. Default action
when not otherwise specified: preserve exactly as-is and carry the defect
forward unchanged (never silently "fix" during migration).

## Resolution status (updated once the rebuild started)

1. Mismatched titles (austin-tx, turf-sports-field-maintenance) — **fixed**
   in `config/routes.php`.
2. Duplicate brand suffix in titles — **fixed**.
3. Duplicate meta description (`/contact` vs `/dfw-turf-cleaning-request-ga`)
   — **fixed**, both now unique; `/dfw-turf-cleaning-request-ga` also got a
   unique title (it was duplicating `/contact`'s title too, found after this
   doc was first written).
4. `/about` missing H1 — **fixed**, added `<h1>About Clean Green Turf</h1>`.
5. Five placeholder blog posts — **resolved per owner decision**: wrote real,
   unique articles for all five (see `content/pages/blog-post{,1,2,3,4}.php`).
6. Phone typo on `/about` — **resolved per owner decision**: corrected to
   469-796-0034 (the TX number) in both regional blocks, not 925-238-3178 as
   originally recommended. Also fixed an additional typo found while editing:
   the TX address block read "McKinney, **CA** 75071" (wrong state code).
7. No LocalBusiness/Organization schema — **added** (`includes/schema.php`),
   additive only, nothing removed.
8. Proprietary lead-form backend — **rebuilt**: `includes/quote-form.php` +
   `forms/handle-quote.php`, emailing andrew@cleangreenturf.com per owner
   decision. Uses PHP `mail()` for now; needs SMTP before launch.
9. Images on the builder's CDN — **done**: all 67 referenced images
   downloaded, converted to WebP, self-hosted under `assets/images/`, with
   width/height attributes and lazy-loading on everything except each page's
   first (hero) image.
10. Stale unredirected 404s from an earlier site iteration — **fixed** for
    the two with a clear equivalent (`config/redirects.php`).
    `/artificial-turf-repair` intentionally left un-redirected — no
    equivalent page exists yet (that's Phase 2).

More things found while migrating (not in the original list):
- The homepage hero image had `alt="black and white bed linen"` — a leftover
  generic stock-template image unrelated to turf. Swapped for a real turf
  photo already used elsewhere on the page, as part of the visual redesign.
- Two galleries (`/contact`, `/dfw-turf-cleaning-request-success`) rendered
  the same set of photos twice in different orders — a builder desktop/mobile
  duplication artifact, not real content. Deduplicated during migration.
- **`/contact` and `/dfw-turf-cleaning-request-ga` had no working form at
  all** after the initial content extraction (form fields aren't "content"
  so the extraction script correctly dropped them, but a real form was never
  added back for these two pages — only the homepage got one in the first
  pass). Both now have the real quote form. `/dfw-turf-cleaning-request-ga`
  is a Google Ads landing page, so this was a real, live gap in ad-driven
  lead capture until fixed — worth independently confirming no ad spend ran
  against a brokenly-migrated version of this page.
- Several pages used `<strong>` text as pseudo-headings instead of real
  heading tags (e.g. "A Few Of Our Past Jobs", the 3 process steps on the
  Google Ads landing page) — same pre-existing pattern as the homepage's
  process steps. Restored as proper `<h4>`s during the design pass, same
  copy, same emphasis.

## Design overhaul (post-launch-prep, per owner request)

The initial rebuild (above) intentionally used a plain, low-risk stylesheet
to validate the migration first. The owner then requested a full visual
redesign ("world class... clean, polished, professional... maximize SEO and
lead capture"). That pass added, all without touching any preserved copy,
headings, or URLs:
- A real design system: self-hosted Plus Jakarta Sans (no external font
  request), a forest-green/amber color system, consistent spacing/type scale.
- A proper hero band on every page (built generically in
  `templates/page.php`/`home.php`/`article.php` by extracting each page's
  existing leading image + H1 via regex — no per-page content rewrites).
- Sitewide trust bar, top utility bar, redesigned header/footer with inline
  SVG icons (`includes/icons.php`) instead of a text-only footer.
- A reusable bottom-of-page CTA banner (`includes/cta-banner.php`) and a
  mobile sticky call/quote bar (`includes/mobile-cta-bar.php`) — standard
  high-conversion patterns for home-service sites.
- Testimonials restyled as real cards; the homepage's 3 process steps
  restyled as icon cards with a stats strip.

### Design v2 — reference-driven typography/color overhaul

The first design pass (above) was judged "still ugly and boring." The owner
pointed to two reference sites (dfwparkinglotservices.com,
eastdfwtrailers.com) as the bar to hit. Both were fetched and their actual
CSS analyzed directly (colors, font-family declarations, type scale) rather
than guessed from a screenshot. Both independently use the same formula:
a bold condensed/uppercase display font for headings (Oswald / Bebas Neue),
a warm off-white ground (not sterile white), a single punchy saturated
accent color used sparingly, and sharp/minimal border-radius instead of soft
pill shapes — the opposite of the first pass's soft rounded Plus-Jakarta-Sans
sentence-case look.

Applied that formula while keeping Clean Green Turf's own green brand
identity (didn't copy the reference sites' red/orange as primary — green
stays primary, a burnt-orange became the accent/CTA color):
- Self-hosted Oswald (headings, uppercase, tracked) alongside the existing
  Plus Jakarta Sans (body) — `assets/fonts/oswald-*.woff2`.
- Warm cream background (`--cream`) replacing pure white.
- Punchier, more saturated green + a real burnt-orange accent
  (`--rust-*`) replacing the previous soft pastel amber.
- Sharper corners sitewide (4-10px, was up to 22px) with a colored
  top/left accent border on cards, the quote form, and testimonials.
- Dark forest header/trust-bar/footer/CTA-band (was light/white header)
  for higher contrast, matching both reference sites' dark-bar pattern.
- Much larger, bolder hero headline scale.
- Removed a duplicate "FREE QUOTE" button that ended up sitting right next
  to the header's real CTA button once the nav got bolder — nav now only
  carries page links, the CTA button is the single free-quote affordance
  in the header (mobile still gets it via the sticky bottom bar).

No structural/template changes were needed for this pass — same class
names throughout, only `assets/css/style.css` (and `includes/navigation.php`
for the duplicate-button fix) changed.

### Design v3 — accent color pulled from the actual logo

Owner feedback: didn't like the burnt-orange accent from v2. Asked for
something that contrasts with the greens already in use — "could even be a
brighter green" — and to reference the actual logo to stay on-brand.

Sampled the logo pixels directly (`assets/images/logo.png`) instead of
guessing: it's built from a deep teal-forest (`#0a4650`) and a soft sage
(`#b4dcbe`), not the pure black-green this project had been using. Rebuilt
the palette around those two real brand colors:
- `--forest-950/900/800` shifted from a near-black green to the logo's
  actual teal-forest hue.
- `--green-700/600/500` (mid-tone, card borders/step icons/links) shifted
  toward the same teal-green family.
- The accent variable was renamed `--rust-*` → `--accent-*` and recolored
  to a bright, saturated green (`#22c05a`) — distinct enough in brightness
  to still pop as the CTA/highlight color against the darker teal-greens,
  without introducing an unrelated hue like the orange did.

Same mechanism as v2: only CSS variable values changed, every component
rule already referenced the variable names, so no template/markup changes
were needed beyond the rename itself (self-contained to
`assets/css/style.css`).

---

## Original findings (as first written, before the fixes above)

## 1. Mismatched `<title>` tags (existing bugs, likely from the AI builder)

- **`/austin-tx`** — `<title>` reads *"Addison-TX-turf-cleaning | Clean Green
  Turf"* but the page's actual H1/content is about Austin, TX. This looks like
  a copy/paste error, not an intentional title.
- **`/turf-sports-field-maintenance`** — `<title>` reads
  *"Rockwall-TX-turf-cleaning | Clean Green Turf"* but the page content is
  about artificial turf field testing/performance analysis (sports fields),
  unrelated to Rockwall.

These are duplicate-title bugs today (both duplicate another page's title)
and don't describe their own page — likely hurting rather than helping
current rankings. **Recommend fixing** (write accurate, unique titles for
these two pages) rather than preserving the bug, but flagging since title
changes are explicitly called out as sensitive.

## 2. Duplicate branding in title tags

`/dallas-tx-turf-cleaning`, `/frisco-tx-turf-cleaning`,
`/mckinney-tx-turf-cleaning`, `/texas-service-areas` all end in
`"... | Clean Green Turf | Clean Green Turf"` (brand suffix appended twice).
Recommend a one-time cleanup to a single `" | Clean Green Turf"` suffix.

## 3. Duplicate meta descriptions across unrelated pages

`/contact` and `/dfw-turf-cleaning-request-ga` share the exact same title
*and* meta description word-for-word. `/dfw-turf-cleaning-request-ga` looks
like a dedicated Google Ads landing page — worth a unique description, or
confirm whether it should stay a near-duplicate of `/contact` intentionally
(e.g. for consistent ad-to-landing-page messaging).

## 4. `/about` has zero `<h1>` tags

No H1 at all on the About page — every other crawled page has exactly one.
Recommend adding one accurate H1 during rebuild (additive, not a content
change).

## 5. Five placeholder/thin "blog" pages, live and indexed

`/blog-post`, `/blog-post1`, `/blog-post2`, `/blog-post3`, `/blog-post4` are
all **identical unfinished template content**: title "Your blog post", meta
"Blog post description.", body text literally "My post content." (184 words
each, word-for-word the same across all five). These are indexed in
`sitemap.xml` today.

This is a real decision point, not a cleanup call I'll make unilaterally:
- **Option A** — Write real, unique content for each (turns 5 dead pages into
  5 real content assets; safest for any existing backlinks/impressions).
- **Option B** — Consolidate into a smaller number of real blog posts and
  301 the rest to the closest match or to a new `/blog` index.
- **Option C** — Leave them out of the new sitemap and `noindex` them (only
  if Search Console shows they get zero impressions/clicks today — need to
  check before choosing this).

**Needs your input** — see open questions below.

## 6. Phone number inconsistency on `/about`

Body copy shows **925-378-3506**, which matches neither the official TX
number (469-796-0034) nor the official CA number (925-238-3178). Likely a
typo of the CA number. Flagging per NAP-consistency requirement (#15) —
confirm the correct number before we carry anything forward.

## 7. No LocalBusiness/Organization schema anywhere

Only `WebSite` (homepage), `WebPage` (most interior pages), and `Article`
(blog posts) schema exist. There's no `LocalBusiness` schema carrying NAP,
service area, hours, or aggregate rating — a straightforward **Phase 2
improvement** (additive, doesn't touch anything existing) once Phase 1
preservation is verified.

## 8. Lead-capture forms have no discoverable backend we can carry over

Covered in detail in `business-info.md` — the forms POST to a
Hostinger-Website-Builder-proprietary endpoint
(`builder-backend.hostinger.com`). This **must** be rebuilt as a new PHP
form handler; it's not a preservation question, it's an unavoidable rebuild,
but the *destination* of leads (who/where they currently notify) needs to
match what you have today. See open questions.

## 9. All images and the favicon are hosted on the builder's CDN

`assets.zyrosite.com` / `cdn.zyrosite.com` — these will stop resolving once
we're off Hostinger Horizons regardless of what we do with page URLs. Every
image needs to be downloaded and self-hosted (or re-sourced) as part of the
rebuild. This isn't optional and isn't a URL-preservation issue (image *page*
URLs are unaffected), but it's real work: ~27 images on the homepage alone
across 36 pages, several already missing alt text (worth fixing during the
re-host since we're touching every image anyway).

## 10. Stale indexed URLs from an even earlier site version (pre-existing, not caused by this migration)

Web search turned up cached titles for URLs that **404 on the live site
today**: `/tx-turf-cleaning-service-areas`, `/service-area-dallas`,
`/artificial-turf-cleaning`, `/artificial-turf-repair` (and a `www.`-prefixed
variant of one). These aren't in the current sitemap and aren't linked
anywhere on the live site — they appear to be leftovers from a prior
iteration that were never redirected. Not something this migration broke,
but worth fixing now: we can 301 these to their closest current equivalent
(e.g. `/tx-turf-cleaning-service-areas` → `/texas-service-areas`) to recover
any residual link equity/impressions, per requirement #3.

## California service discontinued (owner-directed business change, not an SEO/migration decision)

The business no longer services California — this is a real operational
change, not a design or SEO call, and it came from the owner directly.
Explicit instruction: don't remove the CA city pages (keep them live for
search/SEO), but stop presenting California as a currently-served region
anywhere prominent (NAP blocks, schema, trust bar, nav).

What changed:
- **Removed entirely**: the CA `LocalBusiness` JSON-LD entry
  (`includes/schema.php`) — this was the one place actively telling search
  engines "we have a business location/service area in California," which
  is now factually wrong and worth being strict about.
- **Removed the CA address/phone** from the sitewide footer NAP block
  (`includes/footer.php`) and the top-bar service-area line
  (`includes/top-bar.php`).
- **Removed the "California Service Areas" top-nav link**
  (`includes/navigation.php`) and the trust-bar's "Bay Area" mention
  (`includes/trust-bar.php`).
- **`/about`**: removed the California half of "Serving Two Regions" (now
  "Where We're Based," Texas only).
- **Titles/descriptions**: `/about` and `/contact` no longer say "California
  and Texas" (`config/routes.php`).
- **`/blog-post4`**: this article's entire premise was a DFW-vs-Bay-Area
  climate comparison — not preservable, so it's a full rewrite ("How DFW's
  Wild Weather Swings Affect Your Artificial Turf," Texas-only: heat, hail,
  freezes, drought). Same URL/slot, new content and title/description.
  `/blog-post` and `/blog-post2` had one Bay-Area aside each, trimmed to
  Texas-only.

What deliberately did NOT change (per explicit instruction — keep for SEO):
- All 10 CA city pages and `/ca-turf-cleaning-service-areas` are untouched:
  same URLs, same content, same "we clean turf in [city], CA" copy. They're
  no longer linked from primary nav or the sitewide trust bar, but they're
  still reachable via the footer (column heading softened from "CA Service
  Areas" to "California" — still a real link, just framed as a location
  list rather than an active service claim) and `sitemap.xml`, so they stay
  crawlable and don't become orphaned.
- `config/business-info.php` still carries the CA region's address/phone
  and city list — needed for the footer's CA link loop and as a record of
  where those numbers/addresses came from. It's just no longer surfaced as
  an active NAP anywhere.

Open question for the owner: the CA pages themselves still actively invite
calls ("Get a Free Quote" → the same TX phone number, since page-level CTAs
were never region-specific to begin with). If a Bay Area visitor calls
expecting service, that's a real customer-experience gap this change alone
doesn't close. Worth deciding later whether those pages need a brief
"we've paused Bay Area service, but here's what we learned serving this
area" note, get formally noindexed, or stay exactly as-is — flagging rather
than deciding unilaterally, per requirement #33.

## Austin de-emphasized (owner-directed, same treatment as California)

Austin isn't a current priority/viable market — owner instruction was to
stop advertising it prominently but not delete `/austin-tx` (keep it live
for search). Same pattern as the CA discontinuation above, scaled to what
Austin actually had: it was never a separate NAP/schema entry, just a
"DFW & Austin" framing in sitewide chrome plus a full suburb list in the
footer that never had real pages behind it.

What changed:
- **Trust bar**: "Serving DFW & Austin, TX" → "Serving the DFW Metroplex"
  (`includes/trust-bar.php`).
- **Primary nav**: removed `/austin-tx` from the "Texas Service Areas"
  dropdown (`includes/navigation.php`), same as the CA nav-link removal.
- **Footer**: the "Austin Service Areas" column listed 11 suburb cities
  (Round Rock, Cedar Park, etc.) as plain unlinked text — none of them have
  pages, so this was pure promotion of a market that's paused, not a real
  crawlable path to anything. Replaced with a single real link to
  `/austin-tx` (`includes/footer.php`), same shape as the CA column.

What deliberately did NOT change: `/austin-tx` itself is untouched and
still in `sitemap.xml` and the footer, so it isn't orphaned or deindexed —
just no longer pushed sitewide. `config/business-info.php`'s
`service_areas.austin` city list is kept as reference data (same reasoning
as the retained CA region data).

## Top bar removed, hero rebuilt (owner-directed design fix)

Owner feedback on the deployed homepage, with an annotated screenshot:
nothing above the nav, don't advertise Austin (see above), and the hero was
"way too big and empty" — no page content visible without scrolling, no
lead capture up top.

- **Top bar removed entirely** (`includes/top-bar.php` deleted, its
  `require` dropped from `includes/header.php`, its CSS removed). It only
  ever held the email address and social icons — both already live in the
  footer, so nothing was lost, just no longer duplicated above the nav.
- **Real layout bug fixed, not just a size tweak**: `.page-hero__media`
  had no explicit height, so its `<img>` rendered at its natural aspect
  ratio scaled to full page width — on a wide viewport that put the hero
  well over 1000px tall with the H1 anchored at the bottom, i.e. off-screen
  until scrolling. Fixed by giving `.page-hero` an actual capped height
  (`46vh`, `380–560px`) and making the image `position: absolute; inset: 0`
  inside it, so it's cropped to the section instead of dictating the
  section's size. Applies sitewide (every `page-hero` page benefits, not
  just home).
- **Homepage hero specifically** now runs a two-column layout: headline/
  tagline/CTAs on the left, the same quote form that used to sit
  several sections down the page hoisted into a card on the right
  (`templates/home.php`, `includes/quote-form.php` — the form itself is
  unchanged, just extracted via an HTML-comment marker and rendered in the
  hero instead of further down; it is not duplicated, `content/pages/
  __home__.php` is unchanged). This puts what the business does and a way
  to convert in front of the visitor immediately, per the "front and
  center, maybe even a lead capture" instruction. On mobile it stacks
  (image → headline/CTA → form), still no top-bar clutter above it.

## Pre-launch crawl (requirement #28) and SMTP hardening

With the design approved, moved to launch-readiness per the requirements
doc's phased order.

### Pre-launch crawl findings

Ran a full internal-link/metadata crawl against the local build (all 36
routes). Findings and fixes:
- 3 images were missing alt text (`/antioch-ca-turf-cleaning`,
  `/ca-turf-cleaning-service-areas`, `/frisco-tx-turf-cleaning`) — added
  descriptive alt text to each.
- **Real orphan introduced by the California change**:
  `/ca-turf-cleaning-service-areas` lost its only internal link when the
  "California Service Areas" nav item was removed. Fixed by making the
  footer's "California" column heading link to it — restores
  discoverability/link equity without reintroducing prominent promotion.
- Everything else came back clean: no broken internal links, no duplicate
  titles/descriptions, no missing canonicals, exactly one H1 per page, no
  accidental noindex, all canonicals self-referencing and correct.
- `/dfw-turf-cleaning-request-ga` (Google Ads landing page) and
  `/dfw-turf-cleaning-request-success` (post-submit thank-you page) are
  orphans by design — standard practice for a PPC landing page and a
  redirect-only thank-you page respectively. Not treated as defects.

### SMTP hardening

Vendored PHPMailer directly (`vendor/phpmailer/{PHPMailer,SMTP,Exception}.php`,
pulled from the official GitHub source, MIT licensed) rather than requiring
Composer, since Hostinger shared hosting may not have Composer available
and this keeps deployment to a plain file copy.

`config/mail.php` reads SMTP credentials from real server environment
variables first, falling back to a local `.env` file (gitignored;
`.env.example` documents the expected keys and is the only one committed).
`forms/handle-quote.php` now sends via SMTP when credentials are present,
and falls back to PHP `mail()` when they're not — so the form keeps working
either way, it just won't be deliverability-hardened until real credentials
are added.

Tested both paths locally: the `mail()` fallback fails gracefully in this
sandbox (no local MTA, expected — not a code issue) with proper error
handling and no crash; the SMTP path was tested against a deliberately
unreachable fake host and confirmed it fails gracefully (logs the error,
redirects to `/contact?error=send_failed`) rather than throwing an
uncaught exception.

**Still needed from the owner**: a real mailbox/SMTP credentials, placed in
a `.env` file on the actual server (never committed).

**Update**: confirmed `andrew@cleangreenturf.com` is a Google Workspace
address (not Hostinger-hosted email), so the config defaults and
`.env.example` were updated to target Gmail's SMTP relay
(`smtp.gmail.com`) authenticated as that same mailbox via a Google app
password, rather than a separate Hostinger mailbox. This also means leads
get sent from `andrew@cleangreenturf.com` to itself — the same self-
addressed pattern most small-business contact forms use, and it avoids
Gmail's relay rejecting a mismatched From address. Setup steps are in
`.env.example`.

## Real form fields confirmed (owner shared an actual lead notification email)

The first rebuild of the quote form guessed at the checkbox fields since
the live HTML only exposed opaque builder-generated field IDs (see
`docs/business-info.md`'s original note). The owner then shared a real
notification email from the live Hostinger form
(`noreply@notifications.hostinger.com`), which shows the actual fields:
Name, Phone, "Short answer email," Full Address, Approx Size of Your Turf
Area, **"How Frequently Would You Like Your Turf Cleaned?"** (dropdown,
seen with value "Annual"), and a notes field.

There was no services checklist ("Turf Cleaning / Pet Odor Removal /
Infill Replenishment / Commercial") — that was a guess in the first
rebuild pass, and it was wrong. Replaced it with the real cleaning-
frequency dropdown in `includes/quote-form.php` and
`forms/handle-quote.php` (One-Time / Monthly / Quarterly / Bi-Annual /
Annual / Not sure — exact option set inferred from the one visible value,
"Annual," since the full dropdown list wasn't visible in the screenshot).

Also confirmed from that email: the owner's existing workflow is to click
the customer's email address in the notification body to open a reply
addressed to the customer directly (Gmail auto-linkifies plain email
addresses/phone numbers, so this works the same whether the notification
is HTML or plain text). The rebuilt form already does better than this by
setting a `Reply-To` header to the customer's address — the owner can just
hit their mail client's native Reply button and it goes straight to the
lead, no need to click through the body text.
