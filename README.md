<div align="center">
  <h1>🏢 Asset Management App</h1>
  <p><strong>Sistem Manajemen Aset Berbasis Web</strong></p>
  
  ![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=flat-square&logo=laravel)
  ![Filament](https://img.shields.io/badge/Filament-3.x-yellow?style=flat-square)
  ![PHP](https://img.shields.io/badge/PHP-8.1+-blue?style=flat-square&logo=php)
  ![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
</div>

---

## 📋 Tentang Aplikasi

**Asset Management App** adalah aplikasi manajemen aset berbasis web yang dibangun menggunakan Laravel dan Filament Admin Panel. Aplikasi ini dirancang untuk membantu organisasi/perusahaan dalam mengelola aset secara efisien, mulai dari permintaan barang, pembelian, monitoring, mutasi, pemeliharaan, hingga penghapusan aset.

## ✨ Fitur Utama

### 📦 Manajemen Aset
- **Data Aset** - Pengelolaan data aset dengan informasi lengkap (nomor aset, nama, kategori, kondisi, harga, nilai buku, dll)
- **QR Code** - Generate dan print stiker QR Code untuk setiap aset
- **Status Tracking** - Lacak status aset (Active/Inactive) dan status mutasi

### 📝 Permintaan & Pembelian
- **Permintaan Barang** - Sistem pengajuan permintaan barang baru
- **Pembelian Barang** - Manajemen pembelian dengan integrasi ke permintaan

### 📊 Monitoring Aset
- **Scanner QR Code** - Scan QR Code aset menggunakan kamera untuk monitoring cepat
- **Riwayat Monitoring** - Catatan history monitoring kondisi aset
- **Alert System** - Peringatan untuk aset yang butuh perhatian (nilai buku habis, kondisi rusak, dll)

### 🔄 Mutasi Aset
- **Mutasi Keluar** - Aset dari gudang ke individu/pegawai
- **Mutasi Masuk** - Aset dari individu kembali ke gudang
- **Tracking Pemegang** - Lacak pemegang dan lokasi aset saat ini

### 🔧 Pemeliharaan Aset
- **Perbaikan** - Catat riwayat perbaikan aset
- **Jenis Service** - Perbaikan Ringan, Sedang, Berat, Perawatan Berkala
- **Biaya** - Tracking biaya pemeliharaan

### 🗑️ Penghapusan Aset
- **Soft Delete** - Aset yang dihapus tetap tersimpan sebagai arsip
- **Proses Penghapusan** - Dimusnahkan, Dijual, Dihibahkan, atau Dihapus dari Inventaris
- **Approval** - Sistem persetujuan penghapusan oleh pejabat berwenang

### 👥 Fitur Tambahan
- **Manajemen Pegawai** - Data pegawai dan departemen
- **Role & Permission** - Pengaturan hak akses dengan Filament Shield
- **Dashboard** - Statistik dan grafik kondisi aset
- **Multi-language** - Dukungan bahasa Indonesia

## 🛠️ Tech Stack

- **Backend:** Laravel 10.x
- **Admin Panel:** Filament 3.x
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **Authorization:** Spatie Permission + Filament Shield
- **Barcode:** milon/barcode (QR Code)
- **Styling:** Tailwind CSS

## 📋 Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL >= 5.7
- Docker (optional)

## 🚀 Instalasi

### Instalasi Manual

```bash
# Clone repository
git clone https://github.com/Fachrimardliana16/asset-management-app.git
cd asset-management-app

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database di .env
# DB_DATABASE=asset_management
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations dan seeder
php artisan migrate:fresh --seed

# Build assets
npm run build

# Run server
php artisan serve
```

### Instalasi dengan Docker

```bash
# Clone repository
git clone https://github.com/Fachrimardliana16/asset-management-app.git
cd asset-management-app

# Copy environment
cp .env.example .env

# Build dan run dengan Docker
docker-compose up -d --build

# Install dependencies di container
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed

# Build assets
docker-compose exec app npm install
docker-compose exec app npm run build
```

## 🔐 Login Default

Akses aplikasi di `/admin` dengan kredensial:

```
Email: superadmin@starter-kit.com
Password: password
```

## 📁 Struktur Menu

```
Asset
├── Permintaan Barang
├── Pembelian Barang
├── Data Aset
├── Monitoring Aset (Scanner)
├── Riwayat Monitoring
├── Mutasi Aset
├── Pemeliharaan Aset
└── Penghapusan Aset

Master Data
├── Kategori Aset
├── Kondisi Aset
├── Status Aset
├── Lokasi
└── Sub Lokasi

User Management
├── Users
├── Roles
└── Permissions

Settings
├── General Settings
└── Mail Settings
```

## 📷 Screenshots

*Coming soon*

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Fachri Mardliana**
- GitHub: [@Fachrimardliana16](https://github.com/Fachrimardliana16)

---

<p align="center">Made with ❤️ using Laravel & Filament</p>
