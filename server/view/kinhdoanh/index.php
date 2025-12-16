<?php
// THÊM SESSION_START() và KIỂM TRA ĐĂNG NHẬP
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['vaitro'])) {
    header('Location: ../login/login.php');
    exit();
}

// Kiểm tra vai trò - chỉ cho phép kinhdoanh
if ($_SESSION['vaitro'] !== 'kinhdoanh') {
    $_SESSION['error'] = "Bạn không có quyền truy cập!";
    header('Location: ../login/login.php');
    exit();
}

$pageTitle = "Tổng Quan Khuyến Mãi - ABC Resort";

// Kết nối database
require_once __DIR__ . '/../../model/connectDB.php';
$connect = new Connect();
$conn = $connect->openConnect();

// THỐNG KÊ TỔNG QUAN KHUYẾN MÃI
$today = date('Y-m-d');

// Lấy tất cả khuyến mãi
$result = mysqli_query($conn, "SELECT * FROM khuyenmai ORDER BY created_at DESC");
$allPromotions = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $allPromotions[] = $row;
    }
}

// Thống kê trạng thái khuyến mãi
$thongKeTrangThai = [
    'Tổng' => 0,
    'Đang hoạt động' => 0,
    'Sắp diễn ra' => 0,
    'Đã kết thúc' => 0
];

// Thống kê mức giảm giá
$thongKeMucGiamGia = [];

foreach ($allPromotions as $km) {
    // Thống kê tổng
    $thongKeTrangThai['Tổng']++;
    
    // Xác định trạng thái
    if ($km['TrangThai'] == 0) {
        $thongKeTrangThai['Đã kết thúc']++;
    } elseif ($km['NgayBatDau'] <= $today && $km['NgayKetThuc'] >= $today) {
        $thongKeTrangThai['Đang hoạt động']++;
    } elseif ($km['NgayBatDau'] > $today) {
        $thongKeTrangThai['Sắp diễn ra']++;
    } else {
        $thongKeTrangThai['Đã kết thúc']++;
    }
    
    // Thống kê mức giảm giá
    $mucGiam = $km['MucGiamGia'];
    $mucGiamKey = "";
    
    if ($mucGiam <= 10) {
        $mucGiamKey = "0-10%";
    } elseif ($mucGiam <= 20) {
        $mucGiamKey = "11-20%";
    } elseif ($mucGiam <= 30) {
        $mucGiamKey = "21-30%";
    } elseif ($mucGiam <= 50) {
        $mucGiamKey = "31-50%";
    } else {
        $mucGiamKey = "Trên 50%";
    }
    
    if (!isset($thongKeMucGiamGia[$mucGiamKey])) {
        $thongKeMucGiamGia[$mucGiamKey] = 0;
    }
    $thongKeMucGiamGia[$mucGiamKey]++;
}

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <!-- Tiêu đề -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mt-4">Tổng Quan Khuyến Mãi</h1>
            <p class="text-muted">Thống kê tình trạng khuyến mãi</p>
            
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <!-- Tổng số khuyến mãi -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Tổng số khuyến mãi</div>
                    <div class="h3 font-weight-bold"><?php echo $thongKeTrangThai['Tổng']; ?></div>
                    <small class="text-muted">khuyến mãi</small>
                </div>
            </div>
        </div>

        <!-- Khuyến mãi đang hoạt động -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Đang hoạt động</div>
                    <div class="h3 font-weight-bold text-success"><?php echo $thongKeTrangThai['Đang hoạt động']; ?></div>
                    <small class="text-muted">khuyến mãi</small>
                </div>
            </div>
        </div>

        <!-- Khuyến mãi sắp diễn ra -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Sắp diễn ra</div>
                    <div class="h3 font-weight-bold text-warning"><?php echo $thongKeTrangThai['Sắp diễn ra']; ?></div>
                    <small class="text-muted">khuyến mãi</small>
                </div>
            </div>
        </div>

        <!-- Khuyến mãi đã kết thúc -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Đã kết thúc</div>
                    <div class="h3 font-weight-bold text-secondary"><?php echo $thongKeTrangThai['Đã kết thúc']; ?></div>
                    <small class="text-muted">khuyến mãi</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Biểu đồ phân bổ trạng thái -->
        <div class="col-xl-6 col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Phân bổ trạng thái khuyến mãi</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                    <div class="mt-3 text-center small">
                        <span class="mr-3">
                            <i class="fas fa-circle text-success"></i> Đang hoạt động
                        </span>
                        <span class="mr-3">
                            <i class="fas fa-circle text-warning"></i> Sắp diễn ra
                        </span>
                        <span class="mr-3">
                            <i class="fas fa-circle text-secondary"></i> Đã kết thúc
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê mức giảm giá -->
        <div class="col-xl-6 col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Phân bổ theo mức giảm giá</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Mức giảm giá</th>
                                    <th>Số lượng</th>
                                    <th>Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($thongKeMucGiamGia as $mucGiam => $soluong): ?>
                                    <tr>
                                        <td><?php echo $mucGiam; ?></td>
                                        <td><?php echo $soluong; ?> khuyến mãi</td>
                                        <td>
                                            <?php 
                                                $tyLe = round(($soluong / $thongKeTrangThai['Tổng']) * 100, 1);
                                                echo $tyLe . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách khuyến mãi mới nhất -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Khuyến mãi mới nhất</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên khuyến mãi</th>
                                    <th>Mức giảm</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recentPromotions = array_slice($allPromotions, 0, 5); // Lấy 5 khuyến mãi mới nhất
                                foreach ($recentPromotions as $km): 
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($km['TenKhuyenMai']); ?></td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $km['MucGiamGia']; ?>%</span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($km['NgayBatDau'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($km['NgayKetThuc'])); ?></td>
                                        <td>
                                            <?php
                                            if ($km['TrangThai'] == 0) {
                                                echo '<span class="badge bg-secondary">Đã kết thúc</span>';
                                            } elseif ($km['NgayBatDau'] <= $today && $km['NgayKetThuc'] >= $today) {
                                                echo '<span class="badge bg-success">Đang hoạt động</span>';
                                            } elseif ($km['NgayBatDau'] > $today) {
                                                echo '<span class="badge bg-warning">Sắp diễn ra</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Đã kết thúc</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nút chuyển đến quản lý chi tiết -->
    <div class="row">
        <div class="col-md-12 text-center">
            <a href="khuyenmai.php" class="btn btn-primary">
                <i class="fas fa-list me-2"></i>Quản lý chi tiết khuyến mãi
            </a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ trạng thái khuyến mãi
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('statusPieChart').getContext('2d');
        const statusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Đang hoạt động', 'Sắp diễn ra', 'Đã kết thúc'],
                datasets: [{
                    data: [
                        <?php echo $thongKeTrangThai['Đang hoạt động']; ?>,
                        <?php echo $thongKeTrangThai['Sắp diễn ra']; ?>,
                        <?php echo $thongKeTrangThai['Đã kết thúc']; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
                    borderColor: '#fff',
                    borderWidth: 2
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} khuyến mãi (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php 

include_once __DIR__ . '/../layouts/footer.php'; 
?>