<?php
// client/controller/momo_callback.php
session_start();
require_once __DIR__ . '/../model/connectDB.php';
require_once __DIR__ . '/../model/payment.model.php';

class MomoCallbackController
{
    private $conn;

    public function __construct()
    {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }

    /**
     * Xử lý callback từ Momo sau khi thanh toán thành công
     */
    public function handleSuccess()
    {
        try {
            // Lấy thông tin từ URL parameters
            $maHoaDon = $_GET['maHoaDon'] ?? 0;
            $bookingCode = $_GET['bookingCode'] ?? '';
            $amount = $_GET['amount'] ?? 0;

            if (!$maHoaDon || !$bookingCode) {
                throw new Exception('Thiếu thông tin thanh toán');
            }

            // 1. CẬP NHẬT TRẠNG THÁI THANH TOÁN
            $this->updatePaymentStatus($maHoaDon);

            // 2. LẤY THÔNG TIN HÓA ĐƠN ĐỂ HIỂN THỊ
            $invoiceInfo = $this->getInvoiceInfo($maHoaDon);

            // 3. HIỂN THỊ TRANG THÀNH CÔNG
            $this->showSuccessPage($invoiceInfo, $bookingCode);
        } catch (Exception $e) {
            $this->showErrorPage($e->getMessage());
        }
    }

    /**
     * Xử lý khi thanh toán thất bại
     */
    public function handleError()
    {
        $errorMessage = $_GET['message'] ?? 'Thanh toán thất bại';
        $this->showErrorPage($errorMessage);
    }

