# palakar-spec-006: Rilis 0.1.0 dan publikasi ke Packagist

- **Status:** plan (menunggu `ACC SPEC 006`)
- **Dibuat:** 2026-10-25
- **Bergantung pada:** repo sudah ter-push ke `github.com/karsawave/palakar`

## Masalah

Palakar akan dipublikasikan di Packagist sebagai `karsawave/palakar`.
Packagist membaca versi dari **tag git**; field `version` di `composer.json`
(dari spec-001) bertentangan dengan itu dan diperingatkan `composer validate`.
Tanggal rilis 0.1.0 di CHANGELOG juga belum diisi.

## Perubahan

| File / langkah | Perubahan |
|---|---|
| `composer.json` | Hapus field `version` (versi dari tag) |
| `composer.lock` | `composer update --lock` (hash saja) |
| `CHANGELOG.md` | `## [0.1.0] - not released yet` → `## [0.1.0] - <tanggal tag>`; tautan perbandingan versi di bawah file |
| `home.php` | Tetap "version 0.1.0" (teks, bukan dibaca dari composer) |
| Git | Tag beranotasi `v0.1.0` pada commit rilis, lalu push tag (perlu perintah push dari pengguna) |
| Verifikasi | `composer validate` tanpa peringatan, `composer test`, `composer analyse` |

## Langkah manual oleh pemilik (tidak bisa dari repo)

1. Login ke https://packagist.org dengan akun GitHub organisasi/pemilik.
2. Submit `https://github.com/karsawave/palakar`.
3. Aktifkan auto-update: Packagist memasang webhook GitHub otomatis bila
   login lewat GitHub; kalau tidak, ikuti petunjuk di halaman paket.
4. Cek bahwa `karsawave/palakar` versi `v0.1.0` muncul.

## Tidak diubah

Kode, namespace, `"type": "project"` (dipasang dengan
`composer create-project karsawave/palakar`).

## Pertanyaan terbuka

1. Tanggal rilis: hari tag dibuat (default), atau tanggal lain?

## Verifikasi

`composer validate` bersih; `git tag -l` menampilkan `v0.1.0`;
setelah submit, `composer create-project karsawave/palakar demo` berhasil.
