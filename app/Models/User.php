<?php
// app/Models/User.php (FIXED: Invalid parameter number)
namespace App\Models;

use PDO;

class User
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findByUsernameOrEmail($usernameOrEmail)
    {
     
        $stmt = $this->db->prepare("SELECT * FROM users WHERE (username = :username OR email = :email) AND is_active = 1 LIMIT 1");
        
        // Bind value dua kali ke dua named parameter yang berbeda atau menggunakan array execute yang eksplisit
        $stmt->execute([
            'username' => $usernameOrEmail,
            'email' => $usernameOrEmail
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}