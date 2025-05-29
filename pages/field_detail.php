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

$booked_stmt = $conn->prepare("SELECT start_time, end_time, customer_name, customer_phone FROM bookings WHERE field_id = ? AND date = ?");
if (!$booked_stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$booked_stmt->bind_param("is", $field_id, $today);
$booked_stmt->execute();
$result = $booked_stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $slot = date("H:i", strtotime($row['start_time'])) . " - " . date("H:i", strtotime($row['end_time']));
    $booked_slots[$slot] = [
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
?>

<style>
.container { max-width: 800px; margin: 30px auto; font-family: Arial, sans-serif; }
h2, h3 { color: #333; }
.time-slots { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
.time-slots label {
    border: 1px solid #888;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.3s;
    min-width: 140px;
    display: flex; justify-content: space-between; align-items: center;
}
.time-slots label.booked {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    cursor: pointer;
    color: #721c24;
    position: relative;
}
.time-slots label.booked:hover {
    background-color: #f5c6cb;
}
.time-slots input[type=checkbox] { margin-right: 8px; }
.time-slots label.booked input[type=checkbox] { display: none; }

/* Modal */
.modal-bg {
    display: none; 
    position: fixed; 
    top:0; left:0; right:0; bottom:0; 
    background: rgba(0,0,0,0.5); 
    z-index: 9999;
    justify-content: center; 
    align-items: center;
}
.modal {
    background: #fff;
    padding: 20px 25px;
    border-radius: 8px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
.modal h3 { margin-top: 0; }
.modal label { display: block; margin-top: 10px; }
.modal input[type=text], .modal input[type=tel] {
    width: 100%; padding: 8px;
    box-sizing: border-box;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.modal .btn-group {
    margin-top: 20px;
    text-align: right;
}
.modal .btn {
    padding: 8px 15px;
    background-color: #007bff;
    border: none;
    border-radius: 4px;
    color: white;
    cursor: pointer;
    margin-left: 10px;
}
.modal .btn.cancel {
    background-color: #6c757d;
}

/* Tooltip cho khung giờ đã đặt */
.tooltip {
    position: relative;
    display: inline-block;
    cursor: pointer;
    color: #721c24;
}
.tooltip .tooltiptext {
    visibility: hidden;
    width: 220px;
    background-color: #f8d7da;
    color: #721c24;
    text-align: left;
    border-radius: 6px;
    padding: 10px;
    position: absolute;
    z-index: 10;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s;
}
.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>

<div class="container">
    <h2>Thông tin sân: <?= htmlspecialchars($field['name']) ?></h2>
    <p>Loại sân: <strong><?= htmlspecialchars($field['type']) ?></strong></p>

    <form id="booking-form">
        <input type="hidden" name="field_id" value="<?= $field_id ?>">
        <input type="hidden" name="booking_date" value="<?= $today ?>">

        <h3>Chọn khung giờ:</h3>
        <div class="time-slots">
            <?php foreach ($time_slots as $slot): ?>
                <?php if (isset($booked_slots[$slot])): ?>
                    <label class="booked tooltip" data-slot="<?= htmlspecialchars($slot) ?>">
                        <span><?= htmlspecialchars($slot) ?></span>
                        <span class="tooltiptext">
                            Người đặt: <?= htmlspecialchars($booked_slots[$slot]['customer_name']) ?><br>
                            SĐT: <?= htmlspecialchars($booked_slots[$slot]['customer_phone']) ?>
                        </span>
                    </label>
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
        <h3>Thông tin người đặt</h3>
        <form id="modal-form">
            <label>Họ tên:
                <input type="text" id="customer_name" required>
            </label>
            <label>Số điện thoại:
                <input type="tel" id="customer_phone" required pattern="[0-9]{9,15}">
            </label>
            <div class="btn-group">
                <button type="button" class="btn cancel" id="modal-cancel">Hủy</button>
                <button type="submit" class="btn">Xác nhận</button>
            </div>
        </form>
    </div>
</div>

<script>
// Lưu các checkbox đang chọn để show modal
let selectedSlots = [];
const bookingModal = document.getElementById('booking-modal');
const modalForm = document.getElementById('modal-form');
const customerNameInput = document.getElementById('customer_name');
const customerPhoneInput = document.getElementById('customer_phone');
const bookingForm = document.getElementById('booking-form');

bookingForm.addEventListener('submit', function(e){
    e.preventDefault();

    // Lấy tất cả checkbox đã chọn
    const checkedBoxes = bookingForm.querySelectorAll('input[name="slots[]"]:checked');
    if (checkedBoxes.length === 0) {
        alert('Vui lòng chọn ít nhất 1 khung giờ chưa đặt.');
        return;
    }

    // Lưu lại các slot đã chọn để dùng khi submit modal
    selectedSlots = Array.from(checkedBoxes).map(cb => cb.value);

    // Hiện modal nhập thông tin người đặt
    customerNameInput.value = '';
    customerPhoneInput.value = '';
    bookingModal.style.display = 'flex';
    customerNameInput.focus();
});

// Hủy modal
document.getElementById('modal-cancel').addEventListener('click', () => {
    bookingModal.style.display = 'none';
});

// Xử lý submit modal form
modalForm.addEventListener('submit', function(e){
    e.preventDefault();

    const name = customerNameInput.value.trim();
    const phone = customerPhoneInput.value.trim();
    if (!name || !phone) {
        alert('Vui lòng nhập đầy đủ họ tên và số điện thoại.');
        return;
    }

    // Gửi AJAX lưu booking
    fetch('save_booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            field_id: <?= $field_id ?>,
            date: '<?= $today ?>',
            slots: selectedSlots,
            customer_name: name,
            customer_phone: phone
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Đặt sân thành công!');
            bookingModal.style.display = 'none';

            // Cập nhật UI: khóa các slot đã đặt
            selectedSlots.forEach(slot => {
                // Tìm label checkbox tương ứng và chuyển sang booked
                const label = Array.from(document.querySelectorAll('.time-slots label'))
                    .find(lbl => lbl.textContent.trim().startsWith(slot));
                if (label) {
                    label.classList.add('booked');
                    label.innerHTML = `
                        <span>${slot}</span>
                        <span class="tooltiptext">
                            Người đặt: ${name}<br>
                            SĐT: ${phone}
                        </span>
                    `;
                }
            });

            // Bỏ chọn checkbox cũ
            bookingForm.querySelectorAll('input[name="slots[]"]:checked').forEach(cb => cb.checked = false);
        } else {
            alert('Đặt sân thất bại: ' + (data.message || 'Lỗi hệ thống'));
        }
    })
    .catch(() => alert('Có lỗi xảy ra. Vui lòng thử lại sau.'));
});
</script>

<?php include '../includes/footer.php'; ?>
