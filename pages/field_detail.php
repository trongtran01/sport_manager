<?php
require_once '../includes/db.php';
include '../includes/header.php';

$field_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$field_id) {
    echo "<p style='padding:20px'>Không tìm thấy sân!</p>";
    include '../includes/footer.php';
    exit;
}

// Lấy thông tin sân
$stmt = $conn->prepare("SELECT * FROM fields WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $field_id);
$stmt->execute();
$result = $stmt->get_result();
$field = $result->fetch_assoc();
if (!$field) {
    echo "<p style='padding:20px'>Sân không tồn tại!</p>";
    include '../includes/footer.php';
    exit;
}

// Lấy danh sách giờ đã đặt + thông tin người đặt của sân hôm nay
$booked_slots = [];
$today = date('Y-m-d');

$booked_stmt = $conn->prepare("SELECT id, start_time, end_time, customer_name, customer_phone FROM bookings WHERE field_id = ? AND date = ?");
if (!$booked_stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$booked_stmt->bind_param("is", $field_id, $today);
$booked_stmt->execute();
$result = $booked_stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $slot = date("H:i", strtotime($row['start_time'])) . " - " . date("H:i", strtotime($row['end_time']));
    $booked_slots[$slot] = [
        'id' => $row['id'],
        'customer_name' => $row['customer_name'],
        'customer_phone' => $row['customer_phone'],
    ];
}

// Tạo khung giờ từ 6:00 đến 22:00 (mỗi khung 1h30)
function generate_time_slots($start = '06:00', $end = '22:00') {
    $slots = [];
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    while ($start_time + 90 * 60 <= $end_time) {
        $slot_start = date("H:i", $start_time);
        $slot_end = date("H:i", $start_time + 90 * 60);
        $slots[] = "$slot_start - $slot_end";
        $start_time += 90 * 60;
    }
    return $slots;
}
$time_slots = generate_time_slots();

// Include CSS file
echo '<link rel="stylesheet" href="../css/style.css">';
?>

<div class="container">
    <div class="field-info">
        <h2>Thông tin sân: <?= htmlspecialchars($field['name']) ?></h2>
        <p>Loại sân: <strong><?= htmlspecialchars($field['type']) ?></strong></p>
        <p>Ngày đặt: <strong><?= date('d/m/Y', strtotime($today)) ?></strong></p>
    </div>

    <form id="booking-form">
        <input type="hidden" name="field_id" value="<?= $field_id ?>">
        <input type="hidden" name="booking_date" value="<?= $today ?>">

        <h3>Chọn khung giờ:</h3>
        <div class="time-slots">
            <?php foreach ($time_slots as $slot): ?>
                <?php if (isset($booked_slots[$slot])): ?>
                    <div class="booked-slot-container">
                        <label class="booked tooltip" data-slot="<?= htmlspecialchars($slot) ?>">
                            <span><?= htmlspecialchars($slot) ?></span>
                            <span class="tooltiptext">
                                Người đặt: <?= htmlspecialchars($booked_slots[$slot]['customer_name']) ?><br>
                                SĐT: <?= htmlspecialchars($booked_slots[$slot]['customer_phone']) ?>
                            </span>
                        </label>
                        <div class="slot-actions">
                            <!-- <button type="button" class="btn-small btn-edit" 
                                    data-booking-id="<?= $booked_slots[$slot]['id'] ?>"
                                    data-slot="<?= htmlspecialchars($slot) ?>"
                                    data-name="<?= htmlspecialchars($booked_slots[$slot]['customer_name']) ?>"
                                    data-phone="<?= htmlspecialchars($booked_slots[$slot]['customer_phone']) ?>">
                                Sửa
                            </button> -->
                            <button type="button" class="btn-small btn-delete" 
                                    data-booking-id="<?= $booked_slots[$slot]['id'] ?>"
                                    data-slot="<?= htmlspecialchars($slot) ?>">
                                Hủy
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <label>
                        <input type="checkbox" name="slots[]" value="<?= htmlspecialchars($slot) ?>">
                        <?= htmlspecialchars($slot) ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn">Đặt sân</button>
    </form>
</div>

<!-- Modal nhập thông tin người đặt -->
<div class="modal-bg" id="booking-modal">
    <div class="modal">
        <h3 id="modal-title">Thông tin người đặt</h3>
        <form id="modal-form">
            <input type="hidden" id="booking_id" value="">
            <input type="hidden" id="is_edit" value="false">
            <label>Họ tên:
                <input type="text" id="customer_name" required>
            </label>
            <label>Số điện thoại:
                <input type="tel" id="customer_phone" required pattern="[0-9]{9,15}">
            </label>
            <div class="btn-group">
                <button type="button" class="btn cancel" id="modal-cancel">Hủy</button>
                <button type="submit" class="btn" id="modal-submit">Xác nhận</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal xác nhận hủy booking -->
<div class="modal-bg" id="cancel-modal">
    <div class="modal">
        <h3>Xác nhận hủy đặt sân</h3>
        <p>Bạn có chắc chắn muốn hủy đặt sân cho khung giờ <strong id="cancel-slot"></strong> không ?</p>
        <div class="booking-info">
            <p>Khách hàng: <strong id="cancel-customer-name"></strong></p>
            <p>SĐT: <strong id="cancel-customer-phone"></strong></p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn cancel" id="cancel-modal-cancel">Không</button>
            <button type="button" class="btn btn-danger" id="cancel-modal-confirm">Có, hủy đặt</button>
        </div>
    </div>
</div>

<script>
// Config cho BookingModal
const BOOKING_CONFIG = {
    fieldId: <?= $field_id ?>,
    today: '<?= $today ?>'
};
</script>

<!-- Include JavaScript file -->
<script src="../js/booking-modal.js"></script>

<?php include '../includes/footer.php'; ?>