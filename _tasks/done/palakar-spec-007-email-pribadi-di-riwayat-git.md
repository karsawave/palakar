# palakar-spec-007: Ganti email pribadi di riwayat git sebelum push pertama

- **Status:** done (ACC SPEC 007, 2026-10-25)
- **Dibuat:** 2026-10-25

## Masalah

Semua commit buatan fork (30 commit, author `ro` dan `Roma`) memakai email
pribadi sebagai author dan committer. Setelah di-push, email itu terlihat
publik selamanya (`git log`, `.patch` di GitHub, mirror). Repo belum pernah
di-push, jadi riwayat masih aman ditulis ulang.

## Keputusan

- Nama: **Roma**
- Email: **606799+rombyar@users.noreply.github.com** (noreply GitHub akun
  `rombyar`, dibaca dari `gh api user`)

## Perubahan

1. Cadangan dulu: `git bundle create ../panadax-before-spec-007.bundle --all`
   (di luar repo, tidak ter-push).
2. Tulis ulang author **dan** committer setiap commit yang emailnya pribadi
   menjadi `Roma <606799+rombyar@users.noreply.github.com>`, dengan
   `git filter-branch --env-filter` (bawaan git; tanpa memasang
   `git filter-repo`). Commit lain tidak disentuh, termasuk commit baseline
   upstream dan trailer `Co-Authored-By`.
3. Hapus ref cadangan `refs/original/` dan jalankan `git gc` lokal supaya
   email lama tidak tersisa di objek yang akan di-push.
4. Atur identitas **lokal repo ini**: `git config user.name "Roma"` dan
   `git config user.email "606799+rombyar@users.noreply.github.com"`.
   Konfigurasi global tidak diubah.
5. Hash commit berubah. Rujukan hash di dokumen diperbarui ke hash baru:
   `_tasks/done/palakar-spec-000…`, `-001…` sampai `-005…` (tabel/hasil),
   dan CLAUDE.md bila ada.
6. Spec ini dipindah ke `done/` dalam satu commit baru (dengan identitas baru).

## Tidak diubah

Isi file kode, tanggal commit, pesan commit, urutan commit.

## Langkah manual oleh pemilik (GitHub → Settings → Emails)

- Centang **Keep my email addresses private**.
- Centang **Block command line pushes that expose my email**.

## Verifikasi

- `git log --all --format='%an <%ae> %cn <%ce>' | sort -u` tidak memuat email
  pribadi.
- `git log --all -p | grep -c <email pribadi>` = 0.
- Jumlah commit sama, `git diff <bundle-HEAD> HEAD` hanya berisi perubahan
  rujukan hash di dokumen.
- `composer test` dan `composer analyse` hijau.

## Hasil

- Cadangan: `../panadax-before-spec-007.bundle` (diverifikasi `git bundle
  verify`). Masih berisi email lama; hapus setelah yakin, jangan di-push.
- 31 commit ditulis ulang; author dan committer semuanya
  `Roma <606799+rombyar@users.noreply.github.com>`. Pesan commit identik
  dengan sebelumnya (dicek dengan `diff`).
- `git log --all -p | grep -c <email lama>` = 0 setelah `refs/original`
  dihapus, reflog di-expire, dan `git gc --prune=now`.
- Identitas lokal repo diatur; konfigurasi global tidak diubah.
- Rujukan hash diperbarui di spec-000 (satu-satunya dokumen yang memuat
  hash). Spec lain merujuk lewat `git log --grep "spec-NNN"`.
- `composer test`: 135 test, 18 di-skip; `composer analyse`: tanpa temuan.
- Langkah manual GitHub dikonfirmasi pemilik (2026-10-25): Keep my email
  addresses private = On, Block command line pushes that expose my email = On.
