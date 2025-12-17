<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/model/connectDB.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit();
}

// Lấy ID hóa đơn
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit();
}

$bookingId = intval($_POST['id']);
$userId = $_SESSION['user_id'];

// Kết nối database
$connect = new Connect();
$conn = $connect->openConnect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
    exit();
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    // 1. Lấy MaKH của user
    $sqlCustomer = "SELECT MaKH FROM khachhang WHERE MaTaiKhoan = ?";
    $stmtCustomer = $conn->prepare($sqlCustomer);
    
    if (!$stmtCustomer) {
        throw new Exception('Lỗi prepare statement');
    }
    
    $stmtCustomer->bind_param("s", $userId);
    $stmtCustomer->execute();
    $resultCustomer = $stmtCustomer->get_result();
    
    if ($resultCustomer->num_rows == 0) {
        throw new Exception('Không tìm thấy thông tin khách hàng');
    }
    
    $customer = $resultCustomer->fetch_assoc();
    $maKhachHang = $customer['MaKH'];
    $stmtCustomer->close();
    
    // 2. Lấy thông tin hóa đơn và MÃ PHÒNG trước khi xóa
    $sqlGetInvoice = "SELECT h.*, p.MaPhong, p.TrangThai as TrangThaiPhong 
                     FROM hoadondatphong h 
                     LEFT JOIN phong p ON h.MaPhong = p.MaPhong 
                     WHERE h.Id = ? AND h.MaKhachHang = ?";
    
    $stmtGetInvoice = $conn->prepare($sqlGetInvoice);
    if (!$stmtGetInvoice) {
        throw new Exception('Lỗi prepare statement khi lấy thông tin hóa đơn');
    }
    
    $stmtGetInvoice->bind_param("is", $bookingId, $maKhachHang);
    $stmtGetInvoice->execute();
    $resultInvoice = $stmtGetInvoice->get_result();
    
    if ($resultInvoice->num_rows == 0) {
        throw new Exception('Hóa đơn không tồn tại hoặc không thuộc về bạn');
    }
    
    $invoice = $resultInvoice->fetch_assoc();
    $maPhong = $invoice['MaPhong'];
    $trangThaiPhong = $invoice['TrangThaiPhong'];
    $trangThaiHoaDon = $invoice['TrangThai'];
    
    // 3. Kiểm tra trạng thái - chỉ cho xóa nếu chưa thanh toán
    if ($trangThaiHoaDon === 'DaThanhToan') {
        throw new Exception('Không thể xóa hóa đơn đã thanh toán. Vui lòng liên hệ quản lý.');
    }
    
    // 4. Kiểm tra xem phòng có đang được sử dụng không
    if ($trangThaiPhong === 'Đang sử dụng') {
        // Nếu phòng đang sử dụng, xóa hóa đơn và CẬP NHẬT LẠI trạng thái phòng
        $sqlUpdatePhong = "UPDATE phong SET TrangThai = 'Trống' WHERE MaPhong = ?";
        $stmtUpdatePhong = $conn->prepare($sqlUpdatePhong);
        
        if (!$stmtUpdatePhong) {
            throw new Exception('Lỗi prepare statement khi cập nhật phòng');
        }
        
        $stmtUpdatePhong->bind_param("i", $maPhong);
        if (!$stmtUpdatePhong->execute()) {
            throw new Exception('Lỗi khi cập nhật trạng thái phòng: ' . $conn->error);
        }
        
        error_log("Đã cập nhật phòng {$maPhong} từ 'Đang sử dụng' thành 'Trống'");
        $stmtUpdatePhong->close();
    }
    
    // 5. Xóa hóa đơn
    $sqlDelete = "DELETE FROM hoadondatphong WHERE Id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    
    if (!$stmtDelete) {
        throw new Exception('Lỗi prepare statement khi xóa hóa đơn');
    }
    
    $stmtDelete->bind_param("i", $bookingId);
    if (!$stmtDelete->execute()) {
        throw new Exception('Lỗi khi xóa hóa đơn: ' . $conn->error);
    }
    
    $rowsDeleted = $stmtDelete->affected_rows;
    $stmtDelete->close();
    
    if ($rowsDeleted > 0) {
        // Commit transaction nếu tất cả thành công
        $conn->commit();
        
        // LOG SUCCESS
        error_log("Đã xóa hóa đơn #{$bookingId}, phòng {$maPhong} đã được cập nhật thành 'Trống'");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Xóa hóa đơn thành công. Phòng đã được cập nhật lại trạng thái.',
            'data' => [
                'invoice_id' => $bookingId,
                'room_id' => $maPhong,
                'room_status_updated' => true
            ]
        ]);
    } else {
        throw new Exception('Không có hóa đơn nào được xóa');
    }
    
} catch (Exception $e) {
    // Rollback nếu có lỗi
    if (method_exists($conn, 'rollback')) {
        $conn->rollback();
    }
    
    error_log("Lỗi xóa hóa đơn #{$bookingId}: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn) {
        $connect->closeConnect($conn);
    }
}
?>