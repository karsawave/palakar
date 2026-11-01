# palakar-spec-038: Rilis 0.3.3

- **Status:** done (ACC SPEC 038, 2026-11-01)
- **Dibuat:** 2026-11-01
- **Bergantung pada:** spec-022/024 (checklist rilis), spec-037

## Masalah

Penjelasan "tanpa Composer" (spec-037) baru ada di `development`.
Halaman Pages dan README di `master` masih memakai teks yang ambigu.

## Isi rilis (dari CHANGELOG `[Unreleased]`)

- Changed: README dan halaman Pages menjelaskan cara memasang dan
  menjalankan Palakar tanpa Composer.

Tanpa perubahan kode framework, tanpa breaking change → **patch 0.3.3**.

## Perubahan per file

- `panada/Gear.php`: `VERSION = '0.3.3'`.
- `docs/index.html`: meta `v0.3.3`; angka (45 file, ±6.400 baris, 326
  baris `Gear.php`, 152 test) dicek ulang.
- `CHANGELOG.md`: `[Unreleased]` → `[0.3.3] - <tanggal>`, link
  perbandingan `v0.3.2...v0.3.3`.
- `panada/Resources/Rest.php` (User-Agent `/0.3`) dan `SECURITY.md`:
  dicek, tidak berubah.

## Langkah rilis (checklist CLAUDE.md, semua wajib)

1. Push `development`; CI hijau di HEAD tepat sebelum merge (`headSha` =
   `git rev-parse HEAD`), semua matrix dan service, tanpa skip.
2. Lokal: `composer test`, `composer analyse`, `composer cs`.
3. Uji dari paket: `git archive` commit rilis, `composer install`,
   `php -S`; halaman awal, CSS, Guestbook (tanpa token 403, dengan token
   302 + entri tampil), 404 dengan `debug` true/false tanpa bocor path.
   Tambahan rilis ini: paket yang sama **tanpa** `composer install`
   (klaim spec-037) juga membuka halaman awal dan Guestbook.
4. `composer audit` tanpa temuan.
5. Dokumen sesuai daftar di atas.
6. `grep -rn "TODO\|FIXME\|ponytail:" panada/` kosong.
7. Hasil dicatat di spec ini; tunggu CI commit catatan itu hijau.
8. Merge `--no-ff` ke `master` (`chore(release): 0.3.3`), tag `v0.3.3`,
   push `master` + tag; release note GitHub dari CHANGELOG.
9. CI `master` hijau; Packagist menampilkan `v0.3.3`.
10. Halaman live menampilkan "Server tidak perlu Composer" dan `v0.3.3`;
    ZIP sumber dari rilis GitHub `v0.3.3` diunduh, dicek tidak berisi
    `docs/`/`tests/`, dan berjalan dengan `php -S` tanpa Composer.

## Yang tidak diubah

Kode di luar string versi. Bila checklist menemukan bug: berhenti,
perbaiki lewat spec sendiri, ulangi dari poin 1.

## Pertanyaan terbuka

Tidak ada (pola sama dengan spec-036).

## Breaking change

Tidak ada.

## Hasil checklist (2026-11-01)

1. CI hijau di `325c809` (`chore(release): 0.3.3`): PHP 8.2–8.5, 152
   test, 702 assertion, tanpa skip.
   https://github.com/karsawave/palakar/actions/runs/41327677566
   CI commit catatan ini: dicek sebelum merge (headSha = HEAD).
2. Lokal (PHP 8.3): 152 test, 504 assertion, 32 skip (test server tanpa
   env); PHPStan bersih; `composer cs` 0 file.
3. Uji dari paket (`git archive` `325c809`), isi sama dengan 0.3.2,
   tanpa `docs/`. Dua salinan, hasil identik:
   - dengan `composer install`: "Palakar 0.3.3", CSS 200, Guestbook
     tanpa token 403, dengan token 302 + entri tampil, 404 dengan
     `debug` true/false, tanpa bocor path saat false.
   - **tanpa** `composer install` (tidak ada `vendor/`): hasil sama.
4. `composer audit`: tanpa temuan.
5. `Gear::VERSION` dan meta halaman 0.3.3; angka dicek: 45 file, 6.434
   baris, `Gear.php` 326, 152 test. Rest UA `/0.3` dan SECURITY.md
   tidak berubah.
6. `grep TODO|FIXME|ponytail: panada/`: 0.
