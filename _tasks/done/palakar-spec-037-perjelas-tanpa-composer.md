# palakar-spec-037: Perjelas "jalan tanpa Composer" dan teks yang ambigu

- **Status:** done (2026-11-01, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-035

## Masalah

Pengguna bingung: kartu "Jalan tanpa Composer" di halaman Pages
bertentangan dengan Quick start README yang memakai
`composer create-project`. Maksud sebenarnya: **server** tidak butuh
Composer (`Gear.php:83` memakai `vendor/autoload.php` bila ada, kalau
tidak memakai autoloader bawaan; `require` hanya PHP + ekstensi bawaan).
Tidak ada panduan cara memasang tanpa Composer.

Tinjauan ulang teks lain di halaman yang bisa salah tangkap:

- "Return array, dikirim sebagai JSON": subjeknya tidak jelas.
- Tombol "Panduan mulai" membuka bagian atas README, bukan Quick start.
- Satu perintah instal di hero tanpa keterangan bahwa Composer dibutuhkan
  untuk perintah itu saja.

## Keputusan

### Halaman (`docs/index.html`)

| Bagian | Lama | Baru |
|---|---|---|
| Kartu 6 judul | Jalan tanpa Composer | Server tidak perlu Composer |
| Kartu 6 isi | Autoloader sendiri, database default SQLite di `app/storage/`. | Upload `app/`, `panada/`, dan `public/` tanpa `vendor/`; autoloader bawaan mengambil alih. Cocok untuk shared hosting. |
| Kartu 4 isi | Tanpa container DI dan middleware. Return array, dikirim sebagai JSON. | Tanpa container DI dan middleware. Method yang mengembalikan array otomatis dikirim sebagai JSON. |
| Tombol 1 | → `#readme` | → `#quick-start` |
| Di bawah perintah instal | (tidak ada) | Baris kecil: "Tanpa Composer? Unduh ZIP dari [halaman rilis](…/releases/latest)." |

### README (Quick start, bahasa Inggris)

Tambah satu paragraf setelah blok perintah:

> **No Composer?** Download the source ZIP from the
> [latest release](https://github.com/karsawave/palakar/releases/latest)
> and run `php -S localhost:8000 -t public public/index.php`. Palakar
> needs no Composer packages at runtime: without `vendor/` it uses its
> own autoloader. To deploy to shared hosting, upload `app/`, `panada/`
> and `public/`, point the document root at `public/`, and make
> `app/storage/` writable.

## Perubahan per file

- `docs/index.html`: teks di tabel atas.
- `README.md`: paragraf "No Composer?".
- `CHANGELOG.md` `[Unreleased]` → Changed: "README dan halaman Pages
  menjelaskan cara memasang dan menjalankan tanpa Composer."

## Yang tidak diubah

Kode. Layout/CSS halaman.

## Pertanyaan terbuka

1. Tayangkan lewat rilis patch 0.3.3? Usul: ya, spec rilis terpisah
   setelah ini selesai.

## Verifikasi

- Uji klaim: `git archive` dari `development` (= isi ZIP rilis GitHub,
  karena GitHub memakai `export-ignore` yang sama), **tanpa**
  `composer install`, `php -S`: halaman awal 200, Guestbook bisa kirim
  entri.
- Anchor `#quick-start` ada di README yang di-render GitHub.
- Screenshot 360px/1280px tanpa teks terpotong; `composer test/analyse/cs`
  hijau.

## Hasil

- Pertanyaan 1: usulan diterima (rilis 0.3.3 lewat spec terpisah).
- Klaim diuji sebelum ACC: `git archive` HEAD tanpa `composer install`
  (tanpa `vendor/`), `php -S`: halaman awal 200, Guestbook POST 302 dan
  entri tampil. Anchor `user-content-quick-start` ada di README GitHub.
- Screenshot 1280px (terang) dan 360px (gelap): tanpa teks terpotong.
- `composer test` 152 test (32 dilewati, server lokal tidak ada),
  `analyse` bersih, `cs` 0 file.
