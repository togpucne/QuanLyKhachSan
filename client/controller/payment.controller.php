<?php
session_start();

// ĐẢM BẢO KHÔNG CÓ LỖI PHP XUẤT HIỆN TRONG OUTPUT
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để đặt phòng'
    ]);
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
        try {
            $this->paymentModel = new PaymentModel();
            $db = new Connect();
            $this->conn = $db->openConnect();
        } catch (Exception $e) {
            error_log("Lỗi khởi tạo PaymentController: " . $e->getMessage());
        }
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

    public function processPayment()
    {
        // ĐẢM BẢO CHỈ TRẢ VỀ JSON
        ob_clean(); // Xóa buffer output
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new Exception('Phương thức không hợp lệ');
            }

            // Nhận dữ liệu JSON từ request
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Dữ liệu JSON không hợp lệ');
            }

            // Validation cơ bản
            $required = ['customerName', 'customerPhone', 'customerEmail', 'paymentMethod', 'roomId', 'checkin', 'checkout', 'adults'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception('Vui lòng điền đầy đủ thông tin: ' . $field);
                }
            }

            // Thêm trường guestName, guestPhone, guestAddress nếu chưa có
            if (!isset($input['guestName'])) {
                $input['guestName'] = [];
                $input['guestPhone'] = [];
                $input['guestAddress'] = [];
            }

            $paymentData = [
                'roomId' => $input['roomId'],
                'checkin' => $input['checkin'],
                'checkout' => $input['checkout'],
                'adults' => $input['adults'],
                'nights' => $input['nights'] ?? 1,
                'services' => $input['services'] ?? '',
                'customerName' => $input['customerName'],
                'customerPhone' => $input['customerPhone'],
                'customerEmail' => $input['customerEmail'],
                'customerIdNumber' => $input['customerIdNumber'] ?? '',
                'specialRequests' => $input['specialRequests'] ?? '',
                'paymentMethod' => $input['paymentMethod'],
                'totalAmount' => $input['totalAmount'] ?? 0,
                'discountAmount' => $input['discountAmount'] ?? 0,
                'finalAmount' => $input['finalAmount'] ?? ($input['totalAmount'] ?? 0),
                'promotionId' => $input['promotionId'] ?? '',
                'guests' => $input['guests'] ?? [],
                'address' => $input['address'] ?? '',
                'nonSmoking' => $input['nonSmoking'] ?? false,
                'userId' => $_SESSION['user_id'],
                'guestName' => $input['guestName'] ?? [],
                'guestPhone' => $input['guestPhone'] ?? [],
                'guestAddress' => $input['guestAddress'] ?? [],
                'roomPrice' => $input['roomPrice'] ?? 0,
                'servicesPrice' => $input['servicesPrice'] ?? 0
            ];

            if (!$this->paymentModel) {
                throw new Exception('Không thể khởi tạo model');
            }

            $result = $this->paymentModel->processBooking($paymentData);
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            // Trả về lỗi dưới dạng JSON
            error_log("Lỗi processPayment: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi đặt phòng: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }

    // ... (giữ nguyên các hàm getCustomerInfo, getCustomerInfoFallback, getPromotionsFromDB, loadView)
    private function getCustomerInfo($userId)
    {
        try {
            // PHƯƠNG PHÁP ĐÚNG: Bắt đầu từ tai_khoan, LEFT JOIN khachhang
            $sql = "SELECT 
                    -- Từ bảng tai_khoan (LUÔN CÓ với user đã login)
                    tk.id,
                    tk.Email,
                    tk.CMND,           -- CMND từ tai_khoan
                    tk.HoTen as Ten_TK, -- Họ tên từ tài khoản
                    tk.SoDienThoai as SDT_TK, -- SĐT từ tài khoản
                    
                    -- Từ bảng khachhang (CÓ THỂ NULL)
                    kh.HoTen as Ten_KH,
                    kh.SoDienThoai as SDT_KH,
                    kh.DiaChi,         -- Địa chỉ từ khachhang
                    kh.MaKH,
                    kh.TrangThai
                    
                FROM tai_khoan tk 
                LEFT JOIN khachhang kh ON tk.id = kh.MaTaiKhoan 
                WHERE tk.id = ?";  // QUAN TRỌNG: Điều kiện trên tk.id

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();

                // Ghi log để debug
                error_log("=== DEBUG CUSTOMER INFO ===");
                error_log("User ID: " . $userId);
                error_log("CMND từ DB: " . ($data['CMND'] ?? 'NULL'));
                error_log("DiaChi từ DB: " . ($data['DiaChi'] ?? 'NULL'));
                error_log("Ten_TK từ DB: " . ($data['Ten_TK'] ?? 'NULL'));
                error_log("Ten_KH từ DB: " . ($data['Ten_KH'] ?? 'NULL'));
                error_log("========================");

                // Trả về thông tin hợp nhất
                return [
                    'HoTen' => !empty($data['Ten_KH']) ? $data['Ten_KH'] : (!empty($data['Ten_TK']) ? $data['Ten_TK'] : ($_SESSION['user_name'] ?? '')),

                    'SoDienThoai' => !empty($data['SDT_KH']) ? $data['SDT_KH'] : (!empty($data['SDT_TK']) ? $data['SDT_TK'] : ''),

                    'Email' => $data['Email'] ?? $_SESSION['email'] ?? '',

                    // CMND từ tai_khoan
                    'CMND' => $data['CMND'] ?? '',

                    // Địa chỉ từ khachhang
                    'DiaChi' => $data['DiaChi'] ?? '',

                    // Thông tin debug
                    'debug' => [
                        'has_tai_khoan' => !empty($data['id']),
                        'has_khachhang' => !empty($data['MaKH']),
                        'cmnd_exists' => !empty($data['CMND']),
                        'diachi_exists' => !empty($data['DiaChi'])
                    ]
                ];
            }

            // Nếu không tìm thấy, fallback về session
            return $this->getCustomerInfoFallback($userId);
        } catch (Exception $e) {
            error_log("Lỗi getCustomerInfo: " . $e->getMessage());
            return $this->getCustomerInfoFallback($userId);
        }
    }

    // Hàm fallback
    private function getCustomerInfoFallback($userId)
    {
        try {
            // Thử lấy từ tai_khoan trước
            $sql_tk = "SELECT Email, CMND, HoTen, SoDienThoai FROM tai_khoan WHERE id = ?";
            $stmt_tk = $this->conn->prepare($sql_tk);
            $stmt_tk->bind_param("i", $userId);
            $stmt_tk->execute();
            $result_tk = $stmt_tk->get_result();

            if ($result_tk->num_rows > 0) {
                $tk_data = $result_tk->fetch_assoc();

                // Thử lấy địa chỉ từ khachhang
                $sql_kh = "SELECT DiaChi FROM khachhang WHERE MaTaiKhoan = ?";
                $stmt_kh = $this->conn->prepare($sql_kh);
                $stmt_kh->bind_param("i", $userId);
                $stmt_kh->execute();
                $result_kh = $stmt_kh->get_result();
                $kh_data = $result_kh->fetch_assoc() ?? [];

                return [
                    'HoTen' => $tk_data['HoTen'] ?? $_SESSION['user_name'] ?? '',
                    'SoDienThoai' => $tk_data['SoDienThoai'] ?? '',
                    'Email' => $tk_data['Email'] ?? $_SESSION['email'] ?? '',
                    'CMND' => $tk_data['CMND'] ?? '',
                    'DiaChi' => $kh_data['DiaChi'] ?? ''
                ];
            }

            // Cuối cùng lấy từ session
            return [
                'HoTen' => $_SESSION['user_name'] ?? '',
                'SoDienThoai' => $_SESSION['phone'] ?? '',
                'Email' => $_SESSION['email'] ?? '',
                'CMND' => '',
                'DiaChi' => ''
            ];
        } catch (Exception $e) {
            error_log("Lỗi fallback: " . $e->getMessage());
            return [
                'HoTen' => $_SESSION['user_name'] ?? '',
                'SoDienThoai' => $_SESSION['phone'] ?? '',
                'Email' => $_SESSION['email'] ?? '',
                'CMND' => '',
                'DiaChi' => ''
            ];
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
                $row['GiamGiaToiDa'] = $row['GiamGiaToiDa'] ?? 0;

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

                // Tính toán số tiền giảm THỰC TẾ
                $discountAmount = 0;
                if ($row['LoaiGiamGia'] == 'phantram') {
                    // Tính % giảm
                    $discountPercent = $row['MucGiamGia'] / 100;
                    $discountAmount = $totalAmount * $discountPercent;
                    
                    // Áp dụng giới hạn tối đa nếu có
                    if ($row['GiamGiaToiDa'] > 0 && $discountAmount > $row['GiamGiaToiDa']) {
                        $discountAmount = $row['GiamGiaToiDa'];
                    }
                } else {
                    // Giảm theo số tiền cố định
                    $discountAmount = $row['MucGiamGia'];
                    if ($row['GiamGiaToiDa'] > 0 && $discountAmount > $row['GiamGiaToiDa']) {
                        $discountAmount = $row['GiamGiaToiDa'];
                    }
                }

                $row['discount_amount'] = $discountAmount;
                $row['is_available'] = $isAvailable;
                $row['reason'] = $reason;
                
                // DEBUG LOG
                error_log("Promotion: " . $row['TenKhuyenMai'] . 
                         ", Type: " . $row['LoaiGiamGia'] . 
                         ", Discount: " . $row['MucGiamGia'] . 
                         ", Max: " . $row['GiamGiaToiDa'] . 
                         ", Calculated: " . $discountAmount);
                
                $promotions[] = $row;
            }

            return $promotions;
        } catch (Exception $e) {
            error_log("Lỗi getPromotionsFromDB: " . $e->getMessage());
            return [];
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


?>