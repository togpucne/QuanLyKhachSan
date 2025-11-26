<?php
// CHỈ gọi session_start() nếu chưa có session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || !isset($_SESSION['vaitro'])) {
    header('Location: login.php');
    exit();
}
$user = $_SESSION['user'];
$role = $_SESSION['vaitro'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản ly Tỏa Sáng Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/logo/logo_toasang-removebg.png">
    <style>
        :root {
            --primary: #292D33;
            /* Xanh dương chính */
            --secondary: #43a047;
            /* Xanh lá điểm nhấn */
            --accent: #292D33;
            /* Xanh dương đậm */
            --light: #e3f2fd;
            /* Xanh dương nhạt */
            --dark: #292D33;
            /* Xanh dương tối */
            --success: #4caf50;
            /* Xanh lá */
            --sidebar-bg: #292D33;
            /* Xanh sidebar */
        }

        .sidebar {
            background: linear-gradient(135deg, #292D33 0%, #292D33 100%);
            color: white;
            min-height: 100vh;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            padding-left: 25px;
            color: white;
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid red;
            color: white;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
        }

        .navbar {
            background: linear-gradient(135deg, #292D33 0%, #292D33 100%) !important;
            border-bottom: 3px solid #e9dfcb;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.5rem;
        }

        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }

        .welcome-card {
            background: linear-gradient(135deg, #292D33 0%, #292D33 100%);
            color: white;
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(30, 136, 229, 0.3);
        }

        .welcome-card .card-title {
            color: white;
            font-weight: 600;
        }

        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background: white;
            border-left: 4px solid #292D33;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.primary {
            border-left-color: #292D33;
        }

        .stat-card.success {
            border-left-color: #43a047;
        }

        .stat-card.warning {
            border-left-color: #ffa000;
        }

        .stat-card.danger {
            border-left-color: #e53935;
        }

        .stat-card.info {
            border-left-color: #00acc1;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .table-container .table thead {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
        }

        .table-container .table tbody tr:hover {
            background-color: rgba(30, 136, 229, 0.05);
        }

        .role-badge {
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            background: #43a047;
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 10px 20px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 136, 229, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%);
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffa000 0%, #f57c00 100%);
            border: none;
            border-radius: 6px;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
            border: none;
            border-radius: 6px;
            color: white;
        }

        /* Sidebar header */
        .sidebar-header {
            background: rgba(0, 0, 0, 0.2);
            padding: 25px 20px;
            text-align: center;
            border-bottom: 2px solid #43a047;
        }

        .sidebar-header h6 {
            font-weight: 600;
            margin-bottom: 8px;
            color: white;
        }

        .sidebar-header .badge {
            background: #43a047;
            color: white;
            font-weight: 700;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #292D33 0%, #292D33 100%);
            color: white;
            border-top: 3px solid #43a047;

        }

        footer p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Card headers */
        .card-header {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%) !important;
            color: white !important;
            border-bottom: 2px solid #43a047;
            font-weight: 600;
        }

        /* Form controls */
        .form-control:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
        }

        .form-select:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
        }

        /* Badge variations */
        .badge.bg-primary {
            background: #1e88e5 !important;
            color: white;
        }

        .badge.bg-success {
            background: #43a047 !important;
            color: white;
        }

        /* Table action buttons */
        .btn-outline-primary {
            border-color: #1e88e5;
            color: #1e88e5;
        }

        .btn-outline-primary:hover {
            background: #1e88e5;
            border-color: #1e88e5;
        }

        /* Status badges */
        .badge.bg-success {
            background: #43a047 !important;
        }

        .badge.bg-warning {
            background: #ffa000 !important;
        }

        .badge.bg-danger {
            background: #e53935 !important;
        }

        /* Custom scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #43a047;
            border-radius: 3px;
        }

        /* Animation for icons */
        .sidebar .nav-link i {
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover i {
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }

            .stat-card:hover {
                transform: none;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../home/dashboard.php">
                <i class="fas fa-hotel me-2"></i>Tỏa Sáng Resort
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>
                    <?php echo isset($user['username']) ? $user['username'] : 'Guest'; ?>
                    <span class="badge bg-warning role-badge"><?php echo isset($role) ? strtoupper($role) : 'USER'; ?></span>
                </span>
                <a href="../../../server/controller/login.controller.php?action=logout" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Đăng Xuất
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="p-3 text-center">
                    <h6 class="mb-1">VAI TRÒ</h6>
                    <span class="badge bg-warning text-dark"><?php echo strtoupper($role); ?></span>
                </div>
                <nav class="nav flex-column">
                    <?php
                    // Menu cho từng vai trò
                    $menus = [
                        'quanly' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => '../home/dashboard.php'],
                            ['icon' => 'fas fa-users', 'text' => 'Quản lý nhân viên', 'link' => '../quanly/quanlynhanvien.php'],
                            ['icon' => 'fas fa-user-friends', 'text' => 'Quản lý khách hàng', 'link' => '../quanly/quanlyKH.php'],
                            ['icon' => 'fas fa-users', 'text' => 'Quản lý đoàn', 'link' => '../quanly/quanlydoan.php'],
                            ['icon' => 'fas fa-concierge-bell', 'text' => 'Quản lý dịch vụ', 'link' => '../quanly/quanlydichvu.php'],
                            ['icon' => 'fas fa-bed', 'text' => 'Quản lý phòng', 'link' => '../quanly/quanlyphong.php'],
                            ['icon' => 'fas fa-cog', 'text' => 'Cài đặt hệ thống', 'link' => 'caidat.php']
                        ],
                        'ketoan' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => 'dashboard.php'],
                            ['icon' => 'fas fa-money-bill-wave', 'text' => 'Quản lý doanh thu', 'link' => 'quanlydoanhthu.php'],
                            ['icon' => 'fas fa-receipt', 'text' => 'Hóa đơn & Thanh toán', 'link' => 'hoadon.php'],
                            ['icon' => 'fas fa-chart-pie', 'text' => 'Báo cáo tài chính', 'link' => 'baocaotaichinh.php'],
                            ['icon' => 'fas fa-calculator', 'text' => 'Kế toán tổng hợp', 'link' => 'ketoan.php']
                        ],
                        'letan' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => 'dashboard.php'],
                            ['icon' => 'fas fa-calendar-check', 'text' => 'Quản lý đặt phòng', 'link' => 'datphong.php'],
                            ['icon' => 'fas fa-user-plus', 'text' => 'Đăng ký tài khoản', 'link' => 'logon.php'],
                            ['icon' => 'fas fa-cash-register', 'text' => 'Thanh toán', 'link' => 'thanhtoan.php'],
                            ['icon' => 'fas fa-user-check', 'text' => 'Check-in/Check-out', 'link' => 'checkinout.php'],
                            ['icon' => 'fas fa-concierge-bell', 'text' => 'Dịch vụ khách hàng', 'link' => 'dichvukhachhang.php'],
                            ['icon' => 'fas fa-cog', 'text' => 'Cài đặt hệ thống', 'link' => 'caidat.php']

                        ],
                        'buongphong' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => 'dashboard.php'],
                            ['icon' => 'fas fa-bed', 'text' => 'Danh sách phòng', 'link' => '../buongphong/quanlyphong.php'],
                            ['icon' => 'fas fa-broom', 'text' => 'Vệ sinh phòng', 'link' => 'vesinhphong.php'],
                            ['icon' => 'fas fa-tools', 'text' => 'Bảo trì phòng', 'link' => 'baotriphong.php'],
                            ['icon' => 'fas fa-clipboard-list', 'text' => 'Kiểm kê trang thiết bị', 'link' => 'kiemke.php'],
                            ['icon' => 'fas fa-cog', 'text' => 'Cài đặt hệ thống', 'link' => 'caidat.php']

                        ],
                        'kinhdoanh' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => 'dashboard.php'],
                            ['icon' => 'fas fa-gift', 'text' => 'Quản lý khuyến mãi', 'link' => '../kinhdoanh/khuyenmai.php'],
                            ['icon' => 'fas fa-comments', 'text' => 'Phản hồi khách hàng', 'link' => 'phanhoi.php'],
                            ['icon' => 'fas fa-cog', 'text' => 'Cài đặt hệ thống', 'link' => 'caidat.php']

                        ],
                        'thungan' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => 'dashboard.php'],
                            ['icon' => 'fas fa-file-invoice-dollar', 'text' => 'Lập báo cáo', 'link' => 'lapbaocao.php'],
                            ['icon' => 'fas fa-cash-register', 'text' => 'Quản lý thu chi', 'link' => 'thuchi.php'],
                            ['icon' => 'fas fa-receipt', 'text' => 'Hóa đơn điện tử', 'link' => 'hoadondientu.php'],
                            ['icon' => 'fas fa-cog', 'text' => 'Cài đặt hệ thống', 'link' => 'caidat.php']

                        ]
                    ];

                    // Hiển thị menu theo vai trò
                    $currentMenu = $menus[$role] ?? $menus['quanly'];

                    foreach ($currentMenu as $menuItem) {
                        $isActive = basename($_SERVER['PHP_SELF']) === $menuItem['link'] ? 'active' : '';
                        echo '
                        <a class="nav-link ' . $isActive . '" href="' . $menuItem['link'] . '">
                            <i class="' . $menuItem['icon'] . '"></i>' . $menuItem['text'] . '
                        </a>';
                    }
                    ?>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="container-fluid py-4">