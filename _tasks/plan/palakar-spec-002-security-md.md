# palakar-spec-002: SECURITY.md (cara melapor celah keamanan)

- **Status:** plan (menunggu `ACC SPEC 002`)
- **Bergantung pada:** spec-001 (nama dan URL repo)

## Masalah

Belum ada cara resmi melapor celah keamanan secara privat. Tanpa itu, pelapor
akan membuka issue publik dan celahnya terbuka sebelum diperbaiki.

## Perubahan

`SECURITY.md` (bahasa Inggris, supaya bisa dibaca pelapor dari mana saja;
ringkasan bahasa Indonesia di bawahnya):

- Versi yang didukung: `0.x` terbaru.
- Cara melapor: **GitHub Private Vulnerability Reporting**
  (`https://github.com/karsawave/palakar/security/advisories/new`). Tidak ada
  email, sesuai keputusan spec-001.
- Jangan membuka issue publik untuk celah.
- Target waktu: konfirmasi diterima dalam 7 hari, kabar perkembangan
  berkala, perbaikan dan advisory dipublikasikan bersama.
- Pelapor dicantumkan di advisory bila bersedia.
- Celah yang juga ada di Panada asli dikabarkan ke upstream (aturan
  CLAUDE.md).
- README mendapat tautan ke SECURITY.md.

## Langkah manual oleh pemilik repo (tidak bisa dari kode)

Aktifkan *Private vulnerability reporting* di GitHub: Settings → Code security
→ Private vulnerability reporting → Enable.

## Pertanyaan terbuka

1. Target 7 hari untuk konfirmasi realistis? (Bisa diganti 14 hari.)

## Verifikasi

`composer test` hijau; tautan di README benar.
