# Sistem Katering Nusantara (Advanced Eager Loading)
**Nama:** Hanif El Hakim | **Kelas:** XI-1 / XI-RPL



Simulasi sistem katering dengan 7 tabel berelasi kompleks. Proyek ini
mendemonstrasikan penyelesaian ekstrim dari N+1 Query Problem menggunakan
Nested Eager Loading.


## Teknologi

- PHP 8.3+
- Laravel 13
- MySQL atau SQLite
- Laravel Debugbar untuk pengembangan lokal

## Menjalankan aplikasi

1. Pasang dependensi:

   ```bash
   composer install
   npm install
   ```

2. Siapkan konfigurasi aplikasi dan koneksi database pada `.env`:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. Buat skema dan data contoh:

   ```bash
   php artisan migrate:fresh --seed
   ```

   **Peringatan:** `migrate:fresh` menghapus semua tabel pada koneksi yang dipilih, termasuk tabel pengguna, sesi, cache, dan pekerjaan Laravel. Gunakan hanya pada database lokal/pengembangan yang boleh direset; untuk database yang sudah berisi data penting, jangan jalankan perintah ini.

4. Bangun aset frontend dan jalankan server:

   ```bash
   npm run build
   php artisan serve
   ```

   Akun admin bawaan seeder:

   ```text
   Email: admin@rasanusantara.test
   Password: password
   ```

   Seeder membuat satu pengguna admin dan sembilan pengguna customer. Password seluruh akun dummy yang dibuat factory adalah `password`.

   Halaman dashboard tersedia di `/dashboard`, sedangkan pengelolaan data tersedia di `/admin`. Keduanya hanya dapat diakses pengguna dengan email terverifikasi dan peran `admin`.

### Menyiapkan akses admin

Registrasi pengguna baru selalu mendapat peran `customer`; peran tidak diambil dari masukan formulir. Setelah mendaftarkan akun dan memverifikasi email, berikan akses admin melalui:

```bash
php artisan app:promote-user-to-admin alamat@emailanda
```

Perintah hanya mengubah peran akun yang sudah ada. Jangan memberikan akses admin kepada akun yang tidak dipercaya. Pengguna customer tetap dapat login dan diarahkan ke halaman profil; akses dashboard pesanan dan CRUD tetap khusus admin.

## Skema dan data contoh

PRD mendefinisikan delapan tabel domain (meski ringkasannya menyebut tujuh): `cities`, `categories`, `payment_methods`, `couriers`, `customers`, `menus`, `orders`, dan `order_items`. Tabel autentikasi/infrastruktur Laravel tetap terpisah.

`DatabaseSeeder` membuat data dengan urutan relasi yang aman:

| Entitas | Jumlah |
|---|---:|
| Cities | 50 |
| Categories | 10 |
| Payment methods | 3 |
| Couriers | 10 |
| Menus | 150 |
| Customers | 200 |
| Orders | 150 |
| Order items | 3–5 untuk setiap order (450–750 total) |

Item pesanan dibuat secara eksplisit dalam loop seeder; `subtotal` dihitung dari `qty × menu.price`.

## Pengelolaan data (CRUD)

Menu **Kelola Data** membuka daftar Kota, Kategori, Metode Pembayaran, Kurir, Pelanggan, Menu, Pesanan, dan Item Pesanan. Setiap daftar mendukung pencarian dan pagination 15 data per halaman. Filter kategori tersedia pada daftar menu dan item pesanan (yang merujuk ke menu). Nilai relasi seperti kota pelanggan, kategori menu, dan nama menu dapat digunakan untuk pencarian pada daftar terkait.

Form mengikuti kolom dan relasi database. Status pesanan dibatasi pada `menunggu`, `diproses`, `dikirim`, atau `selesai`. Subtotal item pesanan dihitung otomatis dari jumlah dikali harga menu; nilai subtotal dari request tidak dipercaya. Data kota, kategori, metode pembayaran, kurir, pelanggan, dan menu yang masih direferensikan tidak dapat dihapus agar penghapusan tidak merambat lewat foreign key. Menghapus pesanan akan menghapus item-item pesanan tersebut sesuai relasi database.

Semua rute dashboard dan CRUD dibatasi melalui middleware autentikasi, verifikasi email, dan Gate `manage-catering`, yang hanya mengizinkan peran `admin`.

## Nested eager loading

Controller mengambil semua relasi yang digunakan view sebelum render:

```php
Order::query()
    ->with([
        'customer.city',
        'courier',
        'paymentMethod',
        'orderItems.menu.category',
    ])
    ->orderByDesc('orders.id')
    ->paginate(15);
```

Relasi bertingkat pelanggan/kota dan item/menu/kategori dimuat dengan nested eager loading, sementara kurir dan metode pembayaran dimuat sebagai relasi langsung. Query utama memisahkan pesanan beserta masing-masing relasi yang dimuat, lalu Laravel menggabungkannya ke model tanpa lazy loading. Dengan driver session/cache lokal yang tidak memakai database, halaman dashboard berada di bawah batas PRD kurang dari 10 query aplikasi.

Lazy loading dicegah di luar production melalui `Model::preventLazyLoading(! app()->isProduction())`. Saat mengakses relasi yang belum dimuat dalam lingkungan lokal atau testing, Laravel melempar `LazyLoadingViolationException`.

## Verifikasi

Jalankan tes:

```bash
php artisan test
```

Tes dashboard mencakup akses autentikasi, isi relasi bertingkat, paginasi 15 pesanan, batas query, pencegahan lazy loading, jumlah data seed, dan konsistensi subtotal.

## Bukti Laravel Debugbar

Debugbar tersedia sebagai dependensi pengembangan. Untuk mengukur query halaman tanpa query tambahan dari session dan cache database, gunakan driver file di environment lokal:

```powershell
$env:APP_ENV = 'local'
$env:APP_DEBUG = 'true'
$env:SESSION_DRIVER = 'file'
$env:CACHE_STORE = 'file'
php artisan serve
```

Login, lalu buka `/dashboard` dan periksa panel Queries (hasil request yang direkam: 9 query). Jangan aktifkan Debugbar atau debug mode di production.

### Lazy loading

Lazy loading dicegah di lingkungan lokal; demonstrasi baseline berakhir sebagai guard `LazyLoadingViolationException`, bukan menjalankan ratusan query.

### Nested eager loading

![Tampilan dashboard dengan nested eager loading](docs/screenshots/dashboard-desktop.png)
