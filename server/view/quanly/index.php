<?php
// Thiết lập tiêu đề trang
$pageTitle = "Dashboard Quản Lý";

// Include header
require_once '../layouts/header.php';

// Khởi tạo controller và lấy dữ liệu
require_once '../../controller/quanly.controller.php';
$controller = new QuanLyController();
$data = $controller->getThongKeTongQuan();
$chartDataJson = $controller->getChartDataJson();

// Xử lý lỗi nếu không có dữ liệu
if (!$data) {
    echo "<div class='error-message'>Không thể tải dữ liệu thống kê. Vui lòng thử lại sau.</div>";
    require_once '../layouts/footer.php';
    exit();
}
?>
<!-- Thêm Chart.js và Font Awesome -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --primary: #4361ee;
        --secondary: #3a0ca3;
        --success: #4cc9f0;
        --info: #4895ef;
        --warning: #f72585;
        --danger: #e63946;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --light-gray: #e9ecef;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fb;
        color: #333;
        line-height: 1.6;
    }

    .main-content {
        padding: 25px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        border-radius: 16px;
        color: white;
        box-shadow: 0 10px 30px rgba(67, 97, 238, 0.2);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }

    .page-header p {
        font-size: 16px;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        border-left: 5px solid var(--primary);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
    }

    .stat-card:nth-child(1) { border-left-color: #4361ee; }
    .stat-card:nth-child(2) { border-left-color: #4cc9f0; }
    .stat-card:nth-child(3) { border-left-color: #4895ef; }
    .stat-card:nth-child(4) { border-left-color: #f72585; }
    .stat-card:nth-child(5) { border-left-color: #7209b7; }
    .stat-card:nth-child(6) { border-left-color: #e63946; }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
    .stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #4cc9f0, #4895ef); }
    .stat-card:nth-child(3) .stat-icon { background: linear-gradient(135deg, #4895ef, #4361ee); }
    .stat-card:nth-child(4) .stat-icon { background: linear-gradient(135deg, #f72585, #b5179e); }
    .stat-card:nth-child(5) .stat-icon { background: linear-gradient(135deg, #7209b7, #560bad); }
    .stat-card:nth-child(6) .stat-icon { background: linear-gradient(135deg, #e63946, #d00000); }

    .stat-content {
        flex: 1;
    }

    .stat-title {
        font-size: 14px;
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
        line-height: 1.2;
    }

    .stat-subtitle {
        font-size: 14px;
        color: var(--gray);
        font-weight: 400;
    }

    /* Charts Section */
    .charts-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    @media (max-width: 1100px) {
        .charts-section {
            grid-template-columns: 1fr;
        }
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .chart-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--light-gray);
    }

    .chart-title {
        font-size: 18px;
        color: var(--dark);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chart-title i {
        color: var(--primary);
    }

    .chart-actions {
        display: flex;
        gap: 10px;
    }

    .btn-refresh {
        background: var(--light);
        border: 1px solid var(--light-gray);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 14px;
        color: var(--gray);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }

    .btn-refresh:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .chart-container {
        width: 100%;
        height: 300px;
        position: relative;
    }

    /* Dashboard Content */
    .dashboard-content {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 25px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .dashboard-content {
            grid-template-columns: 1fr;
        }
    }

    /* Card Styling */
    .card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 25px;
    }

    .card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    .card:last-child {
        margin-bottom: 0;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--light-gray);
    }

    .card-title {
        font-size: 18px;
        color: var(--dark);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .view-all {
        color: var(--primary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        padding: 6px 15px;
        border: 1px solid var(--primary);
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .view-all:hover {
        background-color: var(--primary);
        color: white;
        text-decoration: none;
    }

    /* Tables */
    .table-responsive {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid var(--light-gray);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    thead {
        background-color: var(--light);
    }

    th {
        padding: 16px;
        text-align: left;
        font-weight: 600;
        color: var(--dark);
        border-bottom: 2px solid var(--light-gray);
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        padding: 16px;
        border-bottom: 1px solid var(--light-gray);
        color: var(--dark);
        font-size: 14px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background-color: #f9fafc;
    }

    /* Status badges */
    .status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-empty {
        background-color: rgba(76, 201, 240, 0.1);
        color: #4cc9f0;
    }

    .status-occupied {
        background-color: rgba(247, 37, 133, 0.1);
        color: #f72585;
    }

    .status-maintenance {
        background-color: rgba(230, 57, 70, 0.1);
        color: #e63946;
    }

    .status-staying {
        background-color: rgba(67, 97, 238, 0.1);
        color: #4361ee;
    }

    .status-not-staying {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    /* Recent Bookings */
    .recent-bookings {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .booking-item {
        padding: 18px;
        background: var(--light);
        border-radius: 12px;
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
    }

    .booking-item:hover {
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .booking-code {
        font-size: 15px;
        font-weight: 600;
        color: var(--dark);
    }

    .booking-amount {
        font-size: 18px;
        font-weight: 700;
        color: var(--success);
    }

    .booking-details {
        display: flex;
        gap: 15px;
        font-size: 14px;
        color: var(--gray);
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .booking-date {
        font-size: 13px;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* No data message */
    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray);
        font-style: italic;
        background-color: var(--light);
        border-radius: 12px;
        border: 2px dashed var(--light-gray);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-content {
            padding: 15px;
        }
        
        .page-header {
            padding: 20px;
        }
        
        .page-header h1 {
            font-size: 24px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .stat-card {
            padding: 20px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
        
        .stat-value {
            font-size: 24px;
        }
        
        .chart-card, .card {
            padding: 20px;
        }
        
        th, td {
            padding: 12px;
        }
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<div class="main-content">
    <!-- Header trang -->
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard Quản Lý</h1>
        <p>Tổng quan hệ thống khách sạn • Cập nhật: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-hotel"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Tổng số phòng</div>
                <div class="stat-value"><?php echo number_format($data['tongPhong']); ?></div>
                <div class="stat-subtitle"><?php echo number_format($data['phongTrong']); ?> phòng trống</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Tổng khách hàng</div>
                <div class="stat-value"><?php echo number_format($data['tongKhachHang']); ?></div>
                <div class="stat-subtitle"><?php echo number_format($data['khachDangO']); ?> đang ở</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Nhân viên</div>
                <div class="stat-value"><?php echo number_format($data['tongNhanVien']); ?></div>
                <div class="stat-subtitle">Đang làm việc</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-concierge-bell"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Dịch vụ</div>
                <div class="stat-value"><?php echo number_format($data['tongDichVu']); ?></div>
                <div class="stat-subtitle">Khả dụng</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Doanh thu hôm nay</div>
                <div class="stat-value"><?php echo $data['doanhThuHomNay']; ?> ₫</div>
                <div class="stat-subtitle">Tính đến hiện tại</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Phòng sử dụng</div>
                <div class="stat-value"><?php echo number_format($data['phongDangSuDung']); ?></div>
                <div class="stat-subtitle">Đang được sử dụng</div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="charts-section">
        <!-- Biểu đồ phân bố phòng -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-pie-chart"></i> Phân bố phòng</h3>
                <div class="chart-actions">
                    <button onclick="refreshRoomChart()" class="btn-refresh" title="Làm mới">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="roomChart"></canvas>
            </div>
        </div>

        <!-- Biểu đồ doanh thu -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-line"></i> Doanh thu 7 ngày</h3>
                <div class="chart-actions">
                    <button onclick="refreshRevenueChart()" class="btn-refresh" title="Làm mới">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Nội dung chính -->
    <div class="dashboard-content">
        <!-- Cột chính -->
        <div class="main-column">
            <!-- Phòng cần chú ý -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> Phòng cần chú ý</h3>
                    <a href="../quanly/quanlyphong.php" class="view-all">Xem tất cả</a>
                </div>
                
                <?php if (!empty($data['phongCanChuY'])): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã phòng</th>
                                    <th>Số phòng</th>
                                    <th>Khách hàng</th>
                                    <th>Trạng thái</th>
                                    <th>Check-out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['phongCanChuY'] as $phong): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($phong['MaPhong']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($phong['SoPhong']); ?></td>
                                        <td><?php echo htmlspecialchars($phong['TenKhach'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = 'status-occupied';
                                            if ($phong['TrangThai'] == 'Trống') {
                                                $statusClass = 'status-empty';
                                            } elseif ($phong['TrangThai'] == 'Bảo trì') {
                                                $statusClass = 'status-maintenance';
                                            }
                                            ?>
                                            <span class="status <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($phong['TrangThai']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($phong['NgayTra'])): ?>
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    <span><?php echo date('d/m/Y H:i', strtotime($phong['NgayTra'])); ?></span>
                                                    <?php if (isset($phong['HoursRemaining']) && $phong['HoursRemaining'] < 24 && $phong['HoursRemaining'] > 0): ?>
                                                        <small style="color: #e63946; font-weight: 600;">
                                                            <i class="fas fa-clock"></i> Còn <?php echo (int)$phong['HoursRemaining']; ?> giờ
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: #95a5a6;">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Không có phòng nào cần chú ý.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar-column">
            <!-- Hóa đơn gần đây -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-receipt"></i> Hóa đơn gần đây</h3>
                    <a href="../quanly/quanlyhoadon.php" class="view-all">Xem tất cả</a>
                </div>
                
                <?php if (!empty($data['hoaDonGanDay'])): ?>
                    <div class="recent-bookings">
                        <?php foreach ($data['hoaDonGanDay'] as $hoadon): ?>
                            <div class="booking-item">
                                <div class="booking-header">
                                    <span class="booking-code">HD#<?php echo $hoadon['Id']; ?></span>
                                    <span class="booking-amount"><?php echo number_format($hoadon['TongTien'], 0, ',', '.'); ?> ₫</span>
                                </div>
                                <div class="booking-details">
                                    <span><i class="fas fa-door-closed"></i> <?php echo htmlspecialchars($hoadon['SoPhong'] ?? $hoadon['MaPhong']); ?></span>
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars(substr($hoadon['TenKhach'] ?? 'N/A', 0, 15)); ?></span>
                                </div>
                                <div class="booking-date">
                                    <span class="status <?php 
                                        echo $hoadon['TrangThai'] == 'DaThanhToan' ? 'status-staying' : 
                                            ($hoadon['TrangThai'] == 'ChuaThanhToan' ? 'status-occupied' : 'status-maintenance');
                                    ?>">
                                        <i class="fas fa-<?php echo $hoadon['TrangThai'] == 'DaThanhToan' ? 'check-circle' : 'clock'; ?>"></i>
                                        <?php 
                                        $trangThaiText = $hoadon['TrangThai'] == 'DaThanhToan' ? 'Đã TT' : 
                                                        ($hoadon['TrangThai'] == 'ChuaThanhToan' ? 'Chưa TT' : $hoadon['TrangThai']);
                                        echo htmlspecialchars($trangThaiText); 
                                        ?>
                                    </span>
                                    <span><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($hoadon['NgayTao'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Chưa có hóa đơn nào.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Thông tin nhanh -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Thông tin nhanh</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--light-gray);">
                        <span style="color: var(--gray);">Tổng hóa đơn:</span>
                        <span style="font-weight: 600; color: var(--dark);"><?php echo number_format($data['tongHoaDon'] ?? 0); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--light-gray);">
                        <span style="color: var(--gray);">Chưa thanh toán:</span>
                        <span style="font-weight: 600; color: var(--warning);"><?php echo number_format($data['hoaDonChuaThanhToan'] ?? 0); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--gray);">Doanh thu tháng:</span>
                        <span style="font-weight: 600; color: var(--success);"><?php echo $data['doanhThuThangNay'] ?? '0'; ?> ₫</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Biến toàn cục cho biểu đồ
    let roomChart = null;
    let revenueChart = null;

    // Màu sắc cho biểu đồ
    const chartColors = {
        'Trống': '#4cc9f0',
        'Đang sử dụng': '#4361ee',
        'Bảo trì': '#e63946',
        'Đặt trước': '#f72585',
        'default': '#6c757d'
    };

    // Khởi tạo biểu đồ khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        
        // Tự động làm mới biểu đồ mỗi 2 phút
        setInterval(refreshAllCharts, 120000);
    });

    function initCharts() {
        try {
            // Parse dữ liệu từ PHP
            const chartData = <?php echo $chartDataJson; ?>;
            
            // Tạo biểu đồ phân bố phòng
            createRoomChart(chartData);
            
            // Tạo biểu đồ doanh thu
            createRevenueChart(chartData);
        } catch (error) {
            console.error('Lỗi khởi tạo biểu đồ:', error);
            createSampleCharts();
        }
    }

    function createRoomChart(chartData) {
        const ctx = document.getElementById('roomChart').getContext('2d');
        
        // Chuẩn bị dữ liệu
        const labels = chartData.room_labels || [];
        const data = chartData.room_data || [];
        
        // Tạo màu tương ứng
        const backgroundColors = chartData.room_colors || labels.map(label => chartColors[label] || chartColors.default);
        
        // Hủy biểu đồ cũ nếu tồn tại
        if (roomChart) {
            roomChart.destroy();
        }
        
        roomChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 13,
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} phòng (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '65%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    }

    function createRevenueChart(chartData) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        const labels = chartData.revenue?.labels || [];
        const data = chartData.revenue?.data || [];
        
        // Hủy biểu đồ cũ nếu tồn tại
        if (revenueChart) {
            revenueChart.destroy();
        }
        
        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4361ee',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const value = context.raw || 0;
                                return `Doanh thu: ${formatCurrency(value)} VNĐ`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }

    function formatCurrency(value) {
        if (value >= 1000000000) {
            return (value / 1000000000).toFixed(1) + ' Tỷ';
        } else if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + ' Triệu';
        } else if (value >= 1000) {
            return (value / 1000).toFixed(1) + ' Nghìn';
        }
        return value.toString();
    }

    function refreshRoomChart() {
        // Thêm hiệu ứng loading
        const btn = event.target.closest('.btn-refresh');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        setTimeout(() => {
            location.reload();
        }, 500);
    }

    function refreshRevenueChart() {
        const btn = event.target.closest('.btn-refresh');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        setTimeout(() => {
            location.reload();
        }, 500);
    }

    function refreshAllCharts() {
        location.reload();
    }

    // Tạo biểu đồ mẫu nếu có lỗi
    function createSampleCharts() {
        const sampleData = {
            room_labels: ['Trống', 'Đang sử dụng', 'Bảo trì'],
            room_data: [<?php echo $data['phongTrong']; ?>, <?php echo $data['phongDangSuDung']; ?>, <?php echo $data['phongBaoTri']; ?>],
            room_colors: ['#4cc9f0', '#4361ee', '#e63946'],
            revenue: {
                labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                data: [1200000, 1900000, 3000000, 2500000, 2200000, 3000000, 2800000]
            }
        };
        
        createRoomChart(sampleData);
        createRevenueChart(sampleData);
    }
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>