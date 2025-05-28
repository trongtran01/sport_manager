<?php
require_once '../includes/db.php';
include '../includes/header.php';

// Lấy danh sách sân
$fields = $pdo->query("SELECT * FROM fields ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Xử lý đặt sân
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO bookings (field_id, customer_name, booking_date, time_slot) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['field_id'],
        $_POST['customer_name'],
        $_POST['booking_date'],
        $_POST['time_slot']
    ]);
}

// Lấy danh sách đặt sân
$stmt = $pdo->query("
    SELECT b.*, f.name AS field_name 
    FROM bookings b 
    JOIN fields f ON b.field_id = f.id 
    ORDER BY booking_date DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>📆 Đặt sân</h2>
    <form method="post">
        <label>Chọn sân:</label><br>
        <select name="field_id" required>
            <option value="">-- Chọn --</option>
            <?php foreach ($fields as $field): ?>
                <option value="<?= $field['id'] ?>"><?= htmlspecialchars($field['name']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Tên khách hàng:</label><br>
        <input type="text" name="customer_name" required><br><br>

        <label>Ngày đặt:</label><br>
        <input type="date" name="booking_date" required><br><br>

        <label>Khung giờ (VD: 17h-18h):</label><br>
        <input type="text" name="time_slot" required><br><br>

        <button class="btn">Đặt sân</button>
    </form>

    <h3 style="margin-top:30px">🗓️ Danh sách đặt sân</h3>
    <?php if (empty($bookings)): ?>
        <p>Chưa có lượt đặt sân nào.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>Sân</th>
                <th>Khách hàng</th>
                <th>Ngày</th>
                <th>Khung giờ</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['field_name']) ?></td>
                    <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                    <td><?= $booking['booking_date'] ?></td>
                    <td><?= htmlspecialchars($booking['time_slot']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
