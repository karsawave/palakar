# palakar-spec-020: header keamanan, secretKey dari env, composer audit

- **Status:** done (2026-10-25), commit: lihat `git log --grep spec-020`
- **Bergantung pada:** -
- **Catatan:** semula bernomor 018; diganti 020 karena bentrok dengan
  palakar-spec-018-rilis-1-0-0 yang dibuat bersamaan (keputusan pengguna).
- **Pertanyaan terbuka:** dijawab lewat ACC, ketiganya sesuai usulan.
- **Hasil:** 137 test (18 dilewati: PostgreSQL/Redis/Memcached/Memcache
  tanpa server lokal), PHPStan level 2 tanpa error, `composer audit` tanpa
  temuan. `PALAKAR_SECRET_KEY` terbaca dari env, fallback ke placeholder
  bila kosong. CI belum dijalankan (belum di-push). Uji manual Guestbook
  dengan `composer serve` tidak dilakukan; header dan session sudah
  dicakup test HTTP.

## Masalah

1. `Response::$headers` hanya mengirim `X-Content-Type-Options: nosniff`.
   Halaman bisa dipasang di iframe situs lain (clickjacking), dan URL
   lengkap (termasuk query) bocor lewat header `Referer` ke situs luar.
2. `secretKey` ditulis langsung di `app/config/security.php`. Pengguna yang
   mengisinya di situ ikut meng-commit rahasia ke git.
3. CI tidak memeriksa celah keamanan di dependency (Whoops, PHPUnit).

## Keputusan

Tiga perubahan kecil, tanpa kelas, opsi config, atau dependency baru.

1. Dua header default ditambahkan ke array yang sudah ada:
   - `X-Frame-Options: SAMEORIGIN`: satu header, didukung semua browser,
     tidak bentrok dengan CSP milik aplikasi.
   - `Referrer-Policy: strict-origin-when-cross-origin`: sama dengan default
     browser modern, ditulis eksplisit supaya browser lama ikut.

   Aplikasi yang perlu nilai lain cukup memanggil `Response::setHeader()`
   atau `unset(Response::$headers['X-Frame-Options'])`. Mekanisme ini sudah
   ada, jadi tidak perlu opsi config baru.
2. Config contoh membaca env dulu:
   `'secretKey' => getenv('PALAKAR_SECRET_KEY') ?: '_put_your_random_string_here_'`.
   Core tidak berubah, karena config memang file PHP biasa. Validasi di
   `Encryption::secret()` (minimal 32 karakter, menolak placeholder) tetap
   berlaku.
3. CI: satu langkah `composer audit` setelah `composer install`.

## Perubahan per file

- `panada/Resources/Response.php`: 2 baris di `$headers`.
- `app/config/security.php`: baris `secretKey` dan komentarnya.
- `.github/workflows/ci.yml`: `- run: composer audit`.
- `tests/Http/SkeletonAppTest.php`: `testHomePage` memeriksa kedua header
  baru, di samping `nosniff`.
- `CHANGELOG.md` (`[Unreleased]`, Changed/Security): header default baru,
  `PALAKAR_SECRET_KEY`.
- `MODERNISASI.md`: catatan `X-Frame-Options` (lihat Breaking change) dan
  cara memakai env.
- `CLAUDE.md`: baris Aturan keamanan → "Header default: nosniff,
  X-Frame-Options SAMEORIGIN, Referrer-Policy".

## Yang tidak diubah (sengaja, KISS)

- **CSP penuh:** isinya tergantung aplikasi (script inline, CDN). CSP
  default yang ketat merusak aplikasi; yang longgar tidak ada gunanya.
  Didokumentasikan saja, beserta contoh `Response::setHeader()`.
- **HSTS:** milik web server atau TLS terminator. Nilai yang salah bisa
  mengunci domain ke HTTPS selama berbulan-bulan, dan framework tidak tahu
  apakah semua subdomain sudah HTTPS.
- **Prefix cookie `__Host-`:** mengganti nama cookie session membuat semua
  pengguna logout, dan gagal kalau `cookieDomain`/`cookiePath` diisi.
  Bisa jadi spec terpisah kalau ada yang membutuhkan.
- **Loader `.env`:** `getenv()` sudah cukup. Env diisi dari web server,
  container, atau `php -S` (`PALAKAR_SECRET_KEY=... composer serve`).
- Helper password dan rate limiting: `password_hash()` bawaan PHP sudah
  cukup, dan rate limiting urusan aplikasi atau web server.

## Breaking change

`X-Frame-Options: SAMEORIGIN` memblokir aplikasi yang sengaja ditampilkan
di iframe domain lain. Cara pindah: `unset(Response::$headers['X-Frame-Options'])`
di controller atau front controller. Dicatat di MODERNISASI.md dan
CHANGELOG.md. Karena masih 0.x, rilisnya cukup menaikkan versi minor.

## Pertanyaan terbuka

1. Nama env: `PALAKAR_SECRET_KEY`? Usulan: ya, sesuai nama paket. Prefix
   `PANADA_` hanya dipakai test fixture.
2. Abandoned package: `composer audit` (Composer ≥ 2.8) juga gagal kalau
   ada dependency yang abandoned. Usulan: biarkan gagal, karena ini sinyal
   yang berguna dan saat ini dependency-nya hanya Whoops dan PHPUnit.
   Alternatifnya `--abandoned=report`.
3. `X-Frame-Options` atau CSP `frame-ancestors 'self'`? Usulan:
   `X-Frame-Options`, supaya framework tidak mengirim header CSP yang bisa
   membingungkan aplikasi yang menulis CSP sendiri.

## Verifikasi

- `composer test` hijau, termasuk assert header baru di `SkeletonAppTest`.
- `composer analyse` hijau.
- `composer audit` lokal tanpa temuan. CI hijau di 8.2 dan 8.3.
- Manual: `PALAKAR_SECRET_KEY=<64 hex> composer serve`, lalu form Guestbook
  (CSRF dan session) tetap jalan.
