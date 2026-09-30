---
target: homepage (resources/views/home.blade.php)
total_score: 23
max_score: 36
na_heuristics: 10
p0_count: 1
p1_count: 2
target_identity: "file:I:\\downloads\\Dlive\\code\\deliveringparcel-New\\resources\\views\\home.blade.php"
target_fingerprint: "sha256:79afe014c3f73b5afa0b03c6aee7520b1b44bcfaec6f74b052b9304c1d2e6565"
target_path: "I:\\downloads\\Dlive\\code\\deliveringparcel-New\\resources\\views\\home.blade.php"
timestamp: 2026-09-27T19-02-32Z
slug: resources-views-home-blade-php
closed: true
---
# Critique: DeliveringParcel Homepage (resources/views/home.blade.php)

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 3 | Country dropdown gives no feedback on zero-match beyond a plain row; "Get Started" has no pending/loading state |
| 2 | Match Between System and Real World | 4 | Vocabulary (Buy For Me, consolidation, named couriers/retailers) matches how actual cross-border shoppers talk |
| 3 | User Control and Freedom | 3 | No "x" clear affordance once a ship-from country is picked |
| 4 | Consistency and Standards | 2 | 4 similarly-styled CTAs ("Get Started", "Create a Request", "Get Started Now", "Get a Quote") - 3 share one destination, 1 doesn't |
| 5 | Error Prevention | 2 | shipfrom is entirely Alpine-owned with no native select fallback - silent failure if JS breaks |
| 6 | Recognition Rather Than Recall | 3 | FAQ defaults to index 1 pre-expanded with no visible reason |
| 7 | Flexibility and Efficiency of Use | 2 | Low relevance for a first-visit persuade page, but the country search filter is a real accelerator |
| 8 | Aesthetic and Minimalist Design | 3 | Same benefits (free address/storage/guarantee/consolidation) restated across 3 separate sections |
| 9 | Error Recovery | 1 | No inline validation-error design anywhere on the hero form |
| 10 | Help and Documentation | n/a | Appropriate for Persuade mode; FAQ is the correct substitute and is present |
| **Total** | | **23/36** | **Acceptable (64%)** |

## Design Specificity Verdict

