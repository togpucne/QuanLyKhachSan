<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TẠO BASE URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_path = '/ABC-Resort';
$base_url = $protocol . '://' . $host . $project_path;

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['vaitro'] ?? '';
$userName = $_SESSION['user_name'] ?? '';

// LẤY THÔNG TIN KHÁCH HÀNG NẾU ĐÃ ĐĂNG NHẬP
$customerInfo = [];
if ($isLoggedIn) {
    $customerInfo = getCustomerInfo($_SESSION['user_id']);
}

function getCustomerInfo($userId)
{
    require_once __DIR__ . '/../../model/connectDB.php';

    try {
        $connect = new Connect();
        $conn = $connect->openConnect();

        // SỬA: THÊM TenDangNhap VÀO SELECT
        $sql = "SELECT kh.*, tk.Email, tk.CMND, tk.TenDangNhap, tk.created_at 
                FROM KhachHang kh 
                JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
                WHERE tk.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
        }

        $stmt->close();
        $connect->closeConnect($conn);
        return $data;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}
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
    <!-- Trong phần head của layout/header.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Hoặc cho Zalo icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        #room-list,
        #khuyen-mai {
            scroll-margin-top: 100px;
            /* Điều chỉnh khoảng cách với header */
        }

        .dropdown-header {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .dropdown-item-text small {
            font-size: 0.8rem;
        }

        /* Làm đẹp dropdown menu */
        .dropdown-menu {
            min-width: 250px;
            border: 1px solid rgba(0, 0, 0, .15);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
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


                    <!-- PHÒNG -->
                    <a class="nav-link" href="<?php echo $base_url; ?>/client/index.php?section=room-list">
                        Phòng
                    </a>

                    <!-- Link Khuyến mãi -->
                    <a class="nav-link" href="<?php echo $base_url; ?>/client/index.php?section=khuyen-mai">
                        <i class="fas fa-gift me-1"></i>Khuyến mãi
                    </a>


                    <!-- Dropdown Hỗ trợ - SỬA ĐƯỜNG DẪN -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?php echo $base_url; ?>/client/view/support/index.php" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-headset me-1"></i>Hỗ trợ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/client/view/support/index.php">
                                    <i class="fas fa-question-circle me-2"></i>Trợ giúp
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/client/view/support/index.php#contact">
                                    <i class="fas fa-phone me-2"></i>Liên hệ chúng tôi
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/client/view/support/index.php">
                                    <i class="fas fa-envelope me-2"></i>Hộp thư của tôi
                                </a></li>
                        </ul>
                    </div>


                    <!-- LIÊN HỆ - SỬA ĐƯỜNG DẪN TUYỆT ĐỐI -->
                    <a class="nav-link" href="<?php echo $base_url; ?>/client/view/contact/index.php">
                        <i class="fas fa-address-book me-1"></i>Liên Hệ
                    </a>
                    <!-- Dropdown Tài khoản -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $isLoggedIn ? htmlspecialchars($userName) : 'Tài khoản'; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($isLoggedIn): ?>
                                <!-- Đã đăng nhập - HIỂN THỊ THÔNG TIN USER -->
                                <li>
                                    <div class="dropdown-header">
                                        <div class="fw-bold"><?php echo htmlspecialchars($customerInfo['HoTen'] ?? $userName); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($userRole); ?></small>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <!-- THÔNG TIN CÁ NHÂN -->
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/client/view/customer/profile.php">
                                        <i class="fas fa-id-card me-2"></i>Thông tin cá nhân
                                    </a></li>

                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/client/view/booking/history.php">
                                        <i class="fas fa-history me-2"></i>Lịch sử đặt phòng
                                    </a></li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <!-- THÔNG TIN NHANH -->
                                <?php if (!empty($customerInfo)): ?>
                                    <li>
                                        <div class="dropdown-item-text small">
                                            <div><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($customerInfo['SoDienThoai'] ?? 'Chưa cập nhật'); ?></div>
                                            <div><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($customerInfo['Email'] ?? $_SESSION['email'] ?? ''); ?></div>
                                        </div>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>

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
    <script>
        // Xử lý scroll mượt cho tất cả anchor links
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý cho link Phòng
            const roomLink = document.querySelector('a[href*="#room-list"]');
            if (roomLink) {
                roomLink.addEventListener('click', function(e) {
                    if (window.location.pathname.includes('index.php')) {
                        e.preventDefault();
                        const roomSection = document.getElementById('room-list');
                        if (roomSection) {
                            roomSection.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            }

            // Xử lý cho link Khuyến mãi
            const promotionLinks = document.querySelectorAll('a[href*="#khuyen-mai"]');
            promotionLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (window.location.pathname.includes('index.php')) {
                        e.preventDefault();
                        const promotionSection = document.getElementById('khuyen-mai');
                        if (promotionSection) {
                            promotionSection.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });

            // Xử lý URL có anchor khi load trang
            if (window.location.hash && window.location.pathname.includes('index.php')) {
                const targetId = window.location.hash.substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    setTimeout(() => {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 100);
                }
            }
        });
    </script>
    
<script>
    // Xử lý scroll khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý parameter ?section=
        const urlParams = new URLSearchParams(window.location.search);
        const sectionParam = urlParams.get('section');
        
        if (sectionParam) {
            scrollToSectionWithDelay(sectionParam);
            
            // Xóa parameter sau khi scroll
            const cleanUrl = window.location.pathname + window.location.hash;
            window.history.replaceState({}, '', cleanUrl);
        }
        
        // Xử lý hash # từ URL
        if (window.location.hash) {
            const hashId = window.location.hash.substring(1);
            scrollToSectionWithDelay(hashId);
        }
        
        // Xử lý click từ các trang khác (dùng sessionStorage)
        const storedSection = sessionStorage.getItem('scrollToSection');
        if (storedSection) {
            scrollToSectionWithDelay(storedSection);
            sessionStorage.removeItem('scrollToSection');
        }
    });
    
    function scrollToSectionWithDelay(sectionId) {
        setTimeout(() => {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 500); // Delay để trang load hoàn toàn
    }
    
    // Hàm để các trang khác gọi
    function scrollToSectionFromOtherPage(sectionId) {
        sessionStorage.setItem('scrollToSection', sectionId);
        window.location.href = 'index.php';
    }
</script>