<?php
// app/Models/StatsModel.php
namespace App\Models;

use PDO;

class StatsModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Mengambil statistik dashboard yang melibatkan Pengajuan/Pencairan/LPJ.
     */
    public function getGlobalDashboardStats()
    {
        // [PERBAIKAN] total_usulan: Hanya menghitung yang statusnya BUKAN 'Draft'
        $sql = "SELECT 
                    COALESCE((SELECT COUNT(*) FROM usulan_kegiatan WHERE status_terkini != 'Draft'), 0) as total_usulan,
                    COALESCE((SELECT COUNT(*) FROM pengajuan_kegiatan WHERE TRIM(status_ppk) = 'Menunggu'), 0) as menunggu_ppk,
                    COALESCE((SELECT COUNT(*) FROM pengajuan_kegiatan WHERE TRIM(status_ppk) = 'Disetujui' AND TRIM(status_wd2) = 'Menunggu'), 0) as menunggu_wd2,
                    
                    COALESCE((SELECT COUNT(*) FROM pengajuan_kegiatan WHERE TRIM(status_ppk) = 'Disetujui' AND TRIM(status_wd2) = 'Disetujui'), 0) as disetujui_total,
                    
                    -- Dana Cair (Berdasarkan Transfer Bendahara)
                    (SELECT COALESCE(SUM(nominal_dicairkan), 0) FROM pencairan_dana) as total_dana_cair,
                    
                    -- Dana Terserap (Berdasarkan Bukti LPJ yang Disetujui)
                    (SELECT COALESCE(SUM(ld.nominal), 0) 
                     FROM lpj_dokumen ld 
                     JOIN lpj l ON ld.lpj_id = l.id 
                     WHERE l.status_terkini = 'Disetujui') as total_dana_lpj_disetujui,
                     
                    COALESCE((SELECT COUNT(*) FROM lpj WHERE status_terkini='Diajukan'), 0) as menunggu_verif_lpj
                FROM DUAL";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getFullyDisbursedCount()
    {
        $sql = "SELECT COUNT(p.id)
                FROM pengajuan_kegiatan p
                JOIN usulan_kegiatan u ON p.usulan_id = u.id
                WHERE p.status_ppk = 'Disetujui' AND p.status_wd2 = 'Disetujui'
                AND u.nominal_pencairan > 0
                AND (SELECT COALESCE(SUM(pc.nominal_dicairkan), 0) FROM pencairan_dana pc WHERE pc.pengajuan_id = p.id) >= u.nominal_pencairan - 1"; // Toleransi
        return $this->db->query($sql)->fetchColumn();
    }

    /**
     * Mengambil daftar kegiatan yang terlambat LPJ-nya.
     */
    public function getOverdueItems()
    {
        // Mencari pengajuan kegiatan yang sudah dicairkan dan melewati batas LPJ
        $sql = "SELECT u.nama_kegiatan, pc.tanggal_batas_lpj, u.id as usulan_id, us.username, us.email 
                FROM pencairan_dana pc
                JOIN pengajuan_kegiatan p ON pc.pengajuan_id = p.id
                JOIN usulan_kegiatan u ON p.usulan_id = u.id
                JOIN users us ON u.user_id = us.id
                LEFT JOIN lpj l ON l.pencairan_id = pc.id
                WHERE u.status_terkini != 'Disetujui' 
                AND pc.tanggal_batas_lpj IS NOT NULL 
                AND pc.tanggal_batas_lpj < CURDATE()
                AND l.status_terkini != 'Disetujui'
                ORDER BY pc.tanggal_batas_lpj ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}