<?php
require_once '../model/PhongModel.php';

class PhongController {
    private $phongModel;
    
    public function __construct() {
        $this->phongModel = new PhongModel();
    }
    
    public function index() {
        // Lấy data từ Model
        $dsPhong = $this->phongModel->getDanhSachPhong();
        
        // TRUYỀN BIẾN SANG VIEW
        $this->loadView('../view/quanlyphong.php', ['dsPhong' => $dsPhong]);
    }
    
    public function capNhatTrangThai() {
        // THÊM HEADER JSON TRƯỚC KHI TRẢ VỀ
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $maPhong = $_POST['maPhong'] ?? '';
            $trangThai = $_POST['trangThai'] ?? '';
            
            if ($this->phongModel->capNhatTrangThai($maPhong, $trangThai)) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
        }
        
        // DỪNG XỬ LÝ SAU KHI TRẢ VỀ JSON
        exit;
    }
    
    public function ghiNhanSuCo() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $maPhong = $_POST['maPhong'] ?? '';
            $moTaSuCo = $_POST['moTaSuCo'] ?? '';
            $chiPhi = $_POST['chiPhi'] ?? '';
            
            if ($this->phongModel->ghiNhanSuCo($maPhong, $moTaSuCo, $chiPhi)) {
                echo json_encode(['success' => true, 'message' => 'Ghi nhận sự cố thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ghi nhận thất bại!']);
            }
        }
        
        exit;
    }
    
    public function ghiNhanChiPhi() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $maPhong = $_POST['maPhong'] ?? '';
            $loaiChiPhi = $_POST['loaiChiPhi'] ?? '';
            $soTien = $_POST['soTien'] ?? '';
            $ghiChu = $_POST['ghiChu'] ?? '';
            
            if ($this->phongModel->ghiNhanChiPhi($maPhong, $loaiChiPhi, $soTien, $ghiChu)) {
                echo json_encode(['success' => true, 'message' => 'Ghi nhận chi phí thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ghi nhận thất bại!']);
            }
        }
        
        exit;
    }
    
    private function loadView($viewPath, $data = []) {
        extract($data);
        require_once $viewPath;
    }
}

// XỬ LÝ REQUEST
$controller = new PhongController();
$action = $_POST['action'] ?? $_GET['action'] ?? 'index';

if ($action == 'capNhatTrangThai') {
    $controller->capNhatTrangThai();
} elseif ($action == 'ghiNhanSuCo') {
    $controller->ghiNhanSuCo();
} elseif ($action == 'ghiNhanChiPhi') {
    $controller->ghiNhanChiPhi();
} else {
    $controller->index();
}
?>