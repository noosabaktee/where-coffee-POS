# Revisi Loading dan Ekspor Excel

## Pemisahan jenis loading

Aplikasi menggunakan dua indikator loading dengan fungsi yang berbeda:

1. `pageSkeleton` hanya tampil ketika route halaman pertama kali dibuka dan endpoint bootstrap sedang mengambil data halaman.
2. `actionLoadingModal` tampil ketika proses mutasi berlangsung, antara lain checkout, tambah/edit/hapus data, penyimpanan pengaturan, reset demo, perpindahan outlet, dan pembuatan laporan Excel.

Refresh data setelah CRUD menggunakan `bootstrapApplication({ quiet: true })`, sehingga halaman tidak kembali menampilkan skeleton setelah setiap aksi.

## Perbaikan skeleton

Skeleton tidak lagi menggunakan posisi absolut. Skeleton dan `pageContent` berada di dalam elemen `<main>` yang sama dan ditukar visibilitasnya oleh `setPageLoading()`. Struktur skeleton dibuat responsif:

- POS memiliki grid katalog dan keranjang tanpa elemen sticky;
- halaman tabel menggunakan grid desktop dan kartu ringkas pada mobile;
- dashboard dan analitik menggunakan ukuran blok yang mengikuti kartu aktual;
- seluruh kolom menggunakan `min-w-0`, batas lebar responsif, dan container `overflow-hidden` untuk mencegah tumpang tindih.

## Ekspor Excel

Endpoint berikut tetap digunakan:

```text
GET /api/transactions/export
```

Respons sekarang berupa `.xlsx` dengan MIME type Excel Open XML. Workbook berisi:

- sheet **Transaksi** untuk ringkasan invoice;
- sheet **Detail Item** untuk rincian produk pada setiap transaksi;
- header berwarna, auto filter, freeze pane, format Rupiah, serta baris total.

Implementasi workbook berada di:

```text
app/Exports/TransactionReportExcel.php
```

Dependency:

```json
"phpoffice/phpspreadsheet": "^5.9"
```
