# palakar-spec-028: halaman sambutan yang profesional

**Status:** done (2026-10-25)

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

## Revisi 1 (2026-10-25, perlu ACC ulang)

Versi pertama (hero + kotak versi + grid kartu sama besar) ditolak pengguna:
"tidak ada keunikan, masih seperti buatan AI". Didesain ulang dengan skill
impeccable.

Perubahan dari rencana awal:

- **Arah visual:** pengguna memilih "standar framework" (patokan kualitas:
  Laravel, Symfony, Next.js/Astro), bukan tema/metafora. Hasil: dua kolom;
  kiri status "Running Palakar X on PHP Y", judul "Welcome to Palakar", daftar
  langkah bernomor (demo Guestbook sebagai langkah utama); kanan panel teal
  "The whole core" berisi jumlah baris dan file `panada/`. Di bawah: daftar
  "Built in, switched on". Header: logo perisai-P + tautan Docs/GitHub.
- **Bukti, bukan klaim:** `Home` controller menghitung file dan baris PHP di
  `GEAR` setiap request (sekitar 45 file; ditandai `ponytail:`). Menggantikan
  kartu "Read the docs" statis.
- **Warna merek:** teal `#0f766e` (terang) / `#2dd4bf` (gelap); favicon dan
  logo perisai-P digambar sendiri (SVG).
- **Tanpa ikon kartu:** kartu dihapus; ikon hanya logo, favicon, centang.
- **CSS:** sekitar 6,5 KB (target awal < 5 KB tidak tercapai; menambah gaya
  form, header, langkah, panel, dan mode gelap).
- **File baru di repo:** `PRODUCT.md` (fakta produk untuk alat desain),
  `.impeccable/surfaces/app-views-home-php.md` (kontrak arah desain).
  Screenshot review di `.impeccable/review/` tidak di-commit.
- **Halaman lain:** lebar halaman 68rem; form Guestbook dibatasi 40rem.

## Revisi 2 (2026-10-25, menggantikan Revisi 1, perlu ACC ulang)

Versi "standar framework" dari Revisi 1 juga ditolak pengguna (tata letak,
warna, logo, dan teks terasa generik). Undi ulang "lebih berani"; pengguna
memilih **sampul katalog** (gaya sampul piringan hitam Factory Records).
Pengguna menyetujui arahnya ("sudah ok, rapikan sedikit lagi").

- **Sampul hitam:** wordmark PALAKAR huruf kotak (SVG digambar sendiri,
  `app/views/partials/wordmark.php`, tanpa file font) + "PLK X.Y.Z" +
  satu kalimat; plot garis selebar sampul; tombol berbingkai "PLK 01 Try the
  guestbook demo"; data PHP/file/baris/lisensi.
- **Plot dari kode asli:** satu garis per file `panada/`; lebar tumbuh
  dengan akar jumlah baris, bentuk = indentasi rata-rata (48 titik,
  dirata-rata 3 titik); blok warna folder di ujung kiri garis; `<title>` per
  file; hover mewarnai garis.
- **Index** PLK 01–05 dan **katalog file** (45 file, kode warna folder,
  jumlah baris, tautan ke GitHub), urutan folder tetap di controller.
- **Header:** wordmark + Docs/GitHub. Favicon: tiga garis plot.
- **Tema:** papan abu-abu (terang) / gelap; sampul hitam di kedua mode;
  font sistem; tanpa sudut membulat, bayangan, atau gradien.
- **Biaya:** `Home` membaca seluruh core (~6.400 baris) per request
  (`ponytail:`); HTML halaman awal bertambah ~20 KB karena SVG plot.
- **CSS:** ~5,9 KB (< 6 KB).
- **Review:** dua putaran finish reviewer impeccable (8 + 2 perbaikan,
  semua diselesaikan). DESIGN.md dibuat oleh documenter.
- **Test:** `SkeletonAppTest` memeriksa judul, versi dari `Gear::VERSION`
  (3 kali), path relatif, dan adanya plot.

### Bentuk akhir (disetujui, ACC SPEC 028)

Versi Revisi 2 di atas lalu dinilai pengguna "terlalu norak/berlebihan".
Dikembalikan ke komposisi tenang yang sebelumnya disetujui:

- Sampul dua kolom: kiri teks "Palakar X.Y.Z", satu paragraf, baris
  "PLK 01 Try the guestbook demo"; kanan plot garis putih penuh (garis
  40–600, punuk di tengah, lebar linear terhadap jumlah baris).
- Dibuang: wordmark besar di sampul, kolom blok warna di plot, tabel data,
  tombol berbingkai kuning, akar kuadrat. Wordmark hanya di header.
- Dipertahankan dari review: bentuk = indentasi (bukan panjang baris),
  caption tanpa klaim panjang, urutan folder tetap, kontras label.
- Blok warna di legenda/katalog diberi bingkai 1px (kuning/abu-abu kurang
  terlihat di latar terang, temuan documenter).

## Hasil

- Lokal (PHP 8.3): 152 test, 32 skip (test server tanpa env); PHPStan
  level 5 tanpa temuan; `composer cs` 0 file; detektor impeccable `[]`.
- CSS 5.505 byte. Screenshot desktop 1440 dan ponsel 390, terang dan
  gelap, dicek manual (tidak di-commit, `.impeccable/review/`).
- `debug => false` dicek manual: tanpa path server, tanpa catatan debug.
- File baru: `PRODUCT.md`, `DESIGN.md`, `.impeccable/design.json`,
  `.impeccable/surfaces/app-views-home-php.md` (semua `export-ignore`),
  `app/views/partials/wordmark.php`, `public/assets/favicon.svg`.
- Belum terverifikasi: CI (belum di-push saat catatan ini), browser lama
  Android, pembaca layar sungguhan.
- Commit: `6078d14` (Gear::VERSION), `36e0197` (halaman sambutan).
