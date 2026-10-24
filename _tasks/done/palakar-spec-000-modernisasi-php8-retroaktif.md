# palakar-spec-000: Modernisasi Panada 1.1.1 ke PHP 8.2+ (retroaktif)

- **Status:** done (ditulis retroaktif; pekerjaan ini selesai sebelum alur spec
  dan `ACC SPEC` ada, jadi tidak pernah melalui ACC)
- **Tanggal:** 2026-10-24
- **Detail lengkap:** `MODERNISASI.md` dan `git log -p`

## Masalah

Panada 1.1.1 fatal di PHP 8.2+, query database menempelkan nilai langsung ke
SQL, tidak ada CSRF, session dan enkripsi lemah, dan komponen lama (Email,
Upload, dll.) punya bug serta celah keamanan. Hampir tidak ada test.

## Yang dikerjakan

| Commit | Langkah |
|---|---|
| `432e26e` | Baseline: Panada asli (upstream `dc904d0`) |
| `f135dac` | Core PHP 8.2+, Composer PSR-4 + autoloader Panada sebagai cadangan |
| `1ff2fb0` | Database PDO-only, semua nilai di-bind |
| `56bf2dc` | Validation dengan sanitize otomatis, CSRF bawaan |
| `ead6958` | Halaman error (Whoops opsional), cookie session modern |
| `b1d6206` | Perbaikan PHP 8 di resource lama (temuan PHPStan) |
| `02b2c3a` | Test PHPUnit 11 (unit + HTTP sungguhan), `check.php` |
| `df34cc2`, `dd06bc1` | MODERNISASI.md, README, CHANGELOG, CLAUDE.md |
| `ed44d2d` | `Uri` memahami path Windows |
| `59bfbf0` | `composer.lock` |
| `5568258` | PHPStan level 2 (`composer analyse`) |
| `f3fdfe5` | ActiveRecord: strict types, test, bug |
| `5654306` | Pagination |
| `75f6835` | Rest |
| `8dcde67` | Upload (celah: file `.php`, path traversal) |
| `8a90334` | Image |
| `9f3ddf3` | Email (celah: header injection, STARTTLS) |
| `063c9e1` | Tools |
| `9f73b5b` | Driver cache (celah: `Apc::__call`) |
| `73bd7d7` | CI GitHub Actions |
| `a86ab20` | Aturan open source di CLAUDE.md |

## Hasil verifikasi

- 135 test PHPUnit, 18 di-skip lokal (14 PostgreSQL, 4 driver cache tanpa
  server). PHPStan level 2 tanpa temuan dan tanpa baseline.
- **Belum terverifikasi:** workflow CI belum pernah dijalankan; test
  PostgreSQL, Redis, Memcached, APCu baru akan jalan di CI. Driver `memcache`
  (ekstensi lama) tidak diuji di mana pun.

## Breaking change

Nomor 1–33 di MODERNISASI.md, bagian "Perubahan yang memutus kompatibilitas".
