# palakar-spec-021: Ringkasan commit harus jelas, padat, singkat

- **Status:** done (ACC SPEC 021, 2026-10-25)
- **Dibuat:** 2026-10-25
- **Bergantung pada:** spec-009

## Masalah

Beberapa ringkasan commit sudah mengikuti format Conventional Commits,
tetapi isinya tidak menjelaskan perubahan. Contoh dari `gh run list`:

- `docs(spec): plan specs 012-018`: hanya nomor, isi tidak terbaca.
- `chore(release): merge development for 0.1.0`: kata "merge" sudah
  terlihat dari Git; yang penting adalah rilisnya.
- `docs(spec): record GitHub email privacy settings`: cukup jelas, tetapi
  bisa lebih singkat.

## Perubahan per file

`CLAUDE.md`, bagian "Pesan commit", poin **Ringkasan** diperluas:

- Menyebut **apa yang berubah** dengan kata yang dipahami tanpa membuka
  spec. Nomor spec cukup di footer `Refs:`, bukan di ringkasan.
- Target ≤ 50 karakter, maksimal 72. Satu hal saja; kalau perlu "and",
  pecah jadi beberapa commit.
- Tanpa kata kosong: `update`, `misc`, `changes`, `stuff`, `various`,
  `wip`, `fix things`.
- Tanpa kata yang sudah tersirat dari type atau Git (`merge ... for`,
  `add commit for`).
- Contoh baik/buruk:

  | Buruk | Baik |
  |-------|------|
  | `docs(spec): plan specs 012-018` | `docs(spec): plan next steps toward 1.0.0` |
  | `chore(release): merge development for 0.1.0` | `chore(release): 0.1.0` |
  | `fix: update upload` | `fix(upload): detect mime from file content` |

`CONTRIBUTING.md`: aturan dan tabel contoh yang sama, dalam bentuk
ringkas.

## Yang tidak diubah

Commit yang sudah di-push tidak ditulis ulang (aturan riwayat dan
larangan force push tetap berlaku).

## Jawaban pertanyaan terbuka

ACC tanpa jawaban terpisah = usulan diterima:

1. 50 karakter: anjuran; 72 tetap batas keras.
2. Merge rilis ke `master`: `chore(release): X.Y.Z`.

## Catatan implementasi

Contoh "baik" untuk commit spec 012-018 diganti dari
`plan CI, PHPStan, guides and 1.0.0` menjadi
`plan next steps toward 1.0.0`, karena yang pertama melanggar aturan
"satu hal, tanpa and" di spec ini sendiri.

## Verifikasi

Commit spec ini sendiri memakai aturan baru; `git log --oneline -1`
diperiksa.

Hasil: `docs: require clear, specific commit summaries` (44 karakter).
