# palakar-spec-033: Rilis 0.3.1

- **Status:** done (ACC SPEC 033, 2026-11-01)
- **Dibuat:** 2026-11-01
- **Bergantung pada:** spec-022/024 (checklist rilis), spec-031, spec-032

## Masalah

Halaman GitHub Pages (`docs/index.html`, spec-031/032) diambil dari
`master` `/docs`, tetapi baru ada di `development`. Pages juga belum
diaktifkan di repo (API `GET /pages` → 404).

## Isi rilis (dari CHANGELOG `[Unreleased]`)

- Added: halaman GitHub Pages di `docs/index.html`.

Tanpa perubahan kode framework, tanpa breaking change → **patch 0.3.1**.

## Perubahan per file

- `panada/Gear.php`: `VERSION = '0.3.1'`.
- `panada/Resources/Rest.php`: User-Agent tetap `.../0.3` (minor sama);
  dicek ulang.
- `docs/index.html`: judul sleeve `Palakar 0.3.1`.
- `CHANGELOG.md`: `[Unreleased]` → `[0.3.1] - <tanggal>`, `[Unreleased]`
  kosong baru, link perbandingan `v0.3.0...v0.3.1`.
- `SECURITY.md`: dicek ulang ("latest 0.x" mencakup 0.3.1).

## Langkah rilis (checklist CLAUDE.md, semua wajib)

1. Push `development`; CI hijau di HEAD tepat sebelum merge (`headSha` =
   `git rev-parse HEAD`), semua matrix dan service.
2. Lokal: `composer test`, `composer analyse`, `composer cs`.
3. Uji dari paket: `git archive` dari commit rilis, ekstrak di folder
   kosong, `composer install`, `php -S`; halaman awal, Guestbook (form +
   CSRF), halaman error `debug` true/false. `docs/` tidak ada di paket.
4. `composer audit` tanpa temuan.
5. Dokumen sesuai daftar di atas.
6. `grep -rn "TODO\|FIXME\|ponytail:" panada/` kosong.
7. Hasil dicatat di spec ini; tunggu CI commit catatan itu hijau.
8. `chore(release): 0.3.1` di `development`, merge ke `master` (merge,
   bukan rebase), tag `v0.3.1`, push `master` + tag.
9. Setelah push: CI `master` hijau, Packagist menampilkan 0.3.1.
10. **Aktifkan Pages:** `gh api -X POST repos/karsawave/palakar/pages`
    dengan source `master` `/docs`; tunggu build, buka
    `https://karsawave.github.io/palakar/` (terang/gelap, 360px,
    Tab), catat hasilnya di sini.

## Yang tidak diubah

Kode di luar string versi. Bila checklist menemukan bug: berhenti,
perbaiki lewat spec sendiri, ulangi dari poin 1.

## Pertanyaan terbuka

1. Spec-031 mengusulkan Pages diaktifkan manual oleh pemilik. Poin 10
   mengusulkan Claude mengaktifkannya lewat `gh api` (pilihan A
   pengguna). Usul: Claude, karena `gh` sudah login.
2. Release note GitHub seperti 0.3.0? Usul: ya, dari CHANGELOG 0.3.1.

## Breaking change

Tidak ada.

## Hasil checklist (2026-11-01)

Pertanyaan terbuka: ACC tanpa jawaban terpisah = usulan diterima (Claude
mengaktifkan Pages lewat `gh api`; release note GitHub dari CHANGELOG).

1. CI hijau di `564cc95` (`chore(release): 0.3.1`): PHP 8.2–8.5, 152
   test, 702 assertion, tanpa skip.
   https://github.com/karsawave/palakar/actions/runs/41322808768
   CI untuk commit catatan ini: dicek sebelum merge (headSha = HEAD).
2. Lokal (PHP 8.3): 152 test, 504 assertion, 32 skip (test server tanpa
   env); PHPStan bersih; `composer cs` 0 file.
3. Uji dari paket: `git archive` dari `564cc95`. Isi: `.editorconfig`,
   CHANGELOG(-PANADA), CONTRIBUTING, LICENSE, MODERNISASI, README,
   SECURITY, `app`, `composer.*`, `panada`, `public`; tanpa `docs/`.
   `composer install` lalu `php -S`:
   - halaman awal 200, judul "Palakar 0.3.1"; `assets/css/main.css` 200
   - Guestbook: POST tanpa token 403; dengan token 302, entri tampil
   - URL tak dikenal 404; `debug` false: tanpa path/trace di respons
   - Catatan jujur: percobaan pertama saya salah path CSS dan lupa field
     `email` (404/200); diulang dengan nilai benar, hasil di atas.
4. `composer audit`: tanpa temuan.
5. `Gear::VERSION` 0.3.1, `docs/index.html` 0.3.1, CHANGELOG `[0.3.1]`;
   User-Agent Rest `/0.3` tetap (minor sama); SECURITY.md "latest 0.x"
   tidak perlu diubah.
6. `grep TODO|FIXME|ponytail: panada/`: 0.
