<?php
include_once '../model/quanlykh.model.php';

class QuanLyKHController {
    private $model;

    public function __construct() {
        $this->model = new QuanLyKHModel();
    }

    public function index() {
        $keyword = $_GET['keyword'] ?? '';
        $loaiKH = $_GET['loaiKH'] ?? '';
        $trangThai = $_GET['trangThai'] ?? '';
        
        $danhSachKH = $this->model->getDanhSachKH($keyword, $loaiKH, $trangThai);
        $thongKe = $this->model->thongKeKH();
        
        include '../view/quanly/quanlykh.php';
    }

    public function themKH() {
        if ($_POST) {
            $data = [
                'HoTen' => $_POST['ho_ten'],
                'CMND' => $_POST['cmnd'],
                'SoDienThoai' => $_POST['so_dien_thoai'],
                'Email' => $_POST['email'],
                'DiaChi' => $_POST['dia_chi'],
                'LoaiKH' => $_POST['loai_kh'],
                'TrangThai' => $_POST['trang_thai']
            ];

            // Kiểm tra CMND trùng
            if ($this->model->kiemTraCMND($data['CMND'])) {
                $_SESSION['error'] = "CMND đã tồn tại trong hệ thống!";
                header('Location: quanlykh.php?action=them_form');
                exit();
            }

            $result = $this->model->themKH($data);
            
            if ($result['success']) {
                $_SESSION['success'] = "Thêm khách hàng thành công! Mã KH: " . $result['maKH'];
            } else {
                $_SESSION['error'] = "Lỗi khi thêm khách hàng!";
            }
            header('Location: quanlykh.php');
            exit();
        }
    }

    public function suaKH() {
        if ($_POST && isset($_GET['ma_kh'])) {
            $maKH = $_GET['ma_kh'];
            $data = [
                'HoTen' => $_POST['ho_ten'],
                'CMND' => $_POST['cmnd'],
                'SoDienThoai' => $_POST['so_dien_thoai'],
                'Email' => $_POST['email'],
                'DiaChi' => $_POST['dia_chi'],
                'LoaiKH' => $_POST['loai_kh'],
                'TrangThai' => $_POST['trang_thai']
            ];

            // Kiểm tra CMND trùng (trừ chính nó)
            if ($this->model->kiemTraCMND($data['CMND'], $maKH)) {
                $_SESSION['error'] = "CMND đã tồn tại trong hệ thống!";
                header('Location: quanlykh.php?action=sua_form&ma_kh=' . $maKH);
                exit();
            }

            $result = $this->model->suaKH($maKH, $data);
            
            if ($result) {
                $_SESSION['success'] = "Cập nhật khách hàng thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi cập nhật khách hàng!";
            }
            header('Location: quanlykh.php');
            exit();
        }
    }

    public function xoaKH() {
        if (isset($_GET['ma_kh'])) {
            $maKH = $_GET['ma_kh'];
            $result = $this->model->xoaKH($maKH);
            
            if ($result) {
                $_SESSION['success'] = "Xóa khách hàng thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi xóa khách hàng!";
            }
            header('Location: quanlykh.php');
            exit();
        }
    }

    public function xoaNhieuKH() {
        if (isset($_POST['ma_kh_list']) && is_array($_POST['ma_kh_list'])) {
            $listMaKH = $_POST['ma_kh_list'];
            $result = $this->model->xoaNhieuKH($listMaKH);
            
            if ($result) {
                $_SESSION['success'] = "Xóa " . count($listMaKH) . " khách hàng thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi xóa khách hàng!";
            }
            header('Location: quanlykh.php');
            exit();
        }
    }
}

// Xử lý request
session_start();
$controller = new QuanLyKHController();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'them':
            $controller->themKH();
            break;
        case 'sua':
            $controller->suaKH();
            break;
        case 'xoa':
            $controller->xoaKH();
            break;
        case 'xoa_nhieu':
            $controller->xoaNhieuKH();
            break;
        default:
            $controller->index();
            break;
    }
} else {
    $controller->index();
}
?>