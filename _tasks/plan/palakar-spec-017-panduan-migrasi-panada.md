# palakar-spec-017: Panduan migrasi dari Panada 1.x

- **Status:** plan
- **Bergantung pada:** -

## Masalah

`MODERNISASI.md` mencatat semua perubahan, tetapi disusun per komponen,
bukan sebagai langkah yang diikuti pengguna saat memindahkan aplikasi.

## Perubahan per file

- `docs/migrasi-panada.md` (baru, bahasa Indonesia): langkah berurutan,
  yaitu salin `app/`, `composer install`, sesuaikan config (`security.php`,
  `session.php`), ganti driver DB lama ke PDO, cek dynamic property, `e()`
  di view, CSRF di form, lalu jalankan dan baca log deprecation.
  Tiap langkah menautkan bagian terkait di `MODERNISASI.md`, tanpa
  menyalin isinya.
- `README.md`: tautan.

## Yang tidak diubah

`MODERNISASI.md` tetap jadi sumber rincian.

## Pertanyaan terbuka

Tidak ada.

## Verifikasi

Langkah diuji dengan memigrasikan aplikasi contoh Panada 1.1.1 dari
commit `baseline` ke tree saat ini; hasil dan kendala dicatat di spec.
