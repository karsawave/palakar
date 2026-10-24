# palakar-spec-009: Aturan pesan commit (Conventional Commits)

- **Status:** plan (menunggu `ACC SPEC 009`)
- **Dibuat:** 2026-10-25

## Masalah

Pesan commit selama ini campuran (`step 1: ...`, `ActiveRecord: ...`,
`spec-001: ...`). Untuk proyek publik, format yang seragam memudahkan
membaca riwayat, menulis CHANGELOG, dan menentukan versi SemVer.

## Keputusan: format

Mengikuti [Conventional Commits 1.0.0](https://www.conventionalcommits.org/):

```
<type>(<scope>): <ringkasan>

<isi: kenapa perubahan ini perlu, apa dampaknya>

Refs: spec-NNN
BREAKING CHANGE: <apa yang berubah dan cara pindah>   (hanya bila ada)
Co-Authored-By: <AI>                                  (bila dibantu AI)
```

- **type:** `feat` (fitur), `fix` (bug), `security` tidak dipakai: celah
  keamanan memakai `fix` dengan scope atau isi yang jelas, `docs`, `test`,
  `refactor` (tanpa ubah perilaku), `perf`, `build` (composer), `ci`,
  `chore` (lain-lain), `revert`.
- **scope** (opsional): nama komponen huruf kecil, mis. `email`, `upload`,
  `database`, `cache`, `session`, `spec`, `release`.
- **ringkasan:** bahasa Inggris, kalimat perintah ("add", bukan "added"),
  huruf kecil di awal, tanpa titik, maksimal 72 karakter.
- **isi:** bahasa Inggris, baris maksimal 72 karakter, menjelaskan *kenapa*
  (aturan transparansi). Boleh dilewati untuk perubahan sepele.
- **Breaking change:** `!` setelah type/scope (`feat(email)!: ...`) dan footer
  `BREAKING CHANGE:`; juga dicatat di MODERNISASI.md/CHANGELOG.md.
- **Nomor spec** di footer `Refs: spec-NNN` (bukan lagi di depan ringkasan).
- **Larangan:** rahasia, token, password, email pribadi, atau path pribadi
  (`C:\Users\...`) di pesan commit; detail celah yang belum dirilis
  perbaikannya.
- Satu commit = satu perubahan logis; jangan mencampur refactor dan fitur.

Contoh:

```
fix(upload)!: refuse files a web server could execute

Without permittedFileType every file was accepted, shell.php included.

Refs: spec-010
BREAKING CHANGE: .php*, .phtml, .phar, .htaccess are always refused.
Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
```

## Perubahan

| File | Perubahan |
|---|---|
| `CLAUDE.md` | Bagian baru "Pesan commit"; ganti aturan lama "pesan commit diawali `spec-NNN:`" |
| `CONTRIBUTING.md` | Ringkasan format + tautan ke Conventional Commits |
| `_tasks/plan/palakar-spec-008-…` | Langkah commit memakai format baru |

## Tidak diubah

Riwayat yang sudah di-push (tidak ditulis ulang). Format berlaku untuk
commit berikutnya.

## Pertanyaan terbuka

Tidak ada. (Alat otomatis seperti commitlint sengaja tidak dipasang: tidak ada
dependency baru; bisa jadi spec lain bila perlu.)

## Verifikasi

`composer test` hijau; commit spec ini sendiri sudah memakai format baru.
