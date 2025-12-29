# Workflow Diagram: Tax Input Integration

## 🔄 Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    PROSES PEMBELIAN ASET                        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Informasi Umum                                         │
│  ├─ Tanggal Pembelian                                          │
│  ├─ Kondisi Aset                                               │
│  ├─ Status Aset                                                │
│  ├─ Sumber Dana                                                │
│  └─ Masa Berlaku Nilai Buku                                    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: Detail Item #1 (Kendaraan)                            │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ 📦 DETAIL PER UNIT ASET (Repeater)                        │ │
│  │ ├─ Merk/Tipe: Dell Latitude 5420                          │ │
│  │ ├─ Harga: Rp 15,000,000                                   │ │
│  │ ├─ Nilai Buku: Rp 15,000,000                              │ │
│  │ └─ Foto: [Upload]                                         │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ 📄 DATA PAJAK (Section - Collapsible) ◄── FITUR BARU!    │ │
│  │                                                            │ │
│  │ ┌─ Toggle: ☑ Catat pajak sekarang?                       │ │
│  │ │                                                          │ │
│  │ └─ [VISIBLE = true] → Tampilkan Repeater                 │ │
│  │                                                            │ │
│  │    ┌─────────────────────────────────────────────────┐   │ │
│  │    │ 📋 PAJAK #1                                      │   │ │
│  │    │ ├─ Jenis: PKB (filtered by category_id)         │   │ │
│  │    │ ├─ Nilai: Rp 2,500,000                          │   │ │
│  │    │ ├─ Jatuh Tempo: 31/01/2025                      │   │ │
│  │    │ └─ Catatan: Pajak tahun 2025                    │   │ │
│  │    └─────────────────────────────────────────────────┘   │ │
│  │                                                            │ │
│  │    ┌─────────────────────────────────────────────────┐   │ │
│  │    │ 📋 PAJAK #2                                      │   │ │
│  │    │ ├─ Jenis: SWDKLLJ (filtered by category_id)     │   │ │
│  │    │ ├─ Nilai: Rp 143,000                            │   │ │
│  │    │ ├─ Jatuh Tempo: 31/01/2025                      │   │ │
│  │    │ └─ Catatan: Asuransi kecelakaan                 │   │ │
│  │    └─────────────────────────────────────────────────┘   │ │
│  │                                                            │ │
│  │    [+ Tambah Pajak Lain]                                  │ │
│  │                                                            │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                  │
│  📋 Preview Nomor Aset: KDR-JKT-2024-001                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: Review & Konfirmasi                                    │
│  ├─ Dokumen: PO-2024-001                                       │
│  ├─ Total Jenis Barang: 1                                      │
│  ├─ Total Unit Aset: 1 aset                                    │
│  └─ ⚠️ Klik "Simpan Semua Pembelian"                           │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SUBMIT PROCESS                              │
│                                                                  │
│  foreach items:                                                  │
│    foreach units:                                                │
│      1. Create AssetPurchase ✓                                  │
│      2. Create Asset ✓                                          │
│      3. IF ($i === 1 AND has_taxes === true):                  │
│         foreach taxes:                                           │
│           Create AssetTax ◄── LOGIC BARU!                       │
│           ├─ asset_id: $createdAsset->id                        │
│           ├─ tax_type_id: from input                            │
│           ├─ tax_amount: from input                             │
│           ├─ due_date: from input                               │
│           ├─ payment_status: 'unpaid'                           │
│           ├─ approval_status: 'pending'                         │
│           ├─ penalty_amount: 0                                  │
│           └─ created_by: auth()->id()                           │
│                                                                  │
│  Update AssetRequest status                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     DATABASE RECORDS                             │
│                                                                  │
│  ✅ assets                                                       │
│     ├─ id: uuid                                                 │
│     ├─ assets_number: KDR-JKT-2024-001                         │
│     ├─ name: Laptop                                             │
│     └─ ...other fields                                          │
│                                                                  │
│  ✅ asset_taxes (NEW RECORDS!)                                  │
│     ├─ id: 1                                                    │
│     ├─ asset_id: uuid (relation to assets)                     │
│     ├─ tax_type_id: 1 (PKB)                                    │
│     ├─ tax_amount: 2500000                                      │
│     ├─ due_date: 2025-01-31                                     │
│     ├─ payment_status: unpaid                                   │
│     ├─ approval_status: pending                                 │
│     └─ penalty_amount: 0                                        │
│                                                                  │
│     ├─ id: 2                                                    │
│     ├─ asset_id: uuid (same asset)                             │
│     ├─ tax_type_id: 3 (SWDKLLJ)                                │
│     ├─ tax_amount: 143000                                       │
│     ├─ due_date: 2025-01-31                                     │
│     └─ ...                                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              INTEGRASI DENGAN SISTEM LAIN                        │
│                                                                  │
│  1. AssetTaxResource                                             │
│     └─ Pajak muncul di table, status: pending                   │
│                                                                  │
│  2. Dashboard Widgets                                            │
│     ├─ TaxStatsOverview: +2 pending taxes                       │
│     ├─ UpcomingTaxesWidget: Show if due_date < 30 days         │
│     └─ OverdueTaxesWidget: Show if overdue                      │
│                                                                  │
│  3. Approval Workflow                                            │
│     └─ Approver dapat approve/reject via table actions          │
│                                                                  │
│  4. Notifications                                                │
│     ├─ TaxReminderNotification (30 hari sebelum due)           │
│     ├─ TaxOverdueNotification (jika lewat due date)            │
│     └─ TaxApprovalNotification (untuk approver)                │
│                                                                  │
│  5. Scheduled Commands                                           │
│     ├─ SendTaxReminders (daily)                                 │
│     └─ UpdateTaxPenalties (daily)                               │
└─────────────────────────────────────────────────────────────────┘
```

## 🎯 Decision Logic

### Visibility Logic untuk Section Pajak
```
IF (MasterTaxType exists WHERE category_id = item.category_id AND is_active = true)
  THEN: Show "Data Pajak" section
  ELSE: Hide section completely
