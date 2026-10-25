# Changelog

All notable changes to Palakar Framework are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and the project uses [Semantic Versioning](https://semver.org/).

Breaking changes and upgrade notes from Panada 1.x are listed in detail in
[MODERNISASI.md](MODERNISASI.md) (Bahasa Indonesia). The history of Panada
Framework itself is in [CHANGELOG-PANADA](CHANGELOG-PANADA).

## [Unreleased]

### Changed

- The Composer package no longer ships development files (`_tasks/`,
  `tests/`, `.github/`, `CLAUDE.md`, PHPUnit/PHPStan config), via
  `export-ignore` in `.gitattributes`.
- **Breaking:** `Upload::getMimeTypes()` reads the type from the file
  content with ext-fileinfo, not from the extension. It now takes a path
  to an existing file and returns `false` for a bare name. `getFileInfo()`
  `mime` and Email attachment `Content-Type` follow (a renamed `.php` sent
  as `.png` no longer reports `image/png`). `ext-fileinfo` is required.
- `Pagination::getUrl()` rewritten from scratch; output unchanged.
- `app/config/security.php` reads `secretKey` from the
  `PALAKAR_SECRET_KEY` environment variable first.

### Security

- **Breaking:** every response sends `X-Frame-Options: SAMEORIGIN` and
  `Referrer-Policy: strict-origin-when-cross-origin`. Apps framed by other
  domains unset `Response::$headers['X-Frame-Options']` (spec-020).
- CI runs `composer audit`.

### Removed

- WordPress-derived code (GPLv2): the extension-to-MIME list in `Upload`
  and the `paginate_links()` adaptation in `Pagination` (spec-011).
- `panada/Modules/ModSample`, an old sample nothing loaded; the module
  example lives in `app/Modules` (spec-012).

## [0.1.0] - 2026-10-25

First version of Palakar, a fork of Panada Framework 1.1.1 by Iskandar
Soesman, modernized for PHP 8.2+ (specs 000 to 010).

### Added

- Composer PSR-4 autoloading; Panada's own autoloader kept as fallback.
- PDO-only database layer: every value bound, `transaction()`, `whereRaw()`.
- Built-in CSRF protection (`csrf_field()`, `X-CSRF-Token` header).
- Validation with auto-sanitize and named filters.
- ErrorHandler with optional Whoops page and a safe production page.
- Session cookies with SameSite, Secure 'auto', HttpOnly, strict mode;
  libsodium encryption and a signed cookie session driver.
- `Resources\Configurable` (`setOption()` for Pagination, Upload, Image, Email).
- PHPUnit test suite (135 tests, unit + real HTTP), PHPStan level 2,
  GitHub Actions CI with PostgreSQL, Redis and Memcached.
- SECURITY.md, CONTRIBUTING.md, credits, spec workflow in `_tasks/`,
  Conventional Commits and master/development branch rules.

### Changed

- Requires PHP 8.2+, `strict_types` in the core.
- Renamed to Palakar Framework (`karsawave/palakar`).
- Email: UTF-8 + quoted-printable, correct MIME, `Date`/`Message-ID` headers.
- Rest: response helpers keep the status code; `getRequest()` reads
  PUT/PATCH/DELETE bodies.
- Cache drivers share one contract; namespaces use random versions.

### Fixed

- ActiveRecord: model `$table` ignored, duplicate insert on second `save()`,
  query state leaking into the next query.
- Pagination `getUrl()` always failed; Tools `xmlDecode()` always failed.
- Image: fractional sizes, lost PNG transparency.
- Dummy cache `flushValues()` fatal error; the cache session driver could not
  write.
- Required fields missing from the request passed validation.
- `Uri` on Windows paths.

### Security

- SQL injection in the PDO driver (`escape()` returned its input).
- XSS in the 404/500 error pages; inherited controller methods reachable
  from URLs.
- Upload accepted executable files (`.php`, `.phtml`, `x.php.jpg`,
  `.htaccess`) and names with `../`.
- Email header injection; `smtpSecure 'tls'` sent the password unencrypted;
  failed SMTP login was ignored; no dot-stuffing.
- `Apc` cache driver forwarded any method name to a global PHP function.
- `getRandomString()` used `mt_rand()`; files were chmod 0666 by Image.

### Removed

- Database drivers mysql, mysqli, pgsql, sqlite, cubrid, mongodb (PDO only).
- Panada logo and GitHub banner from the welcome page.

[Unreleased]: https://github.com/karsawave/palakar/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/karsawave/palakar/releases/tag/v0.1.0
