# palakar-spec-031: Halaman GitHub Pages

- **Status:** done (2026-11-01, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** -

## Masalah

Palakar belum punya halaman web publik. Orang yang menemukan paket di
Packagist atau GitHub hanya melihat README. Perlu satu halaman ringkas
di `https://karsawave.github.io/palakar/`.

## Keputusan

- **Satu file HTML statis** `docs/index.html`, CSS inline, tanpa JS,
  tanpa generator (Jekyll/Hugo), tanpa dependency. Sesuai filosofi KISS.
- **Sumber Pages: branch `master`, folder `/docs`** (diatur sekali di
  Settings → Pages). Tanpa workflow deploy tambahan. Halaman hanya
  berubah saat rilis, ikut aturan branch.
- `docs/.nojekyll` supaya GitHub tidak memproses folder lewat Jekyll.
- Identitas visual mengikuti `DESIGN.md` (warna, huruf, gaya "Bingkai")
  dan logo yang sudah ada di `docs/logo*.svg`. Fakta produk diambil dari
  `PRODUCT.md`, tidak mengarang fitur.
- Bahasa Indonesia (dokumentasi pengguna).

## Isi halaman

1. Header: logo, nama "Palakar Framework", satu kalimat tagline dari
   `PRODUCT.md`, tombol GitHub dan Packagist.
2. Mulai cepat: `composer create-project karsawave/palakar app` lalu
   `composer serve`.
3. Fitur inti (daftar pendek dari "Status saat ini": PHP 8.2–8.5, PDO
   dengan binding otomatis, CSRF bawaan, validasi, session aman, tanpa
   dependency wajib).
4. Contoh controller kecil (dari `app/Controllers/Home.php` atau
   Guestbook, disederhanakan).
5. Footer: lisensi BSD-3-Clause, atribusi faktual "fork dari Panada
   Framework 1.1.1 karya Iskandar Soesman", tautan ke repo fork. Tanpa
   logo/merek Panada.

Mendukung tema gelap (`prefers-color-scheme`), lebar ponsel, kontras
cukup, tanpa font eksternal (font sistem) supaya tidak ada request ke
pihak ketiga.

## Perubahan per file

- `docs/index.html` (baru)
- `docs/.nojekyll` (baru, kosong)
- `README.md`: tautan ke halaman Pages.
- `CHANGELOG.md` `[Unreleased]`: "Ditambahkan: halaman GitHub Pages."

## Yang tidak diubah

Kode di `panada/` dan `app/`. Tidak ada dokumentasi API lengkap (spec
terpisah bila perlu). Tidak ada custom domain.

## Pertanyaan terbuka

1. Sumber Pages dari `master` `/docs` (usulan) atau dari `development`
   supaya bisa dilihat sebelum rilis? Usulan: `master`, halaman publik =
   versi rilis.
2. Apakah `docs/` ikut masuk paket Composer, atau ditambahkan ke
   `archive.exclude` / `.gitattributes export-ignore`? Usulan:
   `export-ignore`, halaman tidak berguna di dalam aplikasi pengguna.
   Bila ya, `.gitattributes` ikut diubah.
3. Mengaktifkan Pages di Settings dilakukan pemilik repo (manual), atau
   lewat `gh api` oleh Claude setelah merge? Usulan: manual oleh pemilik.

## Verifikasi

- Buka `docs/index.html` lokal di browser: tema terang dan gelap, lebar
  360px tanpa scroll horizontal.
- Validasi HTML (W3C validator) tanpa error.
- Semua tautan mengarah ke `github.com/karsawave/palakar` atau
  Packagist, tidak ada tautan ke `github.com/panada/*` /
  `panadaframework.com`.
- `composer test`, `analyse`, `cs` tetap hijau (tidak ada kode berubah).
- Setelah rilis berikutnya: halaman terbuka di
  `https://karsawave.github.io/palakar/`.

## Breaking change

Tidak ada.

## Hasil

- Jawaban pertanyaan terbuka (ACC SPEC 031, usulan diterima): 1) sumber
  Pages `master` `/docs`; 2) `/docs` ternyata sudah `export-ignore` di
  `.gitattributes`, tidak ada perubahan; 3) Pages diaktifkan manual oleh
  pemilik repo.
- `composer test`: 152 test, 504 assertion, 32 dilewati (test server
  PostgreSQL/MySQL/Redis/Memcached tanpa env lokal). `composer analyse`:
  tanpa error. `composer cs`: 0 file perlu diperbaiki.
- Tidak ada tautan ke `github.com/panada/*` atau `panadaframework.com`
  (grep).
- **Belum diverifikasi:** tampilan di browser (terang/gelap, 360px) dan
  W3C validator belum dijalankan; halaman live baru ada setelah rilis
  berikutnya dan Pages diaktifkan.
