# 📦 Warehouse Management System (WMS)

Selamat datang di **Warehouse Management System (WMS)** — aplikasi lengkap berbasis **Laravel 12 (REST API)** dan **Vue 3 + Vite + TypeScript + TailwindCSS** untuk mengelola data barang, transaksi stok, laporan, dan roles user (admin/staff) secara profesional.

README ini menjelaskan:

* Cara instalasi backend (Laravel)
* Cara instalasi frontend (Vue)
* Cara login
* Role & Hak Akses
* Daftar fitur lengkap
* Cara penggunaan dari awal sampai akhir
* Struktur API & alur data

---

# 🚀 1. Teknologi Utama

## Backend (API)

* **Laravel 12**
* Laravel **Sanctum** (API Token)
* PostgreSQL
* Maatwebsite Excel (import/export Excel)
* DomPDF (export PDF)
* Middleware RBAC (Role-Based Access Control)

## Frontend

* **Vue 3**
* Vite
* TypeScript
* TailwindCSS
* Vue Router
* Chart.js (Dashboard grafik)

---

# 🛠️ 2. Instalasi Backend (Laravel 12)

### 2.1 Clone project

```bash
git clone <repo-url>
cd backend-wms
```

### 2.2 Install dependencies

```bash
composer install
```

### 2.3 Copy environment

```bash
cp .env.example .env
```

### 2.4 Setting database (PostgreSQL)

Di file `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=wms
DB_USERNAME=postgres
DB_PASSWORD=yourpass
```

### 2.5 Jalankan migrasi + seeder user

```bash
php artisan migrate --seed
```

Seeder membuat 2 akun default:

* Admin: `admin@example.com` / `password`
* Staff: `staff@example.com` / `password`

### 2.6 Jalankan server

```bash
php artisan serve
```

API default di:
**[http://localhost:8000/api](http://localhost:8000/api)**

---

# 🎨 3. Instalasi Frontend (Vue 3)

### 3.1 Masuk folder frontend

```bash
cd frontend-wms
```

### 3.2 Install dependency

```bash
npm install
```

### 3.3 Jalankan dev server

```bash
npm run dev
```

Frontend jalan di:
**[http://localhost:5173](http://localhost:5173)**

### 3.4 Setting API URL

Edit `.env`:

```
VITE_API_BASE_URL=http://localhost:8000/api
```

---

# 🔐 4. Login & Role

Aplikasi mendukung dua role:

## Role: Admin

✔ CRUD Barang
✔ Import / Export Barang
✔ Import / Export Transaksi
✔ Activity Logs
✔ Dashboard
✔ Laporan Stok
✔ Semua endpoint API

## Role: Staff

✔ Lihat barang
✔ Tambah transaksi
✔ Lihat transaksi
✔ Lihat laporan stok
✔ Dashboard
✘ Tidak bisa CRUD barang
✘ Tidak bisa export/import barang
✘ Tidak bisa akses activity logs

## Cara login

1. Buka `http://localhost:5173/login`
2. Masukkan email & password
3. Setelah login → otomatis masuk ke Dashboard

---

# 📘 5. Fitur-Fitur Utama

## 5.1 Dashboard

* Grafik transaksi 30 hari terakhir
* Summary cepat:

  * Total transaksi
  * Total qty masuk
  * Total qty keluar
  * Jumlah transaksi masuk
  * Jumlah transaksi keluar

## 5.2 Master Barang

Fitur admin:

* Tambah barang
* Edit barang
* Hapus barang
* Import Excel barang
* Export Excel
* Export PDF
* Download Template Excel

Fitur staff:

* Hanya bisa melihat

Fitur umum:

* Search by nama/sku/lokasi
* Sort ascending/descending
* Detail barang + riwayat transaksi

## 5.3 Transaksi Barang

Fitur untuk admin & staff:

* Barang Masuk
* Barang Keluar
* Update stok otomatis
* Validasi agar stok tidak minus
* Filter transaksi by:

  * jenis
  * tanggal
  * barang
* Sort transaksi
* Pagination
* Import transaksi via Excel
* Export Excel & PDF

## 5.4 Laporan Stok Rendah

Menampilkan barang:

* stok < stok_minimum
  atau
* stok < threshold global (default 10)

Fitur:

* Pagination
* Load More
* Mode otomatis (stok_minimum atau global threshold)

## 5.5 Autocomplete Barang (SKU / Nama)

Fitur untuk memudahkan input transaksi:

* Mengetik sebagian nama/sku → muncul suggestion
* Cepat dan efisien

## 5.6 Activity Log (admin-only)

Mencatat:

* Create barang
* Update barang
* Delete barang
* Import
* Export
* Transaksi

---

# 🔌 6. Alur Penggunaan (Step-by-Step)

## 6.1 Login terlebih dulu

Masuk sebagai admin atau staff.

## 6.2 Admin setup awal barang

1. Buka menu **Barang**
2. Klik **Tambah Barang**
3. Isi detail barang
4. Simpan

Atau bisa import banyak barang pakai Excel.

## 6.3 Tambah transaksi stok

1. Buka menu **Transaksi**
2. Pilih barang via autocomplete
3. Pilih jenis: MASUK / KELUAR
4. Isi qty
5. Simpan

Stok barang otomatis berubah.

## 6.4 Lihat grafik di Dashboard

Dashboard langsung ter-update setiap transaksi masuk/keluar.

## 6.5 Cek laporan stok rendah

Menu **Laporan Stok** menampilkan barang yang hampir habis.

## 6.6 Export laporan

* Export transaksi ke Excel/PDF
* Export barang ke Excel/PDF

## 6.7 Import data

* Barang: admin-only
* Transaksi: admin & staff

## 6.8 Detail barang + Riwayat

Klik item → tampil riwayat sampai 100 transaksi terakhir.

---

# 📡 7. Struktur API (Ringkas)

### Auth

```
POST /auth/login
GET  /auth/me
POST /auth/logout
```

### Barang

```
GET    /barang
GET    /barang/autocomplete?q=
POST   /barang (admin)
PUT    /barang/{id} (admin)
DELETE /barang/{id} (admin)
GET    /barang/{id}/detail
```

### Transaksi

```
GET  /transaksi
POST /transaksi
```

### Laporan

```
GET /laporan/stok-rendah
```

### Export

```
GET /exports/barang
GET /exports/barang-pdf
GET /exports/transaksi
GET /exports/transaksi-pdf
```

### Import

```
POST /imports/barang (admin)
POST /imports/transaksi
```

### Template

```
GET /imports/barang/template
GET /imports/transaksi/template
```

---

# 📁 8. Struktur Folder Penting

## Backend

```
app/
  Http/
    Controllers/Api/
    Middleware/
  Models/
exports/
imports/
routes/api.php
bootstrap/app.php  ← tempat daftar middleware
```

## Frontend

```
src/
  api/client.ts
  composables/useAuth.ts
  views/*.vue
  components/*.vue
  router/index.ts
```

---

# 🧪 9. Testing Manual yang Direkomendasikan

1. Login admin → cek menu lengkap
2. Login staff → pastikan menu admin hilang
3. Tambah barang → muncul di tabel
4. Edit barang → berubah sesuai input
5. Hapus barang → hilang dari tabel
6. Tambah transaksi → stok berubah
7. Transaksi keluar melebihi stok → error validasi
8. Import Excel barang → data masuk
9. Import Excel transaksi → stok berubah otomatis
10. Export Excel/PDF → file berhasil diunduh
11. Cek laporan stok rendah → tampil sesuai kondisi
12. Autocomplete → suggestion muncul
13. Activity Log → mencatat semua kegiatan admin
