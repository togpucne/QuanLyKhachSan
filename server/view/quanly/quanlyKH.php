<?php
// KIỂM TRA SESSION
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}

// GỌI MODEL
include_once '../../model/quanlykh.model.php';

// Kiểm tra xem model có tồn tại không
if (!class_exists('QuanLyKHModel')) {
    die("<div class='alert alert-danger'>Lỗi: Không thể load model QuanLyKHModel</div>");
}

$model = new QuanLyKHModel();
$action = $_GET['action'] ?? 'index';
$maKH = $_GET['ma_kh'] ?? '';

// ========== XỬ LÝ CRUD ==========

// 1. XÓA KHÁCH HÀNG
if ($action === 'xoa' && !empty($maKH)) {
    try {
        if ($model->xoaKH($maKH)) {
            $_SESSION['success'] = "Xóa khách hàng thành công!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: quanlykh.php');
    exit();
}

// 2. THÊM KHÁCH HÀNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'them') {
    $data = [
        'HoTen' => $_POST['ho_ten'] ?? '',
        'SoDienThoai' => $_POST['so_dien_thoai'] ?? '',
        'DiaChi' => $_POST['dia_chi'] ?? '',
        'TrangThai' => $_POST['trang_thai'] ?? 'Không ở',
        'TenDangNhap' => $_POST['ten_dang_nhap'] ?? '',
        'MatKhau' => $_POST['mat_khau'] ?? '',
        'Email' => $_POST['email'] ?? '',
        'CMND' => $_POST['cmnd'] ?? ''
    ];

    // Validate cơ bản
    $errors = [];
    if (empty($data['HoTen'])) $errors[] = "Họ tên không được để trống";
    if (empty($data['SoDienThoai'])) $errors[] = "Số điện thoại không được để trống";
    if (!empty($data['SoDienThoai']) && !preg_match('/^\d{10,11}$/', $data['SoDienThoai'])) {
        $errors[] = "Số điện thoại phải có 10-11 chữ số";
    }
    if (!empty($data['Email']) && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }

    if (empty($errors)) {
        // Kiểm tra SĐT trùng
        if ($model->kiemTraSDT($data['SoDienThoai'])) {
            $_SESSION['error'] = "Số điện thoại đã tồn tại trong hệ thống!";
        } else {
            // Nếu có tạo tài khoản, kiểm tra thêm
            if (!empty($data['TenDangNhap'])) {
                if ($model->kiemTraTenDangNhap($data['TenDangNhap'])) {
                    $_SESSION['error'] = "Tên đăng nhập đã tồn tại!";
                } else if (!empty($data['Email']) && $model->kiemTraEmail($data['Email'])) {
                    $_SESSION['error'] = "Email đã tồn tại!";
                } else if (!empty($data['CMND']) && $model->kiemTraCMND($data['CMND'])) {
                    $_SESSION['error'] = "CMND đã tồn tại!";
                } else {
                    $result = $model->themKH($data);
                    if ($result['success']) {
                        $_SESSION['success'] = "Thêm khách hàng thành công! Mã KH: " . $result['maKH'];
                        if ($result['taiKhoanID']) {
                            $_SESSION['success'] .= " - Tài khoản đã được tạo!";
                        }
                        header('Location: quanlykh.php');
                        exit();
                    } else {
                        $_SESSION['error'] = $result['error'] ?? "Lỗi khi thêm khách hàng!";
                    }
                }
            } else {
                // Không tạo tài khoản
                $result = $model->themKH($data);
                if ($result['success']) {
                    $_SESSION['success'] = "Thêm khách hàng thành công! Mã KH: " . $result['maKH'];
                    header('Location: quanlykh.php');
                    exit();
                } else {
                    $_SESSION['error'] = $result['error'] ?? "Lỗi khi thêm khách hàng!";
                }
            }
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// 3. SỬA KHÁCH HÀNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'sua' && !empty($maKH)) {
    $data = [
        'HoTen' => $_POST['ho_ten'] ?? '',
        'SoDienThoai' => $_POST['so_dien_thoai'] ?? '',
        'DiaChi' => $_POST['dia_chi'] ?? '',
        'TrangThai' => $_POST['trang_thai'] ?? 'Không ở',
        'TenDangNhap' => $_POST['ten_dang_nhap'] ?? '',
        'MatKhau' => $_POST['mat_khau'] ?? '', // CÓ THỂ RỖNG
        'Email' => $_POST['email'] ?? '',
        'CMND' => $_POST['cmnd'] ?? ''
    ];

    // Validate
    $errors = [];
    if (empty($data['HoTen'])) $errors[] = "Họ tên không được để trống";
    if (empty($data['SoDienThoai'])) $errors[] = "Số điện thoại không được để trống";
    if (!empty($data['SoDienThoai']) && !preg_match('/^\d{10,11}$/', $data['SoDienThoai'])) {
        $errors[] = "Số điện thoại phải có 10-11 chữ số";
    }
    if (!empty($data['Email']) && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    if (!empty($data['CMND']) && !preg_match('/^\d{9,12}$/', $data['CMND'])) {
        $errors[] = "CMND phải có 9-12 chữ số";
    }

    if (empty($errors)) {
        try {
            $result = $model->suaKH($maKH, $data);

            if ($result) {
                $_SESSION['success'] = "Cập nhật khách hàng thành công!";
                header('Location: quanlykh.php');
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            // KHÔNG redirect, ở lại trang sửa để hiển thị lỗi
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}
// 4. RESET MẬT KHẨU
if ($action === 'resetpassword' && isset($_GET['id'])) {
    $taiKhoanID = $_GET['id'];
    $matKhauMoi = '123456'; // Mật khẩu mặc định
    $result = $model->resetMatKhau($taiKhoanID, $matKhauMoi);

    if ($result) {
        $_SESSION['success'] = "Reset mật khẩu thành công! Mật khẩu mới: $matKhauMoi";
    } else {
        $_SESSION['error'] = "Lỗi khi reset mật khẩu!";
    }
    header('Location: quanlykh.php?action=taikhoan');
    exit();
}

// ========== HIỂN THỊ DỮ LIỆU ==========

// Lấy dữ liệu cho trang chính
$keyword = $_GET['keyword'] ?? '';
$trangThai = $_GET['trangThai'] ?? '';
$danhSachKH = $model->getDanhSachKH($keyword, $trangThai);
$thongKe = $model->thongKeKH();

// Lấy chi tiết KH cho form sửa
$khachHang = null;
if ($action === 'sua' && !empty($maKH)) {
    $khachHang = $model->getChiTietKH($maKH);
    if (!$khachHang) {
        $_SESSION['error'] = "Không tìm thấy khách hàng!";
        header('Location: quanlykh.php');
        exit();
    }
}

// Lấy danh sách tài khoản
if ($action === 'taikhoan') {
    $danhSachTK = $model->getDanhSachTaiKhoanKH();
}
?>

<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>
            <?php
            if ($action === 'them') echo "Thêm Khách Hàng Mới";
            elseif ($action === 'sua') echo "Sửa Thông Tin Khách Hàng";
            elseif ($action === 'taikhoan') echo "Quản Lý Tài Khoản Khách Hàng";
            else echo "Quản Lý Khách Hàng";
            ?>
        </h1>
        <div>
            <span class="me-3 text-muted">
                <i class="fas fa-user me-1"></i>Xin chào: <strong><?php echo $_SESSION['user_name'] ?? 'Quản lý'; ?></strong>
            </span>

            <?php if ($action === 'taikhoan'): ?>
                <a href="?action=index" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            <?php elseif ($action === 'them' || $action === 'sua'): ?>
                <a href="quanlykh.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            <?php else: ?>
                <a href="?action=taikhoan" class="btn btn-info me-2">
                    <i class="fas fa-user-circle me-1"></i>Tài khoản KH
                </a>
                <a href="?action=them" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm Khách Hàng
                </a>
            <?php endif; ?>
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

    <!-- ========== TRANG DANH SÁCH TÀI KHOẢN ========== -->
    <?php if ($action === 'taikhoan'): ?>
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user-circle me-2"></i>Danh Sách Tài Khoản Khách Hàng
                    <span class="badge bg-light text-dark ms-2"><?php echo count($danhSachTK ?? []); ?> TK</span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($danhSachTK)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p class="fs-5">Không có tài khoản khách hàng</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="60">STT</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>CMND</th>
                                    <th>Khách hàng</th>
                                    <th width="120">Trạng thái</th>
                                    <th width="120" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; ?>
                                <?php foreach ($danhSachTK as $tk): ?>
                                    <tr>
                                        <td><?php echo $stt++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($tk['TenDangNhap']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($tk['Email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($tk['CMND'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($tk['MaKH'])): ?>
                                                <span class="badge bg-success"><?php echo htmlspecialchars($tk['MaKH']); ?></span>
                                                <br><small><?php echo htmlspecialchars($tk['HoTen'] ?? ''); ?></small>
                                                <br><small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($tk['SoDienThoai'] ?? ''); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Chưa liên kết</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tk['TrangThai'] == 1): ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="?action=resetpassword&id=<?php echo $tk['id']; ?>"
                                                class="btn btn-warning btn-sm"
                                                onclick="return confirm('Reset mật khẩu về 123456?')">
                                                <i class="fas fa-key"></i> Reset MK
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========== FORM THÊM/SỬA KHÁCH HÀNG ========== -->
    <?php elseif ($action === 'them' || $action === 'sua'): ?>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header <?php echo $action === 'them' ? 'bg-primary' : 'bg-warning'; ?> text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-<?php echo $action === 'them' ? 'plus' : 'edit'; ?> me-2"></i>
                            <?php echo $action === 'them' ? 'Thêm Khách Hàng Mới' : 'Sửa Thông Tin Khách Hàng'; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?action=<?php echo $action; ?><?php echo $action === 'sua' ? '&ma_kh=' . $maKH : ''; ?>" id="formKH">

                            <?php if ($action === 'sua'): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Mã KH:</strong> <?php echo htmlspecialchars($khachHang['MaKH']); ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Mã KH sẽ được tạo tự động</strong> (KH1, KH2, KH3,...)
                                </div>
                            <?php endif; ?>

                            <h5 class="mb-3 border-bottom pb-2"><i class="fas fa-user me-2"></i>Thông tin cá nhân</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ho_ten" required
                                        value="<?php echo $action === 'sua' ? htmlspecialchars($khachHang['HoTen']) : ''; ?>"
                                        placeholder="Nhập họ tên đầy đủ">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="so_dien_thoai" required
                                        value="<?php echo $action === 'sua' ? htmlspecialchars($khachHang['SoDienThoai']) : ''; ?>"
                                        placeholder="Nhập số điện thoại">
                                    <small class="form-text text-muted">Ví dụ: 0909123456</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select class="form-control" name="trang_thai" required>
                                        <option value="Không ở" <?php echo ($action === 'sua' && $khachHang['TrangThai'] === 'Không ở') ? 'selected' : ''; ?>>Không ở</option>
                                        <option value="Đang ở" <?php echo ($action === 'sua' && $khachHang['TrangThai'] === 'Đang ở') ? 'selected' : ''; ?>>Đang ở</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" name="dia_chi" rows="2"
                                        placeholder="Nhập địa chỉ đầy đủ"><?php echo $action === 'sua' ? htmlspecialchars($khachHang['DiaChi'] ?? '') : ''; ?></textarea>
                                </div>
                            </div>

                            <h5 class="mb-3 mt-4 border-bottom pb-2"><i class="fas fa-user-lock me-2"></i>Thông tin tài khoản (tùy chọn)</h5>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <small>Chỉ điền thông tin nếu muốn tạo/liên kết tài khoản cho khách hàng</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" name="ten_dang_nhap"
                                        value="<?php echo $action === 'sua' ? htmlspecialchars($khachHang['TenDangNhap'] ?? '') : ''; ?>"
                                        placeholder="Tên đăng nhập (nếu tạo TK)">
                                    <small class="form-text text-muted">Dùng để đăng nhập hệ thống</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu</label>
                                    <input type="password" class="form-control" name="mat_khau"
                                        placeholder="Mật khẩu (nếu tạo TK)">
                                    <small class="form-text text-muted">
                                        <?php if ($action === 'sua'): ?>
                                            <strong>Chỉ điền nếu muốn đổi mật khẩu</strong><br>
                                            <span class="text-danger">Nếu không thay đổi, để trống ô này</span>
                                        <?php else: ?>
                                            Ít nhất 6 ký tự
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email"
                                        value="<?php echo $action === 'sua' ? htmlspecialchars($khachHang['Email'] ?? '') : ''; ?>"
                                        placeholder="Email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CMND/CCCD</label>
                                    <input type="text" class="form-control" name="cmnd"
                                        value="<?php echo $action === 'sua' ? htmlspecialchars($khachHang['CMND'] ?? '') : ''; ?>"
                                        placeholder="Số CMND/CCCD" maxlength="12">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn <?php echo $action === 'them' ? 'btn-primary' : 'btn-warning'; ?>">
                                    <i class="fas fa-save me-1"></i>
                                    <?php echo $action === 'them' ? 'Thêm KH' : 'Cập nhật'; ?>
                                </button>
                                <a href="quanlykh.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== TRANG CHÍNH - DANH SÁCH KHÁCH HÀNG ========== -->
    <?php else: ?>
        <!-- Thống kê -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tổng Khách Hàng
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongKH'] ?? 0; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Không ở
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongKhongO'] ?? 0; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-home fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Đang ở
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongDangO'] ?? 0; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bed fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Có tài khoản
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $thongKe['tongCoTaiKhoan'] ?? 0; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="" id="searchForm">
                    <div class="row">
                        <div class="col-md-5 mb-2">
                            <input type="text" class="form-control" name="keyword" id="searchInput"
                                placeholder="Nhập mã, tên, SĐT, địa chỉ, email, CMND để tìm kiếm..."
                                value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                        <div class="col-md-4 mb-2">
                            <select class="form-control" name="trangThai" id="trangThaiFilter">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Không ở" <?php echo $trangThai === 'Không ở' ? 'selected' : ''; ?>>Không ở</option>
                                <option value="Đang ở" <?php echo $trangThai === 'Đang ở' ? 'selected' : ''; ?>>Đang ở</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Tìm
                            </button>
                            <?php if ($keyword || $trangThai): ?>
                                <a href="quanlykh.php" class="btn btn-secondary w-100 mt-2">
                                    <i class="fas fa-times me-1"></i>Xóa lọc
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách khách hàng -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-list me-2"></i>Danh Sách Khách Hàng
                    <span class="badge bg-light text-dark ms-2"><?php echo count($danhSachKH); ?> KH</span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($danhSachKH)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p class="fs-5">Không có dữ liệu khách hàng</p>
                        <a href="quanlykh.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i>Tải lại
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="60">STT</th>
                                    <th width="120">Mã KH</th>
                                    <th>Họ tên</th>
                                    <th width="120">SĐT</th>
                                    <th width="150">Email/CMND</th>
                                    <th>Địa chỉ</th>
                                    <th width="100">Trạng thái</th>
                                    <th width="140" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; ?>
                                <?php foreach ($danhSachKH as $kh): ?>
                                    <tr>
                                        <td><?php echo $stt++; ?></td>
                                        <td>
                                            <div>
                                                <span class="badge bg-success"><?php echo htmlspecialchars($kh['MaKH']); ?></span>
                                            </div>
                                            <div class="mt-1">
                                                <?php if (!empty($kh['MaTaiKhoan'])): ?>
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    <small class="text-success">Đã có tài khoản</small>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle text-secondary me-1"></i>
                                                    <small class="text-secondary">Chưa có tài khoản</small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($kh['HoTen']); ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-phone text-primary me-1"></i>
                                            <?php echo htmlspecialchars($kh['SoDienThoai'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($kh['Email'])): ?>
                                                <small><i class="fas fa-envelope text-info"></i> <?php echo htmlspecialchars($kh['Email']); ?></small><br>
                                            <?php endif; ?>
                                            <?php if (!empty($kh['CMND'])): ?>
                                                <small><i class="fas fa-id-card text-warning"></i> <?php echo htmlspecialchars($kh['CMND']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($kh['DiaChi'])): ?>
                                                <small><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($kh['DiaChi']); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Chưa có địa chỉ</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($kh['TrangThai'] === 'Đang ở'): ?>
                                                <span class="badge bg-success"><i class="fas fa-bed me-1"></i>Đang ở</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-home me-1"></i>Không ở</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm w-100">
                                                <a href="?action=sua&ma_kh=<?php echo $kh['MaKH']; ?>"
                                                    class="btn btn-warning" title="Sửa thông tin">
                                                    <i class="fas fa-edit"></i> Sửa
                                                </a>
                                                <a href="?action=xoa&ma_kh=<?php echo $kh['MaKH']; ?>"
                                                    class="btn btn-danger"
                                                    onclick="return confirm('Bạn có chắc muốn xóa khách hàng <?php echo $kh['MaKH']; ?> - <?php echo htmlspecialchars($kh['HoTen']); ?>?')"
                                                    title="Xóa khách hàng">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../layouts/footer.php'; ?>

<script>
    // Validate form KH
    <?php if ($action === 'them' || $action === 'sua'): ?>
        document.getElementById('formKH').addEventListener('submit', function(e) {
            const sdt = document.querySelector('input[name="so_dien_thoai"]').value;
            const hoTen = document.querySelector('input[name="ho_ten"]').value;
            const tenDangNhap = document.querySelector('input[name="ten_dang_nhap"]').value;
            const matKhau = document.querySelector('input[name="mat_khau"]').value;
            const email = document.querySelector('input[name="email"]').value;

            // Validate SĐT (10-11 số)
            if (!/^\d{10,11}$/.test(sdt)) {
                alert('Số điện thoại phải có 10-11 chữ số!');
                e.preventDefault();
                return false;
            }

            // Validate họ tên (ít nhất 2 từ)
            const nameParts = hoTen.trim().split(/\s+/);
            if (nameParts.length < 2) {
                alert('Họ tên phải có ít nhất 2 từ!');
                e.preventDefault();
                return false;
            }

            // Nếu có nhập tên đăng nhập thì phải có mật khẩu (khi thêm mới)
            <?php if ($action === 'them'): ?>
                if (tenDangNhap && !matKhau) {
                    alert('Vui lòng nhập mật khẩu khi tạo tài khoản!');
                    e.preventDefault();
                    return false;
                }
                if (matKhau && matKhau.length < 6) {
                    alert('Mật khẩu phải có ít nhất 6 ký tự!');
                    e.preventDefault();
                    return false;
                }
            <?php endif; ?>

            // Validate email nếu có
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Email không hợp lệ!');
                e.preventDefault();
                return false;
            }

            return true;
        });
    <?php endif; ?>

    // Tự động tìm kiếm khi thay đổi filter
    <?php if ($action === 'index'): ?>
        document.getElementById('trangThaiFilter').addEventListener('change', function() {
            document.getElementById('searchForm').submit();
        });
    <?php endif; ?>

    // Focus vào ô tìm kiếm khi load trang
    <?php if ($action === 'index'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchInput').focus();
        });

    <?php endif; ?>
</script>