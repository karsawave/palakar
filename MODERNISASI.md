# Modernisasi Panada untuk PHP 8.2+

Catatan perubahan dari Panada 1.1.1 (2015) ke versi modern ini. Filosofi tetap:
**KISS, transparan, bisa dibaca habis dalam semalam.** Inti yang direfaktor
(bootstrap, routing, database, validasi, CSRF, session, error) totalnya sekitar
3.500 baris termasuk komentar, dan folder `panada/` justru lebih kecil dari
versi lama (9.334 → 6.480 baris).

Status: 135 test PHPUnit (unit + HTTP sungguhan) dan PHPStan level 2 tanpa
temuan di seluruh `panada/`. CI (GitHub Actions, PHP 8.2 dan 8.3) juga
menjalankan test PostgreSQL 16, Redis, Memcached, dan APCu.

Riwayat git dibuat satu commit per langkah, jadi setiap perubahan bisa dibaca
terpisah dengan `git log -p`.

---

## Mulai cepat

```bash
composer install              # opsional, untuk Whoops + PHPUnit + paket lain
composer serve                # = php -S localhost:8000 -t public public/index.php
```

Tanpa Composer juga jalan: autoloader bawaan Panada memuat semuanya sendiri.

- Buka `http://localhost:8000/` untuk halaman awal.
- Buka `/check.php` untuk cek kebutuhan server (hanya aktif saat `debug` true).
- Buka `/guestbook` untuk demo form: CSRF + validasi + PDO + escape output.

Sebelum produksi:

1. `app/config/main.php` → `'debug' => false`.
2. `app/config/security.php` → isi `secretKey` dengan
   `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`.
3. Hapus `public/check.php`.
4. `composer install --no-dev` (Whoops tidak ikut terpasang).

---

## 1. Kompatibilitas PHP 8.2 / 8.3

Panada 1.1.1 langsung *fatal* di PHP 8.3 pada request pertama: *dynamic
property* `Controller::$childClass` memicu deprecation, error handler lama
mengubahnya jadi exception, lalu halaman error-nya sendiri ikut gagal.

Yang diperbaiki:

- `declare(strict_types=1)`, typed properties, constructor property promotion,
  `match`, nullsafe operator di seluruh komponen inti.
- Semua *dynamic property* dihapus (Controller, Uri, PropertiesLoader).
  `ActiveRecord` sengaja diberi `#[\AllowDynamicProperties]` karena kolom
  tabel memang menjadi properti objek.
- `create_function()` (dihapus di PHP 8) → closure di `Request`.
- `FILTER_SANITIZE_STRING`, `FILTER_FLAG_SCHEME_REQUIRED`, `apc_*` (jadi APCu),
  `Redis::setnx()` dengan 3 argumen, dan beberapa variabel tak terdefinisi.

Catatan desain: `Gear.php`, `Resources\Database`, `Resources\Session`,
`Resources\Cache`, dan `PropertiesLoader` sengaja **tidak** `strict_types`.
Mereka hanya meneruskan panggilan, dan segmen URL selalu string. Jadi `show(int $id)` tetap menerima
`/post/show/15` sebagai integer 15, sedangkan `/post/show/abc` dijawab 404.

## 2. Composer PSR-4 + autoloader bawaan sebagai fallback

Urutan pemuatan kelas (`Gear::registerAutoloader()`):

