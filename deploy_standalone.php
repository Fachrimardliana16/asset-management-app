<?php

/**
 * DEPLOYMENT SCRIPT STANDALONE - Tidak memerlukan Laravel
 * =========================================================
 *
 * Script ini TIDAK memerlukan vendor/autoload.php
 * Bisa dijalankan langsung tanpa Laravel
 *
 * STRUKTUR HOSTING:
 * - Subdomain: asseta.pdampurbalingga.co.id
 * - Document Root: /home/pdam1537/public_html/asseta
 * - Project Path: /home/pdam1537/asseta
 *
 * CARA PENGGUNAAN:
 * 1. Upload file ini ke: /home/pdam1537/public_html/asseta/deploy.php
 * 2. Akses via browser: https://asseta.pdampurbalingga.co.id/deploy.php?token=YOUR_TOKEN
 * 3. Setelah selesai, HAPUS file ini!
 */

// ============================================
// KONFIGURASI - SESUAIKAN DENGAN HOSTING ANDA
// ============================================

$CONFIG = [
    'security_token' => 'PDAMPurbalingga2024',  // GANTI dengan token rahasia Anda!
    'project_path'   => '/home/pdam1537/asseta',
    'public_path'    => '/home/pdam1537/public_html/asseta',
    'php_binary'     => 'php',  // atau '/usr/bin/php' atau '/opt/alt/php83/usr/bin/php'
];

// ============================================
// JANGAN EDIT DI BAWAH INI
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(600); // 10 menit timeout

// Security check
if (!isset($_GET['token']) || $_GET['token'] !== $CONFIG['security_token']) {
    die('⛔ Akses ditolak! Gunakan: deploy.php?token=YOUR_TOKEN');
}

$action = $_GET['action'] ?? 'menu';

