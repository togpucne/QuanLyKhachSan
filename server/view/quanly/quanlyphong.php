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

// LẤY DANH SÁCH LOẠI PHÒNG CHO FORM
$dsLoaiPhong = $model->getDanhSachLoaiPhong();

// XỬ LÝ THÊM PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'them') {
    $data = [
        'Tang' => $_POST['tang'],
        'MaLoaiPhong' => $_POST['ma_loai_phong'],
        'TrangThai' => $_POST['trang_thai'],
        'roomName' => $_POST['room_name'],
        'GiaPhong' => $_POST['gia_phong'],
        'DienTich' => $_POST['dien_tich'],
        'SoKhachToiDa' => $_POST['so_khach_toi_da'],
        'HuongNha' => $_POST['huong_nha'],
        'MoTaChiTiet' => $_POST['mo_ta_chi_tiet'],
        'TienNghi' => $_POST['tien_nghi_json']
    ];

    $avatarFile = isset($_FILES['avatar']) ? $_FILES['avatar'] : null;
    $imageFiles = isset($_FILES['danh_sach_anh']) ? $_FILES['danh_sach_anh'] : null;

    $result = $model->themPhongMoi($data, $avatarFile, $imageFiles);

    if ($result['success']) {
        $_SESSION['success'] = "Thêm phòng thành công! Số phòng: " . $result['soPhong'];
    } else {
        $_SESSION['error'] = "Lỗi khi thêm phòng: " . $result['error'];
    }
    header('Location: quanlyphong.php');
    exit();
}

// XỬ LÝ XÓA PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'xoa') {
    $maPhong = $_POST['ma_phong'] ?? 0;

    if ($maPhong) {
        $result = $model->xoaPhong($maPhong);
        if ($result['success']) {
            $_SESSION['success'] = "Xóa phòng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa phòng: " . $result['error'];
        }
    }
    header('Location: quanlyphong.php');
    exit();
}

// XÓA NHIỀU PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'xoa_nhieu') {
    $maPhongs = $_POST['ma_phongs'] ?? [];

    if (!empty($maPhongs)) {
        $result = $model->xoaNhieuPhong($maPhongs);
        if ($result['success']) {
            $_SESSION['success'] = "Đã xóa " . $result['so_luong'] . " phòng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa phòng: " . $result['error'];
        }
    }
    header('Location: quanlyphong.php');
    exit();
}
// XỬ LÝ LẤY THÔNG TIN PHÒNG ĐỂ SỬA (THÊM VÀO)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'lay_thong_tin') {
    $maPhong = $_GET['ma_phong'] ?? 0;

    // ĐẢM BẢO KHÔNG CÓ OUTPUT NÀO TRƯỚC JSON
    if (ob_get_length()) ob_clean();

    if ($maPhong) {
        $phong = $model->getChiTietPhong($maPhong);

        header('Content-Type: application/json; charset=utf-8');

        if ($phong) {
            echo json_encode($phong, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            echo json_encode(['error' => 'Không tìm thấy phòng'], JSON_UNESCAPED_UNICODE);
        }
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Mã phòng không hợp lệ'], JSON_UNESCAPED_UNICODE);
    }
    exit(); // QUAN TRỌNG: Dừng lại, không render HTML
}
// XỬ LÝ CẬP NHẬT PHÒNG (THÊM VÀO)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sua') {
    $maPhong = $_POST['ma_phong'] ?? 0;

    if ($maPhong) {
        $data = [
            'Tang' => $_POST['tang'],
            'MaLoaiPhong' => $_POST['ma_loai_phong'],
            'TrangThai' => $_POST['trang_thai'],
            'roomName' => $_POST['room_name'],
            'GiaPhong' => $_POST['gia_phong'],
            'DienTich' => $_POST['dien_tich'],
            'SoKhachToiDa' => $_POST['so_khach_toi_da'],
            'HuongNha' => $_POST['huong_nha'],
            'MoTaChiTiet' => $_POST['mo_ta_chi_tiet'],
            'TienNghi' => $_POST['tien_nghi_json']
        ];

        $avatarFile = isset($_FILES['avatar']) ? $_FILES['avatar'] : null;
        $imageFiles = isset($_FILES['danh_sach_anh']) ? $_FILES['danh_sach_anh'] : null;

        $result = $model->capNhatPhong($maPhong, $data, $avatarFile, $imageFiles);

        if ($result['success']) {
            $_SESSION['success'] = "Cập nhật phòng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật phòng: " . $result['error'];
        }
    } else {
        $_SESSION['error'] = "Mã phòng không hợp lệ!";
    }
    header('Location: quanlyphong.php');
    exit();
}
// XỬ LÝ XÓA ẢNH CHI TIẾT (THÊM VÀO - TÙY CHỌN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'xoa_anh') {
    $maPhong = $_POST['ma_phong'] ?? 0;
    $imgPath = $_POST['img_path'] ?? '';

    if ($maPhong && $imgPath) {
        // Thêm hàm này vào model trước
        $result = $model->xoaAnhChiTiet($maPhong, $imgPath);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
}
// LẤY THAM SỐ BỘ LỌC
$keyword = $_GET['keyword'] ?? '';
$tang = $_GET['tang'] ?? '';
$loaiPhong = $_GET['loaiPhong'] ?? '';
$trangThai = $_GET['trangThai'] ?? '';

