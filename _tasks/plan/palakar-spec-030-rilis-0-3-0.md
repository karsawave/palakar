# palakar-spec-030: Rilis 0.3.0

- **Status:** plan
- **Dibuat:** 2026-10-25
- **Bergantung pada:** spec-022/024 (checklist rilis), spec-025 s.d. spec-029

## Masalah

`[Unreleased]` berisi fitur baru dan satu perbaikan yang belum sampai ke
pengguna Packagist (masih 0.2.0). Ada penghapusan di aplikasi contoh
(`views/header.php`), sehingga versi naik ke **0.3.0**, bukan 0.2.1.

## Isi rilis (dari CHANGELOG `[Unreleased]`)

- Added: `composer cs` (PER-CS 2.0) + `.editorconfig`; layout Bingkai;
  `Gear::VERSION`; halaman sambutan "sampul katalog"; logo di `docs/`.
- Changed: view contoh semantik + tema WCAG AA; README ditulis ulang.
- Fixed: `composer serve` melayani file statis (CSS).
- Removed: `app/views/header.php` (aplikasi contoh).
- Security: halaman awal tidak mencetak path absolut saat `debug` false.

## Perubahan per file

- `panada/Gear.php`: `VERSION = '0.3.0'`.
- `panada/Resources/Rest.php`: User-Agent `.../0.2` → `.../0.3`.
- `CHANGELOG.md`: `[Unreleased]` → `[0.3.0] - <tanggal>`, `[Unreleased]`
  kosong baru, link perbandingan `v0.2.0...v0.3.0`.
- `CLAUDE.md`: Backlog mencatat `ponytail:` di `app/Controllers/Home.php`
  (halaman awal membaca seluruh core per request; cache bila perlu).
- `SECURITY.md`: tidak berubah ("latest 0.x" mencakup 0.3.0); dicek ulang.

## Langkah rilis (checklist CLAUDE.md, semua wajib)

1. CI hijau di HEAD `development` tepat sebelum merge (`headSha` =
   `git rev-parse HEAD`), semua matrix dan service.
2. Lokal: `composer test`, `composer analyse`, `composer cs`.
3. Uji dari paket: `composer archive` dari `development`, ekstrak di folder
   kosong, `composer install`, `composer serve`; buka halaman awal (plot,
   katalog, CSS, favicon), Guestbook (kirim form, CSRF), halaman error
   `debug` true dan false (path tidak bocor). Pastikan `docs/`,
   `PRODUCT.md`, `DESIGN.md`, `.impeccable/` tidak ada di paket.
4. `composer audit` tanpa temuan.
5. Dokumen sesuai daftar di atas.
6. `grep -rn "TODO\|FIXME\|ponytail:" panada/` kosong; temuan di luar
   `panada/` dicatat di Backlog.
7. Hasil dicatat di spec ini; tunggu CI commit catatan itu hijau.
8. `chore(release): 0.3.0` di `development`, merge ke `master` (merge,
   bukan rebase), tag `v0.3.0`, push `master` + tag.
9. Setelah push: CI `master` hijau, Packagist menampilkan 0.3.0,
   `composer create-project karsawave/palakar` sekali lagi dari Packagist.

## Yang tidak diubah

Kode di luar string versi. Bila checklist menemukan bug: berhenti,
perbaiki lewat spec sendiri, ulangi checklist dari poin 1.

## Pertanyaan terbuka

1. Rilis GitHub (release note) dibuat seperti 0.2.0? Usul: ya, isi dari
   CHANGELOG 0.3.0.

## Breaking change

Tidak ada di core. Aplikasi contoh: `views/header.php` dihapus (sudah di
CHANGELOG `Removed`).

## Hasil checklist (2026-10-25)

Pertanyaan terbuka: ACC tanpa jawaban terpisah = usulan diterima (release
note GitHub dibuat dari CHANGELOG).

1. CI: dicatat setelah push (lihat di bawah).
2. Lokal (PHP 8.3): 152 test, 504 assertion, 32 skip (test server tanpa
   env); PHPStan level 5 bersih; `composer cs` 0 file.
3. Uji dari paket: `git archive` dari `1c3dc62` (`chore(release): 0.3.0`).
   Isi: `.editorconfig`, CHANGELOG(-PANADA), CONTRIBUTING, LICENSE,
   MODERNISASI, README, SECURITY, `app`, `composer.*`, `panada`,
   `public`; tanpa `docs/`, PRODUCT.md, DESIGN.md, `.impeccable/`,
   `tests`, `_tasks`, CLAUDE.md. `composer install` lalu `php -S`:
   - halaman awal 200, judul "Palakar 0.3.0", 45 garis plot; CSS 200
     `text/css`; favicon 200 `image/svg+xml`;
   - Guestbook 200; POST tanpa token 403; dengan token 302; entri tampil;
   - controller sementara yang melempar exception: `debug` true → 500
     dengan pesan dan path; `debug` false → 500 tanpa pesan, path, atau
     nama file; halaman awal tanpa path dan tanpa catatan debug;
   - URL tak dikenal → 404.
   Catatan: `composer archive` dari working tree ikut mengemas `vendor/`
   dan cache lokal, jadi tidak mewakili paket Packagist; uji memakai
   `git archive` (sama dengan arsip tag GitHub).
4. `composer audit`: tanpa temuan.
5. Dokumen: `Gear::VERSION` 0.3.0, User-Agent REST `/0.3` (test REST
   ikut), CHANGELOG `[0.3.0] - 2026-10-25` + link, Backlog CLAUDE.md
   (ponytail halaman awal). SECURITY.md tidak berubah.
6. `grep TODO|FIXME|ponytail:` di `panada/`: kosong. Di luar `panada/`:
   `app/Controllers/Home.php` (dicatat di Backlog).
