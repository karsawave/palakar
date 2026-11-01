# palakar-spec-041: Copy halaman Pages yang ringkas (KISS)

- **Status:** done (2026-11-02, commit yang memindahkan file ini ke `done/`)
- **Bergantung pada:** spec-040

## Masalah

Masukan pengguna: pengenalan masih berlebihan dan overkill; copy harus
menerapkan KISS. Yang berlebihan di halaman sekarang:

- Hero: judul + subjudul tiga kalimat (angka baris, binding, CSRF,
  shared hosting) sekaligus.
- Dua bagian kartu (3 + 6 = 9 kartu) yang saling mengulang: "tanpa
  container DI" dan "shared hosting" muncul dua kali.
- Kalimat kartu panjang (dua klausa, daftar nama database).
- Footer menambah asal-usul nama yang tidak dibutuhkan pengunjung.

## Keputusan

Satu pesan per bagian, satu kalimat pendek per item, tanpa pengulangan.

**Hero**
- Judul: "Framework PHP yang kecil dan mudah dibaca."
- Subjudul: "Core 45 file, tanpa dependency wajib."
- Tombol, perintah instal, baris ZIP, meta: tetap.

**Satu bagian "Fitur"** (menggantikan "Cocok untuk" dan "Fitur inti"),
6 kartu, judul + satu kalimat:

| Judul | Kalimat |
|---|---|
| Routing di satu file | `Gear.php`, 326 baris. |
| Query selalu di-bind | Nilai dikirim lewat placeholder. |
| CSRF bawaan | Token diperiksa sebelum controller jalan. |
| Tanpa container DI | Controller adalah kelas PHP biasa. |
| Shared hosting | Server tidak perlu Composer. |
| Dari Panada 1.x | Catatan migrasi di `MODERNISASI.md`. |

**Contoh controller**: tetap.

**Footer**: hanya atribusi Panada, lisensi, tautan repo (baris asal
nama dihapus).

`<meta description>`: "Framework PHP 8.2+ yang kecil dan mudah dibaca.
Core 45 file, tanpa dependency wajib. Fork dari Panada Framework 1.1.1."

Cek fakta: 45 file dan `Gear.php` 326 baris (dicek ulang); "tanpa
dependency wajib": `composer.json` `require` hanya `php` dan ekstensi
bawaan (`ext-pdo`, `ext-mbstring`, `ext-fileinfo`).

Dihapus dari halaman (tetap ada di README): jumlah baris, jumlah test,
daftar database CI, `e()`, JSON otomatis, `update()/delete()` tanpa
WHERE. Checklist rilis (CLAUDE.md poin 5) tetap menyebut "angka di
`docs/index.html`"; angka yang tersisa hanya 45 file dan 326 baris.

## Perubahan per file

- `docs/index.html`: teks dan satu section dihapus; CSS tidak berubah.
- `CHANGELOG.md`: entri `[Unreleased]` jadi "GitHub Pages copy
  shortened to a headline, one line and six features."

## Yang tidak diubah

Layout, CSS, favicon, contoh kode, README, kode.

## Pertanyaan terbuka

Tidak ada.

## Verifikasi

- Hitung: hero ≤ 2 kalimat, 6 kartu, tiap kartu 1 kalimat.
- Screenshot 1280px dan 360px tanpa teks terpotong; `composer test/
  analyse/cs` hijau.

## Hasil

- Copy diterapkan sesuai spec: hero 2 kalimat, satu bagian "Fitur" 6
  kartu x 1 kalimat, footer hanya atribusi. Angka dicek: 45 file,
  `Gear.php` 326 baris.
- Screenshot 1280px (terang) dan 360px via iframe (gelap): tanpa teks
  terpotong.
- `composer test` 152 test (32 dilewati, server lokal tidak ada),
  `analyse` bersih, `cs` 0 file.
