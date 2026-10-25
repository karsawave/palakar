# palakar-spec-014: PHP 8.4 dan 8.5 di matrix CI

- **Status:** done (ACC SPEC 014, 2026-10-25)
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

## Jawaban dan penyimpangan

Spec menulis 8.5 sebagai `continue-on-error` (eksperimental). Saat
implementasi ternyata PHP 8.5 sudah stabil sejak November 2025, jadi
ditanyakan ulang. Jawaban (2026-10-25): **8.2–8.5 semua wajib hijau**,
tanpa `continue-on-error`.

Pencarian implicit nullable (`Type $x = null` tanpa `?`) di `panada/`,
`app/`, `tests/`: tidak ada temuan, jadi tidak ada perubahan kode.

## Verifikasi

CI hijau di 8.2, 8.3, 8.4, 8.5. PHP 8.4/8.5 tidak tersedia di lokal
(hanya 8.1–8.3), jadi verifikasi hanya lewat CI; hasilnya dicatat di
bawah setelah run selesai.
