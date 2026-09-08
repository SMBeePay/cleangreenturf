# Pre-Migration Audit Findings — Flagged for Review

Per migration requirement #33 ("ask before destroying SEO value"), these are
existing issues/oddities found on the live production site during the initial
crawl. **Nothing has been changed.** Each needs a decision before or during
the rebuild. Default action if you don't specify otherwise: preserve exactly
as-is and carry the defect forward unchanged (never silently "fix" during
migration).

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
