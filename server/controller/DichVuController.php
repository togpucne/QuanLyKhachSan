<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'model/DichVuModel.php';

class DichVuController {
    private $dichVuModel;
    
    public function __construct() {
        $this->dichVuModel = new DichVuModel();
    }
    
    public function index() {
        // Lấy danh sách dịch vụ
        $dsDichVu = $this->dichVuModel->getDanhSachDichVu();
        
        // Lấy loại dịch vụ
        $loaiDichVu = $this->dichVuModel->getLoaiDichVu();
        
        // TRUYỀN DỮ LIỆU SANG VIEW BẰNG EXTRACT
        $data = [
            'dsDichVu' => $dsDichVu,
            'loaiDichVu' => $loaiDichVu
        ];
        
        $this->loadView('view/quanlydichvu.php', $data);
    }
    
    private function loadView($viewPath, $data = []) {
        // TRÍCH XUẤT BIẾN TỪ MẢNG
        extract($data);
        
        // Include view
        include $viewPath;
    }
    
    // Các phương thức AJAX
    public function themDichVu() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Chức năng đang phát triển']);
        exit;
    }
    
    public function capNhatDichVu() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Chức năng đang phát triển']);
        exit;
    }
    
    public function xoaDichVu() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Chức năng đang phát triển']);
        exit;
    }
    
    public function timKiem() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Chức năng đang phát triển']);
        exit;
    }
    
    public function getChiTiet() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Chức năng đang phát triển']);
        exit;
    }
}

// Xử lý request
$controller = new DichVuController();
$action = $_POST['action'] ?? $_GET['action'] ?? 'index';

switch ($action) {
    case 'themDichVu':
        $controller->themDichVu();
        break;
    case 'capNhatDichVu':
        $controller->capNhatDichVu();
        break;
    case 'xoaDichVu':
        $controller->xoaDichVu();
        break;
    case 'timKiem':
        $controller->timKiem();
        break;
    case 'getChiTiet':
        $controller->getChiTiet();
        break;
    default:
        $controller->index();
        break;
}
?>