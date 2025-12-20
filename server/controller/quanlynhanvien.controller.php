<?php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}

// Gọi Model
include_once '../model/quanlynhanvien.model.php';
$model = new QuanLyNhanVienModel();

// Xác định action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Router đơn giản
if ($method === 'GET') {
    switch ($action) {
        case '':
        case 'index':
            indexGET($model);
            break;

        case 'get_nhan_vien_info':
            getNhanVienInfoGET($model);
            break;

        case 'get_tai_khoan_chua_gan':
            getTaiKhoanChuaGanGET($model);
            break;

        case 'get_ds_phong_ban':
            getDsPhongBanGET($model);
            break;

        case 'xoa':
            xoaGET($model);
            break;

        default:
            indexGET($model);
            break;
    }
} elseif ($method === 'POST') {
    switch ($action) {
        case 'them':
            themPOST($model);
            break;

        case 'sua':
            suaPOST($model);
            break;

        default:
            indexGET($model);
            break;
    }
}

// ============================================
// CÁC HÀM XỬ LÝ GET
// ============================================

function indexGET($model)
{
    $keyword = $_GET['keyword'] ?? '';

    // Lấy dữ liệu
    if (!empty($keyword)) {
        $danhSachNhanVien = $model->timKiemNhanVien($keyword);
    } else {
        $danhSachNhanVien = $model->getDanhSachNhanVien();
    }

    $thongKe = $model->thongKeNhanVien();
    $dsPhongBan = $model->getDanhSachPhongBan();
    $dsTaiKhoanChuaGan = $model->getTaiKhoanChuaGanNhanVien();

    // Load View
    include_once '../view/quanly/quanlynhanvien.php';
}

function getNhanVienInfoGET($model)
{
    if (!isset($_GET['ma_nhan_vien'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu mã nhân viên']);
        exit();
    }

    $maNhanVien = $_GET['ma_nhan_vien'];
    $nhanVien = $model->getChiTietNhanVien($maNhanVien);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => !empty($nhanVien),
        'data' => $nhanVien ?: null
    ]);
    exit();
}

function getTaiKhoanChuaGanGET($model)
{
    $dsTaiKhoan = $model->getTaiKhoanChuaGanNhanVien();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $dsTaiKhoan
    ]);
    exit();
}

function getDsPhongBanGET($model)
{
    $dsPhongBan = $model->getDanhSachPhongBan();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $dsPhongBan
    ]);
    exit();
}

function xoaGET($model)
{
    if (!isset($_GET['ma_nhan_vien'])) {
        $_SESSION['error'] = "Thiếu mã nhân viên";
        header('Location: quanlynhanvien.controller.php');
        exit();
    }

    $maNhanVien = $_GET['ma_nhan_vien'];
    $result = $model->xoaNhanVien($maNhanVien);

    if ($result['success']) {
        $_SESSION['success'] = "Xóa nhân viên thành công! Tài khoản đã được xóa.";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa nhân viên: " . ($result['message'] ?? '');
    }

    header('Location: quanlynhanvien.controller.php');
    exit();
}

// ============================================
// CÁC HÀM XỬ LÝ POST
// ============================================

