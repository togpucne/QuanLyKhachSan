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
    <title>Hệ thống quản lý Tỏa Sáng Resort</title>
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

        /* Dropdown styles */
        .dropdown-toggle::after {
            margin-left: 0.5em;
            vertical-align: 0.15em;
        }

        .dropdown-menu {
            border: 1px solid rgba(0, 0, 0, .15);
            box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
            border-radius: 8px;
            margin-top: 10px;
            padding: 0.5rem 0;
        }



        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }


        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            /* Mảnh hơn */
            opacity: 0.6;
            /* Làm nhạt hơn */
        }


        /* Avatar icon */
        .nav-link.dropdown-toggle .fa-user-circle {
            color: white;
        }



        .dropdown-header small {
            font-size: 0.85rem;
        }

        /* Icons in dropdown */
        .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        /* Active dropdown state */
        .nav-link.dropdown-toggle.show {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
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
                <!-- Dropdown Tài khoản -->
                <div class="nav-item dropdown">
                    <?php
                    // Lấy thêm thông tin nhân viên để hiển thị trong dropdown
                    if (isset($_SESSION['user']['id'])) {
                        require_once __DIR__ . '/../../model/connectDB.php';
                        try {
                            $connect = new Connect();
                            $conn = $connect->openConnect();

                            $sql = "SELECT nv.HoTen, nv.SDT, nv.PhongBan 
                                FROM nhanvien nv
                                WHERE nv.MaTaiKhoan = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $_SESSION['user']['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $employeeDetails = $result->fetch_assoc();

                            $stmt->close();
                            $connect->closeConnect($conn);
                        } catch (Exception $e) {
                            error_log("Database error: " . $e->getMessage());
                            $employeeDetails = [];
                        }
                    }
                    ?>

                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </div>
                        <div class="d-flex flex-column" style="color: white;">
                            Xin chào, <?php echo isset($employeeDetails['HoTen']) ? htmlspecialchars($employeeDetails['HoTen']) : htmlspecialchars($user['username']); ?>
                        </div>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 250px;">
                        <!-- Header với thông tin -->
                        <li>
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-user-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">
                                            <?php echo isset($employeeDetails['HoTen']) ? htmlspecialchars($employeeDetails['HoTen']) : htmlspecialchars($user['username']); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php
                                            $role_names = [
                                                'quanly' => 'Quản lý',
                                                'ketoan' => 'Kế toán',
                                                'letan' => 'Lễ tân',
                                                'buongphong' => 'Buồng phòng',
                                                'kinhdoanh' => 'Kinh doanh',
                                                'thungan' => 'Thủ ngân'
                                            ];
                                            echo isset($role_names[$role]) ? $role_names[$role] : strtoupper($role);
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- Thông tin chi tiết -->
                        <?php if (isset($employeeDetails)): ?>
                            <li>
                                <div class="dropdown-item-text px-3 py-2">
                                    <div class="small">
                                        <?php if (isset($employeeDetails['PhongBan'])): ?>
                                            <div class="mb-1">
                                                <i class="fas fa-building me-2 text-muted"></i>
                                                <span class="text-muted">Phòng ban:</span>
                                                <span class="fw-medium"><?php echo htmlspecialchars($employeeDetails['PhongBan']); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($employeeDetails['SDT'])): ?>
                                            <div class="mb-1">
                                                <i class="fas fa-phone me-2 text-muted"></i>
                                                <span class="text-muted">SĐT:</span>
                                                <span class="fw-medium"><?php echo htmlspecialchars($employeeDetails['SDT']); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            <i class="fas fa-envelope me-2 text-muted"></i>
                                            <span class="text-muted">Email:</span>
                                            <span class="fw-medium"><?php echo htmlspecialchars($user['email'] ?? 'Chưa cập nhật'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        <?php endif; ?>

                        <!-- Menu chức năng -->
                        <li>
                            <a class="dropdown-item" href="../employee/profile.php">
                                <i class="fas fa-id-card me-2"></i>Thông tin cá nhân
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- Đăng xuất -->
                        <li>
                            <a class="dropdown-item text-danger" href="../../../server/controller/login.controller.php?action=logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
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
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => '/ABC-Resort/server/view/quanly/index.php'],
                            ['icon' => 'fas fa-user-tie', 'text' => 'Quản lý nhân viên', 'link' => '/ABC-Resort/server/view/quanly/quanlynhanvien.php'],
                            ['icon' => 'fas fa-user-friends', 'text' => 'Quản lý khách hàng', 'link' => '/ABC-Resort/server/view/quanly/quanlyKH.php'],
                            ['icon' => 'fas fa-users', 'text' => 'Quản lý đoàn', 'link' => '/ABC-Resort/server/view/quanly/quanlydoan.php'],
                            ['icon' => 'fas fa-concierge-bell', 'text' => 'Quản lý dịch vụ', 'link' => '/ABC-Resort/server/view/quanly/quanlydichvu.php'],
                            ['icon' => 'fas fa-bed', 'text' => 'Quản lý phòng', 'link' => '/ABC-Resort/server/view/quanly/quanlyphong.php'],
                            ['icon' => 'fas fa-receipt', 'text' => 'Quản lý hóa đơn', 'link' => '/ABC-Resort/server/view/quanly/quanlyhoadondatphong.php'],
                            ['icon' => 'fas fa-tv', 'text' => 'Quản lý thiết bị', 'link' => '/ABC-Resort/server/view/quanly/quanlythietbi.php'],
                        ],
                        'ketoan' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => '/ABC-Resort/server/view/ketoan/index.php'],
                            ['icon' => 'fas fa-money-bill-wave', 'text' => 'Quản lý doanh thu', 'link' => '/ABC-Resort/server/view/ketoan/quanlydoanhthu.php'],
                          
                        ],
                        'letan' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => '/ABC-Resort/server/view/letan/index.php'],
                            ['icon' => 'fas fa-calendar-check', 'text' => 'Quản lý đặt phòng', 'link' => '/ABC-Resort/server/view/letan/letandatphong.php'],
                            ['icon' => 'fas fa-user-plus', 'text' => 'Đăng ký tài khoản', 'link' => '/ABC-Resort/server/view/letan/letanlogon.php'],
                        ],
                        'buongphong' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => '/ABC-Resort/server/view/buongphong/index.php'],
                            ['icon' => 'fas fa-bed', 'text' => 'Danh sách phòng', 'link' => '/ABC-Resort/server/view/buongphong/quanlyphong.php'],
                        ],
                        'kinhdoanh' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => '/ABC-Resort/server/view/kinhdoanh/index.php'],
                            ['icon' => 'fas fa-gift', 'text' => 'Quản lý khuyến mãi', 'link' => '/ABC-Resort/server/view/kinhdoanh/khuyenmai.php'],
                        ],
                        'thungan' => [
                            ['icon' => 'fas fa-tachometer-alt', 'text' => 'Tổng quan', 'link' => '/ABC-Resort/server/view/thungan/index.php'],
                            ['icon' => 'fas fa-file-invoice-dollar', 'text' => 'Lập báo cáo', 'link' => '/ABC-Resort/server/view/thungan/lapbaocao.php'],
                            ['icon' => 'fas fa-cash-register', 'text' => 'Quản lý thu chi', 'link' => '/ABC-Resort/server/view/thungan/thuchi.php'],
                            ['icon' => 'fas fa-receipt', 'text' => 'Hóa đơn điện tử', 'link' => '/ABC-Resort/server/view/thungan/hoadondientu.php'],
                            ['icon' => 'fas fa-exchange-alt', 'text' => 'Giao dịch', 'link' => '/ABC-Resort/server/view/thungan/giaodich.php'],
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