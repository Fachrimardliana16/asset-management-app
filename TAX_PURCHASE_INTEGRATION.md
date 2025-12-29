# Integrasi Input Pajak saat Proses Pembelian Aset

## 📋 Ringkasan
Fitur ini memungkinkan pengguna untuk mencatat data pajak aset secara langsung saat melakukan proses pembelian aset, sehingga data pajak langsung tercatat tanpa perlu input manual terpisah.

## ✨ Fitur yang Ditambahkan

### 1. Section "Data Pajak" di Form Pembelian
- **Lokasi**: Ditambahkan di setiap step item pembelian
- **Posisi**: Setelah repeater detail unit aset, sebelum preview nomor aset
- **Properti**:
  - Collapsible dan collapsed by default
  - Hanya muncul jika kategori aset memiliki jenis pajak terkait
  - Icon: `heroicon-o-document-text`

### 2. Toggle "Catat pajak sekarang?"
- **Fungsi**: Mengaktifkan/menonaktifkan input pajak
- **Default**: `false` (tidak aktif)
- **Live**: Ya, bereaksi real-time

### 3. Repeater Daftar Pajak
- **Visible**: Hanya jika toggle aktif
- **Support**: Multiple pajak untuk satu aset
- **Fields**:

#### a. Jenis Pajak (Select)
- Filter otomatis berdasarkan `category_id` item
- Hanya tampilkan tax types yang `is_active = true`
- Searchable dan preload
- Required field

#### b. Nilai Pajak (TextInput)
- Numeric dengan prefix "Rp"
- Minimum value: 1
- Required field

#### c. Tanggal Jatuh Tempo (DatePicker)
- Format: `d/m/Y`
- Minimum date: hari ini
- Required field

#### d. Catatan (Textarea)
- Optional field
- Max length: 500 karakter
- Rows: 2

### 4. Item Label
- Menampilkan nama jenis pajak yang dipilih
- Fallback: "Pajak Baru" jika belum dipilih

## 🔧 Perubahan Kode

### File: `ProcessPurchase.php`

#### Import Tambahan
```php
use App\Models\MasterTaxType;
```

#### Schema Form (Baris ~280)
```php
// Section untuk input pajak (opsional)
Forms\Components\Section::make('Data Pajak')
    ->description('Opsional - Catat pajak aset jika ada')
    ->icon('heroicon-o-document-text')
    ->collapsible()
    ->collapsed(true)
    ->schema([
        // Toggle dan Repeater fields
    ])
    ->visible(function () use ($item) {
        // Only show if category has tax types
        return MasterTaxType::where('category_id', $item->category_id)
            ->where('is_active', true)
            ->exists();
    })
    ->columnSpanFull(),
```

#### Submit Method (Baris ~509)
```php
// 3. Simpan pajak jika ada (hanya untuk unit pertama per item)
if ($i === 1 && isset($itemData['has_taxes']) && $itemData['has_taxes'] === true) {
    $taxes = $itemData['taxes'] ?? [];
    foreach ($taxes as $taxData) {
        \App\Models\AssetTax::create([
            'asset_id' => $createdAsset->id,
            'tax_type_id' => $taxData['tax_type_id'],
            'tax_amount' => $taxData['tax_amount'],
            'due_date' => $taxData['due_date'],
            'payment_status' => 'unpaid',
            'approval_status' => 'pending',
            'penalty_amount' => 0,
            'notes' => $taxData['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
```

## 🎯 Logika Bisnis

### 1. Visibility Rules
- Section pajak **hanya muncul** jika:
  - Kategori aset memiliki jenis pajak terkait di `master_tax_types`
  - Tax type tersebut `is_active = true`

### 2. Data Filtering
- Dropdown jenis pajak **otomatis difilter** berdasarkan `category_id` dari item yang sedang diproses
- Ini memastikan pengguna hanya dapat memilih jenis pajak yang relevan dengan kategori aset

### 3. Tax Creation Logic
- Pajak hanya dibuat untuk **unit pertama** (`$i === 1`) dari setiap item
- Alasan: Menghindari duplikasi jika satu item memiliki multiple units
- Status default:
  - `payment_status`: `'unpaid'`
  - `approval_status`: `'pending'`
  - `penalty_amount`: `0`

