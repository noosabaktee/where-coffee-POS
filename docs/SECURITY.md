# Catatan keamanan

- Login memakai Laravel session guard dan CSRF.
- Password disimpan melalui cast `hashed` / Bcrypt.
- Akses resource diperiksa menggunakan Policy dan permission Spatie.
- Resolver outlet mengikat request ke outlet yang berhak diakses pengguna.
- Checkout mengabaikan harga dari browser, mengunci row produk, dan berjalan di dalam transaksi database.
- Detail transaksi menyimpan snapshot untuk menjaga integritas laporan.
- Upload data URI dibatasi pada format gambar dan ukuran maksimum 3 MB.
- Produk/member yang memiliki histori tidak dihapus secara destruktif; statusnya dinonaktifkan.
- Reset data demo dibatasi permission dan environment flag.

Sebelum produksi:

1. Ubah semua password hasil seeder.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, dan `WHERE_COFFEE_DEMO_RESET=false`.
3. Gunakan HTTPS dan `SESSION_SECURE_COOKIE=true`.
4. Batasi akses database dan lakukan backup terjadwal.
5. Jalankan queue worker melalui Supervisor/systemd bila antrean digunakan.
6. Gunakan build Tailwind lokal bila kebijakan produksi tidak mengizinkan CDN.