1. **Composer** (`vendor/autoload.php`), kalau sudah `composer install`.
   Memetakan `Resources\` dan `Drivers\` (PSR-4), plus semua paket vendor.
2. **Autoloader Panada**, selalu terdaftar. Memuat kelas aplikasi
   (`Controllers\`, `Models\`, `Libraries\`, `Modules\`) dari folder yang
   ditunjuk konstanta `APP`, plus namespace kustom di `main.php` → `namespace`.

Kenapa kelas aplikasi tidak dipetakan di `composer.json`? Karena `APP` bisa
diarahkan ke folder lain (satu core untuk beberapa aplikasi). Pemetaan statis di
Composer akan diam-diam memuat controller dari folder yang salah.

Autoloader Panada kini mengikuti aturan PSR-4: tidak pernah melempar exception
saat file tidak ada, sehingga `class_exists()` bisa menjawab `false`.

Pakai paket Composer apa saja langsung di controller:

```bash
composer require monolog/monolog
```

## 3. Database: PDO + prepared statement otomatis

Driver lama (`mysql`, `mysqli`, `pgsql`, `sqlite`, `cubrid`, `mongodb`) dihapus.
Sisa satu: `Drivers\Database\PanadaPDO` untuk MySQL/MariaDB, PostgreSQL, SQLite.

Temuan di kode lama: `PanadaPDO::escape()` hanya `return $string;`, sehingga
`insert()`, `update()`, dan `where()` menempelkan nilai mentah ke SQL.

Aturan baru:

- **Setiap nilai** dikirim sebagai parameter terikat (`?`), tidak pernah
  disambung ke string SQL.
- **Nama tabel/kolom** tidak bisa diikat, jadi dicek dengan pola ketat
  (`users`, `u.name`, `COUNT(id)`). Selain itu ditolak. Aman untuk parameter
  `?sort=` dari URL.
- `update()` / `delete()` tanpa WHERE ditolak (cegah hapus seluruh tabel).
- Error selalu dilempar sebagai `RunException` berisi SQL (tanpa nilai).

API lama tetap sama:

```php
$db = new \Resources\Database();            // atau $this->resource->database()

$db->insert('users', ['name' => $name, 'email' => $email]);
$user  = $db->getOne('users', ['email' => $email]);
$users = $db->select('id', 'name')->from('users')
            ->where('status', 'IN', ['active', 'trial'])
            ->where('deleted_at', '=', null)          // IS NULL
            ->orderBy($_GET['sort'] ?? 'id', 'DESC')  // aman
            ->limit(20, 0)
            ->getAll();

$db->where('id', '=', $id)->update('users', ['name' => $name]);
$db->query('UPDATE posts SET views = views + 1 WHERE id = ?', [$id]);

$db->transaction(function ($db) {
    $db->insert('orders', [...]);
    $db->insert('order_items', [...]);
});   // exception di dalamnya = rollback
```

Baru: `whereRaw($sql, $bindings)`, `transaction()`, `affectedRows()`,
`getLastBindings()`, binding bernama (`:email`), koneksi lazy, DSN mentah lewat
`'dsn'`, dan `'options'` PDO di config.

Konfigurasi default sekarang SQLite di `app/storage/database.sqlite` (tanpa
setup). Contoh MySQL dan PostgreSQL ada di `app/config/database.php`.

## 4. Validation: sanitize otomatis + CSRF bawaan

Class-nya tetap `Resources\Validation` (di Panada tidak ada `Tools\Validation`),
cara pakainya tidak berubah: extend, isi `setRules()`, panggil `validate()`.

Perbaikan bug:

- Field `required` yang **dihapus dari form** dulu lolos, karena yang di-loop
  adalah input, bukan aturan. Sekarang yang di-loop adalah aturan.
- `"0"` dulu dianggap kosong oleh `required`.
- `min`/`max` memakai `mb_strlen` (huruf non-ASCII dihitung benar).
- `url` hanya menerima `http`/`https` (`javascript:` ditolak).

Sanitize otomatis: semua string di-*trim* dan dibersihkan dari karakter kontrol
tak terlihat (termasuk NUL byte) sebelum aturan dijalankan. Per field ada
`'filter'` dengan nama siap pakai (`trim`, `lower`, `upper`, `int`, `float`,
`digits`, `strip_tags`, `email`, `escape`) atau callable apa saja.

```php
'email' => ['rules' => ['required', 'email'], 'filter' => ['lower']],
'age'   => ['rules' => ['integer'], 'filter' => ['int']],
```

`value()` hanya mengembalikan field yang punya aturan, jadi field tambahan
seperti `is_admin=1` dari pengunjung tidak ikut ke `insert()`.

Sanitize bukan pengganti escape. Di view selalu pakai `e()`:

```php
<p><?= e($user->name) ?></p>
```

**CSRF** (`Resources\Csrf`) aktif otomatis. Gear memeriksa token pada setiap
request selain GET/HEAD/OPTIONS, sebelum controller dijalankan; gagal = 403.

```php
<form method="post">
    <?= csrf_field() ?>
    ...
