# 📦 Refactor Asset Request & Purchase ke Master-Detail Pattern

## 🎯 Ringkasan Perubahan

Sistem telah direfactor dari **single-item per permintaan** menjadi **multiple-items per permintaan** dengan:
- ✅ **Master-Detail Pattern**: 1 Request dapat memiliki banyak Items
- ✅ **Foto Per-Item**: Setiap item yang dibeli dapat memiliki foto berbeda
- ✅ **Beda Kategori & Lokasi**: Setiap item dalam 1 permintaan bisa berbeda kategori dan lokasi
- ✅ **Approval Global**: Approval tetap untuk seluruh permintaan, bukan per-item

---

## 📁 File yang Dibuat/Dimodifikasi

### **Database Migrations** ✅
1. `2024_12_17_000001_create_asset_request_items_table.php` - Tabel detail items
2. `2024_12_17_000002_modify_assets_requests_table_for_master_detail.php` - Refactor tabel master
3. `2024_12_17_000003_modify_asset_purchases_table_for_items.php` - Tambah relasi ke items

### **Models** ✅
1. `app/Models/AssetRequestItem.php` - Model baru untuk items (NEW)
2. `app/Models/AssetRequests.php` - Update relasi ke items
3. `app/Models/AssetPurchase.php` - Update relasi ke request items

### **Policies** ✅
1. `app/Policies/AssetRequestItemPolicy.php` - Policy untuk items (NEW)

### **Observers** ✅
1. `app/Observers/AssetRequestItemObserver.php` - Auto-update totals (NEW)
2. `app/Providers/AppServiceProvider.php` - Register observer

### **Resources** ✅
1. `app/Filament/Resources/AssetRequestsResource.php` - Form dengan Repeater untuk multiple items
2. `app/Filament/Resources/AssetPurchaseResource.php` - Form pembelian per-item dengan foto per-item

### **Seeders** ✅
1. `database/seeders/AssetRequestItemSeeder.php` - Sample data untuk testing (NEW)

---

## 🚀 Cara Menjalankan

### **1. Backup Database (PENTING!)**
```powershell
# Backup manual atau export via tools
mysqldump -u root -p nama_database > backup_before_refactor.sql
```

### **2. Fresh Migration dengan Seed**
```powershell
php artisan migrate:fresh --seed
```

### **3. (Opsional) Run Seeder Manual**
Jika ingin run seeder untuk asset requests saja:
```powershell
php artisan db:seed --class=AssetRequestItemSeeder
```

### **4. Clear Cache**
```powershell
php artisan optimize:clear
```

### **5. Test di Browser**
1. Buka menu **Permintaan Barang**
2. Klik **+ New** untuk membuat permintaan baru
3. Perhatikan form **Repeater** untuk tambah multiple items
4. Setelah approve, buka menu **Pembelian Barang**
5. Klik **Proses Pembelian** dan isi data per-item dengan foto masing-masing

---

## 🔄 Perubahan Struktur Database

### **Tabel `assets_requests` (Master)**
**DIHAPUS:**
- `asset_name` → Pindah ke `asset_request_items`
- `category_id` → Pindah ke `asset_request_items`
- `quantity` → Pindah ke `asset_request_items`
- `purpose` → Pindah ke `asset_request_items`
- `employee_id` → Pindah ke `asset_request_items`
- `location_id` → Pindah ke `asset_request_items`
- `sub_location_id` → Pindah ke `asset_request_items`

**DITAMBAH:**
- `total_items` (integer) - Jumlah jenis barang
- `total_quantity` (integer) - Total unit semua item
- `department_id` (uuid) - Department pemohon
- `requested_by` (uuid) - User pemohon

### **Tabel `asset_request_items` (Detail) - BARU**
| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary Key |
| `asset_request_id` | UUID | FK ke `assets_requests` |
| `asset_name` | VARCHAR | Nama barang |
| `category_id` | UUID | FK ke `master_assets_category` |
| `location_id` | UUID | FK ke `master_assets_location` |
| `sub_location_id` | UUID | FK ke `master_assets_sub_location` |
| `quantity` | INTEGER | Jumlah unit |
| `purpose` | VARCHAR | Keperluan item ini |
| `notes` | TEXT | Catatan item |

### **Tabel `asset_purchases`**
**DITAMBAH:**
- `asset_request_item_id` (uuid) - FK ke `asset_request_items`

---

## 💡 Cara Penggunaan

### **A. Membuat Permintaan Barang Baru**

1. Buka menu **Permintaan Barang** → **+ New**
2. Isi **Informasi Umum**:
   - Nomor DBP (auto-generate)
   - Tanggal Permintaan
   - Departemen
   - Pemohon
   - Keterangan Umum

