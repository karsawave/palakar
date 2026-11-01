# palakar-spec-035: Tulis ulang copy halaman GitHub Pages

- **Status:** done (2026-11-01, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-034

## Masalah

Masukan pengguna: copy `docs/index.html` terasa "AI slop". Penyebabnya:

- Frasa pemasaran umum yang bisa ditempel ke framework mana pun:
  "aman sejak awal", "tanpa sihir tersembunyi", "Sesederhana ini",
  "Kenapa Palakar", "Cukup satu malam untuk memahaminya".
- Pola tiga serangkai ("Kecil, aman, dan tanpa ...") dan klaim tanpa
  bukti.
- Tidak ada satu pun nama file, angka, atau perilaku konkret, padahal
  itu justru keunggulan Palakar (PRODUCT.md: "Show, do not hide").

## Keputusan

Aturan copy: setiap kalimat menyebut hal yang bisa dicek di repo (nama
file, angka, perilaku). Tanpa kata sifat promosi, tanpa tiga serangkai,
tanpa tanda seru. Kalimat pendek, bahasa Indonesia biasa, tanpa sapaan
"kamu/Anda".

Angka diambil dari repo saat ini: 45 file PHP dan ±6.400 baris di
`panada/`, `Gear.php` 326 baris, 152 test, CI PHP 8.2–8.5.

### Copy baru

| Bagian | Lama | Baru |
|---|---|---|
| Judul | Framework PHP yang bisa dibaca dalam semalam. | Framework PHP 8.2+ dengan core 45 file. |
| Subjudul | Kecil, aman sejak awal, dan tanpa sihir tersembunyi. Setiap perilaku bisa ditelusuri ke satu file pendek. | Sekitar 6.400 baris di folder `panada/`. Kalau ada perilaku yang tidak jelas, jawabannya ada di salah satu file itu. |
| Tombol 1 | Mulai | Panduan mulai |
| Tombol 2 | GitHub | Kode di GitHub |
| Judul bagian | Kenapa Palakar | Isi core |
| 1 | Kecil dan terbaca | **Routing di satu file.** `Gear.php`, 326 baris: URL ke controller, method, dan argumen. |
| 2 | Aman bawaan | **Query selalu di-bind.** Nilai lewat placeholder. `update()` dan `delete()` tanpa WHERE ditolak. |
| 3 | Tanpa sihir | **CSRF dicek sebelum controller.** POST, PUT, dan DELETE butuh token, kecuali rute di `security.php`. |
| 4 | PHP modern | **Controller kelas biasa.** Tanpa container DI dan middleware. Return array, dikirim sebagai JSON. |
| 5 | Composer opsional | **Diuji di PHP 8.2 sampai 8.5.** 152 test di CI, juga dengan PostgreSQL, MariaDB, Redis, dan Memcached. |
| 6 | Ramah shared hosting | **Jalan tanpa Composer.** Autoloader sendiri, database default SQLite di `app/storage/`. |
| Judul bagian | Sesederhana ini | Contoh controller |
| Footer | (tetap) | (tetap) |

Meta (`v0.3.1 · PHP 8.2–8.5 · BSD-3-Clause`) dan `<meta description>`
disesuaikan dengan judul baru.

## Perubahan per file

- `docs/index.html`: teks saja, struktur dan CSS tetap.
- `CHANGELOG.md`: entri `[Unreleased]` spec-034 cukup, tidak ditambah.

## Yang tidak diubah

Layout, CSS, favicon, kode contoh, footer.

## Pertanyaan terbuka

1. Angka (45 file, 6.400 baris, 152 test, 326 baris) akan basi saat core
   berubah. Usul: tambahkan ke checklist rilis poin 5 di CLAUDE.md
   ("angka di `docs/index.html`"), sama seperti versi.

## Verifikasi

- Semua angka dicek ulang dengan perintah (`find panada -name '*.php' |
  wc -l`, `cat ... | wc -l`, jumlah test) sebelum commit.
- Setiap klaim fitur dicocokkan dengan kode (`Sql.php`, `Gear.php`,
  `Csrf.php`, `security.php`).
- Screenshot 360px dan 1280px: tidak ada teks terpotong.

## Hasil

- Pertanyaan 1: usulan diterima; CLAUDE.md checklist rilis poin 5
  menyebut angka di `docs/index.html`.
- Angka dicek: 45 file PHP, 6.434 baris di `panada/`, `Gear.php` 326
  baris, 152 test. Klaim dicocokkan: `Sql.php:573` (WHERE wajib),
  `Csrf.php:82` (GET/HEAD/OPTIONS dikecualikan), `Response.php:70`
  (array → JSON).
- Screenshot 1280px (terang) dan 360px (gelap): tanpa teks terpotong;
  di 360px kedua tombol bertumpuk karena label lebih panjang.
- Tambahan kecil: `<meta description>` disesuaikan dengan judul baru.
