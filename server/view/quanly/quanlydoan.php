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

// GỌI MODEL TRỰC TIẾP
include_once '../../model/quanlydoan.model.php';
$model = new QuanLyDoanModel();

// LẤY DANH SÁCH KHÁCH HÀNG ĐỂ CHỌN TRƯỞNG ĐOÀN
$dsKhachHang = $model->getDanhSachKhachHang();

// XỬ LÝ CÁC ACTION
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'xoa' && isset($_GET['ma_doan'])) {
        $maDoan = $_GET['ma_doan'];
        if ($model->xoaDoan($maDoan)) {
            $_SESSION['success'] = "Xóa đoàn thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa đoàn!";
        }
        header('Location: quanlydoan.php');
        exit();
    }
}
// XỬ LÝ SỬA ĐOÀN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sua') {
    $maDoan = $_POST['ma_doan'];
    $data = [
        'MaTruongDoan' => $_POST['ma_truong_doan'],
        'TenDoan' => $_POST['ten_doan'],
        'NgayDen' => $_POST['ngay_den'],
        'NgayDi' => $_POST['ngay_di'],
        'GhiChu' => $_POST['ghi_chu'] ?? ''
    ];

    if ($model->suaDoan($maDoan, $data)) {
        $_SESSION['success'] = "Cập nhật đoàn thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi cập nhật đoàn!";
    }
    header('Location: quanlydoan.php');
    exit();
}

// XỬ LÝ AJAX - LẤY THÔNG TIN ĐOÀN ĐỂ SỬA
if (isset($_GET['action']) && $_GET['action'] === 'get_doan_info' && isset($_GET['ma_doan'])) {
    $maDoan = $_GET['ma_doan'];
    $doan = $model->getChiTietDoan($maDoan);

    if ($doan) {
        echo json_encode([
            'success' => true,
            'data' => $doan
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy đoàn'
        ]);
    }
    exit();
}
// XỬ LÝ AJAX - LẤY DANH SÁCH KHÁCH HÀNG CHƯA TRONG ĐOÀN
if (isset($_GET['action']) && $_GET['action'] === 'get_kh_chua_trong_doan' && isset($_GET['ma_doan'])) {
    $maDoan = $_GET['ma_doan'];
    $dsKhachHangChua = $model->getKhachHangChuaTrongDoan($maDoan);

    foreach ($dsKhachHangChua as $kh) {
        echo '<option value="' . $kh['MaKH'] . '">' .
            htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']) .
            '</option>';
    }
    exit();
}

// XỬ LÝ AJAX - LẤY DANH SÁCH THÀNH VIÊN ĐOÀN
if (isset($_GET['action']) && $_GET['action'] === 'get_thanh_vien' && isset($_GET['ma_doan'])) {
    $maDoan = $_GET['ma_doan'];
    $dsThanhVien = $model->getThanhVienDoan($maDoan);

    if (empty($dsThanhVien)) {
        echo '<tr><td colspan="5" class="text-center text-muted">Chưa có thành viên</td></tr>';
    } else {
        $stt = 1;
        foreach ($dsThanhVien as $tv) {
            echo '<tr>';
            echo '<td>' . $stt++ . '</td>';
            echo '<td>';
            echo '<strong>' . htmlspecialchars($tv['HoTen']) . '</strong>';
            if (!empty($tv['Email'])) {
                echo '<br><small class="text-muted">' . htmlspecialchars($tv['Email']) . '</small>';
            }
            echo '</td>';
            echo '<td>' . htmlspecialchars($tv['SoDienThoai']) . '</td>';
            echo '<td>';
            if ($tv['VaiTro'] === 'TruongDoan') {
                echo '<span class="badge bg-warning">Trưởng đoàn</span>';
            } else {
                echo '<span class="badge bg-secondary">Thành viên</span>';
            }
            echo '</td>';
            echo '<td>';
            if ($tv['VaiTro'] !== 'TruongDoan') {
                echo '<button type="button" class="btn btn-danger btn-sm" ' .
                    'onclick="confirmXoaThanhVien(\'' . $tv['MaDoan'] . '\', \'' . $tv['MaKH'] . '\', \'' . htmlspecialchars($tv['HoTen']) . '\')">' .
                    '<i class="fas fa-trash"></i></button>';
            } else {
                echo '<span class="text-muted">Không thể xóa</span>';
            }
            echo '</td>';
            echo '</tr>';
        }
    }
    exit();
}
// XỬ LÝ THÊM THÀNH VIÊN VÀO ĐOÀN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'them_thanh_vien') {
    $maDoan = $_POST['ma_doan'];
    $maKH = $_POST['ma_kh'];

    if ($model->themThanhVienDoan($maDoan, $maKH)) {
        $_SESSION['success'] = "Thêm thành viên vào đoàn thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi thêm thành viên!";
    }
    header('Location: quanlydoan.php');
    exit();
}

