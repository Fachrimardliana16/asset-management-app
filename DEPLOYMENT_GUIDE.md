# Panduan Deployment ke Shared Hosting

## Asset Management App - Laravel + Filament

---

## ⚠️ UNTUK HOSTING TANPA SSH/TERMINAL

Jika hosting Anda tidak menyediakan akses SSH atau terminal, gunakan file `deploy.php` yang sudah disediakan. Lihat bagian **"Deployment Tanpa SSH"** di bawah.

---

## 📋 PERSIAPAN SEBELUM UPLOAD

### 1. Requirements di Shared Hosting

-   PHP >= 8.1
-   MySQL >= 5.7 atau MariaDB >= 10.3
-   Ekstensi PHP yang diperlukan:
    -   BCMath
    -   Ctype
    -   cURL
    -   DOM
    -   Fileinfo
    -   JSON
    -   Mbstring
    -   OpenSSL
    -   PCRE
    -   PDO
    -   Tokenizer
    -   XML
    -   GD atau Imagick

---

## 🚀 LANGKAH-LANGKAH DEPLOYMENT

### Metode 1: Upload Seluruh Project ke Luar public_html

#### Struktur Folder di Hosting:

```
/home/username/
├── asset-management-app/      ← Upload seluruh project di sini
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── lang/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── ...
│
└── public_html/               ← Root domain
    ├── index.php              ← Copy dari public_html_index.php
    ├── .htaccess              ← Copy dari public_html_htaccess.txt
    ├── css/                   ← Copy dari public/css
    ├── js/                    ← Copy dari public/js
    ├── images/                ← Copy dari public/images
    ├── vendor/                ← Copy dari public/vendor
    ├── favicon.ico            ← Copy dari public/favicon.ico
    └── robots.txt             ← Copy dari public/robots.txt
```

#### Langkah Detail:

1. **Extract file ZIP di hosting**

    - Upload `asset-management-app.zip` ke folder `/home/username/`
    - Extract menggunakan File Manager atau SSH

2. **Copy file ke public_html**

    - Copy isi folder `public/` ke `public_html/`
    - Rename `public_html_index.php` menjadi `index.php`
    - Rename `public_html_htaccess.txt` menjadi `.htaccess`

3. **Buat Symbolic Link untuk Storage**
    - Via SSH:
    ```bash
    cd ~/public_html
    ln -s ~/asset-management-app/storage/app/public storage
    ```
    - Atau gunakan File Manager untuk membuat symlink

---

### Metode 2: Upload Langsung ke public_html (Tidak Disarankan)

Jika hosting tidak mendukung akses folder di luar public_html:

1. Upload seluruh project ke `public_html`
2. Gunakan `.htaccess` di root untuk redirect ke folder `public`

---

## ⚙️ KONFIGURASI SETELAH UPLOAD

### 1. Setup Database

-   Buat database baru di cPanel/Plesk
-   Buat user database dan berikan akses penuh
-   Catat nama database, username, dan password

### 2. Konfigurasi .env

-   Rename `.env.production` menjadi `.env`
-   Edit sesuai dengan data hosting:

```env
APP_NAME=Asset-Management-App
APP_ENV=production
APP_KEY=                              # Generate nanti
APP_DEBUG=false
APP_URL=https://domainanda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=username_database_anda
DB_PASSWORD=password_database_anda

MAIL_MAILER=smtp
MAIL_HOST=mail.domainanda.com
MAIL_PORT=465
MAIL_USERNAME=email@domainanda.com
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@domainanda.com"
```

### 3. Generate App Key

Via SSH:

```bash
cd ~/asset-management-app
php artisan key:generate
```

Atau manual: Generate key 32 karakter random dan tambahkan ke .env:

```
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### 4. Set Permissions

```bash
cd ~/asset-management-app

