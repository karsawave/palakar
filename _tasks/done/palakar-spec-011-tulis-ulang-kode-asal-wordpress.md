# palakar-spec-011: Tulis ulang kode yang berasal dari WordPress (GPLv2)

- **Status:** done (ACC SPEC 011, 2026-10-25)
- **Dibuat:** 2026-10-25
- **Commit:** `fix(upload)!: detect mime from content, drop GPL-derived code`
  (satu commit; hash lihat `git log -- _tasks/done/palakar-spec-011*`)
- **Bergantung pada:** -

## Masalah

Dua bagian kode diadaptasi dari WordPress (GPLv2), yang tidak kompatibel
dengan BSD-3-Clause:

- `panada/Resources/Pagination.php` → `getUrl()` (docblock: "Thanks to
  Wordpress ... paginate_links").
- `panada/Resources/Upload.php` → daftar `$mimes` (komentar: "Original from
  Wordpress 3.0").

## Keputusan

Tulis ulang dari nol: kode baru diturunkan dari perilaku yang dikunci
test, bukan dari kode lama. Tidak ada dependency Composer baru.

Jawaban pertanyaan terbuka (2026-10-25):

1. Deteksi MIME: **`finfo` saja**, tanpa fallback berdasarkan ekstensi.
   Diterima sebagai breaking change.
2. Baris "Thanks to Wordpress": **disimpan**, ditambah catatan bahwa kode
   sudah ditulis ulang.

## Perubahan per file

- `Pagination.php`: `getUrl()` ditulis ulang dengan cara lain: hitung
  daftar halaman yang tampil (`shownPages()`), lalu sisipkan separator di
  setiap celah. Output identik.
- `Upload.php`: daftar ekstensi→MIME dihapus. `getMimeTypes($path)` memakai
  `finfo(FILEINFO_MIME_TYPE)` pada file yang ada; `upload()` memanggilnya
  dengan `tmp_name`. Email (`attachmentPart()`) sudah memberi path, jadi
  otomatis ikut.
- `composer.json`/`composer.lock`: `ext-fileinfo` di `require`.
- `.github/workflows/ci.yml`: ekstensi `fileinfo`.
- `tests/Unit/PaginationTest.php`: `testEdgeCases` (9 kombinasi opsi),
  ditulis dan dijalankan **terhadap kode lama dulu** untuk mengunci
  perilaku, lalu kode baru lulus test yang sama.
- `tests/Http/UploadTest.php`: `testMimeComesFromTheContentNotTheName`;
  `data.xyz` kini berisi byte biner (isi teks akan terdeteksi `text/plain`).
- `CHANGELOG.md`, `MODERNISASI.md` (breaking change 35), `CLAUDE.md`
  (backlog 2 dihapus, jumlah test).

## Penyimpangan dari rencana

- Spec menulis "API publik tidak diubah". Nama dan signature
  `Upload::getMimeTypes()` tetap, tetapi **artinya berubah**: dulu menerima
  nama file, sekarang path file yang ada (nama saja → `false`). Ini bagian
  dari breaking change yang diterima di jawaban 1 dan dicatat di
  CHANGELOG serta MODERNISASI.md.
- Tambahan kecil di luar daftar: ekstensi `fileinfo` di CI.

## Yang tidak diubah

Opsi `setOption()` `Pagination` dan `Upload`, nama method publik.

## Verifikasi

- `composer test`: 137 test, 485 assertion, OK; 18 skipped (PostgreSQL,
  Redis, Memcached tanpa server lokal; dijalankan di CI).
- `composer analyse`: PHPStan level 2, tanpa error.
- `grep -i wordpress panada/`: hanya baris atribusi/riwayat di docblock.

## Breaking change

`Upload::getMimeTypes()` dan `mime` di `getFileInfo()` berdasarkan isi
file. Lihat MODERNISASI.md nomor 35.
