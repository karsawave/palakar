# palakar-spec-013: Test MySQL/MariaDB di CI

- **Status:** done (ACC SPEC 013, 2026-10-25)
- **Bergantung pada:** -

## Masalah

Driver `PanadaPDO` mendukung mysql, tetapi hanya SQLite dan PostgreSQL
yang diuji. MySQL/MariaDB adalah database paling umum di hosting PHP.

## Keputusan

Ikuti pola PostgreSQL yang sudah ada: env `PANADA_TEST_MYSQL`, test
dilewati bila kosong. Tanpa abstraksi test baru.

## Perubahan per file

- `.github/workflows/ci.yml`: service `mariadb:11` (atau `mysql:8`),
  ekstensi `pdo_mysql`, env `PANADA_TEST_MYSQL`.
- Test database/ActiveRecord yang sudah membaca `PANADA_TEST_PGSQL`:
  tambah data provider atau cabang untuk `PANADA_TEST_MYSQL`.
- `CLAUDE.md`: perintah test MySQL, hapus poin backlog 3.
- Bila test menemukan bug di `Sql.php`/`PanadaPDO.php` (mis. quoting
  identifier dengan backtick), perbaiki di spec ini dan catat di CHANGELOG.

## Jawaban

ACC tanpa jawaban terpisah = usulan diterima: MariaDB 11 saja.

## Catatan implementasi

- `tests/Unit/Database/MysqlTest.php` mengikuti pola `PgsqlTest`; tidak
  ada cabang di test lain (hanya `DatabaseTestCase` yang dipakai bersama).
- Tidak ada bug ditemukan di `Sql.php`/`PanadaPDO.php`, jadi tidak ada
  perubahan kode dan tidak ada entri CHANGELOG.

## Verifikasi

CI hijau dengan test MySQL benar-benar jalan (bukan skipped); jumlah
test dicatat.

## Hasil

- Commit `10efdef`.
- Lokal (PHP 8.3 + container `mariadb:11`): `MysqlTest` 14 test,
  48 assertion, OK. `composer test` tanpa server: 151 test, 32 skipped.
- CI: 8.2–8.5 `success`; log 8.3 `OK (151 tests, 685 assertions)`, tanpa
  skipped, jadi test MariaDB benar-benar jalan.
- PHPStan level 2 tanpa error.
