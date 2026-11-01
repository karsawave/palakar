# palakar-spec-044: Rilis 0.3.5

- **Status:** done (ACC SPEC 044, 2026-11-02)
- **Dibuat:** 2026-11-02
- **Bergantung pada:** spec-022/024 (checklist rilis), spec-043

## Masalah

Hero halaman Pages dengan nama produk (spec-043) baru ada di
`development`. Situs live masih menampilkan hero 0.3.4.

## Isi rilis (dari CHANGELOG `[Unreleased]`)

- Changed: judul halaman GitHub Pages menjadi nama produk; deskripsi
  pindah ke baris di bawahnya.

Tanpa perubahan kode framework, tanpa breaking change → **patch 0.3.5**.

## Perubahan per file

- `panada/Gear.php`: `VERSION = '0.3.5'`.
- `docs/index.html`: meta `v0.3.5`; angka (45 file, `Gear.php` 326
  baris) dicek ulang.
- `CHANGELOG.md`: `[Unreleased]` → `[0.3.5] - <tanggal>`, link
  perbandingan `v0.3.4...v0.3.5`.
- `panada/Resources/Rest.php` (User-Agent `/0.3`) dan `SECURITY.md`:
  dicek, tidak berubah.

## Langkah rilis (checklist CLAUDE.md, semua wajib)

1. Push `development`; CI hijau di HEAD tepat sebelum merge (`headSha` =
   `git rev-parse HEAD`), semua matrix dan service, tanpa skip.
2. Lokal: `composer test`, `composer analyse`, `composer cs`.
3. Uji dari paket: `git archive` commit rilis, dengan dan tanpa
   `composer install`, `php -S`; halaman awal, CSS, Guestbook (tanpa
   token 403, dengan token 302 + entri tampil), 404 dengan `debug`
   true/false tanpa bocor path. `docs/` tidak ada di paket.
4. `composer audit` tanpa temuan.
5. Dokumen sesuai daftar di atas.
6. `grep -rn "TODO\|FIXME\|ponytail:" panada/` kosong.
7. Hasil dicatat di spec ini; tunggu CI commit catatan itu hijau.
8. Merge `--no-ff` ke `master` (`chore(release): 0.3.5`), tag `v0.3.5`,
   push `master` + tag; release note GitHub dari CHANGELOG.
9. CI `master` hijau; Packagist menampilkan `v0.3.5`.
10. Halaman live menampilkan h1 "Palakar Framework"
    dan `v0.3.5`.

## Yang tidak diubah

Kode di luar string versi. Bila checklist menemukan bug: berhenti,
perbaiki lewat spec sendiri, ulangi dari poin 1.

## Pertanyaan terbuka

Tidak ada (pola sama dengan spec-042).

## Breaking change

Tidak ada.


## Hasil checklist (2026-11-02)

1. CI hijau di `cc3d564` (`chore(release): 0.3.5`): PHP 8.2–8.5, 152
   test, 702 assertion, tanpa skip.
   https://github.com/karsawave/palakar/actions/runs/41330935567
   CI commit catatan ini: dicek sebelum merge (headSha = HEAD).
2. Lokal (PHP 8.3): 152 test, 504 assertion, 32 skip (test server tanpa
   env); PHPStan bersih; `composer cs` 0 file.
3. Uji dari paket (`git archive` `cc3d564`), isi sama dengan 0.3.4,
   tanpa `docs/`. Dengan dan tanpa `composer install`, hasil identik:
   "Palakar 0.3.5", CSS 200, Guestbook tanpa token 403, dengan token
   302 + entri tampil, 404 dengan `debug` true/false tanpa bocor path.
4. `composer audit`: tanpa temuan.
5. `Gear::VERSION` dan meta halaman 0.3.5; angka dicek: 45 file,
   `Gear.php` 326. Rest UA `/0.3` dan SECURITY.md tidak berubah.
6. `grep TODO|FIXME|ponytail: panada/`: 0.

## Hasil publikasi (2026-11-02)

7. CI commit catatan checklist hijau (headSha = HEAD) sebelum merge.
8. Merge `--no-ff` ke `master` (`1f4ffce`), tag `v0.3.5`, push.
   Release note: https://github.com/karsawave/palakar/releases/tag/v0.3.5
9. CI `master` hijau; Packagist menampilkan `v0.3.5`.
10. https://karsawave.github.io/palakar/ menampilkan h1 "Palakar
    Framework" dan `v0.3.5`.