### 4. Optional Feature
- Fitur ini sepenuhnya **opsional**
- Pengguna dapat melewati section ini tanpa mengisi apapun
- Proses pembelian tetap berjalan normal tanpa data pajak

## 📝 Cara Penggunaan

### Skenario 1: Input Pajak Saat Pembelian
1. Akses menu "Proses Pembelian"
2. Pilih request yang akan diproses
3. Di setiap step item, scroll ke section "Data Pajak"
4. Jika tidak muncul → kategori aset tidak memiliki jenis pajak
5. Aktifkan toggle "Catat pajak sekarang?"
6. Klik "+ Tambah Pajak Lain" untuk menambah pajak
7. Isi form:
   - Pilih jenis pajak (otomatis filtered by category)
   - Input nilai pajak
   - Pilih tanggal jatuh tempo
   - (Opsional) tambahkan catatan
8. Ulangi untuk multiple pajak jika perlu
9. Lanjutkan proses pembelian seperti biasa
10. Submit → data pajak otomatis tercatat di `asset_taxes`

### Skenario 2: Skip Input Pajak
1-3. (sama seperti Skenario 1)
4. Jangan aktifkan toggle "Catat pajak sekarang?"
5. Atau aktifkan tapi biarkan repeater kosong
6. Lanjutkan ke step berikutnya
7. Submit → aset dibuat tanpa data pajak

## 🔍 Validasi & Edge Cases

### 1. Kategori Tanpa Tax Types
- Section pajak **tidak akan muncul** sama sekali
- Tidak ada error, UI tetap clean

### 2. Multiple Units per Item
- Pajak hanya dibuat untuk unit pertama
- Ini sesuai dengan konsep: pajak berlaku untuk aset secara keseluruhan, bukan per unit

### 3. Multiple Pajak untuk Satu Aset
- **Didukung penuh** melalui repeater
- Contoh: Kendaraan bisa punya PKB + SWDKLLJ + BPKB sekaligus

### 4. Toggle OFF tapi Ada Data di Repeater
- Data tidak akan diproses
- Hanya diproses jika `has_taxes === true`

### 5. Required Fields
- Jika toggle aktif dan repeater terbuka, semua field required harus diisi
- Filament akan otomatis validasi sebelum submit

## 🎨 UX/UI Benefits

### 1. Efficiency
- ✅ Satu kali proses: beli aset + catat pajak
- ✅ Tidak perlu buka menu terpisah untuk input pajak
- ✅ Workflow lebih streamlined

### 2. Contextual
- ✅ Data pajak langsung terkait dengan aset yang dibuat
- ✅ Auto-filter jenis pajak sesuai kategori
- ✅ Tidak ada manual matching asset_id

### 3. Flexibility
- ✅ Sepenuhnya opsional
- ✅ Collapsed by default (tidak mengganggu)
- ✅ Bisa input multiple pajak sekaligus

### 4. Safety
- ✅ Validation otomatis
- ✅ Conditional visibility (hanya muncul jika relevant)
- ✅ Status pending by default untuk approval workflow

## 🔗 Integrasi dengan Sistem Lain

### 1. Asset Tax Management
- Data yang dibuat akan muncul di **AssetTaxResource**
- Status: `pending` → perlu approval dari approver
- Dapat di-approve/reject melalui table actions

### 2. Dashboard Widgets
- Pajak yang dibuat akan muncul di:
  - **UpcomingTaxesWidget** (jika due date < 30 hari)
  - **TaxStatsOverview** (summary statistics)

### 3. Notification System
- Jika due date mendekat → **TaxReminderNotification**
- Jika overdue → **TaxOverdueNotification**
- Jika perlu approval → **TaxApprovalNotification**

### 4. Automatic Penalties
- Command `UpdateTaxPenalties` akan otomatis hitung denda
- Berdasarkan konfigurasi di `MasterTaxType`

## 🧪 Testing Checklist

