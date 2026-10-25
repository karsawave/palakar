# Berkontribusi ke Palakar Framework

Terima kasih sudah mau membantu. Panduan ini singkat supaya mudah diikuti.

## Filosofi

- **KISS dan transparan:** seluruh core harus bisa dibaca pemula dalam
  semalam. Pilih kode yang lebih pendek dan lebih mudah dibaca.
- **Tanpa dependency baru di core**, tanpa container DI, tanpa middleware
  stack. Paket tambahan hanya boleh di `require-dev`.
- **Keamanan tidak dilonggarkan:** nilai database selalu di-bind, view
  memakai `e()`, CSRF global, mode `debug => false` tidak membocorkan detail.

## Alur kerja

1. **Buka issue** untuk menjelaskan masalah atau ide.
2. **Tulis spec** di `_tasks/plan/palakar-spec-{NNN}-{masalah-singkat}.md`
   (nomor 3 digit berikutnya). Isinya: masalah, keputusan, perubahan per file,
   yang tidak diubah, pertanyaan terbuka, cara verifikasi, breaking change.
   Contoh: lihat `_tasks/done/`.
3. **Tunggu persetujuan:** maintainer menulis `ACC SPEC {NNN}`. Pekerjaan
   besar tanpa spec yang disetujui kemungkinan tidak di-merge.
4. **Kerjakan dan kirim PR ke branch `development`** (bukan `master`;
   `master` hanya untuk rilis bertag). Satu spec = satu PR. Pesan commit mengikuti
   [Conventional Commits](https://www.conventionalcommits.org/), lihat
   bagian "Pesan commit" di bawah.
5. Setelah selesai, spec dipindah ke `_tasks/done/` beserta hasilnya.

Perbaikan kecil (salah ketik, dokumentasi) boleh langsung PR tanpa spec.

## Pesan commit

```
fix(upload)!: refuse files a web server could execute

Without permittedFileType every file was accepted, shell.php included.

Refs: spec-010
BREAKING CHANGE: .php*, .phtml, .phar, .htaccess are always refused.
```

- **type:** `feat`, `fix`, `docs`, `test`, `refactor`, `perf`, `build`, `ci`,
  `chore`, `revert`. **scope** opsional: nama komponen.
- Ringkasan bahasa Inggris, kalimat perintah, maksimal 72 karakter
  (sebaiknya ≤ 50), tanpa titik. Isi menjelaskan *kenapa*.
- Ringkasan menyebut apa yang berubah, bukan nomor spec; satu hal saja;
  tanpa kata kosong (`update`, `misc`, `wip`). Contoh: bukan
  `docs(spec): plan specs 012-018`, tetapi
  `docs(spec): plan next steps toward 1.0.0`. Rilis:
  `chore(release): X.Y.Z`.
- Breaking change: `!` dan footer `BREAKING CHANGE:`.
- Nomor spec di footer `Refs: spec-NNN`; bantuan AI di `Co-Authored-By:`.
- Jangan menulis rahasia, email pribadi, atau detail celah keamanan.

## Wajib sebelum PR

```bash
composer test       # semua test hijau
composer analyse    # PHPStan level 5 tanpa temuan
```

Rilis hanya dibuat setelah checklist uji di bagian "Rilis" `CLAUDE.md`
terpenuhi (CI, uji dari paket, audit, dokumen).

- Bug atau fitur baru disertai test. Test ditulis dulu dan harus gagal
  terhadap kode lama.
- Breaking change dicatat di `MODERNISASI.md`.
- `declare(strict_types=1)` di file baru (pengecualian ada di `CLAUDE.md`).
- Komentar kode dalam bahasa Inggris dan menjelaskan *kenapa*. Dokumentasi
  pengguna dalam bahasa Indonesia.

## Lisensi dan atribusi

- Kontribusi Anda masuk di bawah lisensi BSD-3-Clause proyek ini
  ([LICENSE](LICENSE)).
- Jangan mengubah atau menghapus notice copyright dan `@author` dari Panada
  Framework.
- Kode atau aset dari luar hanya yang lisensinya kompatibel (MIT, BSD, ISC,
  Apache-2.0), dengan notice dan sumbernya. Kode GPL/AGPL, jawaban Stack
  Overflow, atau tanpa lisensi: tulis ulang sendiri.
- Jangan memakai nama atau logo Panada untuk mempromosikan Palakar.

## Bantuan AI

Boleh, asal diungkap (trailer `Co-Authored-By` di commit), direview, dan
diuji seperti kode lain. Jangan menyertakan salinan kode berlisensi lain.

## Celah keamanan

Jangan lewat issue atau PR publik. Ikuti [SECURITY.md](SECURITY.md).

## Etika

Sopan, faktual, dan menghargai penulis Panada serta kontributor lain.
