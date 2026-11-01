# palakar-spec-032: Rapikan halaman GitHub Pages

- **Status:** done (2026-11-01, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-031

## Masalah

Tinjauan `docs/index.html` (spec-031) terhadap `DESIGN.md`:

1. **Tidak konsisten dengan DESIGN.md.** Sleeve memuat `logo.svg` yang
   berisi wordmark, padahal aturannya wordmark hanya di header dan sleeve
   tanpa wordmark kedua. Header justru memakai teks spaced caps, bukan
   wordmark.
2. **Tidak simetris.** Sleeve selebar 68rem, tetapi daftar PLK dan contoh
   kode dibatasi 40rem rata kiri, sehingga sisi kanan kosong di layar
   lebar. Kolom sleeve 19rem + 1fr dengan logo rata bawah terlihat berat
   sebelah. Jarak link nav memakai `margin-left` (link pertama ikut
   bergeser).
3. **Bug di ponsel.** `<pre>` di dalam sel grid `1fr` tanpa
   `min-width: 0` bisa melebarkan halaman (scroll horizontal di 360px)
   untuk baris kode panjang.
4. **Akses kurang tepat.** Hanya PLK 03 yang berupa link, dan hanya
   judulnya yang bisa diklik; target sentuh kecil dan beda dengan idiom
   "baris tertaut membalik warna saat hover" di DESIGN.md.
5. **Peringatan hook "cramped padding"**: padding horizontal baris 0.5rem.

## Keputusan

- Header: wordmark SVG inline (path dari `docs/logo.svg`, stroke
  `currentColor`) dengan teks tersembunyi "Palakar", mengikuti warna
  tema. Sleeve tanpa gambar: satu kolom, judul, versi `0.3.0`, kalimat
  posisi, dan baris primer "PLK 01 Mulai" yang tertaut ke `#mulai`.
  Tidak ada gambar kedua (Black Sleeve / One Image rule).
- Isi di bawah sleeve: grid dua kolom sama lebar (`1fr 1fr`, gap 2.5rem)
  mulai 52rem — kiri "Mulai" + contoh controller, kanan "Isi core".
  Satu kolom di ponsel. Semua blok memakai lebar penuh kolomnya.
- Baris yang tertaut (README, GitHub, LICENSE) menjadi `<a>` selebar
  baris, hover/focus membalik ke Ink, target sentuh ≥ 44px.
- `min-width: 0` pada sel grid; padding baris jadi `.8rem .75rem`.
- Nav: `display: flex; gap: 1.25rem`.
- Kecepatan: tetap satu file, CSS inline, tanpa gambar di halaman
  (wordmark inline, favicon `logo.svg` saja), tanpa JS dan font. Ukuran
  target < 10 KB.

## Perubahan per file

- `docs/index.html`: perubahan di atas.

## Yang tidak diubah

Teks isi, tautan, footer atribusi, `README.md`, kode framework.
CHANGELOG tidak ditambah: spec-031 belum dirilis, entri yang ada tetap
benar.

## Pertanyaan terbuka

1. Versi di sleeve ditulis tangan (`0.3.0`) dan harus dinaikkan tiap
   rilis. Usulan: ya, tambahkan ke poin 5 checklist rilis di CLAUDE.md
   ("versi di `docs/index.html`"). Alternatif: tidak menampilkan versi.

## Verifikasi

- Browser: terang/gelap, 360px dan 1280px tanpa scroll horizontal, tab
  keyboard melewati skip link → nav → baris tertaut dengan fokus
  terlihat.
- Ukuran file < 10 KB; tidak ada request selain halaman dan favicon.
- Hook impeccable tanpa temuan; `composer test/analyse/cs` hijau.

## Hasil

- Pertanyaan 1: usulan diterima; checklist rilis poin 5 di CLAUDE.md
  sekarang menyebut versi di `docs/index.html`.
- Penyimpangan kecil: baris tertaut memakai class `li.go` (bukan `:has`
  atau `display: contents`) supaya outline fokus tampil dan jalan di
  browser Android lama. Tambahan baris PLK 11 (link ke `panada/`) supaya
  kolom kanan punya tujuan baca yang sama dengan kiri.
- Ukuran `docs/index.html`: 7.804 byte, satu request + favicon.
- `composer test`: 152 test, 32 dilewati (server lokal tidak ada).
  `analyse` tanpa error, `cs` 0 file.
- **Belum diverifikasi:** browser (terang/gelap, 360px/1280px, urutan
  tab) belum dibuka manual; mohon dicek sebelum rilis.
