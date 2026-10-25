# palakar-spec-025: Bingkai, layout view standar

**Status:** done (2026-10-25)

## Masalah

Hasil audit view dan CSS aplikasi contoh (`app/views`, `public/assets/css/main.css`):

| # | Temuan | Dampak |
|---|--------|--------|
| 1 | Setiap view menulis sendiri `<body>`, pembungkus, dan `</body></html>`; `header.php` hanya separuh kerangka | Duplikasi, mudah lupa menutup tag, tidak ada contoh layout |
| 2 | `body{width:700px}` padahal ada meta viewport | Halaman terpotong/geser horizontal di ponsel |
| 3 | Ukuran teks 12px, `text-align: justify` | Sulit dibaca, celah antarkata tidak rata (masalah disleksia, WCAG 1.4.8) |
| 4 | Warna tautan `#2583ad` dan hover `#d54e21` di atas putih (rasio keduanya ±4,26:1, dihitung dengan rumus luminans WCAG) | Gagal kontras WCAG 2.2 AA (minimal 4,5:1) |
| 5 | `home.php` punya tiga `<h1>`, tanpa `<header>/<main>/<footer>` | Pembaca layar tidak bisa melompat ke isi; struktur judul tidak jelas |
| 6 | Tautan `target="_blank"` tanpa penanda | Pengguna pembaca layar tidak tahu tab baru terbuka |
| 7 | `errors/*.php` menyalin CSS inline yang sama tiga kali | Tampilan error dan halaman biasa bisa berbeda |
| 8 | Tidak ada mode gelap, tanpa `:focus-visible` yang jelas | Kurang nyaman; navigasi keyboard sulit dilihat |

## Keputusan

Nama: **Bingkai** (bingkai di sekeliling isi halaman). Hanya nama konvensi di
dokumentasi; tidak ada kelas atau kode core bernama Bingkai.

### Mekanisme (tanpa mengubah core)

```php
return $this->output('layouts/main', [
    'title'   => 'Guestbook',
    'content' => $this->output('guestbook', $data),
]);
```

`output()` sudah me-render ke string, jadi layout cukup dua panggilan.

### Enam aturan Bingkai

1. **Satu layout, satu tampilan.** Layout di `views/layouts/`; view halaman
   hanya berisi konten.
2. **Layout tidak berpikir.** Tanpa query, model, atau logika bisnis; data
   disiapkan controller.
3. **Semua di-escape kecuali `$content`.** `$content` adalah HTML hasil view
   yang sudah meng-escape datanya; dicatat dengan komentar di layout.
4. **Slot opsional pakai `??`**, mis. `<?= $head ?? '' ?>`, `e($title ?? 'Palakar')`.
5. **Potongan pakai partial**: `<?= $this->output('partials/nav') ?>`.
6. **Maksimal satu tingkat**: view → layout, tanpa layout bersarang.

### Standar HTML dan CSS

- HTML5 semantik: `<header>`, `<nav>`, `<main id="isi">`, `<footer>`, satu `<h1>`
  per halaman, tautan "Lewati ke isi" untuk pengguna keyboard.
- Target **WCAG 2.2 AA**: kontras ≥ 4,5:1, teks dasar 1rem, rata kiri,
  `:focus-visible` jelas, label form (sudah ada).
- `<html lang="en">` tetap (teks contoh berbahasa Inggris); aturan "set `lang`
  sesuai bahasa konten" ditulis di dokumentasi.
- CSS satu file tanpa build: custom properties untuk tema (`--warna-teks`,
  `--warna-tautan`, ...), `prefers-color-scheme: dark`, `max-width` + `rem`
  supaya responsif, `prefers-reduced-motion` dihormati. Tanpa `@layer` dan
  nesting native: masih ada browser lama di pasar Indonesia (Android lama,
  UC Browser); custom properties dan media query didukung luas.
- Halaman error memakai CSS yang sama lewat `<link>`, tetapi **tetap berdiri
  sendiri** (tidak memakai layout), supaya halaman error tidak ikut rusak
  bila layout rusak. Fallback CSS inline minimal tetap ada bila file CSS
  gagal dimuat.

## Analisis penerimaan (semua kalangan)

| Kalangan | Kebutuhan | Bagaimana dipenuhi |
|----------|-----------|-------------------|
| Pemula | Tanpa sintaks baru | PHP biasa + `output()` yang sudah dikenal; satu file layout |
| Developer berpengalaman | Bisa diganti / diperluas | Ini konvensi, bukan kewajiban; Twig/Blade bisa dipakai di aplikasi tanpa bentrok |
| Penyandang disabilitas | Bisa dipakai dengan pembaca layar, keyboard, zoom | WCAG 2.2 AA, landmark, skip link, fokus terlihat |
| Pengguna ponsel / koneksi lambat | Ringan dan responsif | Tanpa JS, tanpa font eksternal, CSS satu file < 3 KB |
| Shared hosting | Tanpa Node/npm/build | Tidak ada langkah build |
| Desainer | Ganti tema tanpa menyentuh PHP | Variabel CSS di `:root` |
| Keamanan | Tidak membuka celah XSS | Aturan 3; HTTP test memastikan data di-escape |

