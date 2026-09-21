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

## Hero form reverted, cache-busting added, services teaser (owner feedback round 2)

Owner reviewed the deployed "top bar removed, hero rebuilt" change above and
sent a screenshot of the *live* site still showing the old broken hero (huge
empty image, no visible text) even though the top bar was gone and the trust
bar already said "Serving the DFW Metroplex" — i.e. the new PHP/HTML had
deployed, but the hero still looked broken.

Root cause: `includes/seo-head.php` linked `/assets/css/style.css` with no
cache-busting parameter. Hostinger's front end (Cloudflare) was serving the
pre-fix stylesheet indefinitely regardless of deploys, so the new hero
markup was rendering against old CSS. Fixed by appending `?v=<?=
filemtime(...) ?>` to the stylesheet link — every CSS change now forces a
fresh fetch on the next deploy.

Separately, actual design feedback on the two-column hero-with-embedded-form
approach from the previous round: rejected. Reverted the homepage hero back
to the same single-column "text overlaid on the image with a gradient"
pattern used by every other page — no form in the hero, just the headline,
tagline, and CTA buttons (`templates/home.php`, and the now-unused
`.page-hero--home` two-column CSS removed). The quote form stays exactly
where it already was, further down the homepage — not duplicated, never
was.

Also incorporated, per the owner mentioning the business is now expanding
into installation and repair, not just cleaning: a new line under the
hero tagline reading "Turf Cleaning · Installation · Repair"
(`.page-hero__services`). This is additive copy only — it does not touch
the preserved H1 or tagline, and it does not create dedicated
`/installation` or `/repair` pages, routes, nav entries, or schema. That
remains Phase 2 scope per requirement #34 and CLAUDE.md's "Not done yet" —
flagging rather than expanding scope unilaterally, per requirement #33.

## Hero image swapped (owner-directed)

