# palakar-spec-010: Bagian Credits di README

- **Status:** plan (menunggu `ACC SPEC 010`)
- **Dibuat:** 2026-10-25

## Masalah

Repo GitHub dibuat ulang tanpa status fork, jadi label "forked from" yang
menunjuk asal-usul hilang. Atribusi harus tetap jelas dan sopan di repo itu
sendiri (etika open source).

## Perubahan

`README.md`, bagian baru **Credits** sebelum "Links":

- Terima kasih kepada **Iskandar Soesman** dan para kontributor Panada
  Framework, dengan tautan ke `https://github.com/panada/Panada`.
- Palakar dimulai dari Panada Framework 1.1.1 (upstream commit `dc904d0`);
  commit pertama di riwayat git ini adalah kode asli tersebut tanpa
  perubahan.
- Riwayat Panada: `CHANGELOG-PANADA`; lisensi asli tetap di `LICENSE`.
- Bantuan AI dalam modernisasi diungkap (trailer `Co-Authored-By` di commit).

## Tidak diubah

LICENSE, atribusi lain, kode.

## Pertanyaan terbuka

Tidak ada.

## Verifikasi

`composer test` hijau; tautan benar.
