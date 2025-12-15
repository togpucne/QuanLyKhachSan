<?php
// THÊM SESSION_START() và KIỂM TRA ĐĂNG NHẬP
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['vaitro'])) {
    header('Location: ../login/login.php');
    exit();
}

// Kiểm tra vai trò - chỉ cho phép kinhdoanh
if ($_SESSION['vaitro'] !== 'kinhdoanh') {
    $_SESSION['error'] = "Bạn không có quyền truy cập!";
    header('Location: ../home/dashboard.php');
    exit();
}

$pageTitle = "Quản lý khuyến mãi - ABC Resort";

// SỬA ĐƯỜNG DẪN - đi ra 2 cấp rồi vào model
require_once __DIR__ . '/../../model/KhuyenMaiModel.php';
$khuyenMaiModel = new KhuyenMaiModel();
$khuyenMais = $khuyenMaiModel->getAllKhuyenMai();

// Kiểm tra lỗi SQL
$hasError = false;
if ($khuyenMais === false) {
    $hasError = true;
    $errorMessage = "Có lỗi xảy ra khi tải dữ liệu khuyến mãi";
}
require_once __DIR__ . '/../layouts/header.php';

?>
<style>
    /* Style đơn giản cho điều kiện */
    .condition-active {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }

    .condition-disabled {
        opacity: 0.6;
    }

    /* Bố cục đơn giản cho modal */
    .modal-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
    }

    .modal-section-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #495057;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
    }

    /* Ảnh preview */
    .image-preview-container {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        text-align: center;
        background-color: #f8f9fa;
    }

    .image-preview {
        max-width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
    }

    /* Icon đơn giản */
    .simple-icon {
        font-size: 0.9rem;
        opacity: 0.8;
    }
