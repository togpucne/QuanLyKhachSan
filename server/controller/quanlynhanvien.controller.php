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

function themPOST($model) {
    // Validate dữ liệu
    $requiredFields = ['email', 'mat_khau', 'ho_ten', 'sdt', 'ngay_vao_lam', 'phong_ban', 'luong_co_ban', 'trang_thai'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "❌ Vui lòng điền đầy đủ thông tin bắt buộc!";
            header('Location: quanlynhanvien.controller.php');
            exit();
        }
    }
    
    // Kiểm tra lương
    if ($_POST['luong_co_ban'] <= 0) {
        $_SESSION['error'] = "❌ Lương cơ bản phải lớn hơn 0!";
        header('Location: quanlynhanvien.controller.php');
        exit();
    }
    
    // Kiểm tra mật khẩu
    if (strlen($_POST['mat_khau']) < 6) {
        $_SESSION['error'] = "❌ Mật khẩu phải có ít nhất 6 ký tự!";
        header('Location: quanlynhanvien.controller.php');
        exit();
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
        // ... phần success message giữ nguyên ...
    } else {
        $_SESSION['error'] = "❌ " . ($result['message'] ?? 'Lỗi khi thêm nhân viên');
    }
    
    header('Location: quanlynhanvien.controller.php');
    exit();
}

function suaPOST($model) {
    if (!isset($_POST['ma_nhan_vien'])) {
        $_SESSION['error'] = "❌ Thiếu mã nhân viên";
        header('Location: quanlynhanvien.controller.php');
        exit();
    }
    
    $maNhanVien = $_POST['ma_nhan_vien'];
    
    // Kiểm tra các trường bắt buộc không để trống khi update
    $requiredFields = ['ho_ten', 'sdt', 'ngay_vao_lam', 'phong_ban', 'luong_co_ban', 'trang_thai'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "❌ Trường " . str_replace('_', ' ', $field) . " không được để trống!";
            header('Location: quanlynhanvien.controller.php');
            exit();
        }
    }
    
    // Kiểm tra lương
    if ($_POST['luong_co_ban'] <= 0) {
        $_SESSION['error'] = "❌ Lương cơ bản phải lớn hơn 0!";
        header('Location: quanlynhanvien.controller.php');
        exit();
    }
    
    // Lấy dữ liệu từ form
    $email = $_POST['email'] ?? '';
    $cmnd = $_POST['cmnd'] ?? '';
    
    $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'] ?? '',
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? '0000-00-00',
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai'],
        'email' => $email,
        'cmnd' => $cmnd
    ];
    
    // Kiểm tra nếu có yêu cầu reset mật khẩu
    if (isset($_POST['reset_mat_khau']) && $_POST['reset_mat_khau'] == '1') {
        $data['reset_mat_khau'] = '1';
        $data['mat_khau_moi'] = $_POST['mat_khau_moi'] ?? '123456';
    }
    
    // Gọi model
    $result = $model->suaNhanVien($maNhanVien, $data);
    
    if ($result['success']) {
        $message = "✅ Cập nhật nhân viên thành công!<br>";
        
        if ($email) {
            $message .= "📧 Email đã được cập nhật: <strong>$email</strong><br>";
        }
        
        if ($cmnd) {
            $message .= "🆔 CMND đã được cập nhật: <strong>$cmnd</strong><br>";
        }
        
        if (isset($result['mat_khau_moi'])) {
            $message .= "🔑 Mật khẩu mới: <strong>" . $result['mat_khau_moi'] . "</strong><br>";
        }
        
        if (isset($result['auto_updated']) && $result['auto_updated']) {
            $message .= "⚠️ <em>" . $result['message'] . "</em><br>";
        }
        
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "❌ " . ($result['message'] ?? 'Lỗi khi cập nhật nhân viên');
    }
    
    header('Location: quanlynhanvien.controller.php');
    exit();
}
?>