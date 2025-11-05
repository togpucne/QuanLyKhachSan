<?php
// VIEW TỰ REQUIRED MODEL VÀ LẤY DỮ LIỆU
require_once '../../model/DichVuModel.php';

// Tạo instance của Model
$dichVuModel = new DichVuModel();

// XỬ LÝ CÁC ACTION
$action = $_POST['action'] ?? '';

// THÊM DỊCH VỤ
if ($action === 'themDichVu' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenDV = $_POST['tenDV'] ?? '';
    $donGia = $_POST['donGia'] ?? 0;
    $donViTinh = $_POST['donViTinh'] ?? 'Lần';
    $moTa = $_POST['moTa'] ?? '';
    $loaiDV = $_POST['loaiDV'] ?? '';
    
    if ($dichVuModel->themDichVu($tenDV, $donGia, $donViTinh, $moTa, $loaiDV)) {
        echo "<script>alert('Thêm dịch vụ thành công!'); window.location.href = 'quanlydichvu.php';</script>";
    } else {
        echo "<script>alert('Thêm dịch vụ thất bại!');</script>";
    }
}

// CẬP NHẬT DỊCH VỤ
if ($action === 'capNhatDichVu' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $maDV = $_POST['maDV'] ?? '';
    $tenDV = $_POST['tenDV'] ?? '';
    $donGia = $_POST['donGia'] ?? 0;
    $donViTinh = $_POST['donViTinh'] ?? 'Lần';
    $moTa = $_POST['moTa'] ?? '';
    $loaiDV = $_POST['loaiDV'] ?? '';
    $trangThai = $_POST['trangThai'] ?? 'Khả dụng';
    
    if ($dichVuModel->capNhatDichVu($maDV, $tenDV, $donGia, $donViTinh, $moTa, $loaiDV, $trangThai)) {
        echo "<script>alert('Cập nhật dịch vụ thành công!'); window.location.href = 'quanlydichvu.php';</script>";
    } else {
        echo "<script>alert('Cập nhật dịch vụ thất bại!');</script>";
    }
}

// XÓA DỊCH VỤ
if ($action === 'xoaDichVu' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $maDV = $_POST['maDV'] ?? '';
    
    if ($dichVuModel->xoaDichVu($maDV)) {
        echo "<script>alert('Xóa dịch vụ thành công!'); window.location.href = 'quanlydichvu.php';</script>";
    } else {
        echo "<script>alert('Xóa dịch vụ thất bại!');</script>";
    }
}

// TÌM KIẾM DỊCH VỤ
if ($action === 'timKiem' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = $_POST['keyword'] ?? '';
    $dsDichVu = $dichVuModel->timKiemDichVu($keyword);
} else {
    // Lấy dữ liệu trực tiếp từ Model
    $dsDichVu = $dichVuModel->getDanhSachDichVu();
}

// Lấy loại dịch vụ cho dropdown
$loaiDichVu = $dichVuModel->getLoaiDichVu();

// Đảm bảo là mảng
$dsDichVu = $dsDichVu ?? [];
$loaiDichVu = $loaiDichVu ?? [];
?>

