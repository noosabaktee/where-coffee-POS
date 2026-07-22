# Revisi UX: Skeleton, Checkout, Pagination, dan QRIS Statis

## Skeleton loading

Initial page loader menggunakan `resources/views/partials/skeleton.blade.php`, dengan bentuk berbeda untuk halaman POS, halaman tabel, dan dashboard/analitik. Skeleton berada di alur konten normal, bukan overlay absolut, sehingga tidak menutup sidebar dan tidak bertumpuk dengan komponen halaman. Loading modal tetap digunakan khusus untuk proses aksi seperti checkout dan CRUD.

## Konfirmasi pembayaran POS

Tombol **Selesaikan Pembayaran** memanggil `confirmCheckout()`. Dialog konfirmasi menampilkan total akhir, metode pembayaran, serta member yang dipilih. Endpoint checkout baru dipanggil setelah kasir menekan tombol konfirmasi.

## Input angka dengan separator

Input nominal, stok, poin, pajak, service charge, diskon, dan pembayaran tunai menggunakan atribut `data-number-format`. JavaScript menampilkan separator ribuan Indonesia (`100000` menjadi `100.000`) dan membersihkan separator sebelum payload dikirim ke Laravel.

## Pagination tabel

Pagination 10 baris per halaman diterapkan pada:

- inventori;
- laporan transaksi;
- CRM/member;
- biaya operasional;
- kategori;
- pengguna;
- cabang.

Pencarian dan filter otomatis mengembalikan pagination ke halaman pertama.

## QRIS statis

POS tidak lagi memanggil generator QR eksternal. Aset default berada pada:

```text
public/images/qris/where-coffee-qris.png
```

File tersebut adalah placeholder dan dapat diganti langsung dengan gambar QRIS toko. QRIS yang diunggah dari pengaturan outlet tetap dapat menjadi override.

## Rincian biaya di keranjang

Keranjang menampilkan subtotal, diskon, service charge, pajak, potongan poin, dan total tagihan sebelum transaksi dikonfirmasi.

## Modal invoice

Modal invoice memiliki:

- tombol X di bagian atas;
- tombol Tutup yang selalu terlihat di footer sticky;
- scroll internal untuk layar pendek;
- penutupan melalui klik backdrop;
- penutupan melalui tombol Escape.

## Dependency dan lingkungan

- `laravel/tinker` menggunakan constraint `^3.0`.
- Laravel Sail dihapus.
- `compose.yaml` dihapus.
- Instalasi mengasumsikan PostgreSQL sudah tersedia pada host atau server database pengguna.
