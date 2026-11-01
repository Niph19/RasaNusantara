<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

# Product Requirements Document
# Sistem Katering Nusantara V2

**Versi Dokumen:** 1.0  
**Tanggal:** 14 September 2026

---

## 1. Ringkasan Proyek

Sistem web admin untuk usaha katering "Rasa Nusantara" yang telah beroperasi lintas kota dengan armada pengiriman mandiri. Proyek ini berfokus pada dasbor admin yang mampu menampilkan data pesanan secara lengkap dan terstruktur, sekaligus membuktikan penerapan *Nested Eager Loading* sebagai solusi atas N+1 Query Problem pada relasi data yang dalam.

---

## 2. Tujuan

- Membangun skema database relasional 7 tabel yang merepresentasikan operasional katering nyata.
- Menghasilkan dasbor admin yang menampilkan data pesanan lintas entitas dalam satu tabel terintegrasi.
- Mendemonstrasikan dan mendokumentasikan perbaikan performa query menggunakan Nested Eager Loading di Laravel.

---

## 3. Ruang Lingkup

### Termasuk
- Migrasi dan model untuk 7 tabel.
- Factory dan seeder dengan data dummy realistis.
- Satu halaman dasbor admin dengan tabel pesanan yang dipaginasi.
- Implementasi Nested Eager Loading dan pencegahan Lazy Loading di lingkungan non-produksi.
- README GitHub yang menyertakan bukti perbandingan performa (screenshot Debugbar).

### Tidak Termasuk
- Halaman CRUD individual per entitas.
- API atau antarmuka mobile.
- Notifikasi atau laporan ekspor.

---

## 4. Skema Database

### 4.1 Tabel dan Kolom

| Tabel | Kolom | Catatan |
|---|---|---|
| `cities` | `id`, `name` | Master kota |
| `categories` | `id`, `name` | Kategori menu: Pembuka, Utama, Penutup, dll. |
| `payment_methods` | `id`, `name` | Metode pembayaran tersedia |
| `couriers` | `id`, `name`, `phone` | Data kurir pengantar |
| `customers` | `id`, `name`, `phone`, `city_id` | Pelanggan, FK ke `cities` |
| `menus` | `id`, `name`, `price`, `category_id` | Item menu, FK ke `categories` |
| `orders` | `id`, `customer_id`, `payment_method_id`, `courier_id`, `status` | Transaksi utama |
| `order_items` | `id`, `order_id`, `menu_id`, `qty`, `subtotal` | Detail item per pesanan |

### 4.2 Relasi Antar Model

```
Customer    belongsTo  City
Menu        belongsTo  Category
Order       belongsTo  Customer, PaymentMethod, Courier
Order       hasMany    OrderItem
OrderItem   belongsTo  Order, Menu
```

Kedalaman relasi maksimum pada query dasbor: **3 tingkat** (`Order → Customer → City`, `Order → OrderItem → Menu → Category`).

---

## 5. Data Dummy (Factory & Seeder)

Urutan eksekusi wajib diikuti untuk menghindari constraint error:

| Urutan | Entitas | Jumlah |
|---|---|---|
| 1 | Cities | 50 |
| 2 | Categories | 10 |
| 3 | PaymentMethods | 3 |
| 4 | Couriers | 10 |
| 5 | Menus | 150 |
| 6 | Customers | 200 |
| 7 | Orders | 150 |
| 8 | OrderItems | 3–5 per Order (acak, dalam loop seeder) |

Total baris `order_items` yang diharapkan: sekitar 450–750 baris.

---

## 6. Fitur: Dasbor Admin

### 6.1 Tampilan Tabel

Satu tabel HTML yang memuat seluruh pesanan dengan kolom:

| Kolom | Konten |
|---|---|
| **ID Order** | `order.id` |
| **Pelanggan** | `customer.name` — `customer.city.name` |
| **Pesanan** | Loop `orderItems`: `qty × Nama Menu (Kategori)` per baris |
| **Pengiriman & Pembayaran** | `courier.name` — `paymentMethod.name` |

### 6.2 Paginasi

