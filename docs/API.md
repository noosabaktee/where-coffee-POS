# Endpoint aplikasi

Semua endpoint `/api/*` menggunakan middleware `auth`, session cookie, CSRF, dan resolver outlet aktif.

## Session

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/login` | Login menggunakan username dan password |
| POST | `/logout` | Logout, invalidasi session, regenerasi CSRF |

## Bootstrap dan dashboard

| Method | Endpoint | Permission |
|---|---|---|
| GET | `/api/bootstrap?page={pageId}` | Authenticated; payload disesuaikan dengan halaman aktif |
| GET | `/api/dashboard` | `dashboard.view` atau `analytics.view` |
| PUT | `/api/context/outlet` | `outlets.switch` |

## CRUD

| Resource | Endpoint | Permission utama |
|---|---|---|
| Outlets | `/api/outlets` | `outlets.view/create/update/delete` |
| Categories | `/api/categories` | `categories.*` |
| Products | `/api/products` | `products.*` |
| Customers | `/api/customers` | `customers.*` |
| Live search member | `/api/customers/search?q={kata}` | `customers.view` |
| Expenses | `/api/expenses` | `expenses.*` |
| Users | `/api/users` | `users.*` |

## Transaksi

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/transactions` | Daftar transaksi outlet aktif |
| POST | `/api/transactions` | Checkout POS; semua nominal dihitung server |
| GET | `/api/transactions/{transaction}` | Detail invoice |
| GET | `/api/transactions/export` | Unduh workbook Excel `.xlsx` (sheet Transaksi dan Detail Item) |

Contoh checkout:

```json
{
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 3, "quantity": 1}
  ],
  "customer_id": 4,
  "discount_percentage": 5,
  "payment_method": "QRIS",
  "amount_paid": 0,
  "use_points": true
}
```

Server tidak menerima harga dari browser sebagai sumber kebenaran.

## Pengaturan dan role

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/settings` | Setting outlet aktif |
| PUT | `/api/settings` | Update profil, pajak, logo, dan QRIS |
| PUT | `/api/roles/{role}/menus` | Sinkronisasi menu Kasir atau Outlet ke permission Spatie |
| POST | `/api/maintenance/reset-demo` | Reset data demo bila environment mengizinkan |


## Route halaman Blade

Navigasi antarmuka menggunakan request halaman penuh, bukan pergantian view SPA.

| Method | URL | View |
|---|---|---|
| GET | `/dashboard` | `pages.dashboard` |
| GET | `/analisis-bisnis` | `pages.analytics` |
| GET | `/pos` | `pages.pos` |
| GET | `/inventori` | `pages.inventory` |
| GET | `/laporan` | `pages.reports` |
| GET | `/biaya-operasional` | `pages.expenses` |
| GET | `/kategori` | `pages.categories` |
| GET | `/crm` | `pages.crm` |
| GET | `/pengaturan` | `pages.settings` |
| GET | `/cabang` | `pages.outlets` khusus Administrator |

## Dashboard Metrics dengan Filter Periode

```http
GET /api/dashboard?from=2026-07-01&to=2026-07-31
```

Kedua parameter bersifat opsional sebagai pasangan. Tanpa parameter, periode default adalah awal bulan berjalan sampai hari ini. Rentang maksimal 366 hari.

Respons mencakup `period`, `today`, `month`, `summary`, `comparison`, `trend`, `category_contribution`, `top_products`, `payment_mix`, `peak_hours`, `low_stock`, dan `insights`.
