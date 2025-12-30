# Testing Guide: Tax Integration in Purchase Process

## 🧪 Pre-Testing Checklist

### 1. Database Requirements
```bash
# Pastikan migrations sudah dijalankan
php artisan migrate

# Pastikan tax types sudah di-seed
php artisan db:seed --class=TaxTypeSeeder
```

### 2. Verify Tax Types Data
```sql
-- Check tax types per category
SELECT 
    mtt.id,
    mtt.name as tax_name,
    mac.name as category_name,
    mtt.period_type,
    mtt.is_active
FROM master_tax_types mtt
LEFT JOIN master_assets_category mac ON mtt.category_id = mac.id
WHERE mtt.is_active = true
ORDER BY mac.name, mtt.name;
```

Expected result:
- Kendaraan: PKB, BPKB, SWDKLLJ
- Tanah/Bangunan: PBB, IMB, BPHTB

### 3. Create Test Data
```sql
-- Buat asset request untuk testing
INSERT INTO asset_requests (...) VALUES (...);
```

---

## 📋 Test Cases

### TEST CASE 1: Kategori DENGAN Tax Types (Kendaraan)
**Objective**: Verify section pajak muncul dan berfungsi untuk kategori kendaraan

#### Steps:
1. Login sebagai user dengan permission `purchase_asset`
2. Navigate: **Pembelian Aset** → **Proses Pembelian**
3. Pilih request dengan kategori **Kendaraan**
4. Isi Step 1 (Informasi Umum):
   - Tanggal Pembelian: Today
   - Kondisi: Baik
   - Status: Aktif
   - Sumber Dana: APBN
   - Masa Berlaku: 2029-12-31
5. Klik **Next** → Step 2 (Detail Item)
6. Scroll ke bawah setelah "Detail Per Unit Aset"

#### Expected Result:
✅ Section **"Data Pajak"** muncul  
✅ Icon: document-text  
✅ Description: "Opsional - Catat pajak aset jika ada"  
✅ Status: Collapsed (default)

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 2: Toggle Functionality
**Objective**: Verify toggle show/hide repeater

#### Steps:
1. (Lanjutan dari TEST CASE 1)
2. Klik section **"Data Pajak"** untuk expand
3. Observe toggle "Catat pajak sekarang?"

#### Expected Result - Before Toggle:
✅ Toggle: OFF (default)  
✅ Repeater: HIDDEN  
✅ Helper text visible

#### Steps (continued):
4. Aktifkan toggle (click to ON)

#### Expected Result - After Toggle:
✅ Toggle: ON  
✅ Repeater **"Daftar Pajak"**: VISIBLE  
✅ Button "+ Tambah Pajak Lain" visible  
✅ Helper text: "Anda dapat menambahkan lebih dari satu jenis pajak"

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 3: Tax Type Filtering
**Objective**: Verify dropdown hanya menampilkan tax types sesuai kategori

#### Steps:
1. (Lanjutan dari TEST CASE 2 - toggle ON)
2. Klik "+ Tambah Pajak Lain" (atau buka item pertama jika sudah ada)
3. Klik dropdown "Jenis Pajak"

#### Expected Result:
✅ Dropdown muncul  
✅ Options yang tampil:
   - PKB (Pajak Kendaraan Bermotor)
   - BPKB (Pajak Buku Kendaraan)
   - SWDKLLJ (Asuransi Kecelakaan)  
✅ NOT showing: PBB, IMB, BPHTB (karena bukan untuk kendaraan)

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 4: Single Tax Input
**Objective**: Input satu jenis pajak dan verify data tersimpan

#### Steps:
1. (Lanjutan dari TEST CASE 3)
2. Pilih "PKB" dari dropdown
3. Input data:
   - Nilai Pajak: `2500000`
   - Tanggal Jatuh Tempo: `31/01/2025`
   - Catatan: `Pajak PKB tahun 2025`
4. Fill unit details:
   - Merk: `Toyota Avanza`
   - Harga: `200000000`
   - Upload foto
5. Klik **Next** → Review
6. Klik **Simpan Semua Pembelian**

#### Expected Result:
✅ Success notification  
✅ Redirect ke index page

#### Verify in Database:
```sql
-- Check asset created
SELECT * FROM assets 
WHERE name LIKE '%Avanza%' 
ORDER BY created_at DESC LIMIT 1;

-- Get asset ID
SET @asset_id = (SELECT id FROM assets WHERE name LIKE '%Avanza%' ORDER BY created_at DESC LIMIT 1);

-- Check tax record
SELECT 
    at.id,
    a.assets_number,
    a.name,
    mtt.name as tax_type,
    at.tax_amount,
    at.due_date,
    at.payment_status,
    at.approval_status,
    at.notes
FROM asset_taxes at
JOIN assets a ON at.asset_id = a.id
JOIN master_tax_types mtt ON at.tax_type_id = mtt.id
WHERE at.asset_id = @asset_id;
```

