<?php
session_start();

// Kiểm tra quyền truy cập 
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}

// GỌI MODEL
include_once '../../model/quanlythietbi.model.php';
$model = new QuanLyThietBiModel();

// LẤY DANH SÁCH PHÒNG
$danhSachPhong = $model->getDanhSachPhong();

// LẤY DANH SÁCH LOẠI THIẾT BỊ
$dsLoaiThietBi = $model->getDanhSachLoaiThietBi();
// XÓA LOẠI THIẾT BỊ TỪ DANH SÁCH CHECKBOX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'xoa_loai_thiet_bi_tu_danh_sach') {
    $tenThietBi = trim($_POST['ten_thiet_bi_xoa'] ?? '');

    if ($tenThietBi) {
        $result = $model->xoaLoaiThietBi($tenThietBi);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            // Làm mới danh sách thiết bị
            $dsLoaiThietBi = $model->getDanhSachLoaiThietBi();
        } else {
            $_SESSION['error'] = $result['error'];
        }
    } else {
        $_SESSION['error'] = "Tên thiết bị không hợp lệ";
    }

    header('Location: quanlythietbi.php');
    exit();
}
// THÊM THIẾT BỊ VÀO PHÒNG (CÓ KIỂM TRA TRÙNG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'them_vao_phong') {
    $maPhong = $_POST['ma_phong'] ?? 0;
    $thietBiDuocChon = $_POST['thiet_bi'] ?? [];
    $_POST['so_luong'] = 1;
    $soLuong = $_POST['so_luong'] ?? 1;
    $tinhTrang = $_POST['tinh_trang'] ?? 'Tốt';

    if ($maPhong && !empty($thietBiDuocChon)) {
        $thanhCong = 0;
        $thatBai = 0;
        $thongBaoChiTiet = [];

        foreach ($thietBiDuocChon as $tenThietBi) {
            $result = $model->themThietBiVaoPhong($maPhong, $tenThietBi, $tinhTrang, $soLuong);

            if ($result['success']) {
                $thanhCong++;
                $thongBaoChiTiet[] = "✓ {$result['message']}";
            } else {
                $thatBai++;
                $thongBaoChiTiet[] = "✗ {$result['error']}";
            }
        }

        if ($thanhCong > 0) {
            $_SESSION['success'] = "Đã thêm thành công {$thanhCong} loại thiết bị";
            if ($thatBai > 0) {
                $_SESSION['warning'] = "Có {$thatBai} loại thiết bị thất bại:<br>" . implode("<br>", $thongBaoChiTiet);
            }
        } else {
            $_SESSION['error'] = "Không thể thêm thiết bị nào vào phòng";
        }
    } else {
        $_SESSION['error'] = "Vui lòng chọn phòng và thiết bị";
    }

    header('Location: quanlythietbi.php');
    exit();
}

// THÊM THIẾT BỊ MỚI VÀO PHÒNG LUÔN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'them_thiet_bi_moi_vao_phong') {
    $maPhong = $_POST['ma_phong_thiet_bi_moi'] ?? 0;
    $tenThietBi = trim($_POST['ten_thiet_bi_moi'] ?? '');
    $_POST['so_luong_thiet_bi_moi'] = 1;
    $soLuong = $_POST['so_luong_thiet_bi_moi'] ?? 1;
    $tinhTrang = $_POST['tinh_trang_thiet_bi_moi'] ?? 'Tốt';

    if (!$maPhong) {
        $_SESSION['error'] = "Vui lòng chọn phòng";
        header('Location: quanlythietbi.php');
        exit();
    }

    if (!$tenThietBi) {
        $_SESSION['error'] = "Vui lòng nhập tên thiết bị mới";
        header('Location: quanlythietbi.php');
        exit();
    }

    // Sử dụng phương thức mới từ model
    $result = $model->themThietBiMoiVaoPhong($maPhong, $tenThietBi, $tinhTrang, $soLuong);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        // Làm mới danh sách thiết bị
        $dsLoaiThietBi = $model->getDanhSachLoaiThietBi();
    } else {
        $_SESSION['error'] = $result['error'];
    }

    header('Location: quanlythietbi.php');
    exit();
}

