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

// Lấy MaKH của user
$sqlCustomer = "SELECT MaKH FROM khachhang WHERE MaTaiKhoan = ?";
$stmtCustomer = $conn->prepare($sqlCustomer);

if (!$stmtCustomer) {
    echo json_encode(['success' => false, 'message' => 'Lỗi prepare statement']);
    $connect->closeConnect($conn);
    exit();
}

$stmtCustomer->bind_param("s", $userId);
$stmtCustomer->execute();
$resultCustomer = $stmtCustomer->get_result();

if ($resultCustomer->num_rows > 0) {
    $customer = $resultCustomer->fetch_assoc();
    $maKhachHang = $customer['MaKH'];
    
    // Kiểm tra xem hóa đơn có thuộc về user này không
    $sqlCheck = "SELECT Id, TrangThai FROM hoadondatphong WHERE Id = ? AND MaKhachHang = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    
    if (!$stmtCheck) {
        echo json_encode(['success' => false, 'message' => 'Lỗi prepare statement']);
    } else {
        $stmtCheck->bind_param("is", $bookingId, $maKhachHang);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows > 0) {
            $invoice = $resultCheck->fetch_assoc();
            
            // Kiểm tra trạng thái - chỉ cho xóa nếu chưa thanh toán
            if ($invoice['TrangThai'] === 'DaThanhToan') {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa hóa đơn đã thanh toán']);
            } else {
                // Xóa hóa đơn
                $sqlDelete = "DELETE FROM hoadondatphong WHERE Id = ?";
                $stmtDelete = $conn->prepare($sqlDelete);
                
                if (!$stmtDelete) {
                    echo json_encode(['success' => false, 'message' => 'Lỗi prepare statement']);
                } else {
                    $stmtDelete->bind_param("i", $bookingId);
                    
                    if ($stmtDelete->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Xóa hóa đơn thành công']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa hóa đơn: ' . $conn->error]);
                    }
                    
                    $stmtDelete->close();
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Hóa đơn không tồn tại hoặc không thuộc về bạn']);
        }
        
        $stmtCheck->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin khách hàng']);
}

$stmtCustomer->close();
$connect->closeConnect($conn);
?>