### Manual Testing
- [ ] Section pajak muncul untuk kategori kendaraan
- [ ] Section pajak muncul untuk kategori tanah/bangunan
- [ ] Section pajak **tidak muncul** untuk kategori elektronik (jika tidak punya tax type)
- [ ] Toggle berfungsi show/hide repeater
- [ ] Dropdown jenis pajak terfilter sesuai kategori
- [ ] Dapat menambah multiple pajak
- [ ] Validation required fields
- [ ] Submit tanpa aktifkan toggle → no error
- [ ] Submit dengan pajak → data tercatat di `asset_taxes`
- [ ] Item label menampilkan nama pajak yang benar
- [ ] Data pajak hanya dibuat untuk unit pertama (jika qty > 1)

### Database Verification
```sql
-- Cek apakah pajak tercatat
SELECT 
    at.id,
    a.assets_number,
    a.name as asset_name,
    mtt.name as tax_type,
    at.tax_amount,
    at.due_date,
    at.payment_status,
    at.approval_status
FROM asset_taxes at
JOIN assets a ON at.asset_id = a.id
JOIN master_tax_types mtt ON at.tax_type_id = mtt.id
WHERE at.created_at >= CURDATE()
ORDER BY at.created_at DESC;
```

## 📚 Dependencies

### Models Required
- ✅ `MasterTaxType` (untuk options dan filtering)
- ✅ `AssetTax` (untuk create records)
- ✅ `Asset` (untuk relasi)

### Database Tables
- ✅ `master_tax_types` (kategori, jenis pajak, periode)
- ✅ `asset_taxes` (transaksi pajak)
- ✅ `assets` (aset yang dibeli)

## 🚀 Future Enhancements

### Potential Improvements
1. **Auto-calculate Next Due Date**
   - Berdasarkan `period_type` di `MasterTaxType`
   - Otomatis suggest due date untuk pajak tahunan/5 tahunan

2. **Bulk Tax Entry**
   - Jika beli banyak unit sejenis → terapkan pajak yang sama ke semua unit
   - Toggle: "Terapkan ke semua unit"

3. **Tax Amount Suggestions**
   - Load nilai pajak dari record sebelumnya (jika ada)
   - "Last paid amount: Rp XXX"

4. **Attachment Upload**
   - Upload dokumen pajak langsung saat input
   - Mis: foto STNK, BPKB, IMB, dsb

5. **Reminder Settings**
   - Pilih kapan ingin diingatkan (30/60/90 hari sebelum due)
   - Per-pajak reminder preferences

## ⚠️ Important Notes

### 1. Unit Pertama Only
- Pajak dibuat hanya untuk **unit pertama** (`$i === 1`)
- Jika perlu pajak per unit → need modification

### 2. Status Default
- Semua pajak default: `unpaid` + `pending`
- Perlu approval sebelum masuk ke monitoring aktif

### 3. Category Dependency
- **Harus ada** tax types untuk kategori tersebut
- Jika tidak ada → section tidak muncul

### 4. Data Integrity
- `asset_id` di-assign dari `$createdAsset->id`
- Ensures proper relation antara aset dan pajak

## 📞 Support & Troubleshooting

### Issue: Section pajak tidak muncul
**Solusi**: 
- Cek apakah kategori memiliki tax types di `master_tax_types`
- Pastikan tax type `is_active = true`
- Run seeder: `php artisan db:seed --class=TaxTypeSeeder`

### Issue: Dropdown jenis pajak kosong
**Solusi**:
- Cek filter: `category_id` harus match
- Pastikan ada tax types dengan `is_active = true`

### Issue: Data pajak tidak tersimpan
**Solusi**:
- Cek apakah toggle `has_taxes` aktif
- Verifikasi semua required fields terisi
- Check log error di `storage/logs/laravel.log`

### Issue: Error foreign key constraint
**Solusi**:
- Pastikan migration `asset_taxes` sudah dijalankan
- Verifikasi `asset_id` exists di table `assets`

---

## 📅 Changelog

### v1.0.0 (2024-12-24)
- ✅ Initial implementation
- ✅ Tax input section in purchase wizard
- ✅ Auto-filter tax types by category
- ✅ Multiple tax support via repeater
- ✅ Optional feature with toggle
- ✅ Create AssetTax records on submit

---

**Dokumentasi ini dibuat**: 24 Desember 2024  
**Approach**: Simple & Practical (Approach 1)  
**Status**: ✅ Completed & Ready for Testing
