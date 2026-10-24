# palakar-spec-009: Aturan pesan commit (Conventional Commits)

- **Status:** done (ACC SPEC 009, 2026-10-25)
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

## Penulisan ulang riwayat (diputuskan 2026-10-25)

Repo GitHub dibuat ulang oleh pemilik (lepas dari jaringan fork
`panada/app`), jadi riwayat **boleh** ditulis ulang sebelum push ulang.

- Cadangan dulu: `git bundle create ../panadax-before-spec-009.bundle --all`.
- Hanya **ringkasan** (baris pertama) yang diganti; isi pesan, author,
  tanggal, dan isi file tetap. Footer ditambahkan: `Refs: spec-NNN` (spec-000
  untuk modernisasi awal) dan `BREAKING CHANGE: see MODERNISASI.md` untuk
  commit bertanda `!`.
- Commit baseline `cb111a1` (Panada asli) **tidak diubah** (aturan CLAUDE.md:
  riwayat upstream dijaga), commit `3e5ca49` sudah sesuai.
- Alat: `git filter-branch --msg-filter` dengan tabel di bawah.
- Hash di spec-000 dan spec-007 diperbarui ke hash baru.

| Lama | Baru | Refs |
|---|---|---|
| step 1: PHP 8.2+ core, Composer PSR-4 with Panada autoloader fallback | feat(core)!: require PHP 8.2+, add Composer PSR-4 autoloading | 000 |
| step 2: PDO-only database layer with automatic prepared statements | feat(database)!: use PDO only, bind every value | 000 |
| step 3: Validation with auto-sanitize, built-in CSRF protection | feat(validation): auto-sanitize input, add built-in CSRF | 000 |
| step 4: interactive error pages (optional Whoops) and modern session cookies | feat: add interactive error pages and hardened session cookies | 000 |
| PHP 8 fixes found by PHPStan in legacy resources | fix: apply PHP 8 fixes found by PHPStan in legacy resources | 000 |
| step 5: PHPUnit 11 test suite (unit + real HTTP), requirement checker | test: add PHPUnit 11 suite and requirement checker | 000 |
| docs: MODERNISASI.md (Indonesian upgrade guide), README, CHANGELOG | docs: add MODERNISASI.md upgrade guide, README, CHANGELOG | 000 |
| docs: CLAUDE.md project context, tracked .gitignore | docs: add CLAUDE.md project context, track .gitignore | 000 |
| fix: Uri parses Windows paths with backslashes | fix(uri): parse Windows paths with backslashes | 000 |
| chore: commit composer.lock | build: commit composer.lock | 000 |
| chore: PHPStan level 2 as a dev tool (composer analyse) | build: add PHPStan level 2 as dev tool (composer analyse) | 000 |
| ActiveRecord: strict types, tests, fix lost id and leaking query state | fix(activerecord)!: keep new id, reset query state, add tests | 000 |
| Pagination: strict types, tests, getUrl() works again | fix(pagination)!: make getUrl() work, escape links, add tests | 000 |
| Rest: strict types, tests, fix PATCH, piling headers and response status | fix(rest)!: send PATCH, build headers per request, keep status | 000 |
| Upload: strict types, tests, refuse executable files and path traversal | fix(upload)!: refuse executable files and path traversal | 000 |
| Image: strict types, tests, whole pixels, transparency, no chmod 0666 | fix(image): use whole pixels, keep transparency, drop chmod 0666 | 000 |
| Email: rewrite internals, block header injection, enforce STARTTLS | fix(email)!: block header injection, enforce STARTTLS | 000 |
| Tools: strict types, tests, fix xmlDecode and XML escaping | fix(tools)!: repair xmlDecode() and escape XML values | 000 |
| Cache drivers: strict types, one contract test, fix fatal flush and Apc | fix(cache)!: repair dummy flush, drop Apc __call, add tests | 000 |
| ci: GitHub Actions with PostgreSQL, Redis, Memcached and PHPStan | ci: add GitHub Actions with PostgreSQL, Redis, Memcached | 000 |
| docs: open source rules (license, attribution, naming) in CLAUDE.md | docs: add open source rules to CLAUDE.md | 000 |
| docs: spec workflow (_tasks, ACC SPEC) and transparency rules; spec-001 plan | docs: add spec workflow and transparency rules | 001 |
| spec-001: answers to open questions (KarsaWave, no email, repo URL) | docs(spec): answer open questions of spec 001 | 001 |
| specs: 000 (retroactive modernization) done; 002-005 planned | docs(spec): add retroactive spec 000, plan specs 002-005 | 000 |
| spec-001: rebrand to Palakar Framework (karsawave/palakar 0.1.0) | feat!: rebrand to Palakar Framework (karsawave/palakar) | 001 |
| spec-002: SECURITY.md, private vulnerability reporting | docs: add SECURITY.md with private vulnerability reporting | 002 |
| spec-003: CONTRIBUTING.md | docs: add CONTRIBUTING.md | 003 |
| spec-005: CHANGELOG.md per version (Keep a Changelog, SemVer) | docs: add CHANGELOG.md per version (Keep a Changelog) | 005 |
| specs: 004 postponed, 006 planned (release 0.1.0 on Packagist) | docs(spec): postpone spec 004, plan spec 006 | 006 |
| specs: 007 planned (replace personal email in git history before first push) | docs(spec): plan spec 007 | 007 |
| spec-007: personal email replaced in git history | chore: replace personal email in git history | 007 |
| spec-007: GitHub email privacy settings confirmed | docs(spec): record GitHub email privacy settings | 007 |
| specs: 008 planned (master/development branch rules, LICENSE link) | docs(spec): plan spec 008 | 008 |

## Tidak diubah

Commit baseline Panada; isi file; author dan tanggal. Format berlaku juga
untuk semua commit berikutnya.

## Pertanyaan terbuka

Tidak ada. (Alat otomatis seperti commitlint sengaja tidak dipasang: tidak ada
dependency baru; bisa jadi spec lain bila perlu.)

## Verifikasi

`composer test` hijau; commit spec ini sendiri sudah memakai format baru.

## Hasil

- Cadangan: `../panadax-before-spec-009.bundle` (valid).
- 34 ringkasan commit ditulis ulang sesuai tabel, footer `Refs` dan
  `BREAKING CHANGE` ditambahkan di dalam blok trailer. Commit baseline tetap
  `cb111a1`. Satu-satunya ringkasan di luar format: baseline (sengaja).
  Tidak ada ringkasan lebih dari 72 karakter. Jumlah commit tetap 36.
- Ref `origin/*` lama (repo GitHub yang sudah dihapus) dibuang; repo baru
  masih kosong.
- Hash diperbarui di spec-000 dan spec-009.
- Aturan "Pesan commit" di CLAUDE.md dan CONTRIBUTING.md; spec-008 memakai
  format ini.
- `composer test`: 135 test, 18 di-skip.
