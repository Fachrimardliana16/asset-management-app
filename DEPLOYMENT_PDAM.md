# 📋 PANDUAN DEPLOYMENT - PDAM Purbalingga

## Subdomain: asseta.pdampurbalingga.co.id

---

## 📁 Struktur Folder di Hosting

```
/home/pdam1537/
│
├── asseta/                          ← PROJECT FOLDER (upload isi ZIP di sini)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── lang/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                        ← Rename dari .env.production
│   ├── deploy.php                  ← Script deployment
│   ├── artisan
│   ├── composer.json
│   └── ... (file lainnya)
│
└── public_html/
    └── asseta/                      ← DOCUMENT ROOT (file publik)
        ├── index.php               ← Copy dari public_html_index.php
        ├── .htaccess               ← Rename dari public_html_htaccess.txt
        ├── css/
        ├── js/
        ├── images/
        ├── vendor/
        ├── favicon.ico
        ├── robots.txt
        └── storage/                ← Symbolic link atau folder
```

---

## 🚀 LANGKAH DEPLOYMENT

### STEP 1: Upload Project ke `/home/pdam1537/asseta/`

1. Buka **cPanel File Manager**
2. Navigasi ke `/home/pdam1537/`
3. Masuk ke folder `asseta/`
4. Upload file `asset-management-app.zip`
5. Klik kanan pada ZIP → **Extract**
6. Pindahkan semua isi folder hasil extract ke `/home/pdam1537/asseta/`
    - Jika extract menghasilkan folder `asset-management-app/`, masuk ke folder tersebut
    - Select All → Move → Pilih `/home/pdam1537/asseta/`
7. Hapus folder kosong dan file ZIP

### STEP 2: Upload File Public ke `/home/pdam1537/public_html/asseta/`

1. Navigasi ke `/home/pdam1537/asseta/public/`
2. **Select All** file dan folder (css, js, images, vendor, favicon.ico, robots.txt)
3. **Copy** ke `/home/pdam1537/public_html/asseta/`
4. Upload file berikut ke `/home/pdam1537/public_html/asseta/`:
    - `public_html_index.php` → **Rename** jadi `index.php`
    - `public_html_htaccess.txt` → **Rename** jadi `.htaccess`

### STEP 3: Konfigurasi .env

1. Navigasi ke `/home/pdam1537/asseta/`
2. Rename `.env.production` menjadi `.env`
3. **Edit** file `.env`:

```env
APP_NAME=Asset-Management-App
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://asseta.pdampurbalingga.co.id

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pdam1537_asseta      ← Sesuaikan nama database
DB_USERNAME=pdam1537_admin       ← Sesuaikan username database
DB_PASSWORD=password_anda        ← Sesuaikan password database

MAIL_MAILER=smtp
MAIL_HOST=mail.pdampurbalingga.co.id
MAIL_PORT=465
MAIL_USERNAME=asseta@pdampurbalingga.co.id
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="asseta@pdampurbalingga.co.id"
MAIL_FROM_NAME="PDAM Asset Management"
```

### STEP 4: Buat Database di cPanel

1. Buka **MySQL Databases** di cPanel
2. Create Database: `pdam1537_asseta`
3. Create User: `pdam1537_admin` dengan password kuat
4. Add User to Database: Pilih **ALL PRIVILEGES**
5. Update info di file `.env`

### STEP 5: Set Permissions

1. Di File Manager, navigasi ke `/home/pdam1537/asseta/`
2. Klik kanan folder `storage/` → **Change Permissions** → Set ke `755`
3. Klik kanan folder `bootstrap/cache/` → **Change Permissions** → Set ke `755`

### STEP 6: Jalankan Deployment Script

1. Upload file `deploy_standalone.php` ke `/home/pdam1537/public_html/asseta/`
2. Rename menjadi `deploy.php`
3. Edit token di baris 20:

    ```php
    'security_token' => 'PDAMPurbalingga2024',  // Ganti dengan token rahasia Anda!
    ```

4. Buka browser:

    ```
    https://asseta.pdampurbalingga.co.id/deploy.php?token=PDAMPurbalingga2024
    ```

5. Jalankan langkah-langkah:
    - **Langkah 1**: Cek Requirements - Pastikan semua ✅
    - **Langkah 2**: Cek Vendor - **PENTING!** Pastikan semua file ada
    - **Langkah 3**: Cek .env - Pastikan konfigurasi benar
    - **Langkah 4**: Generate APP_KEY
    - **Langkah 5**: Jalankan Migration
    - **Langkah 6**: Setup Storage Link
    - **Langkah 7**: Clear & Optimize Cache
    - **Langkah 8**: Fix Permissions

### STEP 7: Buat Storage Link

Di deployment script sudah otomatis. Jika gagal, buat manual:

1. Di `/home/pdam1537/public_html/asseta/` buat folder `storage/`
2. Copy isi dari `/home/pdam1537/asseta/storage/app/public/` ke folder tersebut

### STEP 8: Selesai & Keamanan

1. **HAPUS** file deploy.php dari:

    - `/home/pdam1537/asseta/deploy.php`
    - `/home/pdam1537/public_html/asseta/deploy.php`

2. Test akses:
    - Website: https://asseta.pdampurbalingga.co.id
    - Admin: https://asseta.pdampurbalingga.co.id/admin

---

## ✅ CHECKLIST

-   [ ] Upload project ke `/home/pdam1537/asseta/`
-   [ ] Upload file public ke `/home/pdam1537/public_html/asseta/`
-   [ ] Rename dan edit `.env`
-   [ ] Buat database di cPanel
-   [ ] Set permissions folder
-   [ ] Jalankan deploy.php
-   [ ] Hapus deploy.php
-   [ ] Test website dan admin panel
-   [ ] Ganti password default

---

## 🔧 TROUBLESHOOTING

### Error 500

-   Cek `.htaccess` sudah benar
-   Cek permissions `storage/` dan `bootstrap/cache/`
-   Lihat error log di cPanel

### Halaman Blank

-   Set `APP_DEBUG=true` sementara di `.env`
-   Lihat error, lalu kembalikan ke `false`

### CSS/JS Tidak Muncul

-   Pastikan folder `css/`, `js/`, `vendor/` sudah ada di `public_html/asseta/`
-   Cek console browser untuk error path

### Upload File Tidak Berfungsi

-   Buat folder `storage/` di `public_html/asseta/`
-   Copy file dari `/home/pdam1537/asseta/storage/app/public/`

---

## 📞 KONTAK

Jika ada kendala, cek:

1. Error Log di cPanel
2. File `/home/pdam1537/asseta/storage/logs/laravel.log`