#### Expected Database Result:
✅ 1 row in `asset_taxes`  
✅ `asset_id`: matches created asset  
✅ `tax_type_id`: 1 (PKB)  
✅ `tax_amount`: 2500000  
✅ `due_date`: 2025-01-31  
✅ `payment_status`: unpaid  
✅ `approval_status`: pending  
✅ `penalty_amount`: 0  
✅ `notes`: "Pajak PKB tahun 2025"  
✅ `created_by`: auth user ID

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 5: Multiple Taxes Input
**Objective**: Input multiple pajak untuk satu aset

#### Steps:
1. Start new purchase process (kategori Kendaraan)
2. Expand "Data Pajak" → toggle ON
3. Add first tax:
   - Jenis: PKB
   - Nilai: 2500000
   - Due Date: 31/01/2025
4. Click "+ Tambah Pajak Lain"
5. Add second tax:
   - Jenis: SWDKLLJ
   - Nilai: 143000
   - Due Date: 31/01/2025
6. Click "+ Tambah Pajak Lain"
7. Add third tax:
   - Jenis: BPKB
   - Nilai: 500000
   - Due Date: 15/02/2025
8. Complete purchase

#### Expected Result:
✅ 3 tax records created  
✅ All linked to same `asset_id`  
✅ Different `tax_type_id`  
✅ All status: unpaid + pending

#### Verify in Database:
```sql
SELECT COUNT(*) as tax_count
FROM asset_taxes at
WHERE at.asset_id = @asset_id;
-- Expected: 3

SELECT 
    mtt.name,
    at.tax_amount,
    at.due_date
FROM asset_taxes at
JOIN master_tax_types mtt ON at.tax_type_id = mtt.id
WHERE at.asset_id = @asset_id
ORDER BY at.tax_amount DESC;
-- Expected: 3 rows (PKB, BPKB, SWDKLLJ)
```

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 6: Skip Tax Input (Toggle OFF)
**Objective**: Verify purchase works normally without tax input

#### Steps:
1. Start new purchase process
2. Expand "Data Pajak" → keep toggle **OFF**
3. Complete purchase normally

#### Expected Result:
✅ Purchase success  
✅ Asset created  
✅ NO tax records created

#### Verify in Database:
```sql
SELECT COUNT(*) as tax_count
FROM asset_taxes at
WHERE at.asset_id = @asset_id;
-- Expected: 0
```

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 7: Kategori TANPA Tax Types (Elektronik)
**Objective**: Verify section tidak muncul untuk kategori tanpa tax types

#### Steps:
1. Start purchase for kategori **Elektronik**
2. Navigate to item detail step
3. Scroll entire form

#### Expected Result:
✅ Section "Data Pajak": **NOT VISIBLE**  
✅ No toggle, no repeater  
✅ Only shows: Units → Preview Nomor Aset

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 8: Multiple Units per Item
**Objective**: Verify pajak hanya dibuat untuk unit pertama

#### Steps:
1. Create request dengan **quantity = 3**
2. Start purchase process
3. Fill 3 units with different brands
4. Add tax data (toggle ON, add PKB)
5. Complete purchase

#### Expected Result:
✅ 3 assets created  
✅ Only **1 tax record** created  
✅ Tax linked to **first asset** only

#### Verify in Database:
```sql
-- Get all assets from this purchase
SELECT id, assets_number, name 
FROM assets 
WHERE assets_number LIKE '%-001' 
   OR assets_number LIKE '%-002'
   OR assets_number LIKE '%-003'
ORDER BY assets_number;

-- Check which asset has tax
SELECT 
    a.assets_number,
    COUNT(at.id) as tax_count
FROM assets a
LEFT JOIN asset_taxes at ON a.id = at.asset_id
WHERE a.assets_number LIKE '%-001' 
   OR a.assets_number LIKE '%-002'
   OR a.assets_number LIKE '%-003'
GROUP BY a.id, a.assets_number
ORDER BY a.assets_number;

-- Expected:
-- -001: tax_count = 1
-- -002: tax_count = 0
-- -003: tax_count = 0
```

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 9: Required Field Validation
**Objective**: Verify validation bekerja jika toggle ON tapi field kosong

#### Steps:
1. Start purchase
2. Toggle "Catat pajak" → ON
3. Klik "+ Tambah Pajak Lain"
4. Leave all fields empty or partial:
   - Test A: All empty
   - Test B: Only select tax type
   - Test C: Only input amount
5. Try to submit