```

### Filter Jenis Pajak
```
SELECT * FROM master_tax_types
WHERE category_id = {current_item.category_id}
  AND is_active = true
```

### Create Tax Logic
```
IF (unit_index === 1 AND has_taxes === true AND taxes array not empty)
  THEN:
    foreach (taxes as tax):
      Create AssetTax with:
        - asset_id: from newly created asset
        - tax data: from form input
        - default statuses: unpaid + pending
```

## 📊 Data Flow

```
┌────────────────┐
│  Form Input    │
│  (User fills)  │
└────────┬───────┘
         │
         ▼
┌────────────────────────────┐
│  has_taxes = true          │
│  taxes = [                 │
│    {                       │
│      tax_type_id: 1,       │
│      tax_amount: 2500000,  │
│      due_date: 2025-01-31, │
│      notes: "..."          │
│    },                      │
│    { ... }                 │
│  ]                         │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│  $data['items'][$itemId]   │
│  ├─ has_taxes              │
│  ├─ taxes                  │
│  └─ units                  │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│  submit() method           │
│  ├─ Create AssetPurchase   │
│  ├─ Create Asset           │
│  └─ IF conditions met:     │
│     Create AssetTax(s)     │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│  Database Tables           │
│  ├─ asset_purchases ✓      │
│  ├─ assets ✓               │
│  └─ asset_taxes ✓ (NEW!)   │
└────────────────────────────┘
```

## 🔐 Conditional Checks

### Check 1: Section Visibility
```php
->visible(function () use ($item) {
    return MasterTaxType::where('category_id', $item->category_id)
        ->where('is_active', true)
        ->exists();
})
```
**Purpose**: Hide section if no tax types for category

### Check 2: Repeater Visibility
```php
->visible(fn (Forms\Get $get) => $get("items.{$item->id}.has_taxes") === true)
```
**Purpose**: Show/hide repeater based on toggle

### Check 3: Tax Creation
```php
if ($i === 1 && isset($itemData['has_taxes']) && $itemData['has_taxes'] === true) {
    // Create taxes
}
```
**Purpose**: 
- Only create for first unit
- Only if toggle is ON
- Only if toggle exists in data

## 🎨 UI States

### State 1: Section Hidden (Default for non-taxable categories)
```
[Item Details]
[Units Repeater]
[Preview Nomor Aset]
```

### State 2: Section Visible, Collapsed (Default for taxable categories)
```
[Item Details]
[Units Repeater]
▶ Data Pajak
[Preview Nomor Aset]
```

### State 3: Section Expanded, Toggle OFF
```
[Item Details]
[Units Repeater]
▼ Data Pajak
  ☐ Catat pajak sekarang?
[Preview Nomor Aset]
```

### State 4: Section Expanded, Toggle ON, No Taxes
```
[Item Details]
[Units Repeater]
▼ Data Pajak
  ☑ Catat pajak sekarang?
  [Daftar Pajak: Empty]
  [+ Tambah Pajak Lain]
[Preview Nomor Aset]
```

### State 5: Section Expanded, Toggle ON, With Taxes
```
[Item Details]
[Units Repeater]
▼ Data Pajak
  ☑ Catat pajak sekarang?
  ▼ Daftar Pajak
    ▼ PKB
      [Jenis: PKB]
      [Nilai: 2,500,000]
      [Due: 31/01/2025]
      [Notes: ...]
    ▼ SWDKLLJ
      [...]
  [+ Tambah Pajak Lain]
[Preview Nomor Aset]
```

## 📈 Impact Analysis

### Before Implementation
```
Purchase Flow:
  1. Create Asset Purchase → 2. Create Asset
  
Tax Management:
  1. Manually open AssetTaxResource
  2. Click Create New
  3. Search & select asset
  4. Fill tax data
  5. Submit
  
Total Steps: 7
```

### After Implementation
```
Purchase Flow:
  1. Create Asset Purchase → 2. Create Asset → 3. Create Tax(s) (optional)
  
Tax Management (if already input during purchase):
  - Already recorded!
  - Only need approval
  
Total Steps: 3
Efficiency Gain: 57% reduction in steps
```

---

**Diagram Version**: 1.0  
**Last Updated**: 24 December 2024