    /**
     * Xử lý IPN (Instant Payment Notification) từ Momo
     */
    public function handleIPN()
    {
        try {
            // Lấy dữ liệu từ Momo
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data) {
                throw new Exception('Không có dữ liệu từ Momo');
            }

            error_log("=== MOMO IPN CALLBACK ===");
            error_log("Data: " . json_encode($data));

            // Kiểm tra chữ ký (nếu cần)
            $partnerCode = $data['partnerCode'] ?? '';
            $orderId = $data['orderId'] ?? '';
            $requestId = $data['requestId'] ?? '';
            $amount = $data['amount'] ?? 0;
            $orderInfo = $data['orderInfo'] ?? '';
            $orderType = $data['orderType'] ?? '';
            $transId = $data['transId'] ?? '';
            $resultCode = $data['resultCode'] ?? 0;
            $message = $data['message'] ?? '';

            // Tìm mã hóa đơn từ orderInfo
            preg_match('/HD(\d+)/', $orderInfo, $matches);
            $maHoaDon = $matches[1] ?? 0;

            if ($resultCode == 0) {
                // Thanh toán thành công
                error_log("Momo payment successful for invoice: $maHoaDon");
                $this->updatePaymentStatus($maHoaDon);

                // Trả về thành công cho Momo
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đã cập nhật trạng thái thanh toán'
                ]);
            } else {
                error_log("Momo payment failed: $message");

                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
        } catch (Exception $e) {
            error_log("IPN Error: " . $e->getMessage());

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function updatePaymentStatus($maHoaDon)
    {
        // Bắt đầu transaction
        $this->conn->begin_transaction();

        try {
            // 1. CẬP NHẬT TRẠNG THÁI HÓA ĐƠN
            // Bảng hoadondatphong KHÔNG CÓ updated_at, chỉ có NgayTao
            $sql1 = "UPDATE hoadondatphong 
                SET TrangThai = 'DaThanhToan',
                    PhuongThucThanhToan = 'Momo'
                WHERE Id = ?";

            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bind_param("i", $maHoaDon);

            if (!$stmt1->execute()) {
                throw new Exception("Không thể cập nhật hóa đơn: " . $stmt1->error);
            }

            error_log("Đã cập nhật hóa đơn $maHoaDon thành 'DaThanhToan' (Momo)");

            // 2. CẬP NHẬT TRẠNG THÁI PHÒNG THÀNH "Đang sử dụng"
            $this->updateRoomStatusFromInvoice($maHoaDon);

            // 3. CẬP NHẬT KHÁCH HÀNG NẾU CHECK-IN NGAY
            $this->updateCustomerStatusIfCheckin($maHoaDon);

            // Commit transaction
            $this->conn->commit();

            error_log("Transaction thành công cho hóa đơn $maHoaDon");
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->conn->rollback();
            throw new Exception("Lỗi cập nhật: " . $e->getMessage());
        }
    }
    private function updateRoomStatusFromInvoice($maHoaDon)
    {
        // Lấy mã phòng từ hóa đơn
        $sql = "SELECT MaPhong FROM hoadondatphong WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maHoaDon);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $maPhong = $row['MaPhong'];

            // KHÔNG CÓ updated_at trong bảng phong, chỉ cập nhật TrangThai
            $sqlUpdate = "UPDATE phong SET TrangThai = 'Đang sử dụng' WHERE MaPhong = ?";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("i", $maPhong);

            if ($stmtUpdate->execute()) {
                error_log("Đã cập nhật phòng $maPhong thành 'Đang sử dụng' (Momo)");
                return true;
            } else {
                error_log("Lỗi cập nhật phòng: " . $stmtUpdate->error);
                return false;
            }
        }

        return false;
    }
    private function updateCustomerStatusIfCheckin($maHoaDon)
    {
        // Kiểm tra xem có phải check-in ngay không
        $sql = "SELECT h.MaKhachHang, h.DanhSachKhach, h.NgayNhan 
            FROM hoadondatphong h 
            WHERE h.Id = ? AND h.TrangThai = 'DaThanhToan'"; // CHỈ kiểm tra khi đã thanh toán

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maHoaDon);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $ngayNhan = $row['NgayNhan'];
            $ngayHienTai = date('Y-m-d');

            // Nếu check-in ngay hôm nay, cập nhật trạng thái khách hàng
            if ($ngayNhan == $ngayHienTai) {
                $danhSachKhach = json_decode($row['DanhSachKhach'], true);
                $maKHList = [$row['MaKhachHang']];

                if (is_array($danhSachKhach)) {
                    foreach ($danhSachKhach as $khach) {
                        if (isset($khach['MaKH'])) {
                            $maKHList[] = $khach['MaKH'];
                        }
                    }
                }

                // Cập nhật trạng thái khách hàng
                if (!empty($maKHList)) {
                    $placeholders = str_repeat('?,', count($maKHList) - 1) . '?';
                    $sqlUpdate = "UPDATE khachhang SET TrangThai = 'Đang ở' WHERE MaKH IN ($placeholders)";

                    $stmtUpdate = $this->conn->prepare($sqlUpdate);
                    $types = str_repeat('s', count($maKHList));
                    $stmtUpdate->bind_param($types, ...$maKHList);
                    $stmtUpdate->execute();

                    error_log("Đã cập nhật " . count($maKHList) . " khách hàng sang 'Đang ở' (Đã thanh toán Momo & check-in)");
                }
            }
        }
    }

    private function getInvoiceInfo($maHoaDon)
    {
        $sql = "SELECT h.*, p.SoPhong, p.roomName 
                FROM hoadondatphong h
                LEFT JOIN phong p ON h.MaPhong = p.MaPhong
                WHERE h.Id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maHoaDon);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Không tìm thấy thông tin hóa đơn');
        }

        return $result->fetch_assoc();
    }

    private function showSuccessPage($invoiceInfo, $bookingCode)
    {
?>
        <!DOCTYPE html>
        <html lang="vi">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Thanh toán thành công - Tỏa Sáng Resort</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }

                .success-card {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-width: 600px;
                    width: 100%;
                    overflow: hidden;
                }

                .success-header {
                    background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
                    color: white;
                    padding: 40px 20px;
                    text-align: center;
                }

                .success-icon {
                    font-size: 80px;
                    margin-bottom: 20px;
                    animation: bounce 1s infinite alternate;
                }

                @keyframes bounce {
                    from {
                        transform: translateY(0);
                    }

                    to {
                        transform: translateY(-20px);
                    }
                }

                .booking-details {
                    padding: 30px;
                }

                .detail-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #eee;
                }

                .detail-item:last-child {
                    border-bottom: none;
                }

                .btn-group {
                    display: flex;
                    gap: 10px;
                    margin-top: 30px;
                }

                .btn-group .btn {
                    flex: 1;
                }
            </style>
        </head>

        <body>
            <div class="success-card">
                <div class="success-header">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1 class="mb-3">🎉 Thanh toán thành công!</h1>
                    <p class="mb-0">Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi</p>
                </div>

                <div class="booking-details">
                    <h4 class="mb-4"><i class="fas fa-receipt me-2"></i> Thông tin đặt phòng</h4>

                    <div class="detail-item">
                        <span class="text-muted">Mã đặt phòng:</span>
                        <strong><?php echo $bookingCode; ?></strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Phòng:</span>
                        <strong><?php echo htmlspecialchars($invoiceInfo['roomName'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($invoiceInfo['SoPhong'] ?? 'N/A'); ?>)</strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Ngày nhận phòng:</span>
                        <strong><?php echo date('d/m/Y', strtotime($invoiceInfo['NgayNhan'])); ?></strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Ngày trả phòng:</span>
                        <strong><?php echo date('d/m/Y', strtotime($invoiceInfo['NgayTra'])); ?></strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Số đêm:</span>
                        <strong><?php echo $invoiceInfo['SoDem']; ?> đêm</strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Số khách:</span>
                        <strong><?php echo $invoiceInfo['SoNguoi']; ?> người</strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Tổng tiền:</span>
                        <strong class="text-success"><?php echo number_format($invoiceInfo['TongTien']); ?> VND</strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Phương thức:</span>
                        <strong>Ví điện tử Momo</strong>
                    </div>

                    <div class="detail-item">
                        <span class="text-muted">Trạng thái:</span>
                        <span class="badge bg-success">Đã thanh toán</span>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Thông tin đặt phòng đã được gửi đến email của bạn. Vui lòng đến quầy lễ tân khi nhận phòng.
                    </div>

                    <div class="btn-group">
                        <a href="/ABC-Resort/client/" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i> Về trang chủ
                        </a>
                        <a href="/ABC-Resort/client/view/booking/history.php" class="btn btn-outline-primary">
                            <i class="fas fa-history me-2"></i> Xem lịch sử
                        </a>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>

        </html>
    <?php
    }

    private function showErrorPage($message)
    {
    ?>
        <!DOCTYPE html>
        <html lang="vi">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Thanh toán thất bại - Tỏa Sáng Resort</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body {
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }

                .error-card {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-width: 500px;
                    width: 100%;
                    padding: 40px;
                    text-align: center;
                }

                .error-icon {
                    font-size: 80px;
                    color: #dc3545;
                    margin-bottom: 20px;
                }
            </style>
        </head>

        <body>
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h1 class="text-danger mb-4">Thanh toán thất bại</h1>
                <p class="mb-4"><?php echo htmlspecialchars($message); ?></p>

                <div class="btn-group mt-4">
                    <a href="/ABC-Resort/client/view/booking/" class="btn btn-danger me-2">
                        <i class="fas fa-arrow-left me-2"></i> Quay lại đặt phòng
                    </a>
                    <a href="/ABC-Resort/client/" class="btn btn-outline-danger">
                        <i class="fas fa-home me-2"></i> Về trang chủ
                    </a>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>

        </html>
<?php
    }
}

// Xử lý request
$action = $_GET['action'] ?? 'success';

$controller = new MomoCallbackController();

if ($action === 'error') {
    $controller->handleError();
} elseif ($action === 'ipn') {
    $controller->handleIPN();
} else {
    $controller->handleSuccess();
}
?>