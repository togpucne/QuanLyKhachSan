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
        'GiaPhong' => $_POST['gia_phong']
    ];

    // Lấy file upload
    $avatarFile = isset($_FILES['avatar']) ? $_FILES['avatar'] : null;
    $imageFiles = isset($_FILES['danh_sach_anh']) ? $_FILES['danh_sach_anh'] : null;

    // Gọi model thêm phòng
    $result = $model->themPhongMoi($data, $avatarFile, $imageFiles);

    if ($result['success']) {
        $_SESSION['success'] = "Thêm phòng thành công! Số phòng: " . $result['soPhong'];
    } else {
        $_SESSION['error'] = "Lỗi khi thêm phòng: " . $result['error'];
    }
    header('Location: quanlyphong.php');
    exit();
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
                            <th width="60">STT</th>
                            <th width="100">Số Phòng</th>
                            <th width="80">Tầng</th>
                            <th>Tên Phòng</th>
                            <th width="150">Loại Phòng</th>
                            <th width="120">Giá Phòng</th>
                            <th width="120">Trạng Thái</th>
                            <th width="100" class="text-center">Ảnh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachPhong)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-door-closed fa-2x mb-3"></i><br>
                                    Không có dữ liệu phòng
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $stt = 1; ?>
                            <?php foreach ($danhSachPhong as $phong): ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td>
                                        <span class="badge bg-success fs-6"><?php echo htmlspecialchars($phong['SoPhong']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Tầng <?php echo $phong['Tang']; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($phong['roomName']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($phong['HangPhong']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($phong['HinhThuc']); ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?php echo number_format($phong['GiaPhong'], 0, ',', '.'); ?> đ</strong>
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
                                    <td class="text-center">
                                        <?php if (!empty($phong['Avatar'])): ?>
                                            <i class="fas fa-image text-success" title="Có ảnh"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-muted" title="Không có ảnh"></i>
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
            <form method="POST" action="quanlyphong.php?action=them" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Mã phòng sẽ được tự động tạo (P101, P102, ...)
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
                            <label class="form-label">Giá Phòng (VND) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="gia_phong" required
                                min="0" step="1000" placeholder="VD: 500000" value="0">
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
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh Sách Ảnh Phòng</label>
                            <input type="file" class="form-control" name="danh_sach_anh[]" multiple accept="image/*">
                            <small class="text-muted">Chọn nhiều ảnh cho phòng</small>
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

<?php include_once '../layouts/footer.php'; ?>