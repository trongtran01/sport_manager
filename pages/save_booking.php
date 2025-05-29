<?php
// save_booking.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$field_id = (int)($data['field_id'] ?? 0);
$date = $data['date'] ?? '';
$slots = $data['slots'] ?? [];
$customer_name = trim($data['customer_name'] ?? '');
$customer_phone = trim($data['customer_phone'] ?? '');

if (!$field_id || !$date || !$slots || !$customer_name || !$customer_phone) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
    exit;
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO bookings (field_id, date, start_time, end_time, customer_name, customer_phone) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    foreach ($slots as $slot) {
        // Chia slot thành start_time và end_time
        $parts = explode(' - ', $slot);
        if (count($parts) != 2) throw new Exception("Slot không đúng định dạng");

        $start_time = $parts[0] . ":00";
        $end_time = $parts[1] . ":00";

        // Kiểm tra slot đã có người đặt chưa
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE field_id = ? AND date = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (start_time >= ? AND end_time <= ?))");
        if (!$checkStmt) throw new Exception("Prepare failed: " . $conn->error);

        $checkStmt->bind_param("isssssss", $field_id, $date, $end_time, $end_time, $start_time, $start_time, $start_time, $end_time);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            throw new Exception("Khung giờ $slot đã được đặt trước đó.");
        }

        // Thêm booking
        $stmt->bind_param("isssss", $field_id, $date, $start_time, $end_time, $customer_name, $customer_phone);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $ex) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
