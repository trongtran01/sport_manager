<?php
require_once '../includes/db.php';
include '../includes/header.php';

$stmt = $pdo->query("SELECT * FROM fields ORDER BY created_at DESC");
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>📋 Danh sách sân thể thao</h2>
    <?php if (empty($fields)): ?>
        <p>Chưa có sân nào được thêm.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Tên sân</th>
                <th>Loại sân</th>
                <th>Giờ hoạt động</th>
                <th>Giá / giờ</th>
            </tr>
            <?php foreach ($fields as $field): ?>
                <tr>
                    <td><?= htmlspecialchars($field['name']) ?></td>
                    <td><?= htmlspecialchars($field['type']) ?></td>
                    <td><?= htmlspecialchars($field['hours']) ?></td>
                    <td><?= number_format($field['price']) ?> đ</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
