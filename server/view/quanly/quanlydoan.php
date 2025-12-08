<?php
// ============================================
// PHẦN 1: KIỂM TRA VÀ KHỞI TẠO
// ============================================
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}

// Gọi Model
include_once '../../model/quanlydoan.model.php';
$model = new QuanLyDoanModel();

// ============================================
// PHẦN 2: XỬ LÝ CÁC ACTION
// ============================================

// 1. Xử lý các action GET
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        // Xóa đoàn
        case 'xoa':
            if (isset($_GET['ma_doan'])) {
                $maDoan = $_GET['ma_doan'];
                if ($model->xoaDoan($maDoan)) {
                    $_SESSION['success'] = "Xóa đoàn thành công!";
                } else {
                    $_SESSION['error'] = "Lỗi khi xóa đoàn!";
                }
                header('Location: quanlydoan.php');
                exit();
            }
            break;

        // Xóa thành viên khỏi đoàn
        case 'xoa_thanh_vien':
            if (isset($_GET['ma_doan']) && isset($_GET['ma_kh'])) {
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
            break;

        // AJAX: Lấy thông tin đoàn để sửa
        case 'get_doan_info':
            if (isset($_GET['ma_doan'])) {
                $maDoan = $_GET['ma_doan'];
                $doan = $model->getChiTietDoan($maDoan);

                echo json_encode([
                    'success' => !empty($doan),
                    'data' => $doan ?: null
                ]);
                exit();
            }
            break;

        // AJAX: Lấy danh sách khách hàng CHO MODAL SỬA (tất cả)
        case 'get_ds_khachhang':
            $dsKhachHang = $model->getDanhSachKhachHang(); // TẤT CẢ KH

            if (empty($dsKhachHang)) {
                echo '<option value="">-- Không có khách hàng --</option>';
            } else {
                foreach ($dsKhachHang as $kh) {
                    echo '<option value="' . $kh['MaKH'] . '">' .
                        htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']) .
                        '</option>';
                }
            }
            exit();
            break;
        // AJAX: Lấy danh sách thành viên của đoàn CHO MODAL SỬA
        case 'get_ds_thanh_vien_doan':
            if (isset($_GET['ma_doan'])) {
                $maDoan = $_GET['ma_doan'];
                $dsThanhVien = $model->getThanhVienDoanChoDropdown($maDoan); // CHỈ THÀNH VIÊN TRONG ĐOÀN

                if (empty($dsThanhVien)) {
                    echo '<option value="">-- Đoàn chưa có thành viên --</option>';
                } else {
                    foreach ($dsThanhVien as $tv) {
                        $selected = ($tv['VaiTro'] === 'TruongDoan') ? 'selected' : '';
                        $vaiTroText = ($tv['VaiTro'] === 'TruongDoan') ? ' (Trưởng đoàn)' : ' (Thành viên)';

                        echo '<option value="' . $tv['MaKH'] . '" ' . $selected . '>' .
                            htmlspecialchars($tv['MaKH'] . ' - ' . $tv['HoTen'] . $vaiTroText) .
                            '</option>';
                    }
                }
                exit();
            }
            break;

        // AJAX: Lấy khách hàng chưa trong đoàn CHO MODAL THÊM THÀNH VIÊN
        case 'get_kh_chua_trong_doan':
            $dsKhachHangChua = $model->getKhachHangChuaTrongDoan(); // CHƯA CÓ ĐOÀN

            if (empty($dsKhachHangChua)) {
                echo '<option value="">-- Không có khách hàng nào trống --</option>';
            } else {
                foreach ($dsKhachHangChua as $kh) {
                    echo '<option value="' . $kh['MaKH'] . '">' .
                        htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']) .
                        '</option>';
                }
            }
            exit();
            break;
            if (isset($_GET['ma_doan'])) {
                $maDoan = $_GET['ma_doan'];
                $dsKhachHangChua = $model->getKhachHangChuaTrongDoan($maDoan);

                foreach ($dsKhachHangChua as $kh) {
                    echo '<option value="' . $kh['MaKH'] . '">' .
                        htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']) .
                        '</option>';
                }
                exit();
            }
            break;

        // AJAX: Lấy danh sách thành viên đoàn
        case 'get_thanh_vien':
            if (isset($_GET['ma_doan'])) {
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
                        echo $tv['VaiTro'] === 'TruongDoan'
                            ? '<span class="badge bg-warning">Trưởng đoàn</span>'
                            : '<span class="badge bg-secondary">Thành viên</span>';
                        echo '</td>';
                        echo '<td>';
                        if ($tv['VaiTro'] !== 'TruongDoan') {
                            echo '<button type="button" class="btn btn-danger btn-sm" onclick="confirmXoaThanhVien(\'' .
                                $tv['MaDoan'] . '\', \'' . $tv['MaKH'] . '\', \'' . htmlspecialchars($tv['HoTen']) . '\')">
                                <i class="fas fa-trash"></i></button>';
                        } else {
                            echo '<span class="text-muted">Không thể xóa</span>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                exit();
            }
            break;
    }
}

