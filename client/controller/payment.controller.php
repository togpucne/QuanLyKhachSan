<?php
session_start();



// ĐƯỜNG DẪN ĐÚNG - model nằm cùng cấp với controller
require_once __DIR__ . '/../model/connectDB.php';
require_once __DIR__ . '/../model/payment.model.php';

class PaymentController {
    private $paymentModel;
    
    public function __construct() {
        $this->paymentModel = new PaymentModel();
    }
    
    public function index() {
        // Lấy thông tin từ URL parameters
        $roomId = $_GET['roomId'] ?? 0;
        $checkin = $_GET['checkin'] ?? '';
        $checkout = $_GET['checkout'] ?? '';
        $adults = $_GET['adults'] ?? 1;
        $nights = $_GET['nights'] ?? 1;
        $services = $_GET['services'] ?? '';
        
        // Lấy thông tin phòng và tính toán giá
        $bookingInfo = $this->paymentModel->getBookingInfo($roomId, $checkin, $checkout, $adults, $nights, $services);
        
        if (!$bookingInfo) {
            header('Location: /ABC-Resort/client/view/home/index.php');
            exit;
        }
        
        // Hiển thị trang thanh toán
        $this->loadView('../view/payment/index.php', $bookingInfo);
    }
    
    public function processPayment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $paymentData = [
                'roomId' => $_POST['roomId'],
                'checkin' => $_POST['checkin'],
                'checkout' => $_POST['checkout'],
                'adults' => $_POST['adults'],
                'nights' => $_POST['nights'],
                'services' => $_POST['services'] ?? '',
                'customerName' => $_POST['customerName'],
                'customerPhone' => $_POST['customerPhone'],
                'customerEmail' => $_POST['customerEmail'],
                'customerIdNumber' => $_POST['customerIdNumber'],
                'specialRequests' => $_POST['specialRequests'] ?? '',
                'paymentMethod' => $_POST['paymentMethod'],
                'totalAmount' => $_POST['totalAmount']
            ];
            
            $result = $this->paymentModel->processBooking($paymentData);
            
            if ($result['success']) {
                header('Location: /ABC-Resort/client/view/payment/payment-success.php?bookingCode=' . $result['bookingCode']);
                exit;
            } else {
                // Xử lý lỗi
                echo json_encode($result);
            }
        }
    }
    
    private function loadView($viewPath, $data = []) {
        extract($data);
        require_once $viewPath;
    }
}

// Xử lý request
$controller = new PaymentController();
$action = $_GET['action'] ?? 'index';

if ($action == 'processPayment') {
    $controller->processPayment();
} else {
    $controller->index();
}
?>