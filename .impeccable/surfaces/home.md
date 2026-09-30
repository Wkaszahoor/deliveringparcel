---
version: 1
slug: "home"
primary_target: "home"
related_targets: []
---

## Direction contract

THESIS: The homepage finally locks onto the real Delivering Parcel brandmark instead of an invented palette — orange and cyan-blue, the company's own two-color hexagon chevron logo — and the hero stops leaning on a generic gray stock illustration in favor of an authored, animated, on-brand route/globe scene.

OWN-WORLD: Palette read directly from the provided logo image: orange `#f5951e` (primary accent, dark `#d97a0e`, soft tint `#fdecd8`) and cyan-blue `#29abe2` (secondary accent, dark `#1b87b8`, soft tint `#e3f4fc`), on near-black ink `#14171a` and warm off-white paper `#f9f9f7`. Same `--dp-*` variable names as the last two passes so downstream markup needs no rewiring. Type and shape (Hanken Grotesk + Bricolage Grotesque, sharp small radii) carry over from the prior restrained-corporate pass — only the color world and the hero illustration change this round, per the user's explicit scope ("follow this color scheme AND change hero section").

STORY: A visitor sees the same brandmark colors in the hero that they'd see on the company's own logo — the page finally looks like it belongs to this specific company rather than a generic template in whatever palette was tried most recently.

FIRST VIEWPORT: Hero right column replaces the generic gray stock hero-img.svg with an authored inline SVG scene: a simplified globe/route composition in the brand's orange+blue, with real motion — a parcel travelling along the dashed route line, pulsing origin/destination dots, gentle globe-ring rotation. Left column (headline, proof strip, route console form) keeps its restrained-corporate structure from the last pass, recolored to orange/blue.

FORM: User-pinned brand reference (the actual logo image) — no concept-seed roll; brand-pinned reference beats the roll per the skill's own rule, same as the impeccable.style-referenced pass.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

## Scope note (disclosed to user)

No image-generation or browser-screenshot tool available this session — the hero illustration is hand-authored inline SVG (code-drawn shapes, not a generated raster), and verification is a manual code/output read, not an automated screenshot diff. This is the third full color-scheme change across three requests; recommending the user lock this one as final (it is the only one backed by an actual brand asset) so PRODUCT.md/DESIGN.md can be written once rather than rewritten a fourth time.
