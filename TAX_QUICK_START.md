# Quick Start - Sistem Pajak Aset

## 🚀 Instalasi Cepat

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Seed Data Master
```bash
php artisan db:seed --class=TaxTypeSeeder
```

### 3. Generate Permissions
```bash
php artisan shield:generate --all
```

### 4. Setup Cron Job
Tambahkan ke crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📂 File-file yang Dibuat

### Models
- `app/Models/MasterTaxType.php`
- `app/Models/AssetTax.php`

### Policies
- `app/Policies/MasterTaxTypePolicy.php`
- `app/Policies/AssetTaxPolicy.php`

### Filament Resources
- `app/Filament/Resources/MasterTaxTypeResource.php`
- `app/Filament/Resources/AssetTaxResource.php`

### Widgets
- `app/Filament/Widgets/TaxStatsOverview.php`
- `app/Filament/Widgets/UpcomingTaxesWidget.php`
- `app/Filament/Widgets/OverdueTaxesWidget.php`

### Services
- `app/Services/TaxPenaltyService.php`

### Notifications
- `app/Notifications/TaxReminderNotification.php`
- `app/Notifications/TaxOverdueNotification.php`
- `app/Notifications/TaxApprovalNotification.php`

### Commands
- `app/Console/Commands/SendTaxReminders.php`
- `app/Console/Commands/UpdateTaxPenalties.php`

### Export/Import
- `app/Filament/Exports/AssetTaxExporter.php`
- `app/Filament/Imports/AssetTaxImporter.php`

### Migrations
- `database/migrations/2024_12_24_000001_create_master_tax_types_table.php`
- `database/migrations/2024_12_24_000002_create_asset_taxes_table.php`

### Seeders
- `database/seeders/TaxTypeSeeder.php`

---

## ⚡ Menu yang Tersedia

### Master Data
- **Jenis Pajak** - Kelola jenis-jenis pajak

### Manajemen Aset
- **Pajak Aset** - Kelola transaksi pembayaran pajak

### Dashboard Widgets
- Statistik pajak
- Pajak akan jatuh tempo
- Pajak terlambat

---

## 🎯 Fitur Utama

✅ Master data jenis pajak (PKB, BPKB, PBB, IMB, dll)
✅ Transaksi pembayaran pajak dengan upload bukti
✅ Approval workflow (Pending → Approved/Rejected)
✅ Kalkulasi denda otomatis (% per hari/bulan atau nominal tetap)
✅ Notifikasi reminder sebelum jatuh tempo
✅ Dashboard widget untuk monitoring
✅ Export data ke Excel
✅ Import bulk data dari Excel
✅ Scheduled commands untuk auto-update denda & reminder

---

## 📋 TODO Selanjutnya

1. **Assign Permissions ke Role**
   - Buka Shield → Roles
   - Centang permissions untuk Master Tax Type dan Asset Tax

2. **Setup Cron (Production)**
   - Pastikan cron job sudah berjalan untuk scheduled commands

3. **Test Fitur**
   - Buat jenis pajak baru
   - Tambah data pajak ke aset
   - Test approval workflow
   - Test notifikasi
   - Test export/import

4. **Customize (Opsional)**
   - Sesuaikan jenis pajak sesuai kebutuhan
   - Atur reminder days per jenis pajak
   - Sesuaikan persentase/nilai denda

---

## 📖 Dokumentasi Lengkap

Lihat `TAX_MANAGEMENT_GUIDE.md` untuk dokumentasi lengkap.

---

## 🆘 Commands

```bash
# Update denda manual
php artisan tax:update-penalties

# Kirim reminder manual
php artisan tax:send-reminders

# Generate permissions
php artisan shield:generate --all
```
