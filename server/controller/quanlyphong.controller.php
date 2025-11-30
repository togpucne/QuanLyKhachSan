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
        'GiaPhong' => $_POST['gia_phong'],
        'DienTich' => $_POST['dien_tich'],
        'SoKhachToiDa' => $_POST['so_khach_toi_da'],
        'HuongNha' => $_POST['huong_nha'],
        'MoTaChiTiet' => $_POST['mo_ta_chi_tiet'],
        'TienNghi' => $_POST['tien_nghi_json'] // Nhận JSON từ hidden input
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
// ... code hiện tại ...

// XỬ LÝ LẤY THÔNG TIN PHÒNG ĐỂ SỬA
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'lay_thong_tin') {
    $maPhong = $_GET['ma_phong'] ?? 0;
    if ($maPhong) {
        $phong = $model->getChiTietPhong($maPhong);
        header('Content-Type: application/json');
        echo json_encode($phong);
        exit();
    }
}

// XỬ LÝ CẬP NHẬT PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sua') {
    $maPhong = $_POST['ma_phong'] ?? 0;

    if ($maPhong) {
        $data = [
            'Tang' => $_POST['tang'],
            'MaLoaiPhong' => $_POST['ma_loai_phong'],
            'TrangThai' => $_POST['trang_thai'],
            'roomName' => $_POST['room_name'],
            'GiaPhong' => $_POST['gia_phong'],
            'DienTich' => $_POST['dien_tich'],
            'SoKhachToiDa' => $_POST['so_khach_toi_da'],
            'HuongNha' => $_POST['huong_nha'],
            'MoTaChiTiet' => $_POST['mo_ta_chi_tiet'],
            'TienNghi' => $_POST['tien_nghi_json']
        ];

        $avatarFile = isset($_FILES['avatar']) ? $_FILES['avatar'] : null;
        $imageFiles = isset($_FILES['danh_sach_anh']) ? $_FILES['danh_sach_anh'] : null;

        $result = $model->capNhatPhong($maPhong, $data, $avatarFile, $imageFiles);

        if ($result['success']) {
            $_SESSION['success'] = "Cập nhật phòng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật phòng: " . $result['error'];
        }
    } else {
        $_SESSION['error'] = "Mã phòng không hợp lệ!";
    }
    header('Location: quanlyphong.php');
    exit();
}

// ... code hiện tại ...

// XỬ LÝ LẤY THÔNG TIN PHÒNG ĐỂ SỬA
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'lay_thong_tin') {
    $maPhong = $_GET['ma_phong'] ?? 0;
    if ($maPhong) {
        $phong = $model->getChiTietPhong($maPhong);
        header('Content-Type: application/json');
        echo json_encode($phong);
        exit();
    }
}

// XỬ LÝ CẬP NHẬT PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sua') {
    $maPhong = $_POST['ma_phong'] ?? 0;
    
    if ($maPhong) {
        $data = [
            'Tang' => $_POST['tang'],
            'MaLoaiPhong' => $_POST['ma_loai_phong'],
            'TrangThai' => $_POST['trang_thai'],
            'roomName' => $_POST['room_name'],
            'GiaPhong' => $_POST['gia_phong'],
            'DienTich' => $_POST['dien_tich'],
            'SoKhachToiDa' => $_POST['so_khach_toi_da'],
            'HuongNha' => $_POST['huong_nha'],
            'MoTaChiTiet' => $_POST['mo_ta_chi_tiet'],
            'TienNghi' => $_POST['tien_nghi_json']
        ];

        $avatarFile = isset($_FILES['avatar']) ? $_FILES['avatar'] : null;
        $imageFiles = isset($_FILES['danh_sach_anh']) ? $_FILES['danh_sach_anh'] : null;

        $result = $model->capNhatPhong($maPhong, $data, $avatarFile, $imageFiles);

        if ($result['success']) {
            $_SESSION['success'] = "Cập nhật phòng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật phòng: " . $result['error'];
        }
    } else {
        $_SESSION['error'] = "Mã phòng không hợp lệ!";
    }
    header('Location: quanlyphong.php');
    exit();
}

// ... code hiện tại ...
// LẤY THAM SỐ BỘ LỌC - ĐẶT MẶC ĐỊNH
$keyword = $_GET['keyword'] ?? '';
$tang = $_GET['tang'] ?? '';
$loaiPhong = $_GET['loaiPhong'] ?? '';
$trangThai = $_GET['trangThai'] ?? '';

// LẤY DANH SÁCH PHÒNG THEO BỘ LỌC
$danhSachPhong = $model->getDanhSachPhong($keyword, $tang, $loaiPhong, $trangThai);

// ĐẢM BẢO BIẾN LUÔN TỒN TẠI
$danhSachPhong = $danhSachPhong ?? [];

// HIỂN TH� VIEW
include 'quanlyphong.view.php';
