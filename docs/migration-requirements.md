# Clean Green Turf Website Migration Requirements

## Project Goal

Rebuild the existing Clean Green Turf website at cleangreenturf.com from Hostinger Horizons into a custom PHP/HTML/CSS/JavaScript website hosted on Hostinger.

The new website should give us complete control over the codebase while preserving and ultimately improving the site's existing SEO performance.

This is a website migration, NOT a brand-new website.

The existing production website should be treated as the source of truth for:

* Existing URLs
* Existing page content
* SEO metadata
* Heading structure
* Internal links
* Images
* Schema
* Conversion tracking
* Forms
* Existing indexed pages

Do not remove, rename, redirect, consolidate, or substantially change existing SEO-relevant content without explicitly documenting the change.

---

# 1. ABSOLUTE SEO MIGRATION RULES

These requirements take priority over design or development convenience.

## Preserve Existing URLs

Existing URLs should remain EXACTLY the same whenever possible.

For example:

Existing:

`https://cleangreenturf.com/artificial-turf-cleaning`

New:

`https://cleangreenturf.com/artificial-turf-cleaning`

NOT:

`/services/artificial-turf-cleaning`

Changing the technology from Horizons to PHP does NOT justify changing URLs.

Do not expose `.php` extensions in public URLs.

Preferred:

`/artificial-turf-repair`

Not:

`/artificial-turf-repair.php`

Use routing or `.htaccess` as necessary.

---

# 2. CREATE AN EXISTING URL INVENTORY BEFORE DEVELOPMENT

Before rebuilding the site, crawl or otherwise inventory the existing production website.

Create a migration table containing:

| Existing URL | New URL | HTTP Status | Title | H1 | Canonical | Migration Action |
| ------------ | ------- | ----------- | ----- | -- | --------- | ---------------- |

Every discoverable/indexable existing URL should appear in this inventory.

The default migration action should be:

**KEEP URL UNCHANGED**

Do not assume pages are unimportant simply because they are not linked prominently in the navigation.

Check:

* Current navigation
* XML sitemap
* Internal links
* Search engine indexed URLs when available
* Existing location pages
* Existing service pages
* Blog/resource pages
* Contact/about pages

---

# 3. REDIRECT REQUIREMENTS

If an existing URL absolutely must change, create a server-side:

**301 Permanent Redirect**

Example:

`/old-page` → `/new-page`

Never redirect old pages to the homepage merely because an equivalent page was not recreated.

Redirect each old URL to the most relevant replacement URL.

Avoid:

* Redirect chains
* Redirect loops
* JavaScript redirects
* Meta refresh redirects

Maintain a documented redirect map.

---

# 4. PAGE CONTENT PRESERVATION

Do not accidentally remove existing SEO content during the redesign.

For each existing page, preserve or intentionally improve:

* Main page topic
* Important copy
* H1
* H2/H3 structure
* Service descriptions
* Geographic references
* FAQs
* Calls to action
* Images
* Image alt text
* Internal links

Design changes should not result in major content loss.

If content is rewritten, the new page must continue satisfying the same search intent as the existing page.

---

# 5. TITLE TAGS

Capture the existing `<title>` for every page.

Preserve strong existing titles unless there is an intentional SEO reason to improve them.

Every indexable page must have:

* A unique title
* Relevant primary keyword
* Appropriate geographic targeting when relevant
* Clean branding

Avoid duplicate titles.

---

# 6. META DESCRIPTIONS

Capture existing meta descriptions.

Every important page should have a unique meta description.

Do not automatically generate identical descriptions across city/service pages.

---

# 7. HEADING STRUCTURE

Every primary page should normally have one clear H1.

Maintain logical hierarchy:

H1
→ H2
→ H3

Do not use heading tags simply for visual styling.

Existing keyword-relevant headings should not disappear accidentally during the redesign.

---

# 8. CANONICAL TAGS

Every indexable page should contain an appropriate canonical URL.

Example:

`<link rel="canonical" href="https://cleangreenturf.com/artificial-turf-cleaning">`

Canonicals must use:

* HTTPS
* Preferred hostname
* Final production URL
* No staging domain
* No accidental `.php` extension

Self-referencing canonicals are preferred for normal indexable pages.

---

# 9. INTERNAL LINKING

Preserve existing useful internal links.

The new architecture should improve internal linking between related services and locations.

Important service categories include:

* Artificial turf cleaning
* Artificial turf repair
* Artificial turf installation
* Pet turf cleaning/odor treatment
* Commercial turf services

Relevant city/location pages should link naturally to appropriate service pages.

Service pages should link to relevant service areas.

Avoid creating orphan pages.

Use descriptive anchor text rather than excessive generic anchors such as "click here."

---

# 10. NAVIGATION

The new navigation should provide clear crawl paths to the major service categories.

Changes to navigation should be evaluated for SEO implications.

Do not accidentally remove important pages from sitewide navigation without considering how this changes internal PageRank/link equity.

---

# 11. XML SITEMAP

Generate a clean:

`/sitemap.xml`

