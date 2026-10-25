# palakar-spec-029: logo Palakar dan merapikan dokumen

**Status:** plan

## Masalah

- Palakar belum punya logo sebagai file. Identitas visual baru (spec-028:
  sampul katalog hitam, garis plot, wordmark huruf kotak PALAKAR) hanya ada
  di halaman sambutan.
- README polos dan sebagian isinya basi. Hasil audit:

| File | Temuan |
|------|--------|
| README.md | "PHPStan level 2" (sekarang 5); `composer cs` belum disebut; tanpa logo; tanpa ringkasan fitur dan contoh kode singkat |
| MODERNISASI.md | "135 test" dan "PHPStan level 2" (sekarang 152 test, level 5) |
| CONTRIBUTING.md, SECURITY.md | Perlu dicek ulang terhadap alur terbaru (cs, spec, versi yang didukung) |
| CHANGELOG.md | `[Unreleased]` perlu dirapikan per kategori setelah spec 025–028 |
| CLAUDE.md | "Status saat ini" (151 test) dan Backlog basi |

## Keputusan

### Logo (file SVG, tanpa alat build)

- `docs/logo.svg`: logo utama = tanda (tiga garis plot dalam kotak hitam,
  sama dengan favicon) + wordmark PALAKAR huruf kotak, garis putih di atas
  hitam.
- `docs/logo-light.svg` dan `docs/logo-dark.svg`: wordmark saja untuk
  latar terang/gelap, dipakai README lewat `<picture>` +
  `prefers-color-scheme` (didukung GitHub).
- `public/assets/favicon.svg` tetap; wordmark di `app/views/partials/wordmark.php`
  memakai path yang sama dengan logo (satu sumber bentuk huruf).
- Digambar sendiri; tidak memakai nama/logo Panada.
- `docs/` ditandai `export-ignore` (tidak ikut paket Composer).

### Dokumen

- **README.md** (bahasa Inggris, seperti sekarang): logo, satu kalimat
  posisi ("whole core readable in one evening"), fitur singkat, quick start,
  contoh controller + view 5–10 baris, layout Bingkai, tests (`test`,
  `analyse`, `cs`), Origin/Credits/Links tetap. Atribusi Panada tidak diubah.
- **MODERNISASI.md:** angka test/PHPStan diperbarui; isi lain tidak diubah.
- **CONTRIBUTING.md, SECURITY.md:** hanya koreksi faktual bila ada.
- **CHANGELOG.md `[Unreleased]`:** disusun ulang per kategori (Added,
  Changed, Fixed, Removed, Security) tanpa mengubah isi.
- **CLAUDE.md:** "Status saat ini" dan jumlah test diperbarui.

## Yang tidak diubah

- `LICENSE`, `CHANGELOG-PANADA`, notice copyright, atribusi pihak lain.
- Kode PHP dan perilaku framework.

## Pertanyaan terbuka

1. Logo di README: wordmark saja, atau tanda + wordmark? Usul: tanda + wordmark.
2. Folder logo `docs/`? Usul: ya (umum di repo GitHub, tidak ikut paket).

## Verifikasi

- Logo dirender di GitHub (mode terang dan gelap) setelah push; dicek manual.
- Semua tautan relatif di README valid.
- `composer test`, `composer analyse`, `composer cs` tetap hijau.

## Breaking change

Tidak ada.
