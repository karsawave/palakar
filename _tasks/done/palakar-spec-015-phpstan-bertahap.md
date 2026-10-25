# palakar-spec-015: Naikkan PHPStan bertahap ke level 5

- **Status:** done (ACC SPEC 015, 2026-10-25)
- **Bergantung pada:** spec-012 (ModSample sudah terhapus)

## Masalah

PHPStan masih level 2. Tipe sudah banyak dipasang, jadi level lebih
tinggi bisa menangkap bug nyata (argumen salah tipe, return yang hilang).

## Keputusan

Naik satu level per commit (3 → 4 → 5), tetap **tanpa baseline**.
Temuan diperbaiki di kode, bukan diabaikan. Properti dan method yang
sengaja tanpa tipe (lihat Konvensi kode di CLAUDE.md) diberi tipe lewat
docblock, bukan tipe native.

## Perubahan per file

- `phpstan.neon.dist`: `level`.
- File di `panada/` sesuai temuan (daftar ditulis setelah dijalankan).
- `CLAUDE.md`: level PHPStan di status.

## Jawaban

ACC tanpa jawaban terpisah = usulan diterima: berhenti di level 5.

## Temuan

Hanya dua, keduanya false positive dengan sebab yang sama: properti
di-reset ke `[]`, lalu diisi oleh method privat yang mengembalikan
`bool`; PHPStan menganggap method itu tanpa efek samping.

| Level | File | Perbaikan |
|---|---|---|
| 3 | `Upload.php` (`getFileInfo` setelah `upload()`) | `@phpstan-impure` di `upload()` |
| 4 | `Validation.php` (`unsetValues` setelah `check()`) | `@phpstan-impure` di `check()` |
| 5 | - | tanpa temuan |

Tidak ada perubahan perilaku, jadi tidak ada entri CHANGELOG.

## Verifikasi

`composer analyse` hijau tanpa baseline; `composer test` hijau. Commit:
satu per level.

## Breaking change

Tidak diharapkan. Bila perbaikan mengubah perilaku, berhenti dan revisi
spec.

## Hasil

- Satu commit per level (lihat `git log -- phpstan.neon.dist`).
- `composer analyse`: level 5, tanpa error, tanpa baseline.
- `composer test` (PHP 8.3, tanpa server): 151 test OK, 32 skipped.
