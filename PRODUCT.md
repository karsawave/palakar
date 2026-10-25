# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- PHP developers in Indonesia, beginner to intermediate, often on cheap
  shared hosting, who want a light framework they can understand.
- Existing Panada 1.x users moving their applications to PHP 8.2+.
- International developers evaluating the framework from GitHub/Packagist.
- Students and teachers using it to learn how a web framework works.

The welcome page is the first screen after `composer create-project` and
`composer serve`.

## Product Purpose

Palakar is a small, secure PHP 8.2+ framework, a fork of Panada Framework
1.1.1 by Iskandar Soesman. Success: a developer reads the whole core in one
evening and ships a safe application without learning hidden machinery.

## Positioning

**The whole core is readable in one evening.** Transparent: no DI
container, no middleware stack, no template engine, no build step. Every
behavior can be traced to a short file in `panada/`.

## Operating Context

Runs from `public/index.php` under Apache, nginx, or `php -S`. The sample
app (`app/`) shows the home page, a guestbook demo (CSRF, validation, PDO),
a module sample, and `public/check.php` for requirements.

## Capabilities and Constraints

- PDO with automatic prepared statements, built-in CSRF, input sanitizing,
  hardened session cookies, readable error pages quiet in production,
  Composer optional.
- Sample UI: plain PHP views, no JavaScript, no external fonts or images,
  no build step; must work on old Android browsers common in Indonesia.
- Must not leak server paths or details when `debug` is false.

## Brand Commitments

- Name: **Palakar** = *Palaka* (Sanskrit: guardian, protector) + *Karsa*
  (Indonesian: will, intention; also from KarsaWave).
- First visual identity (chosen 2026-10-25): a factory-records catalog
  sleeve. Matte black sleeve, white hairline ridge plot drawn from the real
  core files, PLK catalog numbers, folder color codes with a legend, a
  drawn square-letter PALAKAR wordmark. Rejected before it: a dark card
  grid and the standard welcome page (both read as generic).
- Panada name/logo must not be used to promote the fork; factual
  attribution "fork of Panada Framework 1.1.1 by Iskandar Soesman" is
  required and kept in the footer.
- Page copy in English; user documentation in Indonesian.

## Evidence on Hand

- Real facts: version (`Gear::VERSION`), core file list in `panada/`,
  test count in CI, license BSD-3-Clause.
- No testimonials, users, benchmarks, or adoption numbers exist; do not
  invent them.

## Product Principles

1. Show, do not hide: the interface points at real files and real code.
2. Safe by default, explained plainly.
3. Small enough to read; every addition must earn its lines.
4. Respect cheap hardware and slow networks.

## Accessibility & Inclusion

WCAG 2.2 AA: contrast, keyboard navigation with visible focus, skip link,
semantic landmarks, reduced motion respected, readable at 360px and 200%
zoom.
