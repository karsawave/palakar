# palakar-spec-008: Aturan branch master/development

- **Status:** plan (menunggu `ACC SPEC 008`)
- **Dibuat:** 2026-10-25

## Masalah

Repo sudah di-push (`master` dan `development` di
github.com/karsawave/palakar), tetapi aturan pemakaian branch belum tertulis.
Selain itu, link LICENSE di halaman awal menunjuk ke `blob/main/LICENSE`,
padahal branch di GitHub bernama `master` (link 404).

## Keputusan

- `master`: rilis stabil saja, setiap rilis diberi tag `vX.Y.Z` (dibaca
  Packagist). Default branch di GitHub.
- `development`: tempat kerja. Spec yang di-ACC dikerjakan dan di-commit di
  sini, lalu digabung ke `master` saat rilis (merge, bukan rebase, supaya
  riwayat yang sudah publik tidak berubah).
- Branch lokal `main` melacak `origin/master` dan diganti nama menjadi
  `master` supaya nama lokal dan remote sama.
- Tidak ada force push ke `master` atau `development` lagi setelah ini.

## Perubahan

| File / langkah | Perubahan |
|---|---|
| `git branch -m main master` | Nama branch lokal sama dengan remote (tetap melacak `origin/master`) |
| `CLAUDE.md` | Bagian alur kerja: aturan branch di atas; commit selalu di `development` |
| `CONTRIBUTING.md` | PR ditujukan ke `development`; `master` hanya untuk rilis |
| `app/views/home.php` | Link LICENSE `blob/main` → `blob/master` |
| `.github/workflows/ci.yml` | Tidak berubah (sudah jalan di semua push dan PR) |

## Langkah manual oleh pemilik (opsional, GitHub → Settings → Branches)

Branch protection untuk `master`: wajib lewat PR dan CI hijau, larang force
push. Untuk `development`: larang force push.

## Pertanyaan terbuka

Tidak ada.

## Verifikasi

`composer test` hijau; `git branch -vv` menampilkan `master` →
`origin/master` dan `development` → `origin/development`; link LICENSE di
halaman awal tidak 404.