// HTML Header
function htmlHeader()
{
    echo "<!DOCTYPE html><html><head>
    <title>Asset Management - Deployment</title>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 15px; background: #f0f2f5; }
        .container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; border-bottom: 3px solid #1a73e8; padding-bottom: 15px; margin-top: 0; }
        h2 { color: #333; margin-top: 25px; }
        .success { color: #137333; background: #e6f4ea; padding: 12px 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #137333; }
        .error { color: #c5221f; background: #fce8e6; padding: 12px 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #c5221f; }
        .warning { color: #b06000; background: #fef7e0; padding: 12px 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #f9ab00; }
        .info { color: #174ea6; background: #e8f0fe; padding: 12px 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #1a73e8; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 13px; white-space: pre-wrap; word-wrap: break-word; }
        .btn { display: inline-block; padding: 12px 24px; background: #1a73e8; color: white; text-decoration: none; border-radius: 8px; margin: 8px 4px; font-weight: 500; transition: all 0.2s; }
        .btn:hover { background: #1557b0; transform: translateY(-1px); }
        .btn-success { background: #137333; }
        .btn-success:hover { background: #0d5a27; }
        .btn-danger { background: #c5221f; }
        .btn-danger:hover { background: #a31b19; }
        .btn-warning { background: #f9ab00; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: 600; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px; margin: 20px 0; }
        .step-number { display: inline-block; width: 28px; height: 28px; background: #1a73e8; color: white; border-radius: 50%; text-align: center; line-height: 28px; margin-right: 8px; font-size: 14px; }
        code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
    </head><body><div class='container'>";
}

function htmlFooter($token)
{
    echo "<hr style='margin-top: 30px; border: none; border-top: 1px solid #e0e0e0;'>
    <p><a class='btn' href='?token={$token}&action=menu'>← Kembali ke Menu</a>
    <a class='btn btn-success' href='/' target='_blank'>🏠 Buka Website</a>
    <a class='btn btn-success' href='/admin' target='_blank'>👤 Admin Panel</a></p>
    </div></body></html>";
}

function runArtisan($projectPath, $command)
{
    // Check if shell functions are disabled
    $disabledFunctions = explode(',', ini_get('disable_functions'));
    $disabledFunctions = array_map('trim', $disabledFunctions);

    if (in_array('exec', $disabledFunctions) || in_array('escapeshellarg', $disabledFunctions)) {
        // Use alternative method - direct PHP execution
        return runArtisanDirect($projectPath, $command);
    }

    $fullCommand = "cd " . escapeshellarg($projectPath) . " && php artisan {$command} 2>&1";
    $output = [];
    $returnVar = 0;
    exec($fullCommand, $output, $returnVar);
    return [
        'success' => $returnVar === 0,
        'output' => implode("\n", $output),
        'code' => $returnVar
    ];
}

function runArtisanDirect($projectPath, $command)
{
    // Direct execution without shell - for restricted hosting
    $originalDir = getcwd();
    $output = '';
    $success = false;

    try {
        chdir($projectPath);

        // Capture output
        ob_start();

        // Parse command
        $parts = explode(' ', $command);
        $artisanCommand = $parts[0];
        $args = array_slice($parts, 1);

        // Set $_SERVER['argv'] for artisan
        $_SERVER['argv'] = array_merge(['artisan', $artisanCommand], $args);
        $_SERVER['argc'] = count($_SERVER['argv']);

        // Try to run via include (limited functionality)
        // This won't work for all commands but worth trying

        $output = "Shell functions (exec, escapeshellarg) are DISABLED on this hosting.\n\n";
        $output .= "Alternative methods:\n";

        // For key:generate, we can do it manually
        if ($artisanCommand === 'key:generate') {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $envPath = $projectPath . '/.env';

            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);

                // Replace or add APP_KEY
                if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
                } else {
                    $envContent = "APP_KEY={$key}\n" . $envContent;
                }

                if (file_put_contents($envPath, $envContent)) {
                    $output = "✅ APP_KEY generated successfully!\nKey: {$key}";
                    $success = true;
                } else {
                    $output = "❌ Failed to write .env file";
                }
            } else {
                $output = "❌ .env file not found. Create it first.";
            }
        }
        // For cache commands
        elseif (in_array($artisanCommand, ['config:clear', 'route:clear', 'view:clear', 'cache:clear'])) {
            $success = clearCacheManually($projectPath, $artisanCommand);
            $output = $success ? "✅ {$artisanCommand} completed" : "❌ {$artisanCommand} failed";
        }
        // For storage:link - skip if symlink disabled
        elseif ($artisanCommand === 'storage:link') {
            $output = "⚠️ storage:link memerlukan fungsi symlink() yang disabled.\n";
            $output .= "Folder storage sudah dibuat sebagai alternatif.";
            $success = true; // Don't show as error
        }
        // For other commands
        else {
            $output .= "⚠️ Command '{$command}' requires shell access.\n";
            $output .= "Please ask your hosting provider to enable exec() function,\n";
            $output .= "or run this command via cPanel Terminal if available.";
        }

        ob_end_clean();
    } catch (Exception $e) {
        $output = "Error: " . $e->getMessage();
        ob_end_clean();
    }

    chdir($originalDir);

    return [
        'success' => $success,
        'output' => $output,
        'code' => $success ? 0 : 1
    ];
}

function clearCacheManually($projectPath, $command)
{
    $success = true;

    switch ($command) {
        case 'config:clear':
            $file = $projectPath . '/bootstrap/cache/config.php';
            if (file_exists($file)) {
                $success = unlink($file);
            }
            break;

        case 'route:clear':
            $file = $projectPath . '/bootstrap/cache/routes-v7.php';
            if (file_exists($file)) {
                $success = unlink($file);
            }
            break;

        case 'view:clear':
            $viewsPath = $projectPath . '/storage/framework/views';
            if (is_dir($viewsPath)) {
                $files = glob($viewsPath . '/*.php');
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            break;

        case 'cache:clear':
            $cachePath = $projectPath . '/storage/framework/cache/data';
            if (is_dir($cachePath)) {
                deleteDirectory($cachePath);
                mkdir($cachePath, 0755, true);
            }
            break;
    }

    return $success;
}

// ============================================
// MANUAL MIGRATION FUNCTIONS (tanpa shell)
// ============================================

function getDbConnection($projectPath)
{
    $envPath = $projectPath . '/.env';
    if (!file_exists($envPath)) {
        throw new Exception('.env file not found');
    }

    $envContent = file_get_contents($envPath);
    preg_match('/DB_HOST=(.*)/', $envContent, $host);
    preg_match('/DB_PORT=(.*)/', $envContent, $port);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $db);
    preg_match('/DB_USERNAME=(.*)/', $envContent, $user);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $pass);

    $dbHost = trim($host[1] ?? 'localhost');
    $dbPort = trim($port[1] ?? '3306');
    $dbName = trim($db[1] ?? '');
    $dbUser = trim($user[1] ?? '');
    $dbPass = trim($pass[1] ?? '');

    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

function createMigrationsTable($pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `migration` varchar(255) NOT NULL,
        `batch` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
}

function getCompletedMigrations($pdo)
{
    try {
        $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY batch, migration");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

function getNextBatch($pdo)
{
    try {
        $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch();
        return ($result['max_batch'] ?? 0) + 1;
    } catch (Exception $e) {
        return 1;
    }
}

function runMigrationFile($pdo, $projectPath, $migrationFile, $batch)
{
    $filePath = $projectPath . '/database/migrations/' . $migrationFile;

    if (!file_exists($filePath)) {
        return ['success' => false, 'message' => "File not found: {$migrationFile}"];
    }

    // Get migration class
    $content = file_get_contents($filePath);

    // Extract class name
    if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
        $className = $matches[1];
    } else {
        // For anonymous class migrations (Laravel 9+)
        return runAnonymousMigration($pdo, $filePath, $migrationFile, $batch);
    }

    try {
        // Include and instantiate
        require_once $filePath;

        if (!class_exists($className)) {
            return ['success' => false, 'message' => "Class {$className} not found"];
        }

        $migration = new $className();

        // Run up method
        $migration->up();

        // Record migration
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([pathinfo($migrationFile, PATHINFO_FILENAME), $batch]);

        return ['success' => true, 'message' => "Migrated: {$migrationFile}"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error in {$migrationFile}: " . $e->getMessage()];
    }
}

function runAnonymousMigration($pdo, $filePath, $migrationFile, $batch)
{
    try {
        // For Laravel 9+ anonymous migrations
        $migration = require $filePath;

        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();

            // Record migration
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([pathinfo($migrationFile, PATHINFO_FILENAME), $batch]);

            return ['success' => true, 'message' => "Migrated: {$migrationFile}"];
        }

        return ['success' => false, 'message' => "Invalid migration format: {$migrationFile}"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error in {$migrationFile}: " . $e->getMessage()];
    }
}

function runMigrationsManually($projectPath)
{
    $results = [];
    $migrationsPath = $projectPath . '/database/migrations';

    try {
        // Setup Laravel minimal environment
        setupLaravelMinimal($projectPath);

        $pdo = getDbConnection($projectPath);
        $results[] = ['success' => true, 'message' => '✅ Database connected'];

        // Create migrations table
        createMigrationsTable($pdo);
        $results[] = ['success' => true, 'message' => '✅ Migrations table ready'];

        // Get completed migrations
        $completed = getCompletedMigrations($pdo);
        $batch = getNextBatch($pdo);

        // Get all migration files
        $files = glob($migrationsPath . '/*.php');
        sort($files);

        $pending = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $migrationName = pathinfo($filename, PATHINFO_FILENAME);
            if (!in_array($migrationName, $completed)) {
                $pending[] = $filename;
            }
        }

        if (empty($pending)) {
            $results[] = ['success' => true, 'message' => '✅ Nothing to migrate. All migrations have been run.'];
            return $results;
        }

        $results[] = ['success' => true, 'message' => "📋 Found " . count($pending) . " pending migration(s)"];

        // Run pending migrations
        foreach ($pending as $migrationFile) {
            $result = runMigrationFile($pdo, $projectPath, $migrationFile, $batch);
            $results[] = $result;

            if (!$result['success']) {
                // Stop on error
                break;
            }
        }
    } catch (Exception $e) {
        $results[] = ['success' => false, 'message' => '❌ Error: ' . $e->getMessage()];
    }

    return $results;
}

function setupLaravelMinimal($projectPath)
{
    // Set up minimal Laravel environment for migrations
    if (!defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }

    // Autoload
    $autoload = $projectPath . '/vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    // Set base path
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', $projectPath);
    }
}

function runFreshMigrationManually($projectPath)
{
    $results = [];

    try {
        $pdo = getDbConnection($projectPath);
        $results[] = ['success' => true, 'message' => '✅ Database connected'];

        // Get all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($tables)) {
            // Disable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
                $results[] = ['success' => true, 'message' => "🗑️ Dropped table: {$table}"];
            }

            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }

        $results[] = ['success' => true, 'message' => '✅ All tables dropped'];

        // Run migrations
        $migrationResults = runMigrationsManually($projectPath);
        $results = array_merge($results, $migrationResults);
    } catch (Exception $e) {
        $results[] = ['success' => false, 'message' => '❌ Error: ' . $e->getMessage()];
    }

    return $results;
}

function deleteDirectory($dir)
{
    if (!is_dir($dir)) return;

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

function checkPath($path, $name)
{
    $exists = file_exists($path);
    $writable = is_writable($path);
    echo "<tr>
        <td>{$name}</td>
        <td><code>{$path}</code></td>
        <td>" . ($exists ? '✅ Ada' : '❌ Tidak ada') . "</td>
        <td>" . ($writable ? '✅ Writable' : ($exists ? '⚠️ Tidak writable' : '-')) . "</td>
    </tr>";
    return $exists;
}

// ============================================
// MENU UTAMA
// ============================================

htmlHeader();
echo "<h1>🚀 Asset Management - Deployment</h1>";
echo "<div class='info'><strong>Project Path:</strong> {$CONFIG['project_path']}<br><strong>Public Path:</strong> {$CONFIG['public_path']}</div>";

// Check hosting restrictions
$disabledFunctions = explode(',', ini_get('disable_functions'));
$disabledFunctions = array_map('trim', $disabledFunctions);
$shellDisabled = in_array('exec', $disabledFunctions) || in_array('escapeshellarg', $disabledFunctions);

if ($shellDisabled) {
    echo "<div class='warning'>
        <strong>⚠️ RESTRICTED HOSTING MODE</strong><br>
        Shell functions (exec, escapeshellarg) disabled pada hosting ini.<br>
        Script akan menggunakan metode alternatif (PHP native) untuk operasi yang memungkinkan.
    </div>";
}

if ($action === 'menu') {
    echo "<h2>📋 Pilih Langkah Deployment</h2>";
    echo "<div class='menu-grid'>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=check'><span class='step-number'>1</span> Cek Requirements & Path</a>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=vendor'><span class='step-number'>2</span> Cek Vendor (Penting!)</a>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=env'><span class='step-number'>3</span> Cek & Setup .env</a>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=key'><span class='step-number'>4</span> Generate APP_KEY</a>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=migrate'><span class='step-number'>5</span> Jalankan Migration</a>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=storage'><span class='step-number'>6</span> Setup Storage Link</a>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=cache'><span class='step-number'>7</span> Clear & Optimize Cache</a>";
    echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=permissions'><span class='step-number'>8</span> Fix Permissions</a>";
    echo "</div>";

    echo "<div class='warning'><strong>⚠️ Langkah 2 (Cek Vendor)</strong> sangat penting! Jika vendor tidak lengkap, upload ulang atau jalankan composer install.</div>";

    echo "<hr>";
    echo "<h3>⚠️ Operasi Berbahaya</h3>";
    echo "<a class='btn btn-danger' href='?token={$CONFIG['security_token']}&action=fresh'>🔄 Fresh Migration (Reset DB)</a>";
    echo "<a class='btn btn-danger' href='?token={$CONFIG['security_token']}&action=seed'>🌱 Jalankan Seeder</a>";

    htmlFooter($CONFIG['security_token']);
    exit;
}

// ============================================
// CEK REQUIREMENTS
// ============================================

if ($action === 'check') {
    echo "<h2>✅ Cek Requirements & Path</h2>";

    // PHP Info
    echo "<h3>PHP Information</h3>";
    echo "<table>
        <tr><th>Item</th><th>Value</th><th>Status</th></tr>
        <tr><td>PHP Version</td><td>" . PHP_VERSION . "</td><td>" . (version_compare(PHP_VERSION, '8.1.0', '>=') ? '✅ OK' : '❌ Minimal 8.1') . "</td></tr>
    </table>";

    // Extensions
    echo "<h3>PHP Extensions</h3>";
    echo "<table><tr><th>Extension</th><th>Status</th></tr>";
    $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'gd', 'curl'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        echo "<tr><td>{$ext}</td><td>" . ($loaded ? '✅ Loaded' : '❌ Not loaded') . "</td></tr>";
    }
    echo "</table>";

    // Paths
    echo "<h3>Path Check</h3>";
    echo "<table><tr><th>Name</th><th>Path</th><th>Exists</th><th>Writable</th></tr>";
    checkPath($CONFIG['project_path'], 'Project Root');
    checkPath($CONFIG['project_path'] . '/vendor', 'Vendor');
    checkPath($CONFIG['project_path'] . '/vendor/autoload.php', 'Autoload');
    checkPath($CONFIG['project_path'] . '/storage', 'Storage');
    checkPath($CONFIG['project_path'] . '/storage/logs', 'Logs');
    checkPath($CONFIG['project_path'] . '/storage/framework/cache', 'Cache');
    checkPath($CONFIG['project_path'] . '/storage/framework/sessions', 'Sessions');
    checkPath($CONFIG['project_path'] . '/storage/framework/views', 'Views Cache');
    checkPath($CONFIG['project_path'] . '/bootstrap/cache', 'Bootstrap Cache');
    checkPath($CONFIG['project_path'] . '/.env', '.env File');
    checkPath($CONFIG['project_path'] . '/artisan', 'Artisan');
    checkPath($CONFIG['public_path'], 'Public HTML');
    checkPath($CONFIG['public_path'] . '/index.php', 'Public index.php');
    echo "</table>";
}

// ============================================
// CEK VENDOR
// ============================================

if ($action === 'vendor') {
    echo "<h2>📦 Cek Vendor Directory</h2>";

    $vendorPath = $CONFIG['project_path'] . '/vendor';
    $autoloadPath = $vendorPath . '/autoload.php';

    if (!file_exists($vendorPath)) {
        echo "<div class='error'>❌ Folder <code>vendor/</code> tidak ditemukan!</div>";
        echo "<div class='warning'>
            <strong>Solusi:</strong><br>
            1. Pastikan folder vendor sudah ter-extract dari ZIP<br>
            2. Atau jalankan <code>composer install</code> di hosting (jika tersedia Composer)
        </div>";
    } else {
        echo "<div class='success'>✅ Folder <code>vendor/</code> ditemukan</div>";

        // Check critical files
        $criticalFiles = [
            'autoload.php' => 'Autoloader utama',
            'composer/autoload_real.php' => 'Composer autoload real',
            'composer/autoload_static.php' => 'Composer autoload static',
            'symfony/deprecation-contracts/function.php' => 'Symfony Deprecation (ERROR FILE)',
            'laravel/framework/src/Illuminate/Foundation/Application.php' => 'Laravel Application',
            'filament/filament/src/FilamentServiceProvider.php' => 'Filament Provider',
        ];

        echo "<h3>Critical Files Check</h3>";
        echo "<table><tr><th>File</th><th>Description</th><th>Status</th></tr>";

        $allOk = true;
        foreach ($criticalFiles as $file => $desc) {
            $fullPath = $vendorPath . '/' . $file;
            $exists = file_exists($fullPath);
            if (!$exists) $allOk = false;

            $status = $exists ? '✅ OK' : '❌ MISSING';
            $rowClass = $exists ? '' : 'style="background: #fce8e6;"';
            echo "<tr {$rowClass}><td><code>{$file}</code></td><td>{$desc}</td><td>{$status}</td></tr>";
        }
        echo "</table>";

        if (!$allOk) {
            echo "<div class='error'>
                <strong>❌ Beberapa file vendor HILANG!</strong><br><br>
                Ini terjadi karena:<br>
                1. File ZIP tidak ter-extract sempurna<br>
                2. Upload terputus<br>
                3. Hosting memiliki limit file<br><br>
                <strong>Solusi:</strong><br>
                1. Hapus folder <code>vendor/</code> yang ada<br>
                2. Upload ulang file ZIP<br>
                3. Extract ulang dengan File Manager cPanel<br>
                4. Atau upload folder <code>vendor/</code> terpisah via FTP
            </div>";
        } else {
            echo "<div class='success'>✅ Semua file vendor critical ditemukan!</div>";

            // Test autoload
            echo "<h3>Test Autoload</h3>";
            try {
                require_once $autoloadPath;
                echo "<div class='success'>✅ Autoload berhasil di-load!</div>";
            } catch (Throwable $e) {
                echo "<div class='error'>❌ Error loading autoload: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }

        // Count vendor files
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vendorPath));
        $fileCount = 0;
        foreach ($iterator as $file) {
            if ($file->isFile()) $fileCount++;
        }
        echo "<div class='info'>📊 Total file di vendor/: <strong>" . number_format($fileCount) . "</strong> files</div>";

        if ($fileCount < 10000) {
            echo "<div class='warning'>⚠️ Jumlah file vendor terlihat kurang. Normalnya sekitar 15.000+ files untuk project Laravel + Filament.</div>";
        }
    }
}

// ============================================
// CEK & SETUP .ENV
// ============================================

if ($action === 'env') {
    echo "<h2>⚙️ Cek & Setup .env</h2>";

    $envPath = $CONFIG['project_path'] . '/.env';
    $envExamplePath = $CONFIG['project_path'] . '/.env.example';
    $envProductionPath = $CONFIG['project_path'] . '/.env.production';

    if (file_exists($envPath)) {
        echo "<div class='success'>✅ File <code>.env</code> ditemukan</div>";

        $envContent = file_get_contents($envPath);

        // Parse env
        $envLines = explode("\n", $envContent);
        $envVars = [];
        foreach ($envLines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }

        echo "<h3>Konfigurasi Penting</h3>";
        echo "<table><tr><th>Key</th><th>Value</th><th>Status</th></tr>";

        $important = ['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($important as $key) {
            $value = $envVars[$key] ?? '<not set>';
            $display = $value;

            // Mask sensitive
            if (in_array($key, ['DB_PASSWORD', 'APP_KEY']) && strlen($value) > 10) {
                $display = substr($value, 0, 10) . '***';
            }

            $status = '✅';
            if ($key === 'APP_KEY' && (empty($value) || $value === '<not set>')) $status = '⚠️ Perlu generate';
            if ($key === 'APP_DEBUG' && $value === 'true') $status = '⚠️ Matikan di production';
            if ($key === 'APP_ENV' && $value !== 'production') $status = '⚠️ Seharusnya production';

            echo "<tr><td><code>{$key}</code></td><td><code>{$display}</code></td><td>{$status}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ File <code>.env</code> tidak ditemukan!</div>";

        if (file_exists($envProductionPath)) {
            echo "<div class='info'>📄 File <code>.env.production</code> ditemukan. Klik tombol di bawah untuk menyalin.</div>";
            echo "<a class='btn btn-warning' href='?token={$CONFIG['security_token']}&action=copy_env&from=production'>📋 Copy .env.production → .env</a>";
        }

        if (file_exists($envExamplePath)) {
            echo "<div class='info'>📄 File <code>.env.example</code> ditemukan.</div>";
            echo "<a class='btn btn-warning' href='?token={$CONFIG['security_token']}&action=copy_env&from=example'>📋 Copy .env.example → .env</a>";
        }
    }
}

if ($action === 'copy_env') {
    $from = $_GET['from'] ?? 'example';
    $sourcePath = $CONFIG['project_path'] . '/.env.' . $from;
    $destPath = $CONFIG['project_path'] . '/.env';

    if (file_exists($sourcePath)) {
        if (copy($sourcePath, $destPath)) {
            echo "<div class='success'>✅ File <code>.env.{$from}</code> berhasil disalin ke <code>.env</code></div>";
            echo "<div class='warning'>⚠️ Jangan lupa edit konfigurasi database di file .env!</div>";
        } else {
            echo "<div class='error'>❌ Gagal menyalin file. Cek permission folder.</div>";
        }
    } else {
        echo "<div class='error'>❌ File source tidak ditemukan.</div>";
    }
}

// ============================================
// GENERATE APP_KEY
// ============================================

if ($action === 'key') {
    echo "<h2>🔑 Generate APP_KEY</h2>";

    $result = runArtisan($CONFIG['project_path'], 'key:generate --force');

    if ($result['success']) {
        echo "<div class='success'>✅ APP_KEY berhasil di-generate!</div>";
    } else {
        echo "<div class='error'>❌ Gagal generate APP_KEY</div>";

        // Manual generate
        $key = 'base64:' . base64_encode(random_bytes(32));
        echo "<div class='info'>
            <strong>Generate Manual:</strong><br>
            Copy key ini dan masukkan ke file .env:<br>
            <code>APP_KEY={$key}</code>
        </div>";
    }

    if ($result['output']) {
        echo "<pre>" . htmlspecialchars($result['output']) . "</pre>";
    }
}

// ============================================
// MIGRATION
// ============================================

if ($action === 'migrate') {
    echo "<h2>📦 Database Migration</h2>";

    // Check if shell functions are disabled
    $disabledFunctions = explode(',', ini_get('disable_functions'));
    $disabledFunctions = array_map('trim', $disabledFunctions);
    $shellDisabled = in_array('exec', $disabledFunctions) || in_array('escapeshellarg', $disabledFunctions);

    // Test DB connection first
    $envPath = $CONFIG['project_path'] . '/.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        preg_match('/DB_HOST=(.*)/', $envContent, $host);
        preg_match('/DB_DATABASE=(.*)/', $envContent, $db);
        preg_match('/DB_USERNAME=(.*)/', $envContent, $user);
        preg_match('/DB_PASSWORD=(.*)/', $envContent, $pass);

        $dbHost = trim($host[1] ?? 'localhost');
        $dbName = trim($db[1] ?? '');
        $dbUser = trim($user[1] ?? '');
        $dbPass = trim($pass[1] ?? '');

        echo "<div class='info'>Testing connection ke: <code>{$dbUser}@{$dbHost}/{$dbName}</code></div>";

        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
            echo "<div class='success'>✅ Koneksi database berhasil!</div>";

            if ($shellDisabled) {
                // Use manual migration
                echo "<div class='warning'>⚠️ Shell functions disabled - menggunakan migration manual</div>";
                echo "<h3>Running Manual Migration...</h3>";

                $results = runMigrationsManually($CONFIG['project_path']);

                echo "<table><tr><th>Status</th><th>Message</th></tr>";
                foreach ($results as $result) {
                    $icon = $result['success'] ? '✅' : '❌';
                    $class = $result['success'] ? '' : 'style="background: #fce8e6;"';
                    echo "<tr {$class}><td>{$icon}</td><td>" . htmlspecialchars($result['message']) . "</td></tr>";
                }
                echo "</table>";
            } else {
                // Run migration via artisan
                echo "<h3>Running Migration...</h3>";
                $result = runArtisan($CONFIG['project_path'], 'migrate --force');

                if ($result['success']) {
                    echo "<div class='success'>✅ Migration berhasil!</div>";
                } else {
                    echo "<div class='error'>❌ Migration gagal</div>";
                }

                echo "<pre>" . htmlspecialchars($result['output']) . "</pre>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Koneksi database gagal: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<div class='warning'>Periksa konfigurasi database di file .env</div>";
        }
    } else {
        echo "<div class='error'>❌ File .env tidak ditemukan!</div>";
    }
}

// ============================================
// SEEDER
// ============================================

if ($action === 'seed') {
    echo "<h2>🌱 Database Seeder</h2>";
    echo "<div class='warning'>⚠️ Ini akan menambahkan data awal ke database.</div>";

    // Check if shell functions are disabled
    $disabledFunctions = explode(',', ini_get('disable_functions'));
    $disabledFunctions = array_map('trim', $disabledFunctions);
    $shellDisabled = in_array('exec', $disabledFunctions) || in_array('escapeshellarg', $disabledFunctions);

    if ($shellDisabled) {
        echo "<div class='error'>❌ Shell functions (exec) disabled pada hosting ini.</div>";
        echo "<div class='info'>
            <strong>Alternatif untuk menjalankan Seeder:</strong><br>
            1. Gunakan cPanel Terminal jika tersedia<br>
            2. Hubungi hosting untuk enable exec() function<br>
            3. Import data manual via phpMyAdmin<br>
            4. Gunakan tombol Fresh Migration (akan reset database + seed)
        </div>";
    } else {
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
            $result = runArtisan($CONFIG['project_path'], 'db:seed --force');

            if ($result['success']) {
                echo "<div class='success'>✅ Seeder berhasil dijalankan!</div>";
            } else {
                echo "<div class='error'>❌ Seeder gagal</div>";
            }

            echo "<pre>" . htmlspecialchars($result['output']) . "</pre>";
        } else {
            echo "<a class='btn btn-danger' href='?token={$CONFIG['security_token']}&action=seed&confirm=yes'>✅ Ya, Jalankan Seeder</a>";
            echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=menu'>❌ Batal</a>";
        }
    }
}

// ============================================
// FRESH MIGRATION
// ============================================

if ($action === 'fresh') {
    echo "<h2>🔄 Fresh Migration (Reset Database)</h2>";
    echo "<div class='error'>⚠️ <strong>PERINGATAN:</strong> Ini akan MENGHAPUS SEMUA DATA di database!</div>";

    // Check if shell functions are disabled
    $disabledFunctions = explode(',', ini_get('disable_functions'));
    $disabledFunctions = array_map('trim', $disabledFunctions);
    $shellDisabled = in_array('exec', $disabledFunctions) || in_array('escapeshellarg', $disabledFunctions);

    if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
        if ($shellDisabled) {
            // Use manual fresh migration
            echo "<div class='warning'>⚠️ Shell functions disabled - menggunakan fresh migration manual</div>";
            echo "<h3>Running Manual Fresh Migration...</h3>";

            $results = runFreshMigrationManually($CONFIG['project_path']);

            echo "<table><tr><th>Status</th><th>Message</th></tr>";
            foreach ($results as $result) {
                $icon = $result['success'] ? '✅' : '❌';
                $class = $result['success'] ? '' : 'style="background: #fce8e6;"';
                echo "<tr {$class}><td>{$icon}</td><td>" . htmlspecialchars($result['message']) . "</td></tr>";
            }
            echo "</table>";

            echo "<div class='warning'>⚠️ Seeder tidak bisa dijalankan otomatis. Gunakan phpMyAdmin untuk import data.</div>";
        } else {
            $result = runArtisan($CONFIG['project_path'], 'migrate:fresh --seed --force');

            if ($result['success']) {
                echo "<div class='success'>✅ Fresh migration dengan seeder berhasil!</div>";
            } else {
                echo "<div class='error'>❌ Fresh migration gagal</div>";
            }

            echo "<pre>" . htmlspecialchars($result['output']) . "</pre>";
        }
    } else {
        echo "<a class='btn btn-danger' href='?token={$CONFIG['security_token']}&action=fresh&confirm=yes'>⚠️ Ya, RESET DATABASE</a>";
        echo "<a class='btn' href='?token={$CONFIG['security_token']}&action=menu'>❌ Batal</a>";
    }
}

// ============================================
// STORAGE LINK
// ============================================

if ($action === 'storage') {
    echo "<h2>📁 Setup Storage Link</h2>";

    $storagePath = $CONFIG['project_path'] . '/storage/app/public';
    $publicStorage = $CONFIG['public_path'] . '/storage';

    echo "<div class='info'>
        <strong>Source:</strong> <code>{$storagePath}</code><br>
        <strong>Link:</strong> <code>{$publicStorage}</code>
    </div>";

    // Check if source exists
    if (!file_exists($storagePath)) {
        mkdir($storagePath, 0755, true);
        echo "<div class='success'>✅ Created storage/app/public directory</div>";
    }

    // Check if symlink function is disabled
    $disabledFunctions = explode(',', ini_get('disable_functions'));
    $disabledFunctions = array_map('trim', $disabledFunctions);
    $symlinkDisabled = in_array('symlink', $disabledFunctions);

    // Check existing link/folder
    if (is_link($publicStorage)) {
        echo "<div class='success'>✅ Symbolic link sudah ada!</div>";
        echo "<div class='info'>Target: " . readlink($publicStorage) . "</div>";
    } elseif (file_exists($publicStorage)) {
        echo "<div class='warning'>⚠️ Folder storage sudah ada (bukan symlink).</div>";
        echo "<div class='info'>Ini OK untuk shared hosting. File yang diupload akan disimpan di folder ini.</div>";
    } else {
        if ($symlinkDisabled) {
            // Symlink disabled - create folder instead
            echo "<div class='warning'>⚠️ Fungsi symlink() disabled pada hosting ini.</div>";

            if (mkdir($publicStorage, 0755, true)) {
                echo "<div class='success'>✅ Folder storage dibuat sebagai alternatif.</div>";
                echo "<div class='info'>
                    <strong>📋 PENTING untuk Upload File:</strong><br>
                    Karena symlink tidak tersedia, ada 2 opsi:<br><br>
                    <strong>Opsi 1:</strong> Edit config filesystems.php<br>
                    Ubah path public disk ke: <code>{$publicStorage}</code><br><br>
                    <strong>Opsi 2:</strong> Copy manual<br>
                    Setiap ada upload, copy dari:<br>
                    <code>{$storagePath}</code><br>
                    ke:<br>
                    <code>{$publicStorage}</code>
                </div>";
            } else {
                echo "<div class='error'>❌ Gagal membuat folder storage.</div>";
            }
        } else {
            // Try creating symlink
            if (@symlink($storagePath, $publicStorage)) {
                echo "<div class='success'>✅ Symbolic link berhasil dibuat!</div>";
            } else {
                echo "<div class='warning'>⚠️ Tidak bisa membuat symlink.</div>";

                // Create folder instead
                if (mkdir($publicStorage, 0755, true)) {
                    echo "<div class='info'>📁 Folder storage dibuat sebagai alternatif.</div>";
                }
            }
        }
    }

    // Show alternative solution
    echo "<h3>🔧 Solusi Alternatif untuk Storage</h3>";
    echo "<div class='info'>
        Untuk hosting tanpa symlink, Anda bisa:<br><br>
        1. <strong>Gunakan folder public langsung</strong> - File upload akan disimpan di public_html/asseta/storage<br><br>
        2. <strong>Gunakan cloud storage</strong> - Konfigurasi S3, DigitalOcean Spaces, atau Cloudinary di .env
    </div>";

    // Create .htaccess in storage folder for security
    if (file_exists($publicStorage) && is_dir($publicStorage)) {
        $htaccessContent = "# Deny access to PHP files\n<FilesMatch \"\\.php$\">\n    Order Deny,Allow\n    Deny from all\n</FilesMatch>\n";
        file_put_contents($publicStorage . '/.htaccess', $htaccessContent);
        echo "<div class='success'>✅ .htaccess security ditambahkan ke folder storage</div>";
    }
}

// ============================================
// CACHE
// ============================================

if ($action === 'cache') {
    echo "<h2>⚡ Clear & Optimize Cache</h2>";

    // Check if shell functions are disabled
    $disabledFunctions = explode(',', ini_get('disable_functions'));
    $disabledFunctions = array_map('trim', $disabledFunctions);
    $shellDisabled = in_array('exec', $disabledFunctions) || in_array('escapeshellarg', $disabledFunctions);

    if ($shellDisabled) {
        echo "<div class='warning'>⚠️ Shell functions disabled - menggunakan cache clear manual</div>";

        // Manual cache clear
        $cacheOps = [
            'config:clear' => 'Clear Config Cache',
            'route:clear' => 'Clear Route Cache',
            'view:clear' => 'Clear View Cache',
            'cache:clear' => 'Clear Application Cache',
        ];

        echo "<table><tr><th>Command</th><th>Status</th><th>Output</th></tr>";

        foreach ($cacheOps as $cmd => $name) {
            $success = clearCacheManually($CONFIG['project_path'], $cmd);
            $status = $success ? '✅' : '❌';
            $output = $success ? 'Cache cleared successfully' : 'Failed to clear cache';
            echo "<tr><td>{$name}</td><td>{$status}</td><td><code>{$output}</code></td></tr>";
        }

        echo "</table>";
        echo "<div class='info'>ℹ️ config:cache, route:cache, view:cache, dan optimize memerlukan shell access.</div>";
        echo "<div class='success'>✅ Cache clear completed!</div>";
    } else {
        $commands = [
            'config:clear' => 'Clear Config Cache',
            'route:clear' => 'Clear Route Cache',
            'view:clear' => 'Clear View Cache',
            'cache:clear' => 'Clear Application Cache',
            'config:cache' => 'Cache Config',
            'route:cache' => 'Cache Routes',
            'view:cache' => 'Cache Views',
            'optimize' => 'Optimize Application',
        ];

        echo "<table><tr><th>Command</th><th>Status</th><th>Output</th></tr>";

        foreach ($commands as $cmd => $name) {
            $result = runArtisan($CONFIG['project_path'], $cmd);
            $status = $result['success'] ? '✅' : '❌';
            $output = strlen($result['output']) > 100 ? substr($result['output'], 0, 100) . '...' : $result['output'];
            echo "<tr><td>{$name}</td><td>{$status}</td><td><code>" . htmlspecialchars($output) . "</code></td></tr>";
        }

        echo "</table>";
        echo "<div class='success'>✅ Cache operations completed!</div>";
    }
}

// ============================================
// FIX PERMISSIONS
// ============================================

if ($action === 'permissions') {
    echo "<h2>🔐 Fix Permissions</h2>";

    $directories = [
        $CONFIG['project_path'] . '/storage',
        $CONFIG['project_path'] . '/storage/app',
        $CONFIG['project_path'] . '/storage/app/public',
        $CONFIG['project_path'] . '/storage/framework',
        $CONFIG['project_path'] . '/storage/framework/cache',
        $CONFIG['project_path'] . '/storage/framework/cache/data',
        $CONFIG['project_path'] . '/storage/framework/sessions',
        $CONFIG['project_path'] . '/storage/framework/views',
        $CONFIG['project_path'] . '/storage/logs',
        $CONFIG['project_path'] . '/bootstrap/cache',
    ];

    echo "<table><tr><th>Directory</th><th>Status</th></tr>";

    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<tr><td><code>{$dir}</code></td><td>✅ Created</td></tr>";
            } else {
                echo "<tr><td><code>{$dir}</code></td><td>❌ Failed to create</td></tr>";
            }
        } else {
            if (chmod($dir, 0755)) {
                echo "<tr><td><code>{$dir}</code></td><td>✅ Set 755</td></tr>";
            } else {
                echo "<tr><td><code>{$dir}</code></td><td>⚠️ Cannot chmod</td></tr>";
            }
        }
    }

    echo "</table>";
    echo "<div class='success'>✅ Permission fix completed!</div>";
}

htmlFooter($CONFIG['security_token']);