// XỬ LÝ XÓA THÀNH VIÊN KHỎI ĐOÀN
if (isset($_GET['action']) && $_GET['action'] === 'xoa_thanh_vien' && isset($_GET['ma_doan']) && isset($_GET['ma_kh'])) {
    $maDoan = $_GET['ma_doan'];
    $maKH = $_GET['ma_kh'];

    if ($model->xoaThanhVienDoan($maDoan, $maKH)) {
        $_SESSION['success'] = "Xóa thành viên khỏi đoàn thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa thành viên!";
    }
    header('Location: quanlydoan.php');
    exit();
}
// XỬ LÝ THÊM ĐOÀN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'them') {
    $data = [
        'MaTruongDoan' => $_POST['ma_truong_doan'],
        'TenDoan' => $_POST['ten_doan'],
        'NgayDen' => $_POST['ngay_den'],
        'NgayDi' => $_POST['ngay_di'],
        'GhiChu' => $_POST['ghi_chu'] ?? ''
    ];

    $result = $model->themDoan($data);

    if ($result['success']) {
        $_SESSION['success'] = "Thêm đoàn thành công! Mã đoàn: " . $result['maDoan'];
    } else {
        $_SESSION['error'] = "Lỗi khi thêm đoàn!";
    }
    header('Location: quanlydoan.php');
    exit();
}

// Xử lý tìm kiếm
$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
    $danhSachDoan = $model->timKiemDoan($keyword);
} else {
    $danhSachDoan = $model->getDanhSachDoan();
}
?>

