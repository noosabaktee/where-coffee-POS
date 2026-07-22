# Where Coffee POS — Laravel + PostgreSQL

Backend dan integrasi frontend untuk aplikasi **Where Coffee Premium POS & Inventory**. Frontend menggunakan Blade multi-page dengan route Laravel nyata untuk setiap modul. Operasi CRUD dan checkout tetap memakai endpoint JSON berbasis session Laravel, CSRF, policy, dan permission. Endpoint bootstrap menerima `page` sehingga hanya dataset yang diperlukan halaman aktif yang dikirim.

## Stack

- Laravel 13
- Laravel Tinker 3.x
- PHP 8.3+
- PostgreSQL
- Blade
- Tailwind CSS CDN sesuai frontend awal
- Vanilla JavaScript
- Chart.js
- Laravel session authentication
- Spatie Laravel Permission
- Laravel Policies dan Form Requests
- Laravel Storage disk `public`
- PHPUnit
- PhpSpreadsheet 5.x untuk ekspor Excel `.xlsx`

## Modul yang tersedia

Dashboard, analisis bisnis, POS dan checkout, inventori, kategori, CRM/member, loyalitas poin, pengeluaran, laporan transaksi, ekspor Excel `.xlsx`, pengaturan per outlet, CRUD toko cabang khusus Administrator, pengelolaan staff, role/permission, pergantian outlet, mutasi stok, serta reset data demo yang hanya dapat diaktifkan melalui environment.

## Instalasi

Buat database PostgreSQL `where_coffee`, lalu jalankan:

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate:fresh --seed
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### Akun hasil seeder

| Peran | Username | Password |
|---|---|---|
| Administrator | `admin` | `123456` |
| Kasir utama | `kasir1` | `123456` |
| Manajer utama | `managerutama` | `123456` |
| Kasir cabang Selatan | `kasir2` | `123456` |
| Manajer cabang Selatan | `manager2` | `123456` |

Ganti seluruh password demo sebelum aplikasi digunakan di produksi.


## Arsitektur Blade multi-page

Aplikasi ini **bukan SPA**. Setiap menu membuka URL dan file Blade tersendiri sehingga lebih mudah dirawat:

| Halaman | URL | View |
|---|---|---|
| Dashboard | `/dashboard` | `resources/views/pages/dashboard.blade.php` |
| Analisis bisnis | `/analisis-bisnis` | `resources/views/pages/analytics.blade.php` |
| Sistem kasir | `/pos` | `resources/views/pages/pos.blade.php` |
| Inventori | `/inventori` | `resources/views/pages/inventory.blade.php` |
| Laporan | `/laporan` | `resources/views/pages/reports.blade.php` |
| Biaya | `/biaya-operasional` | `resources/views/pages/expenses.blade.php` |
| Kategori | `/kategori` | `resources/views/pages/categories.blade.php` |
| CRM | `/crm` | `resources/views/pages/crm.blade.php` |
| Pengaturan | `/pengaturan` | `resources/views/pages/settings.blade.php` |
| Kelola cabang | `/cabang` | `resources/views/pages/outlets.blade.php` |

Layout, sidebar, header mobile, feedback/toast, dan modal reusable berada di `resources/views/layouts` serta `resources/views/partials`.

Pada halaman POS, member dipilih melalui input autocomplete. Pencarian mulai berjalan setelah dua karakter, memakai debounce 250 ms dan endpoint `GET /api/customers/search?q=...`. Hasil hanya muncul saat pengguna mengetik dan dapat dipilih menggunakan mouse maupun tombol panah dan Enter.


## Revisi UX transaksi dan data

- Saat pertama membuka halaman, area konten memakai skeleton shimmer yang mengikuti layout halaman. Skeleton ditempatkan dalam alur layout sehingga tidak menumpuk di atas elemen lain.
- Aksi yang mengubah data—checkout, CRUD, pengaturan, reset demo, perpindahan outlet, dan ekspor—menggunakan loading modal agar pengguna tidak mengirim aksi ganda.
- Checkout POS menampilkan dialog konfirmasi berisi total, metode pembayaran, dan member sebelum transaksi dikirim.
- Semua input numerik menggunakan separator ribuan Indonesia, misalnya `100000` tampil sebagai `100.000`; nilai tetap dikirim ke backend sebagai angka murni.
- Tabel inventori, laporan, CRM, biaya, kategori, pengguna, dan cabang memakai pagination 10 baris per halaman.
- QRIS bersifat statis dan memakai aset lokal `public/images/qris/where-coffee-qris.png`. Ganti file tersebut dengan QRIS toko tanpa mengubah JavaScript.
- Keranjang POS menampilkan service charge, pajak, potongan poin, dan total akhir sebelum pembayaran.
- Modal invoice dapat ditutup melalui tombol X, tombol Tutup, klik backdrop, atau tombol Escape; kontennya dapat di-scroll pada layar pendek.
- Ekspor laporan menghasilkan workbook Excel `.xlsx` dengan sheet ringkasan transaksi dan detail item, format Rupiah, filter, freeze header, serta baris total.
- Proyek tidak menyertakan Docker Compose maupun Laravel Sail.

