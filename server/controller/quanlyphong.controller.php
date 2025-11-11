<?php
include_once '../model/quanlyphong.model.php';

class QuanLyPhongController {
    private $model;

    public function __construct() {
        $this->model = new QuanLyPhongModel();
    }

    public function index() {
        $keyword = $_GET['keyword'] ?? '';
        $tang = $_GET['tang'] ?? '';
        $loaiPhong = $_GET['loaiPhong'] ?? '';
        $trangThai = $_GET['trangThai'] ?? '';
        
        $danhSachPhong = $this->model->getDanhSachPhong($keyword, $tang, $loaiPhong, $trangThai);
        $dsLoaiPhong = $this->model->getDanhSachLoaiPhong();
        $thongKe = $this->model->thongKePhong();
        
        include '../view/quanly/quanlyphong.php';
    }

    public function themPhong() {
        if ($_POST) {
            // Kiểm tra số phòng đã tồn tại
            if ($this->model->kiemTraSoPhong($_POST['so_phong'])) {
                $_SESSION['error'] = "Số phòng đã tồn tại!";
                header('Location: quanlyphong.php');
                exit();
            }

            $data = [
                'SoPhong' => $_POST['so_phong'],
                'Tang' => $_POST['tang'],
                'MaLoaiPhong' => $_POST['ma_loai_phong'],
                'TrangThai' => $_POST['trang_thai'],
                'Avatar' => $_POST['avatar'] ?? '',
                'DanhSachPhong' => $_POST['danh_sach_phong'] ?? '',
                'roomName' => $_POST['room_name']
            ];

            $result = $this->model->themPhong($data);
            
            if ($result['success']) {
                $_SESSION['success'] = "Thêm phòng thành công! Mã phòng: " . $result['maPhong'];
            } else {
                $_SESSION['error'] = "Lỗi khi thêm phòng!";
            }
            header('Location: quanlyphong.php');
            exit();
        }
    }

    public function suaPhong() {
        if ($_POST && isset($_GET['ma_phong'])) {
            $maPhong = $_GET['ma_phong'];
            
            // Kiểm tra số phòng đã tồn tại (trừ chính nó)
            if ($this->model->kiemTraSoPhong($_POST['so_phong'], $maPhong)) {
                $_SESSION['error'] = "Số phòng đã tồn tại!";
                header('Location: quanlyphong.php');
                exit();
            }

            $data = [
                'SoPhong' => $_POST['so_phong'],
                'Tang' => $_POST['tang'],
                'MaLoaiPhong' => $_POST['ma_loai_phong'],
                'TrangThai' => $_POST['trang_thai'],
                'Avatar' => $_POST['avatar'] ?? '',
                'DanhSachPhong' => $_POST['danh_sach_phong'] ?? '',
                'roomName' => $_POST['room_name']
            ];

            $result = $this->model->suaPhong($maPhong, $data);
            
            if ($result) {
                $_SESSION['success'] = "Cập nhật phòng thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi cập nhật phòng!";
            }
            header('Location: quanlyphong.php');
            exit();
        }
    }

    public function xoaPhong() {
        if (isset($_GET['ma_phong'])) {
            $maPhong = $_GET['ma_phong'];
            $result = $this->model->xoaPhong($maPhong);
            
            if ($result) {
                $_SESSION['success'] = "Xóa phòng thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi xóa phòng!";
            }
            header('Location: quanlyphong.php');
            exit();
        }
    }

    public function themThietBi() {
        if ($_POST && isset($_GET['ma_phong'])) {
            $data = [
                'TenThietBi' => $_POST['ten_thiet_bi'],
                'TinhTrang' => $_POST['tinh_trang'],
                'MaPhong' => $_GET['ma_phong']
            ];

            $result = $this->model->themThietBi($data);
            
            if ($result) {
                $_SESSION['success'] = "Thêm thiết bị thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi thêm thiết bị!";
            }
            header('Location: quanlyphong.php');
            exit();
        }
    }

    public function xoaThietBi() {
        if (isset($_GET['ma_thiet_bi'])) {
            $maThietBi = $_GET['ma_thiet_bi'];
            $result = $this->model->xoaThietBi($maThietBi);
            
            if ($result) {
                $_SESSION['success'] = "Xóa thiết bị thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi xóa thiết bị!";
            }
            header('Location: quanlyphong.php');
            exit();
        }
    }
}

// Xử lý request
session_start();
$controller = new QuanLyPhongController();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'them':
            $controller->themPhong();
            break;
        case 'sua':
            $controller->suaPhong();
            break;
        case 'xoa':
            $controller->xoaPhong();
            break;
        case 'them_thiet_bi':
            $controller->themThietBi();
            break;
        case 'xoa_thiet_bi':
            $controller->xoaThietBi();
            break;
        default:
            $controller->index();
            break;
    }
} else {
    $controller->index();
}
?>