<?php include '../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">Quản Lý Dịch Vụ</h1>

            <!-- Thanh công cụ -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalThemDichVu">
                                <i class="fas fa-plus-circle"></i> Thêm Dịch Vụ Mới
                            </button>
                        </div>
                        <div class="col-md-6">
                            <form id="formSearch" method="POST" class="d-flex">
                                <input type="hidden" name="action" value="timKiem">
                                <input type="text" class="form-control" name="keyword" id="searchKeyword" 
                                       placeholder="Tìm kiếm dịch vụ..." value="<?php echo $_POST['keyword'] ?? ''; ?>">
                                <button type="submit" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if (isset($_POST['keyword']) && !empty($_POST['keyword'])): ?>
                                    <a href="quanlydichvu.php" class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách dịch vụ -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách dịch vụ (<span id="serviceCount"><?php echo count($dsDichVu); ?></span> dịch vụ)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableDichVu">
                            <thead class="table-dark">
                                <tr>
                                    <th width="60px">Mã DV</th>
                                    <th>Tên Dịch Vụ</th>
                                    <th width="120px">Đơn Giá</th>
                                    <th width="100px">Đơn Vị</th>
                                    <th>Loại DV</th>
                                    <th width="100px">Trạng Thái</th>
                                    <th width="120px" class="text-center">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($dsDichVu) > 0): ?>
                                    <?php foreach ($dsDichVu as $dv): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($dv['MaDV']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($dv['TenDV']); ?></strong>
                                                <?php if (!empty($dv['MoTa'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($dv['MoTa']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end"><?php echo number_format($dv['DonGia']); ?> đ</td>
                                            <td class="text-center"><?php echo htmlspecialchars($dv['DonViTinh']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($dv['LoaiDV']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $dv['TrangThai'] == 'Khả dụng' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo htmlspecialchars($dv['TrangThai']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary btn-edit" 
                                                            data-madv="<?php echo $dv['MaDV']; ?>"
                                                            data-tendv="<?php echo $dv['TenDV']; ?>"
                                                            data-dongia="<?php echo $dv['DonGia']; ?>"
                                                            data-donvitinh="<?php echo $dv['DonViTinh']; ?>"
                                                            data-mota="<?php echo $dv['MoTa']; ?>"
                                                            data-loaidv="<?php echo $dv['LoaiDV']; ?>"
                                                            data-trangthai="<?php echo $dv['TrangThai']; ?>"
                                                            title="Sửa dịch vụ">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-delete" 
                                                            data-madv="<?php echo $dv['MaDV']; ?>"
                                                            data-tendv="<?php echo $dv['TenDV']; ?>"
                                                            title="Xóa dịch vụ">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-box-open fa-2x mb-3"></i><br>
                                            <?php echo (isset($_POST['keyword']) && !empty($_POST['keyword'])) ? 
                                                'Không tìm thấy dịch vụ phù hợp' : 
                                                'Chưa có dịch vụ nào trong hệ thống'; ?>
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
</div>

<!-- Modal Thêm Dịch Vụ -->
<div class="modal fade" id="modalThemDichVu" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm Dịch Vụ Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formThemDichVu">
                <input type="hidden" name="action" value="themDichVu">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tenDV" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Loại dịch vụ <span class="text-danger">*</span></label>
                                <select class="form-select" name="loaiDV" required>
                                    <option value="">Chọn loại dịch vụ</option>
                                    <?php foreach ($loaiDichVu as $loai): ?>
                                        <option value="<?php echo htmlspecialchars($loai); ?>"><?php echo htmlspecialchars($loai); ?></option>
                                    <?php endforeach; ?>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Đơn giá <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="donGia" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Đơn vị tính</label>
                                <select class="form-select" name="donViTinh">
                                    <option value="Lần">Lần</option>
                                    <option value="Giờ">Giờ</option>
                                    <option value="Ngày">Ngày</option>
                                    <option value="Người">Người</option>
                                    <option value="Phần">Phần</option>
                                    <option value="Kg">Kg</option>
                                    <option value="Lượt">Lượt</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="trangThai">
                                    <option value="Khả dụng">Khả dụng</option>
                                    <option value="Ngừng cung cấp">Ngừng cung cấp</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả dịch vụ</label>
                        <textarea class="form-control" name="moTa" rows="3" placeholder="Mô tả chi tiết về dịch vụ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm Dịch Vụ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Dịch Vụ -->
<div class="modal fade" id="modalSuaDichVu" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa Thông Tin Dịch Vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formSuaDichVu">
                <input type="hidden" name="action" value="capNhatDichVu">
                <input type="hidden" name="maDV" id="editMaDV">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên dịch vụ</label>
                                <input type="text" class="form-control" name="tenDV" id="editTenDV" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Loại dịch vụ</label>
                                <select class="form-select" name="loaiDV" id="editLoaiDV" required>
                                    <option value="">Chọn loại dịch vụ</option>
                                    <?php foreach ($loaiDichVu as $loai): ?>
                                        <option value="<?php echo htmlspecialchars($loai); ?>"><?php echo htmlspecialchars($loai); ?></option>
                                    <?php endforeach; ?>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Đơn giá</label>
                                <input type="number" class="form-control" name="donGia" id="editDonGia" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Đơn vị tính</label>
                                <select class="form-select" name="donViTinh" id="editDonViTinh">
                                    <option value="Lần">Lần</option>
                                    <option value="Giờ">Giờ</option>
                                    <option value="Ngày">Ngày</option>
                                    <option value="Người">Người</option>
                                    <option value="Phần">Phần</option>
                                    <option value="Kg">Kg</option>
                                    <option value="Lượt">Lượt</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="trangThai" id="editTrangThai">
                                    <option value="Khả dụng">Khả dụng</option>
                                    <option value="Ngừng cung cấp">Ngừng cung cấp</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả dịch vụ</label>
                        <textarea class="form-control" name="moTa" id="editMoTa" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xác nhận xóa -->
<div class="modal fade" id="modalXoaDichVu" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formXoaDichVu">
                <input type="hidden" name="action" value="xoaDichVu">
                <input type="hidden" name="maDV" id="deleteMaDV">
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa dịch vụ <strong id="tenDichVuXoa"></strong>?</p>
                    <p class="text-danger"><small>Hành động này không thể hoàn tác! Dịch vụ sẽ chuyển sang trạng thái "Ngừng cung cấp".</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Sửa dịch vụ - mở modal
    $('.btn-edit').click(function() {
        const maDV = $(this).data('madv');
        const tenDV = $(this).data('tendv');
        const donGia = $(this).data('dongia');
        const donViTinh = $(this).data('donvitinh');
        const moTa = $(this).data('mota');
        const loaiDV = $(this).data('loaidv');
        const trangThai = $(this).data('trangthai');

        $('#editMaDV').val(maDV);
        $('#editTenDV').val(tenDV);
        $('#editDonGia').val(donGia);
        $('#editDonViTinh').val(donViTinh);
        $('#editMoTa').val(moTa);
        $('#editLoaiDV').val(loaiDV);
        $('#editTrangThai').val(trangThai);

        $('#modalSuaDichVu').modal('show');
    });

    // Xóa dịch vụ - mở modal xác nhận
    $('.btn-delete').click(function() {
        const maDV = $(this).data('madv');
        const tenDV = $(this).data('tendv');

        $('#deleteMaDV').val(maDV);
        $('#tenDichVuXoa').text(tenDV);
        $('#modalXoaDichVu').modal('show');
    });

    // Tìm kiếm với Enter
    $('#searchKeyword').on('keypress', function(e) {
        if (e.which === 13) {
            $('#formSearch').submit();
        }
    });

    // Reset form khi modal đóng
    $('#modalThemDichVu').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

    $('#modalSuaDichVu').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
});
</script>

<?php include '../layouts/footer.php'; ?>