<?php
require_once '../includes/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO fields (name, type, hours, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['type'],
        $_POST['hours'],
        $_POST['price']
    ]);
    header('Location: list_fields.php');
    exit;
}
?>

<div class="container">
    <h2>➕ Thêm sân thể thao</h2>
    <form method="post">
        <label>Tên sân:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Loại sân:</label><br>
        <input type="text" name="type" required><br><br>

        <label>Giờ hoạt động:</label><br>
        <input type="text" name="hours" required><br><br>

        <label>Giá thuê / giờ (VNĐ):</label><br>
        <input type="number" name="price" required><br><br>

        <button class="btn">Lưu thông tin</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