function themPOST($model)
{
    // Validate dữ liệu
    $requiredFields = ['email', 'mat_khau', 'ho_ten', 'sdt', 'ngay_vao_lam', 'phong_ban', 'luong_co_ban', 'trang_thai'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin bắt buộc!";
            header('Location: quanlynhanvien.controller.php');
            exit();
        }
    }

    // Dữ liệu nhân viên
    $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'] ?? '',
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? NULL,
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai'],
        'CMND' => $_POST['cmnd'] ?? '',
        'email' => $_POST['email'],
        'mat_khau' => $_POST['mat_khau']
    ];

    $result = $model->themNhanVien($data);

    if ($result['success']) {
        $message = "✅ Thêm nhân viên thành công!<br><br>";

        $message .= "<strong>Thông tin nhân viên:</strong><br>";
        $message .= "• Mã NV: <strong>" . $result['maNhanVien'] . "</strong><br>";
        $message .= "• Họ tên: <strong>" . $_POST['ho_ten'] . "</strong><br>";
        $message .= "• Phòng ban: <strong>" . $_POST['phong_ban'] . "</strong><br>";
        $message .= "• Trạng thái: <strong>" . $_POST['trang_thai'] . "</strong><br><br>";

        $message .= "<strong>Thông tin tài khoản:</strong><br>";
        $message .= "• Tên hiển thị: <strong>" . $result['ten_dang_nhap'] . "</strong><br>";
        $message .= "• Email đăng nhập: <strong>" . $_POST['email'] . "</strong><br>";
        $message .= "• Mật khẩu: <strong>" . $_POST['mat_khau'] . "</strong><br>";
        $message .= "• Vai trò: <strong>" . $model->convertPhongBanToVaiTro($_POST['phong_ban']) . "</strong><br><br>";

        $message .= "<div class='alert alert-warning'>";
        $message .= "<strong>📢 Lưu ý quan trọng:</strong><br>";
        $message .= "1. Tên đăng nhập (TenDangNhap) là: <strong>" . $result['ten_dang_nhap'] . "</strong><br>";
        $message .= "2. Nhân viên sẽ đăng nhập bằng <strong>Email</strong> và <strong>Mật khẩu</strong><br>";
        $message .= "3. Lưu lại thông tin này để cung cấp cho nhân viên";
        $message .= "</div>";

        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "❌ Lỗi khi thêm nhân viên: " . ($result['message'] ?? '');
    }

    header('Location: quanlynhanvien.controller.php');
    exit();
}

function suaPOST($model) {
    if (!isset($_POST['ma_nhan_vien'])) {
        $_SESSION['error'] = "Thiếu mã nhân viên";
        header('Location: quanlynhanvien.controller.php');
        exit();
    }
    
    $maNhanVien = $_POST['ma_nhan_vien'];
    
    // Lấy dữ liệu từ form
    $email = $_POST['email'] ?? '';
    $cmnd = $_POST['cmnd'] ?? '';
    
    // SỬA MẢNG $data - THÊM 2 DÒNG NÀY:
    $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'] ?? '',
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? '0000-00-00',
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai'],
        'email' => $email,  // <=== THÊM DÒNG NÀY
        'cmnd' => $cmnd     // <=== THÊM DÒNG NÀY
    ];
    
    // XÓA phần xử lý email riêng này (nếu có):
    /*
    // Xử lý cập nhật email nếu có
    if ($taiKhoanID && ($email || $cmnd)) {
        $tkData = [];
        if ($email) {
            $tkData['email'] = $email;
        }
        if ($cmnd) {
            $tkData['cmnd'] = $cmnd;
        }
        
        $resultTK = $model->capNhatThongTinTaiKhoan($taiKhoanID, $tkData);
        if (!$resultTK['success']) {
            $_SESSION['error'] = "Lỗi cập nhật tài khoản: " . $resultTK['error'];
            header('Location: quanlynhanvien.controller.php');
            exit();
        }
    }
    */
    
    // Kiểm tra nếu có yêu cầu reset mật khẩu
    if (isset($_POST['reset_mat_khau']) && $_POST['reset_mat_khau'] == '1') {
        $data['reset_mat_khau'] = '1';
        $data['mat_khau_moi'] = $_POST['mat_khau_moi'] ?? '123456';
    }
    
    // Gọi model
    $result = $model->suaNhanVien($maNhanVien, $data);
    
    if ($result['success']) {
        $message = "✅ Cập nhật nhân viên thành công!<br>";
        
        // Thông báo cập nhật email nếu có
        if ($email) {
            $message .= "📧 Email đã được cập nhật: <strong>$email</strong><br>";
        }
        
        // Thông báo cập nhật CMND nếu có
        if ($cmnd) {
            $message .= "🆔 CMND đã được cập nhật: <strong>$cmnd</strong><br>";
        }
        
        // ... phần còn lại ...
    } else {
        $_SESSION['error'] = "❌ Lỗi khi cập nhật nhân viên: " . ($result['message'] ?? '');
    }
    
    header('Location: quanlynhanvien.controller.php');
    exit();
}
?>