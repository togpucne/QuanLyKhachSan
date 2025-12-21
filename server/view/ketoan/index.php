<?php
// Kiểm tra đăng nhập
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['vaitro'] !== 'ketoan') {
    header('Location: /ABC-Resort/server/view/login.php');
    exit;
}

include __DIR__ . '/../layouts/header.php';
?>


<?php include __DIR__ . '/../layouts/footer.php'; ?>