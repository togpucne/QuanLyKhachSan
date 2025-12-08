<?php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}

// Gọi Model
include_once 'quanlynhanvien.model.php';
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

function indexGET($model) {
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
    include_once 'view/quanly/quanlynhanvien.php';
}

function getNhanVienInfoGET($model) {
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

function getTaiKhoanChuaGanGET($model) {
    $dsTaiKhoan = $model->getTaiKhoanChuaGanNhanVien();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $dsTaiKhoan
    ]);
    exit();
}

function getDsPhongBanGET($model) {
    $dsPhongBan = $model->getDanhSachPhongBan();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $dsPhongBan
    ]);
    exit();
}

function xoaGET($model) {
    if (!isset($_GET['ma_nhan_vien'])) {
        $_SESSION['error'] = "Thiếu mã nhân viên";
        header('Location: quanlynhanvien.controller.php');
        exit();
    }
    
    $maNhanVien = $_GET['ma_nhan_vien'];
    
    if ($model->xoaNhanVien($maNhanVien)) {
        $_SESSION['success'] = "Xóa nhân viên thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa nhân viên!";
    }
    
    header('Location: quanlynhanvien.controller.php');
    exit();
}

// ============================================
// CÁC HÀM XỬ LÝ POST
// ============================================

function themPOST($model) {
    // Kiểm tra xem tạo tài khoản mới hay dùng tài khoản có sẵn
    if (isset($_POST['tao_tai_khoan_moi']) && $_POST['tao_tai_khoan_moi'] == '1') {
        // TẠO TÀI KHOẢN MỚI
        $tkData = [
            'email' => $_POST['email_tai_khoan'],
            'mat_khau' => $_POST['mat_khau'],
            'vai_tro' => $_POST['vai_tro_tai_khoan'],
            'cmnd' => $_POST['cmnd_tai_khoan'] ?? ''
        ];
        
        $resultTK = $model->taoTaiKhoanChoNhanVien($tkData);
        
        if (!$resultTK['success']) {
            $_SESSION['error'] = "Lỗi tạo tài khoản: " . $resultTK['message'];
            header('Location: quanlynhanvien.controller.php');
            exit();
        }
        
        $maTaiKhoan = $resultTK['maTaiKhoan'];
    } else {
        // SỬ DỤNG TÀI KHOẢN CÓ SẴN
        $maTaiKhoan = $_POST['ma_tai_khoan'] ?? NULL;
        
        if (empty($maTaiKhoan)) {
            $_SESSION['error'] = "Vui lòng chọn tài khoản cho nhân viên!";
            header('Location: quanlynhanvien.controller.php');
            exit();
        }
    }
    
    // Dữ liệu nhân viên
    $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'],
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? NULL,
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai'],
        'MaTaiKhoan' => $maTaiKhoan
    ];
    
    $result = $model->themNhanVien($data);
    
    if ($result['success']) {
        $_SESSION['success'] = "Thêm nhân viên thành công! Mã nhân viên: " . $result['maNhanVien'];
        if (isset($_POST['tao_tai_khoan_moi'])) {
            $_SESSION['success'] .= "<br>Tài khoản: " . $_POST['email_tai_khoan'];
        }
    } else {
        $_SESSION['error'] = "Lỗi khi thêm nhân viên! " . ($result['message'] ?? '');
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
    $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'],
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? NULL,
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai'],
        'MaTaiKhoan' => $_POST['ma_tai_khoan'] ?? NULL
    ];
    
    if ($model->suaNhanVien($maNhanVien, $data)) {
        $_SESSION['success'] = "Cập nhật nhân viên thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi cập nhật nhân viên!";
    }
    
    header('Location: quanlynhanvien.controller.php');
    exit();
}
?>