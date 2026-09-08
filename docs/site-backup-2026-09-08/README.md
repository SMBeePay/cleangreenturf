# Pre-migration site backup — 2026-09-08

Raw HTML of every URL in the live `/sitemap.xml` at the time this migration
started, plus the sitemap and robots.txt themselves, captured before any
rebuild work began. This is the backup required by migration requirement #30
("Back up the existing Horizons website where possible") and the raw source
for `docs/url-inventory.md`, `docs/business-info.md`, and
`docs/audit-findings.md`.

- `pages/*.html` — one file per URL (`__home__.html` = the homepage)
- `sitemap.xml`, `robots.txt` — as served by the live site
- `cgt_seo_inventory.json` — structured extraction (title, meta description,
  canonical, headings, schema, internal links, image/form counts) for every
  page, generated from the HTML above

Images and other assets referenced by these pages are NOT included — they're
served from the Hostinger builder's CDN (`assets.zyrosite.com`), which is
external to this backup.
