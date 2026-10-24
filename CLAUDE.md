# CLAUDE.md — Panada (modernisasi PHP 8.2+)

Konteks proyek untuk Claude Code. Detail lengkap perubahan dan catatan migrasi
dari 1.x ada di `MODERNISASI.md`.

## Tentang proyek

Fork Panada Framework 1.1.1 (PHP, Indonesia, 2010) yang dimodernisasi untuk
PHP 8.2+. Filosofi wajib dijaga: **KISS, transparan, seluruh core bisa dibaca
pemula dalam semalam.** Jangan menambah lapisan abstraksi, container DI,
middleware stack, atau dependency baru di core. Kalau ragu, pilih kode yang
lebih pendek dan lebih mudah dibaca.

## Status saat ini

Selesai dan teruji (84 test, 292 assertion, PHP 8.3, SQLite + PostgreSQL 16):

1. Kompatibilitas PHP 8.2/8.3 (strict types, typed props, tanpa dynamic property)
2. Composer PSR-4 + autoloader Panada sebagai fallback
3. Database PDO-only, prepared statement otomatis
4. Validation dengan sanitize otomatis + CSRF bawaan
5. Error handler (Whoops opsional) di fase bootstrap
6. Session cookie modern (SameSite, Secure auto, HttpOnly, strict mode)

Riwayat git: satu commit per langkah (`git log -p` untuk membaca alasannya).

## Perintah

```bash
composer install                    # Whoops + PHPUnit (dev)
composer serve                      # php -S localhost:8000 -t public public/index.php
composer test                       # semua test (unit + http)
vendor/bin/phpunit --testsuite unit
vendor/bin/phpunit --testsuite http
vendor/bin/phpunit --filter ValidationTest

# Test PostgreSQL (opsional, dilewati kalau env kosong):
PANADA_TEST_PGSQL="host=127.0.0.1;dbname=panada_test;user=panada;password=panada" composer test
```

Analisis statis yang dipakai selama refaktor: PHPStan level 2 (tidak ada di
`composer.json`; pasang dengan `composer require --dev phpstan/phpstan`).

## Struktur

```
public/index.php        front controller: define APP, GEAR, VENDOR → Gear::send()
panada/Gear.php         bootstrap + routing (controller → sub-controller → module → alias)
panada/helpers.php      e(), csrf_field(), csrf_token()
panada/Resources/       komponen inti (Controller, Config, Uri, Request, Response,
                        ErrorHandler, Csrf, Validation, Session, Database, Encryption, ...)
panada/Drivers/
  Abstraction/Sql.php   query builder (semua nilai di-bind)
  Database/PanadaPDO.php satu-satunya driver DB (mysql, pgsql, sqlite)
  Session/              Native, Database(+Handler), Cache(+Handler), Cookie
  Cache/                driver lama, baru diperbaiki sebatas kompatibel PHP 8
app/                    aplikasi contoh (Home, Guestbook demo, ModSample module)
app/config/             main.php (debug, trustProxyHeaders), database.php,
                        security.php (secretKey, csrf), session.php, cache.php
app/storage/            SQLite default (diabaikan git)
tests/Unit/             test komponen
tests/Http/             request HTTP sungguhan ke `php -S`
tests/Fixtures/         app + public khusus test (APP terpisah dari app/ asli)
```

## Konvensi kode

- `declare(strict_types=1)` di semua file baru, **kecuali** file yang hanya
  meneruskan panggilan: `Gear.php`, `Resources/Database.php`,
  `Resources/Session.php`, `Resources/Cache.php`, `Resources/PropertiesLoader.php`.
  Segmen URL selalu string; kalau file ini strict, `show(int $id)` jadi TypeError.
- Komentar dan docblock dalam bahasa Inggris (mengikuti kode asli), singkat,
  menjelaskan *kenapa*. Dokumentasi untuk pengguna dalam bahasa Indonesia.
- Tidak boleh ada dynamic property. Pengecualian yang disengaja:
  `ActiveRecord` (`#[\AllowDynamicProperties]`).
