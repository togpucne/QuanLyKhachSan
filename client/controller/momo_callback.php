<?php
// client/controller/momo_callback.php
session_start();
require_once __DIR__ . '/../model/connectDB.php';
require_once __DIR__ . '/../model/payment.model.php';

class MomoCallbackController
{
    private $paymentModel;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
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
            $resultCode = $_GET['resultCode'] ?? '';
            $transId = $_GET['transId'] ?? '';
            $orderId = $_GET['orderId'] ?? '';

            error_log("=== MOMO CALLBACK ===");
            error_log("maHoaDon: $maHoaDon");
            error_log("bookingCode: $bookingCode");
            error_log("amount: $amount");
            error_log("resultCode: $resultCode");
            error_log("transId: $transId");

            // Kiểm tra mã kết quả
            if ($resultCode != '0') {
                throw new Exception('Thanh toán thất bại: ' . ($_GET['message'] ?? ''));
            }

            if (!$maHoaDon || !$bookingCode || !$amount || !$transId) {
                throw new Exception('Thiếu thông tin thanh toán');
            }

            // XỬ LÝ THANH TOÁN VÀ LƯU CSDL
            $result = $this->paymentModel->processMomoPayment($maHoaDon, $amount, $transId, $orderId);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            // 3. HIỂN THỊ TRANG THÀNH CÔNG
            $this->showSuccessPage($bookingCode, $amount);
            
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
            error_log("IPN Data: " . json_encode($data));

            $resultCode = $data['resultCode'] ?? 0;
            $message = $data['message'] ?? '';
            $orderId = $data['orderId'] ?? '';
            $transId = $data['transId'] ?? '';

            // Tìm mã hóa đơn từ orderId hoặc orderInfo
            preg_match('/HD(\d+)/', ($data['orderInfo'] ?? ''), $matches);
            $maHoaDon = $matches[1] ?? 0;

            if ($resultCode == 0 && $maHoaDon > 0) {
                // Thanh toán thành công
                error_log("Momo IPN successful for invoice: $maHoaDon");
                
                $amount = $data['amount'] ?? 0;
                $this->paymentModel->processMomoPayment($maHoaDon, $amount, $transId, $orderId);

                // Trả về thành công cho Momo
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đã cập nhật trạng thái thanh toán'
                ]);
            } else {
                error_log("Momo IPN failed: $message");
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

    private function showSuccessPage($bookingCode, $amount)
    {
        // TÍNH GIỜ NHẬN PHÒNG
        $now = new DateTime();
        $now->modify('+2 hours'); // Thêm 2 giờ
        
        $formattedTime = $now->format('H:i');
        $formattedDate = $now->format('d/m/Y');
        
        $amountFormatted = number_format($amount) . ' VND';
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
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
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
                .alert-success {
                    background-color: #d4edda;
                    border-color: #c3e6cb;
                    color: #155724;
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
                    <h4 class="mb-4"><i class="fas fa-receipt me-2"></i> Thông tin thanh toán</h4>
                    
                    <div class="detail-item">
                        <span class="text-muted">Mã đặt phòng:</span>
                        <strong><?php echo htmlspecialchars($bookingCode); ?></strong>
                    </div>
                    
                    <div class="detail-item">
                        <span class="text-muted">Số tiền:</span>
                        <strong class="text-success"><?php echo $amountFormatted; ?></strong>
                    </div>
                    
                    <div class="detail-item">
                        <span class="text-muted">Phương thức:</span>
                        <strong>Ví điện tử Momo</strong>
                    </div>
                    
                    <div class="detail-item">
                        <span class="text-muted">Trạng thái:</span>
                        <span class="badge bg-success">Đã thanh toán</span>
                    </div>
                    
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle me-2"></i>
                        Thanh toán của bạn đã được xác nhận thành công!
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Thông tin đặt phòng đã được gửi đến email của bạn.
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <a href="/ABC-Resort/client/" class="btn btn-primary flex-fill">
                            <i class="fas fa-home me-2"></i> Về trang chủ
                        </a>
                        <a href="/ABC-Resort/client/view/booking/history.php" class="btn btn-outline-primary flex-fill">
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
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
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
                
                <div class="d-flex gap-2">
                    <a href="/ABC-Resort/client/view/booking/" class="btn btn-danger">
                        <i class="fas fa-arrow-left me-2"></i> Quay lại
                    </a>
                    <a href="/ABC-Resort/client/" class="btn btn-outline-danger">
                        <i class="fas fa-home me-2"></i> Trang chủ
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