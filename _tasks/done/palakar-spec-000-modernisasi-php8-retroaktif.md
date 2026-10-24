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
| `b6a9463` | Core PHP 8.2+, Composer PSR-4 + autoloader Panada sebagai cadangan |
| `b6e3478` | Database PDO-only, semua nilai di-bind |
| `1d742f2` | Validation dengan sanitize otomatis, CSRF bawaan |
| `db68a79` | Halaman error (Whoops opsional), cookie session modern |
| `cd6b46c` | Perbaikan PHP 8 di resource lama (temuan PHPStan) |
| `c5934b2` | Test PHPUnit 11 (unit + HTTP sungguhan), `check.php` |
| `e06382d`, `880bf20` | MODERNISASI.md, README, CHANGELOG, CLAUDE.md |
| `fa3676d` | `Uri` memahami path Windows |
| `0d90f25` | `composer.lock` |
| `9dca81f` | PHPStan level 2 (`composer analyse`) |
| `8ed0476` | ActiveRecord: strict types, test, bug |
| `402c8c1` | Pagination |
| `dc88d15` | Rest |
| `4292f7e` | Upload (celah: file `.php`, path traversal) |
| `fe16317` | Image |
| `17ebd8a` | Email (celah: header injection, STARTTLS) |
| `b778e74` | Tools |
| `474a8c9` | Driver cache (celah: `Apc::__call`) |
| `a92e39b` | CI GitHub Actions |
| `dc811ed` | Aturan open source di CLAUDE.md |

## Hasil verifikasi

- 135 test PHPUnit, 18 di-skip lokal (14 PostgreSQL, 4 driver cache tanpa
  server). PHPStan level 2 tanpa temuan dan tanpa baseline.
- **Belum terverifikasi:** workflow CI belum pernah dijalankan; test
  PostgreSQL, Redis, Memcached, APCu baru akan jalan di CI. Driver `memcache`
  (ekstensi lama) tidak diuji di mana pun.

## Breaking change

Nomor 1–33 di MODERNISASI.md, bagian "Perubahan yang memutus kompatibilitas".
