<?php
/**
 * Migration V3.1 - Update Structure & Enum
 */
require_once __DIR__ . '/../config/database.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$db = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}", $dbConfig['username'], $dbConfig['password']);

echo "🚀 Memulai Update Database Sesuai Skenario PDF...\n";

try {
    // 1. Tambah Kolom Skenario 2 (PJ & Pelaksana)
    $cols = $db->query("SHOW COLUMNS FROM usulan_kegiatan")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('penanggung_jawab', $cols)) {
        $db->exec("ALTER TABLE usulan_kegiatan ADD COLUMN penanggung_jawab VARCHAR(255) NULL AFTER user_id");
        echo "   ➕ Kolom 'penanggung_jawab' ditambahkan.\n";
    }
    if (!in_array('pelaksana_kegiatan', $cols)) {
        $db->exec("ALTER TABLE usulan_kegiatan ADD COLUMN pelaksana_kegiatan TEXT NULL AFTER penanggung_jawab");
        echo "   ➕ Kolom 'pelaksana_kegiatan' ditambahkan.\n";
    }

    // 2. Update ENUM Status (Menambah 'Disetujui Verifikator')
    // Ini penting agar sistem tahu kapan saatnya user upload surat
    $db->exec("ALTER TABLE usulan_kegiatan MODIFY COLUMN status_terkini 
        ENUM('Draft','Verifikasi','Revisi','Disetujui Verifikator','Menunggu WD2','Menunggu PPK','Disetujui','Pencairan','LPJ','Selesai','Terlambat','Ditolak') 
        NOT NULL DEFAULT 'Draft'");
    echo "   ✅ ENUM Status diperbarui (Added: 'Disetujui Verifikator').\n";

    // 3. Re-Apply Trigger & Procedure (Safety)
    $db->exec("DROP TRIGGER IF EXISTS after_rab_insert");
    $db->exec("CREATE TRIGGER after_rab_insert AFTER INSERT ON rab_detail FOR EACH ROW BEGIN UPDATE usulan_kegiatan SET nominal_pencairan = (SELECT SUM(volume*harga_satuan) FROM rab_detail WHERE usulan_id=NEW.usulan_id) WHERE id=NEW.usulan_id; END");
    
    $db->exec("DROP TRIGGER IF EXISTS after_rab_update");
    $db->exec("CREATE TRIGGER after_rab_update AFTER UPDATE ON rab_detail FOR EACH ROW BEGIN UPDATE usulan_kegiatan SET nominal_pencairan = (SELECT SUM(volume*harga_satuan) FROM rab_detail WHERE usulan_id=NEW.usulan_id) WHERE id=NEW.usulan_id; END");
    
    $db->exec("DROP TRIGGER IF EXISTS after_rab_delete");
    $db->exec("CREATE TRIGGER after_rab_delete AFTER DELETE ON rab_detail FOR EACH ROW BEGIN UPDATE usulan_kegiatan SET nominal_pencairan = (SELECT COALESCE(SUM(volume*harga_satuan),0) FROM rab_detail WHERE usulan_id=OLD.usulan_id) WHERE id=OLD.usulan_id; END");
    
    echo "✅ Migration Selesai.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}