Kecepatan: view adalah file PHP biasa yang di-cache OPcache; tidak ada kompilasi
template atau cache di disk. Tambahan per request hanya satu `include` dan satu
`ob_start` (orde mikrodetik).

## Perubahan per file

- `app/views/layouts/main.php` (baru): kerangka HTML lengkap.
- `app/views/partials/footer.php` (baru): footer + atribusi Panada (isi atribusi tidak diubah).
- `app/views/header.php`: dihapus.
- `app/views/home.php`, `app/views/guestbook.php`: hanya konten, satu `<h1>`.
- `app/views/errors/{403,404,500}.php`: `<link>` ke CSS bersama + fallback inline minimal; logika `debug` tidak diubah.
- `app/Controllers/Home.php`, `app/Controllers/Guestbook.php`: bungkus dengan `layouts/main`.
- `public/assets/css/main.css`: ditulis ulang (tema variabel, responsif, kontras AA, mode gelap).
- `README.md`: bagian "Layout (Bingkai)" berisi enam aturan.
- `CHANGELOG.md` `[Unreleased]`: "Ditambahkan: layout Bingkai di aplikasi contoh; diperbaiki: tampilan contoh responsif dan kontras WCAG AA".
- `tests/Http`: halaman awal dan Guestbook memuat `<main`, satu `<h1`, dan data tamu tetap di-escape.

## Yang tidak diubah

- Seluruh core (`panada/`), termasuk `Controller::output()`.
- Logika halaman error (`debug` true/false), fixture test.

## Pertanyaan terbuka

1. Nama "Bingkai" disetujui? (alternatif: tanpa nama, cukup "layout")
2. `header.php` dihapus? Usul: ya, hanya bagian aplikasi contoh, bukan API.
   Aplikasi lama yang memanggil `output('header')` punya salinannya sendiri.
3. Teks aplikasi contoh tetap bahasa Inggris? Usul: ya (sama dengan kode), dokumentasi tetap Indonesia.

## Verifikasi

- `composer test` dan `composer analyse` hijau.
- `composer serve`: halaman awal, Guestbook (form + CSRF), error `debug` true/false.
- Kontras dicek dengan alat kontras WCAG; navigasi dengan Tab saja; lebar 360px tanpa scroll horizontal; zoom 200%.
- Validasi HTML (validator.w3.org) tanpa error.

## Breaking change

Tidak ada untuk core. Hanya aplikasi contoh `app/` (dicatat di CHANGELOG).

## Hasil

- Pertanyaan terbuka: diterima sesuai usul (ACC SPEC 025): nama Bingkai,
  `header.php` dihapus, teks contoh tetap bahasa Inggris.
- Test ditulis dulu: `SkeletonAppTest` (judul, skip link, satu `<main>`,
  satu `<h1>`, satu `</html>`) gagal di view lama, lulus sesudahnya.
- Lokal (PHP 8.3): 151 test, 497 assertion, 32 skip (test server tanpa env).
  PHPStan level 5 tanpa temuan, `composer cs` 0 file.
- `php -S`: halaman awal dan Guestbook 200 dengan layout; 404 memuat CSS
  bersama. Validator W3C (nu) tanpa pesan untuk halaman awal dan Guestbook.
- CSS 2.718 byte. Kontras dihitung dengan rumus WCAG: teks 14,7:1, tautan
  `#0b5cad` 6,7:1, teks redup 7,6:1, error 6,4:1; mode gelap tautan 8,6:1.
- Penyimpangan kecil: `$head` dan `$scripts` ditambah sebagai slot opsional
  di layout (contoh aturan 4); `$this->uri` dicek di halaman error karena
  bisa `null` saat error terjadi di fase bootstrap.

## Belum terverifikasi

- Uji visual di browser (ponsel 360px, zoom 200%, Tab saja) **belum bisa**:
  `composer serve` tidak melayani CSS (bug lama, bukan dari spec ini),
  dicatat di spec-027.
- Komentar `Controller.php:30` masih menyebut `output('header')`; tidak
  diubah karena spec ini tidak menyentuh core.
- README masih menulis "PHPStan level 2" (seharusnya 5) dan belum menyebut
  `composer cs`; di luar cakupan.