// LẤY DANH SÁCH PHÒNG THEO BỘ LỌC
$danhSachPhong = $model->getDanhSachPhong($keyword, $tang, $loaiPhong, $trangThai);
?>

<!-- PHẦN VIEW -->
<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center py-4">
        <div>
            <h1 class="h3 mb-1 text-gray-900">Quản Lý Phòng</h1>
            <p class="text-muted">Quản lý thông tin và trạng thái các phòng</p>
        </div>
        <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#themPhongModal">
            + Thêm Phòng
        </button>
    </div>

    <!-- Search and Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Tìm kiếm</label>
                        <input type="text" class="form-control" name="keyword"
                            placeholder="Số phòng, tên phòng..."
                            value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Tầng</label>
                        <select class="form-control" name="tang">
                            <option value="">Tất cả tầng</option>
                            <option value="1" <?php echo $tang === '1' ? 'selected' : ''; ?>>Tầng 1</option>
                            <option value="2" <?php echo $tang === '2' ? 'selected' : ''; ?>>Tầng 2</option>
                            <option value="3" <?php echo $tang === '3' ? 'selected' : ''; ?>>Tầng 3</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Loại phòng</label>
                        <select class="form-control" name="loaiPhong">
                            <option value="">Tất cả loại</option>
                            <?php foreach ($dsLoaiPhong as $lp): ?>
                                <option value="<?php echo $lp['MaLoaiPhong']; ?>"
                                    <?php echo $loaiPhong == $lp['MaLoaiPhong'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lp['HangPhong']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Trạng thái</label>
                        <select class="form-control" name="trangThai">
                            <option value="">Tất cả</option>
                            <option value="Trống" <?php echo $trangThai === 'Trống' ? 'selected' : ''; ?>>Trống</option>
                            <option value="Đang sử dụng" <?php echo $trangThai === 'Đang sử dụng' ? 'selected' : ''; ?>>Đang sử dụng</option>
                            <option value="Đang bảo trì" <?php echo $trangThai === 'Đang bảo trì' ? 'selected' : ''; ?>>Bảo trì</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Lọc</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Danh sách phòng -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-gray-900 fw-semibold">
                    Danh sách phòng
                    <span class="badge bg-light text-dark ms-2"><?php echo count($danhSachPhong); ?></span>
                </h6>
                <?php if (!empty($danhSachPhong)): ?>
                    <div class="d-flex gap-2">

                        <button type="button" class="btn btn-sm btn-danger" id="xoaNhieuPhong" disabled>
                            Xóa đã chọn
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <form id="formXoaNhieu" method="POST" action="quanlyphong.php?action=xoa_nhieu">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="ps-4 py-3">
                                    <input type="checkbox" id="checkAll" class="form-check-input">
                                </th>
                                <th width="60" class="py-3 text-muted small fw-normal">STT</th>
                                <th width="100" class="py-3 text-muted small fw-normal">Số Phòng</th>
                                <th width="80" class="py-3 text-muted small fw-normal">Tầng</th>
                                <th class="py-3 text-muted small fw-normal">Tên Phòng</th>
                                <th width="120" class="py-3 text-muted small fw-normal">Loại Phòng</th>
                                <th width="130" class="py-3 text-muted small fw-normal">Tổng Tiền</th>
                                <th width="100" class="py-3 text-muted small fw-normal">Trạng Thái</th>
                                <th width="120" class="text-center py-3 text-muted small fw-normal">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($danhSachPhong)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <div class="py-4">
                                            <div class="text-muted mb-2">Không có dữ liệu phòng</div>
                                            <small class="text-muted">Thêm phòng mới để bắt đầu quản lý</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $stt = 1; ?>
                                <?php foreach ($danhSachPhong as $phong): ?>
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <input type="checkbox" class="form-check-input checkPhong"
                                                name="ma_phongs[]" value="<?php echo $phong['MaPhong']; ?>">
                                        </td>
                                        <td class="py-3 text-muted"><?php echo $stt++; ?></td>
                                        <td class="py-3">
                                            <span class="fw-semibold text-gray-900"><?php echo htmlspecialchars($phong['SoPhong']); ?></span>
                                        </td>
                                        <td class="py-3 text-muted">Tầng <?php echo $phong['Tang']; ?></td>
                                        <td class="py-3">
                                            <div class="fw-medium text-gray-900"><?php echo htmlspecialchars($phong['roomName']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($phong['HangPhong']); ?></small>
                                        </td>
                                        <td class="py-3 text-muted"><?php echo htmlspecialchars($phong['HinhThuc']); ?></td>
                                        <td class="py-3">
                                            <span class="fw-bold text-success">
                                                <?php echo number_format($phong['TongGia'], 0, ',', '.'); ?> đ
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <?php if ($phong['TrangThai'] === 'Trống'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border-0">Trống</span>
                                            <?php elseif ($phong['TrangThai'] === 'Đang sử dụng'): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border-0">Đang sử dụng</span>
                                            <?php elseif ($phong['TrangThai'] === 'Đang bảo trì'): ?> <span class="badge bg-danger bg-opacity-10 text-danger border-0">Bảo trì</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo $phong['TrangThai']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center py-3">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary border-1 px-3"
                                                    onclick="suaPhong(<?php echo $phong['MaPhong']; ?>)">
                                                    Sửa
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger border-1 px-3"
                                                    onclick="xoaPhong(<?php echo $phong['MaPhong']; ?>, '<?php echo htmlspecialchars($phong['SoPhong']); ?>')">
                                                    Xóa
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Phòng -->
<div class="modal fade" id="themPhongModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-900">Thêm Phòng Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlyphong.php?action=them" enctype="multipart/form-data" id="formThemPhong">
                <div class="modal-body pt-0">
                    <div class="alert alert-info border-0 bg-light mb-4">
                        <small class="text-muted">Mã phòng sẽ được tự động tạo (P101, P102, ...)</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Tầng <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="tang" required>
                                <option value="">Chọn tầng</option>
                                <option value="1">Tầng 1</option>
                                <option value="2">Tầng 2</option>
                                <option value="3">Tầng 3</option>
                                <option value="4">Tầng 4</option>
                                <option value="5">Tầng 5</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Loại Phòng <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="ma_loai_phong" required id="selectLoaiPhong">
                                <option value="">Chọn loại phòng</option>
                                <?php foreach ($dsLoaiPhong as $lp): ?>
                                    <option value="<?php echo $lp['MaLoaiPhong']; ?>" data-dongia="<?php echo $lp['DonGia']; ?>">
                                        <?php echo htmlspecialchars($lp['HangPhong'] . ' - ' . $lp['HinhThuc']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Giá Phòng (VND) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control border-1" name="gia_phong" required
                                min="0" step="1000" placeholder="500000" value="0" id="giaPhong">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Tổng Tiền (VND)</label>
                            <input type="text" class="form-control border-1 bg-light" id="tongGia" readonly value="0 đ">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Trạng Thái <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="trang_thai" required>
                                <option value="Trống">Trống</option>
                                <option value="Đang sử dụng">Đang sử dụng</option>
                                <option value="Đang bảo trì">Bảo trì</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Diện tích (m²) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control border-1" name="dien_tich" required
                                min="0" step="0.1" placeholder="25.5" value="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Số khách tối đa <span class="text-danger">*</span></label>
                            <input type="number" class="form-control border-1" name="so_khach_toi_da" required
                                min="1" max="10" placeholder="2" value="2">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Hướng nhà <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="huong_nha" required>
                                <option value="">Chọn hướng nhà</option>
                                <option value="Hướng Biển">Hướng Biển</option>
                                <option value="Hướng Núi">Hướng Núi</option>
                                <option value="Hướng Thành Phố">Hướng Thành Phố</option>
                                <option value="Hướng Hồ Bơi">Hướng Hồ Bơi</option>
                                <option value="Hướng Sân Vườn">Hướng Sân Vườn</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-gray-900">Tên Phòng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-1" name="room_name" required
                                placeholder="Phòng Deluxe View Biển">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-gray-900">Mô tả chi tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control border-1" name="mo_ta_chi_tiet" rows="3" required
                                placeholder="Mô tả về phòng..."></textarea>
                        </div>

                        <!-- Tiện nghi -->
                        <div class="col-12">
                            <label class="form-label text-gray-900">Tiện nghi</label>
                            <div class="border border-1 rounded p-3 bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php
                                        $tienNghiList = [
                                            'Điều hòa',
                                            'TV màn hình phẳng',
                                            'Minibar',
                                            'Ban công',
                                            'Bồn tắm',
                                            'Vòi sen',
                                            'Wifi miễn phí',
                                            'Bếp nhỏ'
                                        ];
                                        foreach ($tienNghiList as $index => $tienNghi): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="tien_nghi[]"
                                                    value="<?php echo $tienNghi; ?>" id="tienNghi<?php echo $index + 1; ?>">
                                                <label class="form-check-label text-gray-900" for="tienNghi<?php echo $index + 1; ?>">
                                                    <?php echo $tienNghi; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="tienNghiKhacCheck">
                                        <label class="form-check-label text-gray-900" for="tienNghiKhacCheck">Tiện nghi khác</label>
                                    </div>
                                    <textarea class="form-control border-1 mt-2" id="tienNghiKhac" name="tien_nghi_khac" rows="2"
                                        placeholder="Mỗi tiện nghi một dòng" style="display: none;"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Upload ảnh -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Ảnh Đại Diện <span class="text-danger">*</span></label>
                            <input type="file" class="form-control border-1" name="avatar" accept="image/*" required id="avatarUpload">
                            <div class="mt-2" id="avatarPreview"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Ảnh Chi Tiết</label>
                            <input type="file" class="form-control border-1" name="danh_sach_anh[]" multiple accept="image/*" id="multipleUpload">
                            <div class="mt-2" id="multiplePreview"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary px-4">Thêm Phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Sửa Phòng -->
<div class="modal fade" id="suaPhongModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-900">Sửa Thông Tin Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="quanlyphong.php?action=sua" enctype="multipart/form-data" id="formSuaPhong">
                <input type="hidden" name="ma_phong" id="suaMaPhong">
                <div class="modal-body pt-0">
                    <div class="alert alert-info border-0 bg-light mb-4">
                        <small class="text-muted" id="suaSoPhongInfo">Số phòng: </small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Tầng <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="tang" required id="suaTang">
                                <option value="">Chọn tầng</option>
                                <option value="1">Tầng 1</option>
                                <option value="2">Tầng 2</option>
                                <option value="3">Tầng 3</option>
                                <option value="4">Tầng 4</option>
                                <option value="5">Tầng 5</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Loại Phòng <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="ma_loai_phong" required id="suaMaLoaiPhong">
                                <option value="">Chọn loại phòng</option>
                                <?php foreach ($dsLoaiPhong as $lp): ?>
                                    <option value="<?php echo $lp['MaLoaiPhong']; ?>" data-dongia="<?php echo $lp['DonGia']; ?>">
                                        <?php echo htmlspecialchars($lp['HangPhong'] . ' - ' . $lp['HinhThuc']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Giá Phòng (VND) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control border-1" name="gia_phong" required
                                min="0" step="1000" placeholder="500000" id="suaGiaPhong">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Tổng Tiền (VND)</label>
                            <input type="text" class="form-control border-1 bg-light" id="suaTongGia" readonly value="0 đ">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Trạng Thái <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="trang_thai" required id="suaTrangThai">
                                <option value="Trống">Trống</option>
                                <option value="Đang sử dụng">Đang sử dụng</option>
                                <option value="Đang bảo trì">Bảo trì</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Diện tích (m²) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control border-1" name="dien_tich" required
                                min="0" step="0.1" placeholder="25.5" id="suaDienTich">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Số khách tối đa <span class="text-danger">*</span></label>
                            <input type="number" class="form-control border-1" name="so_khach_toi_da" required
                                min="1" max="10" placeholder="2" id="suaSoKhachToiDa">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Hướng nhà <span class="text-danger">*</span></label>
                            <select class="form-control border-1" name="huong_nha" required id="suaHuongNha">
                                <option value="">Chọn hướng nhà</option>
                                <option value="Hướng Biển">Hướng Biển</option>
                                <option value="Hướng Núi">Hướng Núi</option>
                                <option value="Hướng Thành Phố">Hướng Thành Phố</option>
                                <option value="Hướng Hồ Bơi">Hướng Hồ Bơi</option>
                                <option value="Hướng Sân Vườn">Hướng Sân Vườn</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-gray-900">Tên Phòng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-1" name="room_name" required
                                placeholder="Phòng Deluxe View Biển" id="suaRoomName">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-gray-900">Mô tả chi tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control border-1" name="mo_ta_chi_tiet" rows="3" required
                                placeholder="Mô tả về phòng..." id="suaMoTaChiTiet"></textarea>
                        </div>

                        <!-- Tiện nghi -->
                        <div class="col-12">
                            <label class="form-label text-gray-900">Tiện nghi</label>
                            <div class="border border-1 rounded p-3 bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php
                                        $tienNghiList = [
                                            'Điều hòa',
                                            'TV màn hình phẳng',
                                            'Minibar',
                                            'Ban công',
                                            'Bồn tắm',
                                            'Vòi sen',
                                            'Wifi miễn phí',
                                            'Bếp nhỏ'
                                        ];
                                        foreach ($tienNghiList as $index => $tienNghi): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input sua-tien-nghi" type="checkbox" name="tien_nghi[]"
                                                    value="<?php echo $tienNghi; ?>" id="suaTienNghi<?php echo $index + 1; ?>">
                                                <label class="form-check-label text-gray-900" for="suaTienNghi<?php echo $index + 1; ?>">
                                                    <?php echo $tienNghi; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="suaTienNghiKhacCheck">
                                        <label class="form-check-label text-gray-900" for="suaTienNghiKhacCheck">Tiện nghi khác</label>
                                    </div>
                                    <textarea class="form-control border-1 mt-2" id="suaTienNghiKhac" name="tien_nghi_khac" rows="2"
                                        placeholder="Mỗi tiện nghi một dòng" style="display: none;"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Upload ảnh -->
                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Ảnh Đại Diện</label>
                            <input type="file" class="form-control border-1" name="avatar" accept="image/*" id="suaAvatarUpload">
                            <div class="mt-2" id="suaAvatarPreview"></div>
                            <div class="mt-2" id="currentAvatar"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-gray-900">Thêm Ảnh Chi Tiết</label>
                            <input type="file" class="form-control border-1" name="danh_sach_anh[]" multiple accept="image/*" id="suaMultipleUpload">
                            <div class="mt-2" id="suaMultiplePreview"></div>
                            <div class="mt-2" id="currentImages"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary px-4">Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Form Xóa Ẩn -->
<form id="formXoaPhong" method="POST" action="quanlyphong.php?action=xoa" style="display: none;">
    <input type="hidden" name="ma_phong" id="maPhongXoa">
</form>

<script src="../../assets/js/quanlyphong.js"></script>

<?php include_once '../layouts/footer.php'; ?>