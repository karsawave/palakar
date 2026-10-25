<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="docs/logo-dark.svg">
    <img src="docs/logo-light.svg" alt="Palakar" width="320">
  </picture>
</p>

<p align="center">A simple and secure PHP 8.2+ framework whose whole core is readable in one evening.</p>

---

Palakar has no DI container, no middleware stack, no template engine and no
build step. What it does have is switched on by default:

- PDO with automatic prepared statements; table and column names validated
- CSRF check on every non-GET request, before the controller runs
- Input validation and sanitizing
- Session cookies with HttpOnly, SameSite and strict mode
- Error pages that are detailed in development and quiet in production
- Composer optional: Palakar has its own autoloader as a fallback

**Palakar** = *Palaka* (Sanskrit: guardian, protector) + *Karsa*
(Indonesian: will, intention; also from KarsaWave).

Quick start
-----------

PHP 8.2 or newer with the PDO, mbstring and fileinfo extensions. Sodium is
needed for encryption and the cookie session driver.

```
composer create-project karsawave/palakar myapp
cd myapp
composer serve          # php -S localhost:8000 -t public public/index.php
```

Open http://localhost:8000/ for the welcome page and
http://localhost:8000/check.php to check your server. The default database
is SQLite in `app/storage/`, so the guestbook demo works without any setup.

A first page
------------

A URL maps to a controller, a method and its arguments:
`/hello/index/Budi` calls `Hello::index('Budi')`.

```php
// app/Controllers/Hello.php
namespace Controllers;

use Resources;

class Hello extends Resources\Controller
{
    public function index(string $name = 'world'): string
    {
        return $this->output('hello', ['name' => $name]);
    }
}
```

```php
<!-- app/views/hello.php -->
<h1>Hello, <?= e($name) ?>!</h1>
```

Views are plain PHP. Print every value with `e()`.

Layouts (Bingkai)
-----------------

A layout is a view that receives another rendered view as `$content`:

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

The sample theme (`public/assets/css/main.css`) is one small stylesheet with
its colors in `:root`, light and dark, WCAG 2.2 AA contrast. Error pages in
`views/errors/` stay standalone so they still render when a layout is broken.

Development
-----------

```
composer test           # PHPUnit, unit and real HTTP tests
composer analyse        # PHPStan level 5
composer cs             # PER Coding Style 2.0 (composer cs:fix to apply)
```

Origin
------

Palakar is a fork of [Panada Framework](https://github.com/panada/Panada)
1.1.1 by Iskandar Soesman, modernized for PHP 8.2+. It is not an official
Panada release and is not endorsed by the Panada authors. The original
copyright notice and BSD license are kept in [LICENSE](LICENSE).

See [MODERNISASI.md](MODERNISASI.md) (Bahasa Indonesia) for every change and
the upgrade notes from Panada 1.x.

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
