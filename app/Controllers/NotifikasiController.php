<?php
// app/Controllers/NotifikasiController.php
namespace App\Controllers;

use PDO;

class NotifikasiController
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index($page = 1, $perPage = 10)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $offset = ($page - 1) * $perPage;

        // Ambil notifikasi belum dibaca
        $sql = "SELECT *
                FROM notifikasi
                WHERE user_id = :uid AND is_read = 0
                ORDER BY created_at DESC
                LIMIT :offset, :perPage";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->execute();
        $notifikasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hitung total notifikasi belum dibaca
        $countSql = "SELECT COUNT(*)
                     FROM notifikasi
                     WHERE user_id = :uid AND is_read = 0";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute([':uid' => $userId]);
        $total = $countStmt->fetchColumn();

        // ❗ FIX: totalPages DIBUAT
        $totalPages = max(1, ceil($total / $perPage));

        require __DIR__ . '/../Views/notifikasi/index.php';
    }

    public function read($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $link = $_GET['link'] ?? '/notifikasi';

        try {
            // Hapus notifikasi ketika dibaca
            $stmt = $this->db->prepare("
                DELETE FROM notifikasi
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([
                'id' => $id,
                'uid' => $userId
            ]);
        } catch (\Exception $e) {
            // Jika gagal, tetap lanjut
        }

        header("Location: $link");
        exit;
    }
}
