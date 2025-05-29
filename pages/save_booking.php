<?php
/**
 * API xử lý đặt sân
 * Nhận dữ liệu JSON và lưu booking vào database
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Chỉ cho phép POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Kiểm tra AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once '../includes/db.php';

try {
    // Lấy dữ liệu JSON từ request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate dữ liệu đầu vào
    $validation_result = validateBookingData($data);
    if (!$validation_result['valid']) {
        echo json_encode(['success' => false, 'message' => $validation_result['message']]);
        exit;
    }
    
    $field_id = (int)$data['field_id'];
    $date = $data['date'];
    $slots = $data['slots'];
    $customer_name = trim($data['customer_name']);
    $customer_phone = trim($data['customer_phone']);
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // Kiểm tra sân có tồn tại không
        $field_check = $conn->prepare("SELECT id, name FROM fields WHERE id = ?");
        $field_check->bind_param("i", $field_id);
        $field_check->execute();
        $field_result = $field_check->get_result();
        
        if ($field_result->num_rows === 0) {
            throw new Exception('Sân không tồn tại');
        }
        
        $field_info = $field_result->fetch_assoc();
        
        // Kiểm tra các slot có bị trùng không
        $conflicts = checkSlotConflicts($conn, $field_id, $date, $slots);
        if (!empty($conflicts)) {
            throw new Exception('Các khung giờ sau đã được đặt: ' . implode(', ', $conflicts));
        }
        
        // Lưu từng booking slot
        $booking_ids = [];
        $stmt = $conn->prepare("INSERT INTO bookings (field_id, date, start_time, end_time, customer_name, customer_phone, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        foreach ($slots as $slot) {
            $time_parts = explode(' - ', $slot);
            if (count($time_parts) !== 2) {
                throw new Exception('Định dạng khung giờ không hợp lệ: ' . $slot);
            }
            
            $start_time = $time_parts[0] . ':00';
            $end_time = $time_parts[1] . ':00';
            
            $stmt->bind_param("isssss", $field_id, $date, $start_time, $end_time, $customer_name, $customer_phone);
            
            if (!$stmt->execute()) {
                throw new Exception('Lỗi khi lưu booking: ' . $stmt->error);
            }
            
            $booking_ids[] = $conn->insert_id;
        }
        
        // Commit transaction
        $conn->commit();
        
        // Log booking thành công
        logBookingActivity($field_info['name'], $customer_name, $customer_phone, $slots, $date);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đặt sân thành công',
            'booking_ids' => $booking_ids,
            'field_name' => $field_info['name'],
            'slots_count' => count($slots)
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

/**
 * Validate dữ liệu booking
 */
function validateBookingData($data) {
    if (!isset($data['field_id']) || !is_numeric($data['field_id']) || $data['field_id'] <= 0) {
        return ['valid' => false, 'message' => 'ID sân không hợp lệ'];
    }
    
    if (!isset($data['date']) || !validateDate($data['date'])) {
        return ['valid' => false, 'message' => 'Ngày không hợp lệ'];
    }
    
    if (!isset($data['slots']) || !is_array($data['slots']) || empty($data['slots'])) {
        return ['valid' => false, 'message' => 'Vui lòng chọn ít nhất một khung giờ'];
    }
    
    if (count($data['slots']) > 10) {
        return ['valid' => false, 'message' => 'Không thể đặt quá 10 khung giờ cùng lúc'];
    }
    
    if (!isset($data['customer_name']) || empty(trim($data['customer_name']))) {
        return ['valid' => false, 'message' => 'Vui lòng nhập họ tên'];
    }
    
    if (strlen(trim($data['customer_name'])) < 2) {
        return ['valid' => false, 'message' => 'Họ tên phải có ít nhất 2 ký tự'];
    }
    
    if (!isset($data['customer_phone']) || empty(trim($data['customer_phone']))) {
        return ['valid' => false, 'message' => 'Vui lòng nhập số điện thoại'];
    }
    
    if (!preg_match('/^[0-9]{9,15}$/', trim($data['customer_phone']))) {
        return ['valid' => false, 'message' => 'Số điện thoại không hợp lệ (9-15 chữ số)'];
    }
    
    // Validate từng slot
    foreach ($data['slots'] as $slot) {
        if (!validateTimeSlot($slot)) {
            return ['valid' => false, 'message' => 'Khung giờ không hợp lệ: ' . $slot];
        }
    }
    
    // Kiểm tra ngày đặt không được trong quá khứ
    if (strtotime($data['date']) < strtotime('today')) {
        return ['valid' => false, 'message' => 'Không thể đặt sân cho ngày trong quá khứ'];
    }
    
    return ['valid' => true];
}

/**
 * Validate định dạng ngày
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate định dạng khung giờ
 */
function validateTimeSlot($slot) {
    if (!preg_match('/^\d{2}:\d{2} - \d{2}:\d{2}$/', $slot)) {
        return false;
    }
    
    $parts = explode(' - ', $slot);
    if (count($parts) !== 2) {
        return false;
    }
    
    $start_time = strtotime($parts[0]);
    $end_time = strtotime($parts[1]);
    
    // Kiểm tra giờ hợp lệ và end_time > start_time
    return $start_time !== false && $end_time !== false && $end_time > $start_time;
}

/**
 * Kiểm tra slot bị conflict
 */
function checkSlotConflicts($conn, $field_id, $date, $slots) {
    $conflicts = [];
    
    $stmt = $conn->prepare("SELECT start_time, end_time FROM bookings WHERE field_id = ? AND date = ?");
    $stmt->bind_param("is", $field_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $existing_slots = [];
    while ($row = $result->fetch_assoc()) {
        $slot = date("H:i", strtotime($row['start_time'])) . " - " . date("H:i", strtotime($row['end_time']));
        $existing_slots[] = $slot;
    }
    
    foreach ($slots as $slot) {
        if (in_array($slot, $existing_slots)) {
            $conflicts[] = $slot;
        }
    }
    
    return $conflicts;
}

/**
 * Log hoạt động booking
 */
function logBookingActivity($field_name, $customer_name, $customer_phone, $slots, $date) {
    $log_message = sprintf(
        "[BOOKING] Field: %s | Customer: %s (%s) | Date: %s | Slots: %s",
        $field_name,
        $customer_name,
        $customer_phone,
        $date,
        implode(', ', $slots)
    );
    
    error_log($log_message);
    
    // Có thể thêm vào database log table nếu cần
    // insertBookingLog($field_name, $customer_name, $customer_phone, $slots, $date);
}

/**
 * Sanitize output để tránh XSS
 */
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}