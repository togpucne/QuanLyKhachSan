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
                                <th width="50">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th width="60">STT</th>
                                <th>Mã KM</th>
                                <th>Tên khuyến mãi</th>
                                <th>Ảnh</th>
                                <th>Mức giảm giá</th>
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

<!-- Modal Thêm khuyến mãi -->
<div class="modal fade" id="addKhuyenMaiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- SỬA THÀNH: khuyenmaiController.php -->
            <form action="../../controller/khuyenmaiController.php?action=add" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm khuyến mãi mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ten_khuyenmai" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mức giảm giá (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="muc_giamgia" min="1" max="100" step="0.01" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="ngay_batdau" id="add_ngay_batdau" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="ngay_ketthuc" id="add_ngay_ketthuc" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" name="mo_ta" rows="3" placeholder="Mô tả về khuyến mãi..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Ảnh khuyến mãi</label>
                                <div class="border rounded p-3 text-center mb-2">
                                    <img id="add_image_preview" src="../../../client/assets/images/sales/default_promotion.png"
                                        class="img-fluid rounded mb-2"
                                        style="max-height: 150px; object-fit: cover;">
                                    <input type="file" class="form-control" name="hinh_anh" id="add_hinh_anh" accept="image/*">
                                    <small class="text-muted d-block mt-2">Định dạng: JPEG, PNG, GIF, WebP<br>Kích thước tối đa: 5MB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm khuyến mãi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Sửa khuyến mãi (CHỈ GIỮ 1 CÁI) -->
<div class="modal fade" id="editKhuyenMaiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- SỬA THÀNH: ../../controller/khuyenmaiController.php -->
            <form action="../../controller/khuyenmaiController.php?action=edit" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="ma_km" id="edit_ma_km">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ten_khuyenmai" id="edit_ten_khuyenmai" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mức giảm giá (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="muc_giamgia" id="edit_muc_giamgia" min="1" max="100" step="0.01" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="ngay_batdau" id="edit_ngay_batdau" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="ngay_ketthuc" id="edit_ngay_ketthuc" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" name="mo_ta" id="edit_mo_ta" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Ảnh khuyến mãi</label>
                                <div class="border rounded p-3 text-center mb-2">
                                    <img id="edit_image_preview" src=""
                                        class="img-fluid rounded mb-2"
                                        style="max-height: 150px; object-fit: cover;">
                                    <input type="file" class="form-control" name="hinh_anh" id="edit_hinh_anh" accept="image/*">
                                    <small class="text-muted d-block mt-2">Để trống nếu không thay đổi ảnh</small>
                                </div>
                                <input type="hidden" name="current_image" id="edit_current_image">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Thêm jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Thêm jQuery
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.select-item');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Delete multiple - THÊM EVENT.PREVENTDEFAULT()
        document.getElementById('deleteMultipleBtn').addEventListener('click', function(e) {
            e.preventDefault(); // QUAN TRỌNG: Ngăn không cho form submit
            e.stopPropagation(); // Ngăn event bubbling

            const selectedIds = document.querySelectorAll('.select-item:checked');
            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một khuyến mãi để xóa!');
                return;
            }

            if (confirm(`Bạn có chắc muốn xóa ${selectedIds.length} khuyến mãi đã chọn?`)) {
                document.getElementById('deleteMultipleForm').submit();
            }
        });

        // Edit modal data - THÊM EVENT.PREVENTDEFAULT()
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // QUAN TRỌNG: Ngăn không cho form submit
                e.stopPropagation(); // Ngăn event bubbling

                console.log('Edit button clicked!');

                const id = this.getAttribute('data-id');
                const ten = this.getAttribute('data-ten');
                const giamgia = this.getAttribute('data-giamgia');
                const batdau = this.getAttribute('data-batdau');
                const ketthuc = this.getAttribute('data-ketthuc');
                const mota = this.getAttribute('data-mota');

                console.log('Data:', {
                    id,
                    ten,
                    giamgia,
                    batdau,
                    ketthuc
                });

                document.getElementById('edit_ma_km').value = id;
                document.getElementById('edit_ten_khuyenmai').value = ten;
                document.getElementById('edit_muc_giamgia').value = giamgia;
                document.getElementById('edit_ngay_batdau').value = batdau;
                document.getElementById('edit_ngay_ketthuc').value = ketthuc;
                document.getElementById('edit_mo_ta').value = mota || '';
            });
        });
    });
    // Preview image for add modal
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

    // Preview image for edit modal
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

    // Edit modal data
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

            document.getElementById('edit_ma_km').value = id;
            document.getElementById('edit_ten_khuyenmai').value = ten;
            document.getElementById('edit_muc_giamgia').value = giamgia;
            document.getElementById('edit_ngay_batdau').value = batdau;
            document.getElementById('edit_ngay_ketthuc').value = ketthuc;
            document.getElementById('edit_mo_ta').value = mota || '';
            document.getElementById('edit_current_image').value = hinhanh || '';

            // Set image preview
            if (hinhanh) {
                document.getElementById('edit_image_preview').src = '../../../' + hinhanh;
            } else {
                document.getElementById('edit_image_preview').src = '../../../client/assets/images/sales/default_promotion.png';
            }
        });
    });

    // Set min date for date inputs
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('add_ngay_batdau').min = today;
    document.getElementById('add_ngay_ketthuc').min = today;

    // Debug: Kiểm tra xem JavaScript có chạy không
    console.log('JavaScript loaded for khuyenmai.php');
</script>

<?php include_once '../layouts/footer.php'; ?>