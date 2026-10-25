# palakar-spec-019: Bersihkan paket Composer dengan export-ignore

**Status:** plan

## Masalah

Repo belum punya `.gitattributes`. Composer mengunduh arsip dist (zip tag
GitHub) yang berisi semua file ter-track, sehingga `vendor/karsawave/palakar/`
ikut memuat file pengembangan yang tidak dibutuhkan pengguna: `CLAUDE.md`,
`_tasks/`, `.github/`, `tests/`, konfigurasi PHPUnit/PHPStan.

## Keputusan

Tambahkan `.gitattributes` dengan `export-ignore` untuk file khusus
pengembangan. Tidak memakai bagian `archive` di `composer.json`, karena
`.gitattributes` dibaca GitHub (sumber dist) dan Packagist sekaligus.

## Perubahan per file

- `.gitattributes` (baru):

```
* text=auto eol=lf

/_tasks            export-ignore
/CLAUDE.md         export-ignore
/.claude           export-ignore
/.github           export-ignore
/tests             export-ignore
/phpunit.xml       export-ignore
/phpstan.neon.dist export-ignore
/.gitignore        export-ignore
/.gitattributes    export-ignore
```

- `CHANGELOG.md` `[Unreleased]`: "Paket Composer tidak lagi membawa file
  pengembangan (spec, test, CI, CLAUDE.md)."

## Yang tidak diubah

- `LICENSE`, `CHANGELOG-PANADA`, `README.md`, `SECURITY.md`,
  `CONTRIBUTING.md`, `MODERNISASI.md`, `CHANGELOG.md` tetap ikut paket
  (lisensi dan atribusi wajib ikut distribusi).
- Tag `v0.1.0` tidak diubah; efek berlaku mulai tag berikutnya.

## Pertanyaan terbuka (terjawab)

1. `tests/` dikecualikan: **ya**. Test menguji core, bukan aplikasi
   pengguna.
2. `* text=auto eol=lf` ditambahkan: **ya**. Sebelum commit, cek
   `git add --renormalize .`; bila mengubah file, renormalisasi dipisah
   ke commit sendiri.

## Verifikasi

- `git archive HEAD | tar -t` tidak memuat path yang dikecualikan, tetapi
  memuat `LICENSE` dan `panada/`.
- `composer test` dan `composer analyse` tetap hijau.

## Breaking change

Tidak ada.
