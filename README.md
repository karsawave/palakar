# Palakar Framework

A simple and secure PHP 8.2+ framework: PDO with automatic prepared
statements, built-in CSRF protection, input sanitizing, hardened session
cookies, Composer PSR-4 autoloading (with its own autoloader as fallback) and
an interactive error page. The philosophy: keep it simple, keep it
transparent, the whole core should be readable in one evening.

**Palakar** = *Palaka* (Sanskrit: guardian, protector) + *Karsa*
(Indonesian: will, intention; also from KarsaWave).

Origin
------

Palakar is a fork of [Panada Framework](https://github.com/panada/Panada)
1.1.1 by Iskandar Soesman, modernized for PHP 8.2+. It is not an official
Panada release and is not endorsed by the Panada authors. The original
copyright notice and BSD license are kept in [LICENSE](LICENSE).

See [MODERNISASI.md](MODERNISASI.md) (Bahasa Indonesia) for every change and
the upgrade notes from Panada 1.x.

Requirements
------------

PHP 8.2 or newer with the PDO and mbstring extensions. Sodium is needed for
encryption and the cookie session driver. Composer is optional.

Installation
------------

```
composer install        # optional: Whoops error page, PHPUnit, your own packages
composer serve          # php -S localhost:8000 -t public public/index.php
```

Then open http://localhost:8000/ and http://localhost:8000/check.php.

Layout (Bingkai)
----------------

Views are plain PHP; there is no template engine. A layout is a view that
receives another rendered view as `$content`:

```php
return $this->output('layouts/main', [
    'title'   => 'Guestbook',
    'content' => $this->output('guestbook', $data),
]);
```

Six rules keep it simple and fast (OPcache caches views; there is no
compile step):

1. One layout per look, in `views/layouts/`; page views hold content only.
2. Layouts do not think: no queries or models, the controller prepares data.
3. Print everything with `e()`, except `$content` (already-rendered HTML).
4. Optional slots use `??`: `<?= $scripts ?? '' ?>`, `e($title ?? 'Palakar')`.
5. Reuse pieces as partials: `<?= $this->output('partials/footer') ?>`.
6. One level only: view, then layout. No layout inside a layout.

The sample theme in `public/assets/css/main.css` uses CSS variables (change
the colors in `:root`), follows WCAG 2.2 AA contrast, works on phones and
supports dark mode, without a build step. Set `<html lang>` to the language
of your content. Error pages in `views/errors/` stay standalone so they still
render when a layout is broken.

Tests
-----

```
composer test           # PHPUnit
composer analyse        # PHPStan level 2
```

Credits
-------

Thank you to **Iskandar Soesman** and all contributors of
[Panada Framework](https://github.com/panada/Panada). Palakar started from
Panada Framework 1.1.1 (upstream commit `dc904d0`): the first commit in this
repository's history is that original code, unchanged. Panada's own history
is kept in [CHANGELOG-PANADA](CHANGELOG-PANADA) and its license in
[LICENSE](LICENSE).

The modernization was done with the help of AI (Claude by Anthropic); those
commits say so with a `Co-Authored-By` trailer.

Links
-----

- Source and issues: https://github.com/karsawave/palakar
- Security problems: report privately, see [SECURITY.md](SECURITY.md)
- Contributing: see [CONTRIBUTING.md](CONTRIBUTING.md) (Bahasa Indonesia)
- Changes: see [CHANGELOG.md](CHANGELOG.md)
- License: BSD-3-Clause, see [LICENSE](LICENSE)
