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
include_once '../../model/quanlyphong.model.php';
$model = new QuanLyPhongModel();

// LẤY DANH SÁCH LOẠI PHÒNG
$dsLoaiPhong = $model->getDanhSachLoaiPhong();

// XỬ LÝ CÁC ACTION
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'xoa' && isset($_GET['ma_phong'])) {
        $maPhong = $_GET['ma_phong'];
        if ($model->xoaPhong($maPhong)) {
            $_SESSION['success'] = "Xóa phòng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa phòng!";
        }
        header('Location: quanlyphong.php');
        exit();
    }
}

// XỬ LÝ THÊM THIẾT BỊ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'them_thiet_bi') {
    $data = [
        'TenThietBi' => $_POST['ten_thiet_bi'],
        'TinhTrang' => $_POST['tinh_trang'],
        'MaPhong' => $_POST['ma_phong']
    ];

    if ($model->themThietBi($data)) {
        $_SESSION['success'] = "Thêm thiết bị thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi thêm thiết bị!";
    }
    header('Location: quanlyphong.php');
    exit();
}

// XỬ LÝ XÓA THIẾT BỊ
if (isset($_GET['action']) && $_GET['action'] === 'xoa_thiet_bi' && isset($_GET['ma_thiet_bi'])) {
    $maThietBi = $_GET['ma_thiet_bi'];
    if ($model->xoaThietBi($maThietBi)) {
        $_SESSION['success'] = "Xóa thiết bị thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa thiết bị!";
    }
    header('Location: quanlyphong.php');
    exit();
}

// XỬ LÝ THÊM PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'them') {
    $data = [
        'Tang' => $_POST['tang'],
        'MaLoaiPhong' => $_POST['ma_loai_phong'],
        'TrangThai' => $_POST['trang_thai'],
        'roomName' => $_POST['room_name']
    ];

    // Lấy file upload
    $avatarFile = isset($_FILES['avatar']) ? $_FILES['avatar'] : null;
    $imageFiles = isset($_FILES['danh_sach_anh']) ? $_FILES['danh_sach_anh'] : null;

    // GỌI PHƯƠNG THỨC MỚI
    $result = $model->themPhongMoi($data, $avatarFile, $imageFiles);

    if ($result['success']) {
        $_SESSION['success'] = "Thêm phòng thành công! Mã phòng: " . $result['soPhong'];
    } else {
        $_SESSION['error'] = "Lỗi khi thêm phòng!";
    }
    header('Location: quanlyphong.php');
    exit();
}
// XỬ LÝ SỬA PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sua') {
    $maPhong = $_POST['ma_phong'];
    $data = [
        'SoPhong' => $_POST['so_phong'],
        'Tang' => $_POST['tang'],
        'MaLoaiPhong' => $_POST['ma_loai_phong'],
        'TrangThai' => $_POST['trang_thai'],
        'Avatar' => $_POST['avatar'] ?? '',
        'DanhSachPhong' => $_POST['danh_sach_phong'] ?? '',
        'roomName' => $_POST['room_name']
    ];

    if ($model->suaPhong($maPhong, $data)) {
        $_SESSION['success'] = "Cập nhật phòng thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi cập nhật phòng!";
    }
    header('Location: quanlyphong.php');
    exit();
}

// XỬ LÝ AJAX - LẤY THÔNG TIN PHÒNG ĐỂ SỬA
if (isset($_GET['action']) && $_GET['action'] === 'get_phong_info' && isset($_GET['ma_phong'])) {
    $maPhong = $_GET['ma_phong'];
    $phong = $model->getChiTietPhong($maPhong);

    if ($phong) {
        echo json_encode([
            'success' => true,
            'data' => $phong
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy phòng'
        ]);
    }
    exit();
}

// XỬ LÝ AJAX - LẤY THIẾT BỊ PHÒNG
if (isset($_GET['action']) && $_GET['action'] === 'get_thiet_bi' && isset($_GET['ma_phong'])) {
    $maPhong = $_GET['ma_phong'];
    $dsThietBi = $model->getThietBiPhong($maPhong);

    if (empty($dsThietBi)) {
        echo '<tr><td colspan="4" class="text-center text-muted">Chưa có thiết bị</td></tr>';
    } else {
        $stt = 1;
        foreach ($dsThietBi as $tb) {
            echo '<tr>';
            echo '<td>' . $stt++ . '</td>';
            echo '<td>' . htmlspecialchars($tb['TenThietBi']) . '</td>';
            echo '<td>';
            if ($tb['TinhTrang'] === 'Tốt') {
                echo '<span class="badge bg-success">Tốt</span>';
            } elseif ($tb['TinhTrang'] === 'Hỏng') {
                echo '<span class="badge bg-danger">Hỏng</span>';
            } else {
                echo '<span class="badge bg-warning">' . $tb['TinhTrang'] . '</span>';
            }
            echo '</td>';
            echo '<td>';
            echo '<button type="button" class="btn btn-danger btn-sm" ' .
                'onclick="confirmXoaThietBi(\'' . $tb['MaThietBi'] . '\', \'' . htmlspecialchars($tb['TenThietBi']) . '\')">' .
                '<i class="fas fa-trash"></i></button>';
            echo '</td>';
            echo '</tr>';
        }
    }
    exit();
}

