# palakar-spec-034: Desain ulang halaman GitHub Pages

- **Status:** done (2026-11-01, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-031, spec-032, spec-033 (0.3.1 tayang)

## Masalah

Masukan pengguna atas https://karsawave.github.io/palakar/ (0.3.1):
"kurang profesional, pengguna baru malah tidak jadi pakai". Temuan:

1. Judul terbesar adalah nomor versi, bukan alasan memakai Palakar.
2. Nomor katalog `PLK 01–11` (idiom aplikasi contoh) tidak bermakna bagi
   pengunjung baru dan membuat halaman terasa aneh.
3. Ajakan utama berupa baris katalog, tidak terbaca sebagai tombol.
4. Perintah instal di kolom sempit terpotong dan memunculkan scrollbar.
5. Fitur berupa daftar panjang dua kolom; sulit dipindai.
6. Favicon memakai logo lebar (`logo.svg`), kecil dan gepeng di tab.

## Keputusan

Halaman Pages adalah halaman pemasaran untuk orang baru, bukan aplikasi
contoh. Ia tetap memakai palet, huruf sistem, sudut siku, dan hairline
dari `DESIGN.md`, tetapi **tidak memakai nomor PLK dan sleeve katalog**.
Pengecualian ini dicatat di `DESIGN.md` (satu paragraf "GitHub Pages").

Struktur baru (satu kolom, terpusat, max 60rem):

1. **Header:** logo kotak + wordmark (kiri), `Dokumentasi` · `GitHub`
   (kanan).
2. **Hero:** judul satu kalimat
   "Framework PHP yang bisa dibaca dalam semalam." + subjudul satu
   kalimat; dua tombol: **Mulai** (primer, ke README) dan **GitHub**
   (sekunder, bergaris); di bawahnya satu baris perintah instal
   selebar penuh (`user-select: all`, tanpa JS); baris meta kecil
   `v0.3.1 · PHP 8.2–8.5 · BSD-3-Clause`.
3. **Kenapa Palakar:** grid 3 kolom × 2 baris (1 kolom di ponsel),
   tiap item judul 2–3 kata + satu kalimat pendek, dipisah hairline.
   Isi: Kecil dan terbaca · Aman bawaan (PDO binding, CSRF) · Tanpa
   sihir (tanpa DI/middleware/build) · PHP 8.2–8.5 · Composer opsional
   · Ringan untuk shared hosting.
4. **Contoh:** kode controller 8 baris + hasilnya (`→ {"hello":"Budi"}`)
   dalam satu blok kode lebar penuh.
5. **Footer:** atribusi Panada, lisensi, tautan repo (tetap).

**Favicon:** `docs/favicon.svg` baru, logo kotak saja (persegi 32×32
hitam dengan tiga garis ridge, diambil dari `logo-light.svg`). Logo yang
sama dipakai di header. `logo.svg` tetap untuk README.

**Cepat:** tetap satu file HTML, CSS inline, tanpa JS, tanpa gambar
selain SVG inline dan favicon, tanpa font eksternal; target < 8 KB.

## Perubahan per file

- `docs/index.html`: ditulis ulang sesuai struktur di atas.
- `docs/favicon.svg` (baru).
- `DESIGN.md`: paragraf pengecualian halaman Pages.
- `CHANGELOG.md` `[Unreleased]` → Changed: "Halaman GitHub Pages
  didesain ulang untuk pengunjung baru; favicon logo kotak."

## Yang tidak diubah

Aplikasi contoh (`app/`), kode framework, logo di README. Tayang di
Pages baru setelah rilis berikutnya (aturan branch, spec-031).

## Pertanyaan terbuka

1. **Bahasa halaman.** `PRODUCT.md` menetapkan copy halaman dalam bahasa
   Inggris (pengunjung internasional dari GitHub/Packagist), sedangkan
   halaman sekarang berbahasa Indonesia. Usul: **Indonesia** tetap
   (target utama pengembang Indonesia), README tetap Inggris.
2. **Rilis.** Setelah selesai, langsung rilis patch 0.3.2 lewat
   checklist lengkap supaya tayang? Usul: ya, spec rilis terpisah.

## Verifikasi

- Lokal di browser: terang/gelap, 360px dan 1280px tanpa scroll
  horizontal, perintah instal tidak terpotong di 360px (huruf turun
  bila perlu, bukan scrollbar), urutan Tab, fokus terlihat.
- Favicon tampil tajam di tab terang dan gelap.
- Ukuran < 8 KB; hook impeccable tanpa temuan; `composer test/analyse/cs`
  hijau.

## Hasil

- Pertanyaan terbuka: usulan diterima (bahasa Indonesia; rilis 0.3.2
  lewat spec terpisah).
- Diverifikasi dengan screenshot Edge headless: 1280px tema gelap, 360px
  (lewat iframe, karena jendela headless minimal ~500px) tema terang dan
  gelap. Perintah instal satu baris di 360px, contoh kode muat tanpa
  scroll, tanpa scroll horizontal halaman.
- Penyesuaian saat implementasi: method contoh `name()` → `hi()` (URL
  `/hello/hi/Budi`) supaya baris terpanjang muat di 360px; ukuran huruf
  kode/instal pakai `clamp()` dengan minimum kecil.
- `docs/index.html` 6.720 byte; request: halaman + `favicon.svg`.
- `composer test` 152 test (32 dilewati, server lokal tidak ada),
  `analyse` bersih, `cs` 0 file.
- **Belum diverifikasi:** urutan Tab dan favicon di tab browser nyata;
  tema terang di lebar 1280px (hanya dicek di 360px).
