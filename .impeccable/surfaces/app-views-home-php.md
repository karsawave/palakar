---
version: 1
slug: "app-views-home-php"
primary_target: "app/views/home.php"
related_targets: ["app/views/layouts/main.php","public/assets/css/main.css"]
---

# Welcome page (app/views/home.php)

Scope: first screen after install. Mode: persuade (orient and act).
Audience: Indonesian PHP developers, Panada 1.x users, international evaluators, students.
Action: open the guestbook demo; secondary: check requirements, edit this page, read a core file.
Proof: the plot and the catalog are drawn from the real files in panada/ on every request.
Constraints: plain PHP, no JS, no external fonts/images, no build, CSS < 6 KB, WCAG 2.2 AA, light + dark page ground, 360px.
History: card grid and canon split hero rejected as generic; catalog sleeve chosen (bolder re-roll); a louder pass (giant wordmark, tick column, framed yellow CTA, spec table) rejected as too much; the quiet version below was approved.

## Direction contract

THESIS: The core is the record: one quiet image of the whole framework, drawn from its files. Refuse ornaments around it; the page stays restrained.
OWN-WORLD: Matte black sleeve (black in both schemes) on a grey board ground; white hairline ridge plot; PLK catalog numbers in small spaced caps; folder color codes only in the catalog legend and as the plot hover; square-letter wordmark only in the header; no rounded cards, shadows, gradients.
STORY: The visitor sees every file of panada/ in one picture, learns it is small enough to read in one evening, then takes PLK 01: the guestbook demo.
FIRST VIEWPORT: Header wordmark left, Docs/GitHub right. Sleeve in two columns from 52rem: left 19rem text (H1 "Palakar X.Y.Z" light weight, one muted paragraph, PLK 01 entry ruled at the bottom); right the plot with full-width hairlines and a one-line caption. Phones stack text above plot.
FORM: challenger brand-identity-canon-saville-catalog-sleeve (bolder re-roll 1), then quieted at user request. Seed key 61d7f5f7.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