It should contain only canonical, indexable URLs.

Do NOT include:

* Redirecting URLs
* 404 pages
* Staging URLs
* Parameter URLs
* Duplicate URLs
* Noindex pages

Update the sitemap whenever important pages are added or removed.

---

# 12. ROBOTS.TXT

Maintain:

`/robots.txt`

Production must allow legitimate search engine crawling.

Never accidentally deploy staging rules such as:

`Disallow: /`

to production.

Reference the production XML sitemap from robots.txt.

---

# 13. NOINDEX / STAGING PROTECTION

The development/staging version of the site must NOT become indexed.

Use appropriate staging protections.

However, any staging-specific blocking/noindex configuration MUST be removed or correctly changed before production launch.

Perform an explicit production check for:

* `noindex`
* `nofollow`
* robots.txt blocking
* HTTP authentication
* staging canonical URLs

---

# 14. STRUCTURED DATA / SCHEMA

Audit existing structured data before migration.

Preserve valid existing schema and improve it where appropriate.

Potential relevant schema types include:

* LocalBusiness
* Organization
* Service
* BreadcrumbList
* FAQPage when eligible and appropriate

Schema must represent actual visible content/business information.

Do not generate spammy schema solely to manipulate search results.

---

# 15. BUSINESS INFORMATION

Keep Clean Green Turf's business information consistent throughout the website.

This includes relevant:

* Business name
* Phone
* Service area
* Website URL
* Contact information

Do not introduce conflicting business information across templates.

---

# 16. IMAGES

Preserve important existing images where practical.

For all images:

* Use descriptive filenames when appropriate
* Include meaningful alt text
* Specify width/height where possible
* Compress images
* Prefer modern formats where appropriate
* Lazy-load below-the-fold imagery
* Do not lazy-load critical above-the-fold imagery if it harms LCP

If an existing image URL receives meaningful search traffic or backlinks, preserve the URL or redirect appropriately.

---

# 17. PERFORMANCE

The custom site should improve performance relative to the Horizons site.

Prioritize:

* Minimal JavaScript
* Minimal dependencies
* Optimized CSS
* Optimized images
* Browser caching
* Compression
* Fast server response
* Avoiding render-blocking resources
* Good Core Web Vitals

Do not introduce a large JavaScript framework unless there is a compelling functional reason.

PHP should primarily handle reusable templates/components and server-side functionality.

---

# 18. MOBILE

Every page must be fully responsive.

Test:

* Navigation
* Forms
* Buttons
* Phone links
* Images
* Tables
* CTAs
* Typography
* Spacing

Mobile content should not be materially reduced compared with desktop simply for design convenience.

---

# 19. FORMS AND LEAD TRACKING

Existing lead-generation functionality must continue working.

Test every:

* Contact form
* Quote form
* Phone link
* Email link
* CTA
* Confirmation message/page

Spam protection should not prevent legitimate submissions.

Do not expose API keys, passwords, SMTP credentials, or sensitive configuration in client-side code or the GitHub repository.

---

# 20. ANALYTICS AND SEARCH CONSOLE

Identify and preserve existing analytics/tracking integrations before launch.

Potential systems may include:

* Google Analytics / GA4
* Google Tag Manager
* Google Ads conversion tracking
* Meta Pixel
* Google Search Console verification

Do not accidentally create duplicate tracking events.

Preserve Search Console verification when possible.

---

# 21. 404 HANDLING

Create a proper custom 404 page.

A missing page must return:

`HTTP 404`

Do NOT return HTTP 200 for missing pages.

The 404 page should provide useful navigation back into the website.

---

# 22. HTTPS AND DOMAIN CONSISTENCY

Production should force HTTPS.

Choose one canonical hostname structure and use it consistently.

For example:

`https://cleangreenturf.com/`

Redirect alternate versions appropriately.

Avoid duplicate accessibility through:

* HTTP
* HTTPS
* www
* non-www
* `.php`
* trailing/non-trailing URL variations

---

# 23. PHP ARCHITECTURE

Use reusable PHP components rather than duplicating sitewide elements.

Suggested structure:

`/includes/header.php`
`/includes/footer.php`
`/includes/navigation.php`
`/includes/cta.php`
`/includes/schema.php`

Centralize common business information and configuration where practical.

Do not hard-code the same information independently across dozens of pages.

---

# 24. URL ARCHITECTURE FOR NEW SERVICES

Existing URLs have priority and should remain unchanged.

For NEW content, create a deliberate SEO architecture before generating large numbers of pages.

Major new service opportunities include:

### Artificial Turf Repair

Create a strong primary DFW repair page targeting relevant repair searches.

Potential supporting topics can include:

* Seam repair
* Turf lifting
* Wrinkle repair
* Drainage problems
* Infill correction
* Burn/damage repair
* Pet damage
* Edge repair

### Artificial Turf Installation

Create a strong primary installation page.

Potential supporting intent includes:

* Residential artificial turf
* Pet turf installation
* Backyard turf
* Putting greens when offered
* Commercial installation

Do not automatically generate hundreds of thin keyword/location pages.