</form>
```

AJAX: kirim header `X-CSRF-Token: <?= csrf_token() ?>`. Webhook atau API
berbasis token dikecualikan di `app/config/security.php` → `csrf.except`
(mis. `'api/*'`).

## 5. Error handler interaktif

`Resources\ErrorHandler` dipasang di fase bootstrap (`Gear::send()`).

| `debug` | Whoops terpasang? | Hasil |
| --- | --- | --- |
| `true` | ya (`composer require --dev filp/whoops`) | Halaman interaktif Whoops; JSON untuk request AJAX |
| `true` | tidak | Halaman bawaan Panada: pesan, potongan kode, stack trace (semua di-escape) |
| `false` | apa saja | Halaman umum "Something went wrong", detail hanya ke error log |

- Warning/notice diubah jadi exception agar tidak terlewat.
- Deprecation hanya dicatat ke log, supaya upgrade PHP tidak menjatuhkan situs
  (persis penyebab Panada lama mati di PHP 8.2).
- Fatal error (mis. memori habis) ditangkap di shutdown.
- Whoops dipakai hanya sebagai *renderer*, tidak didaftarkan global, jadi alur
  respons tetap lewat `Resources\Response`.

## 6. Session: cookie modern

```php
// app/config/session.php
'cookieSecure'   => 'auto',   // Secure hanya saat HTTPS
'cookieHttpOnly' => true,     // tidak bisa dibaca JavaScript
'cookieSameSite' => 'Lax',    // 'None' otomatis memaksa Secure
```

- `session.use_strict_mode` aktif: ID session yang dikarang penyerang ditolak
  (anti *session fixation*). Panggil `regenerateId()` setelah login.
- Di balik reverse proxy (Traefik, Nginx, Cloudflare Tunnel), set
  `'trustProxyHeaders' => true` di `main.php` agar `X-Forwarded-Proto: https`
  dipercaya. Default `false`, karena header itu bisa dipalsukan pengunjung.
- Driver `database` dan `cache` ditulis ulang sebagai `SessionHandlerInterface`.
  Struktur tabel ada di `panada/Drivers/Session/DatabaseHandler.php`.
- Driver `cookie` sekarang ditandatangani HMAC-SHA256 dan berformat JSON
  (tidak ada `unserialize`), opsional dienkripsi. Dulu hanya MD5 checksum.
- `Resources\Encryption` sekarang libsodium (authenticated encryption). Versi
  lama hanya menggeser karakter, bukan enkripsi.

## 7. Komponen lama: strict types, test, perbaikan bug

Komponen yang dulu hanya "lolos lint" kini punya test sendiri. Bug yang
ditemukan saat menulis test langsung diperbaiki.

### ActiveRecord

```php
namespace Models;

class Users extends \Resources\ActiveRecord
{
    protected $table = 'users';         // default: nama class huruf kecil
}

