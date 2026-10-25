# palakar-spec-026: standar gaya kode (EditorConfig + PER-CS)

**Status:** done (2026-10-25)

## Masalah

Hasil audit 111 file PHP (`panada/`, `app/`, `tests/`):

| # | Temuan | Dampak |
|---|--------|--------|
| 1 | Gaya kode tidak tertulis dan tidak dicek otomatis | Kontributor baru menebak gaya; review habis untuk spasi |
| 2 | Tidak ada `.editorconfig` | Editor di Windows bisa menyimpan CRLF atau tab; `.gitattributes eol=lf` hanya memperbaiki saat commit |
| 3 | Inkonsistensi kecil di view, mis. `<?=$this->output('header');?>` vs `<?= e(...) ?>` | Sulit dibaca, contoh buruk untuk pemula |
| 4 | `panada/Resources/Import.php` tanpa `declare(strict_types=1)` dan tidak ada di daftar pengecualian CLAUDE.md | Melanggar konvensi proyek, entah sengaja atau terlewat |
| 5 | Positif: 0 file ber-tab, 0 trailing whitespace, LF dijaga `.gitattributes` | Penerapan standar hanya butuh perubahan kecil |

## Keputusan

- **EditorConfig** (`.editorconfig`): UTF-8, LF, 4 spasi untuk PHP, 2 untuk YAML/JSON, newline di akhir file. Dibaca langsung oleh VS Code, JetBrains, Vim, dll., tanpa dependency.
- **PER Coding Style 2.0** (PHP-FIG, penerus PSR-12) sebagai standar tertulis.
- **PHP-CS-Fixer** (MIT, kompatibel dengan BSD-3) di `require-dev`, aturan `@PER-CS2.0` + `declare_strict_types` **tidak** diaktifkan otomatis (pengecualian di CLAUDE.md harus tetap tanpa strict).
  - `composer cs` (cek, `--dry-run --diff`) dan `composer cs:fix` (perbaiki).
  - CI menjalankan `composer cs` pada satu versi PHP saja (gaya tidak bergantung versi).
- Perapian awal dalam **commit terpisah** `chore(cs): apply PER-CS 2.0`
  (type `style` tidak ada di daftar CLAUDE.md), supaya `git blame` bisa mengabaikannya lewat `.git-blame-ignore-revs`.
- `Import.php`: dicek alasannya; bila tidak ada alasan, ditambah strict types dan test tetap hijau, bila ada, ditambah ke daftar pengecualian CLAUDE.md.

### Alternatif yang ditolak

| Alternatif | Alasan |
|------------|--------|
| PHP_CodeSniffer (BSD-3) | Setara, tetapi fixer-nya (`phpcbf`) lebih lemah; satu alat cukup |
| Laravel Pint | Pembungkus PHP-CS-Fixer; dependency ekstra tanpa nilai tambah |
| Gaya sendiri | Tidak dikenal kontributor; PER-CS adalah standar komunitas PHP |
| Pre-commit hook wajib | Memaksa setup lokal; CI sudah cukup sebagai penjaga |

## Analisis penerimaan (semua kalangan)

| Kalangan | Kebutuhan | Bagaimana dipenuhi |
|----------|-----------|-------------------|
| Pemula | Tidak perlu hafal aturan | `composer cs:fix` merapikan otomatis; pesan CI menunjukkan diff |
| Kontributor berpengalaman | Standar yang sudah dikenal | PER-CS dipakai luas di ekosistem PHP |
| Pengguna framework | Tidak terbebani | Hanya `require-dev`; `composer create-project --no-dev` tidak ikut mengunduh; core tanpa dependency baru |
| Developer Windows | Tidak ada CRLF/tab nyasar | EditorConfig + `.gitattributes` |
| Maintainer | Review fokus ke logika | Gaya dicek mesin |

## Perubahan per file

- `.editorconfig` (baru).
- `.php-cs-fixer.dist.php` (baru): `@PER-CS2.0`, folder `panada/`, `app/`, `tests/`, `public/`.
- `composer.json`: `friendsofphp/php-cs-fixer` di `require-dev`, script `cs` dan `cs:fix`.
- `.gitattributes`: `.php-cs-fixer.dist.php export-ignore`; `.gitignore`: `.php-cs-fixer.cache`.
- `.github/workflows/ci.yml`: langkah `composer cs`.
- `.git-blame-ignore-revs` (baru): hash commit perapian.
- `CONTRIBUTING.md`: bagian "Gaya kode" (`composer cs:fix` sebelum commit).
- `CLAUDE.md`: `composer cs` ditambahkan ke perintah wajib hijau sebelum commit dan ke checklist rilis poin 2.
- File PHP yang dirapikan fixer (perubahan spasi saja, tanpa ubah perilaku).

## Yang tidak diubah

- Perilaku kode; PHPStan level 5; konvensi strict types beserta pengecualiannya.

## Pertanyaan terbuka

1. Setuju menambah `friendsofphp/php-cs-fixer` di `require-dev`? (dependency dev pertama sejak spec CI)
2. Perapian awal satu commit besar sekarang, atau bertahap per folder? Usul: satu commit, plus `.git-blame-ignore-revs`.
3. `Import.php`: lihat Keputusan; temuan dilaporkan sebelum diubah.

## Verifikasi

- `composer cs`, `composer test`, `composer analyse` hijau; jumlah test sama sebelum dan sesudah perapian.
- CI hijau di semua matrix.
- `composer audit` tanpa temuan setelah dependency baru.

## Breaking change

Tidak ada.

## Urutan

Dikerjakan **sebelum** spec-025, supaya view baru Bingkai langsung mengikuti standar dan diff spec-025 tidak tercampur perapian.

## Hasil

- Commit: `36555c6` (alat + dokumen), `d98cef6` (perapian, di
  `.git-blame-ignore-revs`), dan commit ini (langkah CI + catatan spec).
  Langkah CI masuk terakhir supaya tidak ada commit dengan CI merah.
- Pertanyaan terbuka: diterima sesuai usul (ACC SPEC 026).
  `Import.php` ditambah ke daftar pengecualian strict di CLAUDE.md, karena
  meneruskan argumen ke kelas vendor lama; perilaku tidak berubah.
- `.editorconfig` sengaja **tidak** `export-ignore`: berguna untuk aplikasi
  pengguna dari `create-project`.
- PHP-CS-Fixer 3.95.27: 71 dari 112 file dirapikan. Aturan yang terpakai:
  `concat_space` (64), `trailing_comma_in_multiline` (14),
  `function_declaration` (10), `single_line_empty_body` (6), sisanya ≤ 4.
- Lokal (PHP 8.3): 151 test, 487 assertion, 32 skip (test server
  PostgreSQL/MySQL/Redis/Memcached, tanpa env); sama dengan sebelum
  perapian. PHPStan level 5 tanpa temuan. `composer cs` 0 file.
  `composer audit` tanpa temuan.
- CI: belum jalan (belum di-push).
