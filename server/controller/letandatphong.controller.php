<?php
// server/controller/letandatphong.controller.php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'letan') {
    http_response_code(403);
    echo json_encode(['error' => 'Truy cập bị từ chối']);
    exit();
}

require_once __DIR__ . '/../model/letandatphong.model.php';

// Xử lý action
$action = $_GET['action'] ?? '';

if ($action === 'chitiet') {
    // Xem chi tiết hóa đơn
    if (!isset($_GET['id'])) {
        echo json_encode(['error' => 'Thiếu ID hóa đơn'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $id = intval($_GET['id']);
    $model = new LetanDatPhongModel();
    $hoadon = $model->getHoaDonById($id);
    
    if ($hoadon) {
        // Xử lý dữ liệu trước khi trả về
        foreach ($hoadon as $key => $value) {
            if (is_string($value)) {
                $hoadon[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
        }
        echo json_encode($hoadon, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Không tìm thấy hóa đơn'], JSON_UNESCAPED_UNICODE);
    }
    
} elseif ($action === 'capnhat') {
    // Cập nhật trạng thái
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Phương thức không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Nhận dữ liệu JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Thử nhận dữ liệu form
        $input = $_POST;
    }
    
    if (!isset($input['id']) || !isset($input['trangthai'])) {
        echo json_encode(['error' => 'Thiếu thông tin cập nhật'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $id = intval($input['id']);
    $trangthai = $input['trangthai'];
    
    // Kiểm tra trạng thái hợp lệ
    if (!in_array($trangthai, ['DaThanhToan', 'ChuaThanhToan'])) {
        echo json_encode(['error' => 'Trạng thái không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $model = new LetanDatPhongModel();
    $result = $model->updateTrangThai($id, $trangthai);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật thành công'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Cập nhật thất bại'], JSON_UNESCAPED_UNICODE);
    }
    
} else {
    echo json_encode(['error' => 'Action không hợp lệ'], JSON_UNESCAPED_UNICODE);
}
?>