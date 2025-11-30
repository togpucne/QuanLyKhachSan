<?php
require_once '../model/buongphongQLPhong.model.php';

class PhongController {
    private $phongModel;
    
    public function __construct() {
        $this->phongModel = new PhongModel();
    }
    
    public function index() {
        // Lấy data từ Model
        $dsPhong = $this->phongModel->getDanhSachPhong();
        
        // TRUYỀN BIẾN SANG VIEW
        $this->loadView('../view/buongphong/quanlyphong.php', ['dsPhong' => $dsPhong]);
    }
    
    public function capNhatTrangThai() {
        // THÊM HEADER JSON TRƯỚC KHI TRẢ VỀ
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $maPhong = $_POST['maPhong'] ?? '';
            $trangThai = $_POST['trangThai'] ?? '';
            $ghiChuKyThuat = $_POST['ghiChuKyThuat'] ?? ''; // THÊM DÒNG NÀY
            $lyDo = $_POST['lyDo'] ?? ''; // THÊM DÒNG NÀY
            
            // DEBUG: KIỂM TRA DỮ LIỆU NHẬN ĐƯỢC
            error_log("=== CONTROLLER DEBUG ===");
            error_log("maPhong: " . $maPhong);
            error_log("trangThai: " . $trangThai);
            error_log("ghiChuKyThuat: " . $ghiChuKyThuat);
            error_log("lyDo: " . $lyDo);
            
            // Lấy mã nhân viên từ session (tạm thời dùng 1)
            $maNhanVien = 1; // TODO: Lấy từ session sau
            
            // SỬA: THÊM 2 THAM SỐ CUỐI
            $result = $this->phongModel->capNhatTrangThai($maPhong, $trangThai, $maNhanVien, $lyDo, $ghiChuKyThuat);
            
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
        }
        
        // DỪNG XỬ LÝ SAU KHI TRẢ VỀ JSON
        exit;
    }
    
    // Tạm thời comment các hàm chưa dùng
    /*
    public function ghiNhanSuCo() {
        // Sẽ làm sau
    }
    
    public function ghiNhanChiPhi() {
        // Sẽ làm sau
    }
    */
    
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
} 
/*
elseif ($action == 'ghiNhanSuCo') {
    $controller->ghiNhanSuCo();
} elseif ($action == 'ghiNhanChiPhi') {
    $controller->ghiNhanChiPhi();
} 
*/
else {
    $controller->index();
}
?>