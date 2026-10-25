---
name: Palakar
description: Sample UI of a PHP framework small enough to read in one evening, drawn as a quiet matte black catalog sleeve.
colors:
  sleeve: "#111111"
  sleeve-ink: "#f2f2f2"
  sleeve-muted: "#b3b3b3"
  ground: "#e8e8e6"
  ink: "#111111"
  muted: "#4a4a4a"
  rule: "#b9b9b6"
  error: "#a8200d"
  ground-dark: "#1d1d1c"
  ink-dark: "#ededeb"
  muted-dark: "#a9a9a6"
  rule-dark: "#3d3d3b"
  sleeve-dark: "#0b0b0b"
  error-dark: "#ff8f80"
  code-0: "#ffd400"
  code-1: "#ff6a13"
  code-2: "#e4002b"
  code-3: "#c724b1"
  code-4: "#0085ca"
  code-5: "#00a651"
  code-6: "#9ea2a2"
typography:
  display:
    fontFamily: "system-ui, Segoe UI, Roboto, sans-serif"
    fontSize: "clamp(2rem, 4vw, 2.75rem)"
    fontWeight: 300
    lineHeight: 1.15
    letterSpacing: "-0.02em"
  headline:
    fontFamily: "system-ui, Segoe UI, Roboto, sans-serif"
    fontSize: "2em"
    fontWeight: 700
    lineHeight: 1.15
  body:
    fontFamily: "system-ui, Segoe UI, Roboto, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "system-ui, Segoe UI, Roboto, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    letterSpacing: "0.14em"
  caption:
    fontFamily: "system-ui, Segoe UI, Roboto, sans-serif"
    fontSize: "0.8rem"
    fontWeight: 400
  mono:
    fontFamily: "ui-monospace, Cascadia Mono, Consolas, monospace"
    fontSize: "0.875em"
    lineHeight: 1.5
rounded:
  none: "0"
spacing:
  xs: "0.5rem"
  sm: "1rem"
  md: "1.5rem"
  lg: "2rem"
  xl: "2.5rem"
  section: "3.5rem"
components:
  button-primary:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.ground}"
    rounded: "{rounded.none}"
    padding: "0.6rem 1.4rem"
  input:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    padding: "0.55rem 0.7rem"
  sleeve:
    backgroundColor: "{colors.sleeve}"
    textColor: "{colors.sleeve-ink}"
    rounded: "{rounded.none}"
    padding: "clamp(1.25rem, 4vw, 2.5rem)"
  catalog-entry:
    textColor: "{colors.ink}"
    padding: "0.8rem 0.5rem"
  catalog-entry-hover:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.ground}"
  catalog-entry-primary:
    textColor: "{colors.sleeve-ink}"
    padding: "0.8rem 0.5rem"
  catalog-entry-primary-hover:
    backgroundColor: "{colors.sleeve-ink}"
    textColor: "{colors.sleeve}"
---

# Design System: Palakar

## Overview

**Creative North Star: "The Quiet Sleeve"**

The sample UI is a record sleeve: one matte black object on a plain grey board, carrying a single image that is the framework itself drawn as data, and catalog numbers (PLK 01, PLK 02, ...) that index what to do next. Everything outside the sleeve is quiet print: hairline rules, plain system text, small spaced caps for section headings.

The page stays quiet. A louder version (big wordmark on the sleeve, yellow marker, framed entry, spec table, tick blocks) was built and rejected as too much; the approved build keeps one image and nothing that decorates it. Color appears only as small code squares that mean something (a folder of `panada/`) and are decoded by a legend. The page ground follows the color scheme; the sleeve stays black in both. Constraints: no JavaScript, no external fonts or images, no build, one small stylesheet.

**Key Characteristics:**
- One black sleeve per page at most, carrying the page's single image.
- Hairline rules (1px) as the only structure; no cards, shadows, gradients or radii.
- Catalog numbering (`PLK NN`) as the navigation idiom.
- Color as code: small bordered squares keyed to a legend, never as decoration.
- The stroked wordmark lives in the header only.

## Colors

A greyscale print world with a seven-color directory code set and no other accent.

### Primary
- **Matte Sleeve Black** (sleeve): the sleeve surface; black in both schemes (deepened to sleeve-dark on the dark ground so the object still reads).
- **Sleeve White** (sleeve-ink): sleeve heading, ridge lines, the rules around the primary entry.
- **Sleeve Grey** (sleeve-muted): sleeve paragraph, caption, the primary entry's catalog number.

### Tertiary
- **Directory Codes** (code-0 to code-6: yellow, orange, red, magenta, blue, green, grey): one per `panada/` folder in a fixed order (Core, Resources, Resources/Interfaces, Drivers/Abstraction, Drivers/Database, Drivers/Session, Drivers/Cache), set by `GROUPS` in `Home.php`. Used only as legend and catalog squares and as the ridge hover stroke.

### Neutral
- **Board Grey** (ground / ground-dark): page ground, follows `prefers-color-scheme`.
- **Ink** (ink / ink-dark): body text, button fill, hover inversion, focus ring (via currentColor), selection, accent-color.
- **Muted Ink** (muted / muted-dark): catalog numbers, descriptions, footer, input borders, the 1px border on code squares.
- **Hairline** (rule / rule-dark): every rule and the sleeve outline. Non-text only.
- **Error Red** (error / error-dark): validation messages and the current line in the debug source listing.

