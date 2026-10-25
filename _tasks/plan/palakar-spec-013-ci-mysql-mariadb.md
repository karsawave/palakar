# palakar-spec-013: Test MySQL/MariaDB di CI

- **Status:** plan
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

## Pertanyaan terbuka

1. MariaDB saja, MySQL saja, atau keduanya? Usulan: MariaDB 11 saja
   (satu service, CI tetap cepat).

## Verifikasi

CI hijau dengan test MySQL benar-benar jalan (bukan skipped); jumlah
test dicatat.
