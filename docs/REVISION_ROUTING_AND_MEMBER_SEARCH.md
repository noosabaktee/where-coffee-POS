# Revisi Routing dan Live Search Member

## 1. Navigasi multi-page

Versi ini tidak lagi menggunakan pola SPA berupa seluruh page dalam satu file lalu mengganti class `hidden`. Route halaman didefinisikan di `routes/web.php` dan dilayani oleh `PageController`.

Shared layout:

- `resources/views/layouts/app.blade.php`
- `resources/views/partials/sidebar.blade.php`
- `resources/views/partials/mobile-header.blade.php`
- `resources/views/partials/feedback.blade.php`

Konten halaman:

- `resources/views/pages/dashboard.blade.php`
- `resources/views/pages/analytics.blade.php`
- `resources/views/pages/pos.blade.php`
- `resources/views/pages/inventory.blade.php`
- `resources/views/pages/reports.blade.php`
- `resources/views/pages/expenses.blade.php`
- `resources/views/pages/categories.blade.php`
- `resources/views/pages/crm.blade.php`
- `resources/views/pages/settings.blade.php`
- `resources/views/pages/outlets.blade.php`

Modal dipisahkan lagi ke `resources/views/partials/modals` dan hanya dimuat oleh halaman yang membutuhkannya.

## 2. Bootstrap per halaman

Frontend mengirim page ID saat mengambil bootstrap:

```text
GET /api/bootstrap?page=pos
```

Controller hanya mengirim dataset yang dibutuhkan halaman tersebut. Contohnya, halaman POS menerima produk dan kategori, tetapi tidak menerima seluruh direktori member. Direktori member dicari saat dibutuhkan.

## 3. Live search member POS

Select member diganti dengan input autocomplete. Alur pencarian:

1. Kasir mengetik minimal dua karakter.
2. Frontend menunggu 250 ms untuk menghindari request pada setiap keystroke.
3. Frontend memanggil `GET /api/customers/search?q=...`.
4. Backend mencari member aktif berdasarkan nama, kode member, atau nomor telepon dan membatasi hasil menjadi 10.
5. Kasir memilih hasil dengan mouse atau keyboard.
6. Hanya ID member terpilih yang dikirim pada checkout.

Endpoint tetap dilindungi session authentication, policy/permission `customers.view`, CSRF context aplikasi, dan query dibatasi agar respons tetap ringan.
