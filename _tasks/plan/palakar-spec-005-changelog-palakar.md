# palakar-spec-005: CHANGELOG Palakar per versi (SemVer)

- **Status:** plan (menunggu `ACC SPEC 005`)
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
