<?php
// server/view/letan/letandatphong.php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'letan') {
    header('Location: ../login/login.php');
    exit();
}

require_once __DIR__ . '/../../model/letandatphong.model.php';

// Xử lý cập nhật trạng thái (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['trangthai'])) {
    $id = intval($_POST['id']);
    $trangthai = $_POST['trangthai'];
    
    $model = new LetanDatPhongModel();
    $result = $model->updateTrangThai($id, $trangthai);
    
    if ($result) {
        $_SESSION['success'] = "Cập nhật trạng thái thành công!";
    } else {
        $_SESSION['error'] = "Cập nhật trạng thái thất bại!";
    }
    
    header('Location: letandatphong.php');
    exit();
}

// Lấy danh sách hóa đơn
$model = new LetanDatPhongModel();

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

if (!empty($search)) {
    $hoadon = $model->searchHoaDon($search);
} else {
    $hoadon = $model->getAllHoaDon();
}

// Lọc theo trạng thái nếu có
if (!empty($status)) {
    $hoadon = array_filter($hoadon, function($item) use ($status) {
        return $item['TrangThai'] == $status;
    });
    $hoadon = array_values($hoadon); // Reset keys
}

$total = count($hoadon);
$paid = 0;
$totalRevenue = 0;

foreach ($hoadon as $hd) {
    if ($hd['TrangThai'] == 'DaThanhToan') {
        $paid++;
        $totalRevenue += floatval($hd['TongTien']);
    }
}

$unpaid = $total - $paid;
$paymentRate = $total > 0 ? round(($paid / $total) * 100) : 0;

// Include header
include_once '../layouts/header.php';
?>

