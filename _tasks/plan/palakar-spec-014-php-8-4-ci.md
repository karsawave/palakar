# palakar-spec-014: PHP 8.4 di matrix CI

- **Status:** plan
- **Bergantung pada:** -

## Masalah

CI hanya menguji PHP 8.2 dan 8.3. PHP 8.4 menambah deprecation, mis.
implicit nullable (`Type $x = null`), dan karena PHPUnit memakai
`failOnDeprecation`, deprecation itu akan langsung membuat test gagal.

## Perubahan per file

- `.github/workflows/ci.yml`: `php: ['8.2', '8.3', '8.4']`.
- File yang memicu deprecation: perbaiki seminimal mungkin (mis.
  `?Type $x = null`). Daftar file ditulis di spec setelah dijalankan.
- `CLAUDE.md`: status dan backlog 5.

## Yang tidak diubah

Minimum PHP tetap 8.2.

## Pertanyaan terbuka

1. Sekalian 8.5 sebagai `continue-on-error` (eksperimental)? Usulan: ya,
   satu baris, supaya masalah ketahuan lebih awal.

## Verifikasi

CI hijau di 8.2, 8.3, 8.4.