</style>
<div class="row">
    <div class="col-12">


        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Quản lý khuyến mãi</h4>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKhuyenMaiModal">
                    <i class="fas fa-plus me-1"></i>Thêm khuyến mãi
                </button>
            </div>
        </div>

        <!-- THÊM PHẦN THÔNG BÁO -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Hiển thị lỗi SQL nếu có -->
        <?php if ($hasError): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $errorMessage; ?>
                <br><small>Vui lòng kiểm tra cấu trúc database hoặc liên hệ quản trị viên.</small>
            </div>
        <?php endif; ?>

        <!-- Form xóa nhiều -->
        <form id="deleteMultipleForm" action="../../controller/khuyenmaiController.php?action=deleteMultiple" method="POST">
            <div class="table-container p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="50"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th width="60">STT</th>
                                <th>Mã KM</th>
                                <th>Tên khuyến mãi</th>
                                <th>Ảnh</th>
                                <th>Giảm giá</th>
                                <th>Điều kiện</th> <!-- THÊM CỘT NÀY -->
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Người tạo</th>
                                <th>Trạng thái</th>
                                <th width="120">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$hasError && $khuyenMais && $khuyenMais->num_rows > 0): ?>
                                <?php $stt = 1; ?>
                                <?php while ($km = $khuyenMais->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_ids[]" value="<?php echo $km['MaKM']; ?>" class="form-check-input select-item">
                                        </td>
                                        <td><?php echo $stt++; ?></td>
                                        <td><strong>KM<?php echo str_pad($km['MaKM'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo htmlspecialchars($km['TenKhuyenMai']); ?></td>
                                        <td>
                                            <?php if ($km['HinhAnh']): ?>
                                                <?php
                                                // Tạo đường dẫn tuyệt đối từ document root
                                                $imagePath = '/ABC-Resort/client/' . $km['HinhAnh'];
                                                ?>
                                                <img src="<?php echo $imagePath; ?>"
                                                    alt="<?php echo htmlspecialchars($km['TenKhuyenMai']); ?>"
                                                    class="img-thumbnail"
                                                    style="width: 80px; height: 80px; object-fit: cover;"
                                                    onerror="this.src='../../client/assets/images/sales/default_promotion.png'">
                                            <?php else: ?>
                                                <div class="text-muted">Không có ảnh</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $km['MucGiamGia']; ?>%</span>
                                        </td>
                                        <!-- Trong vòng lặp hiển thị khuyến mãi -->
                                        <td>
                                            <?php
                                            $dkText = '';
                                            if (!empty($km['DK_HoaDonTu'])) {
                                                $dkText = 'HĐ từ ' . number_format($km['DK_HoaDonTu'], 0, ',', '.') . ' VND';
                                            } elseif (!empty($km['DK_SoDemTu'])) {
                                                $dkText = 'Từ ' . $km['DK_SoDemTu'] . ' đêm';
                                            } else {
                                                $dkText = '<span class="text-muted">Không có ĐK</span>';
                                            }
                                            echo $dkText;
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($km['NgayBatDau'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($km['NgayKetThuc'])); ?></td>
                                        <td><?php echo $km['TenNhanVienTao'] ?? 'N/A'; ?></td>
                                        <td>
                                            <?php
                                            $today = date('Y-m-d');
                                            if ($km['TrangThai'] == 0) {
                                                echo '<span class="badge bg-secondary">Đã kết thúc</span>';
                                            } elseif ($km['NgayBatDau'] <= $today && $km['NgayKetThuc'] >= $today) {
                                                echo '<span class="badge bg-success">Đang hoạt động</span>';
                                            } elseif ($km['NgayBatDau'] > $today) {
                                                echo '<span class="badge bg-warning">Sắp diễn ra</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Đã kết thúc</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <!-- Trong table, cập nhật nút edit -->
                                            <button class="btn btn-sm btn-outline-primary edit-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editKhuyenMaiModal"
                                                data-id="<?php echo $km['MaKM']; ?>"
                                                data-ten="<?php echo htmlspecialchars($km['TenKhuyenMai']); ?>"
                                                data-giamgia="<?php echo $km['MucGiamGia']; ?>"
                                                data-batdau="<?php echo $km['NgayBatDau']; ?>"
                                                data-ketthuc="<?php echo $km['NgayKetThuc']; ?>"
                                                data-mota="<?php echo htmlspecialchars($km['MoTa']); ?>"
                                                data-hinhanh="<?php echo $km['HinhAnh']; ?>"
                                                data-loaigiamgia="<?php echo $km['LoaiGiamGia'] ?? 'phantram'; ?>"
                                                data-dkhoadontu="<?php echo $km['DK_HoaDonTu'] ?? ''; ?>"
                                                data-dksodemtu="<?php echo $km['DK_SoDemTu'] ?? ''; ?>"
                                                data-giamgiatoida="<?php echo $km['GiamGiaToiDa'] ?? '0'; ?>"
                                                title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="../../controller/khuyenmaiController.php?action=delete&id=<?php echo $km['MaKM']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa khuyến mãi này?')"
                                                title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php elseif (!$hasError): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <i class="fas fa-gift fa-2x mb-2"></i><br>
                                        Chưa có khuyến mãi nào
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Bulk Actions -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <button type="button" id="deleteMultipleBtn" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i>Xóa nhiều
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Thêm khuyến mãi - BỐ CỤC MỚI -->
<div class="modal fade" id="addKhuyenMaiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addKhuyenMaiModalForm" action="../../controller/khuyenmaiController.php?action=add" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm khuyến mãi mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Phần 1: Thông tin cơ bản -->
                    <div class="modal-section">
                        <div class="modal-section-title">Thông tin cơ bản</div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ten_khuyenmai" placeholder="Nhập tên khuyến mãi" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" name="mo_ta" rows="2" placeholder="Mô tả ngắn về khuyến mãi"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Phần 2: Chi tiết giảm giá -->
                    <div class="modal-section">
                        <div class="modal-section-title">Chi tiết giảm giá</div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Loại giảm giá </label>
                                <select class="form-control" name="loai_giamgia" id="add_loai_giamgia" required>
                                    <option value="phantram">Giảm theo phần trăm (%)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mức giảm giá <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="muc_giamgia" id="add_muc_giamgia"
                                        min="1" step="0.01" placeholder="10" required>
                                    <span class="input-group-text" id="add_donvi_giamgia">%</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giảm tối đa (VND) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="giamgia_toida" id="add_giamgia_toida"
                                        min="0" step="10000" placeholder="0" required>
                                    <span class="input-group-text">VND</span>
                                </div>
                                <div class="form-text">Để 0 nếu không giới hạn</div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Thêm khuyến mãi - Phần điều kiện -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            Điều kiện áp dụng <span class="text-danger">*</span>
                        </div>

                        <div class="alert alert-light border mb-3">
                           

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card condition-field" id="add_condition_hoadon">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-bold">Hóa đơn từ</label>
                                                <span class="badge bg-primary">Điều kiện 1</span>
                                            </div>
                                            <div class="input-group">
                                                <input type="number" class="form-control condition-input"
                                                    name="dk_hoadon_tu" id="add_dk_hoadon_tu"
                                                    min="500000" step="100000"
                                                    placeholder="500000">
                                                <span class="input-group-text">VND</span>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i> Tối thiểu: 500,000 VND
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card condition-field" id="add_condition_sodem">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-bold">Số đêm từ</label>
                                                <span class="badge bg-primary">Điều kiện 2</span>
                                            </div>
                                            <div class="input-group">
                                                <input type="number" class="form-control condition-input"
                                                    name="dk_sodem_tu" id="add_dk_sodem_tu"
                                                    min="2" step="1" placeholder="2">
                                                <span class="input-group-text">đêm</span>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i> Tối thiểu: 2 đêm
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            

                            <div class="alert alert-info mt-3">
                                <div class="small">
                                    <i class="fas fa-lightbulb me-1"></i> <strong>Lưu ý:</strong>
                                    Nếu không chọn điều kiện nào, hệ thống sẽ tự động áp dụng <strong>Hóa đơn từ 500,000 VND</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần 4: Thời gian áp dụng -->
                    <div class="modal-section">
                        <div class="modal-section-title">Thời gian áp dụng</div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_batdau" id="add_ngay_batdau" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_ketthuc" id="add_ngay_ketthuc" required>
                            </div>
                        </div>
                    </div>

                    <!-- Phần 5: Hình ảnh -->
                    <div class="modal-section">
                        <div class="modal-section-title">Hình ảnh khuyến mãi <span class="text-danger">*</span></div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="image-preview-container">
                                    <img id="add_image_preview" src="../../../client/assets/images/sales/default_promotion.png"
                                        class="image-preview">
                                    <div class="mb-2">
                                        <input type="file" class="form-control" name="hinh_anh" id="add_hinh_anh" accept="image/*" required>
                                    </div>
                                    <div class="small text-muted">
                                        Định dạng: JPEG, PNG, GIF • Kích thước tối đa: 5MB
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm khuyến mãi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa khuyến mãi - BỐ CỤC MỚI -->
<div class="modal fade" id="editKhuyenMaiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editKhuyenMaiModalForm" action="../../controller/khuyenmaiController.php?action=edit" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="ma_km" id="edit_ma_km">

                <div class="modal-header">
                    <h5 class="modal-title">Sửa khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Phần 1: Thông tin cơ bản -->
                    <div class="modal-section">
                        <div class="modal-section-title">Thông tin cơ bản</div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ten_khuyenmai" id="edit_ten_khuyenmai" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" name="mo_ta" id="edit_mo_ta" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Phần 2: Chi tiết giảm giá -->
                    <div class="modal-section">
                        <div class="modal-section-title">Chi tiết giảm giá</div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Loại giảm giá </label>
                                <select class="form-control" name="loai_giamgia" id="edit_loai_giamgia" required>
                                    <option value="phantram">Giảm theo phần trăm (%)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mức giảm giá <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="muc_giamgia" id="edit_muc_giamgia"
                                        min="1" step="0.01" required>
                                    <span class="input-group-text" id="edit_donvi_giamgia">%</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giảm tối đa (VND)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="giamgia_toida" id="edit_giamgia_toida"
                                        min="0" step="10000">
                                    <span class="input-group-text">VND</span>
                                </div>
                                <div class="form-text">Để 0 nếu không giới hạn</div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần 3: Điều kiện áp dụng -->
                    <div class="modal-section">
                        <div class="modal-section-title d-flex justify-content-between align-items-center">
                            <span>Điều kiện áp dụng</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="edit_reset_dk">
                                <span class="simple-icon">↻ Reset</span>
                            </button>
                        </div>

                        <div class="alert alert-light border mb-3">
                            <div class="small text-muted mb-2">Chỉ chọn một trong hai điều kiện:</div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card condition-field" id="edit_condition_hoadon">
                                        <div class="card-body">
                                            <label class="form-label">Hóa đơn từ</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="dk_hoadon_tu"
                                                    id="edit_dk_hoadon_tu" min="500000" step="100000">
                                                <span class="input-group-text">VND</span>
                                            </div>
                                            <div class="form-text">Tối thiểu: 500,000 VND</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card condition-field" id="edit_condition_sodem">
                                        <div class="card-body">
                                            <label class="form-label">Số đêm từ</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="dk_sodem_tu"
                                                    id="edit_dk_sodem_tu" min="2" step="1">
                                                <span class="input-group-text">đêm</span>
                                            </div>
                                            <div class="form-text">Tối thiểu: 2 đêm</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần 4: Thời gian áp dụng -->
                    <div class="modal-section">
                        <div class="modal-section-title">Thời gian áp dụng</div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_batdau" id="edit_ngay_batdau" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_ketthuc" id="edit_ngay_ketthuc" required>
                            </div>
                        </div>
                    </div>

                    <!-- Phần 5: Hình ảnh -->
                    <div class="modal-section">
                        <div class="modal-section-title">Hình ảnh khuyến mãi</div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="image-preview-container">
                                    <img id="edit_image_preview" src=""
                                        class="image-preview">
                                    <div class="mb-2">
                                        <input type="file" class="form-control" name="hinh_anh" id="edit_hinh_anh" accept="image/*">
                                    </div>
                                    <div class="small text-muted">
                                        Để trống nếu không thay đổi ảnh
                                    </div>
                                </div>
                                <input type="hidden" name="current_image" id="edit_current_image">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật khuyến mãi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Thêm jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.select-item');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Delete multiple
        document.getElementById('deleteMultipleBtn').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const selectedIds = document.querySelectorAll('.select-item:checked');
            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một khuyến mãi để xóa!');
                return;
            }

            if (confirm(`Bạn có chắc muốn xóa ${selectedIds.length} khuyến mãi đã chọn?`)) {
                document.getElementById('deleteMultipleForm').submit();
            }
        });

        // Xử lý thay đổi loại giảm giá
        function handleLoaiGiamGiaChange(prefix) {
            const loaiGiamGia = document.getElementById(prefix + '_loai_giamgia');
            const donViGiamGia = document.getElementById(prefix + '_donvi_giamgia');
            const giamGiaToiDa = document.getElementById(prefix + '_giamgia_toida');

            function updateDisplay() {
                if (loaiGiamGia.value === 'phantram') {
                    donViGiamGia.textContent = '%';
                    giamGiaToiDa.parentElement.parentElement.style.display = 'block';
                } else {
                    donViGiamGia.textContent = 'VND';
                    giamGiaToiDa.parentElement.parentElement.style.display = 'none';
                    giamGiaToiDa.value = '0';
                }
            }

            loaiGiamGia.addEventListener('change', updateDisplay);
            updateDisplay();
        }

        handleLoaiGiamGiaChange('add');
        handleLoaiGiamGiaChange('edit');

        // Xử lý điều kiện BẮT BUỘC nhập một trong hai
        function handleConditionValidation(prefix) {
            const dkHoaDonEl = document.getElementById(prefix + '_dk_hoadon_tu');
            const dkSoDemEl = document.getElementById(prefix + '_dk_sodem_tu');
            const resetBtn = document.getElementById(prefix + '_reset_dk');
            const conditionHoaDon = document.getElementById(prefix + '_condition_hoadon');
            const conditionSoDem = document.getElementById(prefix + '_condition_sodem');
            const conditionError = document.getElementById(prefix + '_condition_error');

            // Kiểm tra xem có ít nhất một điều kiện không
            function checkCondition() {
                const hasHoaDon = dkHoaDonEl.value.trim() !== '';
                const hasSoDem = dkSoDemEl.value.trim() !== '';
                
                if (!hasHoaDon && !hasSoDem) {
                    // Không có điều kiện nào - hiển thị cảnh báo
                    if (conditionError) conditionError.classList.remove('d-none');
                    conditionHoaDon.classList.add('border-danger');
                    conditionSoDem.classList.add('border-danger');
                    return false;
                } else {
                    // Có ít nhất 1 điều kiện
                    if (conditionError) conditionError.classList.add('d-none');
                    conditionHoaDon.classList.remove('border-danger');
                    conditionSoDem.classList.remove('border-danger');
                    return true;
                }
            }

            // Reset điều kiện
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    dkHoaDonEl.value = '';
                    dkSoDemEl.value = '';
                    checkCondition();
                    updateConditionStyle();
                });
            }

            function updateConditionStyle() {
                // Xóa style cũ
                conditionHoaDon.classList.remove('condition-active', 'condition-disabled', 'border-primary', 'border-success');
                conditionSoDem.classList.remove('condition-active', 'condition-disabled', 'border-primary', 'border-success');

                if (dkHoaDonEl.value.trim() !== '') {
                    conditionHoaDon.classList.add('condition-active', 'border-primary');
                    conditionSoDem.classList.add('condition-disabled');
                } else if (dkSoDemEl.value.trim() !== '') {
                    conditionSoDem.classList.add('condition-active', 'border-success');
                    conditionHoaDon.classList.add('condition-disabled');
                }
            }

            // Khi nhập hóa đơn => xóa số đêm
            dkHoaDonEl.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    dkSoDemEl.value = '';
                }
                checkCondition();
                updateConditionStyle();
            });

            // Khi nhập số đêm => xóa hóa đơn
            dkSoDemEl.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    dkHoaDonEl.value = '';
                }
                checkCondition();
                updateConditionStyle();
            });

            // Kiểm tra ban đầu
            checkCondition();
            updateConditionStyle();
        }

        handleConditionValidation('add');
        handleConditionValidation('edit');

        // Xử lý edit modal
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const id = this.getAttribute('data-id');
                const ten = this.getAttribute('data-ten');
                const giamgia = this.getAttribute('data-giamgia');
                const batdau = this.getAttribute('data-batdau');
                const ketthuc = this.getAttribute('data-ketthuc');
                const mota = this.getAttribute('data-mota');
                const hinhanh = this.getAttribute('data-hinhanh');
                const loaigiamgia = this.getAttribute('data-loaigiamgia') || 'phantram';
                const dkhoadontu = this.getAttribute('data-dkhoadontu') || '';
                const dksodemtu = this.getAttribute('data-dksodemtu') || '';
                const giamgiatoida = this.getAttribute('data-giamgiatoida') || '0';

                // Set giá trị
                document.getElementById('edit_ma_km').value = id;
                document.getElementById('edit_ten_khuyenmai').value = ten;
                document.getElementById('edit_muc_giamgia').value = giamgia;
                document.getElementById('edit_ngay_batdau').value = batdau;
                document.getElementById('edit_ngay_ketthuc').value = ketthuc;
                document.getElementById('edit_mo_ta').value = mota || '';
                document.getElementById('edit_loai_giamgia').value = loaigiamgia;
                document.getElementById('edit_giamgia_toida').value = giamgiatoida;
                document.getElementById('edit_current_image').value = hinhanh || '';

                // Set điều kiện
                const dkHoaDonEl = document.getElementById('edit_dk_hoadon_tu');
                const dkSoDemEl = document.getElementById('edit_dk_sodem_tu');

                if (dkhoadontu !== '') {
                    dkHoaDonEl.value = dkhoadontu;
                    dkSoDemEl.value = '';
                } else if (dksodemtu !== '') {
                    dkSoDemEl.value = dksodemtu;
                    dkHoaDonEl.value = '';
                } else {
                    // Nếu không có điều kiện nào trong database
                    // Để trống, validation sẽ xử lý
                    dkHoaDonEl.value = '';
                    dkSoDemEl.value = '';
                }

                // Update display
                setTimeout(() => {
                    updateConditionStyleForEdit();
                    // Kiểm tra lại điều kiện
                    const conditionError = document.getElementById('edit_condition_error');
                    if (conditionError && !dkHoaDonEl.value && !dkSoDemEl.value) {
                        conditionError.classList.remove('d-none');
                    }
                }, 100);

                // Cập nhật display theo loại giảm giá
                const donViEl = document.getElementById('edit_donvi_giamgia');
                const giamGiaToiDaContainer = document.getElementById('edit_giamgia_toida').parentElement.parentElement;
                if (loaigiamgia === 'phantram') {
                    donViEl.textContent = '%';
                    giamGiaToiDaContainer.style.display = 'block';
                } else {
                    donViEl.textContent = 'VND';
                    giamGiaToiDaContainer.style.display = 'none';
                }

                // Set image preview
                if (hinhanh) {
                    document.getElementById('edit_image_preview').src = '../../../' + hinhanh;
                } else {
                    document.getElementById('edit_image_preview').src = '../../../client/assets/images/sales/default_promotion.png';
                }
            });
        });

        function updateConditionStyleForEdit() {
            const dkHoaDonEl = document.getElementById('edit_dk_hoadon_tu');
            const dkSoDemEl = document.getElementById('edit_dk_sodem_tu');
            const conditionHoaDon = document.getElementById('edit_condition_hoadon');
            const conditionSoDem = document.getElementById('edit_condition_sodem');

            conditionHoaDon.classList.remove('condition-active', 'condition-disabled', 'border-primary', 'border-success');
            conditionSoDem.classList.remove('condition-active', 'condition-disabled', 'border-primary', 'border-success');

            if (dkHoaDonEl.value.trim() !== '') {
                conditionHoaDon.classList.add('condition-active', 'border-primary');
                conditionSoDem.classList.add('condition-disabled');
            } else if (dkSoDemEl.value.trim() !== '') {
                conditionSoDem.classList.add('condition-active', 'border-success');
                conditionHoaDon.classList.add('condition-disabled');
            }
        }

        // Preview image
        document.getElementById('add_hinh_anh').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('add_image_preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('edit_hinh_anh').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit_image_preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Set min date và validation cho ngày
        const today = new Date().toISOString().split('T')[0];
        const addNgayBatDau = document.getElementById('add_ngay_batdau');
        const addNgayKetThuc = document.getElementById('add_ngay_ketthuc');
        const editNgayBatDau = document.getElementById('edit_ngay_batdau');
        const editNgayKetThuc = document.getElementById('edit_ngay_ketthuc');

        addNgayBatDau.min = today;
        addNgayKetThuc.min = today;
        editNgayBatDau.min = today;
        editNgayKetThuc.min = today;

        // Validation: Ngày kết thúc phải sau ngày bắt đầu
        function setupDateValidation(startId, endId) {
            const startEl = document.getElementById(startId);
            const endEl = document.getElementById(endId);

            function validateDates() {
                if (startEl.value && endEl.value) {
                    if (new Date(endEl.value) <= new Date(startEl.value)) {
                        endEl.setCustomValidity('Ngày kết thúc phải sau ngày bắt đầu');
                        return false;
                    } else {
                        endEl.setCustomValidity('');
                        return true;
                    }
                }
                return true;
            }

            startEl.addEventListener('change', validateDates);
            endEl.addEventListener('change', validateDates);
        }

        setupDateValidation('add_ngay_batdau', 'add_ngay_ketthuc');
        setupDateValidation('edit_ngay_batdau', 'edit_ngay_ketthuc');

        // Validation: Hóa đơn tối thiểu 500,000 VND
        function setupMinValidation(inputId, minValue, message) {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', function() {
                    if (this.value && parseFloat(this.value) < minValue) {
                        this.setCustomValidity(message);
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        }

        setupMinValidation('add_dk_hoadon_tu', 500000, 'Hóa đơn tối thiểu 500,000 VND');
        setupMinValidation('edit_dk_hoadon_tu', 500000, 'Hóa đơn tối thiểu 500,000 VND');
        setupMinValidation('add_dk_sodem_tu', 2, 'Số đêm tối thiểu 2');
        setupMinValidation('edit_dk_sodem_tu', 2, 'Số đêm tối thiểu 2');

        // Validation: Mức giảm giá tối đa không âm
        function setupNonNegativeValidation(inputId, message) {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', function() {
                    if (this.value && parseFloat(this.value) < 0) {
                        this.setCustomValidity(message);
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        }

        setupNonNegativeValidation('add_giamgia_toida', 'Giảm giá tối đa không được âm');
        setupNonNegativeValidation('edit_giamgia_toida', 'Giảm giá tối đa không được âm');
        setupNonNegativeValidation('add_muc_giamgia', 'Mức giảm giá không được âm');
        setupNonNegativeValidation('edit_muc_giamgia', 'Mức giảm giá không được âm');
    });

    // Validate form submit với điều kiện BẮT BUỘC
    function validateForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return true;

        const dkHoaDon = document.querySelector(`#${formId} [name="dk_hoadon_tu"]`);
        const dkSoDem = document.querySelector(`#${formId} [name="dk_sodem_tu"]`);

        // Kiểm tra chỉ được nhập một điều kiện
        if (dkHoaDon && dkSoDem && dkHoaDon.value.trim() !== '' && dkSoDem.value.trim() !== '') {
            alert('Chỉ được nhập một điều kiện: HOẶC hóa đơn từ, HOẶC số đêm từ!');
            return false;
        }

        // Kiểm tra BẮT BUỘC phải có ít nhất một điều kiện
        if (dkHoaDon && dkSoDem && dkHoaDon.value.trim() === '' && dkSoDem.value.trim() === '') {
            alert('Vui lòng nhập ít nhất một điều kiện áp dụng!');
            return false;
        }

        // Kiểm tra giá trị tối thiểu
        if (dkHoaDon && dkHoaDon.value.trim() !== '' && parseFloat(dkHoaDon.value) < 500000) {
            alert('Hóa đơn tối thiểu phải từ 500,000 VND!');
            return false;
        }

        if (dkSoDem && dkSoDem.value.trim() !== '' && parseInt(dkSoDem.value) < 2) {
            alert('Số đêm tối thiểu phải từ 2 đêm!');
            return false;
        }

        // Kiểm tra ngày
        const ngayBatDau = document.querySelector(`#${formId} [name="ngay_batdau"]`);
        const ngayKetThuc = document.querySelector(`#${formId} [name="ngay_ketthuc"]`);

        if (ngayBatDau && ngayKetThuc && ngayBatDau.value && ngayKetThuc.value) {
            if (new Date(ngayKetThuc.value) <= new Date(ngayBatDau.value)) {
                alert('Ngày kết thúc phải sau ngày bắt đầu!');
                return false;
            }
        }

        // Kiểm tra mức giảm giá
        const mucGiamGia = document.querySelector(`#${formId} [name="muc_giamgia"]`);
        if (mucGiamGia && mucGiamGia.value && parseFloat(mucGiamGia.value) <= 0) {
            alert('Mức giảm giá phải lớn hơn 0!');
            return false;
        }

        // Kiểm tra giảm giá tối đa
        const giamGiaToiDa = document.querySelector(`#${formId} [name="giamgia_toida"]`);
        if (giamGiaToiDa && giamGiaToiDa.value && parseFloat(giamGiaToiDa.value) < 0) {
            alert('Giảm giá tối đa không được âm!');
            return false;
        }

        return true;
    }

    // Event listener cho form submit
    document.addEventListener('submit', function(e) {
        let shouldSubmit = true;
        
        if (e.target.closest('form[action*="action=add"]')) {
            if (!validateForm('addKhuyenMaiModalForm')) {
                e.preventDefault();
                shouldSubmit = false;
            }
        }

        if (e.target.closest('form[action*="action=edit"]')) {
            if (!validateForm('editKhuyenMaiModalForm')) {
                e.preventDefault();
                shouldSubmit = false;
            }
        }

        // Nếu validation pass, có thể xử lý thêm
        if (shouldSubmit) {
            // Đảm bảo có ít nhất một điều kiện
            const form = e.target;
            const dkHoaDon = form.querySelector('[name="dk_hoadon_tu"]');
            const dkSoDem = form.querySelector('[name="dk_sodem_tu"]');
            
            // Nếu không có điều kiện nào, tự động set hóa đơn 500k
            if (dkHoaDon && dkSoDem && !dkHoaDon.value && !dkSoDem.value) {
                dkHoaDon.value = '500000';
            }
            
            // Nếu có cả hai điều kiện, ưu tiên giữ hóa đơn
            if (dkHoaDon && dkSoDem && dkHoaDon.value && dkSoDem.value) {
                dkSoDem.value = '';
                console.log('Đã tự động xóa điều kiện số đêm để giữ điều kiện hóa đơn');
            }
        }
    });

    // Reset form khi modal đóng
    document.addEventListener('hidden.bs.modal', function(e) {
        if (e.target.id === 'addKhuyenMaiModal') {
            // Reset các validation message
            const form = document.getElementById('addKhuyenMaiModalForm');
            if (form) {
                form.reset();
                // Reset image preview
                document.getElementById('add_image_preview').src = '../../../client/assets/images/sales/default_promotion.png';
                // Reset điều kiện
                const conditionError = document.getElementById('add_condition_error');
                if (conditionError) conditionError.classList.add('d-none');
                const conditionHoaDon = document.getElementById('add_condition_hoadon');
                const conditionSoDem = document.getElementById('add_condition_sodem');
                if (conditionHoaDon) conditionHoaDon.classList.remove('border-danger', 'condition-active', 'condition-disabled', 'border-primary', 'border-success');
                if (conditionSoDem) conditionSoDem.classList.remove('border-danger', 'condition-active', 'condition-disabled', 'border-primary', 'border-success');
            }
        }
    });
</script>
<?php include_once '../layouts/footer.php'; ?>