// THÊM THIẾT BỊ MỚI VÀO HỆ THỐNG (KHÔNG GÁN PHÒNG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'them_thiet_bi_moi_he_thong') {
    $tenThietBi = trim($_POST['ten_thiet_bi_moi_he_thong'] ?? '');

    if ($tenThietBi) {
        $result = $model->themThietBiMoi($tenThietBi);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            // Làm mới danh sách thiết bị
            $dsLoaiThietBi = $model->getDanhSachLoaiThietBi();
        } else {
            $_SESSION['error'] = $result['error'];
        }
    } else {
        $_SESSION['error'] = "Vui lòng nhập tên thiết bị";
    }

    header('Location: quanlythietbi.php');
    exit();
}
// XÓA THIẾT BỊ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'xoa_thiet_bi') {
    $maThietBi = $_POST['ma_thiet_bi'] ?? 0;

    if ($maThietBi) {
        $result = $model->xoaThietBi($maThietBi);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
    } else {
        $_SESSION['error'] = "Mã thiết bị không hợp lệ";
    }

    header('Location: quanlythietbi.php');
    exit();
}

// CHUYỂN THIẾT BỊ (SỬA LẠI)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'chuyen_thiet_bi') {
    $maThietBi = $_POST['ma_thiet_bi'] ?? 0;
    $maPhongMoi = $_POST['ma_phong_moi'] ?? 0;

    if ($maThietBi && $maPhongMoi) {
        $result = $model->chuyenThietBi($maThietBi, $maPhongMoi);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
    } else {
        $_SESSION['error'] = "Vui lòng chọn thiết bị và phòng mới";
    }

    header('Location: quanlythietbi.php');
    exit();
}

// LẤY THÔNG TIN THIẾT BỊ CHO AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'lay_thong_tin') {
    $maPhong = $_GET['ma_phong'] ?? 0;

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    if ($maPhong) {
        $thietBiPhong = $model->getThietBiByPhong($maPhong);
        echo json_encode($thietBiPhong, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([], JSON_UNESCAPED_UNICODE);
    }
    exit();
}

// LẤY THỐNG KÊ
$thongKe = $model->getThongKe();
?>

<!DOCTYPE html>
<html lang="vi">


