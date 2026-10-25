# palakar-spec-023: Rilis 0.2.0

- **Status:** done (ACC SPEC 023, 2026-10-25)
- **Dibuat:** 2026-10-25
- **Bergantung pada:** spec-022 (checklist rilis), spec-011 s.d. spec-015,
  spec-019, spec-020

## Masalah

`[Unreleased]` berisi dua breaking change (deteksi MIME, header keamanan)
dan perbaikan bug PHP 8.5. Pengguna Packagist masih mendapat 0.1.0.
Karena ada breaking change di seri 0.x, versi naik ke **0.2.0**, bukan
0.1.1.

## Isi rilis (dari CHANGELOG `[Unreleased]`)

- Breaking: `Upload::getMimeTypes()` dari isi file (`ext-fileinfo`
  wajib); header `X-Frame-Options` dan `Referrer-Policy`.
- Fixed: halaman error kosong setelah fatal error di PHP 8.5.
- Removed: kode asal WordPress (GPLv2), `panada/Modules/ModSample`.
- Lainnya: `secretKey` dari env, paket Composer tanpa file development,
  CI PHP 8.2–8.5 + MariaDB + `composer audit`, PHPStan level 5.

## Perubahan per file

- `CHANGELOG.md`: `[Unreleased]` → `[0.2.0] - <tanggal>`, tambah
  `[Unreleased]` kosong dan link perbandingan bila formatnya ada.
- `app/views/home.php`: "version 0.1.0" → "version 0.2.0".
- `panada/Resources/Rest.php`: User-Agent `.../0.1` → `.../0.2`.
- `CLAUDE.md`: status, bila perlu.
- `SECURITY.md`: tidak berubah ("latest 0.x" sudah mencakup 0.2.0);
  dicek ulang sesuai checklist.

## Langkah rilis (checklist spec-022, semua wajib)

1. CI hijau di commit kandidat, tanpa test yang dilewati.
2. Lokal: `composer test`, `composer analyse`.
3. Uji dari paket: `composer archive` dari `development`, ekstrak di folder
   kosong, `composer install`, `composer serve`; buka halaman awal,
   Guestbook (kirim form, CSRF), `/boom`-setara di app contoh untuk
   halaman error `debug` true/false. Hasil dicatat.
4. `composer audit` tanpa temuan.
5. Dokumen sesuai daftar di atas.
6. `grep -rn "TODO\|FIXME\|ponytail:" panada/` (saat spec ditulis: kosong).
7. Hasil dicatat di spec ini.
8. Commit `chore(release): 0.2.0` di `development`, merge ke `master`
   (merge, bukan rebase), tag `v0.2.0`, push `master` + tag.
9. Setelah push: CI di `master`/tag hijau, Packagist menampilkan 0.2.0,
   lalu `composer create-project karsawave/palakar` di folder kosong
   sekali lagi dari Packagist.

## Yang tidak diubah

Kode di luar string versi. Kalau checklist menemukan bug: berhenti,
perbaiki lewat spec sendiri, ulangi checklist.

## Jawaban

ACC tanpa jawaban terpisah = usulan diterima: (1) `throw` sementara di
folder uji, (2) rilis tidak menunggu laporan upstream, (3) GitHub
Release dibuat.

## Verifikasi

Poin 1–9 di atas, masing-masing dengan hasil tertulis.

## Hasil checklist (2026-10-25)

1. CI commit `f96cd8c`
   (https://github.com/karsawave/palakar/actions/runs/40186560573):
   8.2, 8.3, 8.4, 8.5 `success`, masing-masing `OK (151 tests,
   685 assertions)`, tanpa skipped.
2. Lokal PHP 8.3: 151 test OK (32 skipped, server hanya di CI);
   PHPStan level 5 tanpa error.
3. Uji dari paket (`composer archive`, ekstrak, `composer install`,
   `php -S`): tanpa `tests/`, `_tasks/`, `CLAUDE.md`. Halaman awal 200,
   "version 0.2.0". Guestbook: POST tanpa token 403, dengan token 302,
   pesan `<script>` tampil ter-escape. Header `X-Frame-Options` dan
   `Referrer-Policy` ada. Error (`throw` sementara): `debug` true → 500,
   halaman Whoops dengan pesan; `debug` false → 500, "Something went
   wrong", tanpa pesan/path; detail tercatat di log.
   Catatan: `.phpunit.cache` ikut di arsip lokal karena `composer archive`
   membaca folder kerja; folder itu di-ignore git dan tidak ada di
   `git archive` (dipakai GitHub/Packagist).
4. `composer audit`: tanpa temuan.
5. CHANGELOG `[0.2.0] - 2026-10-25` + link; versi di `home.php`,
   User-Agent Rest (`/0.2`) dan test-nya, CLAUDE.md. SECURITY.md tetap
   ("latest 0.x").
6. `TODO`/`FIXME`/`ponytail:` di `panada/`: kosong.
7. Hasil di atas dicatat sebelum merge (commit spec di `development`).
8. Merge `development` → `master` (`--no-ff`, commit `955a179`), tag
   `v0.2.0`, GitHub Release
   https://github.com/karsawave/palakar/releases/tag/v0.2.0.
9. CI `master` dan tag `v0.2.0`: `success`. Packagist: `v0.2.0`, `v0.1.0`.
   `composer create-project karsawave/palakar . 0.2.0` di folder kosong:
   berhasil, "version 0.2.0", tanpa file development.
