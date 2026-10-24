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

Tests
-----

```
composer test           # PHPUnit
composer analyse        # PHPStan level 2
```

Links
-----

- Source and issues: https://github.com/karsawave/palakar
- Security problems: report privately, see [SECURITY.md](SECURITY.md)
- License: BSD-3-Clause, see [LICENSE](LICENSE)
