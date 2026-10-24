# Panada (PHP 8.2+ modernization)

This is a modernized fork of Panada 1.1.1 for PHP 8.2 and newer: PDO with
automatic prepared statements, built-in CSRF protection, input sanitizing,
hardened session cookies, Composer PSR-4 autoloading (with Panada's own
autoloader as fallback) and an interactive error page. The philosophy is
unchanged: the whole core should be readable in one evening.

See [MODERNISASI.md](MODERNISASI.md) (Bahasa Indonesia) for every change and
the upgrade notes from 1.x.

About Panada
------------

Panada is a high performance PHP development framework, yet simple.
Not only in contexts about how to use it, but also how the core system run it.

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
composer test
```

Documentation
-------------

Documentation is available in Indonesian language at [https://panadaframework.com/documentation/id/](https://panadaframework.com/documentation/id/)

Samples
-------

Twlike, a twitter like simple app http://twlike.gopagoda.com/

You can download more simple applications on this https://github.com/panada/samples repo.
But remember, Panada core system are not included. You need to download it by your
self at this repo https://github.com/panada/Panada/tree/master