## Revisi tampilan terbaru

- Palet dashboard, login, sidebar, kartu statistik, dan halaman cabang dibuat lebih berwarna dengan gradient serta accent border yang tetap mengikuti desain awal.
- Logo kopi pada login dan sidebar menggunakan SVG inline sehingga tetap tampil walaupun ikon CDN terlambat dimuat.
- Katalog POS menggunakan foto menu lokal berformat WebP di `public/images/menu`, bukan lagi ikon placeholder.
- Profil pengguna sidebar dibuat ringkas, memiliki truncation, dan tidak keluar dari lebar sidebar.
- Toast tampil dari pojok kanan bawah.
- Administrator memperoleh menu **Kelola Cabang** untuk menambah, mengubah, menonaktifkan, dan menghapus cabang kosong.

### Memperbarui instalasi yang sudah memiliki database

Revisi ini tidak menambah tabel baru. Karena ekspor Excel menambahkan dependency PhpSpreadsheet, setelah mengganti source jalankan:

```bash
composer update phpoffice/phpspreadsheet
php artisan optimize:clear
php artisan test
```

Bila instalasi Anda belum pernah menerima revisi cabang dan foto menu sebelumnya, jalankan sekali:

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ProductSeeder
```

Seeder produk memperbarui foto menu tanpa menghapus transaksi yang sudah ada. Cabang yang telah memiliki transaksi tidak dapat dihapus demi menjaga histori; gunakan status nonaktif.

## Konfigurasi PostgreSQL

Buat database terlebih dahulu:

```sql
CREATE DATABASE where_coffee;
```

Kemudian sesuaikan bagian berikut di `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=where_coffee
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

## Laravel Tinker

Dependency menggunakan `laravel/tinker:^3.0`. Jalankan:

```bash
php artisan tinker
```

## Menjalankan pengujian

```bash
php artisan test
```

Test suite menggunakan SQLite in-memory agar cepat dan mencakup autentikasi, routing Blade per halaman, live search member, checkout server-side, pengurangan stok, audit mutasi stok, validasi harga, isolasi outlet, CRUD cabang khusus Administrator, serta respons ekspor Excel.

## Struktur penting

```text
app/
├── Exports                # Builder workbook laporan Excel
├── Http/Controllers       # Endpoint session dan JSON
├── Http/Requests          # Validasi terpisah tiap use case
├── Http/Resources         # Kontrak respons frontend
├── Models                 # Eloquent model dan relationship
├── Policies               # Authorization per resource
└── Services               # Checkout, dashboard, gambar, nomor referensi

database/
├── migrations             # Satu tabel per file migration
├── seeders                # Data demo realistis
└── factories              # Data pengujian

resources/views/
├── auth/login.blade.php
├── layouts/app.blade.php
├── pages/                 # Satu file Blade per halaman
└── partials/              # Sidebar, header, toast, dan modal

public/js/where-coffee.js  # Interaksi umum dan operasi API
```

## Prinsip checkout

Frontend hanya mengirim `product_id` dan jumlah. Harga modal, harga jual, pajak, service charge, diskon, poin, total, profit, dan stok dihitung ulang oleh server. Checkout berjalan di dalam transaksi database dan produk dikunci dengan `lockForUpdate()` untuk mencegah penjualan stok yang sama secara bersamaan.

## Penyimpanan gambar

Gambar produk, logo, dan QRIS yang dipilih dari browser dikompresi menjadi data URI, divalidasi, lalu disimpan ke disk Laravel `public`. Pastikan perintah berikut sudah dijalankan:

```bash
php artisan storage:link
```

## Reset data demo

Reset dari menu pengaturan hanya aktif ketika:

```dotenv
WHERE_COFFEE_DEMO_RESET=true
```

Pada produksi, gunakan `false`.

## Dokumentasi lanjutan

- [ERD](docs/ERD.md)
- [Daftar endpoint](docs/API.md)
- [Pemetaan frontend](docs/FRONTEND_INTEGRATION.md)
- [Catatan keamanan](docs/SECURITY.md)
- [Validasi build](docs/BUILD_VALIDATION.md)

## Dashboard & Analisis Periode

Dashboard dan halaman Analisis Bisnis sekarang mendukung filter tanggal maksimal 366 hari melalui endpoint:

```text
GET /api/dashboard?from=2026-07-01&to=2026-07-31
```

Metrik yang tersedia meliputi pendapatan, laba kotor, laba bersih, biaya, transaksi, average basket, item terjual, gross/net margin, rasio biaya, transaksi member, repeat customer, pajak, diskon, tren cashflow, menu terlaris, kontribusi kategori, metode pembayaran, jam sibuk, perbandingan periode sebelumnya, dan proyeksi pendapatan akhir bulan.

Seluruh akun demo hasil `UserSeeder` menggunakan password yang sama:

```text
123456
```

Password tersebut hanya ditujukan untuk lingkungan demo. Ganti seluruh password sebelum deployment produksi.
