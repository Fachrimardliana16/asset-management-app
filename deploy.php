<?php

/**
 * DEPLOYMENT SCRIPT - Jalankan via Browser
 * ==========================================
 *
 * STRUKTUR HOSTING:
 * - Subdomain: asseta.pdampurbalingga.co.id
 * - Document Root: /home/pdam1537/public_html/asseta
 * - Project Path: /home/pdam1537/asseta
 *
 * CARA PENGGUNAAN:
 * 1. Upload file ini ke folder project (/home/pdam1537/asseta/)
 * 2. Akses via browser: https://asseta.pdampurbalingga.co.id/deploy.php?token=YOUR_TOKEN
 *    (atau langsung dari folder project jika bisa diakses)
 * 3. Setelah selesai, HAPUS file ini!
 *
 * PENTING: Hapus file ini setelah deployment selesai untuk keamanan!
 */

// Security token - GANTI dengan token rahasia Anda
$SECURITY_TOKEN = 'ganti_dengan_token_rahasia_anda_123';

// Cek token keamanan
if (!isset($_GET['token']) || $_GET['token'] !== $SECURITY_TOKEN) {
    die('⛔ Akses ditolak! Gunakan: deploy.php?token=YOUR_TOKEN');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 menit timeout

echo "<html><head><title>Asset Management - Deployment</title>";
echo "<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
    .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
    .btn:hover { background: #0056b3; }
    .btn-danger { background: #dc3545; }
    .btn-success { background: #28a745; }
</style></head><body><div class='container'>";

echo "<h1>🚀 Asset Management - Deployment Script</h1>";

$action = $_GET['action'] ?? 'menu';

// Menu utama
if ($action === 'menu') {
    echo "<div class='info'>📋 Pilih langkah deployment yang ingin dijalankan:</div>";
    echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=check'>1. ✅ Cek Requirements</a></p>";
    echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=key'>2. 🔑 Generate APP_KEY</a></p>";
    echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=migrate'>3. 📦 Jalankan Migration</a></p>";
    echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=seed'>4. 🌱 Jalankan Seeder</a></p>";
    echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=storage'>5. 📁 Link Storage</a></p>";
    echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=cache'>6. ⚡ Clear & Optimize Cache</a></p>";
    echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=all'>🎯 Jalankan Semua (Rekomendasi)</a></p>";
    echo "<hr>";
    echo "<p><a class='btn btn-danger' href='?token={$SECURITY_TOKEN}&action=fresh'>⚠️ Fresh Migration (Reset Database)</a></p>";
    echo "</div></body></html>";
    exit;
}

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

function runCommand($command, $params = [])
{
    try {
        Artisan::call($command, $params);
        return Artisan::output();
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

function showOutput($title, $output, $success = true)
{
    $class = $success ? 'success' : 'error';
    echo "<div class='{$class}'><strong>{$title}</strong></div>";
    if ($output) {
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
}

// Cek Requirements
if ($action === 'check' || $action === 'all') {
    echo "<h2>✅ Cek Requirements</h2>";

    $checks = [
        'PHP Version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'Mbstring' => extension_loaded('mbstring'),
        'OpenSSL' => extension_loaded('openssl'),
        'Tokenizer' => extension_loaded('tokenizer'),
        'XML' => extension_loaded('xml'),
        'Ctype' => extension_loaded('ctype'),
        'JSON' => extension_loaded('json'),
        'BCMath' => extension_loaded('bcmath'),
        'Fileinfo' => extension_loaded('fileinfo'),
        'GD' => extension_loaded('gd'),
        'storage/ Writable' => is_writable(__DIR__ . '/storage'),
        'bootstrap/cache/ Writable' => is_writable(__DIR__ . '/bootstrap/cache'),
        '.env Exists' => file_exists(__DIR__ . '/.env'),
    ];

    echo "<table style='width:100%; border-collapse: collapse;'>";
    foreach ($checks as $name => $status) {
        $icon = $status ? '✅' : '❌';
        $color = $status ? '#28a745' : '#dc3545';
        echo "<tr><td style='padding:8px; border-bottom:1px solid #ddd;'>{$name}</td>";
        echo "<td style='padding:8px; border-bottom:1px solid #ddd; color:{$color};'>{$icon}</td></tr>";
    }
    echo "</table>";
    echo "<div class='info'>PHP Version: " . PHP_VERSION . "</div>";
}

// Generate APP_KEY
if ($action === 'key' || $action === 'all') {
    echo "<h2>🔑 Generate APP_KEY</h2>";

    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);

        // Check if key already exists
        if (preg_match('/^APP_KEY=base64:.+$/m', $envContent)) {
            echo "<div class='warning'>APP_KEY sudah ada. Skip generate.</div>";
        } else {
            $output = runCommand('key:generate', ['--force' => true]);
            showOutput('APP_KEY berhasil di-generate!', $output);
        }
    } else {
        echo "<div class='error'>File .env tidak ditemukan! Buat dulu file .env dari .env.production</div>";
    }
}

// Migration
if ($action === 'migrate' || $action === 'all') {
    echo "<h2>📦 Database Migration</h2>";

    try {
        DB::connection()->getPdo();
        echo "<div class='success'>✅ Koneksi database berhasil!</div>";

        $output = runCommand('migrate', ['--force' => true]);
        showOutput('Migration selesai!', $output);
    } catch (Exception $e) {
        echo "<div class='error'>❌ Koneksi database gagal: " . $e->getMessage() . "</div>";
        echo "<div class='warning'>Pastikan konfigurasi database di .env sudah benar!</div>";
    }
}

// Seeder
if ($action === 'seed') {
    echo "<h2>🌱 Database Seeder</h2>";

    try {
        $output = runCommand('db:seed', ['--force' => true]);
        showOutput('Seeder selesai!', $output);
    } catch (Exception $e) {
        echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fresh Migration (Reset)
if ($action === 'fresh') {
    echo "<h2>⚠️ Fresh Migration (Reset Database)</h2>";
    echo "<div class='warning'>PERINGATAN: Ini akan menghapus semua data!</div>";

    if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
        try {
            $output = runCommand('migrate:fresh', ['--force' => true, '--seed' => true]);
            showOutput('Fresh migration dengan seeder selesai!', $output);
        } catch (Exception $e) {
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<p><a class='btn btn-danger' href='?token={$SECURITY_TOKEN}&action=fresh&confirm=yes'>⚠️ Ya, Saya Yakin Reset Database</a></p>";
        echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=menu'>❌ Batal</a></p>";
    }
}

// Storage Link
if ($action === 'storage' || $action === 'all') {
    echo "<h2>📁 Storage Link</h2>";

    $publicStorage = __DIR__ . '/public/storage';
    $target = __DIR__ . '/storage/app/public';

    if (is_link($publicStorage)) {
        echo "<div class='warning'>Symbolic link sudah ada.</div>";
    } else if (file_exists($publicStorage)) {
        echo "<div class='warning'>Folder storage sudah ada (bukan symlink).</div>";
    } else {
        // Coba buat symlink
        if (@symlink($target, $publicStorage)) {
            echo "<div class='success'>✅ Symbolic link berhasil dibuat!</div>";
        } else {
            // Jika symlink gagal, copy folder sebagai alternatif
            echo "<div class='warning'>Symlink tidak didukung. Mencoba metode alternatif...</div>";

            // Buat folder dan file .htaccess untuk redirect
            if (!file_exists($publicStorage)) {
                mkdir($publicStorage, 0755, true);
            }

            // Copy file dari storage/app/public ke public/storage
            $files = glob($target . '/*');
            foreach ($files as $file) {
                $dest = $publicStorage . '/' . basename($file);
                if (is_dir($file)) {
                    // Untuk shared hosting tanpa symlink, rekomendasikan upload manual
                    echo "<div class='info'>Folder: " . basename($file) . " - upload manual ke public/storage/</div>";
                } else {
                    copy($file, $dest);
                }
            }
            echo "<div class='info'>Untuk upload file, gunakan folder: public/storage/</div>";
        }
    }
}

// Cache
if ($action === 'cache' || $action === 'all') {
    echo "<h2>⚡ Clear & Optimize Cache</h2>";

    $commands = [
        'config:clear' => 'Clear Config Cache',
        'route:clear' => 'Clear Route Cache',
        'view:clear' => 'Clear View Cache',
        'cache:clear' => 'Clear Application Cache',
        'config:cache' => 'Cache Config',
        'route:cache' => 'Cache Routes',
        'view:cache' => 'Cache Views',
    ];

    foreach ($commands as $cmd => $name) {
        try {
            $output = runCommand($cmd);
            echo "<div class='success'>✅ {$name}</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ {$name}: " . $e->getMessage() . "</div>";
        }
    }
}

// Selesai
if ($action === 'all') {
    echo "<hr>";
    echo "<div class='success' style='font-size: 18px;'>🎉 <strong>Deployment Selesai!</strong></div>";
    echo "<div class='warning'>⚠️ <strong>PENTING:</strong> Hapus file deploy.php ini sekarang untuk keamanan!</div>";
}

echo "<hr>";
echo "<p><a class='btn' href='?token={$SECURITY_TOKEN}&action=menu'>← Kembali ke Menu</a></p>";
echo "<p><a class='btn btn-success' href='/'>🏠 Buka Website</a></p>";
echo "<p><a class='btn btn-success' href='/admin'>👤 Buka Admin Panel</a></p>";

echo "</div></body></html>";
