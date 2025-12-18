<?php
// server/view/letan/index.php - TỐI ƯU HÓA
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || !isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'letan') {
    header('Location: ../../../client/view/login.php?error=Vui lòng đăng nhập với vai trò lễ tân');
    exit;
}

// Lấy thông tin user
$user = $_SESSION['user'];
$role = $_SESSION['vaitro'];

// Kết nối database
require_once __DIR__ . '/../../model/connectDB.php';
$connect = new Connect();
$conn = $connect->openConnect();

// TỐI ƯU: Dùng 1 query duy nhất để lấy tất cả thống kê
$stats = [];
try {
    // Query 1: Lấy thống kê hóa đơn
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN TrangThai = 'DaThanhToan' THEN 1 ELSE 0 END) as paid,
                SUM(CASE WHEN TrangThai = 'ChuaThanhToan' THEN 1 ELSE 0 END) as unpaid,
                SUM(CASE WHEN TrangThai = 'DaThanhToan' THEN TongTien ELSE 0 END) as revenue
              FROM hoadondatphong";
    
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_hoadon'] = $row['total'] ?? 0;
        $stats['paid_hoadon'] = $row['paid'] ?? 0;
        $stats['unpaid_hoadon'] = $row['unpaid'] ?? 0;
        $stats['total_revenue'] = number_format($row['revenue'] ?? 0, 0, ',', '.');
    }

    // Query 2: Lấy thống kê khách hàng
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN MaTaiKhoan != 0 THEN 1 ELSE 0 END) as with_account
              FROM khachhang";
    
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_customers'] = $row['total'] ?? 0;
        $stats['customers_with_account'] = $row['with_account'] ?? 0;
    }

    // Query 3: Lấy 5 hóa đơn mới nhất (chỉ lấy các trường cần thiết)
    $query = "SELECT hd.Id, hd.MaKhachHang, hd.MaPhong, hd.TongTien, hd.TrangThai, 
                     kh.HoTen, kh.SoDienThoai 
              FROM hoadondatphong hd
              LEFT JOIN khachhang kh ON hd.MaKhachHang = kh.MaKH
              ORDER BY hd.NgayTao DESC 
              LIMIT 5";
    
    $result = mysqli_query($conn, $query);
    $recent_hoadon = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_hoadon[] = $row;
        }
    }

    // Query 4: Lấy 5 khách hàng mới nhất (chỉ lấy các trường cần thiết)
    $query = "SELECT kh.MaKH, kh.HoTen, kh.SoDienThoai, kh.MaTaiKhoan, tk.Email 
              FROM khachhang kh
              LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id
              ORDER BY kh.created_at DESC 
              LIMIT 5";
    
    $result = mysqli_query($conn, $query);
    $recent_customers = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_customers[] = $row;
        }
    }

    // Query 5: Lấy danh sách phòng trống (cho button đặt phòng)
    $query = "SELECT MaPhong, SoPhong, roomName, GiaPhong, TrangThai 
              FROM phong 
              WHERE TrangThai = 'Trống' 
              ORDER BY SoPhong ASC 
              LIMIT 10";
    
    $result = mysqli_query($conn, $query);
    $available_rooms = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $available_rooms[] = $row;
        }
    }

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
}

$connect->closeConnect($conn);

// Include header
require_once '../layouts/header.php';
?>

