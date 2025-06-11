<?php
$baseUrl = "/Sport_Manager";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sân Zone 9</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="/Sport_Manager/">
            <img src="/Sport_Manager/asset/img/logo.png" alt="Logo">
        </a>
    </div>
    <h1>Hệ thống quản lý sân bóng Zone 9</h1>
    
    <div class="login-section">
        <?php if (isset($_SESSION['username'])): ?>
            <p>Xin chào, <strong class="admin-name"><?= htmlspecialchars($_SESSION['username']) ?></strong> | 
            <a class="logout-link" href="<?= $baseUrl ?>/logout.php">Đăng xuất</a>
        <?php else: ?>
            <form action="login.php" method="post">
                <input type="text" name="username" placeholder="Tài khoản admin" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <button type="submit">Đăng nhập</button>
                <!-- <a href="logout.php">Đăng xuất</a></p> -->
            </form>
        <?php endif; ?>
    </div>
</header>
