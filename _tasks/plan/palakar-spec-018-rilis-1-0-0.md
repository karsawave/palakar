# palakar-spec-018: Rilis 1.0.0

- **Status:** plan, menunggu spec lain
- **Bergantung pada:** spec-004, spec-011 s.d. spec-017

## Masalah

Versi 0.x tidak menjanjikan API stabil. Pengguna produksi butuh janji
SemVer.

## Keputusan

1.0.0 dirilis hanya setelah semua dependensi di atas `done`, CI hijau,
dan tidak ada breaking change yang masih direncanakan.

## Perubahan per file

- `CHANGELOG.md`: `[Unreleased]` → `[1.0.0] - <tanggal>`.
- `README.md`, `CLAUDE.md`: versi dan status.
- Merge `development` → `master` (merge, bukan rebase), tag `v1.0.0`.
- `SECURITY.md`: versi yang didukung.

## Pertanyaan terbuka

1. Rilis antara (0.2.0) untuk spec 011–015 dulu sebelum 1.0.0? Usulan:
   ya, supaya breaking change dari spec-011 tidak langsung masuk 1.0.0
   tanpa sempat dicoba.
2. Kebijakan dukungan: berapa lama 0.x/1.x menerima perbaikan keamanan?

## Verifikasi

Tag terbaca di Packagist; `composer create-project karsawave/palakar`
berjalan di folder kosong.
