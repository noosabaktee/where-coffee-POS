# Revisi Dashboard, Analytics, dan Coffee Loader

## Dashboard

- Filter tanggal dari/sampai dengan preset 7 hari, 30 hari, bulan berjalan, dan 90 hari.
- Perbandingan otomatis terhadap periode sebelumnya dengan durasi yang sama.
- KPI periode: pendapatan, laba bersih, transaksi, average basket, item terjual, dan persentase transaksi member.
- Ringkasan bulan berjalan: omzet, laba bersih, net margin, pertumbuhan dibanding bulan lalu, dan proyeksi omzet akhir bulan.
- Grafik tren adaptif: harian untuk rentang pendek, mingguan untuk rentang menengah, dan bulanan untuk rentang panjang.
- Ranking menu terlaris, komposisi metode pembayaran, peringatan stok, dan smart insight.

## Analisis Bisnis

- Filter periode yang sama dengan dashboard.
- KPI profitabilitas dan perilaku pelanggan.
- Cashflow trend, kontribusi kategori, ranking menu, pola transaksi per jam, dan metode pembayaran.
- Operational ratios: expense ratio, rata-rata item per transaksi, pajak terkumpul, diskon, member rate, dan repeat customer rate.

## Backend

- `DashboardPeriodRequest` memvalidasi rentang tanggal dan membatasi analisis maksimal 366 hari.
- `DashboardService` menghitung metrik periode, periode pembanding, tren adaptif, top products, payment mix, peak hours, dan insight.
- Endpoint: `GET /api/dashboard?from=YYYY-MM-DD&to=YYYY-MM-DD`.

## Loading Modal

Loading aksi CRUD, checkout, perpindahan outlet, dan filter analitik menggunakan animasi cangkir kopi hangat dengan:

- tiga jalur uap;
- animasi cairan kopi;
- cangkir mengambang;
- biji kopi bergerak;
- indikator tiga titik.

Skeleton tetap hanya digunakan ketika halaman pertama kali dibuka.

## Seeder Demo

- Semua password demo: `123456`.
- Seeder produksi demo membuat histori transaksi dan pengeluaran sekitar delapan bulan agar grafik bulanan lebih representatif.
- Saat environment `testing`, volume histori diperkecil agar test suite tetap cepat.
