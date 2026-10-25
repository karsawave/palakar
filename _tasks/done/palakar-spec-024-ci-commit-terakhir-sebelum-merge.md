# palakar-spec-024: CI yang dicek adalah commit terakhir sebelum merge

- **Status:** done (ACC SPEC 024, 2026-10-25)
- **Dibuat:** 2026-10-25
- **Bergantung pada:** spec-022

## Masalah

Checklist rilis (spec-022) poin 1 berbunyi "CI hijau di commit yang
dirilis". Saat rilis 0.2.0, yang dicek adalah run `f96cd8c` (commit
`chore(release)`), lalu masih ada commit catatan spec `af56840` sebelum
merge. Run `af56840` gagal (timeout unduh dependency) dan tidak ketahuan
sampai setelah rilis (lihat spec-023, "Catatan setelah rilis").

## Perubahan per file

`CLAUDE.md`, bagian "Rilis", poin 1 diganti:

> 1. **CI hijau di HEAD `development` tepat sebelum merge** (commit
>    terakhir, termasuk commit catatan spec): semua versi PHP di matrix
>    dan semua service, tanpa test yang dilewati. Cek dengan
>    `gh run list --branch development --limit 1` dan pastikan
>    `headSha` sama dengan `git rev-parse HEAD`. Run yang gagal karena
>    infrastruktur (timeout unduh, runner) dijalankan ulang
>    (`gh run rerun --failed`) dan baru dianggap lolos setelah hijau;
>    penyebabnya dicatat di spec rilis.

Poin 7 ditambah: setelah hasil dicatat (commit spec), **tunggu CI commit
itu hijau** sebelum merge.

## Yang tidak diubah

Poin checklist lain, alur branch.

## Pertanyaan terbuka

Tidak ada.

## Verifikasi

Dipakai di rilis berikutnya; spec rilis mencantumkan `headSha` run yang
dicek dan sama dengan commit yang di-merge.
