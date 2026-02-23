<?php
// config/database.php (FINALIZED)

// Variabel Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'satria');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Fungsi untuk membuat koneksi PDO ke database.
 * @return PDO
 */
function connectDB() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}