Not a reskinned template. Copy is domain-fluent (Buy For Me, 200-entry ISO country picker, named couriers/retailers, two-sided shopper/shipper marketplace angle). CSS shows an actual design process: documented contrast rationale for ink-on-orange buttons (~9:1 vs ~2.3:1), physically-reasoned bounded 3D tilt on the globe illustration. Generic spots: the floating accessibility widget (raw #1b6ec2 blue, disconnected from --dp-* tokens) and the WhatsApp bubble both read as bolted on.

Deterministic scan: impeccable detect --json returned [] / exit 0 on home.blade.php - verified genuine (same tool found real issues elsewhere in resources/views/).

Visual overlays: unavailable this run - no working browser automation in this sandbox (Puppeteer navigation timeout against the live URL). All findings are source-derived, not screenshot-observed.

## Overall Impression

Good bones - real copy, real trust data, documented accessibility/motion process - but the page repeats its best three arguments three times instead of once, and puts four similar-but-different CTAs in front of the visitor without explaining why they differ. Biggest opportunity: collapse CTA/label confusion and move trust reassurance next to the hero ask instead of several scrolls later.

## What's Working

1. .dp-console-submit/.dp-btn-accent contrast decision (app.css ~309-325): ink-over-white-on-orange with a documented ~2.3:1 vs ~9:1 rationale, on the highest-stakes button on the page.
2. partials/review-badges.blade.php pulls live Trustpilot/SiteJabber ratings from Setting, renders nothing when unset - exactly the anti-fabrication discipline this project requires.
3. dp-globe-turn's bounded rotateY(-14deg to 14deg) tilt, with a comment explaining why a full spin was rejected - a specific, physically-reasoned motion choice.

## Priority Issues

**[P0] Hero conversion form has no non-JS fallback or visible error state**
Why it matters: shipfrom is a required hidden input entirely driven by Alpine's x-data. If Alpine fails, the dropdown never opens and the field can never get a value - the site's single primary conversion action fails silently.
Fix: render a real select name=shipfrom baseline, progressively enhanced into the Alpine dropdown; add a visible inline error state.
Suggested command: /impeccable harden

**[P1] --dp-navy token is bound to the wrong color - it's cyan, not navy**
Why it matters: --dp-navy is defined as #29abe2 (cyan), not the stated brand navy #0e2a6b. The hardcoded #0e2a6b fallback never fires because the variable IS defined. Nav text and accents render the wrong brand color sitewide.
Fix: rename the token to match its actual value, or correct --dp-navy to #0e2a6b and re-audit every call site.
Suggested command: /impeccable audit

**[P1] Four visually-similar CTAs, three destinations conflated as one label set**
Why it matters: "Get a Quote" (-> /request), "Get Started" / "Create a Request" / "Get Started Now" (all -> route('country')) - a visitor can't tell which do the same thing.
Fix: standardize one label+destination for the primary path, reserve "Get a Quote" for the genuinely separate flow, relabel/merge the rest.
Suggested command: /impeccable clarify

**[P2] Accessibility widget's "High contrast" mode doesn't touch the homepage**
Why it matters: dp-a11y-contrast selectors only target admin-panel classes, never .dp-card/.dp-console/.dp-btn-accent. The one feature built for accessibility-dependent visitors does nothing on this page.
Fix: extend html.dp-a11y-contrast to cover public-site .dp-* classes.
Suggested command: /impeccable harden

**[P2] No skip-to-content link despite a ready anchor already existing**
Why it matters: main id=main exists in fmaster.blade.php but nothing points to it; the floating header nav sits ahead of it in tab order on every page.
Fix: add a visually-hidden-until-focused "Skip to main content" link as the first focusable element in body.
Suggested command: /impeccable harden

**[P3] The same core benefits are stated three times over**
Why it matters: free address/storage/guarantee/consolidation is independently restated across three sections, inflating scroll length and diluting the testimonials section's peak-end effect.
Fix: consolidate into one authoritative benefits block; let the other sections carry distinct information.
Suggested command: /impeccable distill

## Persona Red Flags

**Sam (accessibility-dependent)**: High-contrast toggle is inert on this page (see P2). Separately, .dp-blob-1/.dp-blob-2, .dp-globe-turn, and .dp-marquee-track all run without a prefers-reduced-motion gate - only .dp-about-anim/.dp-about-sheen respect it.

**Casey (distracted mobile user)**: The hero's country picker requires scrolling a ~200-country list or typing a search query before the primary CTA is meaningfully usable, with no "popular: US/UK/EU/Australia" shortcut despite those being the emphasized supported origins.

**Jordan (first-timer)**: The first form field asks "Where are you shopping from?" with no inline explanation of what happens next, while trust answers (guarantee, ratings, testimonials) sit sections below the fold.

## Minor Observations

- .dp-word-rotator/dpWordCycle keyframes exist in app.css but aren't referenced in current hero markup - likely dead CSS.
- Country list uses superseded ISO names (Swaziland, Macedonia FYR, Czech Republic) instead of current names.
- WhatsApp widget copy "Typically replies within a day" undercuts a reassurance touchpoint.
- Real content bug (home.blade.php ~line 767): a retailer-logo entry links to walmart.com but its image/alt text both say "eBay".
- Two near-duplicate FAQ questions with overlapping answers.
- WhatsApp-vs-a11y-button: current numbers don't collide when closed; a stale code comment in dp-a11y.css cites an old WhatsApp position that no longer matches config.

## Questions to Consider

- If the same three benefits are worth saying three times, is that deliberate reinforcement, or has no one decided which section owns each claim?
- Is "Where are you shopping from?" really the visitor's first mental step, or is it "will these people actually get my package to me"?
- Has the intended navy #0e2a6b actually appeared on a live page recently, given --dp-navy currently resolves to cyan everywhere?
