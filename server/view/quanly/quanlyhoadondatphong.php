<?php
// server/view/quanly/quanlyhoadondatphong.php

// ========== XỬ LÝ PHẦN ĐẦU - TRƯỚC KHI CÓ BẤT KỲ OUTPUT NÀO ==========
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}

require_once __DIR__ . '/../../model/quanlyhoadondatphong.model.php';
require_once __DIR__ . '/../../model/connectDB.php'; // Thêm để có thể kết nối database

// ========== XỬ LÝ XÓA HÓA ĐƠN TRỰC TIẾP ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'xoa') {
    $id = $_POST['id'] ?? 0;
    
    if ($id > 0) {
        $model = new QuanLyHoaDonDatPhongModel();
        
        // Kiểm tra hóa đơn tồn tại
        $hoadon = $model->getHoaDonById($id);
        if ($hoadon) {
            // Lấy thông tin hóa đơn
            $maKhachHang = $hoadon['MaKhachHang'];
            $maPhong = $hoadon['MaPhong'];
            
            // 1. Xóa hóa đơn
            $result = $model->deleteHoaDon($id);
            
            if ($result) {
                // 2. Update trạng thái phòng về "Trống"
                $db = new Connect();
                $conn = $db->openConnect();
                
                if ($conn) {
                    // Update phòng
                    $sqlPhong = "UPDATE phong SET TrangThai = 'Trống' WHERE MaPhong = ?";
                    $stmtPhong = $conn->prepare($sqlPhong);
                    $stmtPhong->bind_param("i", $maPhong);
                    $updatePhong = $stmtPhong->execute();
                    $stmtPhong->close();
                    
                    // Update khách hàng
                    $sqlKhachHang = "UPDATE KhachHang SET TrangThai = 'Không ở' WHERE MaKH = ?";
                    $stmtKhachHang = $conn->prepare($sqlKhachHang);
                    $stmtKhachHang->bind_param("s", $maKhachHang);
                    $updateKhachHang = $stmtKhachHang->execute();
                    $stmtKhachHang->close();
                    
                    $db->closeConnect($conn);
                    
                    $_SESSION['success'] = "Đã xóa hóa đơn #$id thành công!<br>";
                    $_SESSION['success'] .= "- Phòng $maPhong đã được cập nhật về 'Trống'<br>";
                    $_SESSION['success'] .= "- Khách hàng $maKhachHang đã được cập nhật về 'Không ở'";
                } else {
                    $_SESSION['error'] = "Xóa hóa đơn thành công nhưng không thể cập nhật trạng thái phòng!";
                }
            } else {
                $_SESSION['error'] = "Xóa hóa đơn thất bại!";
            }
        } else {
            $_SESSION['error'] = "Không tìm thấy hóa đơn #$id!";
        }
        
        // Redirect về trang hiện tại
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ========== XỬ LÝ LẤY CHI TIẾT HÓA ĐƠN (CHO AJAX) ==========
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'chiTiet') {
    $id = $_GET['id'] ?? 0;
    if ($id > 0) {
        $model = new QuanLyHoaDonDatPhongModel();
        $hoadon = $model->getHoaDonById($id);
        
        header('Content-Type: application/json');
        if ($hoadon) {
            echo json_encode(['success' => true, 'data' => $hoadon]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Không tìm thấy hóa đơn']);
        }
        exit();
    }
}

// ========== XỬ LÝ DỮ LIỆU CHO VIEW ==========
$model = new QuanLyHoaDonDatPhongModel();

// Xử lý lọc nếu có tham số
$tuNgayFilter = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : null;
$denNgayFilter = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : null;
$tuKhoaFilter = isset($_GET['tu_khoa']) ? $_GET['tu_khoa'] : null;

// Lấy dữ liệu theo bộ lọc
if (!empty($tuKhoaFilter)) {
    $hoadon = $model->searchHoaDon($tuKhoaFilter);
    $filterType = 'search';
    $filterValue = $tuKhoaFilter;
} elseif ($tuNgayFilter && $denNgayFilter) {
    $hoadon = $model->filterHoaDonByDate($tuNgayFilter, $denNgayFilter);
    $filterType = 'date';
    $filterValue = "$tuNgayFilter đến $denNgayFilter";
} else {
    $hoadon = $model->getAllHoaDon();
    $filterType = 'all';
}

// Lấy thống kê tổng quan
$tongDoanhThuData = $model->getTongDoanhThu();

// Tính thống kê nhanh
$tongHoaDon = count($hoadon);
$daThanhToan = 0;
$tongTien = 0;

foreach ($hoadon as $hd) {
    if ($hd['TrangThai'] == 'DaThanhToan') {
        $daThanhToan++;
        $tongTien += $hd['TongTien'];
    }
}
$chuaThanhToan = $tongHoaDon - $daThanhToan;
$trungBinh = $tongHoaDon > 0 ? $tongTien / $tongHoaDon : 0;

// Bây giờ mới include header
include_once '../layouts/header.php';

// ========== PHẦN HIỆN TẠI GIỮ NGUYÊN ==========
$model = new QuanLyHoaDonDatPhongModel();

// Xử lý lọc nếu có tham số
$tuNgayFilter = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : null;
$denNgayFilter = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : null;
$tuKhoaFilter = isset($_GET['tu_khoa']) ? $_GET['tu_khoa'] : null;

// Lấy dữ liệu theo bộ lọc
if (!empty($tuKhoaFilter)) {
    $hoadon = $model->searchHoaDon($tuKhoaFilter);
    $filterType = 'search';
    $filterValue = $tuKhoaFilter;
} elseif ($tuNgayFilter && $denNgayFilter) {
    $hoadon = $model->filterHoaDonByDate($tuNgayFilter, $denNgayFilter);
    $filterType = 'date';
    $filterValue = "$tuNgayFilter đến $denNgayFilter";
} else {
    $hoadon = $model->getAllHoaDon();
    $filterType = 'all';
}

// Lấy thống kê tổng quan
$tongDoanhThuData = $model->getTongDoanhThu();

// Tính thống kê nhanh
$tongHoaDon = count($hoadon);
$daThanhToan = 0;
$tongTien = 0;

foreach ($hoadon as $hd) {
    if ($hd['TrangThai'] == 'DaThanhToan') {
        $daThanhToan++;
        $tongTien += $hd['TongTien'];
    }
}
$chuaThanhToan = $tongHoaDon - $daThanhToan;
$trungBinh = $tongHoaDon > 0 ? $tongTien / $tongHoaDon : 0;
?>

<!-- CSS đơn giản -->
<style>
    .card {
        border: 1px solid #dee2e6;
        margin-bottom: 20px;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .badge {
        font-size: 0.85em;
        padding: 4px 8px;
    }

    .total-amount {
        font-weight: 600;
    }

    .filter-active {
        background-color: #e7f1ff !important;
        border-left: 3px solid #0d6efd !important;
    }

    .stat-number {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>

<div class="py-4">
    <!-- Tiêu đề -->
    <!-- Header -->
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center py-4">
            <div>
                <h1 class="h3 mb-1">Quản Lý Hóa Đơn</h1>
                <p class="text-muted">Xem/ xóa và in danh thu từ hóa đơn</p>
            </div>
        </div>

        <!-- Hiển thị thông báo -->
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

        <!-- Bộ lọc và tìm kiếm -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Bộ lọc</h6>
                <form method="GET" action="" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="tu_ngay" class="form-control"
                                value="<?php echo $tuNgayFilter ? htmlspecialchars($tuNgayFilter) : date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="den_ngay" class="form-control"
                                value="<?php echo $denNgayFilter ? htmlspecialchars($denNgayFilter) : date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" name="tu_khoa" class="form-control"
                                    placeholder="Mã KH, mã phòng, dịch vụ..."
                                    value="<?php echo $tuKhoaFilter ? htmlspecialchars($tuKhoaFilter) : ''; ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="resetFilter()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                        </div>
                    </div>

                    <!-- Hiển thị thông tin lọc đang áp dụng -->
                    <?php if ($filterType != 'all'): ?>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php if ($filterType == 'search'): ?>
                                    Đang tìm: "<?php echo htmlspecialchars($filterValue); ?>"
                                <?php elseif ($filterType == 'date'): ?>
                                    Từ <?php echo date('d/m/Y', strtotime($tuNgayFilter)); ?> đến <?php echo date('d/m/Y', strtotime($denNgayFilter)); ?>
                                <?php endif; ?>
                                <a href="?" class="text-danger ms-2"><i class="fas fa-times"></i></a>
                            </span>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Thống kê nhanh -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="stat-number text-primary"><?php echo $tongHoaDon; ?></div>
                        <div class="stat-label">Tổng hóa đơn</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="stat-number text-success"><?php echo $daThanhToan; ?></div>
                        <div class="stat-label">Đã thanh toán</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="stat-number text-warning"><?php echo $chuaThanhToan; ?></div>
                        <div class="stat-label">Chưa thanh toán</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="stat-number text-info"><?php echo number_format($tongTien, 0, ',', '.') . ' đ'; ?></div>
                        <div class="stat-label">Tổng doanh thu</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="stat-number text-secondary"><?php echo number_format($trungBinh, 0, ',', '.') . ' đ'; ?></div>
                        <div class="stat-label">Trung bình/hóa đơn</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê chi tiết -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê chi tiết</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Thống kê tổng quan</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Tổng doanh thu (đã thanh toán):</td>
                                <td class="text-end fw-bold">
                                    <?php echo isset($tongDoanhThuData['TongDoanhThu']) ? number_format($tongDoanhThuData['TongDoanhThu'], 0, ',', '.') . ' đ' : '0 đ'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Số hóa đơn (đã thanh toán):</td>
                                <td class="text-end"><?php echo isset($tongDoanhThuData['SoHoaDon']) ? $tongDoanhThuData['SoHoaDon'] : '0'; ?></td>
                            </tr>
                            <tr>
                                <td>Trung bình/hóa đơn:</td>
                                <td class="text-end">
                                    <?php echo isset($tongDoanhThuData['TrungBinh']) ? number_format($tongDoanhThuData['TrungBinh'], 0, ',', '.') . ' đ' : '0 đ'; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Thống kê theo phương thức thanh toán</h6>
                        <?php
                        $paymentStats = $model->getThongKeThanhToan();
                        if (!empty($paymentStats)):
                        ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Phương thức</th>
                                        <th class="text-end">Số lượng</th>
                                        <th class="text-end">Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentStats as $stat): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($stat['PhuongThucThanhToan']); ?></td>
                                            <td class="text-end"><?php echo $stat['SoLuong']; ?></td>
                                            <td class="text-end fw-bold"><?php echo number_format($stat['TongTien'], 0, ',', '.') . ' đ'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Không có dữ liệu</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng danh sách hóa đơn -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách Hóa đơn</h6>
                <span class="badge bg-light text-dark">
                    <?php echo count($hoadon); ?> hóa đơn
                </span>
            </div>
            <div class="card-body">
                <!-- Form xóa ẩn -->
                <form id="formXoaHoaDon" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="xoa">
                    <input type="hidden" name="id" id="idHoaDonXoa">
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã KH</th>
                                <th>Phòng</th>
                                <th>Ngày nhận/trả</th>
                                <th>Số đêm</th>
                                <th>Tổng tiền</th>
                                <th>Phương thức TT</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($hoadon) > 0): ?>
                                <?php foreach ($hoadon as $hd): ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($hd['Id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($hd['MaKhachHang']); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo htmlspecialchars($hd['MaPhong']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted d-block">Nhận:</small>
                                            <?php echo date('d/m/Y', strtotime($hd['NgayNhan'])); ?>
                                            <br>
                                            <small class="text-muted d-block">Trả:</small>
                                            <?php echo date('d/m/Y', strtotime($hd['NgayTra'])); ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">
                                                <?php echo htmlspecialchars($hd['SoDem']); ?> đêm
                                            </span>
                                        </td>
                                        <td class="total-amount">
                                            <?php echo number_format($hd['TongTien'], 0, ',', '.') . ' đ'; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo htmlspecialchars($hd['PhuongThucThanhToan']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = $hd['TrangThai'] == 'DaThanhToan' ? 'bg-success text-white' : 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($hd['TrangThai']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y', strtotime($hd['NgayTao'])); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($hd['NgayTao'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-sm btn-outline-primary" onclick="xemChiTiet(<?php echo $hd['Id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="xoaHoaDonTrucTiep(<?php echo $hd['Id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Không tìm thấy hóa đơn nào</p>
                                        <?php if ($filterType != 'all'): ?>
                                            <a href="?" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="fas fa-times me-1"></i>Xóa bộ lọc
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết -->
<div class="modal fade" id="modalChiTiet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice me-2"></i>Chi tiết Hóa đơn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalChiTietBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Đang tải dữ liệu...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="inHoaDon()">
                    <i class="fas fa-print me-1"></i>In hóa đơn
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    console.log('jQuery loaded:', typeof $ !== 'undefined');
});

// Function xóa hóa đơn trực tiếp (không dùng AJAX)
function xoaHoaDonTrucTiep(id) {
    if (confirm('Bạn có chắc chắn muốn xóa hóa đơn #' + id + '?\n\nSau khi xóa:\n- Phòng sẽ được cập nhật về "Trống"\n- Khách hàng sẽ được cập nhật về "Không ở"')) {
        // Submit form trực tiếp
        document.getElementById('idHoaDonXoa').value = id;
        document.getElementById('formXoaHoaDon').submit();
    }
}

// Function xem chi tiết bằng AJAX
function xemChiTiet(id) {
    console.log('Đang xem chi tiết hóa đơn ID:', id);

    // Hiển thị modal
    var modalElement = document.getElementById('modalChiTiet');
    if (!modalElement) {
        alert('Không tìm thấy modal!');
        return;
    }

    var modal = new bootstrap.Modal(modalElement);
    modal.show();

    // Tạo URL đúng - gọi đến chính trang hiện tại
    var url = window.location.pathname + '?action=chiTiet&id=' + id;
    console.log('Request URL:', url);

    // Load dữ liệu chi tiết
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        timeout: 10000,
        beforeSend: function() {
            $('#modalChiTietBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Đang tải dữ liệu...</p>
                </div>
            `);
        },
        success: function(response) {
            console.log('Response received:', response);

            if (response && response.success) {
                var hd = response.data;

                if (!hd) {
                    $('#modalChiTietBody').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Không tìm thấy dữ liệu hóa đơn
                        </div>
                    `);
                    return;
                }

                // Format danh sách khách nếu có
                var danhSachKhachHtml = '';
                if (hd.DanhSachKhach) {
                    try {
                        var danhSachKhach = JSON.parse(hd.DanhSachKhach);
                        if (Array.isArray(danhSachKhach) && danhSachKhach.length > 0) {
                            danhSachKhachHtml = `
                                <div class="mt-3">
                                    <h6>Danh sách khách</h6>
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Họ tên</th>
                                                <th>Số điện thoại</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                            danhSachKhach.forEach(function(khach) {
                                danhSachKhachHtml += `
                                    <tr>
                                        <td>${khach.HoTen || ''}</td>
                                        <td>${khach.SoDienThoai || ''}</td>
                                    </tr>`;
                            });
                            danhSachKhachHtml += `</tbody></table></div>`;
                        }
                    } catch (e) {
                        console.log('Error parsing DanhSachKhach:', e);
                    }
                }

                var html = `
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Thông tin hóa đơn #${hd.Id}</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Mã khách hàng:</strong></td>
                                    <td>${hd.MaKhachHang || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Mã phòng:</strong></td>
                                    <td>${hd.MaPhong || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Số người:</strong></td>
                                    <td>${hd.SoNguoi || '0'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày nhận:</strong></td>
                                    <td>${formatDate(hd.NgayNhan)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày trả:</strong></td>
                                    <td>${formatDate(hd.NgayTra)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Số đêm:</strong></td>
                                    <td>${hd.SoDem || '0'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Thanh toán</h6>
                                    <p><strong>Tổng tiền:</strong><br>
                                    <span class="text-success fw-bold">${formatCurrency(hd.TongTien)}</span></p>
                                    <p><strong>Phương thức:</strong><br>
                                    <span class="badge bg-light text-dark">${hd.PhuongThucThanhToan || 'N/A'}</span></p>
                                    <p><strong>Trạng thái:</strong><br>
                                    <span class="badge ${hd.TrangThai == 'DaThanhToan' ? 'bg-success text-white' : 'bg-warning text-dark'}">
                                        ${hd.TrangThai || 'N/A'}
                                    </span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Chi tiết dịch vụ</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Dịch vụ</th>
                                    <th>Giá phòng</th>
                                    <th>Tiền dịch vụ</th>
                                    <th>Khuyến mãi</th>
                                    <th>Thuế</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>${hd.TenDichVu || 'Không có'}</td>
                                    <td class="text-end">${formatCurrency(hd.GiaPhong)}</td>
                                    <td class="text-end">${formatCurrency(hd.TienDichVu)}</td>
                                    <td class="text-end">${formatCurrency(hd.TienKhuyenMai)}</td>
                                    <td class="text-end">${formatCurrency(hd.TienThue)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    ${danhSachKhachHtml}
                    
                    <div class="mt-3">
                        <p><strong>Yêu cầu đặc biệt:</strong> ${hd.YeuCauDacBiet || 'Không có'}</p>
                        <p><strong>Ngày tạo:</strong> ${formatDateTime(hd.NgayTao)}</p>
                    </div>
                `;

                $('#modalChiTietBody').html(html);
            } else {
                var errorMsg = response && response.error ? response.error : 'Có lỗi xảy ra!';
                $('#modalChiTietBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${errorMsg}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', status, error);
            var errorMsg = 'Lỗi tải dữ liệu!';
            $('#modalChiTietBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${errorMsg}
                </div>
            `);
        }
    });
}

function resetFilter() {
    window.location.href = '?';
}

function inHoaDon() {
    // Lấy nội dung modal để in
    var modalBody = document.getElementById('modalChiTietBody');
    if (!modalBody) {
        alert('Không có dữ liệu hóa đơn để in!');
        return;
    }
    
    var printContent = modalBody.innerHTML;
    
    // Tạo cửa sổ in
    var printWindow = window.open('', '_blank', 'width=800,height=600');
    
    // Tạo HTML cho trang in
    var html = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Hóa đơn Resort</title>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .invoice-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 20px; }
                .invoice-header h1 { margin: 0; color: #2c3e50; }
                .invoice-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .invoice-table th, .invoice-table td { border: 1px solid #ddd; padding: 8px; }
                .invoice-table th { background-color: #f2f2f2; }
                .total-amount { font-size: 18px; color: #e74c3c; font-weight: bold; }
                .footer { margin-top: 50px; text-align: center; color: #7f8c8d; }
                @media print { 
                    body { margin: 0; padding: 20px; }
                    .no-print { display: none !important; }
                }
                .btn-print { 
                    background: #3498db; 
                    color: white; 
                    padding: 10px 20px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 10px; 
                    border: none;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <div class="invoice-header">
                <h1>ABC RESORT</h1>
                <p>Địa chỉ: 123 Đường Biển, Nha Trang, Khánh Hòa</p>
                <p>Điện thoại: 0258 123 456 - Email: info@abcresort.com</p>
            </div>
            
            <h2 style="text-align: center; color: #2c3e50;">HÓA ĐƠN THANH TOÁN</h2>
            <p style="text-align: center; color: #7f8c8d;">Ngày in: ${new Date().toLocaleDateString('vi-VN')}</p>
            
            <div id="invoice-content">
                ${printContent}
            </div>
            
            <div class="footer">
                <p>Cảm ơn quý khách đã sử dụng dịch vụ!</p>
                <p>Hóa đơn điện tử có giá trị như hóa đơn GTGT</p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 30px;">
                <button class="btn-print" onclick="window.print()">In hóa đơn</button>
                <button class="btn-print" onclick="window.close()" style="background: #95a5a6;">Đóng</button>
            </div>
        </body>
        </html>
    `;
    
    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
    
    // Đóng modal
    var modal = bootstrap.Modal.getInstance(document.getElementById('modalChiTiet'));
    if (modal) {
        modal.hide();
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        var date = new Date(dateString);
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleDateString('vi-VN');
    } catch (e) {
        return 'N/A';
    }
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    try {
        var date = new Date(dateTimeString);
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleString('vi-VN');
    } catch (e) {
        return 'N/A';
    }
}

function formatCurrency(amount) {
    if (!amount || isNaN(amount)) return '0 đ';
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount).replace('₫', 'đ');
}
</script>

<?php
include_once '../layouts/footer.php';
?>