#### Expected Result:
✅ Validation error shown  
✅ Cannot proceed to next step  
✅ Error messages highlight required fields:
   - "Jenis Pajak wajib diisi"
   - "Nilai Pajak wajib diisi"
   - "Tanggal Jatuh Tempo wajib diisi"

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 10: Item Label Display
**Objective**: Verify repeater item label shows tax type name

#### Steps:
1. Start purchase
2. Toggle pajak ON
3. Add pajak: select "PKB"
4. Observe repeater item header

#### Expected Result - Before Selection:
✅ Item label: "Pajak Baru"

#### Expected Result - After Selection:
✅ Item label: "PKB" (atau full name: "Pajak Kendaraan Bermotor")

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 11: Integration with AssetTaxResource
**Objective**: Verify created taxes appear in AssetTaxResource

#### Steps:
1. Complete purchase with tax data (TEST CASE 4)
2. Navigate to: **Pajak Aset** menu
3. Check table

#### Expected Result:
✅ New row(s) visible in table  
✅ Columns show correct data:
   - Aset: asset name/number
   - Jenis Pajak: tax type name
   - Nilai Pajak: formatted amount
   - Jatuh Tempo: due date
   - Status Pembayaran: badge "Belum Dibayar"
   - Status Persetujuan: badge "Pending"
✅ Can click row to view details  
✅ Table actions available: Approve, Reject

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

### TEST CASE 12: Dashboard Widget Integration
**Objective**: Verify taxes appear in widgets

#### Steps:
1. Complete purchase with tax (due date < 30 days)
2. Navigate to Dashboard
3. Check widgets

#### Expected Result:
✅ **TaxStatsOverview**:
   - Total Pajak: +1
   - Pending: +1
   - Belum Dibayar: +1
✅ **UpcomingTaxesWidget**:
   - Shows new tax record if due_date within 30 days
   - Click → redirects to AssetTaxResource

#### Actual Result:
- [ ] Pass
- [ ] Fail (describe issue): _______________

---

## 🐛 Common Issues & Solutions

### Issue 1: Section tidak muncul untuk kendaraan
**Diagnosis**:
```sql
SELECT * FROM master_tax_types 
WHERE category_id = (SELECT id FROM master_assets_category WHERE name = 'Kendaraan')
AND is_active = true;
```
**Solution**: Run `php artisan db:seed --class=TaxTypeSeeder`

### Issue 2: Dropdown jenis pajak kosong
**Diagnosis**: Check `category_id` matching
**Solution**: Verify tax types have correct `category_id`

### Issue 3: Data pajak tidak tersimpan
**Diagnosis**: 
- Check toggle ON?
- Check validation errors?
- Check console/log errors?
**Solution**: 
```bash
tail -f storage/logs/laravel.log
```

### Issue 4: Error "Call to undefined method"
**Diagnosis**: AssetTax model method not found
**Solution**: Verify `app/Models/AssetTax.php` exists and has correct methods

### Issue 5: Foreign key constraint error
**Diagnosis**: 
```sql
SHOW CREATE TABLE asset_taxes;
```
**Solution**: Re-run migration or fix foreign key definition

---

## 📊 Test Results Summary

### Test Execution Date: _______________
### Tester Name: _______________
### Environment: [ ] Local [ ] Staging [ ] Production

| Test Case | Status | Notes |
|-----------|--------|-------|
| TC1: Kategori dengan Tax Types | [ ] Pass [ ] Fail | |
| TC2: Toggle Functionality | [ ] Pass [ ] Fail | |
| TC3: Tax Type Filtering | [ ] Pass [ ] Fail | |
| TC4: Single Tax Input | [ ] Pass [ ] Fail | |
| TC5: Multiple Taxes Input | [ ] Pass [ ] Fail | |
| TC6: Skip Tax Input | [ ] Pass [ ] Fail | |
| TC7: Kategori tanpa Tax Types | [ ] Pass [ ] Fail | |
| TC8: Multiple Units per Item | [ ] Pass [ ] Fail | |
| TC9: Required Field Validation | [ ] Pass [ ] Fail | |
| TC10: Item Label Display | [ ] Pass [ ] Fail | |
| TC11: AssetTaxResource Integration | [ ] Pass [ ] Fail | |
| TC12: Dashboard Widget Integration | [ ] Pass [ ] Fail | |

### Overall Result:
- **Pass**: _____ / 12
- **Fail**: _____ / 12
- **Pass Rate**: _____%

### Critical Issues Found:
1. _______________
2. _______________

### Recommendations:
1. _______________
2. _______________

---

## 🚀 Sign-Off

### Developer:
- Name: _______________
- Date: _______________
- Signature: _______________

### Tester:
- Name: _______________
- Date: _______________
- Signature: _______________

### Approval:
- Name: _______________
- Date: _______________
- Signature: _______________

---

**Document Version**: 1.0  
**Last Updated**: 24 December 2024
