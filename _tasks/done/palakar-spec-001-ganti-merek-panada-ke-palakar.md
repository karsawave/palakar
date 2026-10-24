# palakar-spec-001: Ganti merek Panada ke Palakar Framework

- **Status:** done (ACC SPEC 001, 2026-10-24)
- **Dibuat:** 2026-10-24
- **Backlog:** CLAUDE.md nomor 1

## Masalah

Fork ini masih memakai nama, logo, dan link Panada di paket, README, halaman
awal, dan teks yang terlihat pengguna. Syarat ke-3 lisensi BSD-3 Panada
melarang memakai nama Panada untuk mempromosikan produk turunan tanpa izin
tertulis. Paket `panada/panada` di Packagist juga milik upstream, dan "Panada
2.0" resmi sudah ada di `github.com/panada/app`.

## Keputusan

- Nama: **Palakar Framework**. Asal nama: *Palaka* (Sanskerta: penjaga,
  pelindung) + *Karsa* (kehendak; juga dari KarsaWave).
- Paket Composer: `karsawave/palakar`, versi awal `0.1.0` (SemVer, tidak
  melanjutkan versi Panada).
- Atribusi tetap: "Fork dari Panada Framework 1.1.1 karya Iskandar Soesman".

## Perubahan

| File | Perubahan |
|---|---|
| `composer.json` | `name`, `description`, `version` 0.1.0, `homepage`, `support`; author baru ditambahkan, Iskandar Soesman tetap sebagai penulis asli |
| `README.md` | Judul, asal nama, atribusi, link ke repo fork |
| `LICENSE` | Tambah baris copyright pemilik fork **di bawah** baris asli; teks lisensi tidak diubah |
| `app/views/home.php` | Logo Panada diganti teks "Palakar"; "Powered by Palakar 0.1.0"; link ke repo fork; catatan atribusi |
| `app/views/header.php`, `public/check.php` | Judul halaman |
| `panada/Resources/Email.php` | Header `X-Mailer: Palakar Mailer` |
| `panada/Resources/Rest.php` | User-Agent `Palakar Framework REST API/0.1` |
| `panada/Resources/ErrorHandler.php` | Judul halaman error Whoops |
| `public/assets/img/logo.png`, `forkgithub.png` | Dihapus dari halaman (logo/banner Panada) |
| Test | Teks yang berubah (`SkeletonAppTest`, `RestTest`) |
| `CLAUDE.md`, `MODERNISASI.md` | Nama baru, backlog nomor 1 ditandai selesai |

## Tidak diubah

- Notice `@author`/`@link` di file asal Panada, teks `LICENSE` asli.
- Namespace dan nama kelas (`Resources\`, `Drivers\`, `Gear`), supaya aplikasi
  lama tetap jalan.
- Riwayat git upstream.

## Jawaban pertanyaan terbuka

1. Pemegang copyright: **KarsaWave** → `Copyright (C) 2026, KarsaWave.` di
   bawah baris asli `LICENSE`.
2. Email author: **tidak dicantumkan**. Author di `composer.json` hanya
   `{"name": "KarsaWave", "homepage": "https://github.com/karsawave"}`;
   Iskandar Soesman tetap tercantum sebagai penulis asli (emailnya dari
   `composer.json` asli dipertahankan karena itu notice upstream).
3. Repo: **https://github.com/karsawave/palakar** → `homepage`,
   `support.source`, `support.issues` (`/issues`), link di README dan halaman
   awal.

## Verifikasi

- `composer test` dan `composer analyse` hijau.
- `grep -ri panada` di luar notice/atribusi, `panada/` (nama folder core), dan
  dokumen sejarah hanya menyisakan atribusi yang disengaja.
- Halaman awal di `composer serve` menampilkan Palakar dan atribusinya.

## Hasil

- `composer test`: 135 test, 18 di-skip (sama seperti sebelumnya);
  `composer analyse`: tanpa temuan.
- Sisa kata "Panada" di aplikasi dan README hanya atribusi yang disengaja,
  path folder `panada/` (sengaja tidak diganti), dan nama kelas `PanadaPDO`.
- Penyimpangan kecil dari tabel perubahan: contoh nama database di
  `app/config/database.php` dan dua komentar (`main.php`, `public/index.php`)
  ikut diganti; tercatat sebagai breaking change nomor 34 di MODERNISASI.md.
- Catatan: `composer validate` menyarankan tanpa field `version` bila paket
  dipublikasikan di Packagist (versi dibaca dari tag git). Dipertahankan
  sesuai spec; bisa dihapus saat tag `v0.1.0` dibuat (lihat spec-005).
- Commit: lihat `git log --grep "spec-001"`.
