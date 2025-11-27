<?php
// 1. Masukkan password yang ingin Anda hash
$passwordPlain = "admin123";

// 2. Buat hash menggunakan algoritma BCRYPT
// Cost default biasanya adalah 10, yang sudah cukup aman.
$hash = password_hash($passwordPlain, PASSWORD_BCRYPT);

// 3. Tampilkan hasilnya
echo "Password Asli: " . $passwordPlain . "<br>";
echo "Bcrypt Hash: " . $hash;
?>