<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>Quản Lý Đoàn
        </h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#themDoanModal">
            <i class="fas fa-plus me-2"></i>Thêm Đoàn Mới
        </button>
    </div>

    <!-- Thống kê nhanh -->
    <?php
    $thongKe = $model->thongKeDoan();
    if ($thongKe): ?>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tổng số đoàn
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongDoan']; ?> đoàn
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Tổng số người
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongNguoi']; ?> người
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Trung bình người/đoàn
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['trungBinhNguoi']; ?> người
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="keyword"
                            placeholder="Tìm kiếm theo mã đoàn, tên đoàn, mã trưởng đoàn..."
                            value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Tìm Kiếm
                        </button>
                        <a href="quanlydoan.php" class="btn btn-secondary">
                            <i class="fas fa-refresh me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Danh sách đoàn -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Danh Sách Đoàn
                <span class="badge bg-light text-dark ms-2"><?php echo count($danhSachDoan); ?> đoàn</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="50"><input type="checkbox" id="selectAll"></th>
                            <th width="60">STT</th>
                            <th width="120">Mã Đoàn</th>
                            <th>Tên Đoàn</th>
                            <th width="150">Trưởng Đoàn</th>
                            <th width="120">Số Lượng</th>
                            <th width="120">Ngày Đến</th>
                            <th width="120">Ngày Đi</th>
                            <th width="150" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachDoan)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-3"></i><br>
                                    Không có dữ liệu đoàn
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $stt = 1; ?>
                            <?php foreach ($danhSachDoan as $doan): ?>
                                <tr>
                                    <td><input type="checkbox" name="ma_doan_list[]" value="<?php echo $doan['MaDoan']; ?>" class="row-checkbox"></td>
                                    <td><?php echo $stt++; ?></td>
                                    <td>
                                        <span class="badge bg-success fs-6"><?php echo htmlspecialchars($doan['MaDoan']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($doan['TenDoan']); ?></strong>
                                        <?php if (!empty($doan['GhiChu'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($doan['GhiChu']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($doan['MaTruongDoan']); ?></span>
                                        <br><small><?php echo htmlspecialchars($doan['TenTruongDoan']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6"><?php echo $doan['SoLuongThanhVien']; ?> người</span>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold"><?php echo date('d/m/Y', strtotime($doan['NgayDen'])); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold"><?php echo date('d/m/Y', strtotime($doan['NgayDi'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100">
                                            <button type="button" class="btn btn-info"
                                                onclick="showThanhVienModal('<?php echo $doan['MaDoan']; ?>', '<?php echo htmlspecialchars($doan['TenDoan']); ?>')"
                                                title="Quản lý thành viên">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning"
                                                onclick="showSuaDoanModal('<?php echo $doan['MaDoan']; ?>')"
                                                title="Sửa thông tin đoàn">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="quanlydoan.php?action=xoa&ma_doan=<?php echo $doan['MaDoan']; ?>"
                                                class="btn btn-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa đoàn <?php echo $doan['MaDoan']; ?>?')"
                                                title="Xóa đoàn">
                                                <i class="fas fa-trash"></i>
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


<!-- Modal Thêm Đoàn -->
<div class="modal fade" id="themDoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Thêm Đoàn Mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlydoan.php?action=them" id="formThemDoan">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Mã đoàn sẽ được tạo tự động</strong> (MD1, MD2, MD3,...)
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trưởng Đoàn <span class="text-danger">*</span></label>
                            <select class="form-control" name="ma_truong_doan" required>
                                <option value="">-- Chọn trưởng đoàn --</option>
                                <?php foreach ($dsKhachHang as $kh): ?>
                                    <option value="<?php echo $kh['MaKH']; ?>">
                                        <?php echo htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Đoàn <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_doan" required
                                placeholder="Nhập tên đoàn">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Đến <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_den" required
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Đi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_di" required
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Ghi Chú</label>
                            <textarea class="form-control" name="ghi_chu" rows="3"
                                placeholder="Nhập ghi chú (nếu có)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Đóng
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Thêm Đoàn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Sửa Đoàn -->
<div class="modal fade" id="suaDoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Sửa Thông Tin Đoàn
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlydoan.php?action=sua" id="formSuaDoan">
                <input type="hidden" name="ma_doan" id="sua_ma_doan">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Sửa thông tin đoàn <strong id="sua_ten_doan_display"></strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trưởng Đoàn <span class="text-danger">*</span></label>
                            <select class="form-control" name="ma_truong_doan" id="sua_ma_truong_doan" required>
                                <option value="">-- Chọn trưởng đoàn --</option>
                                <?php foreach ($dsKhachHang as $kh): ?>
                                    <option value="<?php echo $kh['MaKH']; ?>">
                                        <?php echo htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Đoàn <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_doan" id="sua_ten_doan" required
                                placeholder="Nhập tên đoàn">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Đến <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_den" id="sua_ngay_den" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Đi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_di" id="sua_ngay_di" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Ghi Chú</label>
                            <textarea class="form-control" name="ghi_chu" id="sua_ghi_chu" rows="3"
                                placeholder="Nhập ghi chú (nếu có)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Đóng
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Quản lý Thành viên -->
<div class="modal fade" id="thanhVienModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="thanhVienModalTitle">
                    <i class="fas fa-user-friends me-2"></i>Quản lý Thành viên
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="currentMaDoan">

                <!-- Form thêm thành viên mới -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Thêm Thành viên Mới
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="quanlydoan.php?action=them_thanh_vien" id="formThemThanhVien">
                            <input type="hidden" name="ma_doan" id="maDoanThemThanhVien">
                            <div class="row">
                                <div class="col-md-8">
                                    <select class="form-control" name="ma_kh" id="selectKhachHang" required>
                                        <option value="">-- Chọn khách hàng --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-user-plus me-1"></i>Thêm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách thành viên hiện tại -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>Danh sách Thành viên
                            <span class="badge bg-light text-dark ms-2" id="totalThanhVien">0 người</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">STT</th>
                                        <th>Họ tên</th>
                                        <th width="120">SĐT</th>
                                        <th width="100">Vai trò</th>
                                        <th width="80">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="tableThanhVien">
                                    <!-- Danh sách thành viên sẽ được load bằng JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Quản lý Thành viên -->
<div class="modal fade" id="thanhVienModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="thanhVienModalTitle">
                    <i class="fas fa-user-friends me-2"></i>Quản lý Thành viên
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="currentMaDoan">

                <!-- Form thêm thành viên mới -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Thêm Thành viên Mới
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="quanlydoan.php?action=them_thanh_vien" id="formThemThanhVien">
                            <input type="hidden" name="ma_doan" id="maDoanThemThanhVien">
                            <div class="row">
                                <div class="col-md-8">
                                    <select class="form-control" name="ma_kh" id="selectKhachHang" required>
                                        <option value="">-- Chọn khách hàng --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-user-plus me-1"></i>Thêm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách thành viên hiện tại -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>Danh sách Thành viên
                            <span class="badge bg-light text-dark ms-2" id="totalThanhVien">0 người</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">STT</th>
                                        <th>Họ tên</th>
                                        <th width="120">SĐT</th>
                                        <th width="100">Vai trò</th>
                                        <th width="80">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="tableThanhVien">
                                    <!-- Danh sách thành viên sẽ được load bằng JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript để validate form
    document.getElementById('formThemDoan').addEventListener('submit', function(e) {
        const ngayDen = document.querySelector('input[name="ngay_den"]').value;
        const ngayDi = document.querySelector('input[name="ngay_di"]').value;

        if (ngayDen && ngayDi) {
            if (new Date(ngayDi) <= new Date(ngayDen)) {
                alert('Ngày đi phải lớn hơn ngày đến!');
                e.preventDefault();
                return false;
            }
        }

        return true;
    });

    // Tự động set min date cho ngày đi khi chọn ngày đến
    document.querySelector('input[name="ngay_den"]').addEventListener('change', function() {
        const ngayDiInput = document.querySelector('input[name="ngay_di"]');
        if (this.value) {
            ngayDiInput.min = this.value;
        }
    });

    // Chọn tất cả
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    // Hiển thị modal quản lý thành viên
    function showThanhVienModal(maDoan, tenDoan) {
        document.getElementById('thanhVienModalTitle').innerHTML =
            '<i class="fas fa-user-friends me-2"></i>Quản lý Thành viên - ' + tenDoan;
        document.getElementById('currentMaDoan').value = maDoan;
        document.getElementById('maDoanThemThanhVien').value = maDoan;

        // Load danh sách khách hàng chưa trong đoàn
        loadKhachHangChuaTrongDoan(maDoan);

        // Load danh sách thành viên hiện tại
        loadThanhVienDoan(maDoan);

        // Hiển thị modal
        new bootstrap.Modal(document.getElementById('thanhVienModal')).show();
    }

    // Load danh sách khách hàng chưa trong đoàn
    function loadKhachHangChuaTrongDoan(maDoan) {
        fetch('quanlydoan.php?action=get_kh_chua_trong_doan&ma_doan=' + maDoan)
            .then(response => response.text())
            .then(data => {
                document.getElementById('selectKhachHang').innerHTML =
                    '<option value="">-- Chọn khách hàng --</option>' + data;
            });
    }

    // Load danh sách thành viên đoàn
    function loadThanhVienDoan(maDoan) {
        fetch('quanlydoan.php?action=get_thanh_vien&ma_doan=' + maDoan)
            .then(response => response.text())
            .then(data => {
                document.getElementById('tableThanhVien').innerHTML = data;
                // Cập nhật tổng số thành viên
                const totalRows = document.getElementById('tableThanhVien').querySelectorAll('tr').length;
                document.getElementById('totalThanhVien').textContent = totalRows + ' người';
            });
    }

    // Xác nhận xóa thành viên
    function confirmXoaThanhVien(maDoan, maKH, tenKH) {
        if (confirm('Bạn có chắc muốn xóa "' + tenKH + '" khỏi đoàn?')) {
            window.location.href = 'quanlydoan.php?action=xoa_thanh_vien&ma_doan=' + maDoan + '&ma_kh=' + maKH;
        }
    }
    // Hiển thị modal sửa đoàn
    function showSuaDoanModal(maDoan) {
        // Gọi API lấy thông tin đoàn
        fetch('quanlydoan.php?action=get_doan_info&ma_doan=' + maDoan)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const doan = data.data;

                    // Điền dữ liệu vào form
                    document.getElementById('sua_ma_doan').value = doan.MaDoan;
                    document.getElementById('sua_ten_doan_display').textContent = doan.TenDoan;
                    document.getElementById('sua_ma_truong_doan').value = doan.MaTruongDoan;
                    document.getElementById('sua_ten_doan').value = doan.TenDoan;
                    document.getElementById('sua_ngay_den').value = doan.NgayDen;
                    document.getElementById('sua_ngay_di').value = doan.NgayDi;
                    document.getElementById('sua_ghi_chu').value = doan.GhiChu || '';

                    // Hiển thị modal
                    new bootstrap.Modal(document.getElementById('suaDoanModal')).show();
                } else {
                    alert('Không thể tải thông tin đoàn!');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Lỗi khi tải thông tin đoàn!');
            });
    }

    // Validate form sửa đoàn
    document.getElementById('formSuaDoan').addEventListener('submit', function(e) {
        const ngayDen = document.getElementById('sua_ngay_den').value;
        const ngayDi = document.getElementById('sua_ngay_di').value;

        if (ngayDen && ngayDi) {
            if (new Date(ngayDi) <= new Date(ngayDen)) {
                alert('Ngày đi phải lớn hơn ngày đến!');
                e.preventDefault();
                return false;
            }
        }

        return true;
    });

    // Tự động set min date cho ngày đi khi chọn ngày đến (sửa đoàn)
    document.getElementById('sua_ngay_den').addEventListener('change', function() {
        const ngayDiInput = document.getElementById('sua_ngay_di');
        if (this.value) {
            ngayDiInput.min = this.value;
        }
    });
</script>

<?php include_once '../layouts/footer.php'; ?>