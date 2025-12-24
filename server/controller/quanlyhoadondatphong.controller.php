<?php
// server/controller/quanlyhoadondatphong.controller.php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Truy cập bị từ chối']);
    exit();
}

require_once __DIR__ . '/../model/quanlyhoadondatphong.model.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Tạo model
$model = new QuanLyHoaDonDatPhongModel();

// Xử lý action
switch($action) {
    case 'chiTiet':
        xuLyChiTiet($model);
        break;
    case 'xoa':
        xuLyXoa($model);
        break;
    default:
        traVeJSON(['success' => false, 'error' => 'Action không hợp lệ'], 400);
}

function xuLyChiTiet($model) {
    if (!isset($_GET['id'])) {
        traVeJSON(['success' => false, 'error' => 'Thiếu ID hóa đơn'], 400);
        return;
    }
    
    $id = intval($_GET['id']);
    $hoadon = $model->getHoaDonById($id);
    
    if ($hoadon) {
        traVeJSON(['success' => true, 'data' => $hoadon]);
    } else {
        traVeJSON(['success' => false, 'error' => 'Không tìm thấy hóa đơn'], 404);
    }
}

function xuLyXoa($model) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        traVeJSON(['success' => false, 'error' => 'Phương thức không hợp lệ'], 405);
        return;
    }
    
    // Nhận dữ liệu từ POST
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id <= 0) {
        traVeJSON(['success' => false, 'error' => 'ID hóa đơn không hợp lệ'], 400);
        return;
    }
    
    // Kiểm tra xem hóa đơn có tồn tại không
    $hoadon = $model->getHoaDonById($id);
    if (!$hoadon) {
        traVeJSON(['success' => false, 'error' => 'Hóa đơn không tồn tại'], 404);
        return;
    }
    
    // Thực hiện xóa
    $result = $model->deleteHoaDon($id);
    
    if ($result) {
        traVeJSON(['success' => true, 'message' => 'Đã xóa hóa đơn thành công']);
    } else {
        traVeJSON(['success' => false, 'error' => 'Xóa hóa đơn thất bại'], 500);
    }
}

function traVeJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
?>