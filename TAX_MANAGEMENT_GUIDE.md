# Dokumentasi Sistem Pajak Aset

## 📋 Overview

Sistem ini mengelola pajak aset untuk kendaraan, tanah, dan bangunan dengan fitur:
- Master data jenis pajak
- Transaksi pembayaran pajak
- Approval workflow
- Kalkulasi denda otomatis
- Notifikasi reminder
- Export/Import data
- Dashboard widget

---

## 🗂️ Struktur Database

### Tabel: `master_tax_types`
Master data jenis pajak (PKB, BPKB, PBB, IMB, dll)

**Kolom Penting:**
- `period_type`: yearly, 5yearly, custom
- `has_penalty`: boolean untuk mengaktifkan denda
- `penalty_type`: percentage atau fixed
- `penalty_period`: daily atau monthly (untuk percentage)
- `reminder_days`: hari sebelum jatuh tempo untuk kirim notifikasi

### Tabel: `asset_taxes`
Data transaksi pajak per aset

**Kolom Penting:**
- `payment_status`: pending, paid, overdue, cancelled
- `approval_status`: pending, approved, rejected
- `penalty_amount`: jumlah denda (auto-calculated)
- `overdue_days`: jumlah hari keterlambatan

**Relasi:**
- `asset_id` → assets
- `tax_type_id` → master_tax_types

---

## 🚀 Cara Implementasi

### 1. Jalankan Migration

```bash
php artisan migrate
```

File migration yang dibuat:
- `2024_12_24_000001_create_master_tax_types_table.php`
- `2024_12_24_000002_create_asset_taxes_table.php`

### 2. Seed Data Master Jenis Pajak

```bash
php artisan db:seed --class=TaxTypeSeeder
```

Data yang akan dibuat:
- PKB (Pajak Kendaraan Bermotor)
- BPKB (Bea Balik Nama)
- SWDKLLJ (Sumbangan Wajib)
- PBB (Pajak Bumi dan Bangunan)
- IMB (Izin Mendirikan Bangunan)
- BPHTB (Bea Perolehan Hak)

### 3. Generate Permissions (Shield)

```bash
php artisan shield:generate --all
```

Permissions yang dibuat:
- `view_any_master::tax::type`
- `create_master::tax::type`
- `update_master::tax::type`
- `delete_master::tax::type`
- `view_any_asset::tax`
- `create_asset::tax`
- `update_asset::tax`
- `delete_asset::tax`
- `approve_asset::tax`
- `reject_asset::tax`
- `verify_asset::tax`
- `export_asset::tax`
- `import_asset::tax`

### 4. Setup Scheduled Commands

Tambahkan di `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Update denda setiap hari jam 00:30
    $schedule->command('tax:update-penalties')
        ->dailyAt('00:30');
    
    // Kirim reminder setiap hari jam 08:00
    $schedule->command('tax:send-reminders')
        ->dailyAt('08:00');
}
```

Atau jalankan manual:
```bash
# Update denda
php artisan tax:update-penalties

# Kirim reminder
php artisan tax:send-reminders
```

---

## 📊 Fitur-Fitur

### 1. Dashboard Widgets

**TaxStatsOverview** - Statistik pajak
- Pajak akan jatuh tempo (30 hari)
- Pajak terlambat
- Menunggu approval
- Total belum dibayar
- Total denda

**UpcomingTaxesWidget** - Tabel pajak akan jatuh tempo

**OverdueTaxesWidget** - Tabel pajak terlambat

### 2. Approval Workflow

**Alur:**
1. User membuat data pajak → status `pending approval`
2. Approver menyetujui/menolak
3. Jika disetujui → bisa dilakukan pembayaran
4. Setelah dibayar → status `paid`

**Actions:**
- Approve: Menyetujui pembayaran pajak
- Reject: Menolak dengan alasan
- Verify: Verifikasi pembayaran (opsional)

### 3. Kalkulasi Denda Otomatis

**Cara Kerja:**
```php
// Di Model AssetTax
$penalty = $tax->calculatePenalty();
// Returns: ['penalty_amount' => 150000, 'overdue_days' => 30, 'calculation' => 'Rp 5.000.000 × 2% × 1 bulan = Rp 150.000']

// Update penalty
$tax->updatePenalty();
```

**Jenis Denda:**
- **Percentage + Daily**: Denda per hari (misal: 0.1% × nilai pajak × hari)
- **Percentage + Monthly**: Denda per bulan (misal: 2% × nilai pajak × bulan)
- **Fixed**: Denda nominal tetap (misal: Rp 50.000 per bulan)

### 4. Notifikasi

**TaxReminderNotification** - Reminder sebelum jatuh tempo
- Dikirim X hari sebelum due date (sesuai `reminder_days` di tax type)
- Dikirim ke admin dan PIC aset

**TaxOverdueNotification** - Notifikasi pajak terlambat
- Dikirim saat pajak melewati due date
- Menampilkan nilai denda

