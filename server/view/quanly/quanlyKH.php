<?php
// KIỂM TRA SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền truy cập 
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') { 
    header('Location: ../login/login.php'); 
    exit(); 
}

// GỌI MODEL
include_once '../../model/quanlykh.model.php';
$model = new QuanLyKHModel();

// XỬ LÝ CRUD ACTION
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'xoa' && isset($_GET['ma_kh'])) {
        $maKH = $_GET['ma_kh'];
        if ($model->xoaKH($maKH)) {
            $_SESSION['success'] = "Xóa khách hàng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa khách hàng!";
        }
        header('Location: quanlykh.php');
        exit();
    }
}

// XỬ LÝ THÊM KHÁCH HÀNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'them') {
    $data = [
        'HoTen' => $_POST['ho_ten'],
        'CMND' => $_POST['cmnd'],
        'SoDienThoai' => $_POST['so_dien_thoai'],
        'Email' => $_POST['email'],
        'DiaChi' => $_POST['dia_chi'],
        'LoaiKH' => $_POST['loai_kh'],
        'TrangThai' => $_POST['trang_thai']
    ];

    // Kiểm tra CMND trùng
    if ($model->kiemTraCMND($data['CMND'])) {
        $_SESSION['error'] = "CMND đã tồn tại trong hệ thống!";
        header('Location: quanlykh.php');
        exit();
    }

    $result = $model->themKH($data);
    
    if ($result['success']) {
        $_SESSION['success'] = "Thêm khách hàng thành công! Mã KH: " . $result['maKH'];
    } else {
        $_SESSION['error'] = "Lỗi khi thêm khách hàng!";
    }
    header('Location: quanlykh.php');
    exit();
}

// Lấy tham số filter
$keyword = $_GET['keyword'] ?? '';
$loaiKH = $_GET['loaiKH'] ?? '';
$trangThai = $_GET['trangThai'] ?? '';

// Lấy dữ liệu
$danhSachKH = $model->getDanhSachKH($keyword, $loaiKH, $trangThai);
$thongKe = $model->thongKeKH();
?>

<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>Quản Lý Khách Hàng
        </h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#themKHModal">
            <i class="fas fa-plus me-2"></i>Thêm Khách Hàng
        </button>
    </div>

    <!-- Thống kê đơn giản -->
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng Khách Hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $thongKe['tongKH']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Khách VIP
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $thongKe['tongVIP']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Đang hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $thongKe['tongHoatDong']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ngừng hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $thongKe['tongNgungHoatDong']; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="" id="searchForm">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control" name="keyword" id="searchInput"
                               placeholder="Nhập mã, tên, CMND, SĐT để tìm kiếm..."
                               value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="loaiKH" id="loaiKHFilter">
                            <option value="">Tất cả loại KH</option>
                            <option value="VIP" <?php echo $loaiKH === 'VIP' ? 'selected' : ''; ?>>VIP</option>
                            <option value="Thuong" <?php echo $loaiKH === 'Thuong' ? 'selected' : ''; ?>>Thường</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="trangThai" id="trangThaiFilter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="HoatDong" <?php echo $trangThai === 'HoatDong' ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="NgungHoatDong" <?php echo $trangThai === 'NgungHoatDong' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Tìm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
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

    <!-- Danh sách khách hàng -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Danh Sách Khách Hàng
                <span class="badge bg-light text-dark ms-2"><?php echo count($danhSachKH); ?> KH</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">STT</th>
                            <th width="120">Mã KH</th>
                            <th>Họ tên</th>
                            <th width="150">CMND</th>
                            <th width="120">SĐT</th>
                            <th width="100">Loại KH</th>
                            <th width="120">Ngày đăng ký</th>
                            <th width="120">Trạng thái</th>
                            <th width="120" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachKH)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-3"></i><br>
                                    Không có dữ liệu khách hàng
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $stt = 1; ?>
                            <?php foreach ($danhSachKH as $kh): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td>
                                    <span class="badge bg-success fs-6"><?php echo htmlspecialchars($kh['MaKH']); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($kh['HoTen']); ?></strong>
                                    <?php if (!empty($kh['Email'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($kh['Email']); ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($kh['DiaChi'])): ?>
                                    <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($kh['DiaChi']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($kh['CMND']); ?></td>
                                <td>
                                    <i class="fas fa-phone text-primary me-1"></i>
                                    <?php echo htmlspecialchars($kh['SoDienThoai']); ?>
                                </td>
                                <td>
                                    <?php if ($kh['LoaiKH'] === 'VIP'): ?>
                                        <span class="badge bg-warning fs-6"><i class="fas fa-crown me-1"></i>VIP</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary fs-6">Thường</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y', strtotime($kh['created_at'])); ?></small>
                                    <br><small class="text-muted"><?php echo date('H:i', strtotime($kh['created_at'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($kh['TrangThai'] === 'HoatDong'): ?>
                                        <span class="badge bg-success"><i class="fas fa-play-circle me-1"></i>Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-stop-circle me-1"></i>Ngừng HĐ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm w-100">
                                        <a href="suakh.php?ma_kh=<?php echo $kh['MaKH']; ?>" 
                                           class="btn btn-warning" title="Sửa thông tin">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <a href="quanlykh.php?action=xoa&ma_kh=<?php echo $kh['MaKH']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa khách hàng <?php echo $kh['MaKH']; ?> - <?php echo $kh['HoTen']; ?>?')"
                                           title="Xóa khách hàng">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
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

<!-- Modal Thêm KH -->
<div class="modal fade" id="themKHModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Thêm Khách Hàng Mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlykh.php?action=them" id="formThemKH">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Mã KH sẽ được tạo tự động</strong> (KH1, KH2, KH3,...)
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ho_ten" required
                                   placeholder="Nhập họ tên đầy đủ">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CMND/CCCD <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="cmnd" required
                                   placeholder="Nhập số CMND/CCCD" maxlength="12">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="so_dien_thoai" required
                                   placeholder="Nhập số điện thoại">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   placeholder="Nhập email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại KH <span class="text-danger">*</span></label>
                            <select class="form-control" name="loai_kh" required>
                                <option value="Thuong">Thường</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-control" name="trang_thai" required>
                                <option value="HoatDong">Hoạt động</option>
                                <option value="NgungHoatDong">Ngừng hoạt động</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <textarea class="form-control" name="dia_chi" rows="2"
                                      placeholder="Nhập địa chỉ"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Đóng
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Thêm KH
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tự động tìm kiếm khi thay đổi filter
document.getElementById('loaiKHFilter').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});

document.getElementById('trangThaiFilter').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});

// Validate form thêm KH
document.getElementById('formThemKH').addEventListener('submit', function(e) {
    const cmnd = document.querySelector('input[name="cmnd"]').value;
    const sdt = document.querySelector('input[name="so_dien_thoai"]').value;
    
    // Validate CMND (9-12 số)
    if (!/^\d{9,12}$/.test(cmnd)) {
        alert('CMND/CCCD phải có 9-12 chữ số!');
        e.preventDefault();
        return false;
    }
    
    // Validate SĐT (10-11 số)
    if (!/^\d{10,11}$/.test(sdt)) {
        alert('Số điện thoại phải có 10-11 chữ số!');
        e.preventDefault();
        return false;
    }
    
    return true;
});

// Focus vào ô tìm kiếm khi load trang
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput').focus();
});
</script>

<?php include_once '../layouts/footer.php'; ?>