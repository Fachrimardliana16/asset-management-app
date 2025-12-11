# 🔧 Troubleshooting: Gambar Upload Tidak Tampil

## 📋 Masalah yang Ditemukan

Gambar yang di-upload tidak dapat ditampilkan meskipun sudah menjalankan `php artisan storage:link`.

## 🔍 Penyebab Masalah

### 1. **Inkonsistensi Nama Folder**
   - Beberapa resource menggunakan folder dengan huruf **BESAR** (misal: `Assets`, `Maintenance`, `Disposals`)
   - Folder lain menggunakan huruf **kecil** (misal: `assets`, `bukti-permintaan`)
   - Hal ini menyebabkan konflik path saat file disimpan dan ditampilkan

### 2. **Missing Disk Configuration**
   - FileUpload component tidak secara eksplisit menentukan disk `public`
   - Menyebabkan file bisa tersimpan di lokasi yang tidak sesuai

### 3. **Path Rendering Issue**
   - Custom HTML rendering (seperti di `MonitoringAsetScanner.php`) menggunakan `asset('storage/' . $path)`
   - Path ini bergantung pada konsistensi nama folder

## ✅ Solusi yang Diterapkan

### 1. **Standardisasi Nama Folder**
Semua folder upload diubah menjadi huruf kecil dan menggunakan dash (`-`) untuk spasi:

**Sebelum:**
```
storage/app/public/
├── Assets/
├── Mutation Assets/
├── Maintenance/
├── Disposals/
└── bukti-permintaan/
```

**Sesudah:**
```
storage/app/public/
├── assets/
├── mutation-assets/
├── maintenance/
├── disposals/
└── bukti-permintaan/
```

### 2. **Update Konfigurasi FileUpload**

**File yang diubah:**

#### `app/Filament/Resources/AssetResource.php`
```php
// SEBELUM
Forms\Components\FileUpload::make('img')
    ->directory('Assets')
    ->label('Gambar'),

// SESUDAH
Forms\Components\FileUpload::make('img')
    ->directory('assets')
    ->image()
    ->disk('public')
    ->visibility('public')
    ->label('Gambar'),
```

#### `app/Filament/Resources/AssetMutationResource.php`
```php
// SEBELUM
Forms\Components\FileUpload::make('scan_doc')
    ->directory('Mutation Assets')

// SESUDAH
Forms\Components\FileUpload::make('scan_doc')
    ->directory('mutation-assets')
    ->disk('public')
```

#### `app/Filament/Resources/AssetMaintenanceResource.php`
```php
// SEBELUM
Forms\Components\FileUpload::make('invoice_file')
    ->directory('Maintenance')

// SESUDAH
Forms\Components\FileUpload::make('invoice_file')
    ->directory('maintenance')
    ->disk('public')
```

#### `app/Filament/Resources/AssetDisposalResource.php`
```php
// SEBELUM
Forms\Components\FileUpload::make('docs')
    ->directory('Disposals')

// SESUDAH
Forms\Components\FileUpload::make('docs')
    ->directory('disposals')
    ->disk('public')
```

### 3. **Verifikasi Symbolic Link**

Pastikan symbolic link sudah benar:
```powershell
# Cek link
Get-Item "public\storage" | Select-Object LinkType, Target

# Output yang benar:
# LinkType: Junction
# Target: {D:\...\storage\app\public}
```

## 🚀 Cara Testing

### 1. Upload Gambar Baru
   - Buka halaman Asset
   - Upload gambar baru
   - Periksa apakah tersimpan di `storage/app/public/assets/`

### 2. Tampilkan Gambar
   - Buka halaman Monitoring Aset Scanner
   - Scan barcode aset yang memiliki gambar
   - Pastikan gambar ditampilkan dengan benar

### 3. Verifikasi Path
   - Periksa browser DevTools (F12) → Network
   - Lihat path gambar yang diminta: `http://localhost:8000/storage/assets/namafile.jpg`
   - Status harus 200 (OK), bukan 404 (Not Found)

## 📝 Catatan Penting

### Jika Upload Gambar Baru
- Gunakan huruf kecil untuk semua nama folder
- Selalu gunakan `->disk('public')` pada FileUpload component
- Gunakan `->visibility('public')` untuk file yang perlu diakses publik

### Jika Menampilkan Gambar
- Untuk ImageColumn (Filament Table): Path otomatis ditangani
- Untuk Custom HTML: Gunakan `asset('storage/' . $path)` dimana `$path` sudah termasuk folder

### Struktur Path yang Benar
```
Database Field: assets/01KC3KEJDYYPW1YP7KVX4JKH2H.jpeg
Physical Path:  storage/app/public/assets/01KC3KEJDYYPW1YP7KVX4JKH2H.jpeg
Public URL:     http://localhost:8000/storage/assets/01KC3KEJDYYPW1YP7KVX4JKH2H.jpeg
```

## ⚠️ Migration Data Lama

Jika ada gambar lama yang tersimpan dengan path folder huruf besar, perlu dilakukan update database:

```sql
-- Update path gambar di tabel assets
UPDATE assets 
SET img = REPLACE(REPLACE(img, 'Assets/', 'assets/'), 'Assets\\', 'assets/')
WHERE img LIKE 'Assets/%' OR img LIKE 'Assets\\%';

-- Update path dokumen di tabel mutations
UPDATE assets_mutations 
SET scan_doc = REPLACE(REPLACE(scan_doc, 'Mutation Assets/', 'mutation-assets/'), 'Mutation Assets\\', 'mutation-assets/')
WHERE scan_doc LIKE 'Mutation Assets/%' OR scan_doc LIKE 'Mutation Assets\\%';

-- Update path dokumen di tabel maintenance
UPDATE assets_maintenances 
SET invoice_file = REPLACE(REPLACE(invoice_file, 'Maintenance/', 'maintenance/'), 'Maintenance\\', 'maintenance/')
WHERE invoice_file LIKE 'Maintenance/%' OR invoice_file LIKE 'Maintenance\\%';

-- Update path dokumen di tabel disposals
UPDATE assets_disposals 
SET docs = REPLACE(REPLACE(docs, 'Disposals/', 'disposals/'), 'Disposals\\', 'disposals/')
WHERE docs LIKE 'Disposals/%' OR docs LIKE 'Disposals\\%';
```

## 📌 Checklist Verifikasi

- [x] Symbolic link dibuat dengan `php artisan storage:link`
- [x] Folder storage dengan huruf kecil sudah dibuat
- [x] FileUpload components sudah update dengan `->disk('public')`
- [x] Nama folder sudah konsisten (huruf kecil)
- [ ] Database path sudah diupdate (jika ada data lama)
- [ ] Testing upload dan tampil gambar berhasil

## 🔗 File Terkait

- `config/filesystems.php` - Konfigurasi disk storage
- `app/Filament/Resources/AssetResource.php` - Upload gambar aset
- `app/Filament/Resources/AssetPurchaseResource.php` - Upload gambar pembelian
- `app/Filament/Resources/AssetRequestsResource.php` - Upload bukti permintaan
- `app/Filament/Resources/AssetMutationResource.php` - Upload dokumen mutasi
- `app/Filament/Resources/AssetMaintenanceResource.php` - Upload invoice maintenance
- `app/Filament/Resources/AssetDisposalResource.php` - Upload dokumen disposal
- `app/Filament/Pages/MonitoringAsetScanner.php` - Tampilan gambar scanner

---
**Tanggal Perbaikan:** 10 Desember 2025  
**Status:** ✅ Resolved
