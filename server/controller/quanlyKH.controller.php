<?php
include_once '../model/quanlykh.model.php';

class QuanLyKHController {
    private $model;

    public function __construct() {
        $this->model = new QuanLyKHModel();
    }

    // Hiển thị trang chính
    public function index() {
        $keyword = $_GET['keyword'] ?? '';
        $trangThai = $_GET['trangThai'] ?? '';
        
        $danhSachKH = $this->model->getDanhSachKH($keyword, $trangThai);
        $thongKe = $this->model->thongKeKH();
        
        // Hiển thị view
        $this->renderView('quanlykh', [
            'danhSachKH' => $danhSachKH,
            'thongKe' => $thongKe,
            'keyword' => $keyword,
            'trangThai' => $trangThai
        ]);
    }

    // Hiển thị form thêm
    public function themForm() {
        $this->renderView('themkh');
    }

    // Xử lý thêm KH
    public function themKH() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: quanlykh.php');
            exit();
        }

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

        // Validate
        $errors = $this->validateKH($data);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->themForm();
            return;
        }

        // Kiểm tra SĐT
        if ($this->model->kiemTraSDT($data['SoDienThoai'])) {
            $_SESSION['error'] = "Số điện thoại đã tồn tại trong hệ thống!";
            $this->themForm();
            return;
        }

        // Nếu có tạo tài khoản, kiểm tra thông tin
        if (!empty($data['TenDangNhap'])) {
            if ($this->model->kiemTraTenDangNhap($data['TenDangNhap'])) {
                $_SESSION['error'] = "Tên đăng nhập đã tồn tại!";
                $this->themForm();
                return;
            }
            if (!empty($data['Email']) && $this->model->kiemTraEmail($data['Email'])) {
                $_SESSION['error'] = "Email đã tồn tại!";
                $this->themForm();
                return;
            }
            if (!empty($data['CMND']) && $this->model->kiemTraCMND($data['CMND'])) {
                $_SESSION['error'] = "CMND đã tồn tại!";
                $this->themForm();
                return;
            }
        }

        $result = $this->model->themKH($data);
        
        if ($result['success']) {
            $message = "Thêm khách hàng thành công! Mã KH: " . $result['maKH'];
            if ($result['taiKhoanID']) {
                $message .= " - Tài khoản đã được tạo!";
            }
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = $result['error'] ?? "Lỗi khi thêm khách hàng!";
        }
        
        header('Location: quanlykh.php');
        exit();
    }

    // Hiển thị form sửa
    public function suaForm() {
        $maKH = $_GET['ma_kh'] ?? '';
        
        if (empty($maKH)) {
            $_SESSION['error'] = "Không tìm thấy khách hàng!";
            header('Location: quanlykh.php');
            exit();
        }

        $khachHang = $this->model->getChiTietKH($maKH);
        
        if (!$khachHang) {
            $_SESSION['error'] = "Khách hàng không tồn tại!";
            header('Location: quanlykh.php');
            exit();
        }

        $this->renderView('suakh', ['khachHang' => $khachHang]);
    }

    // Xử lý sửa KH
    public function suaKH() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_GET['ma_kh'])) {
            header('Location: quanlykh.php');
            exit();
        }

        $maKH = $_GET['ma_kh'];
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

        // Validate
        $errors = $this->validateKH($data);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->suaForm();
            return;
        }

        // Kiểm tra SĐT (trừ chính nó)
        if ($this->model->kiemTraSDT($data['SoDienThoai'], $maKH)) {
            $_SESSION['error'] = "Số điện thoại đã tồn tại trong hệ thống!";
            $this->suaForm();
            return;
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

    // Xử lý xóa KH
    public function xoaKH() {
        $maKH = $_GET['ma_kh'] ?? '';
        
        if (empty($maKH)) {
            $_SESSION['error'] = "Không tìm thấy khách hàng!";
            header('Location: quanlykh.php');
            exit();
        }

        try {
            $result = $this->model->xoaKH($maKH);
            
            if ($result) {
                $_SESSION['success'] = "Xóa khách hàng thành công!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: quanlykh.php');
        exit();
    }

    // Hiển thị danh sách tài khoản
    public function danhSachTaiKhoan() {
        $danhSachTK = $this->model->getDanhSachTaiKhoanKH();
        $this->renderView('quanlytaikhoan', ['danhSachTK' => $danhSachTK]);
    }

    // Reset mật khẩu
    public function resetMatKhau() {
        $taiKhoanID = $_GET['id'] ?? '';
        $matKhauMoi = $_POST['mat_khau_moi'] ?? '123456'; // Mặc định
        
        if (empty($taiKhoanID)) {
            $_SESSION['error'] = "Không tìm thấy tài khoản!";
            header('Location: quanlykh.php?action=taikhoan');
            exit();
        }

        $result = $this->model->resetMatKhau($taiKhoanID, $matKhauMoi);
        
        if ($result) {
            $_SESSION['success'] = "Reset mật khẩu thành công! Mật khẩu mới: $matKhauMoi";
        } else {
            $_SESSION['error'] = "Lỗi khi reset mật khẩu!";
        }
        
        header('Location: quanlykh.php?action=taikhoan');
        exit();
    }

    // ========== HELPER METHODS ==========

    private function validateKH($data) {
        $errors = [];

        if (empty($data['HoTen'])) {
            $errors[] = "Họ tên không được để trống";
        } else if (str_word_count($data['HoTen']) < 2) {
            $errors[] = "Họ tên phải có ít nhất 2 từ";
        }

        if (empty($data['SoDienThoai'])) {
            $errors[] = "Số điện thoại không được để trống";
        } else if (!preg_match('/^\d{10,11}$/', $data['SoDienThoai'])) {
            $errors[] = "Số điện thoại phải có 10-11 chữ số";
        }

        // Nếu có tạo tài khoản
        if (!empty($data['TenDangNhap'])) {
            if (empty($data['MatKhau'])) {
                $errors[] = "Mật khẩu không được để trống khi tạo tài khoản";
            } else if (strlen($data['MatKhau']) < 6) {
                $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
            }

            if (!empty($data['Email']) && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email không hợp lệ";
            }

            if (!empty($data['CMND']) && !preg_match('/^\d{9,12}$/', $data['CMND'])) {
                $errors[] = "CMND phải có 9-12 chữ số";
            }
        }

        return $errors;
    }

    private function renderView($view, $data = []) {
        extract($data);
        include_once "../view/quanly/{$view}.php";
    }
}

// ========== XỬ LÝ REQUEST ==========
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}

// Khởi tạo controller
$controller = new QuanLyKHController();

// Xác định action
$action = $_GET['action'] ?? 'index';

// Xử lý action
switch ($action) {
    case 'them':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->themForm();
        } else {
            $controller->themKH();
        }
        break;
        
    case 'sua':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->suaForm();
        } else {
            $controller->suaKH();
        }
        break;
        
    case 'xoa':
        $controller->xoaKH();
        break;
        
    case 'taikhoan':
        $controller->danhSachTaiKhoan();
        break;
        
    case 'resetpassword':
        $controller->resetMatKhau();
        break;
        
    case 'index':
    default:
        $controller->index();
        break;
}
?>