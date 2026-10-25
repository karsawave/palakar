# palakar-spec-015: Naikkan PHPStan bertahap ke level 5

- **Status:** plan
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

## Pertanyaan terbuka

1. Berhenti di level 5, atau lanjut ke 6 (tipe iterable/array wajib)?
   Usulan: 5; level 6 menambah banyak docblock tanpa banyak manfaat.

## Verifikasi

`composer analyse` hijau tanpa baseline; `composer test` hijau. Commit:
satu per level.

## Breaking change

Tidak diharapkan. Bila perbaikan mengubah perilaku, berhenti dan revisi
spec.
