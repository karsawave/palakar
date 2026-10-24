# CLAUDE.md — Palakar Framework

Konteks proyek untuk Claude Code. Detail lengkap perubahan dan catatan migrasi
dari Panada 1.x ada di `MODERNISASI.md`.

## Tentang proyek

**Palakar Framework** (`karsawave/palakar`, versi 0.1.0,
https://github.com/karsawave/palakar), fork Panada Framework 1.1.1 (PHP,
Indonesia, 2010) yang dimodernisasi untuk PHP 8.2+. Folder core tetap bernama
`panada/` dan namespace tetap `Resources\`/`Drivers\`, supaya aplikasi lama
tetap jalan. Filosofi wajib dijaga: **KISS, transparan, seluruh core bisa dibaca
pemula dalam semalam.** Jangan menambah lapisan abstraksi, container DI,
middleware stack, atau dependency baru di core. Kalau ragu, pilih kode yang
lebih pendek dan lebih mudah dibaca.

## Status saat ini

Selesai dan teruji (135 test, PHP 8.3 lokal; CI: PHP 8.2 + 8.3, PostgreSQL 16,
Redis, Memcached, APCu; PHPStan level 2 tanpa baseline):

1. Kompatibilitas PHP 8.2/8.3 (strict types, typed props, tanpa dynamic property)
2. Composer PSR-4 + autoloader Panada sebagai fallback
3. Database PDO-only, prepared statement otomatis
4. Validation dengan sanitize otomatis + CSRF bawaan
5. Error handler (Whoops opsional) di fase bootstrap
6. Session cookie modern (SameSite, Secure auto, HttpOnly, strict mode)
7. Komponen lama (ActiveRecord, Pagination, Rest, Upload, Image, Email, Tools,
   driver cache): strict types, test, bug dan celah keamanan diperbaiki
8. CI GitHub Actions (`.github/workflows/ci.yml`)

Riwayat git: satu commit per langkah (`git log -p` untuk membaca alasannya).

## Perintah

```bash
composer install                    # Whoops + PHPUnit (dev)
composer serve                      # php -S localhost:8000 -t public public/index.php
composer test                       # semua test (unit + http)
composer analyse                    # PHPStan level 2 (phpstan.neon.dist)
vendor/bin/phpunit --testsuite unit
vendor/bin/phpunit --testsuite http
vendor/bin/phpunit --filter ValidationTest

# Test dengan server (opsional, dilewati kalau env kosong):
PANADA_TEST_PGSQL="host=127.0.0.1;dbname=panada_test;user=panada;password=panada" composer test
PANADA_TEST_REDIS=127.0.0.1:6379 PANADA_TEST_MEMCACHED=127.0.0.1:11211 composer test
```

`composer test` dan `composer analyse` wajib hijau sebelum commit. PHPStan
tidak memakai baseline; temuan baru diperbaiki, bukan diabaikan.

## Struktur

```
public/index.php        front controller: define APP, GEAR, VENDOR → Gear::send()
panada/Gear.php         bootstrap + routing (controller → sub-controller → module → alias)
panada/helpers.php      e(), csrf_field(), csrf_token()
panada/Resources/       komponen inti (Controller, Config, Uri, Request, Response,
                        ErrorHandler, Csrf, Validation, Session, Database, Encryption, ...)
                        + ActiveRecord, Pagination, Rest, Upload, Image, Email, Tools, Cache
  Configurable.php      trait setOption() untuk Pagination/Upload/Image/Email
panada/Drivers/
  Abstraction/Sql.php   query builder (semua nilai di-bind)
  Database/PanadaPDO.php satu-satunya driver DB (mysql, pgsql, sqlite)
  Session/              Native, Database(+Handler), Cache(+Handler), Cookie
  Cache/                Dummy, Apc, Memcached, Memcache, Redis (+ trait KeyNamespace)
app/                    aplikasi contoh (Home, Guestbook demo, ModSample module)
app/config/             main.php (debug, trustProxyHeaders), database.php,
                        security.php (secretKey, csrf), session.php, cache.php
app/storage/            SQLite default (diabaikan git)
tests/Unit/             test komponen
tests/Http/             request HTTP sungguhan ke `php -S`
tests/Fixtures/         app + public khusus test (APP terpisah dari app/ asli),
                        smtp.php (server SMTP palsu untuk EmailTest)