- 15 data per halaman menggunakan `->paginate(15)`.
- Link paginasi ditampilkan di bawah tabel.

### 6.3 Persyaratan Performa

- Total query pada satu halaman: **kurang dari 10 query** setelah optimasi.
- Wajib menggunakan Nested Eager Loading:

```php
Order::with([
    'customer.city',
    'courier',
    'paymentMethod',
    'orderItems.menu.category',
])->paginate(15);
```

### 6.4 Filter
- Tambahkan filter pada CRUD dashboard admin (Ascending/Descending)
- Tambah filter pada setiap kolom pada tabel data, namun hanya bisa diaktifkan 1 saja. Tidak bisa filter aktif lebih dari 1 pada kolom yang berbeda

---

## 7. Implementasi Teknis

### 7.1 Stack

- **Framework:** Laravel (versi terkini stabil)
- **Database:** MySQL / SQLite (lokal)
- **Debugging:** Laravel Debugbar

### 7.2 Pencegahan Lazy Loading

Tambahkan di `App\Providers\AppServiceProvider::boot()`:

```php
Model::preventLazyLoading(! app()->isProduction());
```

Ini akan melempar exception jika ada relasi yang diakses tanpa eager loading di lingkungan development, memaksa developer memperbaiki query sebelum naik ke produksi.

### 7.3 Struktur File yang Dihasilkan

```
database/
  migrations/
    xxxx_create_cities_table.php
    xxxx_create_categories_table.php
    xxxx_create_payment_methods_table.php
    xxxx_create_couriers_table.php
    xxxx_create_customers_table.php
    xxxx_create_menus_table.php
    xxxx_create_orders_table.php
    xxxx_create_order_items_table.php
  factories/
    CityFactory.php
    CategoryFactory.php
    PaymentMethodFactory.php
    CourierFactory.php
    MenuFactory.php
    CustomerFactory.php
    OrderFactory.php
    OrderItemFactory.php
  seeders/
    DatabaseSeeder.php

app/Models/
  City.php
  Category.php
  PaymentMethod.php
  Courier.php
  Menu.php
  Customer.php
  Order.php
  OrderItem.php

app/Http/Controllers/
  DashboardController.php

resources/views/
  dashboard/index.blade.php
```

---

## 8. Kriteria Penerimaan

| # | Kriteria | Cara Verifikasi |
|---|---|---|
| 1 | Semua migrasi berjalan tanpa error | `php artisan migrate` sukses |
| 2 | Seeder mengisi data sesuai jumlah yang ditentukan | Cek jumlah baris di tiap tabel |
| 3 | Dasbor menampilkan semua kolom yang disyaratkan | Review tampilan browser |
| 4 | Query count < 10 per halaman setelah optimasi | Screenshot Laravel Debugbar |
| 5 | `preventLazyLoading` aktif dan tidak ada exception yang muncul | Jalankan di mode `APP_ENV=local` |
| 6 | README menyertakan dua screenshot Debugbar (sebelum dan sesudah) | Review repository GitHub |

---

## 9. Deliverable

1. Repository GitHub dengan kode lengkap.
2. `README.md` yang diperbarui sesuai format yang ditentukan, termasuk:
   - Screenshot Debugbar kondisi Lazy Loading (ratusan query / error).
   - Screenshot Debugbar kondisi Eager Loading (di bawah 10 query).
3. Link repository dikumpulkan ke Google Classroom beserta screenshot tampilan dasbor.

---

## 10. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Urutan seeder salah | Foreign key constraint error saat seeding | Ikuti urutan eksekusi di bagian 5 secara ketat |
| Lazy Loading tidak terdeteksi | Query meledak di produksi tanpa peringatan | Aktifkan `preventLazyLoading` sejak awal development |
| Nested relation terlupakan | Kolom "Pesanan" tampil kosong atau error | Verifikasi dengan `dd($orders->first()->orderItems->first()->menu->category)` sebelum ke view |
| Data order_items tidak ter-generate | Tabel pesanan kosong di dasbor | Gunakan loop eksplisit di seeder, bukan relasi factory |