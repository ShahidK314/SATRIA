<?php
/**
 * Fresh Installation Script
 * Jalankan untuk setup database baru: php database/install.php
 */

require_once __DIR__ . '/../config/database.php';

$dbConfig = require __DIR__ . '/../config/database.php';

echo "🎯 SATRIA Database Installer V2.0\n";
echo "==================================\n\n";

try {
    // Connect tanpa database dulu
    $dsn = "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "📡 Koneksi ke MySQL berhasil!\n";
    
    // Drop & Create Database
    $dbName = $dbConfig['dbname'];
    echo "🗄️ Membuat database '$dbName'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
    $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    
    // Execute SQL File
    $sqlFile = __DIR__ . '/structure_and_seed.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("File structure_and_seed.sql tidak ditemukan!");
    }
    
    echo "📥 Menjalankan struktur tabel...\n";
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon dan execute
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($stmt) => !empty($stmt) && strpos($stmt, '--') !== 0
    );
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    // Run migration untuk fitur V2
    echo "🔧 Menjalankan migration V2...\n";
    require __DIR__ . '/migration_v2.php';
    
    echo "\n✅ Instalasi selesai!\n";
    echo "👤 Akun default:\n";
    echo "   - Admin: admin / admin123\n";
    echo "   - Pengusul: pengusul_demo / demo1\n";
    echo "   - Verifikator: verifikator_demo / demo2\n";
    echo "   - WD2: wd2_demo / demo3\n";
    echo "   - PPK: ppk_demo / demo4\n";
    echo "   - Bendahara: bendahara_demo / demo5\n";
    echo "   - Direktur: direktur_demo / demo6\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}