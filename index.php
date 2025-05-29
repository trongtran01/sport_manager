<?php
include 'includes/header.php';
require 'includes/db.php';

$fields = $conn->query("SELECT * FROM fields");
?>

<div class="field-map">
    <?php while($field = $fields->fetch_assoc()): ?>
        <a class="field-box <?= strtolower($field['type']) ?>" href="pages/field_detail.php?id=<?= $field['id'] ?>">
            <?= htmlspecialchars($field['name']) ?><br>
            <span><?= $field['type'] ?></span>
        </a>
    <?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>
