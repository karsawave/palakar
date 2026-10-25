# palakar-spec-022: Checklist uji wajib sebelum rilis

- **Status:** done (ACC SPEC 022, 2026-10-25)
- **Dibuat:** 2026-10-25
- **Bergantung pada:** spec-006, spec-008

## Masalah

Aturan rilis saat ini hanya "merge `development` → `master`, beri tag".
Tidak ada syarat uji tertulis. Contoh nyata: bug halaman error kosong di
PHP 8.5 baru ketahuan setelah 8.5 masuk CI (spec-014). Rilis tanpa
pengujian menumpuk utang teknis yang ditemukan pengguna, bukan kita.

## Keputusan

Rilis (tag `vX.Y.Z`) hanya boleh dibuat bila semua poin checklist di
bawah terpenuhi dan hasilnya dicatat di spec rilis. Kalau satu poin
gagal: perbaiki di `development` (spec sendiri bila perlu), lalu ulangi
checklist dari awal. Tidak ada "rilis dulu, perbaiki nanti".

## Perubahan per file

`CLAUDE.md`, bagian baru **"Rilis (wajib)"** setelah "Branch":

1. **CI hijau** di commit `development` yang akan dirilis: semua versi
   PHP di matrix, PostgreSQL, MariaDB, Redis, Memcached, dan tanpa test
   yang dilewati di CI.
2. **Lokal:** `composer test` dan `composer analyse` hijau.
3. **Uji dari paket, bukan dari repo:** `composer create-project` dari
   tag kandidat (atau `composer archive`) di folder kosong, lalu
   `composer serve` dan halaman awal, demo Guestbook (form + CSRF), serta
   halaman error (`debug` true dan false) dibuka manual.
4. **`composer audit`** tanpa temuan.
5. **Dokumen:** CHANGELOG `[Unreleased]` diubah jadi `[X.Y.Z] - tanggal`;
   setiap breaking change ada di CHANGELOG dan MODERNISASI.md;
   `SECURITY.md` (versi yang didukung) diperbarui.
6. **Tidak ada utang tersembunyi:** `grep -rn "TODO\|FIXME\|ponytail:"
   panada/` diperiksa; setiap temuan diperbaiki atau ditulis di backlog
   CLAUDE.md beserta alasannya.
7. **Hasil dicatat** di spec rilis: hash commit, link run CI, jumlah
   test, hasil uji manual. Baru setelah itu merge ke `master` dan tag.

`CONTRIBUTING.md`: satu kalimat yang menautkan ke checklist ini.

## Yang tidak diubah

Alur branch (spec-008) dan format commit rilis `chore(release): X.Y.Z`
(spec-021).

## Jawaban

ACC tanpa jawaban terpisah = usulan diterima:

1. Uji manual wajib di setiap rilis, termasuk patch.
2. `TODO`/`FIXME`/`ponytail:` boleh ada saat rilis asal tercatat di
   Backlog beserta alasannya.

## Catatan implementasi

`CONTRIBUTING.md` masih menulis "PHPStan level 2"; ikut diperbaiki jadi
level 5 (terlewat di spec-015).

## Verifikasi

Checklist ini dipakai pertama kali di spec rilis 0.2.0.
