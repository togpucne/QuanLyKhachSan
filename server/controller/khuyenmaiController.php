<?php
session_start();

class KhuyenMaiController {
    private $khuyenMaiModel;

    public function __construct() {
        // Include model
        require_once '../model/KhuyenMaiModel.php';
        $this->khuyenMaiModel = new KhuyenMaiModel();
    }

    // Hiển thị trang quản lý khuyến mãi
    public function index() {
        // Lấy dữ liệu khuyến mãi từ model
        $khuyenMais = $this->khuyenMaiModel->getAllKhuyenMai();
        
        // Include view và truyền dữ liệu
        require_once '../view/khuyenmai.php';
    }

    // Thêm khuyến mãi mới
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tenKM = $_POST['ten_khuyenmai'] ?? '';
            $mucGiamGia = $_POST['muc_giamgia'] ?? '';
            $ngayBatDau = $_POST['ngay_batdau'] ?? '';
            $ngayKetThuc = $_POST['ngay_ketthuc'] ?? '';
            $moTa = $_POST['mo_ta'] ?? '';
            
            // CỨNG MÃ NHÂN VIÊN LÀ 3
            $maNVTao = 3;

            if (!empty($tenKM) && !empty($mucGiamGia) && !empty($ngayBatDau) && !empty($ngayKetThuc)) {
                $result = $this->khuyenMaiModel->addKhuyenMai($tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $maNVTao);
                
                if ($result) {
                    $_SESSION['success'] = "Thêm khuyến mãi thành công!";
                } else {
                    $_SESSION['error'] = "Thêm khuyến mãi thất bại!";
                }
            } else {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
            }
            
            // SỬA LẠI: Redirect về VIEW thay vì controller
            header('Location: ../view/khuyenmai.php');
            exit();
        }
    }

    // Sửa khuyến mãi
    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maKM = $_POST['ma_km'] ?? '';
            $tenKM = $_POST['ten_khuyenmai'] ?? '';
            $mucGiamGia = $_POST['muc_giamgia'] ?? '';
            $ngayBatDau = $_POST['ngay_batdau'] ?? '';
            $ngayKetThuc = $_POST['ngay_ketthuc'] ?? '';
            $moTa = $_POST['mo_ta'] ?? '';

            if (!empty($maKM) && !empty($tenKM) && !empty($mucGiamGia)) {
                $result = $this->khuyenMaiModel->updateKhuyenMai($maKM, $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa);
                
                if ($result) {
                    $_SESSION['success'] = "Cập nhật khuyến mãi thành công!";
                } else {
                    $_SESSION['error'] = "Cập nhật khuyến mãi thất bại!";
                }
            } else {
                $_SESSION['error'] = "Thiếu thông tin cần thiết!";
            }
            
            // SỬA LẠI: Redirect về VIEW
            header('Location: ../view/khuyenmai.php');
            exit();
        }
    }

    // Xóa khuyến mãi
    public function delete() {
        if (isset($_GET['id'])) {
            $maKM = $_GET['id'];
            $result = $this->khuyenMaiModel->deleteKhuyenMai($maKM);
            
            if ($result) {
                $_SESSION['success'] = "Xóa khuyến mãi thành công!";
            } else {
                $_SESSION['error'] = "Xóa khuyến mãi thất bại!";
            }
        } else {
            $_SESSION['error'] = "Không tìm thấy khuyến mãi để xóa!";
        }
        
        // SỬA LẠI: Redirect về VIEW
        header('Location: ../view/khuyenmai.php');
        exit();
    }

    // Xóa nhiều khuyến mãi
    public function deleteMultiple() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids'])) {
            $selectedIds = $_POST['selected_ids'];
            $successCount = 0;
            
            foreach ($selectedIds as $id) {
                $result = $this->khuyenMaiModel->deleteKhuyenMai($id);
                if ($result) $successCount++;
            }
            
            if ($successCount > 0) {
                $_SESSION['success'] = "Đã xóa thành công $successCount khuyến mãi!";
            } else {
                $_SESSION['error'] = "Xóa khuyến mãi thất bại!";
            }
        } else {
            $_SESSION['error'] = "Vui lòng chọn khuyến mãi để xóa!";
        }
        
        // SỬA LẠI: Redirect về VIEW
        header('Location: ../view/khuyenmai.php');
        exit();
    }
}

// Xử lý request
$action = $_GET['action'] ?? 'index';
$controller = new KhuyenMaiController();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    $controller->index();
}
?>