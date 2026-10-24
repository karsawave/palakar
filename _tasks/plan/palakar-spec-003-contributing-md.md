# palakar-spec-003: CONTRIBUTING.md (cara berkontribusi)

- **Status:** plan (menunggu `ACC SPEC 003`)
- **Bergantung pada:** spec-001

## Masalah

Aturan kerja (spec + ACC, test, lisensi, etika) baru tertulis di CLAUDE.md,
yang ditujukan untuk asisten AI. Kontributor manusia butuh panduan sendiri.

## Perubahan

`CONTRIBUTING.md` (bahasa Indonesia, sesuai dokumentasi pengguna):

- Filosofi: KISS, transparan, core bisa dibaca dalam semalam; tidak ada
  dependency baru di core.
- Alur: buka issue → spec `_tasks/plan/palakar-spec-NNN-*.md` → maintainer
  menulis `ACC SPEC NNN` → PR → spec pindah ke `_tasks/done/`.
- Wajib sebelum PR: `composer test` dan `composer analyse` hijau; test baru
  untuk bug atau fitur; breaking change dicatat di MODERNISASI.md dan CHANGELOG.
- Konvensi kode (ringkasan dari CLAUDE.md): `strict_types`, komentar bahasa
  Inggris yang menjelaskan *kenapa*, aturan keamanan yang tidak boleh
  dilonggarkan.
- Lisensi: kontribusi masuk di bawah BSD-3-Clause; hanya kode dengan lisensi
  kompatibel; notice Panada tidak diubah.
- Bantuan AI boleh, wajib diungkap (`Co-Authored-By`) dan direview.
- Celah keamanan: lewat SECURITY.md, bukan issue/PR publik.
- README mendapat tautan ke CONTRIBUTING.md.

## Pertanyaan terbuka

Tidak ada.

## Verifikasi

`composer test` hijau; isi tidak bertentangan dengan CLAUDE.md.
