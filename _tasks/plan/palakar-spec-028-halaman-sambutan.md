# palakar-spec-028: halaman sambutan yang profesional

**Status:** plan

## Masalah

Halaman awal adalah kesan pertama pengguna. Setelah spec-025/027, halaman ini
sudah bergaya, tetapi masih berupa daftar teks dan punya beberapa masalah:

| # | Temuan | Dampak |
|---|--------|--------|
| 1 | Tidak ada bagian utama: nama, versi, dan tagline tidak menonjol | Terlihat seperti halaman debug, bukan framework |
| 2 | **Path absolut server** (`D:\PROJECT\...\app/views/home.php`) dicetak juga saat `debug => false` | Bocor struktur folder server bila halaman contoh lupa diganti sebelum deploy; bertentangan dengan aturan "debug false tidak membocorkan path" |
| 3 | Langkah berikutnya tercampur dalam satu daftar | Pemula tidak tahu harus mulai dari mana |
| 4 | Versi `0.2.0` ditulis manual di footer | Mudah lupa diperbarui saat rilis |
| 5 | Tidak ada ikon/favicon | Tab browser kosong, kurang rapi |

## Keputusan

Tetap memakai layout Bingkai dan aturan spec-025: PHP biasa, **tanpa JS,
tanpa font atau gambar eksternal, tanpa build**.

### Isi halaman

1. **Bagian utama:** "Palakar" + lencana versi, tagline "A simple and secure
   PHP 8.2+ framework", dua tombol: *Open the guestbook demo* dan
   *Check requirements*.
2. **Kartu "Start here"** (grid, 1 kolom di ponsel, 2–3 di layar lebar):
   - *Edit this page*: `app/views/home.php` dan `app/Controllers/Home.php`.
   - *Change the look*: `app/views/layouts/main.php`, variabel di `main.css`.
   - *Try a form*: Guestbook (CSRF, validasi, PDO).
   - *Modules*: contoh module.
   - *Read the docs*: README, MODERNISASI.md, GitHub.
3. **Fitur singkat** (daftar ikon kecil): PDO prepared statements, CSRF,
   sanitize, session aman, error page, Composer opsional.
4. Footer tetap (atribusi Panada tidak diubah).

### Keputusan teknis

- **Path relatif** (`app/views/home.php`), bukan `APP` absolut. Path absolut
  hanya ditampilkan bila `debug` true (berguna saat development).
- **Versi** dari satu konstanta `Gear::VERSION` di `panada/Gear.php`; footer
  dan halaman awal membacanya. Dicatat di checklist rilis CLAUDE.md
  ("naikkan `Gear::VERSION`"). Satu baris di core, tanpa logika.
- **Ikon:** SVG inline kecil (dibuat sendiri, tanpa lisensi pihak ketiga)
  untuk logo dan kartu; favicon SVG di `public/assets/favicon.svg`.
- CSS ditambah di `main.css` (kartu, grid, tombol, lencana) memakai variabel
  yang ada; target total < 5 KB. Kontras tetap WCAG 2.2 AA, mode gelap ikut.

## Analisis penerimaan

| Kalangan | Yang didapat |
|----------|--------------|
| Pemula | Tahu langkah pertama: file mana diedit, demo mana dicoba |
| Developer berpengalaman | Ringkasan fitur dan tautan dokumentasi sekilas |
| Tim/klien yang melihat demo | Tampilan rapi, terkesan produk serius |
| Keamanan | Path server tidak bocor di produksi |
| Ponsel/koneksi lambat | Tanpa JS/font/gambar eksternal; SVG inline kecil |
| Aksesibilitas | Satu `<h1>`, kartu memakai `<h2>`, ikon `aria-hidden`, kontras AA |

## Perubahan per file

- `app/views/home.php`: ditulis ulang.
- `app/Controllers/Home.php`: kirim `debug` dan versi ke view.
- `app/views/partials/footer.php`: versi dari `Gear::VERSION`.
- `app/views/layouts/main.php`: `<link rel="icon">`.
- `panada/Gear.php`: `public const VERSION = '0.2.0';`.
- `public/assets/css/main.css`: gaya kartu, grid, tombol, lencana.
- `public/assets/favicon.svg` (baru).
- `tests/Http/SkeletonAppTest.php`: halaman awal tidak memuat path absolut
  `APP` saat `debug` false; versi tampil.
- `CLAUDE.md`: checklist rilis poin 5 menyebut `Gear::VERSION`.
- `CHANGELOG.md` `[Unreleased]`.

## Yang tidak diubah

- Halaman Guestbook, halaman error, routing, logika core selain konstanta.

## Pertanyaan terbuka

1. `Gear::VERSION` di core disetujui? Alternatif: baca `composer.json`
   (lebih lambat, gagal bila file tidak ikut di-deploy), atau tetap manual.
   Usul: konstanta.
2. Teks halaman tetap bahasa Inggris (sesuai spec-025)? Usul: ya.
3. Tampilkan path absolut saat `debug` true? Usul: ya, membantu development.

## Verifikasi

- Test baru gagal sebelum perubahan, lulus sesudahnya; `composer test`,
  `composer analyse`, `composer cs` hijau.
- `composer serve`: halaman awal dicek di browser (lebar 360px dan desktop,
  mode terang dan gelap, navigasi Tab), lalu screenshot dilampirkan ke
  pengguna untuk persetujuan tampilan.
- Validator W3C tanpa error.

## Breaking change

Tidak ada.