.github/workflows/ci.yml CI: composer install, test (PostgreSQL/Redis/Memcached), analyse
```

## Konvensi kode

- `declare(strict_types=1)` di semua file baru, **kecuali** file yang hanya
  meneruskan panggilan: `Gear.php`, `Resources/Database.php`,
  `Resources/Session.php`, `Resources/Cache.php`, `Resources/PropertiesLoader.php`.
  Segmen URL selalu string; kalau file ini strict, `show(int $id)` jadi TypeError.
- Komentar dan docblock dalam bahasa Inggris (mengikuti kode asli), singkat,
  menjelaskan *kenapa*. Dokumentasi untuk pengguna dalam bahasa Indonesia.
- Tidak boleh ada dynamic property. Pengecualian yang disengaja:
  `ActiveRecord` (`#[\AllowDynamicProperties]`).
- Properti/method yang biasa di-override kelas pengguna (`ActiveRecord::$table`,
  `$connection`, `$primaryKey`, `relations()`, ...) dan return type di
  `Interfaces\Cache` sengaja tanpa tipe: PHP menolak override tanpa tipe bila
  induknya bertipe.
- Opsi lewat properti publik + `setOption()` dari trait `Resources\Configurable`
  (opsi tak dikenal → exception, string di-cast ke tipe properti).
- Exception: `RunException` untuk error runtime (500), `HttpException` dengan
  kode status HTTP (default 404). Jangan memanggil `outputError()` lalu lanjut
  jalan; lempar exception.
- Pertahankan API publik lama Panada 1 bila memungkinkan (lihat signature lama
  yang masih diterima, mis. `results($sql, 'array')`).

## Aturan keamanan (jangan dilonggarkan)

- **Database:** nilai selalu lewat binding `?`/`:name`. Nama tabel/kolom
  divalidasi regex di `Sql.php`. Hanya `select()`, `whereRaw()`, dan `query()`
  yang menerima SQL mentah. `update()`/`delete()` tanpa WHERE ditolak.
- **View:** semua data dicetak dengan `e()`, termasuk pesan error.
- **CSRF:** Gear memeriksa setiap request selain GET/HEAD/OPTIONS sebelum
  controller jalan. Pengecualian hanya lewat `security.php` → `csrf.except`.
- **Routing:** hanya method publik yang dideklarasikan di controller pengguna
  yang bisa dipanggil lewat URL (`Gear::isRoutable()`).
- **Error:** `debug => false` tidak boleh membocorkan pesan, path, atau trace.
- **Session:** strict mode, HttpOnly, SameSite; `X-Forwarded-Proto` hanya
  dipercaya bila `trustProxyHeaders` true.
- **Kripto:** pakai libsodium (`Resources\Encryption`), `hash_equals`,
  `random_bytes`. Jangan `unserialize` data dari klien.

## Open source: lisensi, atribusi, etika (wajib)

