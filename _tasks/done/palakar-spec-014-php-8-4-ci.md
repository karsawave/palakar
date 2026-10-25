# palakar-spec-014: PHP 8.4 dan 8.5 di matrix CI

- **Status:** done (ACC SPEC 014, 2026-10-25)
- **Bergantung pada:** -

## Masalah

CI hanya menguji PHP 8.2 dan 8.3. PHP 8.4 menambah deprecation, mis.
implicit nullable (`Type $x = null`), dan karena PHPUnit memakai
`failOnDeprecation`, deprecation itu akan langsung membuat test gagal.

## Perubahan per file

- `.github/workflows/ci.yml`: `php: ['8.2', '8.3', '8.4']`.
- File yang memicu deprecation: perbaiki seminimal mungkin (mis.
  `?Type $x = null`). Daftar file ditulis di spec setelah dijalankan.
- `CLAUDE.md`: status dan backlog 5.

## Yang tidak diubah

Minimum PHP tetap 8.2.

## Jawaban dan penyimpangan

Spec menulis 8.5 sebagai `continue-on-error` (eksperimental). Saat
implementasi ternyata PHP 8.5 sudah stabil sejak November 2025, jadi
ditanyakan ulang. Jawaban (2026-10-25): **8.2–8.5 semua wajib hijau**,
tanpa `continue-on-error`.

Pencarian implicit nullable (`Type $x = null` tanpa `?`) di `panada/`,
`app/`, `tests/`: tidak ada temuan.

Job 8.5 pertama gagal: `/boom/fatal` (kehabisan memori) memberi halaman
500 kosong. Dugaan awal (memori habis lagi saat render) salah; commit
`81c566b` dibatalkan. Penyebab sebenarnya, didapat dari image
`php:8.5-cli`: setelah fatal error PHP sudah mengirim `HTTP/1.0 500`, dan
sejak 8.5 `http_response_code()` sesudahnya memicu warning yang diubah
jadi exception oleh error handler. Perbaikan di `Response::sendHeader()`:
status hanya di-set bila berbeda. Ini perubahan kode di luar rencana
spec, tetapi termasuk tujuan spec (CI hijau di 8.5) dan dicatat di
CHANGELOG bagian Fixed.

## Verifikasi

CI hijau di 8.2, 8.3, 8.4, 8.5. PHP 8.4/8.5 tidak tersedia di lokal
(hanya 8.1–8.3), jadi verifikasi hanya lewat CI; hasilnya dicatat di
bawah setelah run selesai.

## Hasil

- Commit: `3d9cf1b` (matrix), `81c566b` (tebakan salah), `ac4c637`
  (perbaikan sebenarnya, membatalkan `81c566b`).
- CI run untuk `ac4c637`: test 8.2, 8.3, 8.4, 8.5 semua `success`.
- Lokal PHP 8.3: 137 test OK, 18 skipped (server PostgreSQL/Redis/
  Memcached hanya di CI); PHPStan level 2 tanpa error.
- Docker `php:8.5-cli`: lulus kecuali 7 test Image/Upload karena image
  itu tanpa ekstensi GD (CI memasang `gd`).
