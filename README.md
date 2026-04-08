# UKKGARA - Sistem Manajemen Parkir

Proyek ini adalah aplikasi manajemen parkir berbasis Laravel untuk kebutuhan UKK, dengan fokus pada:

- Login berbasis role
- Pemisahan hak akses admin, petugas, dan owner
- Transaksi kendaraan masuk dan keluar
- Perhitungan tarif otomatis
- Rekap dan log aktivitas

## Ringkasan Fitur

1. Autentikasi
- Login dan logout berbasis session.
- User nonaktif ditolak saat login.

2. Hak Akses Role
- Admin: kelola user, tarif, area parkir, kendaraan, log aktivitas.
- Petugas: input kendaraan masuk, proses kendaraan keluar, cetak struk dari halaman keluar.
- Owner: akses rekap transaksi.

3. Transaksi Parkir
- Kendaraan masuk memvalidasi area dan tarif aktif.
- Kendaraan keluar menghitung durasi parkir dengan pembulatan ke atas.
- Minimal biaya parkir 1 jam.

4. Dashboard
- Statistik kendaraan dan transaksi.
- Profil user di dashboard sudah dinamis sesuai akun yang login.

## Struktur Modul Utama

- Routing: [routes/web.php](routes/web.php)
- Middleware role: [app/Http/Middleware/EnsureRole.php](app/Http/Middleware/EnsureRole.php)
- Transaksi: [app/Http/Controllers/TransaksiController.php](app/Http/Controllers/TransaksiController.php)
- Dashboard: [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php)

## Alur Transaksi Keluar

1. Petugas mencari kendaraan aktif berdasarkan plat.
2. Sistem menghitung durasi dari waktu masuk ke waktu sekarang.
3. Rumus durasi menggunakan pembulatan ke atas:

$$
durasi\_jam = \left\lceil \frac{durasi\_menit}{60} \right\rceil
$$

4. Total biaya:

$$
biaya\_total = durasi\_jam \times tarif\_per\_jam
$$

5. Setelah diproses, status transaksi berubah menjadi keluar.
6. Cetak struk dilakukan langsung dari halaman transaksi keluar.

## Setup Lokal

1. Install dependency PHP:

```bash
composer install
```

2. Install dependency frontend:

```bash
npm install
```

3. Konfigurasi environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Migrasi dan seed database:

```bash
php artisan migrate --seed
```

5. Jalankan aplikasi:

```bash
php artisan serve
npm run dev
```

## Akun Default Seeder

- admin / admin123
- petugas / petugas123
- owner / owner123

Silakan cek seeder pada [database/seeders/DefaultUsersSeeder.php](database/seeders/DefaultUsersSeeder.php) bila ada perubahan kredensial.

## Pengujian

Pengujian fitur role dan endpoint ada pada:

- [tests/Feature/RoleAccessTest.php](tests/Feature/RoleAccessTest.php)
- [tests/Feature/RoleCrudEndpointTest.php](tests/Feature/RoleCrudEndpointTest.php)

Jalankan test:

```bash
php artisan test --filter=Role
```