<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center py-4">
        <div>
            <h1 class="h3 mb-1">Quản Lý Thiết Bị Phòng</h1>
            <p class="text-muted">Thêm/xóa thiết bị cho từng phòng</p>
        </div>
    </div>

    <!-- Thống kê -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Tổng thiết bị</h6>
                    <h3 class="mb-0"><?php echo $thongKe['tong_thiet_bi'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Số phòng có thiết bị</h6>
                    <h3 class="mb-0"><?php echo $thongKe['so_phong_co_thiet_bi'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Số loại thiết bị</h6>
                    <h3 class="mb-0"><?php echo $thongKe['so_loai_thiet_bi'] ?? count($dsLoaiThietBi); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <?php echo $_SESSION['warning'];
            unset($_SESSION['warning']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- Form thêm thiết bị -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Thêm thiết bị vào phòng</h5>
        </div>
        <div class="card-body">
            <!-- Form 1: Chọn từ thiết bị có sẵn -->
            <form method="POST" action="quanlythietbi.php" class="mb-4">
                <input type="hidden" name="action" value="them_vao_phong">

                <h6 class="mb-3">1. Chọn từ thiết bị có sẵn</h6>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Chọn phòng</label>
                        <select class="form-control" name="ma_phong" required>
                            <option value="">-- Chọn phòng --</option>
                            <?php foreach ($danhSachPhong as $phong): ?>
                                <option value="<?php echo $phong['MaPhong']; ?>">
                                    <?php echo htmlspecialchars($phong['SoPhong'] . ' - ' . $phong['roomName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Số lượng mỗi loại</label>
                        <input type="number" class="form-control" name="so_luong" value="1" min="1" max="10" >
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tình trạng</label>
                        <select class="form-control" name="tinh_trang">
                            <option value="Tốt">Tốt</option>
                            <option value="Hỏng">Hỏng</option>
                            <option value="Đang sửa chữa">Đang sửa</option>
                        </select>
                    </div>
                </div>


                <!-- Danh sách thiết bị để tích chọn (phiên bản đơn giản) -->
                <div class="mb-4">
                    <label class="form-label">Chọn thiết bị (tích vào ô để chọn)</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        <?php if (empty($dsLoaiThietBi)): ?>
                            <p class="text-muted mb-0">Chưa có thiết bị nào trong hệ thống</p>
                        <?php else: ?>
                            <?php foreach ($dsLoaiThietBi as $thietbi): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="thiet_bi[]" value="<?php echo htmlspecialchars($thietbi); ?>"
                                            id="tb_<?php echo md5($thietbi); ?>">
                                        <label class="form-check-label" for="tb_<?php echo md5($thietbi); ?>">
                                            <?php echo htmlspecialchars($thietbi); ?>
                                        </label>
                                    </div>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-sm"
                                        onclick="xoaThietBiTuDanhSach('<?php echo htmlspecialchars($thietbi); ?>')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Nút chọn/bỏ chọn tất cả -->
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="chonTatCa()">
                        <i class="bi bi-check-square"></i> Chọn tất cả
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="boChonTatCa()">
                        <i class="bi bi-square"></i> Bỏ chọn tất cả
                    </button>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Thêm thiết bị đã chọn
                </button>
            </form>

            <hr class="my-4">

            <!-- Form 2: Thêm thiết bị mới vào phòng luôn -->
            <form method="POST" action="quanlythietbi.php" class="mb-4">
                <input type="hidden" name="action" value="them_thiet_bi_moi_vao_phong">

                <h6 class="mb-3">2. Thêm thiết bị mới vào phòng</h6>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Chọn phòng</label>
                        <select class="form-control" name="ma_phong_thiet_bi_moi" required>
                            <option value="">-- Chọn phòng --</option>
                            <?php foreach ($danhSachPhong as $phong): ?>
                                <option value="<?php echo $phong['MaPhong']; ?>">
                                    <?php echo htmlspecialchars($phong['SoPhong'] . ' - ' . $phong['roomName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tên thiết bị mới</label>
                        <input type="text" class="form-control" name="ten_thiet_bi_moi" required
                            placeholder="Nhập tên thiết bị mới">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Số lượng</label>
                        <input type="number" class="form-control" name="so_luong_thiet_bi_moi" 
                            value="1" min="1" max="10">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tình trạng</label>
                        <select class="form-control" name="tinh_trang_thiet_bi_moi">
                            <option value="Tốt">Tốt</option>
                            <option value="Hỏng">Hỏng</option>
                            <option value="Đang sửa chữa">Đang sửa</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-plus-circle-dotted me-1"></i> Thêm thiết bị mới
                </button>
            </form>

            <hr class="my-4">

            <!-- Form 3: Chỉ thêm thiết bị mới vào hệ thống (không gán phòng) -->
            <form method="POST" action="quanlythietbi.php">
                <input type="hidden" name="action" value="them_thiet_bi_moi_he_thong">

                <h6 class="mb-3">3. Thêm thiết bị mới vào hệ thống (chưa gán phòng)</h6>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Tên thiết bị mới</label>
                        <input type="text" class="form-control" name="ten_thiet_bi_moi_he_thong"
                            placeholder="Nhập tên thiết bị mới để thêm vào danh sách">
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-plus-lg me-1"></i> Thêm vào danh sách
                        </button>
                    </div>
                </div>

                <small class="text-muted">
                    Thiết bị này sẽ được thêm vào danh sách chung, sau đó có thể tích chọn để thêm vào phòng.
                </small>
            </form>
        </div>
    </div>

    <!-- Danh sách phòng -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Danh sách phòng</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="ps-4">STT</th>
                            <th>Số Phòng</th>
                            <th>Tên Phòng</th>
                            <th>Trạng Thái</th>
                            <th>Loại Thiết Bị</th>
                            <th class="text-center">Xem Thiết Bị</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachPhong)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Không có dữ liệu phòng
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $stt = 1; ?>
                            <?php foreach ($danhSachPhong as $phong): ?>
                                <tr>
                                    <td class="ps-4"><?php echo $stt++; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($phong['SoPhong']); ?></td>
                                    <td><?php echo htmlspecialchars($phong['roomName']); ?></td>
                                    <td>
                                        <?php if ($phong['TrangThai'] === 'Trống'): ?>
                                            <span class="badge bg-success">Trống</span>
                                        <?php elseif ($phong['TrangThai'] === 'Đang sử dụng'): ?>
                                            <span class="badge bg-warning">Đang sử dụng</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bảo trì</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $phong['so_thiet_bi']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="xemThietBiPhong(<?php echo $phong['MaPhong']; ?>, '<?php echo htmlspecialchars($phong['SoPhong']); ?>')">
                                            <i class="bi bi-eye"></i> Xem
                                        </button>
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

<!-- Modal xem thiết bị của phòng -->
<div class="modal fade" id="modalThietBiPhong" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Đang tải dữ liệu...</p>
                </div>
                <div id="thietBiContent" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Xóa thiết bị khỏi danh sách checkbox
    function xoaThietBiTuDanhSach(tenThietBi) {
        if (confirm(`Bạn có chắc muốn xóa thiết bị "${tenThietBi}" khỏi hệ thống?\n\nThao tác này sẽ xóa tất cả thiết bị "${tenThietBi}" (kể cả chưa gán phòng).`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'quanlythietbi.php';

            const inputAction = document.createElement('input');
            inputAction.type = 'hidden';
            inputAction.name = 'action';
            inputAction.value = 'xoa_loai_thiet_bi_tu_danh_sach';
            form.appendChild(inputAction);

            const inputTenTB = document.createElement('input');
            inputTenTB.type = 'hidden';
            inputTenTB.name = 'ten_thiet_bi_xoa';
            inputTenTB.value = tenThietBi;
            form.appendChild(inputTenTB);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // Chọn tất cả checkbox
    function chonTatCa() {
        const checkboxes = document.querySelectorAll('input[name="thiet_bi[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    }

    // Bỏ chọn tất cả checkbox
    function boChonTatCa() {
        const checkboxes = document.querySelectorAll('input[name="thiet_bi[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    // Tìm kiếm trong danh sách thiết bị
    function timKiemThietBiTrongDanhSach() {
        const input = document.getElementById('timKiemThietBiInput');
        const filter = input.value.toUpperCase();
        const container = document.getElementById('danhSachThietBiContainer');
        const items = container.getElementsByClassName('col-md-6');

        for (let i = 0; i < items.length; i++) {
            const label = items[i].getElementsByTagName('label')[0];
            if (label) {
                const txtValue = label.textContent || label.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    items[i].style.display = '';
                } else {
                    items[i].style.display = 'none';
                }
            }
        }
    }

    function xemThietBiPhong(maPhong, soPhong) {
        const modal = new bootstrap.Modal(document.getElementById('modalThietBiPhong'));
        const title = document.getElementById('modalTitle');
        const loading = document.getElementById('loading');
        const content = document.getElementById('thietBiContent');

        title.textContent = `Thiết bị phòng ${soPhong}`;
        loading.style.display = 'block';
        content.style.display = 'none';
        content.innerHTML = '';

        modal.show();

        // Gọi AJAX
        fetch(`quanlythietbi.php?action=lay_thong_tin&ma_phong=${maPhong}`)
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                content.style.display = 'block';

                if (data.length === 0) {
                    content.innerHTML = '<p class="text-muted">Phòng chưa có thiết bị nào</p>';
                    return;
                }

                let html = `
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên Thiết Bị</th>
                                    <th>Tình Trạng</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                data.forEach((item, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.TenThietBi}</td>
                            <td>
                                ${item.TinhTrang === 'Tốt' ? 
                                    '<span class="badge bg-success">Tốt</span>' : 
                                 item.TinhTrang === 'Hỏng' ? 
                                    '<span class="badge bg-danger">Hỏng</span>' : 
                                    '<span class="badge bg-warning">Đang sửa</span>'}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="xoaThietBiModal(${item.MaThietBi})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-info" 
                                        onclick="chuyenThietBiModal(${item.MaThietBi}, '${item.TenThietBi}')">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                content.innerHTML = html;
            })
            .catch(error => {
                loading.style.display = 'none';
                content.innerHTML = '<p class="text-danger">Lỗi khi tải dữ liệu</p>';
            });
    }

    function xoaThietBiModal(maThietBi) {
        if (confirm('Bạn có chắc muốn xóa thiết bị này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'quanlythietbi.php';

            const inputAction = document.createElement('input');
            inputAction.type = 'hidden';
            inputAction.name = 'action';
            inputAction.value = 'xoa_thiet_bi';
            form.appendChild(inputAction);

            const inputMaTB = document.createElement('input');
            inputMaTB.type = 'hidden';
            inputMaTB.name = 'ma_thiet_bi';
            inputMaTB.value = maThietBi;
            form.appendChild(inputMaTB);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function chuyenThietBiModal(maThietBi, tenThietBi) {
        let modalHTML = `
        <div class="modal fade" id="modalChuyenPhong" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chuyển thiết bị "${tenThietBi}"</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Chọn phòng mới cho thiết bị này:</p>
                        <select class="form-control" id="selectPhongMoi">
                            <option value="">-- Chọn phòng --</option>
                            <?php foreach ($danhSachPhong as $phong): ?>
                                <option value="<?php echo $phong['MaPhong']; ?>">
                                    <?php echo htmlspecialchars($phong['SoPhong'] . ' - ' . $phong['roomName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" onclick="xacNhanChuyen(${maThietBi})">Chuyển</button>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Thêm modal vào body
        const modalDiv = document.createElement('div');
        modalDiv.innerHTML = modalHTML;
        document.body.appendChild(modalDiv);

        // Hiển thị modal
        const modal = new bootstrap.Modal(document.getElementById('modalChuyenPhong'));
        modal.show();

        // Xóa modal khi đóng
        modal._element.addEventListener('hidden.bs.modal', function() {
            modalDiv.remove();
        });
    }

    function xacNhanChuyen(maThietBi) {
        const selectPhongMoi = document.getElementById('selectPhongMoi');
        const maPhongMoi = selectPhongMoi.value;

        if (!maPhongMoi) {
            alert('Vui lòng chọn phòng');
            return;
        }

        // Tạo form submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'quanlythietbi.php';

        const inputAction = document.createElement('input');
        inputAction.type = 'hidden';
        inputAction.name = 'action';
        inputAction.value = 'chuyen_thiet_bi';
        form.appendChild(inputAction);

        const inputMaTB = document.createElement('input');
        inputMaTB.type = 'hidden';
        inputMaTB.name = 'ma_thiet_bi';
        inputMaTB.value = maThietBi;
        form.appendChild(inputMaTB);

        const inputPhongMoi = document.createElement('input');
        inputPhongMoi.type = 'hidden';
        inputPhongMoi.name = 'ma_phong_moi';
        inputPhongMoi.value = maPhongMoi;
        form.appendChild(inputPhongMoi);

        document.body.appendChild(form);
        form.submit();
    }
</script>

<?php include_once '../layouts/footer.php'; ?>