// 2. Xử lý các action POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        // Thêm đoàn
        case 'them':
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
            break;

        // Sửa đoàn
        case 'sua':
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
            break;

        // Thêm thành viên vào đoàn
        case 'them_thanh_vien':
            $maDoan = $_POST['ma_doan'];
            $maKH = $_POST['ma_kh'];

            if ($model->themThanhVienDoan($maDoan, $maKH)) {
                $_SESSION['success'] = "Thêm thành viên vào đoàn thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi thêm thành viên!";
            }
            header('Location: quanlydoan.php');
            exit();
            break;
    }
}

// 3. XỬ LÝ AJAX - LẤY DANH SÁCH KHÁCH HÀNG CHƯA TRONG ĐOÀN
if (isset($_GET['action']) && $_GET['action'] === 'get_kh_chua_trong_doan') {
    // KHÔNG CẦN isset($_GET['ma_doan']) nữa
    $dsKhachHangChua = $model->getKhachHangChuaTrongDoan();

    if (empty($dsKhachHangChua)) {
        echo '<option value="">-- Không có khách hàng nào trống --</option>';
    } else {
        foreach ($dsKhachHangChua as $kh) {
            echo '<option value="' . $kh['MaKH'] . '">' .
                htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']) .
                '</option>';
        }
    }
    exit();
}
// ============================================
// PHẦN 3: LẤY DỮ LIỆU CHO VIEW
// ============================================
// Lấy danh sách khách hàng CHO MODAL THÊM ĐOÀN (chưa có đoàn)
$dsKhachHangThem = $model->getKhachHangChuaCoDoan();

// Lấy danh sách đoàn (có tìm kiếm)
$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
    $danhSachDoan = $model->timKiemDoan($keyword);
} else {
    $danhSachDoan = $model->getDanhSachDoan();
}

// Lấy thống kê
$thongKe = $model->thongKeDoan();

// ============================================
// PHẦN 4: HIỂN THỊ VIEW
// ============================================
include_once '../layouts/header.php';
?>

<!-- DEBUG BUTTON (có thể xóa khi deploy) -->
<div class="mt-3 mb-3">
    <button class="btn btn-warning btn-sm" onclick="testEverything()">
        <i class="fas fa-bug me-1"></i>Debug Test
    </button>
    <button class="btn btn-info btn-sm" onclick="testModalThem()">
        <i class="fas fa-plus me-1"></i>Test Modal Thêm
    </button>
