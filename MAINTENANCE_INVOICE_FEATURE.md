# Fitur Cetak Kwitansi Pemeliharaan Aset

## Deskripsi
Fitur ini memungkinkan user untuk mencetak kwitansi/faktur pemeliharaan aset dalam format PDF dengan format yang profesional dan siap cetak.

## File yang Dibuat/Dimodifikasi

### 1. Blade View - Kwitansi Template
**File:** `resources/views/invoices/maintenance-invoice.blade.php`
- Template kwitansi dengan format siap cetak
- Menggunakan tema yang konsisten dengan invoice/dokumen lainnya
- Fitur:
  - Header dengan logo perusahaan
  - Informasi umum (nomor kwitansi, tanggal)
  - Detail aset yang diperbaiki
  - Detail perbaikan (lokasi service, deskripsi)
  - Total biaya dengan format terbilang
  - Bagian tanda tangan untuk:
    - Pihak Tempat Perbaikan/Lokasi Service
    - Kepala Sub Bagian Kerumahtanggaan
  - Footer dengan timestamp

### 2. Controller
**File:** `app/Http/Controllers/MaintenanceInvoiceController.php`
- Method `printInvoice($id)` - untuk menampilkan PDF di browser
- Method `downloadInvoice($id)` - untuk download PDF langsung
- Menggunakan DomPDF untuk generate PDF
- Paper size: A4 portrait

### 3. Helper Class
**File:** `app/Helpers/NumberToWords.php`
- Fungsi untuk konversi angka ke kata-kata bahasa Indonesia
- Digunakan untuk menampilkan total biaya dalam format terbilang
- Contoh: 150000 → "seratus lima puluh ribu"

### 4. Routes
**File:** `routes/web.php`
Ditambahkan 2 route baru:
```php
Route::get('/maintenance/invoice/{id}', [MaintenanceInvoiceController::class, 'printInvoice'])
    ->name('maintenance.invoice');
Route::get('/maintenance/invoice/{id}/download', [MaintenanceInvoiceController::class, 'downloadInvoice'])
    ->name('maintenance.invoice.download');
```

### 5. Resource File (Updated)
**File:** `app/Filament/Resources/AssetMaintenanceResource.php`
- Diupdate bagian actions pada table
- Ditambahkan 2 action button:
  - **Cetak Kwitansi** (hijau) - Membuka PDF di tab baru
  - **Download Kwitansi** (biru) - Download PDF langsung

## Cara Penggunaan

1. **Melihat List Pemeliharaan**
   - Buka menu "Pemeliharaan Barang" di sidebar
   - Pilih record pemeliharaan yang ingin dicetak kwitansinya

2. **Cetak Kwitansi**
   - Klik tombol "Actions" pada record
   - Pilih "Cetak Kwitansi"
   - PDF akan terbuka di tab baru

3. **Download Kwitansi**
   - Klik tombol "Actions" pada record
   - Pilih "Download Kwitansi"
   - PDF akan otomatis terdownload

## Format Kwitansi

### Informasi yang Ditampilkan:
1. **Header**
   - Logo perusahaan (jika ada di public/images/logo.png)
   - Nama perusahaan
   - Alamat dan kontak

2. **Informasi Umum**
   - Nomor Kwitansi (format: KWT-MAINT-XXXXX)
   - Tanggal Pemeliharaan
   - Tanggal Cetak

3. **Detail Aset**
   - Nomor Aset
   - Nama Aset
   - Jenis Perbaikan (badge dengan warna sesuai tingkat)

4. **Detail Perbaikan**
   - Lokasi Service/Tempat Perbaikan
   - Deskripsi Kerusakan & Perbaikan

5. **Biaya**
   - Total Biaya (format Rupiah)
   - Terbilang dalam kata-kata

6. **Tanda Tangan**
   - Pihak Tempat Perbaikan (kiri)
   - Kepala Sub Bagian Kerumahtanggaan (kanan)
   - Ruang untuk tanda tangan dan nama

## Customisasi

### Mengubah Logo
Letakkan file logo di: `public/images/logo.png`

### Mengubah Informasi Perusahaan
Edit file: `resources/views/invoices/maintenance-invoice.blade.php`
Bagian:
```php
<div class="company-name">Pemerintah Kabupaten/Kota</div>
<div class="company-subtitle">PERUSAHAAN DAERAH AIR MINUM</div>
<div class="company-address">
    Jl. Alamat Kantor No. XX, Kota, Provinsi | Telp: (0XXX) XXXXXX | Email: info@pdam.go.id
</div>
```

### Mengubah Format Nomor Kwitansi
Edit di file blade, bagian:
```php
{{ sprintf('KWT-MAINT-%05d', $maintenance->id) }}
```

### Mengubah Jabatan Penandatangan
Edit di bagian signature-section:
```html
<div class="signature-title">Kepala Sub Bagian Kerumahtanggaan</div>
```

## Troubleshooting

### PDF Tidak Muncul Logo
- Pastikan file logo ada di `public/images/logo.png`
- Cek permission folder public/images

### Error "Class NumberToWords not found"
- Jalankan: `composer dump-autoload`

### Format Terbilang Tidak Muncul
- Pastikan helper NumberToWords sudah di-autoload
- Cek namespace: `App\Helpers\NumberToWords`

### Tanda Tangan Tidak Muncul di Print
- Pastikan CSS print sudah benar
- Gunakan `page-break-inside: avoid` pada signature-section

## Fitur Tambahan yang Bisa Dikembangkan

1. **QR Code untuk Verifikasi**
   - Tambahkan QR code di kwitansi untuk validasi online

2. **Nomor Seri Otomatis**
   - Generate nomor seri berdasarkan tahun/bulan

3. **Email Kwitansi**
   - Kirim kwitansi otomatis ke email terkait

4. **Multi-Currency**
   - Support mata uang selain Rupiah

5. **Digital Signature**
   - Integrasi tanda tangan digital

6. **Watermark**
   - Tambahkan watermark "COPY" untuk duplikat

7. **Template Customizable**
   - Admin bisa custom template dari dashboard

## Dependencies

- Laravel Framework (^10.10)
- Filament PHP (^3.2)
- barryvdh/laravel-dompdf (^3.1)

## Catatan Penting

- Kwitansi menggunakan paper size A4 Portrait
- Format angka menggunakan format Indonesia (Rp)
- Tanggal menggunakan format Indonesia
- Pastikan relasi AssetMaintenance ke Asset sudah benar
- Badge warna disesuaikan dengan jenis perbaikan

## Support

Jika ada kendala atau pertanyaan, hubungi developer atau buat issue di repository.
