<?php
// server/controller/quanlyhoadondatphong.controller.php
require_once __DIR__ . '/../model/quanlyhoadondatphong.model.php';

class QuanLyHoaDonDatPhongController {
    private $model;
    
    public function __construct() {
        $this->model = new QuanLyHoaDonDatPhongModel();
    }
    
    // Xử lý action từ request
    public function xuLyAction() {
        $action = $_GET['action'] ?? 'danhSach';
        
        switch($action) {
            case 'danhSach':
                $this->hienThiDanhSach();
                break;
            case 'chiTiet':
                $this->hienThiChiTiet();
                break;
            case 'xoa':
                $this->xuLyXoa();
                break;
            case 'doanhThu':
                $this->hienThiDoanhThu();
                break;
            case 'loc':
                $this->xuLyLoc();
                break;
            case 'timKiem':
                $this->xuLyTimKiem();
                break;
            case 'thongKe':
                $this->hienThiThongKe();
                break;
            case 'capNhatTrangThai':
                $this->xuLyCapNhatTrangThai();
                break;
            default:
                $this->hienThiDanhSach();
        }
    }
    
    // Hiển thị danh sách hóa đơn
    private function hienThiDanhSach() {
        $hoadon = $this->model->getAllHoaDon();
        $this->traVeJSON(['success' => true, 'data' => $hoadon]);
    }
    
    // Hiển thị chi tiết hóa đơn
    private function hienThiChiTiet() {
        if(!isset($_GET['id'])) {
            $this->traVeJSON(['error' => 'Thiếu ID hóa đơn'], 400);
            return;
        }
        
        $id = intval($_GET['id']);
        $hoadon = $this->model->getHoaDonById($id);
        
        if($hoadon) {
            $this->traVeJSON(['success' => true, 'data' => $hoadon]);
        } else {
            $this->traVeJSON(['error' => 'Không tìm thấy hóa đơn'], 404);
        }
    }
    
    // Xử lý xóa hóa đơn
    private function xuLyXoa() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->traVeJSON(['error' => 'Phương thức không hợp lệ'], 405);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(!isset($data['id'])) {
            $this->traVeJSON(['error' => 'Thiếu ID hóa đơn'], 400);
            return;
        }
        
        $id = intval($data['id']);
        $result = $this->model->deleteHoaDon($id);
        
        if($result) {
            $this->traVeJSON(['success' => true, 'message' => 'Đã xóa hóa đơn thành công']);
        } else {
            $this->traVeJSON(['error' => 'Xóa hóa đơn thất bại'], 500);
        }
    }
    
    // Hiển thị doanh thu
    private function hienThiDoanhThu() {
        $doanhThu = $this->model->getTongDoanhThu();
        $this->traVeJSON(['success' => true, 'data' => $doanhThu]);
    }
    
    // Xử lý lọc hóa đơn
    private function xuLyLoc() {
        $tuNgay = $_GET['tu_ngay'] ?? date('Y-m-01');
        $denNgay = $_GET['den_ngay'] ?? date('Y-m-d');
        
        $hoadon = $this->model->filterHoaDonByDate($tuNgay, $denNgay);
        $this->traVeJSON(['success' => true, 'data' => $hoadon]);
    }
    
    // Xử lý tìm kiếm
    private function xuLyTimKiem() {
        $tuKhoa = $_GET['tu_khoa'] ?? '';
        
        if(empty($tuKhoa)) {
            $this->traVeJSON(['error' => 'Vui lòng nhập từ khóa tìm kiếm'], 400);
            return;
        }
        
        $hoadon = $this->model->searchHoaDon($tuKhoa);
        $this->traVeJSON(['success' => true, 'data' => $hoadon]);
    }
    
    // Hiển thị thống kê
    private function hienThiThongKe() {
        $loai = $_GET['loai'] ?? 'thanh_toan';
        
        switch($loai) {
            case 'thanh_toan':
                $data = $this->model->getThongKeThanhToan();
                break;
            case 'theo_thang':
                $nam = $_GET['nam'] ?? date('Y');
                $data = $this->model->getThongKeTheoThang($nam);
                break;
            default:
                $data = $this->model->getThongKeThanhToan();
        }
        
        $this->traVeJSON(['success' => true, 'data' => $data]);
    }
    
    // Xử lý cập nhật trạng thái
    private function xuLyCapNhatTrangThai() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->traVeJSON(['error' => 'Phương thức không hợp lệ'], 405);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(!isset($data['id']) || !isset($data['trang_thai'])) {
            $this->traVeJSON(['error' => 'Thiếu thông tin cập nhật'], 400);
            return;
        }
        
        $id = intval($data['id']);
        $trangThai = $data['trang_thai'];
        
        $result = $this->model->updateTrangThai($id, $trangThai);
        
        if($result) {
            $this->traVeJSON(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            $this->traVeJSON(['error' => 'Cập nhật thất bại'], 500);
        }
    }
    
    // Trả về JSON response
    private function traVeJSON($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Khởi tạo controller và xử lý request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    $controller = new QuanLyHoaDonDatPhongController();
    $controller->xuLyAction();
}
?>