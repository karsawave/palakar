# palakar-spec-027: `composer serve` melayani file statis

**Status:** plan

## Masalah

Ditemukan saat verifikasi spec-025. `composer serve` menjalankan
`php -S ... public/index.php`, sehingga **setiap** request, termasuk
`/assets/css/main.css`, masuk ke Gear. Hasilnya, CSS dijawab dengan halaman
awal (`200`, `text/html`). Dengan header `nosniff`, browser menolak CSS itu,
jadi tampilan di server dev selalu tanpa gaya. Bug ini sudah ada sebelum
spec-025 (dicek dengan `git stash`). Apache/nginx tidak terkena karena
melayani file statis langsung.

## Keputusan

Di `public/index.php`, sebelum Gear: bila berjalan di `cli-server` dan path
menunjuk file yang ada di `public/`, kembalikan `false` supaya `php -S`
melayani file itu sendiri (cara yang didokumentasikan PHP untuk router script).

```php
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}
```

Hanya aktif di server dev. `is_file` dengan path di bawah `public/`; `php -S`
sendiri menolak path di luar document root.

## Perubahan per file

- `public/index.php`: tiga baris di atas.
- `tests/Http/SkeletonAppTest.php`: `/assets/css/main.css` → 200, `text/css`.
- `CHANGELOG.md` `[Unreleased]` → Fixed.

## Yang tidak diubah

- Gear, routing, konfigurasi web server produksi.

## Pertanyaan terbuka

1. `tests/Fixtures/public/index.php` ikut diubah? Usul: tidak, fixture tidak memakai aset.

## Verifikasi

- Test baru gagal sebelum perubahan, lulus sesudahnya.
- `composer test`, `composer analyse`, `composer cs` hijau.
- `composer serve`: halaman awal tampil dengan CSS.

## Breaking change

Tidak ada.