$user = new \Models\Users();
$user->name = 'Budi';
$user->save();                          // INSERT, lalu $user->id terisi
$user->name = 'Budi Santoso';
$user->save();                          // UPDATE ... WHERE id = ?
```

- `$table` yang ditulis di model kini dipakai. Dulu selalu ditimpa nama class.
- Setelah INSERT, primary key diisi ke objek, jadi `save()` kedua meng-update
  record yang sama (dulu menyisipkan baris baru lagi).
- `condition()`, `order()`, `limit()`, `select()`, `group()`, `from()` hanya
  berlaku untuk satu query berikutnya. Dulu ikut terbawa ke query sesudahnya.
- `delete()` / `update()` tanpa argumen pada record yang sudah dimuat
  (`get($id)`, `getByEmail()`) memakai primary key record itu.
- Baris dari model yang punya relasi selalu berupa objek model, jadi
  `$posts[0]->user` jalan (dulu hanya pada query pertama).
- Method selain `getBy...()`, `first()`, `last()` melempar exception (dulu diam-diam
  `null`, salah ketik tidak ketahuan).

### `setOption()` (Pagination, Upload, Image, Email)

Satu trait kecil, `Resources\Configurable`. Nama opsi yang salah ketik kini
melempar exception (dulu diam-diam membuat properti baru dan opsinya tidak
berefek). Nilai string dari URL, config, atau `getenv()` di-cast ke tipe
properti, jadi `setOption('current', $this->uri->path(2))` tetap jalan.

### Pagination

```php
$links = (new \Resources\Pagination())->setOption([
    'urlReference' => $this->location('posts%_%'),
    'format' => '/page/%#%',
    'total' => $total,
    'limit' => 10,
    'current' => $page,
])->getUrl();
```

- `getUrl()` dulu selalu error (`$this->base` tidak ada). Link "Previous" juga
  memuat `%_%` mentah.
- `href` di-escape. `prevText`, `nextText`, `groupSeparator` tetap HTML milik
  developer, jangan isi dengan input pengunjung.
- Kurang dari 2 halaman: `[]` (dulu `null`). `limit` 0 tidak lagi membagi nol.

### Rest

```php
$rest = new \Resources\Rest();
$rest->setRequestAuthorization($token, 'Bearer');
$body = $rest->sendRequest('https://api.example.com/items', 'PATCH', ['stock' => 3]);

if ($body === false) { /* gagal koneksi / timeout */ }
$status = $rest->getResponseStatus();
```

- PATCH (dan method lain selain GET/POST/PUT/DELETE) dulu terkirim sebagai POST.
- Header tidak lagi menumpuk: request kedua dengan objek yang sama dulu
  mengirim header ganda (termasuk `Content-Length` request sebelumnya).
- `sendRequest()` mengembalikan `''` untuk respons kosong (mis. 204), `false`
  hanya saat request gagal. Dulu respons kosong juga `false`.
- `getRequest()` membaca body PUT/PATCH/DELETE (form atau JSON). Dulu
  `$_REQUEST`, yang kosong untuk method itu dan bisa memuat cookie.
- `outputJSON()`/`outputXML()` tidak lagi butuh ekstensi curl, tidak lagi
  me-reset status ke 200, dan mengirim `charset=utf-8`.
- `setResponseHeader(404)` kini benar-benar dipakai (dulu ditimpa 200 oleh
  `Response` saat dikirim).

### Upload

```php
$upload = new \Resources\Upload();
$upload->setOption([
    'folderLocation' => APP.'storage/uploads',     // sebaiknya di luar public/
    'permittedFileType' => 'jpg|jpeg|png|pdf',
    'maximumSize' => 2_000_000,
    'autoRename' => true,
]);

if (!$upload->now($_FILES['photo'])) {
    return e($upload->getError('message'));
}
```

Perbaikan keamanan:

- File yang bisa dijalankan server selalu ditolak, apa pun isi
  `permittedFileType`: `.php`, `.php5`, `.phtml`, `.phar`, nama ganda seperti
  `foto.php.jpg`, serta `.htaccess` dan `.user.ini`. Dulu tanpa
  `permittedFileType` semua file diterima, termasuk `shell.php` ke folder publik.
- `setFileName` berisi `../` dulu menulis file di luar folder tujuan. Sekarang
  nama selalu di-`basename()`.
- `autoRename` memakai nama acak (`random_bytes`), dulu `time().rand()` yang
  bisa ditebak. Folder baru dibuat `0755` (dulu `0777`).

Perbaikan lain: ekstensi yang tidak ada di daftar MIME dulu membuat error 500
(sekarang `application/octet-stream`); `autoCreateFolder` (folder `tahun/bulan`)
dulu tidak pernah dijalankan; `getError('message')` sebelum ada error dulu
memicu warning.

### Image

```php
$image = (new \Resources\Image())->setOption([
    'folder' => APP.'storage/uploads',
    'editType' => PIMG_RESIZE_CROP,
    'resizeWidth' => 300,
    'cropWidth' => 200,
    'cropHeight' => 200,
    'newFileName' => 'avatar-small',
]);