# Set folder permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Pastikan writable
chmod -R 775 storage/logs
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
```

### 5. Jalankan Migrasi Database

```bash
cd ~/asset-management-app
php artisan migrate --force
```

### 6. Jalankan Seeder (Opsional)

```bash
php artisan db:seed --force
```

### 7. Clear & Optimize Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 8. Buat Storage Link

```bash
php artisan storage:link
```

---

## 🔒 KEAMANAN TAMBAHAN

### 1. Protect Sensitive Files

Pastikan file berikut TIDAK bisa diakses dari browser:

-   `.env`
-   `composer.json`
-   `composer.lock`
-   `artisan`

### 2. Tambahkan di .htaccess root project:

```apache
<Files "artisan">
    Order allow,deny
    Deny from all
</Files>
```

### 3. Set Cron Job untuk Queue (Jika diperlukan)

Di cPanel, tambahkan cron job:

```
* * * * * cd /home/username/asset-management-app && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 TROUBLESHOOTING

### Error 500 Internal Server Error

1. Cek file `.htaccess` apakah benar
2. Cek permission folder `storage` dan `bootstrap/cache`
3. Cek PHP version (minimal 8.1)
4. Lihat log error di `storage/logs/laravel.log`

### Halaman Blank

1. Enable `APP_DEBUG=true` sementara di `.env`
2. Cek error message
3. Kembalikan `APP_DEBUG=false` setelah selesai

### Assets Tidak Muncul (CSS/JS)

1. Pastikan semua file di `public/` sudah di-copy ke `public_html/`
2. Jalankan `php artisan storage:link`
3. Cek URL di browser apakah path sudah benar

### Session/Login Tidak Bekerja

1. Cek permission `storage/framework/sessions`
2. Clear session: `php artisan session:clear`

### Upload File Tidak Bekerja

1. Cek symbolic link storage
2. Cek permission `storage/app/public`
3. Pastikan symlink benar: `public_html/storage` → `asset-management-app/storage/app/public`

---

## 📝 CHECKLIST DEPLOYMENT

-   [ ] Upload dan extract ZIP
-   [ ] Setup struktur folder
-   [ ] Buat database di cPanel
-   [ ] Konfigurasi .env
-   [ ] Generate APP_KEY
-   [ ] Set permissions
-   [ ] Jalankan migration
-   [ ] Jalankan seeder (opsional)
-   [ ] Buat storage link
-   [ ] Clear dan cache config
-   [ ] Test akses website
-   [ ] Test login
-   [ ] Test upload file
-   [ ] Setup cron job (jika perlu)

---

## 📞 BANTUAN

Jika mengalami masalah, cek:

1. Error log di `storage/logs/laravel.log`
2. Error log hosting di cPanel > Error Logs
3. PHP error log

---

**Selamat! Aplikasi Asset Management siap digunakan! 🎉**

---

## 🌐 DEPLOYMENT TANPA SSH/TERMINAL

Jika hosting Anda **tidak menyediakan SSH atau terminal**, ikuti langkah berikut:

### Langkah 1: Persiapan File

1. Extract `asset-management-app.zip` di komputer lokal Anda
2. Edit file `.env.production`:

    - Rename menjadi `.env`
    - Ubah konfigurasi sesuai data hosting:

    ```env
    APP_NAME=Asset-Management-App
    APP_ENV=production
    APP_KEY=                              # Akan di-generate nanti
    APP_DEBUG=false
    APP_URL=https://domainanda.com

    DB_CONNECTION=mysql
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=nama_database_anda
    DB_USERNAME=username_database_anda
    DB_PASSWORD=password_database_anda
    ```

3. Edit file `deploy.php`:
    - Buka file `deploy.php`
    - Ganti security token di baris:
    ```php
    $SECURITY_TOKEN = 'ganti_dengan_token_rahasia_anda_123';
    ```
    - Contoh: `$SECURITY_TOKEN = 'MySecretToken2024';`

### Langkah 2: Upload ke Hosting

**Struktur Upload:**