3. Tambah **Daftar Barang**:
   - Klik **+ Tambah Barang**
   - Isi:
     - Nama Barang
     - Kategori
     - Jumlah Unit
     - Lokasi & Sub Lokasi
     - Keperluan
     - Catatan (opsional)
   - Ulangi untuk menambah item lain
   - Bisa drag-and-drop untuk reorder
   - Bisa clone item dengan tombol duplicate

4. **Pengesahan**:
   - Toggle approval (sama seperti sebelumnya)
   - Upload foto lampiran (GLOBAL untuk seluruh permintaan)

5. **Submit**
   - Sistem akan auto-calculate `total_items` dan `total_quantity`

### **B. Proses Pembelian**

1. Buka menu **Pembelian Barang**
2. Pilih permintaan yang sudah diapprove
3. Klik **Proses Pembelian**

4. Modal akan muncul dengan:
   - **Informasi Permintaan** (collapsible)
   - **Data Pembelian Global** (tanggal, sumber dana, kondisi, status)
   - **Section per-Item**:
     - Input **Merk/Tipe** per-item
     - Input **Harga Satuan** per-item
     - Input **Nilai Buku** per-item (opsional)
     - Upload **Foto Aset** per-item ⭐ **(BARU - PER ITEM!)**
     - Preview nomor aset yang akan di-generate

5. Klik **Simpan Semua Pembelian**
   - Sistem akan create:
     - N records di `asset_purchases` (N = total quantity)
     - N records di `assets` dengan nomor aset otomatis
     - Setiap aset akan punya foto sesuai item-nya

---

## 🔍 Fitur Baru

### **1. Repeater Form di Permintaan**
- ✅ Tambah/hapus item dinamis
- ✅ Reorder dengan drag-and-drop
- ✅ Clone item untuk duplikasi cepat
- ✅ Collapse/expand per-item
- ✅ Auto-calculate totals

### **2. Foto Per-Item di Pembelian**
- ✅ Setiap item bisa punya foto berbeda
- ✅ Upload foto saat proses pembelian
- ✅ Foto disimpan per-unit aset

### **3. Progress Tracking**
- ✅ Progress bar pembelian (X/Y unit)
- ✅ Status per-item: Pending, Partial, Complete
- ✅ Status permintaan: Pending, In Progress, Purchased

### **4. Validasi & Helpers**
- ✅ Auto-calculate total items & quantity
- ✅ Helper methods di model untuk cek status
- ✅ Observer untuk auto-update totals

---

## ⚠️ Breaking Changes

### **1. API / External Integration**
Jika ada external system yang hit API untuk create/read asset requests, perlu update:

**OLD:**
```json
{
  "document_number": "PR-202412-0001",
  "asset_name": "Laptop",
  "category_id": "uuid",
  "quantity": 5
}
```

**NEW:**
```json
{
  "document_number": "PR-202412-0001",
  "department_id": "uuid",
  "requested_by": "uuid",
  "items": [
    {
      "asset_name": "Laptop",
      "category_id": "uuid",
      "location_id": "uuid",
      "quantity": 5,
      "purpose": "..."
    }
  ]
}
```

### **2. Report/Export**
Jika ada report yang query langsung `assets_requests.asset_name`, perlu update query untuk join ke `asset_request_items`.

### **3. Custom Filament Pages**
Jika ada custom page yang akses field yang dihapus, perlu update.

---

## 🐛 Troubleshooting

### **Error: Column not found 'asset_name'**
**Solusi:** Jalankan `php artisan migrate:fresh --seed`

### **Error: Class AssetRequestItemObserver not found**
**Solusi:** Jalankan `composer dump-autoload`

### **Repeater items tidak muncul**
**Solusi:** 
1. Cek relasi di model `AssetRequests::items()`
2. Clear cache: `php artisan optimize:clear`

### **Foto tidak tersimpan**
**Solusi:**
1. Cek permission folder `storage/app/public/assets`
2. Jalankan `php artisan storage:link`

---

## 📊 Testing Checklist

- [ ] Buat permintaan dengan 1 item
- [ ] Buat permintaan dengan 3 items berbeda kategori
- [ ] Buat permintaan dengan 5 items berbeda lokasi
- [ ] Edit permintaan: tambah/hapus item
- [ ] Proses pembelian dengan foto per-item
- [ ] Cek nomor aset auto-generate
- [ ] Cek foto aset di detail asset
- [ ] Cek progress bar pembelian
- [ ] Cek status update otomatis
- [ ] Export/print invoice pembelian

---

## 📞 Support

Jika ada pertanyaan atau issue, silakan dokumentasikan di:
- Issue tracker internal
- Atau kontak developer

---

**Version:** 2.0 - Master-Detail Pattern  
**Date:** 17 Desember 2025  
**Author:** Development Team
