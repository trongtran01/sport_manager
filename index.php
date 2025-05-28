<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>🏟️ Hệ thống quản lý sân thể thao</h1>
    <p>Chào mừng bạn đến với hệ thống quản lý sân thể thao mini viết bằng PHP thuần!</p>

    <div class="dashboard">
        <div class="card">
            <h3>⚽ Thêm sân mới</h3>
            <p>Quản lý loại sân, giờ hoạt động, giá thuê</p>
            <a href="pages/add_field.php" class="btn">Thêm sân</a>
        </div>
        <div class="card">
            <h3>📋 Danh sách sân</h3>
            <p>Xem và chỉnh sửa thông tin các sân hiện có</p>
            <a href="pages/list_fields.php" class="btn">Xem danh sách</a>
        </div>
        <div class="card">
            <h3>📆 Lịch đặt sân</h3>
            <p>Quản lý và theo dõi lịch đặt sân</p>
            <a href="pages/bookings.php" class="btn">Xem lịch</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
