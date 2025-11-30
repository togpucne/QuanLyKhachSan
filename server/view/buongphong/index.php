<?php
require_once '../../model/buongphongQLPhong.model.php';
$model = new PhongModel();
$dsPhong = $model->getDanhSachPhong();

// Thống kê trạng thái phòng
$thongKeTrangThai = [
    'Tổng' => 0,
    'Trống' => 0,
    'Đang sử dụng' => 0,
    'Đang bảo trì' => 0
];

// Thống kê hạng phòng
$thongKeHangPhong = [];

foreach ($dsPhong as $phong) {
    // Thống kê trạng thái
    $thongKeTrangThai['Tổng']++;
    $thongKeTrangThai[$phong['TrangThai']]++;
    
    // Thống kê hạng phòng
    $hangPhong = $phong['HangPhong'];
    if (!isset($thongKeHangPhong[$hangPhong])) {
        $thongKeHangPhong[$hangPhong] = 0;
    }
    $thongKeHangPhong[$hangPhong]++;
}
?>

<?php include '../layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Tiêu đề -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mt-4">Tổng Quan Buồng Phòng</h1>
            <p class="text-muted">Thống kê tình trạng phòng</p>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <!-- Tổng số phòng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Tổng số phòng</div>
                    <div class="h3 font-weight-bold"><?php echo $thongKeTrangThai['Tổng']; ?></div>
                    <small class="text-muted">phòng</small>
                </div>
            </div>
        </div>

        <!-- Phòng trống -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Phòng trống</div>
                    <div class="h3 font-weight-bold text-success"><?php echo $thongKeTrangThai['Trống']; ?></div>
                    <small class="text-muted">phòng</small>
                </div>
            </div>
        </div>

        <!-- Phòng đang sử dụng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Đang sử dụng</div>
                    <div class="h3 font-weight-bold text-primary"><?php echo $thongKeTrangThai['Đang sử dụng']; ?></div>
                    <small class="text-muted">phòng</small>
                </div>
            </div>
        </div>

        <!-- Phòng bảo trì -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted mb-2">Đang bảo trì</div>
                    <div class="h3 font-weight-bold text-warning"><?php echo $thongKeTrangThai['Đang bảo trì']; ?></div>
                    <small class="text-muted">phòng</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Biểu đồ phân bổ trạng thái -->
        <div class="col-xl-6 col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Phân bổ trạng thái phòng</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                    <div class="mt-3 text-center small">
                        <span class="mr-3">
                            <i class="fas fa-circle text-success"></i> Trống
                        </span>
                        <span class="mr-3">
                            <i class="fas fa-circle text-primary"></i> Đang sử dụng
                        </span>
                        <span class="mr-3">
                            <i class="fas fa-circle text-warning"></i> Bảo trì
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê hạng phòng -->
        <div class="col-xl-6 col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Phân bổ theo hạng phòng</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Hạng phòng</th>
                                    <th>Số lượng</th>
                                    <th>Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($thongKeHangPhong as $hang => $soluong): ?>
                                    <tr>
                                        <td><?php echo $hang; ?></td>
                                        <td><?php echo $soluong; ?> phòng</td>
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

    <!-- Nút chuyển đến quản lý chi tiết -->
    <div class="row">
        <div class="col-md-12 text-center">
            <a href="quanlyphong.php" class="btn btn-primary">
                <i class="fas fa-list me-2"></i>Quản lý chi tiết phòng
            </a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ trạng thái phòng
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('statusPieChart').getContext('2d');
        const statusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Trống', 'Đang sử dụng', 'Đang bảo trì'],
                datasets: [{
                    data: [
                        <?php echo $thongKeTrangThai['Trống']; ?>,
                        <?php echo $thongKeTrangThai['Đang sử dụng']; ?>,
                        <?php echo $thongKeTrangThai['Đang bảo trì']; ?>
                    ],
                    backgroundColor: ['#28a745', '#007bff', '#ffc107'],
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
                    }
                }
            }
        });
    });
</script>

<?php include '../layouts/footer.php'; ?>