**TaxApprovalNotification** - Notifikasi status approval
- Dikirim saat pending, approved, atau rejected

### 5. Export & Import

**Export:**
- Format Excel (XLSX)
- Semua data pajak dengan detail lengkap
- Bisa filter per periode, status, dll

**Import:**
- Upload Excel untuk bulk create pajak
- Template kolom:
  - Kode Aset
  - Kode Jenis Pajak
  - Tahun Pajak
  - Nilai Pajak
  - Tanggal Jatuh Tempo
  - Tanggal Pembayaran (opsional)
  - Status Pembayaran (opsional)
  - Catatan (opsional)

---

## 🔧 Service Classes

### TaxPenaltyService

```php
use App\Services\TaxPenaltyService;

$service = app(TaxPenaltyService::class);

// Update semua denda yang overdue
$service->updateOverduePenalties();

// Update status overdue
$service->updateOverdueStatus();

// Get laporan denda
$report = $service->getPenaltyReport('2024-01-01', '2024-12-31');

// Proses pembayaran
$service->processTaxPayment($tax, [
    'payment_date' => now(),
    'notes' => 'Pembayaran via transfer'
]);

// Generate pajak tahun depan otomatis
$nextYearTax = $service->generateNextYearTax($currentTax);
```

---

## 📱 Cara Penggunaan

### Menambah Jenis Pajak Baru

1. Buka menu **Master Data → Jenis Pajak**
2. Klik **Create**
3. Isi form:
   - Nama: PKB (Pajak Kendaraan Bermotor)
   - Kode: PKB
   - Kategori Aset: Kendaraan
   - Tipe Periode: Tahunan
   - Denda: Ya
   - Tipe Denda: Persentase
   - Nilai Denda: 2%
   - Periode Perhitungan: Per Bulan
   - Reminder: 30 hari

### Mencatat Pembayaran Pajak

1. Buka menu **Manajemen Aset → Pajak Aset**
2. Klik **Create**
3. Isi form:
   - Pilih Aset
   - Pilih Jenis Pajak (otomatis filter sesuai kategori aset)
   - Tahun Pajak
   - Nilai Pajak
   - Tanggal Jatuh Tempo
4. Upload bukti pembayaran
5. Simpan → Status: **Menunggu Approval**

### Approval Pajak

1. Buka tab **Menunggu Approval**
2. Klik action menu (3 titik) pada row
3. Pilih **Setujui** atau **Tolak**
4. Jika tolak, isi alasan penolakan

### Monitoring Pajak Jatuh Tempo

1. Lihat widget di Dashboard
2. Atau buka menu **Pajak Aset** → Tab **Akan Jatuh Tempo**
3. Klik **View** untuk detail
4. Proses pembayaran jika belum

---

## ⚙️ Konfigurasi Tambahan

### Custom Periode Pajak

Jika ada jenis pajak dengan periode khusus (misal: 3 tahunan):
1. Pilih **Tipe Periode**: Custom
2. Isi **Periode (Bulan)**: 36

### Custom Denda

**Contoh 1: Denda 0.1% per hari**
- Tipe Denda: Persentase
- Nilai Denda: 0.1
- Periode Perhitungan: Per Hari

**Contoh 2: Denda Rp 50.000 flat**
- Tipe Denda: Nominal Tetap
- Nilai Denda: 50000

### Disable Denda

Set **Memiliki Denda**: Off

---

## 🐛 Troubleshooting

### Denda tidak terupdate otomatis
```bash
# Manual update
php artisan tax:update-penalties
```

### Notifikasi tidak terkirim
```bash
# Manual kirim reminder
php artisan tax:send-reminders

# Check queue
php artisan queue:work
```

### Permission error
```bash
# Regenerate permissions
php artisan shield:generate --all

# Assign ke role
# Melalui panel admin → Shield → Roles → Edit Role → Centang permissions
```

---

## 📝 Notes

1. **Denda dihitung otomatis** saat:
   - Data pajak disimpan/diupdate
   - Command `tax:update-penalties` dijalankan
   - Action "Update Denda" diklik

2. **Status `overdue` diupdate otomatis** saat:
   - Data pajak disimpan dan due_date < today
   - Command `tax:update-penalties` dijalankan

3. **Notifikasi dikirim** saat:
   - Command `tax:send-reminders` dijalankan (scheduled daily)
   - Approval diproses

4. **Best Practice**:
   - Setup cron untuk scheduled commands
   - Backup database secara berkala
   - Export data pajak per bulan untuk laporan
   - Review permission secara berkala

---

## 🔐 Security

- Approval memerlukan permission khusus: `approve_asset::tax`
- Export/Import memerlukan permission: `export_asset::tax` dan `import_asset::tax`
- Soft delete untuk audit trail
- Activity log terintegrasi (jika sudah setup)

---

## 📞 Support

Jika ada pertanyaan atau issues, hubungi IT Support.