if (!$image->edit('avatar.jpg')) {
    $errors = $image->getErrorMessage();
}
```

Bisa juga lewat Upload: `'editImage' => [['resizeWidth' => 800], [...]]`.

- Resize dengan rasio pecahan (100×75 → lebar 33) dulu memicu deprecation
  float→int; ukuran kini dibulatkan ke piksel utuh.
- Transparansi PNG dipertahankan (dulu latar jadi hitam).
- File yang bukan gambar memberi pesan error, bukan warning/500. `crop` tanpa
  `cropWidth`/`cropHeight` memberi pesan error, bukan `ValueError`.
- `getErrorMessage()` hanya berisi error dari `edit()` terakhir.
- `stripSpaces` hanya mengubah nama file baru, bukan path folder.
- File sumber tidak lagi di-`chmod 0666` (bisa ditulis semua user).

### Email

```php
$email = new \Resources\Email();
$email->setOption([
    'smtpHost' => 'smtp.example.com',
    'smtpPort' => 587,
    'smtpSecure' => 'tls',                  // STARTTLS; 'ssl' untuk port 465
    'smtpUsername' => 'app@example.com',
    'smtpPassword' => getenv('SMTP_PASSWORD'),
    'messageType' => 'html',
]);

$sent = $email->from('app@example.com', 'Toko Kopi')
    ->to($customer)
    ->subject('Pesanan diterima')
    ->message($this->output('emails/order', ['order' => $order]))
    ->attach(APP.'storage/invoice-15.pdf')
    ->mail();

if (!$sent) {
    error_log(implode("\n", $email->printDebug(false)));
}
```

API-nya sama, isinya ditulis ulang (894 → 521 baris). Perbaikan keamanan:

- *Header injection*: subject atau nama pengirim berisi baris baru dari form
  bisa menambah header, misalnya `Bcc:` ke ribuan alamat. Baris baru kini dibuang
  dan semua alamat divalidasi.
- `smtpSecure => 'tls'` dulu diabaikan: password terkirim tanpa enkripsi.
  Sekarang STARTTLS wajib berhasil, kalau tidak email tidak dikirim.
- Login SMTP gagal dulu diabaikan dan pengiriman diteruskan. Penerima yang
  ditolak server juga diabaikan. Keduanya kini membuat `mail()` mengembalikan
  `false`.
- Baris yang diawali titik kini digandakan (*dot-stuffing*). Dulu baris berisi
  `.` saja memotong email di tengah.
- `printDebug()` di-escape; username dan password tidak muncul di log debug.

Perbaikan lain: email HTML dan lampiran kini MIME yang benar (boundary
ditutup, lampiran juga terkirim lewat `mail()` bawaan), UTF-8 +
quoted-printable (huruf non-ASCII dan baris panjang aman), subject dan nama
non-ASCII di-*encode*, ada header `Date` dan `Message-ID`, hanya CRLF (server
modern menolak LF tunggal).

### Tools

- `xmlDecode()` dulu selalu fatal untuk string XML (`SimpleXMLElement` tanpa
  `\`). XML tidak valid kini `false`, bukan warning.
- `xmlEncode()` (juga dipakai `outputXML()`): nilai di-escape untuk XML (dulu
  `htmlentities`, jadi `Café` merusak XML), nama elemen tidak valid diganti `_`,
  objek bersarang didukung.
- `getRandomString()` memakai `random_int()` (dulu `mt_rand()` yang bisa
  ditebak, berbahaya bila dipakai untuk token atau password).
- `setStatusHeader()` kini mengatur status di `Response`; dulu langsung
  `header()` lalu ditimpa 200 saat Gear mengirim respons.

### Cache

```php
$cache = new \Resources\Cache();              // 'default' di app/config/cache.php
$cache->setValue('rates', $rates, 3600);
$rates = $cache->getValue('rates');           // false bila tidak ada / kedaluwarsa