- Exception: `RunException` untuk error runtime (500), `HttpException` dengan
  kode status HTTP (default 404). Jangan memanggil `outputError()` lalu lanjut
  jalan; lempar exception.
- Pertahankan API publik lama Panada 1 bila memungkinkan (lihat signature lama
  yang masih diterima, mis. `results($sql, 'array')`).

## Aturan keamanan (jangan dilonggarkan)

- **Database:** nilai selalu lewat binding `?`/`:name`. Nama tabel/kolom
  divalidasi regex di `Sql.php`. Hanya `select()`, `whereRaw()`, dan `query()`
  yang menerima SQL mentah. `update()`/`delete()` tanpa WHERE ditolak.
- **View:** semua data dicetak dengan `e()`, termasuk pesan error.
- **CSRF:** Gear memeriksa setiap request selain GET/HEAD/OPTIONS sebelum
  controller jalan. Pengecualian hanya lewat `security.php` → `csrf.except`.
- **Routing:** hanya method publik yang dideklarasikan di controller pengguna
  yang bisa dipanggil lewat URL (`Gear::isRoutable()`).
- **Error:** `debug => false` tidak boleh membocorkan pesan, path, atau trace.
- **Session:** strict mode, HttpOnly, SameSite; `X-Forwarded-Proto` hanya
  dipercaya bila `trustProxyHeaders` true.
- **Kripto:** pakai libsodium (`Resources\Encryption`), `hash_equals`,
  `random_bytes`. Jangan `unserialize` data dari klien.

## Keputusan desain (sudah diputuskan, jangan diubah tanpa alasan kuat)

- Composer PSR-4 hanya memetakan `Resources\` dan `Drivers\`. Kelas aplikasi
  (`Controllers\`, `Models\`, `Libraries\`, `Modules\`) dimuat autoloader Panada
  dari folder `APP`, karena `APP` bisa diarahkan ke folder lain.
- Autoloader Panada mengikuti PSR-4: tidak pernah melempar exception.
- Whoops dipakai hanya sebagai renderer (`writeToOutput(false)`), tidak
  didaftarkan global, supaya respons tetap lewat `Resources\Response`.
- Deprecation hanya di-log, tidak dilempar (penyebab Panada lama mati di 8.2).
- Controller yang me-return array dikirim sebagai JSON; `echo` juga ditangkap.
- Segmen URL non-numerik untuk parameter `int`/`float` → 404, bukan 500.
- Default database: SQLite di `app/storage/database.sqlite` (tanpa setup).

## Hal yang perlu diingat saat menguji

- `php -S` dengan OPcache aktif bisa menyimpan config lama sampai 2 detik;
  test HTTP menjalankan server dengan `-d opcache.enable=0`.
- Test HTTP butuh ekstensi `curl` dan bisa menjalankan `php -S`.
- `tests/Fixtures/app/config/*.php` membaca env (`PANADA_DEBUG`,
  `PANADA_NO_COMPOSER`, `PANADA_TRUST_PROXY`, `PANADA_SESSION_DRIVER`, ...)
  supaya satu fixture bisa diuji dalam banyak mode.
- Test session native memakai `#[RunInSeparateProcess]`.

## Backlog (urut prioritas)

1. **Nama fork + nama paket Composer.** "Panada 2.0" resmi sudah ada di
   `github.com/panada/app`, dan `panada/panada` di Packagist milik upstream.
   `composer.json` masih memakai nama lama; belum ada label versi.
2. Commit `composer.lock` setelah `composer install` pertama di lokal.
3. CI (Jenkins/GitHub Actions): `composer install`, `composer test` dengan
   service PostgreSQL, plus PHPStan.
4. Komponen lama yang baru kompatibel PHP 8 (lolos lint + PHPStan level 2),
   belum strict types dan belum ada test: `Email`, `Image`, `Upload`,
   `Pagination`, `Rest`, `Tools`, driver cache, `ActiveRecord`.
5. Test MySQL/MariaDB (sejauh ini hanya SQLite dan PostgreSQL yang diuji).
6. `panada/Modules/ModSample` adalah sisa contoh lama yang tidak dipakai
   (module aktif ada di `app/Modules`); putuskan hapus atau rapikan.