### Named Rules
**The Color Is Code Rule.** A hue other than grey appears only when it encodes a directory and a legend on the same page decodes it. There is no brand accent color.

**The Bordered Swatch Rule.** Every code square carries a 1px Muted Ink border so light codes (yellow, grey) stay visible on the light ground.

**The Black Sleeve Rule.** The sleeve does not invert in dark mode; only the board around it changes.

## Typography

**Display Font:** system-ui (with Segoe UI, Roboto, sans-serif), light weight.
**Body Font:** the same system stack.
**Label/Mono Font:** ui-monospace (with Cascadia Mono, Consolas, monospace) for code, paths and pre blocks.

**Character:** Plain system text set like catalog print. Identity comes from the sleeve, the hairlines and the spaced caps, not from a typeface; external fonts are excluded by the product constraints.

### Hierarchy
- **Display** (300, clamp(2rem, 4vw, 2.75rem), 1.15, -0.02em): the sleeve H1, plain text "Palakar X.Y.Z".
- **Headline** (700, browser 2em, 1.15, balanced): text H1 on guestbook and error pages.
- **Body** (400, 1rem, 1.6): running text; the sleeve paragraph at 0.95rem; forms and entries cap at 40rem.
- **Label** (600, 0.75rem, 0.14em, uppercase): H2 section headings, `PLK` catalog numbers.
- **Caption** (400, 0.8rem): figure caption, footer.
- **Wordmark** (SVG, stroke 2.4, 1rem high): header only, with visually hidden text "Palakar".

### Named Rules
**The Spaced Caps Rule.** Small spaced caps are reserved for section headings and `PLK` catalog numbers. Never set sentences or buttons in them.

## Layout

A single column capped at 68rem with 1.25rem side padding. Header: wordmark left, two text links right. Sections are separated by a 3.5rem top margin on the H2, never by boxes. The sleeve is a grid with a 2rem gap: stacked on phones; from 52rem, a 19rem text column (H1, paragraph, primary entry pinned to the bottom) beside the plot column. The catalog flows in CSS columns (3 columns, 15rem minimum, 2.5rem gap). Index entries use a 5.5rem number column and a 1fr label column. Numbers align with `tabular-nums`.

## Elevation & Depth

Flat. No shadows anywhere. Depth comes from the sleeve as a black object on a grey board, framed by a 1px hairline outline, and inside the plot from each ridge's black fill occluding the line behind it.

### Named Rules
**The Printed Object Rule.** If something needs to stand out, it goes on the sleeve or gets a hairline; it never gets a shadow, gradient or blur.

## Shapes

Square everywhere: radius 0 on the sleeve, buttons and inputs. Code markers are 0.7em bordered squares. Lines are 1px hairlines; the wordmark and favicon are open strokes with round joins.

## Components

### Buttons
- **Shape:** square (0).
- **Primary:** Ink fill, Board Grey text, 600 weight, 0.6rem 1.4rem padding, no border.
- **Focus:** 2px currentColor outline, 3px offset (global rule).

### Inputs / Fields
- **Style:** transparent background, 1px Muted Ink border, square, 0.55rem 0.7rem padding, full width; bold block label above.
- **Focus:** the global 2px currentColor outline.
- **Error:** messages listed above the form in Error Red with `role="alert"`.

### Navigation
- **Style:** two plain text links at 0.875rem, no underline, right of the wordmark; hover thickens the underline to 2px. A skip link appears on focus.

### Catalog Entry (signature)
A row: `PLK NN` number in Label style, a 600-weight title, a muted one-line description beneath. Rows sit between hairlines. Linked rows invert to Ink on hover; unlinked rows (instructions) stay static.
- **Primary variant (on the sleeve):** the same row with Sleeve White rules above and below, number in Sleeve Grey, no frame, no marker; pinned to the bottom of the text column; hover inverts to Sleeve White with black text.

### Ridge Plot (signature)
One line per file of `panada/`, top to bottom, in directory order. Every line runs the full plot width (x 40 to 600 of a 640 viewBox); the ridge sits centred on it, its width linear to the file's line count (560 x lines / max) and its height the smoothed indentation (3-point average, up to 36 units at 16+ spaces). Each ridge: black polygon fill to occlude the line behind, Sleeve White 1.1 stroke; hover recolors the stroke to the directory code at 2.2. Each group has a `<title>` with path and line count; the figure has a one-sentence caption. No ticks, labels or axes.

### Legend and Catalog List
Legend: flex row of folder names, each led by a bordered 0.7em code square. Catalog: file names led by the same square, line count right-aligned in Muted Ink, hairline under each row.

## Do's and Don'ts

### Do:
- **Do** keep the page quiet: one image, and nothing added to decorate it.
- **Do** keep all colors as custom properties in `main.css`; the scheme switch only redefines the neutrals and the sleeve.
- **Do** use `PLK NN` catalog numbers for ordered next steps.
- **Do** pair every directory code color with a legend on the same page, and border every code square.
- **Do** separate content with 1px hairlines and whitespace.

### Don't:
- **Don't** add ornaments to the sleeve: no second wordmark, accent markers, frames, stat tables or tick blocks.
- **Don't** add rounded corners, shadows, gradients or card grids.
- **Don't** add a second sleeve or a second image to a page.
- **Don't** use the code colors as decoration, backgrounds or text color.
- **Don't** load external fonts, images or JavaScript.
