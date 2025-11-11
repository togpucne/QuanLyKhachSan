<!-- <?php
session_start(); // THÊM DÒNG NÀY
include_once '../model/quanlydoan.model.php';

class QuanLyDoanController {
    private $model;

    public function __construct() {
        $this->model = new QuanLyDoanModel();
    }

    // Hiển thị danh sách đoàn
    public function index() {
        // Kiểm tra quyền quản lý
        if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
            header('Location: ../view/login/login.php');
            exit();
        }

        $danhSachDoan = $this->model->getDanhSachDoan();
        
        // DEBUG: Kiểm tra dữ liệu từ Model
        error_log("CONTROLLER: Có " . count($danhSachDoan) . " đoàn từ Model");
        
        // TRUYỀN BIẾN SANG VIEW BẰNG EXTRACT
        $data = [
            'danhSachDoan' => $danhSachDoan,
            'keyword' => ''
        ];
        extract($data); // Biến $danhSachDoan và $keyword sẽ có trong View
        
        include_once '../view/quanly/quanlydoan.php';
        exit(); // QUAN TRỌNG: Dừng lại sau khi include
    }

    // Tìm kiếm đoàn
    public function timKiem() {
        if (isset($_GET['keyword'])) {
            $keyword = $_GET['keyword'];
            $danhSachDoan = $this->model->timKiemDoan($keyword);
            
            // TRUYỀN BIẾN SANG VIEW
            $data = [
                'danhSachDoan' => $danhSachDoan,
                'keyword' => $keyword
            ];
            extract($data);
            
            include_once '../view/quanly/quanlydoan.php';
            exit();
        }
    }

    // Thêm đoàn mới - SỬA ĐƯỜNG DẪN
    public function themDoan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'MaDoan' => $this->generateMaDoan(),
                'MaTruongDoan' => $_POST['ma_truong_doan'],
                'TenDoan' => $_POST['ten_doan'],
                'SoLuongNguoi' => $_POST['so_luong_nguoi'],
                'NgayDen' => $_POST['ngay_den'],
                'NgayDi' => $_POST['ngay_di'],
                'GhiChu' => $_POST['ghi_chu'] ?? ''
            ];

            if ($this->model->themDoan($data)) {
                $_SESSION['success'] = "Thêm đoàn thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi thêm đoàn!";
            }

            // SỬA: Chuyển hướng về CONTROLLER
            header('Location: ../../controller/quanlydoan.controller.php');
            exit();
        }
    }

    // Xóa đoàn - SỬA ĐƯỜNG DẪN
    public function xoaDoan() {
        if (isset($_GET['ma_doan'])) {
            $maDoan = $_GET['ma_doan'];
            
            if ($this->model->xoaDoan($maDoan)) {
                $_SESSION['success'] = "Xóa đoàn thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi xóa đoàn!";
            }

            // SỬA: Chuyển hướng về CONTROLLER
            header('Location: ../../controller/quanlydoan.controller.php');
            exit();
        }
    }

    // Tạo mã đoàn tự động
    private function generateMaDoan() {
        return 'DOAN' . date('YmdHis') . rand(100, 999);
    }
}

// DEBUG: Kiểm tra controller có chạy
error_log("🎯 CONTROLLER ĐƯỢC GỌI - Action: " . ($_GET['action'] ?? 'index'));

// Xử lý request
$controller = new QuanLyDoanController();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
} else {
    $controller->index();
}
?> -->