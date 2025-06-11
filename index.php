<?php
session_start();
include 'includes/header.php';
require 'includes/db.php';

$fields = $conn->query("SELECT * FROM fields");
if (!$fields) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>
<div class="field-map">
    <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
        <div class="overlay">
            <div class="overlay-content">
                <p>Bạn cần đăng nhập để quản lý sân bóng.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php while ($field = $fields->fetch_assoc()): ?>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a class="field-box <?= strtolower($field['type']) ?>" href="pages/field_detail.php?id=<?= $field['id'] ?>">
                <?= htmlspecialchars($field['name']) ?><br>
                <span><?= $field['type'] ?></span>
            </a>
        <?php else: ?>
            <div class="field-box <?= strtolower($field['type']) ?>" style="pointer-events: none; opacity: 0.5;">
                <?= htmlspecialchars($field['name']) ?><br>
                <span><?= $field['type'] ?></span>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>
</div>
<?php include 'includes/footer.php'; ?>
