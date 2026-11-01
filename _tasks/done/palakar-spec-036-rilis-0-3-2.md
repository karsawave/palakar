# palakar-spec-036: Rilis 0.3.2

- **Status:** done (ACC SPEC 036, 2026-11-01)
- **Dibuat:** 2026-11-01
- **Bergantung pada:** spec-022/024 (checklist rilis), spec-034, spec-035

## Masalah

Desain ulang halaman Pages (spec-034) dan copy baru (spec-035) baru ada
di `development`. https://karsawave.github.io/palakar/ masih
menampilkan halaman 0.3.1 yang dinilai kurang profesional.

## Isi rilis (dari CHANGELOG `[Unreleased]`)

- Changed: halaman GitHub Pages didesain ulang untuk pengunjung baru;
  favicon logo kotak. (Copy spec-035 termasuk di entri ini.)

Tanpa perubahan kode framework, tanpa breaking change → **patch 0.3.2**.

## Perubahan per file

- `panada/Gear.php`: `VERSION = '0.3.2'`.
- `docs/index.html`: meta `v0.3.2`; angka (45 file, ±6.400 baris, 326
  baris `Gear.php`, 152 test) dicek ulang.
- `CHANGELOG.md`: `[Unreleased]` → `[0.3.2] - <tanggal>`, link
  perbandingan `v0.3.1...v0.3.2`.
- `panada/Resources/Rest.php` (User-Agent `/0.3`) dan `SECURITY.md`
  ("latest 0.x"): dicek, tidak berubah.

## Langkah rilis (checklist CLAUDE.md, semua wajib)

1. Push `development`; CI hijau di HEAD tepat sebelum merge (`headSha` =
   `git rev-parse HEAD`), semua matrix dan service, tanpa skip.
2. Lokal: `composer test`, `composer analyse`, `composer cs`.
3. Uji dari paket: `git archive` commit rilis, `composer install`,
   `php -S`; halaman awal, CSS, Guestbook (tanpa token 403, dengan token
   302 + entri tampil), 404 dengan `debug` true/false tanpa bocor path.
   `docs/` tidak ada di paket.
4. `composer audit` tanpa temuan.
5. Dokumen sesuai daftar di atas.
6. `grep -rn "TODO\|FIXME\|ponytail:" panada/` kosong.
7. Hasil dicatat di spec ini; tunggu CI commit catatan itu hijau.
8. Merge `--no-ff` ke `master` (`chore(release): 0.3.2`), tag `v0.3.2`,
   push `master` + tag; release note GitHub dari CHANGELOG.
9. CI `master` hijau; Packagist menampilkan `v0.3.2`.
10. Pages (sudah aktif dari `master` `/docs`) membangun ulang; halaman
    live menampilkan judul baru dan `v0.3.2`, `favicon.svg` 200.

## Yang tidak diubah

Kode di luar string versi. Bila checklist menemukan bug: berhenti,
perbaiki lewat spec sendiri, ulangi dari poin 1.

## Pertanyaan terbuka

Tidak ada (pola sama dengan spec-033).

## Breaking change

Tidak ada.

## Hasil checklist (2026-11-01)

1. CI hijau di `d0baf47` (`chore(release): 0.3.2`): PHP 8.2–8.5, 152
   test, 702 assertion, tanpa skip.
   https://github.com/karsawave/palakar/actions/runs/41326074221
   CI commit catatan ini: dicek sebelum merge (headSha = HEAD).
2. Lokal (PHP 8.3): 152 test, 504 assertion, 32 skip (test server tanpa
   env); PHPStan bersih; `composer cs` 0 file.
3. Uji dari paket (`git archive` `d0baf47`): isi sama dengan 0.3.1,
   tanpa `docs/`. Halaman awal "Palakar 0.3.2", CSS 200; Guestbook tanpa
   token 403, dengan token 302 dan entri tampil; 404 dengan `debug`
   true dan false, tanpa path/trace saat false.
4. `composer audit`: tanpa temuan.
5. `Gear::VERSION` dan meta halaman 0.3.2; angka dicek: 45 file, 6.434
   baris, `Gear.php` 326, 152 test. Rest UA `/0.3` dan SECURITY.md
   tidak berubah.
6. `grep TODO|FIXME|ponytail: panada/`: 0.

## Hasil publikasi (2026-11-01)

7. CI commit catatan checklist hijau (headSha = HEAD) sebelum merge.
8. Merge `--no-ff` ke `master` (`615b7b9`), tag `v0.3.2`, push.
   Release note: https://github.com/karsawave/palakar/releases/tag/v0.3.2
9. CI `master` hijau; Packagist menampilkan `v0.3.2`.
10. https://karsawave.github.io/palakar/ menampilkan judul "Framework PHP
    8.2+ dengan core 45 file." dan `v0.3.2`; `favicon.svg` 200.
    Tampilan visual dicek lewat screenshot lokal (spec-034/035), belum
    dibuka di browser sungguhan oleh Claude.