<!-- CSS cho trang dashboard - GIẢM BỚT ANIMATION -->
<style>
    :root {
        --primary: #1e88e5;
        --secondary: #43a047;
        --warning: #ffa000;
        --danger: #e53935;
        --info: #00acc1;
        --light: #f8f9fa;
        --dark: #343a40;
    }

    .stat-card {
        border-radius: 10px;
        border: 1px solid #e9ecef;
        background: white;
        transition: transform 0.2s;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .stat-card .card-body {
        padding: 1.25rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
    }

    .stat-icon i {
        font-size: 1.5rem;
        color: white;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    .recent-table {
        background: white;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .recent-table .table {
        margin-bottom: 0;
        font-size: 0.9rem;
    }

    .recent-table .table thead {
        background-color: #f8f9fa;
        color: #495057;
    }

    .recent-table .table tbody tr {
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .recent-table .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .status-badge {
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-paid {
        background-color: #d1fae5;
        color: #065f46;
    }

    .badge-unpaid {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-account {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .badge-noaccount {
        background-color: #f3f4f6;
        color: #374151;
    }

    .welcome-card {
        background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
        color: white;
        border-radius: 12px;
        overflow: hidden;
    }

    .quick-actions .btn-action {
        padding: 12px;
        border-radius: 8px;
        background: white;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
        text-align: center;
        color: #495057;
        font-weight: 500;
        text-decoration: none;
        display: block;
    }

    .quick-actions .btn-action:hover {
        border-color: #1e88e5;
        color: #1e88e5;
        text-decoration: none;
    }

    .quick-actions .btn-action i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #1e88e5;
    }

    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        border: 1px solid #e9ecef;
        height: 100%;
    }

    .room-booking-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }

    .room-item {
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
        background: white;
    }

    .room-item:hover {
        border-color: #1e88e5;
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .room-number {
        font-weight: bold;
        font-size: 1.1rem;
        color: #1e88e5;
    }

    .room-price {
        font-size: 0.85rem;
        color: #28a745;
        margin-top: 5px;
    }

    .room-status {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 3px;
    }

    @media (max-width: 768px) {
        .room-booking-grid {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        }
        
        .stat-value {
            font-size: 1.3rem;
        }
        
        .quick-actions .btn-action {
            padding: 10px;
        }
    }
</style>

<div class="container-fluid py-3">
    <!-- Welcome Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="welcome-card p-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold mb-1">Xin chào, <?php echo htmlspecialchars($user['username']); ?>!</h4>
                        <p class="mb-0 opacity-90">
                            <i class="fas fa-user-tag me-2"></i>Lễ tân |
                            <i class="fas fa-calendar-alt me-2 ms-3"></i><?php echo date('d/m/Y H:i'); ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="bg-white text-primary rounded-pill px-3 py-1 d-inline-block">
                            <i class="fas fa-hotel me-2"></i>
                            <span class="fw-bold">TỎA SÁNG RESORT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Thao tác nhanh</h6>
                </div>
                <div class="card-body py-2">
                    <div class="row quick-actions g-2">
                        <div class="col-md-3 col-6">
                            <a href="letandatphong.php" class="btn-action">
                                <i class="fas fa-calendar-check"></i>
                                <div class="mt-1">Quản lý đặt phòng</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="letanlogon.php" class="btn-action">
                                <i class="fas fa-user-plus"></i>
                                <div class="mt-1">Đăng ký khách hàng</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="letandatphong.php" class="btn-action">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <div class="mt-1">Hóa đơn & Thanh toán</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <button class="btn-action w-100 border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#quickBookingModal">
                                <i class="fas fa-plus-circle"></i>
                                <div class="mt-1">Đặt phòng nhanh</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-3">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="stat-value text-primary"><?php echo $stats['total_hoadon'] ?? 0; ?></div>
                    <div class="stat-label">Tổng hóa đơn</div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            <?php echo $stats['paid_hoadon'] ?? 0; ?> đã thanh toán
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value text-success"><?php echo $stats['total_revenue'] ?? '0'; ?> đ</div>
                    <div class="stat-label">Doanh thu</div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-chart-line me-1"></i>
                            Tổng doanh thu
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ffa000 0%, #f57c00 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value text-warning"><?php echo $stats['total_customers'] ?? 0; ?></div>
                    <div class="stat-label">Khách hàng</div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-user-check text-info me-1"></i>
                            <?php echo $stats['customers_with_account'] ?? 0; ?> có tài khoản
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e53935 0%, #c62828 100%);">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-value text-danger"><?php echo $stats['unpaid_hoadon'] ?? 0; ?></div>
                    <div class="stat-label">Chờ thanh toán</div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-clock text-warning me-1"></i>
                            Cần xử lý
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ và Recent Activities -->
    <div class="row mb-3">
        <!-- Biểu đồ đơn giản -->
        <div class="col-xl-8 mb-3">
            <div class="chart-container">
                <h6 class="mb-2"><i class="fas fa-chart-bar text-primary me-2"></i>Thống kê hóa đơn</h6>
                <div style="height: 200px;">
                    <canvas id="invoiceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tỷ lệ thanh toán -->
        <div class="col-xl-4 mb-3">
            <div class="chart-container">
                <h6 class="mb-2"><i class="fas fa-percentage text-success me-2"></i>Tỷ lệ thanh toán</h6>
                <div class="text-center py-3">
                    <div class="mb-2">
                        <div class="position-relative d-inline-block">
                            <canvas id="paymentChart" width="120" height="120"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <h5 class="mb-0 fw-bold">
                                    <?php 
                                    $total = $stats['total_hoadon'] ?? 1;
                                    $paid = $stats['paid_hoadon'] ?? 0;
                                    echo $total > 0 ? round(($paid / $total) * 100) : 0; 
                                    ?>%
                                </h5>
                                <small class="text-muted">Đã TT</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-success">
                                <i class="fas fa-circle me-1"></i>
                                <small>Đã TT: <?php echo $stats['paid_hoadon'] ?? 0; ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-warning">
                                <i class="fas fa-circle me-1"></i>
                                <small>Chưa TT: <?php echo $stats['unpaid_hoadon'] ?? 0; ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <!-- Hóa đơn gần đây -->
        <div class="col-xl-6 mb-3">
            <div class="recent-table">
                <div class="card border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0"><i class="fas fa-clock text-primary me-2"></i>Hóa đơn gần đây</h6>
                        <a href="letandatphong.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="60">Mã</th>
                                        <th>Khách hàng</th>
                                        <th width="80">Phòng</th>
                                        <th width="120">Tổng tiền</th>
                                        <th width="100">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_hoadon)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted">
                                                <i class="fas fa-inbox me-1"></i>Chưa có hóa đơn
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_hoadon as $hd): ?>
                                            <tr onclick="window.location.href='letandatphong.php'" style="cursor: pointer;">
                                                <td><small class="text-muted">#<?php echo $hd['Id']; ?></small></td>
                                                <td>
                                                    <div class="fw-medium small"><?php echo htmlspecialchars($hd['HoTen'] ?? 'N/A'); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($hd['SoDienThoai'] ?? ''); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($hd['MaPhong']); ?></span>
                                                </td>
                                                <td class="fw-bold text-success small">
                                                    <?php echo number_format($hd['TongTien'], 0, ',', '.'); ?> đ
                                                </td>
                                                <td>
                                                    <?php if ($hd['TrangThai'] == 'DaThanhToan'): ?>
                                                        <span class="status-badge badge-paid">
                                                            <i class="fas fa-check-circle me-1"></i>Đã TT
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-badge badge-unpaid">
                                                            <i class="fas fa-clock me-1"></i>Chưa TT
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khách hàng mới -->
        <div class="col-xl-6 mb-3">
            <div class="recent-table">
                <div class="card border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0"><i class="fas fa-user-plus text-success me-2"></i>Khách hàng mới</h6>
                        <a href="letanlogon.php" class="btn btn-sm btn-outline-success">Thêm mới</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="80">Mã KH</th>
                                        <th>Họ tên</th>
                                        <th width="100">SĐT</th>
                                        <th width="100">Tài khoản</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_customers)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted">
                                                <i class="fas fa-users me-1"></i>Chưa có khách hàng
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_customers as $kh): ?>
                                            <tr onclick="window.location.href='letanlogon.php'" style="cursor: pointer;">
                                                <td><small class="fw-bold"><?php echo htmlspecialchars($kh['MaKH']); ?></small></td>
                                                <td class="fw-medium small"><?php echo htmlspecialchars($kh['HoTen']); ?></td>
                                                <td class="small"><?php echo htmlspecialchars($kh['SoDienThoai']); ?></td>
                                                <td>
                                                    <?php if ($kh['MaTaiKhoan'] != 0): ?>
                                                        <span class="status-badge badge-account">
                                                            <i class="fas fa-check-circle me-1"></i>Có TK
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-badge badge-noaccount">
                                                            <i class="fas fa-times-circle me-1"></i>Chưa có
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal đặt phòng nhanh - HIỂN THỊ PHÒNG TRỐNG -->
<div class="modal fade" id="quickBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Đặt phòng nhanh</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($available_rooms)): ?>
                    <p class="mb-3">Chọn phòng để đặt:</p>
                    <div class="room-booking-grid">
                        <?php foreach ($available_rooms as $room): ?>
                            <div class="room-item" 
                                 onclick="bookRoom('<?php echo $room['MaPhong']; ?>', '<?php echo $room['roomName']; ?>', <?php echo $room['GiaPhong']; ?>)">
                                <div class="room-number"><?php echo htmlspecialchars($room['SoPhong']); ?></div>
                                <div class="room-name small"><?php echo htmlspecialchars($room['roomName']); ?></div>
                                <div class="room-price">
                                    <?php echo number_format($room['GiaPhong'], 0, ',', '.'); ?> đ/đêm
                                </div>
                                <div class="room-status">
                                    <i class="fas fa-circle text-success me-1"></i>Trống
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="letandatphong.php" class="btn btn-outline-primary">
                            <i class="fas fa-list me-1"></i>Xem tất cả phòng
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-bed fa-2x text-muted mb-3"></i>
                        <h6>Hiện không có phòng trống</h6>
                        <p class="text-muted">Vui lòng kiểm tra lại sau</p>
                        <a href="letandatphong.php" class="btn btn-primary">
                            <i class="fas fa-calendar-alt me-1"></i>Quản lý đặt phòng
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Biểu đồ hóa đơn
document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu cho biểu đồ
    const paid = <?php echo $stats['paid_hoadon'] ?? 0; ?>;
    const unpaid = <?php echo $stats['unpaid_hoadon'] ?? 0; ?>;
    
    // Biểu đồ cột
    const invoiceCtx = document.getElementById('invoiceChart').getContext('2d');
    new Chart(invoiceCtx, {
        type: 'bar',
        data: {
            labels: ['Đã thanh toán', 'Chưa thanh toán'],
            datasets: [{
                data: [paid, unpaid],
                backgroundColor: ['#10b981', '#f59e0b'],
                borderColor: ['#0da271', '#d97706'],
                borderWidth: 1,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value;
                            }
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ tròn
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Đã thanh toán', 'Chưa thanh toán'],
            datasets: [{
                data: [paid, unpaid],
                backgroundColor: ['#10b981', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});

// Hàm đặt phòng nhanh
function bookRoom(roomCode, roomName, roomPrice) {
    if (confirm(`Bạn muốn đặt phòng ${roomCode} - ${roomName}?\nGiá: ${roomPrice.toLocaleString()} đ/đêm\n\nSẽ chuyển đến trang đặt phòng của khách hàng.`)) {
        // Chuyển đến trang đặt phòng của client với thông tin phòng
        const url = `http://localhost/ABC-Resort/client/index.php?room=${roomCode}&roomName=${encodeURIComponent(roomName)}&price=${roomPrice}`;
        window.open(url, '_blank');
        
        // Đóng modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('quickBookingModal'));
        modal.hide();
    }
}

// Auto refresh page every 2 minutes (giảm xuống 2 phút)
setTimeout(() => {
    location.reload();
}, 120000);
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>