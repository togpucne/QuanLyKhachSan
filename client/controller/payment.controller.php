<?php
session_start();

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: /ABC-Resort/client/view/auth/login.php?message=Vui lòng đăng nhập để đặt phòng');
    exit;
}

require_once __DIR__ . '/../model/connectDB.php';
require_once __DIR__ . '/../model/payment.model.php';

class PaymentController
{
    private $paymentModel;
    private $conn;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $db = new Connect();
        $this->conn = $db->openConnect();
    }

    public function index()
    {
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

        // LẤY THÔNG TIN KHÁCH HÀNG TỪ DATABASE
        $customerInfo = $this->getCustomerInfo($_SESSION['user_id']);
        $bookingInfo['customerInfo'] = $customerInfo;

        // LẤY KHUYẾN MÃI TỪ DATABASE
        $promotions = $this->getPromotionsFromDB($bookingInfo['totalAmount'], $nights);
        $bookingInfo['promotions'] = $promotions;

        // Hiển thị trang thanh toán
        $this->loadView('../view/payment/index.php', $bookingInfo);
    }

    // SỬA HÀM NÀY: Lấy thông tin khách hàng và CMND
    private function getCustomerInfo($userId)
    {
        try {
            // JOIN cả hai bảng để lấy CMND từ tai_khoan và địa chỉ từ khachhang
            $sql = "SELECT 
                        kh.*, 
                        tk.Email,
                        tk.CMND,  -- LẤY CMND TỪ TAI_KHOAN
                        tk.HoTen as TenTaiKhoan,
                        tk.SoDienThoai as SDTTaiKhoan
                    FROM khachhang kh 
                    LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
                    WHERE kh.MaTaiKhoan = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $customer = $result->fetch_assoc();

                // Đảm bảo các trường có giá trị
                return [
                    'HoTen' => $customer['HoTen'] ?? $customer['TenTaiKhoan'] ?? '',
                    'SoDienThoai' => $customer['SoDienThoai'] ?? $customer['SDTTaiKhoan'] ?? '',
                    'Email' => $customer['Email'] ?? '',
                    'CMND' => $customer['CMND'] ?? '',  // TRẢ VỀ CMND
                    'DiaChi' => $customer['DiaChi'] ?? ''  // TRẢ VỀ ĐỊA CHỈ
                ];
            }

            // Nếu không có trong khachhang, lấy từ tai_khoan
            $sql2 = "SELECT * FROM tai_khoan WHERE id = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($result2->num_rows > 0) {
                $user = $result2->fetch_assoc();
                return [
                    'HoTen' => $user['HoTen'] ?? '',
                    'SoDienThoai' => $user['SoDienThoai'] ?? '',
                    'Email' => $user['Email'] ?? '',
                    'CMND' => $user['CMND'] ?? '',
                    'DiaChi' => $user['DiaChi'] ?? ''
                ];
            }

            return [];
        } catch (Exception $e) {
            error_log("Lỗi getCustomerInfo: " . $e->getMessage());
            return [];
        }
    }

    private function getPromotionsFromDB($totalAmount, $nights)
    {
        $today = date('Y-m-d');

        try {
            // Lấy tất cả khuyến mãi đang hoạt động
            $sql = "SELECT * FROM khuyenmai 
                    WHERE TrangThai = 1 
                    AND NgayBatDau <= ? 
                    AND NgayKetThuc >= ?
                    ORDER BY MucGiamGia DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $today, $today);
            $stmt->execute();
            $result = $stmt->get_result();

            $promotions = [];
            while ($row = $result->fetch_assoc()) {
                // Đảm bảo các trường có giá trị
                $row['LoaiGiamGia'] = $row['LoaiGiamGia'] ?? 'phantram';
                $row['DK_HoaDonTu'] = $row['DK_HoaDonTu'] ?? null;
                $row['DK_SoDemTu'] = $row['DK_SoDemTu'] ?? null;

                // Kiểm tra điều kiện
                $isAvailable = true;
                $reason = "";

                // Kiểm tra điều kiện hóa đơn
                if (!empty($row['DK_HoaDonTu']) && $totalAmount < $row['DK_HoaDonTu']) {
                    $isAvailable = false;
                    $reason = "Hóa đơn tối thiểu " . number_format($row['DK_HoaDonTu']) . " VND";
                }

                // Kiểm tra điều kiện số đêm
                if (!empty($row['DK_SoDemTu']) && $nights < $row['DK_SoDemTu']) {
                    $isAvailable = false;
                    $reason = "Đặt tối thiểu " . $row['DK_SoDemTu'] . " đêm";
                }

                // Tính toán số tiền giảm
                if ($row['LoaiGiamGia'] == 'phantram') {
                    $discountAmount = $totalAmount * ($row['MucGiamGia'] / 100);
                    $row['discount_amount'] = $discountAmount;
                } else {
                    $row['discount_amount'] = $row['MucGiamGia'];
                }

                $row['is_available'] = $isAvailable;
                $row['reason'] = $reason;
                $promotions[] = $row;
            }

            return $promotions;
        } catch (Exception $e) {
            error_log("Lỗi getPromotionsFromDB: " . $e->getMessage());
            return [];
        }
    }

    // Dữ liệu mẫu fallback
    private function getSamplePromotions($totalAmount, $nights)
    {
        $samplePromotions = [
            [
                'MaKM' => '16',
                'TenKhuyenMai' => 'Khuyến mãi tuần lễ vàng',
                'MucGiamGia' => '10.00',
                'LoaiGiamGia' => 'phantram',
                'DK_HoaDonTu' => 2000000,
                'DK_SoDemTu' => null,
                'MoTa' => 'Giảm 10% tất cả các loại phòng',
                'is_available' => $totalAmount >= 2000000,
                'reason' => $totalAmount >= 2000000 ? '' : 'Hóa đơn tối thiểu 2,000,000 VND'
            ]
        ];

        return $samplePromotions;
    }

    public function processPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validation cơ bản
            $required = ['customerName', 'customerPhone', 'customerEmail', 'customerIdNumber', 'paymentMethod'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Vui lòng điền đầy đủ thông tin'
                    ]);
                    exit;
                }
            }

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
                'totalAmount' => $_POST['totalAmount'],
                'discountAmount' => $_POST['discountAmount'] ?? 0,
                'finalAmount' => $_POST['finalAmount'] ?? $_POST['totalAmount'],
                'promotionId' => $_POST['promotionId'] ?? '',
                'userId' => $_SESSION['user_id']
            ];

            $result = $this->paymentModel->processBooking($paymentData);

            echo json_encode($result);
            exit;
        }
    }

    private function loadView($viewPath, $data = [])
    {
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
