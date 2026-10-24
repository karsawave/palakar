# palakar-spec-005: CHANGELOG Palakar per versi (SemVer)

- **Status:** done (ACC SPEC 005, 2026-10-24)
- **Bergantung pada:** spec-001 (versi `0.1.0`)

## Masalah

File `CHANGELOG` sekarang berisi riwayat Panada ditambah bagian
"Unreleased (PHP 8.2+ modernization)". Belum ada riwayat versi milik Palakar.

## Perubahan

- `CHANGELOG.md` baru dengan format *Keep a Changelog* dan SemVer:
  - `## [0.1.0] - <tanggal rilis>`: ringkasan modernisasi (spec-000) dan
    ganti merek (spec-001), dikelompokkan Added / Changed / Fixed / Security /
    Removed, plus tautan ke breaking change di MODERNISASI.md.
  - `## [Unreleased]` untuk perubahan berikutnya.
- File `CHANGELOG` lama (riwayat Panada) **tetap disimpan** sebagai sejarah
  upstream, diganti nama jadi `CHANGELOG-PANADA` dengan catatan di atasnya,
  tanpa mengubah isinya. Bagian "Unreleased" buatan fork dipindah ke
  `CHANGELOG.md`.
- Aturan di CLAUDE.md: setiap spec yang mengubah perilaku menambah entri di
  `[Unreleased]`.

## Pertanyaan terbuka

1. Tanggal rilis 0.1.0: saat spec-001 selesai, atau nanti saat tag git
   pertama dibuat?

## Verifikasi

`composer test` hijau; isi cocok dengan MODERNISASI.md.

## Hasil

- `CHANGELOG.md` (Keep a Changelog) dengan `[Unreleased]` dan `[0.1.0]`.
- Pertanyaan terbuka tidak dijawab saat ACC: versi ditulis
  `[0.1.0] - not released yet`; tanggal diisi saat tag `v0.1.0` dibuat.
  Field `version` di `composer.json` dibiarkan (keputusan terpisah).
- `CHANGELOG` lama di-`git mv` ke `CHANGELOG-PANADA`; bagian "Unreleased"
  buatan fork dipindah ke `CHANGELOG.md`, riwayat Panada tidak diubah
  (hanya diberi 2 baris catatan di atasnya).
- Aturan CHANGELOG ditambahkan ke CLAUDE.md; README menautkan CHANGELOG.md.
- `composer test`: 135 test, 18 di-skip.
