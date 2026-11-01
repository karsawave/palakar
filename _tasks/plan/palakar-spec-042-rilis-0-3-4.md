# palakar-spec-042: Rilis 0.3.4

- **Status:** plan
- **Dibuat:** 2026-11-02
- **Bergantung pada:** spec-022/024 (checklist rilis), spec-039, spec-040, spec-041

## Masalah

Copy halaman Pages yang ringkas (spec-039 s.d. spec-041) baru ada di
`development`. Situs live masih menampilkan copy 0.3.3.

## Isi rilis (dari CHANGELOG `[Unreleased]`)

- Changed: copy halaman GitHub Pages diringkas menjadi judul, satu
  kalimat, dan enam fitur.

Tanpa perubahan kode framework, tanpa breaking change → **patch 0.3.4**.

## Perubahan per file

- `panada/Gear.php`: `VERSION = '0.3.4'`.
- `docs/index.html`: meta `v0.3.4`; angka (45 file, `Gear.php` 326
  baris) dicek ulang.
- `CHANGELOG.md`: `[Unreleased]` → `[0.3.4] - <tanggal>`, link
  perbandingan `v0.3.3...v0.3.4`.
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
8. Merge `--no-ff` ke `master` (`chore(release): 0.3.4`), tag `v0.3.4`,
   push `master` + tag; release note GitHub dari CHANGELOG.
9. CI `master` hijau; Packagist menampilkan `v0.3.4`.
10. Halaman live menampilkan "Framework PHP yang kecil dan mudah
    dibaca." dan `v0.3.4`.

## Yang tidak diubah

Kode di luar string versi. Bila checklist menemukan bug: berhenti,
perbaiki lewat spec sendiri, ulangi dari poin 1.

## Pertanyaan terbuka

Tidak ada (pola sama dengan spec-038).

## Breaking change

Tidak ada.
