<?php
$pageTitle = "Quản lý khuyến mãi - ABC Resort";
include_once 'layouts/header.php';

// TỰ LẤY DỮ LIỆU TRONG VIEW ĐỂ ĐẢM BẢO LUÔN CÓ
require_once '../model/KhuyenMaiModel.php';
$khuyenMaiModel = new KhuyenMaiModel();
$khuyenMais = $khuyenMaiModel->getAllKhuyenMai();
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
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form xóa nhiều -->
        <form id="deleteMultipleForm" action="../controller/KhuyenMaiController.php?action=deleteMultiple" method="POST">
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
                                <th>Mức giảm giá</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Người tạo</th>
                                <th>Trạng thái</th>
                                <th width="120">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($khuyenMais && $khuyenMais->num_rows > 0): ?>
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
                                            <span class="badge bg-success"><?php echo $km['MucGiamGia']; ?>%</span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($km['NgayBatDau'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($km['NgayKetThuc'])); ?></td>
                                        <td><?php echo $km['TenNhanVienTao'] ?? 'N/A'; ?></td>
                                        <td>
                                            <?php 
                                            $today = date('Y-m-d');
                                            if ($km['NgayBatDau'] <= $today && $km['NgayKetThuc'] >= $today) {
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
                                                    title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="../controller/KhuyenMaiController.php?action=delete&id=<?php echo $km['MaKM']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Bạn có chắc muốn xóa khuyến mãi này?')"
                                               title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
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
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Thêm khuyến mãi -->
<div class="modal fade" id="addKhuyenMaiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../controller/KhuyenMaiController.php?action=add" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm khuyến mãi mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                                <input type="date" class="form-control" name="ngay_batdau" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_ketthuc" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="mo_ta" rows="3" placeholder="Mô tả về khuyến mãi..."></textarea>
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

<!-- Modal Sửa khuyến mãi -->
<div class="modal fade" id="editKhuyenMaiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../controller/KhuyenMaiController.php?action=edit" method="POST">
                <input type="hidden" name="ma_km" id="edit_ma_km">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
            
            console.log('Data:', {id, ten, giamgia, batdau, ketthuc});
            
            document.getElementById('edit_ma_km').value = id;
            document.getElementById('edit_ten_khuyenmai').value = ten;
            document.getElementById('edit_muc_giamgia').value = giamgia;
            document.getElementById('edit_ngay_batdau').value = batdau;
            document.getElementById('edit_ngay_ketthuc').value = ketthuc;
            document.getElementById('edit_mo_ta').value = mota || '';
        });
    });
});

// Debug: Kiểm tra xem JavaScript có chạy không
console.log('JavaScript loaded for khuyenmai.php');
</script>

<?php include_once 'layouts/footer.php'; ?>