$cache->setValue('item_15', $item, 0, 'products');
$cache->deleteValue('products');              // semua nilai namespace 'products' hilang
```

Semua driver (`dummy`, `apc`, `memcached`, `memcache`, `redis`) kini lolos test
kontrak yang sama (`tests/Unit/CacheTest.php`). Yang diperbaiki:

- `dummy`: `flushValues()` dulu fatal, nilai `0`/`''`/`false` dulu dianggap
  tidak ada, dan `setValue()` mengembalikan `null`, sehingga session driver
  `cache` dengan `dummy` gagal menulis.
- `redis`: kunci namespace dibaca dengan serializer berbeda di `deleteValue()`,
  jadi menghapus nilai bernamespace mengenai kunci yang salah. `incrementBy()`
  gagal untuk angka yang disimpan dengan `setValue()`. `delete()` lama diganti
  `del()`.
- `memcached`: opsi `persistent` dulu dikirim sebagai *weight* server.
- `apc`: `$cache->namaFungsiApaSaja()` dulu diteruskan ke fungsi global PHP
  dengan nama itu (termasuk `exec`). Dihapus.
- Namespace memakai versi acak, bukan `time()`: namespace yang dihapus lalu
  dipakai lagi di detik yang sama dulu menampilkan nilai lama.
- Ekstensi yang belum terpasang, koneksi atau driver yang salah tulis memberi
  `RunException` yang jelas (dulu `die()` atau warning). Di `app/config/cache.php`
  koneksi `memcached` dulu memakai driver `memcache`.

Test Redis/Memcached jalan bila server tersedia:
`PANADA_TEST_REDIS=127.0.0.1:6379 PANADA_TEST_MEMCACHED=127.0.0.1:11211 composer test`.
APCu butuh `apc.enable_cli=1`.

---

## Perubahan yang memutus kompatibilitas

Wajib dibaca sebelum memindahkan aplikasi Panada 1.x:

1. **PHP minimal 8.2.**
2. **Semua form POST butuh `csrf_field()`.** Endpoint API/webhook masukkan ke
   `csrf.except`.
3. **Database hanya PDO.** Nama driver lama (`mysqli`, `postgres`, `sqlite3`)
   dipetakan otomatis. MongoDB dan CUBRID dihapus.
4. `where('a.id', '=', 'b.a_id')` dulu dianggap perbandingan kolom; sekarang
   nilainya selalu diikat. Pakai `join()->on()` atau `whereRaw('a.id = b.a_id')`.
5. Nama tabel/kolom dengan karakter khusus atau tanda kutip ditolak oleh query
   builder. Pakai `query($sql, $bindings)` untuk kasus itu.
6. `results()`/`getAll()` mengembalikan `[]` (bukan `false`) saat kosong.
   `if (!$rows)` tetap bekerja.
7. `update()`/`delete()` tanpa kondisi melempar exception. `setThrowError()`
   tidak berefek lagi (error selalu dilempar).
8. Hanya method **publik yang ditulis di controller Anda** yang bisa dipanggil
   lewat URL. Method warisan (`output`, `redirect`, ...) tidak lagi bisa.
9. Model yang memakai `$this->model` / `$this->library` / `$this->resource` harus
   `extends \Resources\Model` (atau `use \Resources\Loadable;`).
10. Validation: field opsional yang kosong melewati aturan lain; field
    `required` yang tidak dikirim sekarang gagal.
11. Session: cookie dari driver `cookie` versi lama tidak valid lagi (pengguna
    logout sekali). Driver `cookie` dan `Encryption` butuh `secretKey` minimal
    32 karakter. Data terenkripsi versi lama tidak bisa dibuka.
12. Tampilan error diatur oleh `'debug'` di `main.php`, bukan lagi oleh
    parameter `Gear::send()` (parameter itu kini hanya `error_reporting`).
13. Controller yang mengembalikan array otomatis dikirim sebagai JSON.
14. Cache query di `ActiveRecord` dihapus (rawan data basi setelah update).
15. Driver cache `apc` sekarang memakai APCu.
16. ActiveRecord: `protected $table` di model dipakai; query ke tabel bernama
    class (huruf kecil) hanya terjadi kalau `$table` tidak ditulis.
17. ActiveRecord: `condition()`/`order()`/`limit()`/... tidak lagi terbawa ke
    query berikutnya pada objek yang sama.
18. ActiveRecord: `delete()`/`update()` tanpa argumen pada record yang sudah
    dimuat kini menghapus/meng-update record itu (dulu exception "without a
    WHERE").
19. ActiveRecord: memanggil method yang tidak ada melempar `RunException`.
20. `setOption()` dengan nama opsi yang tidak ada (atau bukan public) melempar
    `RunException`.
21. `Pagination::getUrl()` mengembalikan `[]` bila kurang dari 2 halaman.
22. `Rest::sendRequest()` mengembalikan `''` (bukan `false`) untuk respons
    kosong. Cek kegagalan dengan `=== false`.
23. `Rest::getRequest()` untuk GET hanya `$_GET`, untuk POST `$_POST` (atau body
    JSON), selain itu body request. Properti publik `$setRequestHeaders` (daftar
    header yang menumpuk) dihapus; pakai `setRequestHeaders([...])`.
24. JSON dari `outputJSON()` tidak lagi meng-escape `/` dan karakter Unicode
    (sama dengan controller yang me-return array).
25. Upload menolak file yang bisa dijalankan server (`.php*`, `.phtml`,
    `.phar`, `x.php.jpg`, `.htaccess`, `.user.ini`) tanpa pengecualian.
26. `Upload::getFileExtension('README')` mengembalikan `''` (dulu `'README'`).
    `getFileInfo()['folder']` berisi folder sebenarnya (termasuk `tahun/bulan`
    bila `autoCreateFolder`).
27. Email selalu UTF-8. Opsi privat lama (`charset`, `breakLine`, `boundary`)
    tidak bisa diset lagi lewat `setOption()`; `timeoutConnection` kini publik.
28. Email dengan alamat tidak valid, lampiran yang tidak ada, login SMTP gagal,
    atau penerima yang ditolak tidak dikirim (`mail()` → `false`). Dulu sebagian
    dari itu tetap "terkirim".
29. `smtpSecure => 'tls'` mewajibkan STARTTLS. Server yang tidak mendukungnya
    ditolak (pakai `'ssl'` atau `false` dengan sadar).
30. `Tools::setStatusHeader($code, $text)`: `$text` diabaikan (PHP mengirim
    teks status standar). `Tools::xmlDecode()` mengembalikan `false` untuk XML
    tidak valid.
31. Cache `apc`: method yang tidak ada di driver tidak lagi diteruskan ke
    fungsi global (`$cache->apcu_cas(...)` tidak bisa lagi; panggil fungsinya
    langsung).
32. Cache `dummy`: method statis `_get()`, `_set()`, `_delete()`, `_flush()`
    dihapus. `deleteValue()` mengembalikan `bool` di semua driver (Redis dulu
    jumlah kunci).
33. Nilai bernamespace yang disimpan versi lama tidak terbaca lagi (versi
    namespace kini acak), cukup diisi ulang. Driver cache buatan sendiri yang
    meng-*implements* `Resources\Interfaces\Cache` tetap jalan (interface hanya
    menambah tipe parameter, tanpa tipe return).

---

## Menjalankan test

```bash
composer install
composer test                      # = vendor/bin/phpunit
composer analyse                   # PHPStan level 2

# termasuk PostgreSQL, Redis, Memcached (dilewati kalau env kosong):
PANADA_TEST_PGSQL="host=127.0.0.1;dbname=panada_test;user=panada;password=panada" \
PANADA_TEST_REDIS=127.0.0.1:6379 PANADA_TEST_MEMCACHED=127.0.0.1:11211 composer test
```

Isi test suite:

- `tests/Unit`: autoloader, config, encryption, request, validation, CSRF,
  session (native/database/cookie), query builder (SQLite + PostgreSQL),
  ActiveRecord, Pagination, Image, Email (lewat server SMTP palsu di
  `tests/Fixtures/smtp.php`), Tools, driver cache.
- `tests/Http`: request HTTP sungguhan ke `php -S`. Mencakup aplikasi bawaan,
  plus aplikasi fixture untuk mode error (Whoops / bawaan / produksi), mode
  tanpa Composer, CSRF, flag cookie, Rest (klien dan server), dan upload file.

CI ada di `.github/workflows/ci.yml`: `composer install`, `composer test`
dengan service PostgreSQL 16, Redis, dan Memcached, lalu `composer analyse`.
