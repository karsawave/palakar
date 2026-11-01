# palakar-spec-040: Copy halaman Pages yang profesional dan tidak berlebihan

- **Status:** done (2026-11-01, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-039

## Masalah

Masukan pengguna atas copy spec-039: harus profesional, jangan lebay,
jangan berlebihan. Yang terbaca berlebihan:

- Judul "bisa kamu pahami sampai ke dalam" bernada slogan.
- "Kamu yang ingin paham, bukan menghafal": kontras retoris, menggurui.
- "kamu" diulang di tiga judul kartu dan "aplikasimu": terlalu akrab
  untuk dokumentasi teknis.
- "sejak instalasi", "Saat ada error, buka file-nya dan baca": dramatis.

## Keputusan

Nada: kalimat deskriptif, bahasa Indonesia baku tapi tidak kaku, tanpa
sapaan, tanpa kontras retoris, tanpa kata "mudah/cepat/terbaik". Satu
klaim per kalimat, bisa dicek di repo. Ini membatalkan pilihan sapaan
"kamu" di spec-039 (lihat pertanyaan terbuka).

### Copy baru

| Bagian | spec-039 | Baru |
|---|---|---|
| Judul | Framework PHP yang bisa kamu pahami sampai ke dalam. | Framework PHP 8.2+ yang ringkas dan mudah ditelusuri. |
| Subjudul | Core-nya 45 file, sekitar 6.400 baris. Query di-bind dan CSRF dicek otomatis sejak instalasi, dan aplikasimu jalan di shared hosting tanpa Composer. | Core terdiri dari 45 file (sekitar 6.400 baris). Query di-bind dan token CSRF diperiksa secara bawaan. Dapat berjalan di shared hosting tanpa Composer. |
| Judul bagian | Untuk siapa | Cocok untuk |
| Kartu 1 | **Kamu yang ingin paham, bukan menghafal.** Saat ada error, buka file-nya dan baca. Tanpa container DI dan middleware: … | **Mempelajari cara kerja framework.** Alur request dapat diikuti dari `Gear.php` hingga controller, tanpa container DI atau middleware. |
| Kartu 2 | **Kamu yang memakai shared hosting.** Upload … Tanpa SSH, tanpa Composer di server. | **Shared hosting.** Unggah `app/`, `panada/`, dan `public/`, lalu arahkan document root ke `public/`. Composer tidak diperlukan di server. |
| Kartu 3 | **Kamu yang masih memakai Panada 1.x.** … | **Migrasi dari Panada 1.x.** Namespace `Resources\` dan struktur `Controllers`/`Models` dipertahankan. Catatan migrasi ke PHP 8.2+ tersedia di `MODERNISASI.md`. |
| Judul bagian | Yang ada di dalamnya | Fitur inti |
| Kartu "e()" | Helper escape untuk semua data di view; view contoh memakainya di setiap output dari pengguna. | Helper `e()` untuk escape output di view. Aplikasi contoh memakainya untuk setiap data dari pengguna. |
| Footer | Palakar: *palaka* (penjaga) + *karsa* (kehendak). | Nama Palakar berasal dari *palaka* (penjaga) dan *karsa* (kehendak). |
| `<meta description>` | …yang bisa kamu pahami sampai ke dalam… | Framework PHP 8.2+ yang ringkas: core 45 file, query di-bind, CSRF bawaan, berjalan di shared hosting. Fork dari Panada Framework 1.1.1. |

Kartu fitur inti lain, tombol, perintah instal, baris ZIP, contoh kode:
sudah deskriptif, tetap.

## Perubahan per file

- `docs/index.html`: teks di tabel.
- `CHANGELOG.md`: entri `[Unreleased]` spec-039 diganti "GitHub Pages
  copy rewritten: who Palakar suits and why, in a plain descriptive
  tone." (belum dirilis, jadi satu entri cukup).

## Yang tidak diubah

Layout, CSS, struktur bagian, kode.

## Pertanyaan terbuka

1. Sapaan: spec-039 memakai "kamu" atas pilihan pengguna. Usul: tanpa
   sapaan, karena itu yang paling netral dan profesional. Alternatif:
   "Anda".
2. Kata "mudah ditelusuri" di judul: satu-satunya kata sifat penilaian.
   Usul: pertahankan (inti positioning, dibuktikan oleh subjudul).
   Alternatif tanpa penilaian: "Framework PHP 8.2+ dengan core 45 file."

## Verifikasi

- Baca ulang: tidak ada "kamu/-mu", tanda seru, atau kontras retoris
  (`grep -i "kamu\|mu\b\|!"`).
- Screenshot 1280px dan 360px tanpa teks terpotong; `composer test/
  analyse/cs` hijau.

## Hasil

- Pertanyaan terbuka: usulan diterima (tanpa sapaan; "mudah ditelusuri"
  dipertahankan di judul).
- Copy diterapkan sesuai tabel. `grep` "kamu"/"-mu": 0; satu-satunya
  "!" adalah `<!doctype html>`.
- Screenshot 1280px (terang) dan 360px via iframe (gelap): tanpa teks
  terpotong.
- `composer test` 152 test (32 dilewati, server lokal tidak ada),
  `analyse` bersih, `cs` 0 file.
