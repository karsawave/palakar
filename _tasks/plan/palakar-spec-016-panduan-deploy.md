# palakar-spec-016: Panduan deploy

- **Status:** plan
- **Bergantung pada:** -

## Masalah

Belum ada panduan memasang Palakar di server. Banyak pengguna target
memakai shared hosting.

## Perubahan per file

- `docs/deploy.md` (baru, bahasa Indonesia), bagian:
  1. Shared hosting (document root ke `public/`, atau `.htaccess` bila
     tidak bisa diubah).
  2. Apache (`mod_rewrite`).
  3. Nginx + PHP-FPM (contoh `location` minimal).
  4. Checklist produksi: `debug => false`, `secretKey`,
     `trustProxyHeaders`, izin tulis `app/storage/`, HTTPS.
- `README.md`: tautan ke `docs/deploy.md`.

## Yang tidak diubah

Kode. Docker tidak dibahas (di luar target KISS; bisa spec terpisah
bila ada permintaan).

## Pertanyaan terbuka

1. Perlukah `public/.htaccess` ikut disertakan di repo? Usulan: ya, file
   kecil, memudahkan shared hosting. Bila ya, ini perubahan kode dan
   dicatat di CHANGELOG.

## Verifikasi

Contoh konfigurasi Nginx dan Apache diuji manual minimal sekali; hasilnya
dicatat di spec saat selesai (atau ditulis terang-terangan bila tidak
bisa diuji).