<!-- CSS riêng cho trang -->
<style>
    .status-badge {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.9em;
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }
    .status-paid {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    .status-unpaid {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .btn-toggle {
        min-width: 90px;
    }
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>

<div class="py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i> Quản lý hóa đơn</h3>
        <span class="badge bg-light text-dark fs-6">
            <i class="fas fa-list me-1"></i>Tổng: <?php echo $total; ?> hóa đơn
        </span>
    </div>
    
    <!-- Thông báo -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Bộ lọc -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm mã KH, mã phòng, ID..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="DaThanhToan" <?php echo $status == 'DaThanhToan' ? 'selected' : ''; ?>>Đã thanh toán</option>
                        <option value="ChuaThanhToan" <?php echo $status == 'ChuaThanhToan' ? 'selected' : ''; ?>>Chưa thanh toán</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Tìm
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="letandatphong.php" class="btn btn-secondary w-100">
                        <i class="fas fa-times me-1"></i>Xóa lọc
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Thống kê -->
    <div class="row mb-3 g-3">
        <div class="col-md-3">
            <div class="card border-primary stat-card">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-file-invoice fa-2x"></i>
                    </div>
                    <h4 class="text-primary mb-1"><?php echo $total; ?></h4>
                    <p class="text-muted mb-0">Tổng hóa đơn</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success stat-card">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h4 class="text-success mb-1"><?php echo $paid; ?></h4>
                    <p class="text-muted mb-0">Đã thanh toán</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning stat-card">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h4 class="text-warning mb-1"><?php echo $unpaid; ?></h4>
                    <p class="text-muted mb-0">Chưa thanh toán</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info stat-card">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-percentage fa-2x"></i>
                    </div>
                    <h4 class="text-info mb-1"><?php echo $paymentRate; ?>%</h4>
                    <p class="text-muted mb-0">Tỷ lệ thanh toán</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bảng hóa đơn -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Mã KH</th>
                            <th>Phòng</th>
                            <th>Ngày nhận/trả</th>
                            <th>Số đêm</th>
                            <th>Tổng tiền</th>
                            <th>Phương thức TT</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hoadon)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i><br>
                                    Không tìm thấy hóa đơn nào
                                    <?php if (!empty($search) || !empty($status)): ?>
                                        <div class="mt-2">
                                            <a href="letandatphong.php" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-redo me-1"></i>Hiển thị tất cả
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($hoadon as $hd): ?>
                                <?php
                                // Parse danh sách khách
                                $danhSachKhach = json_decode($hd['DanhSachKhach'] ?? '[]', true);
                                $soKhach = is_array($danhSachKhach) ? count($danhSachKhach) : 0;
                                ?>
                                <tr>
                                    <td><strong class="text-primary">#<?php echo $hd['Id']; ?></strong></td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                            <?php echo htmlspecialchars($hd['MaKhachHang']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                            <?php echo htmlspecialchars($hd['MaPhong']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">Nhận:</small>
                                        <?php echo date('d/m/Y', strtotime($hd['NgayNhan'])); ?>
                                        <br>
                                        <small class="d-block text-muted">Trả:</small>
                                        <?php echo date('d/m/Y', strtotime($hd['NgayTra'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            <?php echo $hd['SoDem']; ?> đêm
                                        </span>
                                        <br>
                                        <small class="text-muted"><?php echo $soKhach; ?> người</small>
                                    </td>
                                    <td class="fw-bold text-success">
                                        <?php echo number_format($hd['TongTien'], 0, ',', '.'); ?> đ
                                    </td>
                                    <td>
                                        <?php 
                                        $pttt = $hd['PhuongThucThanhToan'] ?? '';
                                        if ($pttt == 'TienMat'): ?>
                                            <span class="badge bg-success">Tiền mặt</span>
                                        <?php elseif ($pttt == 'Momo'): ?>
                                            <span class="badge bg-purple" style="background-color: #b200ff;">Momo</span>
                                        <?php elseif ($pttt == 'TheNganHang'): ?>
                                            <span class="badge bg-primary">Thẻ NH</span>
                                        <?php elseif ($pttt == 'ChuyenKhoan'): ?>
                                            <span class="badge bg-info">Chuyển khoản</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($pttt); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hd['TrangThai'] == 'DaThanhToan'): ?>
                                            <span class="status-badge status-paid">
                                                <i class="fas fa-check-circle me-1"></i>Đã TT
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-unpaid">
                                                <i class="fas fa-clock me-1"></i>Chưa TT
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Nút xem chi tiết -->
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewDetail(<?php echo $hd['Id']; ?>)"
                                                    title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Nút chuyển trạng thái -->
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $hd['Id']; ?>">
                                                <input type="hidden" name="trangthai" 
                                                       value="<?php echo $hd['TrangThai'] == 'DaThanhToan' ? 'ChuaThanhToan' : 'DaThanhToan'; ?>">
                                                <button type="submit" 
                                                        class="btn btn-sm <?php echo $hd['TrangThai'] == 'DaThanhToan' ? 'btn-warning' : 'btn-success'; ?> btn-toggle"
                                                        title="<?php echo $hd['TrangThai'] == 'DaThanhToan' ? 'Hủy thanh toán' : 'Xác nhận thanh toán'; ?>">
                                                    <?php if ($hd['TrangThai'] == 'DaThanhToan'): ?>
                                                        <i class="fas fa-times-circle me-1"></i>Hủy TT
                                                    <?php else: ?>
                                                        <i class="fas fa-check-circle me-1"></i>Đã TT
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </div>
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

<!-- Modal xem chi tiết -->
<div class="modal fade" id="detailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice me-2"></i>Chi tiết hóa đơn
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Nội dung sẽ được load ở đây -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="fas fa-print me-1"></i>In hóa đơn
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Hàm xem chi tiết hóa đơn
async function viewDetail(id) {
    try {
        console.log('Đang tải chi tiết hóa đơn ID:', id);
        
        // Hiển thị loading
        document.getElementById('detailContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Đang tải dữ liệu hóa đơn...</p>
            </div>
        `;
        
        // Hiển thị modal
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        modal.show();
        
        // Tạo URL API (điều chỉnh đường dẫn nếu cần)
        const url = `../../controller/letandatphong.controller.php?action=chitiet&id=${id}`;
        console.log('API URL:', url);
        
        // Gọi API lấy chi tiết
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API Response:', data);
        
        if (data.error) {
            document.getElementById('detailContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${data.error}
                </div>
            `;
            return;
        }
        
        // Parse danh sách khách
        let guestList = '';
        try {
            const guests = JSON.parse(data.DanhSachKhach || '[]');
            if (Array.isArray(guests) && guests.length > 0) {
                guestList = `
                    <div class="mt-3">
                        <h6><i class="fas fa-users me-2"></i>Danh sách khách (${data.SoNguoi || guests.length} người)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr><th>Tên</th><th>Điện thoại</th><th>CMND</th></tr>
                                </thead>
                                <tbody>
                                    ${guests.map(g => `<tr>
                                        <td>${g.HoTen || ''}</td>
                                        <td>${g.SoDienThoai || ''}</td>
                                        <td>${g.CMND || ''}</td>
                                    </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }
        } catch (e) {
            console.log('Lỗi parse danh sách khách:', e);
        }
        
        // Format tiền
        const formatMoney = (amount) => {
            if (amount === null || amount === undefined || isNaN(amount)) return '0 đ';
            return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
        };
        
        // Format ngày
        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;
                return date.toLocaleDateString('vi-VN');
            } catch (e) {
                return dateString;
            }
        };
        
        // Format ngày giờ
        const formatDateTime = (dateTimeString) => {
            if (!dateTimeString) return 'N/A';
            try {
                const date = new Date(dateTimeString);
                if (isNaN(date.getTime())) return dateTimeString;
                return date.toLocaleString('vi-VN');
            } catch (e) {
                return dateTimeString;
            }
        };
        
        // Tạo nội dung chi tiết
        document.getElementById('detailContent').innerHTML = `
            <div class="invoice-details">
                <div class="text-center mb-4">
                    <h5 class="text-primary">ABC RESORT</h5>
                    <h6>HÓA ĐƠN THANH TOÁN</h6>
                    <p class="text-muted">Mã hóa đơn: <strong>#${data.Id || ''}</strong></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-hotel me-2"></i>Thông tin đặt phòng</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Mã khách hàng:</strong></td>
                                <td>${data.MaKhachHang || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td><strong>Mã phòng:</strong></td>
                                <td><span class="badge bg-secondary">${data.MaPhong || 'N/A'}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Số đêm:</strong></td>
                                <td>${data.SoDem || 0} đêm</td>
                            </tr>
                            <tr>
                                <td><strong>Số người:</strong></td>
                                <td>${data.SoNguoi || 0} người</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-calendar-alt me-2"></i>Thời gian</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Ngày nhận:</strong></td>
                                <td>${formatDate(data.NgayNhan)}</td>
                            </tr>
                            <tr>
                                <td><strong>Ngày trả:</strong></td>
                                <td>${formatDate(data.NgayTra)}</td>
                            </tr>
                            <tr>
                                <td><strong>Ngày tạo:</strong></td>
                                <td>${formatDateTime(data.NgayTao)}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-concierge-bell me-2"></i>Yêu cầu đặc biệt</h6>
                        <div class="p-3 bg-light rounded">
                            ${data.YeuCauDacBiet || '<em class="text-muted">Không có yêu cầu đặc biệt</em>'}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-utensils me-2"></i>Dịch vụ sử dụng</h6>
                        <div class="p-3 bg-light rounded">
                            ${data.TenDichVu || '<em class="text-muted">Không sử dụng dịch vụ</em>'}
                        </div>
                    </div>
                </div>
                
                ${guestList}
                
                <div class="mt-3">
                    <h6><i class="fas fa-money-bill-wave me-2"></i>Chi tiết thanh toán</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Giá phòng:</strong></td>
                            <td class="text-end">${formatMoney(parseFloat(data.GiaPhong || 0))}</td>
                        </tr>
                        <tr>
                            <td><strong>Tiền dịch vụ:</strong></td>
                            <td class="text-end">${formatMoney(parseFloat(data.TienDichVu || 0))}</td>
                        </tr>
                        <tr>
                            <td><strong>Tiền khuyến mãi:</strong></td>
                            <td class="text-end text-danger">-${formatMoney(parseFloat(data.TienKhuyenMai || 0))}</td>
                        </tr>
                        <tr>
                            <td><strong>Tiền thuế:</strong></td>
                            <td class="text-end">${formatMoney(parseFloat(data.TienThue || 0))}</td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>TỔNG TIỀN:</strong></td>
                            <td class="text-end"><h5 class="mb-0">${formatMoney(parseFloat(data.TongTien || 0))}</h5></td>
                        </tr>
                    </table>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong>Phương thức thanh toán:</strong><br>
                        <span class="badge bg-primary">${data.PhuongThucThanhToan || 'N/A'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Trạng thái:</strong><br>
                        <span class="badge ${data.TrangThai == 'DaThanhToan' ? 'bg-success' : 'bg-warning'}">
                            ${data.TrangThai || 'N/A'}
                        </span></p>
                    </div>
                </div>
            </div>
        `;
        
    } catch (error) {
        console.error('Lỗi:', error);
        document.getElementById('detailContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Lỗi tải dữ liệu: ${error.message}
                <br><small>Vui lòng kiểm tra console để biết chi tiết</small>
            </div>
        `;
    }
}

// Hàm in hóa đơn
function printInvoice() {
    const printContent = document.querySelector('.invoice-details');
    if (!printContent) {
        alert('Không có nội dung để in!');
        return;
    }
    
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Hóa đơn</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                @media print {
                    body { padding: 20px; font-size: 14px; }
                    .no-print { display: none !important; }
                }
                .invoice-header {
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="invoice-header text-center">
                    <h3 class="text-primary">ABC RESORT</h3>
                    <h5>HÓA ĐƠN THANH TOÁN</h5>
                    <p class="text-muted">Ngày in: ${new Date().toLocaleString('vi-VN')}</p>
                </div>
                ${printContent.innerHTML}
                <div class="text-center mt-4 pt-3 border-top">
                    <p><strong>Cảm ơn quý khách đã sử dụng dịch vụ!</strong></p>
                    <p><small>Hóa đơn này có giá trị thanh toán và xuất hóa đơn</small></p>
                </div>
                <div class="text-center no-print mt-3">
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> In hóa đơn
                    </button>
                    <button class="btn btn-secondary" onclick="window.close()">Đóng</button>
                </div>
            </div>
            <script>
                window.onload = function() { window.print(); }
            <\/script>
        </body>
        </html>
    `;
    
    window.print();
    setTimeout(() => {
        document.body.innerHTML = originalContent;
    }, 100);
}

// Tự động ẩn alert sau 5 giây
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

// Thêm confirm cho nút chuyển trạng thái
document.addEventListener('submit', function(e) {
    if (e.target.tagName === 'FORM' && e.target.querySelector('input[name="trangthai"]')) {
        const form = e.target;
        const id = form.querySelector('input[name="id"]').value;
        const trangthai = form.querySelector('input[name="trangthai"]').value;
        const action = trangthai === 'DaThanhToan' ? 'xác nhận thanh toán' : 'hủy thanh toán';
        
        if (!confirm(`Bạn có chắc muốn ${action} hóa đơn #${id}?`)) {
            e.preventDefault();
        }
    }
});
</script>

<?php
// Include footer
include_once '../layouts/footer.php';
?>