Owner didn't like the homepage hero photo. Rather than sourcing something
new, swapped in `img-1438-amq1qlojlqtwkxyo.webp` — already in
`assets/images/`, already used further down the same page, and already
cropped almost exactly to hero-banner proportions (1440×499) — a crisp,
professionally striped/brushed turf lawn with no clutter in frame. Since
that image was already doing a job lower on the page (illustrating "DFW
Artificial Turf Experts"), swapped a different, previously-unused photo
into that spot instead (`bat-photo5-m7v3qvwpl1hjk71q.webp`, a technician
actively pressure-washing next to a clean turf yard) so nothing on the
page ended up duplicated. `content/pages/__home__.php` updated; alt text
rewritten for both to match what's actually in each photo.

## Stray homepage image removed (owner-directed)

Owner flagged a specific image on the homepage, between the process-step
cards and the "Clean Artificial Turf Yards" gallery, as "floating odd."
It was a stock photo (`photo-1565309849855-0e30dde06cb4.webp`, "Lush green
artificial turf bordered by a concrete edge") sized 400×280 — noticeably
smaller than the two real job-site photos immediately above it
(768×538 each), which is exactly why it looked out of place stacked with
them. Removed the `<img>` tag from `content/pages/__home__.php`; the two
consistently-sized photos remain. The unused image file itself was left in
`assets/images/` (unreferenced now, but not worth a separate cleanup pass).

## Hero image was actually collapsed to zero height (real bug, not a cache issue)

Owner kept reporting no hero image after the previous "fixes," including
after a hard refresh, a cache clear, and an incognito window — correctly
refusing to accept "it's your cache" once they'd ruled that out
themselves. That pushback was right: it was never a caching problem. My
own "verification" screenshot from the cache-busting round was wrong too —
I looked at a flat gradient and read it as a photo; it wasn't.

Actual bug: `.page-hero` is a CSS grid container with `align-items: end`
(to bottom-align the headline). `.page-hero__media` was a grid item
(`grid-area: 1/1`) with no explicit height, and its only child (`<img>`)
was `position: absolute` — which takes it out of normal flow and
contributes zero height to its parent. With `align-items: end`, a grid
item without `stretch` sizes to its own content instead of filling the
row, so `.page-hero__media` computed to `height: 0`, and the image's
`height: 100%` then resolved against that zero-height box — also `0`.
Verified directly via computed box dimensions in a headless browser
(`mediaRect.height` was literally `0`), not just by eyeballing a
screenshot this time.

Fix: `.page-hero__media` is now `position: absolute; inset: 0` directly
against `.page-hero` (which is already `position: relative`), instead of
depending on CSS grid stretch behavior it was never getting. Confirmed via
the same computed-dimensions check (now 100% of the hero's height) and
screenshots on both the homepage and an inner location page (the image was
visible on both once this landed — `.page-hero` is shared by every
template).

## Installation and repair mentions sprinkled sitewide (owner-directed)

Owner asked to make sure enough is said about repair work and full turf
installations — up to now, awareness of these two services beyond the
homepage hero's one-line "Turf Cleaning · Installation · Repair" teaser
(added earlier) and the estimate scheduler was thin: real "repair" copy
existed on only 2 pages sitewide, and "installation" mentions were mostly
incidental (referring to other installers, not services offered).

**Scope**: added real, substantive copy to the highest-traffic/highest-
leverage pages and to sitewide elements, rather than rewriting all 36
pages — the ~20 hyper-local city pages are each narrowly optimized for
"[city] turf cleaning" search intent, and stapling installation/repair
paragraphs onto every one of them risks diluting that focus for
comparatively little benefit. Full location-page-strategy work (which
would properly fold in install/repair per-city) is already Phase 2 scope
per requirement #34 — flagging that this pass is deliberately narrower
than a full site-wide rewrite, not an oversight.

**What changed:**
- **Homepage**: new "More Than Just Cleaning" section — three real
  service cards (Cleaning &amp; Rejuvenation, Installation, Repair), each
  with substantive copy and its own CTA (installation links to
  `/schedule-turf-installation-estimate`; repair links to the quote
  form). Not just a copy change — this is the first place on the site
  that actually explains what turf repair covers (seams, sunken patches,
  sun-damaged fibers, pet-dug edges) or what installation covers (base
  prep, drainage, seaming).
- **About page**: added a full paragraph connecting Andrew's original
  installation background (already in the bio) to the business now
  offering installation and repair again, not just cleaning — a natural
  narrative fit, not a bolted-on mention. Updated 3 other sentences on
  the same page for consistency (intro line, closing CTA, "Where We're
  Based" blurb).
- **Contact page**: one-line subhead now mentions all three services.
- **Footer** (sitewide, every page): tagline now reads "...turf cleaning,
  installation, and repair" instead of cleaning-only.
- **`includes/schema.php`**: added a real `hasOfferCatalog` with three
  `Service` entries (Cleaning, Installation, Repair) to the LocalBusiness
  JSON-LD — this is invisible on the page but is exactly the kind of
  structured signal that helps search engines understand the full
  service scope, independent of visible copy.
- **Meta descriptions** for `/`, `/about`, `/contact` updated to mention
  all three services (titles left untouched — retitling for new keyword
  targeting is a bigger ranking decision than a description tweak, and
  not made unilaterally here).

## Mobile header simplified (owner-directed, post mobile-responsiveness check)

Owner asked for a full mobile pass to confirm the site scales correctly.
Ran an automated horizontal-overflow check against all 36 routes at both
390px and 320px viewport widths — zero overflow anywhere — plus a visual
check of the highest-risk pages (home, about, contact form, a location
page, the scheduler's interactive calendar). Everything worked; nothing
was broken.

Off the back of that check, owner asked to simplify the mobile header
specifically: the phone number row and the 4-item trust bar
(5.0 Rated / Pet & Family-Safe / Serving DFW / Free Quotes) were both
always visible on mobile, on top of the logo+hamburger row, pushing real
page content below the fold before a visitor saw anything.

Both are genuinely redundant on mobile, not just visually heavy: the
sticky bottom bar (`mobile-cta-bar`, already shown on every mobile page)
provides the same Call Now and Free Quote actions the header phone
number and button offered. Trust badges aren't actions at all, and
desktop still shows all four with room to spare.

**What changed** (`assets/css/style.css`, `@media (max-width: 900px)`):
- `.site-header__cta` (the phone number + Free Quote button) now
  `display: none` — previously only the button was hidden, leaving a
  bare phone number in the header.
- `.trust-bar` now `display: none`.
- Removed the now-dead `.site-header__phone small { display: none; }`
  rule at the old 560px breakpoint (its parent is hidden at 900px, so it
  was unreachable).

Result: on a phone, the header is just logo + hamburger, and the actual
hero headline/CTA is visible immediately below it instead of after two
extra rows. Desktop is unaffected. Verified with a fresh mobile
screenshot and re-ran both the full route regression and the
horizontal-overflow scan — no regressions.

## About page cleanup + family photo restored (owner-directed)

Owner asked to clean up the text styling on `/about` and add back the
photo of him and his family. Investigating the "styling" complaint found
a real content/CSS mismatch, not just a cosmetic tweak:

- `blockquote img` is styled sitewide as a 56px circular avatar (float
  left, `object-fit: cover`) — the right treatment for an actual customer
  headshot (this is exactly how the homepage's one legitimate testimonial
  photo works). But all three testimonial blockquotes on `/about` had
  images that were **not** customer headshots: a photo of cleaned turf
  (Barbie's review), a family photo with alt text "Andrew Neal, owner of
  Clean Green Turf" oddly attached to a *different* customer's review
  (Marissa's), and a generic stock sports-field photo on an empty 5-star
  review. Cropped into small circles, none of these read as anything
  coherent — this is what looked like broken "text styling." These were
  pre-existing leftovers from the original Hostinger builder site (same
  category as the other mismatched-content bugs already documented above),
  not something introduced during migration.
- Fix: removed the image from all three testimonial blockquotes
  (`content/pages/about.php`) — plain text testimonials read cleanly and
  don't fight the avatar-circle styling meant for real headshots.
- The family photo wasn't actually missing from the site (it was the one
  misattached to Marissa's review above) — but it's now placed properly:
  in the bio section, right after Andrew's pull-quote, sized as a normal
  content photo (not a forced circle) with a real caption. Owner uploaded
  a fresh copy directly to GitHub (`IMG_2815.jpeg`, 640×480) rather than
  reuse the old one already in `assets/images/` (a differently-cropped
  768×791 export of what looks like the same photo) — used the fresh
  upload per that explicit action, converted to WebP at
  `assets/images/andrew-neal-family.webp` (self-hosted WebP is the site's
  standing convention), and removed the stray unconverted JPEG from the
  repo root afterward. The old, now-unused copy was left in place rather
  than hunted down, consistent with how other superseded assets have been
  handled elsewhere in this project.

  **Follow-up fix (same day)**: the first conversion rendered sideways on
  the live page. The source JPEG had EXIF orientation tag 6 (rotate 90°)
  — phones commonly store the sensor's raw landscape pixels plus a
  rotation flag rather than pre-rotating the data, and the initial
  `PIL.Image.open().save()` conversion copied the raw pixels without
  applying that flag. Re-converted with `ImageOps.exif_transpose()` first
  (480×640 after correction, `width`/`height` attributes updated to
  match) — confirmed upright via a fresh screenshot before shipping.

## Turf installation estimate scheduler (owner-directed, new capability)

The business is expanding beyond cleaning into installation. Owner's ask,
based on a friend's company having sold "1000s of installs" this way: a
simple page where a lead picks a day/time for a free in-person install
estimate, gets an email confirmation immediately, and a text reminder the
day before with an easy reschedule link. Owner explicitly chose a fully
custom build over embedding Acuity Scheduling (the real product their
friend used) — no recurring third-party subscription, full control, at
the cost of building booking logic ourselves.

This is new functionality, not a migration of anything from the live site
— nothing here touches the preserved cleaning-business content/URLs/SEO.
It's also, functionally, the start of Phase 2 (new Installation-service
capability, requirement #34's "new Repair/Installation pages") — flagging
that per requirement #33 rather than treating "Phase 1 first" as a hard
blocker against a direct, explicit owner request to build it now.

**What it is:**
- `/schedule-turf-installation-estimate` — public booking page. A vanilla-
  JS month calendar (`assets/js/scheduler.js`, no external calendar
  library) fetches open slots from `scheduler/availability.php` and posts
  a completed booking to `scheduler/book.php`.
- `/reschedule?token=...` — same calendar widget in reschedule mode, plus
  a cancel option. The token is a random 32-hex-char string
  (`bin2hex(random_bytes(16))`), sent only in the confirmation email/
  reminder text, never guessable. `noindex, nofollow` and left out of
  `sitemap.xml` since it's a private per-customer link.
- `/admin/index.php` + `/admin/appointments.php` — the "how does Andrew
  see the calendar" piece. Simple session-login gated by an `ADMIN_PASSWORD`
  in `.env` (same out-of-git-secrets pattern as SMTP), listing upcoming/
  all/cancelled appointments. Deliberately outside the public template
  shell (own minimal HTML) — this is an operator tool, not a marketing
  page, same reasoning as `forms/handle-quote.php` being a standalone
  script rather than a routed page.
- `bin/send-reminders.php` — meant to run daily via a Hostinger cron job
  (hPanel &gt; Advanced &gt; Cron Jobs — this project has no way to set that
  up itself, it's a manual one-time hPanel step). Texts anyone with a
  confirmed appointment tomorrow that hasn't already gotten a reminder,
  with a link to reschedule.

**Storage — SQLite, not MySQL, on purpose:** zero setup on Hostinger
shared hosting, no separate database credentials to configure before this
can go live, same "works out of the box" philosophy as the mail()
fallback in `config/mail.php`. Lives at `data/scheduler.sqlite`, created
automatically on first booking. `/data/` is blocked from direct web
access two ways — the top-level `.htaccess`'s blocked-directories rule
(now includes `data` alongside `config|content|includes|templates|bin|
docs|vendor`) and its own deny-all `.htaccess` — since it holds customer
name/phone/email/address. `/data/` is gitignored (runtime PII, never
committed) with one explicit exception so its `.htaccess` itself still
ships: `.gitignore` has `/data/*` then `!/data/.htaccess`.

**SMTP works today; SMS needs one more setup step, same as the original
quote form did:** booking confirmations and the notification to
`andrew@cleangreenturf.com` reuse the exact SMTP-with-mail()-fallback
pattern from `forms/handle-quote.php` (pulled into a shared
`includes/mailer.php` so `scheduler/book.php` and `scheduler/reschedule.php`
don't each reimplement it) — so booking, confirming, rescheduling, and
cancelling all work fully right now. The day-before **text** reminder is
the one piece that needs real Twilio credentials
(`TWILIO_ACCOUNT_SID`/`TWILIO_AUTH_TOKEN`/`TWILIO_FROM_NUMBER` in `.env` —
see `.env.example` for the ~5-minute signup steps) before it actually
sends; until then `bin/send-reminders.php` logs and skips instead of
failing, exactly like the mail() fallback did before the Gmail app
password was set up.

**Double-booking prevention is server-side, not just UI:** every submitted
slot (new booking or reschedule) is re-derived from business hours/lead
time/blackout dates/booking window and cross-checked against the database
at request time (`scheduler_slot_is_valid_and_open()` in
`includes/scheduler.php`) — a client never gets to just assert a slot is
open. Verified locally end-to-end: booking a slot, attempting to double-
book the same slot (rejected 409), rescheduling (old slot freed, new slot
blocked), cancelling, and attempting to cancel an already-cancelled
appointment (rejected).

**Not done as part of this build** (flagging, not deciding unilaterally):
- Not linked from primary nav or any existing page — reachable only by
  direct URL for now, e.g. from an ad landing page. Owner mentioned having
  "the copy framework for an ads landing page" for turf installation;
  once that's shared, the natural move is a dedicated
  `/[something]-turf-installation` landing page (same pattern as
  `/dfw-turf-cleaning-request-ga`) with its CTA pointing at this
  scheduler — not yet built, waiting on that copy.
- Business hours/slot length/lead time/booking window are in
  `config/scheduler.php` with reasonable defaults (Mon&ndash;Fri 9&ndash;5,
  Sat 9&ndash;1, 60-minute slots, 24hr lead time, 21-day window) — plain
  PHP array, easy to hand-edit, not exposed in any admin UI.
- No cap on how many estimates can be booked in the same slot across
  multiple installers/crews (assumes one estimate visit at a time,
  single-person/single-crew scheduling) — fine at current scale, would
  need a "resources" concept if multiple installers run estimates
  simultaneously.

## Real SMS opt-in checkbox added (compliance, not just Twilio paperwork)

Owner hit Twilio's toll-free verification step, which asks for proof of a
real SMS opt-in flow — and asked for a screenshot to submit. The booking
form at that point had no opt-in checkbox at all: it just collected a
phone number and texted a reminder automatically. Generating a mockup
screenshot of consent language that didn't actually exist on the site
would have been submitting false compliance documentation to Twilio, not
just an inconvenience — so the actual fix was to build the real thing
first, then screenshot that.

**What changed:**
- `includes/scheduler-widget.php`: added an actual SMS-consent checkbox to
  the booking/reschedule form, unchecked by default, with the language
  carriers/Twilio require — message frequency, "Msg & data rates may
  apply," STOP/HELP instructions, and an explicit "consent isn't required
  to book" (required per TCPA — SMS consent can never be a condition of
  service).
- `appointments` table gained an `sms_opt_in` column (`includes/scheduler.php`,
  with a runtime `ALTER TABLE` for any database already created before
  this — the feature may already be live). `scheduler_create_appointment()`
  and `scheduler_reschedule()` now take it; `scheduler_appointments_needing_reminder()`
  filters on `sms_opt_in = 1`, so `bin/send-reminders.php` only ever texts
  someone who actually checked the box — verified locally with one opted-in
  and one opted-out test appointment for the same date, confirming only
  the opted-in one was even returned by the query.
- Confirmation email/on-screen message now says the honest thing
  ("we'll text you" vs. "you didn't opt in, so we won't") instead of
  always claiming a text is coming.
- `admin/appointments.php` got an "SMS OK" column so the owner can see
  opt-in status per booking.

**What to actually submit to Twilio**: their own form says to link the
live page where users opt in, and only fall back to a hosted screenshot
if that page is behind a login or unpublished — this one is neither.
Once this deploys, `https://cleangreenturf.com/schedule-turf-installation-estimate`
is itself the correct thing to paste into their "Opt-in policy proof"
field. A screenshot of the real (not mocked) checkbox was generated and
sent to the owner as a fallback in case they want to submit before the
next deploy finishes.

## Austin and California fully hidden from footer (owner-directed, round 3)

Owner instruction: hide all mention of Austin and California from the
footer, going further than the earlier de-emphasis (which had already
dropped both from schema/NAP/trust-bar/nav but kept one link each in the
footer specifically so `/austin-tx`, `/ca-turf-cleaning-service-areas`, and
the 10 CA city pages stayed reachable by an internal link, not just
`sitemap.xml`).

What changed: removed the "Austin, TX" and "California" columns from
`includes/footer.php` entirely. The footer's `.site-footer__top` grid was
hard-coded to 5 columns (`1.6fr repeat(4, 1fr)`); dropped to 3
(`1.6fr repeat(2, 1fr)`) to match, so the remaining columns (About, DFW
Service Areas, Turf Cleaning Resources) fill the row evenly instead of
leaving a blank gap on the right.

**Flagging per requirement #33, since this crosses into orphan-page
territory**: as of this change, `/austin-tx`, `/ca-turf-cleaning-service-
areas`, and (transitively, since the overview page is their only inbound
link) all 10 CA city pages have zero internal links pointing to them
anywhere on the site. They're still live, still in `sitemap.xml`, and still
`index, follow` — so Google can still find and crawl them via the sitemap —
but internal link equity/discovery to that whole cluster is now effectively
zero. If that's more than intended, the fix is cheap (e.g. one quiet
footer line, or a mention in the relevant TX city pages' copy) — flagging
rather than deciding unilaterally whether that tradeoff is acceptable long
term.

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

## Turf installation sales page (owner-directed, new page)

The homepage's "More Than Just Cleaning" section (added in "Installation
and repair mentions sprinkled sitewide," above) linked its Turf Installation
card straight to the booking scheduler
(`/schedule-turf-installation-estimate`) — a one-sentence blurb into a
calendar with no education in between. Owner asked for a real page in
between: "a page about turf installations as opposed to linking straight
to the estimate scheduler from the home page mention."

Built `/turf-installation` (`content/pages/turf-installation.php`,
`config/routes.php`), a long-form educational/sales page modeled on the
site's existing long-form pattern (`turf-sports-field-maintenance.php`):
why-us copy, a 3-card process overview (base prep, drainage/grading,
seaming/infill), a detailed numbered process list, a photo gallery, a
service-area note, and a link over to turf repair for anyone who lands
here needing a fix instead of a full install. Updated the homepage card's
link to point here instead of the scheduler; this page's own CTAs point
on to the scheduler, so the funnel is now card → education → booking
instead of card → booking directly. Also added one internal link from
`/about`'s installation/repair paragraph, since that paragraph already
mentioned installation by name. Added the route to `sitemap.xml` via
`bin/generate-sitemap.php`.

**Photos**: the owner uploaded 10 new JPEGs directly to the repo root
(same direct-to-GitHub workflow as the family photo earlier in this doc)
— real installation work done by the team. Converted to WebP
(`assets/images/turf-installation-1.webp` through `-9.webp`,
EXIF-orientation-corrected per the process established after the sideways
family-photo bug). Per owner direction, the copy makes no mention of
where any given photo was shot — installation photos, no location
commentary needed.

Three of the ten source photos needed handling before use:
- **`BAT-Photo1.jpeg` excluded entirely** — visible competing company
  logo/watermark ("Bay Area Turf") in the corner, can't be published as
  Clean Green Turf's own work.
- **`BAT-Photo.jpeg` (→ `turf-installation-1.webp`) and `BAT-Photo6.jpeg`
  (→ `turf-installation-4.webp`) dropped from the gallery** — one had a
  Halloween tombstone lawn decoration and tall background conifers, the
  other a distinctive mountain-peak skyline; neither reads as a generic,
  timeless installation photo. Deleted both files rather than leave them
  as unused assets.
- **`BAT-Photo8.jpeg` (`turf-installation-6.webp`) cropped** — the
  original framing included tall palm trees; cropped to the bottom ~65%
  of the frame (turf, patio, and fence only) to keep a genuinely nice
  curved-patio shot without the distinctive palms. New dimensions
  1000×490 (was 1000×750).

Final gallery: `turf-installation-3` (pressure-washing close-up),
`-5` (close-up curved patch), `-6` (cropped patio shot), `-7` and `-8`
(side yards), `-9` (front yard) — six photos, all close-in or without
strongly identifying background elements, plus `-2` as the page hero.

Not done as part of this change, flagged rather than assumed: no nav or
footer link was added for `/turf-installation` — it's reachable via the
homepage card, the About page link, and the sitemap, matching how other
non-nav pages (e.g. the Google Ads landing page) are handled on this site,
but unlike the city pages it isn't yet backed by a "Services" nav entry
since none currently exists. Regenerated `sitemap.xml` (39 URLs, up from
38) and verified all 39 routes return 200 with exactly one `<h1>` each,
plus a mobile (390px) pass confirming no horizontal overflow and that all
gallery images load correctly.

## Turf repair page added, Services nav dropdown added (owner-directed)

Follow-up to the installation page above. Owner asked whether the site
needed a "Services" tab in primary nav to house Cleaning/Installation/
Repair, then confirmed a dedicated repair page was worth building too,
with a genuinely useful piece of business context: many installation
companies don't want to come back out for one-off repair jobs (small,
hard to schedule around a full install calendar), so being the company
that *does* take repair calls is a real differentiator worth stating on
the page, not just implying.

**New `/turf-repair` page** (`content/pages/turf-repair.php`,
`config/routes.php`): why-us copy leading with that differentiator, a
3-card process overview (diagnose, seam/patch repair, refresh/reset), a
bulleted list of common repair issues, a numbered repair-visit process,
an honest "Repair or Replace?" section (isolated issues are repairable;
turf that's uniformly worn across the whole yard is often better
replaced — said plainly rather than upselling every job into a repair),
and CTAs to the quote form (no scheduler for repair — that stays
installation-only). No dedicated repair photos exist yet, so this page
intentionally has no leading image; `templates/page.php` already falls
back to the same plain-gradient hero used on `/about` when a page's `<h1>`
has no image in front of it, so no new hero pattern was needed.

**"Services" nav dropdown** (`includes/navigation.php`,
`assets/css/style.css`): added between "Home" and "Texas Service Areas,"
listing Turf Cleaning (→ `/`), Turf Installation (→ `/turf-installation`),
Turf Repair (→ `/turf-repair`). "Services" has no single overview page of
its own, so its top-level label is a plain `<span>` rather than a link
(only the three dropdown items navigate anywhere) — a small CSS addition
(`.site-nav__dropdown-label`) matches its hover/typography to the other
top-level nav links so it doesn't look inert. Added a
`.site-nav__dropdown--single` modifier (single column, narrower) since
this dropdown only has 3 short items, unlike the 11-item two-column
"Texas Service Areas" dropdown.

Updated both homepage service cards ("More Than Just Cleaning") and the
`/about` installation/repair paragraph to link to their respective new
pages. Regenerated `sitemap.xml` (40 URLs). Verified: all 40 routes
return 200 with exactly one `<h1>` each; mobile (390px) pass confirms no
horizontal overflow on `/`, `/turf-repair`, and `/turf-installation`;
visually confirmed the Services dropdown on both desktop (hover) and
mobile (tap-to-open nav, dropdown always expanded inline as an indented
list under its label, matching the existing Texas Service Areas pattern).

## Zoho CRM integration (owner-directed, new capability)

Owner signed up for Zoho CRM and asked for the site's forms to feed it —
specifically, the turf-installation scheduler should create a lead in the
Turf Installation pipeline, and it's fine for cleaning leads to land in a
Cleaning pipeline, "all info pulls into zoho."

**What was found, not assumed**: the Zoho org (an Enterprise-trial account
created the same day as this work, trial expires 2026-10-02) already had
a fully built-out Deals module — not a blank trial default. It uses
Zoho's single-Deals-module, multi-pipeline design (one "Pipeline" picklist
field, each pipeline value carrying its own Stage picklist via field
mapping) with three pipelines already configured, each with real stages:
- **Turf Installation** (the default pipeline): New Lead → Estimate
  Booked → Estimate Completed → Quote Sent → Follow-Up → Won – Install
  Scheduled → Won – Installed / Lost. Quirk: this pipeline reused Zoho's
  original system Stage field, so the underlying API value for "New Lead"
  is the string `Qualification`, not "New Lead" — display label and
  actual value only match on the other two pipelines. Documented as
  `ZOHO_STAGE_INSTALLATION_NEW` in `includes/zoho-crm.php` so this isn't
  re-discovered the hard way later.
- **Turf Cleaning**: New Cleaning Inquiry → Contacted → Awaiting Response
  → Cleaning Scheduled → Cleaning Completed → Invoice Sent → Paid.
- **Turf Repair**: New Repair Inquiry → Contacted → Awaiting Response →
  Repair Scheduled → Repair Completed → Invoice Sent → Paid.

Also already present: custom Deals fields clearly built for this exact
business (`Estimate_Scheduled`, `Est_Turf_Sq_Ft`, `Cleaning_Status`,
`Service_Line`, `Lead_Channel`, `Install_Crew_Sub`, `Wave_Invoice_Number`,
`Actual_Job_Cost`, `Profit_Margin`, and more). This code only writes to
fields that already existed — no modules, pipelines, stages, or custom
fields were created by this change.

**What this code does**: `includes/zoho-crm.php` is a plain-curl Zoho CRM
v8 REST client (no SDK/Composer, same philosophy as the vendored
PHPMailer and the Twilio-via-curl SMS code) with two operations — refresh
an OAuth access token (cached in `data/zoho-token-cache.json`, already
covered by the existing `/data/` deny-all + gitignore rules) and create a
Deal. Every call is best-effort and never throws: a Zoho outage, expired
token, or missing `.env` config logs an error and returns `false` without
blocking the actual lead email, exactly like the SMTP/mail() fallback
pattern. Wired into:
- **`forms/handle-quote.php`** (the shared quote form on Home and
  Contact) — creates a Deal in Turf Cleaning or Turf Repair depending on
  the new `service` field (see below), after the confirmation email is
  confirmed sent.
- **`scheduler/book.php`** — creates a Deal in Turf Installation with the
  real booked date/time in `Estimate_Scheduled`, after both confirmation
  emails send. This is the one lead source where the appointment date is
  actually known at creation time, unlike the quote form's placeholder
  `Closing_Date` (`+14 days`, since Zoho requires a Closing_Date and none
  of the website's forms collect a real expected-close date).

**Service-routing decision, made after asking the owner**: the shared
quote form (Home, Contact) previously had no way to distinguish a
cleaning request from a repair request — both were "the quote form."
Asked the owner how to route repair through it; the answer: give the
Google Ads landing page (`/dfw-turf-cleaning-request-ga`) its own
separate form instead of sharing the main one, and add a 3-option
dropdown ("Turf Cleaning" / "Turf Repair" / "Both Cleaning & Repair",
defaulting to Turf Cleaning since that's most of current lead volume) to
the shared form.
- New `includes/quote-form-ga.php` — a standalone copy of the shared form
  without the dropdown, `service` fixed to `cleaning` via a hidden field.
  Keeps the paid-traffic landing page's conversion path isolated from
  future edits to the shared form, and avoids adding dropdown friction to
  a page whose whole premise is already "cleaning."
- `includes/quote-form.php` gained the dropdown; its heading changed from
  "Get Your Free Turf Cleaning Quote" to "Get Your Free Quote" since it
  now genuinely serves both services.
- **"Both Cleaning & Repair" is filed under the Turf Repair pipeline**,
  not Cleaning — a judgment call, not something the owner specified:
  repair is the lower-volume, higher-touch service, so routing the combo
  there makes it less likely to get lost in the high-volume cleaning
  queue. The Deal's Description notes that cleaning was also requested.
  Flagging this in case the owner would rather it default the other way;
  it's one `if` branch in `forms/handle-quote.php` to flip.

**Left unmapped, flagged rather than guessed**: neither `Lead_Source` nor
the custom `Lead_Channel` picklist has a clean "Website" or "Google Ads"
value today (`Lead_Channel`'s options are Thumbtack / Referral /
Google/Organic / Repeat Customer / Other — "Google/Organic" would
misrepresent the paid Google Ads landing page as organic traffic, and
doesn't accurately describe organic-site-visit leads either). Rather than
force a misleading bucket, both fields are left unset and the exact
source (page referrer for quote-form leads, "booked via scheduler" for
installation) is written into the Deal's Description instead. Add
"Website" and "Google Ads" as `Lead_Channel` options in Zoho and this is
a one-line change to wire up correctly.

**Also not done, needs the owner's Zoho login**: `Account_Name` and
`Contact_Name` are sent as name-only objects (`{"name": "..."}` for
Account, `{"First_Name", "Last_Name"}` for Contact) relying on Zoho's
documented auto-create-by-name behavior for Deals lookups — this could
not be verified end-to-end against the live org from this environment
(no real API credentials were available here; see `.env.example`'s new
`ZOHO_*` section for the ~5-minute Self Client setup). **The first real
form submission or booking after credentials are added should be checked
in Zoho** to confirm Deals land in the right pipeline with a properly
linked Account/Contact, not just that the HTTP call returns success.

Not attempted: creating the pipelines/stages/custom fields themselves via
API — the Zoho CRM API (and the tools available here) don't expose
pipeline/layout creation; that's a Setup-UI-only operation, moot anyway
since the owner had already built all three out.

### Update: real end-to-end test found and fixed a genuine bug

Once the owner had real Zoho credentials in place, the first live test
(one scheduler booking, one quote-form submission) surfaced exactly the
kind of issue flagged above as unverified — a real one, not a config
problem. Diagnosing it needed a second fix first: **Hostinger's error
log wasn't easy to find** (no log tab under hPanel's PHP Configuration,
no plain `error_log` file at the site root), so `includes/zoho-crm.php`
now also writes every failure (and success) to `data/zoho-debug.log` —
same directory as the scheduler's SQLite db, already blocked from web
access and gitignored — with `curl_error()` captured alongside the HTTP
code for better diagnostics. That surfaced the real error immediately:

```
{"code":"MANDATORY_NOT_FOUND","details":{"api_name":"id",
"json_path":"$.data[0].Contact_Name.id"},"message":"required field
not found","status":"error"}
```

`Account_Name`'s `{"name": "..."}` shorthand was accepted fine, but
`Contact_Name` rejected the `{"First_Name", "Last_Name"}` shape I'd
used and demanded a real Contact record `id` instead.

**First attempted fix (didn't work)**: sent `Contact_Name` as
`{"name": "..."}` too, matching `Account_Name`'s shape. A second live
test with this in place hit the *exact same* `MANDATORY_NOT_FOUND` on
`Contact_Name.id` — so `Contact_Name` on this org's Deals layout doesn't
support the inline auto-create-by-name shorthand at all, regardless of
the object shape sent. (Possible explanation: the "allow adding new
records" setting for a lookup field, found under Setup → Customization →
Modules and Fields → Deals → Contact Name field, likely isn't enabled
for `Contact_Name` — only `Account_Name` — though this wasn't confirmed
by inspecting that setting directly, since fixing it in code was more
reliable than guessing at a UI toggle a second time.)

**Actual fix**: stopped relying on the inline shorthand for either
field. `includes/zoho-crm.php` now has `zoho_upsert_record()` (a generic
upsert-by-dedup-field helper hitting Zoho's `/crm/v8/{module}/upsert`
endpoint), `zoho_upsert_account()`/`zoho_upsert_contact()` (Account
deduped on `Account_Name`, Contact deduped on `Email`), and
`zoho_push_lead()` — the new single entry point both callers use, which
upserts the Account and Contact first and only then creates the Deal,
linked to their real `id`s. `Account_Name` is required on this Deals
layout, so a failed Account upsert aborts the whole push;
`Contact_Name` is optional, so a failed Contact upsert just omits it.
`forms/handle-quote.php` and `scheduler/book.php` both call
`zoho_push_lead()` now instead of `zoho_create_deal()` directly, and the
now-unused `zoho_split_name()` helper was removed (name-splitting moved
into `zoho_upsert_contact()`).

**Requires broader OAuth scope than originally documented**: the
refresh token generated for `ZohoCRM.modules.deals.CREATE` alone can't
call the Accounts/Contacts upsert endpoints. `.env.example` now asks for
`ZohoCRM.modules.deals.ALL,ZohoCRM.modules.accounts.ALL,
ZohoCRM.modules.contacts.ALL` when generating the Self Client code — a
new grant code and refresh token are needed if one was already generated
with the narrower scope.

**One more real bug on the way to verifying this**: after generating a
new refresh token with the wider scope, the very next test still failed
with `OAUTH_SCOPE_MISMATCH`. Cause: `zoho_get_access_token()` caches an
access token in `data/zoho-token-cache.json` for up to ~1 hour to avoid
a refresh-token round-trip on every form submission — that cache doesn't
know the underlying refresh token (and its scope) changed, so it kept
serving the old narrow-scope access token even after `.env` was updated
correctly. No code fix needed for this (it's correct behavior for the
normal case, a refresh token's scope doesn't normally change mid-life) —
just deleting the stale cache file was enough to force a fresh token.
Worth remembering if OAuth scope is ever widened again: **delete
`data/zoho-token-cache.json` after rotating `ZOHO_REFRESH_TOKEN`.**

**Verified end-to-end, live — quote form**: with the cache cleared, a
real quote-form submission created Deal `7612959000000758001` ("Andrew
Neal — Turf Cleaning Quote") in the Turf Cleaning pipeline, Stage "New
Cleaning Inquiry", with both Account Name and Contact correctly linked
(confirmed by finding the record in Zoho's Deals list view, not just
trusting the API's success response).

**A fourth real bug, found testing the scheduler side**: a real
turf-installation-estimate booking failed with a new error:

```
{"code":"MAPPING_MISMATCH","details":{"mapped_field":"Pipeline",
"api_name":"Stage","json_path":"$.data[0].Stage"},"message":
"Pipeline doesn't contain the Stage","status":"error"}
```

Re-pulled the live Deals layout directly from Zoho (rather than trusting
the copy recorded earlier in this doc) and found the actual cause: the
**Turf Installation pipeline is secretly still Zoho's original default
"Standard" pipeline** — renamed for display only. Its Pipeline field's
real stored value is `"Standard (Standard)"`, not `"Turf Installation"`.
Turf Cleaning and Turf Repair were created as brand-new pipelines, so
their stored value happens to equal their display text exactly — which
is exactly why the quote form's Deal (Turf Cleaning) worked on the first
real try while the scheduler's (Turf Installation) didn't: pure
coincidence that Cleaning's display and internal values matched.

Fix: added `ZOHO_PIPELINE_INSTALLATION` (`'Standard (Standard)'`),
`ZOHO_PIPELINE_CLEANING`, and `ZOHO_PIPELINE_REPAIR` constants to
`includes/zoho-crm.php`, and switched all three `Pipeline` field
assignments in `forms/handle-quote.php` and `scheduler/book.php` to use
them instead of hardcoded display strings. **Not yet re-verified with a
second live scheduler booking** after this fix.

**A fifth real bug, on the very next scheduler retest**: the Pipeline
value fix above (bug #4) still failed, this time with:

```
{"code":"MAPPING_MISMATCH","details":{"mapped_field":"Layout",
"api_name":"Pipeline","json_path":"$.data[0].Pipeline"},"message":
"Layout doesn't contain the Pipeline","status":"error"}
```

Re-pulled the live layout data a second time to rule out a stale
assumption: still exactly one Deals layout (`Standard`, id
`7612959000000091023`), and its `Pipeline` field's pick_list_values
still include `Turf Installation` → `actual_value: "Standard
(Standard)"` character-for-character, matching the constant exactly.
So the value was correct; the problem was that `zoho_create_deal()`
never told Zoho which `Layout` to validate `Pipeline` against — the
Deals API doesn't reliably auto-resolve this even when only one layout
exists, especially for an unusual-looking value like `"Standard
(Standard)"`. Fix: added `ZOHO_DEALS_LAYOUT_ID` and had
`zoho_create_deal()` default `Layout` to it on every call (`$fields['Layout'] ??= ['id' => ZOHO_DEALS_LAYOUT_ID]`) rather than requiring
each caller to remember to set it. **Not yet re-verified** with a third
live scheduler booking attempt.

**Correction — the real root cause, found by reading actual records
instead of field metadata**: the Layout fix (bug #5) did not resolve
it — the third live attempt failed with the exact same
`MAPPING_MISMATCH` on `Pipeline`. Rather than guess a fourth time,
pulled several of the org's 97 real pre-existing Deal records directly
(`getRecords`) instead of trusting the field-configuration endpoint's
picklist metadata. Every real record stores `Pipeline` and `Stage` as
**plain display text** — `"Pipeline":"Turf Installation"`,
`"Stage":"Won – Installed"` — not the `pick_list_values[].actual_value`
strings (`"Standard (Standard)"`, `"Qualification"`) that bugs #4 and
#5 were built around. That `actual_value` field is a legacy/reporting
artifact left over from when the pipeline was renamed from Zoho's
original default "Standard" pipeline; it is not what create/read record
calls actually use. **Both prior "fixes" were chasing the wrong value
entirely.**

Real fix: `ZOHO_PIPELINE_INSTALLATION` is now `'Turf Installation'`
(plain text, matching `ZOHO_PIPELINE_CLEANING`/`ZOHO_PIPELINE_REPAIR`'s
existing pattern) and `ZOHO_STAGE_INSTALLATION_NEW` is now `'New Lead'`.
The `ZOHO_DEALS_LAYOUT_ID` default added in bug #5 was kept (harmless,
and good practice regardless) but was never the actual problem. No
Zoho-side configuration change was needed — this was purely a
code-side misreading of Zoho's metadata API. **Not yet re-verified**
with a live scheduler booking using the corrected values.

**Final result — both lead paths confirmed live**: the corrected fix
was verified with a real scheduler booking, which created "Andrew Neal
— Turf Installation Estimate" in the Turf Installation pipeline at
Stage "New Lead," with Account Name and Contact both correctly linked
— visible directly in Zoho's Deals list alongside the earlier confirmed
"Andrew Neal — Turf Cleaning Quote." The Zoho CRM integration is
complete: both the quote form (Cleaning/Repair pipelines) and the
scheduler (Installation pipeline) are pushing real leads into the
correct pipeline with linked Account/Contact records, from real website
traffic, not just successful API responses trusted at face value.

Separately (not a bug): the owner initially expected a customer-facing
confirmation email from the quote form, matching the scheduler's
behavior — the quote form was only ever built to notify the owner, per
its original design (see "Real form fields confirmed," above); the
customer's only acknowledgment is the redirect to the thank-you page.
Flagging in case the owner wants a customer confirmation email added to
the quote form too, matching the scheduler's pattern — not done as part
of this fix since it wasn't what was reported broken.

## Scheduler page layout: calendar moved above the fold (owner-directed)

Owner feedback on `/schedule-turf-installation-estimate`: the centered
heading + trust bullets pushed the calendar below the fold on both
desktop and mobile — "someone should immediately see what they need to
do." Asked for the calendar closer to the top, ideally a two-column
layout with a smaller heading on the left and the calendar on the right.

Restructured `content/pages/schedule-turf-installation-estimate.php`
into three pieces inside a new `.scheduler-hero` grid container: the
intro (H1 + one-line subtext), the calendar widget, and the trust
bullets. New CSS (`assets/css/style.css`) uses `grid-template-areas` so
the three pieces can reorder per breakpoint without touching the HTML:
- **Mobile (default, single column)**: intro → widget → trust. The
  calendar appears immediately after the heading, ahead of the trust
  bullets, so there's no bulleted list to scroll past before reaching
  the actionable part of the page.
- **900px+ (two columns)**: intro and trust stack in a narrower left
  column (0.85fr), the calendar fills a wider right column (1.15fr)
  spanning both rows — heading, subtext, and calendar all visible
  without scrolling on a normal desktop viewport.
- The page's H1 is sized down for this layout (`--step-2` instead of
  the sitewide `--step-4`) via `.scheduler-hero__intro h1` — it no
  longer needs to dominate the page now that the calendar shares top
  billing.

`/reschedule` shares the same `includes/scheduler-widget.php` but wasn't
part of this ask (it's managing an existing appointment, not the
first-visit booking flow) — kept its original simpler centered
`.scheduler-page-header` styling unchanged; that class had to be
restored after an initial pass accidentally deleted it while adding the
new `.scheduler-hero` rules, which would have left `/reschedule`
unstyled. Also restored `.scheduler`'s own `max-width: 720px` (needed
so `/reschedule`'s calendar, which isn't inside the new grid, doesn't
stretch to the site's full 1240px content width) — harmless for the new
two-column layout too, since that grid column already renders narrower
than 720px at realistic viewport widths.

Verified via headless-browser screenshots at 1400px, 900px, and 390px:
no horizontal overflow at any width, and on a 390×844 mobile viewport
the calendar's top edge sits at y≈407 — comfortably the first screen,
no scrolling needed to see it.

## SMTP credentials added; found and fixed a real mojibake bug

Owner added the Gmail app password to `.env`. First live test after
that confirmed SMTP is genuinely active now: the "via
srv569.main-hosting.eu" tag that previously appeared on delivered mail
(a symptom of the `mail()` fallback) is gone — mail now routes through
Gmail's real SMTP relay. This closes out the last item that had been
sitting in "Not done yet" since the initial rebuild.

That same test surfaced a real, previously-latent bug: the email
subject rendered as `New Quote Request â€" Andrew Neal TEST` in Gmail —
mojibake where the em dash should be. Cause: neither
`forms/handle-quote.php` nor `includes/mailer.php` ever set
PHPMailer's `CharSet` property, so it defaulted to `iso-8859-1`.
Confirmed by reproducing locally with `preSend()` +
`getSentMIMEMessage()` (no live send needed): PHPMailer declared the
Subject header as `=?iso-8859-1?Q?...=E2=80=94...?=` while the actual
bytes (`E2 80 94`) are UTF-8 for an em dash — a genuine mismatch, not
just a display quirk. Gmail decoded those bytes per the (wrong)
declared charset and produced exactly the garbled text seen live.

This bug was latent from the start but invisible until now — every
email had been going out via the `mail()` fallback (which already sets
`Content-Type: text/plain; charset=UTF-8` explicitly), so PHPMailer's
default charset was never actually exercised until real SMTP creds
went in. It would have affected **every** transactional email with an
em dash in the subject or body — which is most of them (quote-form
notification, scheduler confirmation, owner notification, reschedule
and cancellation notices all use "—" for visual separation).

Fix: `$mailer->CharSet = PHPMailer::CHARSET_UTF8;` added right after
`new PHPMailer(true)` in both `forms/handle-quote.php` and
`includes/mailer.php` (the shared helper `scheduler/book.php` and
`scheduler/reschedule.php` both call, so one fix covers all scheduler
emails too). Re-verified locally: the same reproduction now produces
`=?utf-8?Q?...=E2=80=94...?=` — charset and bytes match. Not yet
re-verified with another live send, but the local proof is exact
(same PHPMailer code path, same input, correct output).

## Customer confirmation email added; address-truncation concern investigated (no bug found)

Owner asked for two things: (1) an auto-reply "thank you for reaching out"
email to the customer after they submit the quote form, and (2) a check
that the old Hostinger-era issue of the address field getting cut off
(suspected browser autofill mishandling) isn't present in the new
implementation.

**Customer confirmation email.** `forms/handle-quote.php` was refactored
first: it had its own inline PHPMailer/`mail()` block, duplicating (and
now drifting from) the shared `includes/mailer.php` helper already used
by the scheduler — including missing the `CharSet` fix from the mojibake
bug above until this refactor. Replaced it with a call to the same
`send_transactional_email()` helper, so the quote form now inherits any
future fix to that one place automatically.

Added a second `send_transactional_email()` call right after the owner
notification succeeds: a "Thanks for Reaching Out to Clean Green Turf!"
email to the customer's own submitted address, echoing back what they
entered (address, approx size, cleaning frequency, notes as applicable)
and the service type in plain language ("turf cleaning" / "turf repair" /
"turf cleaning and repair"), plus a one-line "we usually reply same-day"
expectation-setter and the business phone number. Sent with `replyToEmail`
left null (unlike the owner notification, which sets the customer as
reply-to) — a reply to this one should reach Clean Green Turf, not the
customer, since PHPMailer's `addReplyTo()` isn't called at all here so it
defaults to the `From` address already set in `mailConfig`.

This follows the same best-effort convention as the Zoho CRM push
directly below it in the same file: the owner notification is the one
send that blocks the response (a lost lead is the failure that actually
matters), the customer confirmation is logged-on-failure but never blocks
the success redirect — a customer not getting a courtesy email shouldn't
strand them on an error page after they already successfully submitted.

Verified locally with a fake `sendmail_path` capturing outgoing mail to a
log file (no real SMTP needed): submitted a full test POST to
`forms/handle-quote.php` and confirmed both emails were sent with correct
recipients and subjects —
`To: andrew@cleangreenturf.com` / `Subject: New Quote Request — Jane
Smith` and `To: jane@example.com` / `Subject: Thanks for Reaching Out to
Clean Green Turf!` — and both bodies rendered complete and correctly
formatted.

**Address-truncation investigation.** Traced the address field end to
end looking for anything that could cut it off:
- The `<input>` for address in `includes/quote-form.php` and
  `includes/quote-form-ga.php` has no `maxlength` attribute, so the
  browser imposes no character cap.
- `assets/css/style.css`'s `.quote-form__row input` rule is `width:
  100%` with no fixed narrow width — nothing visually truncates a long
  value.
- `forms/handle-quote.php` only trims and strips CR/LF from the address
  (`clean_line()`, to prevent header injection) — no `substr()` or any
  other length-limiting call anywhere in the request path. A grep of the
  whole project (excluding `vendor/`) for `substr(` turns up only unrelated
  uses in `config/mail.php`/`config/zoho.php` (stripping quote characters
  around `.env` values) and `templates/*.php` (hero-extraction regex,
  unrelated to form data).
- `autocomplete="street-address"` is used correctly on the single-line
  address field, which is the right autocomplete token for a composite
  address input — not a likely autofill-truncation cause on its own.
- Confirmed in the same local test above: the test address ("123 Main
  St, McKinney TX") arrived complete and untruncated in both the owner
  notification and the new customer-confirmation email bodies.

No truncation risk found anywhere in the current implementation. The
original issue the owner recalled was very likely specific to
Hostinger's proprietary form builder (replaced entirely by this
project — see the file's own docblock) rather than something carried
forward here.

## Zoho CRM lead-attribution fields (Lead_Source, Lead_Channel, Landing_Page_URL, Service_Line)

Owner reported that after the two test Deals landed in the right
pipelines/stages, four fields were still coming through null:
`Lead_Source`, `Lead_Channel`, `Landing_Page_URL`, `Service_Line`. Owner
gave the exact target values: Lead Channel = "Quote Form" for quote-form
submissions, "Scheduler" for scheduler bookings.

Checked the real Deals-module field metadata (`getFields`) before writing
any code, since the Pipeline/Stage bug earlier in this project was caused
by exactly this: a renamed picklist where the API's `actual_value` no
longer matches what's shown in the CRM UI. Same pattern here —
`Lead_Channel`'s "Quote Form" option has `actual_value` "Thumbtack" and
"Scheduler" has `actual_value` "Referral" (both leftover from before the
picklist was relabeled). Confirmed directly against two real Deal records
(`updateRecord` + read-back) that the API accepts and stores the current
**display text**, not the legacy `actual_value` — consistent with the
Pipeline/Stage finding, so no repeat of that earlier mistake:

- `Andrew Neal TEST — Turf Cleaning Quote` (id `...768008`): set
  `Lead_Channel: "Quote Form"`, `Lead_Source: "Website"`,
  `Landing_Page_URL: "https://cleangreenturf.com/contact"`,
  `Service_Line: "Turf Cleaning"` — read back identical.
- `Andrew Neal — Turf Installation Estimate` (id `...768002`): set
  `Lead_Channel: "Scheduler"`, `Lead_Source: "Website"`,
  `Landing_Page_URL: "https://cleangreenturf.com/schedule-turf-installation-estimate"`,
  `Service_Line: "Turf Installation"` — read back identical.

**Lead_Source** picklist also turned out to have real "Website" and
"Google Ads" display options now (this is new since the last audit —
previously noted as not existing). `forms/handle-quote.php` sets
`Lead_Source` based on which page the submission came from: if the
Referer header points at `/dfw-turf-cleaning-request-ga` it's "Google
Ads", otherwise "Website" (the quote form and the GA landing page share
the same handler with no other distinguishing field). The scheduler only
has one entry point today, so its bookings are always "Website".

**Landing_Page_URL** is populated from the request's `Referer` header
(new `zoho_landing_page_url()` helper in `includes/zoho-crm.php`, shared
by both callers), falling back to the relevant page's own URL on the site
domain if no Referer is present. This is the page the form/widget was
actually submitted from, not true first-touch multi-page attribution —
this project has no click-tracking layer to do better than that.

**Service_Line found a real gap, flagged rather than guessed around**:
at the time this was first built, the picklist only had two options —
"Turf Installation" and "Turf Cleaning" — no "Turf Repair". Repair-only
and "cleaning + repair" quote submissions were left with Service_Line
**unset** rather than mismapped to "Turf Cleaning", and the gap was
flagged to the owner as a possible Zoho-side picklist addition.

**Follow-up, same day**: owner added a "Turf Repair" option to the
Service_Line picklist in Zoho (Setup → Customization → Deals →
Service_Line). Confirmed via `getFields` that the new option's
`actual_value` matches its display text exactly (`"Turf Repair"` →
`"Turf Repair"`, no renaming-artifact mismatch this time — unlike
Lead_Channel above). Verified live by writing `Service_Line: "Turf
Repair"` to the same test Deal used earlier and reading it back
identical, then added `ZOHO_SERVICE_LINE_REPAIR = 'Turf Repair'` to
`includes/zoho-crm.php` and wired it into the repair/cleaning+repair
branch of `forms/handle-quote.php`'s `zoho_push_lead()` call. All three
service lines (cleaning, installation, repair) now populate
Service_Line correctly — no more gap.

New constants added to `includes/zoho-crm.php`
(`ZOHO_LEAD_CHANNEL_QUOTE_FORM`, `ZOHO_LEAD_CHANNEL_SCHEDULER`,
`ZOHO_LEAD_SOURCE_WEBSITE`, `ZOHO_LEAD_SOURCE_GOOGLE_ADS`,
`ZOHO_SERVICE_LINE_INSTALLATION`, `ZOHO_SERVICE_LINE_CLEANING`,
`ZOHO_SERVICE_LINE_REPAIR`) follow the same pattern as the existing
Pipeline/Stage constants, each documented with the same
actual_value-vs-display-text warning where it applies. Wired into
`forms/handle-quote.php` and `scheduler/book.php`'s existing
`zoho_push_lead()` calls. Verified with the full 40-route regression
check (no regressions) and live-record updates on real test Deals (exact
values round-tripped through the real API each time).
