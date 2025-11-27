<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TẠO BASE URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_path = '/ABC-RESORT';
$base_url = $protocol . '://' . $host . $project_path;

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['vaitro'] ?? '';
$userName = $_SESSION['user_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tỏa Sáng Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DÙNG BASE URL -->
    <link href="<?php echo $base_url; ?>/client/assets/css/style.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo $base_url; ?>/client/assets/images/logo/logo_toasang-removebg.png">
    <style>
        .room-card {
            transition: all 0.3s ease;
            border: none;
        }

        .room-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }
        
        .user-welcome {
            color: #fff;
            margin-right: 15px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <!-- LOGO SỬA VỀ TRANG CHỦ QUA INDEX.PHP -->
            <a class="navbar-brand" href="<?php echo $base_url; ?>/client/index.php">
                <img src="<?php echo $base_url; ?>/client/assets/images/logo/logo_toasang-removebg.png" width="60" height="60" alt="Logo">
                Tỏa Sáng RESORT
            </a>

            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <!-- TRANG CHỦ SỬA VỀ INDEX.PHP -->
                    <a class="nav-link active" href="<?php echo $base_url; ?>/client/index.php">Trang Chủ</a>
                    
                    <!-- PHÒNG SỬA VỀ INDEX.PHP KÈM ANCHOR -->
                    <a class="nav-link" href="<?php echo $base_url; ?>/client/index.php#room-list">Phòng</a>

                    <!-- Dropdown Khuyến mãi -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-gift me-1"></i>Khuyến mãi
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-percentage me-2"></i>Ưu đãi đặc biệt
                                </a></li>
                            <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-tag me-2"></i>Combo tiết kiệm
                                </a></li>
                            <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-star me-2"></i>Ưu đãi thành viên
                                </a></li>
                        </ul>
                    </div>

                    <!-- Dropdown Hỗ trợ -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-headset me-1"></i>Hỗ trợ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-question-circle me-2"></i>Trợ giúp
                                </a></li>
                            <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-phone me-2"></i>Liên hệ chúng tôi
                                </a></li>
                            <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-envelope me-2"></i>Hộp thư của tôi
                                </a></li>
                        </ul>
                    </div>

                    <a class="nav-link" href="#">Đặt Phòng</a>
                    <a class="nav-link" href="#">Liên Hệ</a>
                    
                    <!-- Dropdown Tài khoản -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $isLoggedIn ? htmlspecialchars($userName) : 'Tài khoản'; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($isLoggedIn): ?>
                                <!-- Đã đăng nhập - THÊM THÔNG TIN USER -->
                                <li><span class="dropdown-item-text small text-muted">
                                    <i class="fas fa-user-tag me-2"></i><?php echo htmlspecialchars($userRole); ?>
                                </span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-suitcase me-2"></i>Đặt chỗ của tôi
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-user-edit me-2"></i>Thông tin tài khoản
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>/client/controller/user.controller.php?action=logout">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </a></li>
                            <?php else: ?>
                                <!-- Chưa đăng nhập -->
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/client/controller/user.controller.php?action=login">
                                        <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/client/controller/user.controller.php?action=register">
                                        <i class="fas fa-user-plus me-2"></i>Đăng ký
                                    </a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>