// Lấy tham số filter
$keyword = $_GET['keyword'] ?? '';
$tang = $_GET['tang'] ?? '';
$loaiPhong = $_GET['loaiPhong'] ?? '';
$trangThai = $_GET['trangThai'] ?? '';

// Lấy dữ liệu
$danhSachPhong = $model->getDanhSachPhong($keyword, $tang, $loaiPhong, $trangThai);
$thongKe = $model->thongKePhong();
?>

<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-hotel me-2"></i>Quản Lý Phòng
        </h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#themPhongModal">
            <i class="fas fa-plus me-2"></i>Thêm Phòng Mới
        </button>
    </div>

    <!-- Thống kê nhanh -->
    <?php if ($thongKe): ?>
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tổng Phòng
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongPhong']; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-door-closed fa-2x text-gray-300"></i>
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
                                    Phòng Trống
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongTrong']; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bed fa-2x text-gray-300"></i>
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
                                    Đang Sử Dụng
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongDangSuDung']; ?>
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
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Bảo Trì
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongBaoTri']; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tools fa-2x text-gray-300"></i>
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
                    <div class="col-md-3 mb-2">
                        <input type="text" class="form-control" name="keyword"
                            placeholder="Tìm theo số phòng, tên..."
                            value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="tang">
                            <option value="">Tất cả tầng</option>
                            <option value="1" <?php echo $tang === '1' ? 'selected' : ''; ?>>Tầng 1</option>
                            <option value="2" <?php echo $tang === '2' ? 'selected' : ''; ?>>Tầng 2</option>
                            <option value="3" <?php echo $tang === '3' ? 'selected' : ''; ?>>Tầng 3</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="loaiPhong">
                            <option value="">Tất cả loại phòng</option>
                            <?php foreach ($dsLoaiPhong as $lp): ?>
                                <option value="<?php echo $lp['MaLoaiPhong']; ?>"
                                    <?php echo $loaiPhong == $lp['MaLoaiPhong'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lp['HangPhong'] . ' - ' . $lp['HinhThuc']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="trangThai">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Trống" <?php echo $trangThai === 'Trống' ? 'selected' : ''; ?>>Trống</option>
                            <option value="Đang sử dụng" <?php echo $trangThai === 'Đang sử dụng' ? 'selected' : ''; ?>>Đang sử dụng</option>
                            <option value="Bảo trì" <?php echo $trangThai === 'Bảo trì' ? 'selected' : ''; ?>>Bảo trì</option>
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

    <!-- Danh sách phòng -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Danh Sách Phòng
                <span class="badge bg-light text-dark ms-2"><?php echo count($danhSachPhong); ?> phòng</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="50"><input type="checkbox" id="selectAll"></th>
                            <th width="60">STT</th>
                            <th width="100">Số Phòng</th>
                            <th width="80">Tầng</th>
                            <th>Tên Phòng</th>
                            <th width="150">Loại Phòng</th>
                            <th width="120">Đơn Giá</th>
                            <th width="120">Trạng Thái</th>
                            <th width="150" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachPhong)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-door-closed fa-2x mb-3"></i><br>
                                    Không có dữ liệu phòng
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $stt = 1; ?>
                            <?php foreach ($danhSachPhong as $phong): ?>
                                <tr>
                                    <td><input type="checkbox" name="ma_phong_list[]" value="<?php echo $phong['MaPhong']; ?>" class="row-checkbox"></td>
                                    <td><?php echo $stt++; ?></td>
                                    <td>
                                        <span class="badge bg-success fs-6"><?php echo htmlspecialchars($phong['SoPhong']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Tầng <?php echo $phong['Tang']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($phong['roomName']); ?></strong>
                                        <?php if (!empty($phong['GhiChu'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($phong['GhiChu']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($phong['HangPhong']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($phong['HinhThuc']); ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?php echo number_format($phong['DonGia'], 0, ',', '.'); ?> đ</strong>
                                    </td>
                                    <td>
                                        <?php if ($phong['TrangThai'] === 'Trống'): ?>
                                            <span class="badge bg-success">Trống</span>
                                        <?php elseif ($phong['TrangThai'] === 'Đang sử dụng'): ?>
                                            <span class="badge bg-warning">Đang sử dụng</span>
                                        <?php elseif ($phong['TrangThai'] === 'Bảo trì'): ?>
                                            <span class="badge bg-danger">Bảo trì</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo $phong['TrangThai']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100">
                                            <button type="button" class="btn btn-info"
                                                onclick="showThietBiModal('<?php echo $phong['MaPhong']; ?>', '<?php echo htmlspecialchars($phong['roomName']); ?>')"
                                                title="Quản lý thiết bị">
                                                <i class="fas fa-tools"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning"
                                                onclick="showSuaPhongModal('<?php echo $phong['MaPhong']; ?>')"
                                                title="Sửa thông tin phòng">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="quanlyphong.php?action=xoa&ma_phong=<?php echo $phong['MaPhong']; ?>"
                                                class="btn btn-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa phòng <?php echo $phong['SoPhong']; ?>?')"
                                                title="Xóa phòng">
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

<!-- Modal Thêm Phòng -->
<div class="modal fade" id="themPhongModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Thêm Phòng Mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlyphong.php?action=them" id="formThemPhong" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Mã phòng sẽ được tự động tạo ngẫu nhiên (P123, P456, ...)
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tầng <span class="text-danger">*</span></label>
                            <select class="form-control" name="tang" required>
                                <option value="">-- Chọn tầng --</option>
                                <option value="1">Tầng 1</option>
                                <option value="2">Tầng 2</option>
                                <option value="3">Tầng 3</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại Phòng <span class="text-danger">*</span></label>
                            <select class="form-control" name="ma_loai_phong" required>
                                <option value="">-- Chọn loại phòng --</option>
                                <?php foreach ($dsLoaiPhong as $lp): ?>
                                    <option value="<?php echo $lp['MaLoaiPhong']; ?>">
                                        <?php echo htmlspecialchars($lp['HangPhong'] . ' - ' . $lp['HinhThuc'] . ' - ' . number_format($lp['DonGia'], 0, ',', '.') . ' đ'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
                            <select class="form-control" name="trang_thai" required>
                                <option value="Trống">Trống</option>
                                <option value="Đang sử dụng">Đang sử dụng</option>
                                <option value="Bảo trì">Bảo trì</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Tên Phòng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="room_name" required
                                placeholder="VD: Phòng Deluxe View Biển">
                        </div>
                        
                        <!-- Upload ảnh -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh Đại Diện (Avatar)</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                            <small class="text-muted">Chọn ảnh đại diện cho phòng</small>
                            <div id="avatar_preview" class="mt-2"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh Sách Ảnh Phòng</label>
                            <input type="file" class="form-control" name="danh_sach_anh[]" multiple accept="image/*">
                            <small class="text-muted">Chọn nhiều ảnh cho phòng</small>
                            <div id="images_preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm Phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Phòng -->
<div class="modal fade" id="suaPhongModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Sửa Thông Tin Phòng
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlyphong.php?action=sua" id="formSuaPhong">
                <input type="hidden" name="ma_phong" id="sua_ma_phong">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Sửa thông tin phòng <strong id="sua_so_phong_display"></strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Phòng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="so_phong" id="sua_so_phong" required
                                placeholder="VD: P101, P201">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tầng <span class="text-danger">*</span></label>
                            <select class="form-control" name="tang" id="sua_tang" required>
                                <option value="">-- Chọn tầng --</option>
                                <option value="1">Tầng 1</option>
                                <option value="2">Tầng 2</option>
                                <option value="3">Tầng 3</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại Phòng <span class="text-danger">*</span></label>
                            <select class="form-control" name="ma_loai_phong" id="sua_ma_loai_phong" required>
                                <option value="">-- Chọn loại phòng --</option>
                                <?php foreach ($dsLoaiPhong as $lp): ?>
                                    <option value="<?php echo $lp['MaLoaiPhong']; ?>">
                                        <?php echo htmlspecialchars($lp['HangPhong'] . ' - ' . $lp['HinhThuc'] . ' - ' . number_format($lp['DonGia'], 0, ',', '.') . ' đ'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
                            <select class="form-control" name="trang_thai" id="sua_trang_thai" required>
                                <option value="Trống">Trống</option>
                                <option value="Đang sử dụng">Đang sử dụng</option>
                                <option value="Bảo trì">Bảo trì</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Tên Phòng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="room_name" id="sua_room_name" required
                                placeholder="VD: Phòng Deluxe View Biển">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Quản lý Thiết bị -->
<div class="modal fade" id="thietBiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="thietBiModalTitle">
                    <i class="fas fa-tools me-2"></i>Quản lý Thiết bị
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="currentMaPhong">

                <!-- Form thêm thiết bị mới -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Thêm Thiết bị Mới
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="quanlyphong.php?action=them_thiet_bi" id="formThemThietBi">
                            <input type="hidden" name="ma_phong" id="maPhongThemThietBi">
                            <div class="row">
                                <div class="col-md-5 mb-2">
                                    <input type="text" class="form-control" name="ten_thiet_bi" required
                                        placeholder="Tên thiết bị">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <select class="form-control" name="tinh_trang" required>
                                        <option value="Tốt">Tốt</option>
                                        <option value="Hỏng">Hỏng</option>
                                        <option value="Cần bảo trì">Cần bảo trì</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button type="submit" class="btn btn-success w-100">Thêm</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách thiết bị hiện tại -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>Danh sách Thiết bị
                            <span class="badge bg-light text-dark ms-2" id="totalThietBi">0 thiết bị</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">STT</th>
                                        <th>Tên thiết bị</th>
                                        <th width="120">Tình trạng</th>
                                        <th width="80">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="tableThietBi">
                                    <!-- Danh sách thiết bị sẽ được load bằng JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Xem trước ảnh khi chọn file
    function previewImage(input, previewElementId) {
        const preview = document.getElementById(previewElementId);
        preview.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '200px';
                img.style.maxHeight = '150px';
                img.className = 'img-thumbnail m-1';
                preview.appendChild(img);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Xem trước nhiều ảnh
    function previewMultipleImages(input, previewElementId) {
        const preview = document.getElementById(previewElementId);
        preview.innerHTML = '';

        if (input.files) {
            for (let i = 0; i < input.files.length; i++) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.maxHeight = '150px';
                    img.className = 'img-thumbnail m-1';
                    preview.appendChild(img);
                }

                reader.readAsDataURL(input.files[i]);
            }
        }
    }

    // Thêm event listeners
    document.querySelector('input[name="avatar"]')?.addEventListener('change', function() {
        previewImage(this, 'avatar_preview');
    });

    document.querySelector('input[name="danh_sach_anh[]"]')?.addEventListener('change', function() {
        previewMultipleImages(this, 'images_preview');
    });
    
    // Chọn tất cả
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Hiển thị modal sửa phòng
    function showSuaPhongModal(maPhong) {
        fetch('quanlyphong.php?action=get_phong_info&ma_phong=' + maPhong)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const phong = data.data;

                    // Điền dữ liệu vào form
                    document.getElementById('sua_ma_phong').value = phong.MaPhong;
                    document.getElementById('sua_so_phong_display').textContent = phong.SoPhong;
                    document.getElementById('sua_so_phong').value = phong.SoPhong;
                    document.getElementById('sua_tang').value = phong.Tang;
                    document.getElementById('sua_ma_loai_phong').value = phong.MaLoaiPhong;
                    document.getElementById('sua_trang_thai').value = phong.TrangThai;
                    document.getElementById('sua_room_name').value = phong.roomName;

                    // Hiển thị modal
                    new bootstrap.Modal(document.getElementById('suaPhongModal')).show();
                } else {
                    alert('Không thể tải thông tin phòng!');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Lỗi khi tải thông tin phòng!');
            });
    }

    // Hiển thị modal quản lý thiết bị
    function showThietBiModal(maPhong, tenPhong) {
        document.getElementById('thietBiModalTitle').innerHTML =
            '<i class="fas fa-tools me-2"></i>Quản lý Thiết bị - ' + tenPhong;
        document.getElementById('currentMaPhong').value = maPhong;
        document.getElementById('maPhongThemThietBi').value = maPhong;

        // Load danh sách thiết bị hiện tại
        loadThietBiPhong(maPhong);

        // Hiển thị modal
        new bootstrap.Modal(document.getElementById('thietBiModal')).show();
    }

    // Load danh sách thiết bị phòng
    function loadThietBiPhong(maPhong) {
        fetch('quanlyphong.php?action=get_thiet_bi&ma_phong=' + maPhong)
            .then(response => response.text())
            .then(data => {
                document.getElementById('tableThietBi').innerHTML = data;
                // Cập nhật tổng số thiết bị
                const totalRows = document.getElementById('tableThietBi').querySelectorAll('tr').length;
                document.getElementById('totalThietBi').textContent = totalRows + ' thiết bị';
            });
    }

    // Xác nhận xóa thiết bị
    function confirmXoaThietBi(maThietBi, tenThietBi) {
        if (confirm('Bạn có chắc muốn xóa thiết bị "' + tenThietBi + '"?')) {
            window.location.href = 'quanlyphong.php?action=xoa_thiet_bi&ma_thiet_bi=' + maThietBi;
        }
    }
</script>

<?php include_once '../layouts/footer.php'; ?>