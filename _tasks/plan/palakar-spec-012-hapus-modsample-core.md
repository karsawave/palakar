# palakar-spec-012: Hapus `panada/Modules/ModSample`

- **Status:** plan
- **Bergantung pada:** -

## Masalah

`panada/Modules/ModSample` adalah sisa contoh lama yang tidak dimuat
siapa pun. Module aktif ada di `app/Modules/ModSample`. Folder ini
dikecualikan dari PHPStan, jadi kode mati yang tidak diperiksa.

## Perubahan per file

- Hapus `panada/Modules/` (`git rm -r`).
- `phpstan.neon.dist`: hapus `excludePaths` untuk folder itu.
- `CLAUDE.md`: hapus poin backlog 4.
- `CHANGELOG.md`: entri "Removed".

## Yang tidak diubah

`app/Modules/ModSample` (contoh module yang dipakai).

## Pertanyaan terbuka

Tidak ada.

## Verifikasi

`grep -r "panada/Modules"` kosong; `composer test` dan `composer analyse`
hijau.

## Breaking change

Tidak ada: folder ini tidak pernah di-autoload (PSR-4 hanya `Resources\`
dan `Drivers\`).
