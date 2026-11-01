# palakar-spec-039: Copy halaman Pages menjawab "kenapa Palakar"

- **Status:** done (2026-11-01, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-035, spec-037

## Masalah

Masukan pengguna atas halaman live 0.3.3: copy berisi fakta ("core 45
file", "6.400 baris") tapi tidak menjawab kenapa calon pengguna harus
memilih Palakar. Angka tanpa manfaat terbaca seperti spesifikasi.

(Screenshot pengguna menampilkan `v0.3.2`; situs live sudah `v0.3.3`.
Kemungkinan cache browser/CDN, bukan bug.)

## Keputusan

Struktur **manfaat di atas, bukti di bawah**. Positioning diambil dari
`PRODUCT.md` (pengguna: pemula–menengah di Indonesia, shared hosting,
pengguna Panada 1.x, pelajar; posisi: transparan, bisa dibaca). Tanpa
testimoni, jumlah pengguna, atau benchmark (datanya tidak ada).

Dipilih pengguna (pertanyaan saat perencanaan):
- Judul: "Framework PHP yang bisa kamu pahami sampai ke dalam."
- Sapaan **"kamu"** di seluruh copy. Ini mengganti aturan "tanpa
  sapaan" di spec-035; aturan "tiap klaim bisa dicek di repo" tetap.

### Cek fakta (koreksi atas draf rencana)

- Draf menulis "tidak ada magic method": **salah**. Core memakai
  `__call`/`__get` (`Resources/Database.php:64`,
  `PropertiesLoader.php:34`, `ActiveRecord.php:270`). Diganti klaim
  yang benar: tanpa container DI dan middleware, alur bisa diikuti dari
  `Gear.php`.
- Draf menulis "XSS sudah ditangani sejak instalasi": **berlebihan**.
  `e()` (`panada/helpers.php`) adalah helper yang harus dipanggil; view
  contoh memakainya untuk semua data pengguna (output tanpa `e()` hanya
  angka internal dan HTML yang sudah dirender). XSS tidak masuk
  subjudul; `e()` disebut apa adanya.
- Migrasi Panada: `MODERNISASI.md` berisi catatan pindah; namespace
  `Resources\` dan folder `Controllers\`/`Models\` tetap (CLAUDE.md,
  `MODERNISASI.md:74-76`).
- Angka: 45 file, 6.434 baris, `Gear.php` 326, 152 test.

### Copy baru

**Hero**

| | Lama | Baru |
|---|---|---|
| Judul | Framework PHP 8.2+ dengan core 45 file. | Framework PHP yang bisa kamu pahami sampai ke dalam. |
| Subjudul | Sekitar 6.400 baris di folder `panada/`. Kalau ada perilaku yang tidak jelas, jawabannya ada di salah satu file itu. | Core-nya 45 file, sekitar 6.400 baris. Query di-bind dan CSRF dicek otomatis sejak instalasi, dan aplikasimu jalan di shared hosting tanpa Composer. |

Tombol, perintah instal, baris ZIP, meta: tetap.

**Bagian baru "Untuk siapa"** (grid 3 kolom, sebelum "Isi core")

1. **Kamu yang ingin paham, bukan menghafal.** Saat ada error, buka
   file-nya dan baca. Tanpa container DI dan middleware: alur request
   bisa diikuti dari `Gear.php` sampai controller.
2. **Kamu yang memakai shared hosting.** Upload `app/`, `panada/`, dan
   `public/`, arahkan document root ke `public/`. Tanpa SSH, tanpa
   Composer di server.
3. **Kamu yang masih memakai Panada 1.x.** Namespace `Resources\` dan
   folder `Controllers`/`Models` tetap. Catatan pindah ke PHP 8.2+ ada
   di `MODERNISASI.md`.

**"Isi core"** → judul bagian "Yang ada di dalamnya". Enam kartu tetap,
kecuali kartu 6 (sudah tercakup "Untuk siapa" no. 2) diganti:
- **Output lewat `e()`.** Helper escape untuk semua data di view; view
  contoh memakainya di setiap output dari pengguna.

**Footer**: tambah baris "Palakar: *palaka* (penjaga) + *karsa*
(kehendak)." Atribusi Panada tetap.

`<meta description>`: "Framework PHP 8.2+ yang bisa kamu pahami sampai
ke dalam: core 45 file, jalan di shared hosting. Fork dari Panada
Framework 1.1.1."

## Perubahan per file

- `docs/index.html`: teks di atas, satu `<section>` baru memakai ulang
  `.grid`; CSS tidak berubah kecuali grid 3 kolom butuh
  `minmax(14rem, 1fr)`.
- `CHANGELOG.md` `[Unreleased]` → Changed: "GitHub Pages copy now leads
  with who Palakar is for and why."

## Yang tidak diubah

Layout dasar, warna, favicon, contoh kode, README, kode framework.

## Pertanyaan terbuka

Tidak ada (judul dan sapaan sudah dijawab pengguna; tayang lewat rilis
0.3.4 dengan spec rilis terpisah, pola spec-038).

## Verifikasi

- Screenshot 1280px (terang) dan 360px via iframe (gelap): tanpa teks
  terpotong, grid "Untuk siapa" rapi.
- `composer test`, `analyse`, `cs` hijau.

## Hasil

- Copy diterapkan sesuai tabel. Tambahan kecil: `MODERNISASI.md` di
  kartu Panada 1.x diberi tautan ke file di GitHub.
- CSS tidak berubah: `.grid` (`minmax(16rem, 1fr)`) sudah memberi 3
  kolom di 1280px dan 1 kolom di 360px.
- Screenshot 1280px (terang) dan 360px via iframe (gelap): tanpa teks
  terpotong.
- `composer test` 152 test (32 dilewati, server lokal tidak ada),
  `analyse` bersih, `cs` 0 file.
