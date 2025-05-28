<?php
$host = 'localhost';
$db   = 'sport_manager';
$user = 'root';
$pass = ''; // Đổi nếu bạn có mật khẩu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối DB: " . $e->getMessage());
}