```
/home/username/
├── asset-management-app/      ← Upload SEMUA file project di sini
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── deploy.php            ← File deployment
│   ├── .env                  ← File konfigurasi (sudah diedit)
│   ├── vendor/
│   └── ...
│
└── public_html/               ← Root domain
    ├── index.php              ← Copy dari public_html_index.php
    ├── .htaccess              ← Rename dari public_html_htaccess.txt
    ├── css/
    ├── js/
    ├── images/
    ├── vendor/
    ├── favicon.ico
    └── robots.txt
```

**Cara Upload via cPanel File Manager:**

1. Login ke cPanel hosting Anda
2. Buka **File Manager**
3. Upload `asset-management-app.zip` ke folder `/home/username/`
4. Klik kanan pada ZIP → **Extract**
5. Masuk ke folder `asset-management-app/public/`
6. Copy semua file dan folder di dalamnya ke `public_html/`
7. Di `public_html/`:
    - Upload `public_html_index.php` dan rename jadi `index.php`
    - Upload `public_html_htaccess.txt` dan rename jadi `.htaccess`

### Langkah 3: Buat Database

1. Di cPanel, buka **MySQL Databases**
2. Buat database baru (catat namanya)
3. Buat user baru dengan password
4. Tambahkan user ke database dengan **ALL PRIVILEGES**
5. Pastikan `.env` sudah diupdate dengan info database

### Langkah 4: Jalankan Deployment Script

1. Buka browser dan akses:

    ```
    https://domainanda.com/../asset-management-app/deploy.php?token=TOKEN_ANDA
    ```

    Atau jika aplikasi di root:

    ```
    https://domainanda.com/deploy.php?token=TOKEN_ANDA
    ```

2. Ganti `TOKEN_ANDA` dengan security token yang Anda set di `deploy.php`

3. Di halaman deployment:

    - Klik **"1. ✅ Cek Requirements"** - Pastikan semua hijau ✅
    - Klik **"🎯 Jalankan Semua (Rekomendasi)"** - Untuk menjalankan semua langkah

4. Atau jalankan satu per satu:
    - Cek Requirements
    - Generate APP_KEY
    - Jalankan Migration
    - Link Storage
    - Clear & Optimize Cache

### Langkah 5: Selesai & Keamanan

1. **HAPUS file `deploy.php`** setelah deployment selesai!
    - Ini sangat penting untuk keamanan
2. Akses website:

    - Homepage: `https://domainanda.com`
    - Admin Panel: `https://domainanda.com/admin`

3. Default Login (jika menggunakan seeder):
    - Email: `admin@example.com`
    - Password: `password`
    - **Segera ganti password setelah login!**

---

## ❓ FAQ - Pertanyaan Umum

### Q: Tidak bisa upload file besar?

**A:** Tingkatkan limit upload di cPanel:

-   PHP Settings → `upload_max_filesize` = 256M
-   PHP Settings → `post_max_size` = 256M

### Q: Error 500 setelah upload?

**A:** Cek permissions:

-   Folder `storage/` → 755
-   Folder `bootstrap/cache/` → 755
-   Via File Manager: klik kanan → Change Permissions

### Q: Gambar/file tidak muncul?

**A:**

1. Pastikan folder `public/storage` sudah ada
2. Jalankan "Link Storage" di deploy.php
3. Atau manual: copy isi `storage/app/public` ke `public/storage`

### Q: Lupa security token deploy.php?

**A:** Download ulang file dari backup, atau buka file via File Manager dan lihat isi `$SECURITY_TOKEN`

### Q: Cara update aplikasi?

**A:**

1. Backup database via phpMyAdmin
2. Upload file baru (timpa yang lama, kecuali .env)
3. Jalankan deploy.php → Migration
4. Hapus deploy.php

---

## 📞 BANTUAN

Jika masih mengalami masalah:

1. Cek error log di cPanel → Error Logs
2. Lihat file `storage/logs/laravel.log` via File Manager
3. Aktifkan debug sementara: ubah `APP_DEBUG=true` di `.env`