Proyek ini (nama baru: **Palakar Framework**, paket `karsawave/palakar`, lihat
palakar-spec-001) fork dari Panada Framework 1.1.1, lisensi BSD-3-Clause
(`LICENSE`, © 2011 Iskandar Soesman; sumber:
https://github.com/panada/panada?tab=License-1-ov-file). Mengubah dan
mendistribusikan ulang boleh tanpa izin, dengan syarat berikut.

- **Notice asli tidak diubah atau dihapus:** isi `LICENSE`, baris copyright,
  dan tag `@author`/`@link` di file yang berasal dari Panada. Copyright untuk
  modifikasi ditambahkan sebagai baris baru di bawah yang asli.
- **Nama dan merek Panada** (nama, logo, nama kontributor) tidak dipakai untuk
  mempromosikan fork: tidak di nama paket, judul halaman, header HTTP/email,
  User-Agent, banner, atau logo. Atribusi faktual boleh dan harus ada:
  "fork dari Panada Framework 1.1.1 karya Iskandar Soesman".
- **Jangan mengesankan resmi:** homepage, issue, support, dan dokumentasi
  mengarah ke repo fork, bukan `github.com/panada/*` atau `panadaframework.com`.
  Fork punya nomor versi sendiri (SemVer), tidak melanjutkan versi Panada.
- **Kode dan aset dari luar** hanya yang lisensinya kompatibel dengan BSD-3
  (MIT, BSD, ISC, Apache-2.0); notice-nya ikut disalin dan sumbernya dicatat.
  GPL/AGPL, CC BY-SA (jawaban Stack Overflow), atau tanpa lisensi: tulis ulang
  sendiri, jangan salin. Berlaku juga untuk dependency, termasuk `require-dev`.
- **Atribusi pihak lain dipertahankan**, mis. komentar "Thanks to Wordpress"
  dan "Thanks to djdykes".
- **AI:** commit yang dibantu AI memakai trailer `Co-Authored-By`, tidak
  disembunyikan. Kode dari AI direview dan diuji seperti kode lain, dan tidak
  boleh berisi salinan verbatim kode berlisensi lain.
- **Riwayat git upstream** (commit `baseline`) tidak di-squash atau dihapus:
  itu bukti asal-usul kode.
- **Celah keamanan** yang belum diperbaiki tidak ditulis di issue, PR, atau
  commit publik. Celah yang juga ada di Panada asli: kabari maintainer upstream
  secara privat sebelum detailnya dipublikasikan.
- **Komunikasi publik** (README, issue, PR, release note) sopan dan faktual,
  tidak merendahkan proyek asli atau penulisnya: "diperbaiki: ..." boleh,
  "kode lama buruk" tidak.

## Alur kerja spec (wajib)

Setiap perubahan kode atau dokumen proyek dimulai dari spec di `_tasks/`.

- Nama file: `palakar-spec-{NNN}-{masalah-singkat}.md`, nomor 3 digit
  berurutan (`001`, `002`, ...), slug huruf kecil dengan tanda hubung.
- Spec baru masuk `_tasks/plan/`. Isinya: status, masalah, keputusan,
  perubahan per file, yang tidak diubah, pertanyaan terbuka, verifikasi,
  dan breaking change bila ada.
- **Implementasi hanya setelah pengguna menulis `ACC SPEC {NNN}`** (mis.
  `ACC SPEC 001`). Tanpa itu: boleh membaca, menganalisis, dan menulis
  atau merevisi spec, tetapi tidak mengubah kode.
- Pertanyaan terbuka harus dijawab sebelum ACC. Kalau saat implementasi
  ternyata perlu menyimpang dari spec, berhenti, perbarui spec, minta ACC lagi.
- Selesai: spec dipindah ke `_tasks/done/` (pakai `git mv`), diberi status
  `done`, tanggal, hash commit, dan hasil verifikasi (jumlah test, PHPStan).
  Satu spec = satu commit (atau beberapa commit yang disebut di spec).
- Nomor spec ditulis di footer pesan commit: `Refs: spec-NNN` (lihat bagian
  Pesan commit).
- Spec yang mengubah perilaku menambah entri di `CHANGELOG.md` bagian
  `[Unreleased]` (Keep a Changelog, SemVer). `CHANGELOG-PANADA` adalah
  riwayat upstream dan tidak diubah.
- Spec yang dibatalkan tetap disimpan dengan status `cancelled` dan alasannya,
  supaya riwayat keputusan tetap transparan.

## Transparansi (wajib)

- Laporan ke pengguna jujur: test yang di-skip, bagian yang tidak bisa
  diverifikasi (mis. CI belum pernah jalan, driver tanpa server), dan
  penyimpangan dari rencana disebut terang-terangan.
- Breaking change selalu dicatat di MODERNISASI.md; perubahan perilaku tidak
  boleh diam-diam.
- Pesan commit menjelaskan *kenapa*, bukan hanya *apa*.
- Keputusan desain dan alasannya ditulis di spec atau CLAUDE.md, tidak hanya
  di percakapan.

## Pesan commit (wajib, Conventional Commits 1.0.0)

```
<type>(<scope>): <ringkasan>

<isi: kenapa perubahan ini perlu, apa dampaknya>

Refs: spec-NNN
BREAKING CHANGE: <apa yang berubah, cara pindah>   (hanya bila ada)
Co-Authored-By: Claude ... <noreply@anthropic.com> (bila dibantu AI)
```

- **type:** `feat`, `fix`, `docs`, `test`, `refactor` (tanpa ubah perilaku),
  `perf`, `build` (composer, PHPStan), `ci`, `chore`, `revert`. Celah keamanan
  memakai `fix`.
- **scope** (opsional): komponen huruf kecil, mis. `email`, `upload`,
  `database`, `cache`, `session`, `spec`, `release`.
- **Ringkasan:** bahasa Inggris, kalimat perintah ("add", bukan "added"),
  huruf kecil di awal, tanpa titik, maksimal 72 karakter.
- **Isi:** bahasa Inggris, baris maksimal 72 karakter, menjelaskan *kenapa*.
  Boleh dilewati untuk perubahan sepele.
- **Breaking change:** `!` setelah type/scope (`fix(upload)!: ...`) dan footer
  `BREAKING CHANGE:`; juga dicatat di MODERNISASI.md dan CHANGELOG.md.
- Satu commit = satu perubahan logis.
- **Dilarang:** rahasia, token, password, email pribadi, path pribadi
  (`C:\Users\...`), dan detail celah yang perbaikannya belum dirilis.
- Riwayat yang sudah di-push tidak ditulis ulang. Pengecualian satu kali:
  spec-009 menulis ulang ringkasan commit lama sebelum repo baru di-push.
  Commit baseline Panada (`cb111a1`) tidak pernah diubah.

## Keputusan desain (sudah diputuskan, jangan diubah tanpa alasan kuat)

- Composer PSR-4 hanya memetakan `Resources\` dan `Drivers\`. Kelas aplikasi
  (`Controllers\`, `Models\`, `Libraries\`, `Modules\`) dimuat autoloader Panada
  dari folder `APP`, karena `APP` bisa diarahkan ke folder lain.
- Autoloader Panada mengikuti PSR-4: tidak pernah melempar exception.
- Whoops dipakai hanya sebagai renderer (`writeToOutput(false)`), tidak
  didaftarkan global, supaya respons tetap lewat `Resources\Response`.
- Deprecation hanya di-log, tidak dilempar (penyebab Panada lama mati di 8.2).
- Controller yang me-return array dikirim sebagai JSON; `echo` juga ditangkap.
- Segmen URL non-numerik untuk parameter `int`/`float` → 404, bukan 500.
- Default database: SQLite di `app/storage/database.sqlite` (tanpa setup).

## Hal yang perlu diingat saat menguji

- `php -S` dengan OPcache aktif bisa menyimpan config lama sampai 2 detik;
  test HTTP menjalankan server dengan `-d opcache.enable=0`.
- Test HTTP butuh ekstensi `curl` dan bisa menjalankan `php -S`.
- `tests/Fixtures/app/config/*.php` membaca env (`PANADA_DEBUG`,
  `PANADA_NO_COMPOSER`, `PANADA_TRUST_PROXY`, `PANADA_SESSION_DRIVER`, ...)
  supaya satu fixture bisa diuji dalam banyak mode.
- Test session native memakai `#[RunInSeparateProcess]`.
- Test juga dijalankan di Windows: path dari `$_SERVER` bisa memakai `\`, dan
  file SQLite yang masih terbuka tidak bisa dihapus (test ActiveRecord memakai
  `DROP TABLE`, bukan menghapus file).
- Upload hanya bisa diuji lewat HTTP (`move_uploaded_file`); `HttpTestCase`
  mengirim multipart bila data berisi `CURLFile`.
- PHPUnit memakai `failOnWarning`/`failOnDeprecation`: warning di kode yang diuji
  langsung membuat test gagal.

## Backlog (urut prioritas)

1. **Sebelum repo publik:** spec 004 (CODE OF
   CONDUCT)
   dan kabari upstream soal celah Panada 1.x yang
   dijelaskan di MODERNISASI.md. (Ganti merek ke Palakar: selesai, spec-001.)
2. **Asal-usul lisensi:** `Pagination::getUrl()` (diadaptasi dari
   `paginate_links()` WordPress) dan daftar MIME di `Upload` ("Original from
   Wordpress 3.0") berasal dari WordPress yang berlisensi GPLv2. Periksa atau
   tulis ulang supaya tidak ada keraguan lisensi.
3. Test MySQL/MariaDB (sejauh ini hanya SQLite dan PostgreSQL yang diuji).
4. `panada/Modules/ModSample` adalah sisa contoh lama yang tidak dipakai
   (module aktif ada di `app/Modules`, dikecualikan dari PHPStan); putuskan
   hapus atau rapikan.
5. Driver cache `memcache` (ekstensi lama) belum diuji di CI; PHP 8.4 belum ada
   di matrix CI.
