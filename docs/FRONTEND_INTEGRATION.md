# Integrasi frontend asli

File HTML awal tetap menjadi acuan visual, tetapi implementasi aktif sudah dipecah menjadi Blade multi-page. Setiap menu mempunyai route dan file view sendiri; tidak ada lagi seluruh halaman yang ditumpuk dalam satu `app.blade.php` lalu ditampilkan/disembunyikan seperti SPA.

## Struktur view

```text
resources/views/
├── auth/login.blade.php
├── layouts/app.blade.php
├── pages/
│   ├── dashboard.blade.php
│   ├── analytics.blade.php
│   ├── pos.blade.php
│   ├── inventory.blade.php
│   ├── reports.blade.php
│   ├── expenses.blade.php
│   ├── categories.blade.php
│   ├── crm.blade.php
│   ├── settings.blade.php
│   └── outlets.blade.php
└── partials/
    ├── sidebar.blade.php
    ├── mobile-header.blade.php
    ├── feedback.blade.php
    └── modals/
```

Sidebar menggunakan anchor route Laravel. Fungsi `changeView()` hanya dipertahankan sebagai helper kompatibilitas untuk tombol internal dan sekarang melakukan navigasi URL penuh.

## Perubahan data

| Frontend awal | Integrasi sekarang |
|---|---|
| `localStorage` produk | `products` PostgreSQL melalui `/api/products` |
| `localStorage` transaksi | Checkout atomik `/api/transactions` |
| `sessionStorage` login | Laravel session authentication |
| Password plaintext array | Password hash Laravel |
| Permission array lokal | Spatie Laravel Permission + Policy |
| Gambar base64 permanen | Laravel Storage public disk |
| Perhitungan total browser | Perhitungan ulang server |
| Fungsi `fetchInitialData()` tidak tersedia | `/api/bootstrap` terimplementasi |
| Select member berisi semua pelanggan | Autocomplete live search `/api/customers/search` |

## Live search member POS

- Pencarian mulai setelah minimal dua karakter.
- Request memakai debounce 250 ms.
- Hasil dibatasi maksimal 10 member aktif.
- Dapat mencari nama, kode member, atau nomor telepon.
- Mendukung mouse, tombol panah atas/bawah, Enter, dan Escape.
- Member baru dipasang ke transaksi setelah salah satu hasil dipilih.

## Interaksi tambahan

- Transisi masuk halaman setelah route selesai dimuat.
- Card hover lift dan shadow ringan.
- Ilustrasi kopi SVG pada login dan dashboard.
- Animasi steam, coffee bean loader, dan feedback tombol.
- Loading overlay saat sinkronisasi, checkout, dan perpindahan outlet.
- Dukungan `prefers-reduced-motion`.
