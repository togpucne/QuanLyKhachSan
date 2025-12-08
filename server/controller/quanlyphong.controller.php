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

    // DEBUG: Kiểm tra xem có lỗi không
    error_reporting(0); // Tắt hiển thị lỗi
    ini_set('display_errors', 0);

    if ($maPhong) {
        try {
            $phong = $model->getChiTietPhong($maPhong);

            // Đảm bảo không có output nào trước JSON
            if (ob_get_length()) ob_clean();

            header('Content-Type: application/json; charset=utf-8');

            if ($phong) {
                echo json_encode($phong, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode(['error' => 'Không tìm thấy phòng'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            // Đảm bảo không có output nào trước JSON
            if (ob_get_length()) ob_clean();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Lỗi server: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    } else {
        // Đảm bảo không có output nào trước JSON
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Mã phòng không hợp lệ'], JSON_UNESCAPED_UNICODE);
    }
    exit();
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
// THÊM XỬ LÝ XÓA ẢNH CHI TIẾT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'xoa_anh') {
    $maPhong = $_POST['ma_phong'] ?? 0;
    $imgPath = $_POST['img_path'] ?? '';

    if ($maPhong && $imgPath) {
        $result = $model->xoaAnhChiTiet($maPhong, $imgPath);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
}

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