</div>

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
    <?php if ($thongKe): ?>
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
                                    <?= $thongKe['tongDoan'] ?> đoàn
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
                                    <?= $thongKe['tongNguoi'] ?> người
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
                                    <?= $thongKe['trungBinhNguoi'] ?> người
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
                            value="<?= htmlspecialchars($keyword) ?>">
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
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Danh sách đoàn -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Danh Sách Đoàn
                <span class="badge bg-light text-dark ms-2"><?= count($danhSachDoan) ?> đoàn</span>
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
                                    <td><input type="checkbox" name="ma_doan_list[]" value="<?= $doan['MaDoan'] ?>" class="row-checkbox"></td>
                                    <td><?= $stt++ ?></td>
                                    <td><span class="badge bg-success fs-6"><?= htmlspecialchars($doan['MaDoan']) ?></span></td>
                                    <td>
                                        <strong><?= htmlspecialchars($doan['TenDoan']) ?></strong>
                                        <?php if (!empty($doan['GhiChu'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($doan['GhiChu']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($doan['MaTruongDoan']) ?></span>
                                        <br><small><?= htmlspecialchars($doan['TenTruongDoan']) ?></small>
                                    </td>
                                    <td><span class="badge bg-primary fs-6"><?= $doan['SoLuongThanhVien'] ?> người</span></td>
                                    <td><span class="text-success fw-bold"><?= date('d/m/Y', strtotime($doan['NgayDen'])) ?></span></td>
                                    <td><span class="text-danger fw-bold"><?= date('d/m/Y', strtotime($doan['NgayDi'])) ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100">
                                            <button type="button" class="btn btn-info"
                                                onclick="showThanhVienModal('<?= $doan['MaDoan'] ?>', '<?= htmlspecialchars($doan['TenDoan']) ?>')"
                                                title="Quản lý thành viên">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning"
                                                onclick="showSuaDoanModal('<?= $doan['MaDoan'] ?>')"
                                                title="Sửa thông tin đoàn">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="quanlydoan.php?action=xoa&ma_doan=<?= $doan['MaDoan'] ?>"
                                                class="btn btn-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa đoàn <?= $doan['MaDoan'] ?>?')"
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

<!-- ============================================
PHẦN 5: CÁC MODAL
============================================ -->

<!-- Modal Thêm Đoàn -->
<div class="modal fade" id="themDoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Thêm Đoàn Mới</h5>
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
                                <?php if (empty($dsKhachHangThem)): ?>
                                    <option value="" disabled>-- Không có khách hàng trống --</option>
                                <?php else: ?>
                                    <?php foreach ($dsKhachHangThem as $kh): ?>
                                        <option value="<?= $kh['MaKH'] ?>">
                                            <?= htmlspecialchars($kh['MaKH'] . ' - ' . $kh['HoTen'] . ' - ' . $kh['SoDienThoai']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Đoàn <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_doan" required placeholder="Nhập tên đoàn">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Đến <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_den" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Đi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="ngay_di" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Ghi Chú</label>
                            <textarea class="form-control" name="ghi_chu" rows="3" placeholder="Nhập ghi chú (nếu có)"></textarea>
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
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa Thông Tin Đoàn</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlydoan.php?action=sua" id="formSuaDoan">
                <input type="hidden" name="ma_doan" id="sua_ma_doan">
                <div class="modal-body">
                    <div class="alert alert-info" id="sua_alert_info">
                        <i class="fas fa-info-circle me-2"></i>Sửa thông tin đoàn
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trưởng Đoàn <span class="text-danger">*</span></label>
                            <select class="form-control" name="ma_truong_doan" id="sua_ma_truong_doan" required>
                                <option value="">-- Đang tải danh sách... --</option>
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Khi đổi trưởng đoàn, trưởng đoàn cũ sẽ trở thành thành viên thường
                            </small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Đoàn <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_doan" id="sua_ten_doan" required>
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
                            <textarea class="form-control" name="ghi_chu" id="sua_ghi_chu" rows="3"></textarea>
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
                        <h6 class="mb-0"><i class="fas fa-plus me-2"></i>Thêm Thành viên Mới</h6>
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
                                    <!-- Load bằng JS -->
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

<!-- ============================================
PHẦN 6: JAVASCRIPT
============================================ -->
<script>
    // ==================== UTILITY FUNCTIONS ====================
    // Load danh sách khách hàng chưa trong đoàn
    function loadKhachHangChuaTrongDoan(maDoan) {
        fetch('quanlydoan.php?action=get_kh_chua_trong_doan') // KHÔNG CẦN ma_doan nữa
            .then(response => response.text())
            .then(data => {
                document.getElementById('selectKhachHang').innerHTML =
                    '<option value="">-- Chọn khách hàng --</option>' + data;
            });
    }

    function loadThanhVienDoan(maDoan) {
        fetch('quanlydoan.php?action=get_thanh_vien&ma_doan=' + maDoan)
            .then(response => response.text())
            .then(data => {
                document.getElementById('tableThanhVien').innerHTML = data;
                const totalRows = document.getElementById('tableThanhVien').querySelectorAll('tr').length;
                document.getElementById('totalThanhVien').textContent = totalRows + ' người';
            });
    }

    // ==================== MAIN FUNCTIONS ====================
    function showThanhVienModal(maDoan, tenDoan) {
        document.getElementById('thanhVienModalTitle').innerHTML =
            '<i class="fas fa-user-friends me-2"></i>Quản lý Thành viên - ' + tenDoan;
        document.getElementById('currentMaDoan').value = maDoan;
        document.getElementById('maDoanThemThanhVien').value = maDoan;

        loadKhachHangChuaTrongDoan(maDoan);
        loadThanhVienDoan(maDoan);

        new bootstrap.Modal(document.getElementById('thanhVienModal')).show();
    }

    function showSuaDoanModal(maDoan) {
        const selectElement = document.getElementById('sua_ma_truong_doan');
        selectElement.innerHTML = '<option value="">-- Đang tải danh sách thành viên... --</option>';

        // Lấy thông tin đoàn
        fetch(`quanlydoan.php?action=get_doan_info&ma_doan=${maDoan}`)
            .then(response => response.json())
            .then(dataInfo => {
                if (!dataInfo.success || !dataInfo.data) {
                    alert('Không tìm thấy thông tin đoàn!');
                    return;
                }

                const doan = dataInfo.data;

                // Điền thông tin cơ bản
                document.getElementById('sua_ma_doan').value = maDoan;
                document.getElementById('sua_ten_doan').value = doan.TenDoan || '';
                document.getElementById('sua_ngay_den').value = doan.NgayDen || '';
                document.getElementById('sua_ngay_di').value = doan.NgayDi || '';
                document.getElementById('sua_ghi_chu').value = doan.GhiChu || '';

                // Lấy danh sách THÀNH VIÊN CỦA ĐOÀN (chỉ người trong đoàn này)
                return fetch(`quanlydoan.php?action=get_ds_thanh_vien_doan&ma_doan=${maDoan}`)
                    .then(response => response.text())
                    .then(dsThanhVienHTML => {
                        if (dsThanhVienHTML.trim()) {
                            selectElement.innerHTML = dsThanhVienHTML;

                            // Tự động chọn trưởng đoàn hiện tại
                            setTimeout(() => {
                                if (doan.MaTruongDoan) {
                                    selectElement.value = doan.MaTruongDoan;
                                    selectElement.dataset.oldValue = doan.MaTruongDoan;
                                }
                            }, 100);
                        } else {
                            selectElement.innerHTML = '<option value="">-- Đoàn chưa có thành viên --</option>';
                        }

                        // HIỂN THỊ THÔNG TIN RÕ HƠN
                        document.getElementById('sua_alert_info').innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Sửa đoàn:</strong> ${doan.TenDoan || maDoan}
                            </div>
                            <div class="text-muted">
                                <small>Trưởng đoàn hiện tại: <span class="badge bg-info">${doan.MaTruongDoan} - ${doan.TenTruongDoan}</span></small>
                            </div>
                        </div>
                        <div class="mt-2 alert alert-light">
                            <small><i class="fas fa-lightbulb text-warning me-1"></i> 
                            <strong>Chú ý:</strong> Chỉ có thể chọn trưởng đoàn mới từ <strong>thành viên hiện có trong đoàn</strong>.</small>
                        </div>
                    `;

                        new bootstrap.Modal(document.getElementById('suaDoanModal')).show();
                    });
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Lỗi khi tải dữ liệu!');
            });
    }
    // Load danh sách khách hàng chưa trong đoàn
    function loadKhachHangChuaTrongDoan() {
        fetch('quanlydoan.php?action=get_kh_chua_trong_doan')
            .then(response => response.text())
            .then(data => {
                const selectElement = document.getElementById('selectKhachHang');
                selectElement.innerHTML = '<option value="">-- Chọn khách hàng --</option>' + data;

                // Thêm cảnh báo nếu không có khách hàng trống
                if (data.includes('-- Không có khách hàng nào trống --')) {
                    const warningDiv = document.createElement('div');
                    warningDiv.className = 'alert alert-warning mt-2';
                    warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Không có khách hàng nào trống để thêm!';
                    selectElement.parentNode.appendChild(warningDiv);
                }
            });
    }

    function confirmXoaThanhVien(maDoan, maKH, tenKH) {
        if (confirm('Bạn có chắc muốn xóa "' + tenKH + '" khỏi đoàn?')) {
            window.location.href = `quanlydoan.php?action=xoa_thanh_vien&ma_doan=${maDoan}&ma_kh=${maKH}`;
        }
    }

    // ==================== EVENT LISTENERS ====================
    // Validate form thêm đoàn
    document.getElementById('formThemDoan').addEventListener('submit', function(e) {
        const ngayDen = document.querySelector('input[name="ngay_den"]').value;
        const ngayDi = document.querySelector('input[name="ngay_di"]').value;

        if (ngayDen && ngayDi && new Date(ngayDi) <= new Date(ngayDen)) {
            alert('Ngày đi phải lớn hơn ngày đến!');
            e.preventDefault();
            return false;
        }
        return true;
    });

    // Validate form sửa đoàn
    document.getElementById('formSuaDoan').addEventListener('submit', function(e) {
        const oldTruongDoan = document.querySelector('#sua_ma_truong_doan').dataset.oldValue;
        const newTruongDoan = document.getElementById('sua_ma_truong_doan').value;
        const ngayDen = document.getElementById('sua_ngay_den').value;
        const ngayDi = document.getElementById('sua_ngay_di').value;

        // Validate ngày
        if (ngayDen && ngayDi && new Date(ngayDi) <= new Date(ngayDen)) {
            alert('Ngày đi phải lớn hơn ngày đến!');
            e.preventDefault();
            return false;
        }

        // Confirm đổi trưởng đoàn
        if (oldTruongDoan && oldTruongDoan !== newTruongDoan) {
            if (!confirm('⚠️ BẠN SẮP ĐỔI TRƯỞNG ĐOÀN!\n\n' +
                    'Trưởng đoàn cũ sẽ trở thành thành viên thường.\n' +
                    'Bạn có chắc chắn muốn tiếp tục?')) {
                e.preventDefault();
                return false;
            }
        }
        return true;
    });

    // Tự động set min date cho ngày đi
    document.querySelector('input[name="ngay_den"]').addEventListener('change', function() {
        const ngayDiInput = document.querySelector('input[name="ngay_di"]');
        if (this.value) ngayDiInput.min = this.value;
    });

    document.getElementById('sua_ngay_den').addEventListener('change', function() {
        const ngayDiInput = document.getElementById('sua_ngay_di');
        if (this.value) ngayDiInput.min = this.value;
    });

    // Chọn tất cả checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // ==================== DEBUG FUNCTIONS (có thể xóa) ====================
    function testEverything() {
        console.log('=== TEST EVERYTHING ===');
        const modalThem = new bootstrap.Modal(document.getElementById('themDoanModal'));
        modalThem.show();
    }

    function testModalThem() {
        new bootstrap.Modal(document.getElementById('themDoanModal')).show();
    }

    // ==================== INITIALIZATION ====================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ Quản lý đoàn đã sẵn sàng');
    });
</script>

<?php include_once '../layouts/footer.php'; ?>