---

# 25. LOCATION PAGE STRATEGY

Location pages should provide genuine value and should not simply swap city names in otherwise identical text.

Priority should be based on actual SEO opportunity and service geography.

Potential DFW markets should be researched before creating the complete location architecture.

Each location page should have useful localized content and strong internal links to relevant services.

---

# 26. SEO CONTENT EXPANSION

This migration should preserve existing rankings FIRST.

SEO expansion comes second.

Do not sacrifice existing working pages simply to create a cleaner-looking URL hierarchy.

New Repair and Installation pages should complement existing Cleaning pages rather than cannibalize them.

Each major page should have a clearly defined primary search intent.

---

# 27. GITHUB REQUIREMENTS

The entire custom website should live in a private GitHub repository.

Use sensible commits.

Never commit:

* Passwords
* SMTP credentials
* API secrets
* Database passwords
* Hostinger credentials
* Private keys

Use environment/configuration mechanisms for secrets.

Maintain a `.gitignore`.

---

# 28. PRE-LAUNCH CRAWL

Before changing cleangreenturf.com to the new website, perform a complete crawl of the staging site.

Check for:

* Broken links
* 404s
* Redirect chains
* Missing titles
* Duplicate titles
* Missing descriptions
* Multiple H1s where unintended
* Missing canonicals
* Incorrect canonicals
* Staging URLs
* Broken images
* Missing alt attributes
* Broken forms
* Incorrect schema
* Orphan pages
* Accidental noindex
* Robots.txt problems
* Sitemap problems

DO NOT launch until critical migration issues are resolved.

---

# 29. OLD SITE VS NEW SITE COMPARISON

Before launch, compare the old production crawl with the new staging crawl.

Every important existing URL must have one of these outcomes:

**200 — preserved**

or

**301 — intentionally redirected to the most relevant replacement**

There should be no unexplained loss of existing indexable pages.

---

# 30. LAUNCH PROCEDURE

Immediately before launch:

1. Back up the existing Horizons website where possible.
2. Save the complete old URL inventory.
3. Save the redirect map.
4. Confirm production domain configuration.
5. Confirm SSL.
6. Confirm canonical URLs.
7. Remove staging-specific noindex/blocking.
8. Confirm robots.txt.
9. Confirm sitemap.xml.
10. Confirm analytics.
11. Confirm forms.
12. Confirm 301 redirects.
13. Confirm 404 handling.
14. Deploy.
15. Crawl the LIVE site immediately after deployment.

---

# 31. POST-LAUNCH VALIDATION

After deployment, verify:

* Homepage returns 200
* Major service pages return 200
* Existing URLs return expected status
* Redirects work
* No redirect chains
* Canonicals point to production
* Sitemap loads
* Robots.txt loads
* Forms work
* Analytics fires
* Mobile site works
* Images load
* Schema validates
* No staging references remain

Submit/update the XML sitemap in Google Search Console if appropriate.

---

# 32. POST-MIGRATION MONITORING

Monitor Google Search Console after migration.

Watch:

* Indexed pages
* Crawl errors
* 404s
* Redirect errors
* Canonicalization
* Rankings
* Impressions
* Clicks
* Core Web Vitals

Do not panic over normal short-term crawling fluctuations, but investigate significant unexpected page/indexing losses.

---

# 33. DEVELOPMENT RULE: ASK BEFORE DESTROYING SEO VALUE

When rebuilding an existing page, do NOT make irreversible SEO decisions merely because they make the code or design cleaner.

If uncertain whether to:

* Delete a page
* Change a URL
* Consolidate pages
* Remove substantial copy
* Remove internal links
* Change canonicalization
* Change heading/topic targeting
* Remove schema
* Redirect an established URL

STOP and flag the decision for review.

Preservation is the default.

---

# 34. CORE MIGRATION PRINCIPLE

The migration should separate two goals:

### Phase 1: Preserve

Reproduce the existing site's SEO assets, URLs, content, tracking and functionality in the new custom PHP architecture.

### Phase 2: Improve

After preservation is verified, improve:

* Page speed
* UX
* Conversion rate
* Technical SEO
* Internal linking
* Content quality
* Repair SEO
* Installation SEO
* DFW location targeting
* Schema
* Site architecture

Do not unnecessarily combine migration and major SEO restructuring into one uncontrolled change.

---

# DEFINITION OF SUCCESS

The migration is successful when:

1. Clean Green Turf is completely independent of Hostinger Horizons.
2. Hostinger functions only as the web host.
3. The site is controlled through normal PHP/HTML/CSS/JS files.
4. The complete source is maintained in GitHub.
5. Claude Code can safely modify the website.
6. Existing important URLs and SEO equity are preserved.
7. Existing analytics and lead tracking continue working.
8. Site performance equals or exceeds the existing website.
9. New Repair and Installation services can easily be expanded.
10. Future service and location pages can be created without relying on a proprietary website builder.

**When in doubt during development, preserve the existing SEO asset and flag the proposed change for review rather than deleting or changing it automatically.**
