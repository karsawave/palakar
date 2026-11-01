# palakar-spec-043: Hero halaman Pages memakai nama produk

- **Status:** done (2026-11-02, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-041, spec-042 (0.3.4 tayang)

## Masalah

Permintaan pengguna: h1 halaman Pages berisi nama produk, bukan
kalimat deskripsi; deskripsi pindah ke paragraf di bawahnya. Pengguna
juga meminta angka pertama versi sebagai pangkat di h1.

## Keputusan

- **h1:** "Palakar Framework".
- **p:** "Framework PHP yang kecil dan mudah dibaca: core 45 file, tanpa
  dependency wajib." (titik dua memisahkan klaim dari bukti; "core"
  huruf kecil di tengah kalimat).
- **Pangkat versi ditunda sampai 1.0** (disetujui pengguna). Versi
  sekarang 0.x; "Palakar Framework⁰" mudah terbaca sebagai pangkat nol
  atau catatan kaki, dan menonjolkan status pra-1.0. Versi lengkap tetap
  di baris meta. Saat rilis 1.0: tambah `<sup>1</sup>` di h1 (dengan
  `aria-label`), dicatat di spec rilis 1.0 (spec-018).
- `<meta description>` mengikuti p baru.

## Perubahan per file

- `docs/index.html`: h1, p, meta description.
- `_tasks/plan/palakar-spec-018-rilis-1-0-0.md`: satu baris catatan
  "tambah pangkat versi ¹ di h1 `docs/index.html`".
- `CHANGELOG.md` `[Unreleased]` → Changed: "GitHub Pages headline is now
  the product name; the description moves to the line below."

## Yang tidak diubah

Layout, CSS, bagian Fitur, contoh kode, footer, kode.

## Pertanyaan terbuka

Tidak ada (tayang lewat rilis patch 0.3.5, spec rilis terpisah).

## Verifikasi

- Screenshot 1280px dan 360px tanpa teks terpotong.
- `composer test`, `analyse`, `cs` hijau.

## Hasil

- h1, p, dan meta description diterapkan sesuai spec. Catatan pangkat
  versi ditambahkan di spec-018 (Perubahan per file).
- Screenshot 1280px (gelap) dan 360px via iframe (terang): tanpa teks
  terpotong.
- `composer test` 152 test (32 dilewati, server lokal tidak ada),
  `analyse` bersih, `cs` 0 file.
