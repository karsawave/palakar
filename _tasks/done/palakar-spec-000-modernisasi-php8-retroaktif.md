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
| `cb111a1` | Baseline: Panada asli (upstream `dc904d0`) |
| `6ddb691` | Core PHP 8.2+, Composer PSR-4 + autoloader Panada sebagai cadangan |
| `f8c2a4f` | Database PDO-only, semua nilai di-bind |
| `cf95e1c` | Validation dengan sanitize otomatis, CSRF bawaan |
| `13ceeed` | Halaman error (Whoops opsional), cookie session modern |
| `cee6e0e` | Perbaikan PHP 8 di resource lama (temuan PHPStan) |
| `e547f52` | Test PHPUnit 11 (unit + HTTP sungguhan), `check.php` |
| `9fd2a6f`, `0692cf9` | MODERNISASI.md, README, CHANGELOG, CLAUDE.md |
| `c8909a4` | `Uri` memahami path Windows |
| `5d0c0e9` | `composer.lock` |
| `c263b76` | PHPStan level 2 (`composer analyse`) |
| `62684ce` | ActiveRecord: strict types, test, bug |
| `f886bd5` | Pagination |
| `607084f` | Rest |
| `65465e7` | Upload (celah: file `.php`, path traversal) |
| `377a93d` | Image |
| `b6e265a` | Email (celah: header injection, STARTTLS) |
| `32b8ded` | Tools |
| `a34a510` | Driver cache (celah: `Apc::__call`) |
| `eb65468` | CI GitHub Actions |
| `76dcc41` | Aturan open source di CLAUDE.md |

## Hasil verifikasi

- 135 test PHPUnit, 18 di-skip lokal (14 PostgreSQL, 4 driver cache tanpa
  server). PHPStan level 2 tanpa temuan dan tanpa baseline.
- Saat ditulis, CI belum pernah jalan. **Terverifikasi 2026-10-25** (CI #1,
  commit `c2d2ca9`): PHP 8.2 dan 8.3 masing-masing OK, 135 test, 622
  assertion, tanpa skip (PostgreSQL 16, Redis 7, Memcached, APCu, memcache
  ikut jalan), PHPStan tanpa temuan.

## Breaking change

Nomor 1–33 di MODERNISASI.md, bagian "Perubahan yang memutus kompatibilitas".
