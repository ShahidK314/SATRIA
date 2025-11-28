<?php
/**
 * Migration Script untuk Update Database ke Versi 2.0
 * Dapat dijalankan berulang tanpa error.
 */

require_once __DIR__ . '/../config/database.php';

$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

echo "🚀 Memulai Migration Database ke V2.0...\n\n";

/**
 * Helper untuk menambahkan kolom jika belum ada
 */
function addColumnIfNotExists($db, $table, $column, $definition)
{
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE table_schema = DATABASE()
        AND table_name = ?
        AND column_name = ?
    ");
    $stmt->execute([$table, $column]);

    if ($stmt->fetchColumn() == 0) {
        $db->exec("ALTER TABLE $table ADD COLUMN $column $definition");
        echo "   ➕ Menambah kolom $column pada tabel $table\n";
    } else {
        echo "   ✔ Kolom $column sudah ada pada tabel $table, skip...\n";
    }
}

try {

    // -----------------------------------------
    // 1. Update tabel usulan_kegiatan
    // -----------------------------------------
    echo "📝 Update tabel usulan_kegiatan...\n";

    addColumnIfNotExists($db, "usulan_kegiatan", "indikator_kinerja", "JSON NULL");
    addColumnIfNotExists($db, "usulan_kegiatan", "tanggal_mulai", "DATE NULL");
    addColumnIfNotExists($db, "usulan_kegiatan", "tanggal_selesai", "DATE NULL");

    $db->exec("
        ALTER TABLE usulan_kegiatan 
            MODIFY COLUMN metode_pelaksanaan JSON NULL,
            MODIFY COLUMN tahapan_pelaksanaan JSON NULL
    ");
    echo "   ✔ Modify kolom metode_pelaksanaan & tahapan_pelaksanaan\n";


    // -----------------------------------------
    // 2. Update tabel tor_iku
    // -----------------------------------------
    echo "📊 Update tabel tor_iku...\n";
    addColumnIfNotExists($db, "tor_iku", "bobot_persen", "DECIMAL(5,2) DEFAULT 0");


    // -----------------------------------------
    // 3. Update tabel master_iku
    // -----------------------------------------
    echo "🗂️ Update tabel master_iku...\n";

    addColumnIfNotExists($db, "master_iku", "status", "ENUM('active','inactive') DEFAULT 'active'");
    addColumnIfNotExists($db, "master_iku", "created_at", "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    addColumnIfNotExists($db, "master_iku", "updated_at", "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");


    // -----------------------------------------
    // 4. Stored Function
    // -----------------------------------------
    echo "⚙️ Membuat stored function calculate_category_total...\n";

    $db->exec("DROP FUNCTION IF EXISTS calculate_category_total");
    $db->exec("
        CREATE FUNCTION calculate_category_total(p_usulan_id INT, p_kategori_id INT)
        RETURNS DECIMAL(18,2)
        DETERMINISTIC
        BEGIN
            DECLARE total DECIMAL(18,2);
            SELECT COALESCE(SUM(volume * harga_satuan), 0)
            INTO total
            FROM rab_detail
            WHERE usulan_id = p_usulan_id AND kategori_id = p_kategori_id;
            RETURN total;
        END
    ");
    echo "   ✔ Function calculate_category_total dibuat ulang\n";


    echo "⚙️ Membuat stored function calculate_grand_total...\n";

    $db->exec("DROP FUNCTION IF EXISTS calculate_grand_total");
    $db->exec("
        CREATE FUNCTION calculate_grand_total(p_usulan_id INT)
        RETURNS DECIMAL(18,2)
        DETERMINISTIC
        BEGIN
            DECLARE total DECIMAL(18,2);
            SELECT COALESCE(SUM(volume * harga_satuan), 0)
            INTO total
            FROM rab_detail
            WHERE usulan_id = p_usulan_id;
            RETURN total;
        END
    ");
    echo "   ✔ Function calculate_grand_total dibuat ulang\n";


    // -----------------------------------------
    // 5. Trigger
    // -----------------------------------------
    echo "🔧 Membuat trigger auto update nominal...\n";

    $db->exec("DROP TRIGGER IF EXISTS after_rab_insert");
    $db->exec("
        CREATE TRIGGER after_rab_insert
        AFTER INSERT ON rab_detail
        FOR EACH ROW
        BEGIN
            UPDATE usulan_kegiatan 
            SET nominal_pencairan = calculate_grand_total(NEW.usulan_id)
            WHERE id = NEW.usulan_id;
        END
    ");

    $db->exec("DROP TRIGGER IF EXISTS after_rab_update");
    $db->exec("
        CREATE TRIGGER after_rab_update
        AFTER UPDATE ON rab_detail
        FOR EACH ROW
        BEGIN
            UPDATE usulan_kegiatan 
            SET nominal_pencairan = calculate_grand_total(NEW.usulan_id)
            WHERE id = NEW.usulan_id;
        END
    ");

    $db->exec("DROP TRIGGER IF EXISTS after_rab_delete");
    $db->exec("
        CREATE TRIGGER after_rab_delete
        AFTER DELETE ON rab_detail
        FOR EACH ROW
        BEGIN
            UPDATE usulan_kegiatan 
            SET nominal_pencairan = calculate_grand_total(OLD.usulan_id)
            WHERE id = OLD.usulan_id;
        END
    ");
    echo "   ✔ Semua trigger dibuat ulang\n";


    // -----------------------------------------
    // 6. Stored Procedure
    // -----------------------------------------
    echo "✅ Membuat stored procedure validate_iku_weights...\n";

    $db->exec("DROP PROCEDURE IF EXISTS validate_iku_weights");
    $db->exec("
        CREATE PROCEDURE validate_iku_weights(IN p_usulan_id INT, OUT is_valid BOOLEAN)
        BEGIN
            DECLARE total_weight DECIMAL(5,2);
            SELECT COALESCE(SUM(bobot_persen),0)
            INTO total_weight
            FROM tor_iku 
            WHERE usulan_id = p_usulan_id;

            SET is_valid = (total_weight = 100.00);
        END
    ");
    echo "   ✔ Stored procedure dibuat ulang\n";


    echo "\n✅ Migration berhasil tanpa error!\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
