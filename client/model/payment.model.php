<?php
require_once 'connectDB.php';

class PaymentModel
{
    private $conn;

    public function __construct()
    {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }

    public function getBookingInfo($roomId, $checkin, $checkout, $adults, $nights, $services)
    {
        // Lấy thông tin phòng
        $sql = "SELECT p.*, lp.HangPhong 
                FROM phong p 
                JOIN loaiphong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                WHERE p.MaPhong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $roomId);
        $stmt->execute();
        $result = $stmt->get_result();
        $room = $result->fetch_assoc();

        if (!$room) return false;

        // LẤY THUẾ TỪ BẢNG THUE
        $taxRate = $this->getTaxRate();
        
        // DEBUG LOG
        error_log("=== DEBUG getBookingInfo ===");
        error_log("roomId: $roomId, adults: $adults, nights: $nights");
        error_log("services param: " . $services);
        
        // Tính toán giá
        $roomPrice = $room['TongGia'] * $nights;
        $servicesPrice = 0;
        $servicesList = [];

        if (!empty($services) && $services !== '') {
            $serviceIds = explode(',', $services);
            $serviceIds = array_filter($serviceIds); // Loại bỏ giá trị rỗng
            
            error_log("serviceIds count: " . count($serviceIds));
            error_log("serviceIds: " . print_r($serviceIds, true));
            
            if (!empty($serviceIds)) {
                $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
                $sqlServices = "SELECT * FROM dichvu WHERE MaDV IN ($placeholders)";
                error_log("SQL Services: $sqlServices");
                
                $stmtServices = $this->conn->prepare($sqlServices);
                $types = str_repeat('i', count($serviceIds));
                $stmtServices->bind_param($types, ...$serviceIds);
                $stmtServices->execute();
                $servicesResult = $stmtServices->get_result();

                while ($service = $servicesResult->fetch_assoc()) {
                    // NHÂN GIÁ DỊCH VỤ VỚI SỐ NGƯỜI - FIX LỖI Ở ĐÂY
                    $servicePricePerPerson = $service['DonGia'];
                    $serviceTotalForAll = $servicePricePerPerson * $adults;
                    $servicesPrice += $serviceTotalForAll;
                    
                    // DEBUG
                    error_log("Service: " . $service['TenDV'] . 
                             ", DonGia per person: " . $servicePricePerPerson . 
                             ", x $adults = $serviceTotalForAll");
                    
                    // Lưu thông tin dịch vụ với giá đã nhân
                    $service['DonGia_DaNhan'] = $serviceTotalForAll;
                    $service['SoNguoi'] = $adults;
                    $service['DonGia_PerPerson'] = $servicePricePerPerson;
                    $servicesList[] = $service;
                }
            }
        }

        // TÍNH THUẾ THEO TỶ LỆ TỪ DATABASE
        $tax = ($roomPrice + $servicesPrice) * $taxRate;
        $totalAmount = $roomPrice + $servicesPrice + $tax;

        // DEBUG tổng
        error_log("=== TỔNG KẾT ===");
        error_log("roomPrice: $roomPrice");
        error_log("servicesPrice: $servicesPrice");
        error_log("taxRate: $taxRate");
        error_log("tax: $tax");
        error_log("totalAmount: $totalAmount");
        error_log("=================");

        return [
            'room' => $room,
            'roomName' => $room['TenPhong'] ?? 'Phòng chưa đặt tên',
            'HangPhong' => $room['HangPhong'] ?? 'Standard',
            'DienTich' => $room['DienTich'] ?? '0',
            'checkin' => $checkin,
            'checkout' => $checkout,
            'adults' => $adults,
            'nights' => $nights,
            'services' => $servicesList,
            'roomPrice' => $roomPrice,
            'servicesPrice' => $servicesPrice,
            'tax' => $tax,
            'taxRate' => $taxRate, // THÊM TRƯỜNG NÀY
            'totalAmount' => $totalAmount,
            'roomId' => $roomId
        ];
    }

    // HÀM LẤY TỶ LỆ THUẾ TỪ DATABASE
    private function getTaxRate()
    {
        try {
            $sql = "SELECT TyLeThue FROM THUE WHERE TrangThai = 1 ORDER BY NgayApDung DESC LIMIT 1";
            $result = $this->conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $taxRate = $row['TyLeThue'];
                
                // Nếu TyLeThue lưu dưới dạng phần trăm (ví dụ: 10), chia cho 100
                if ($taxRate > 1) {
                    $taxRate = $taxRate / 100;
                }
                
                error_log("Tax rate from database: " . $taxRate);
                return $taxRate;
            }
            
            // Nếu không tìm thấy, dùng mặc định 10%
            error_log("No tax rate found, using default 10%");
            return 0.1;
            
        } catch (Exception $e) {
            error_log("Error getting tax rate: " . $e->getMessage());
            return 0.1; // Mặc định 10%
        }
    }

    public function processBooking($paymentData) {
        try {
            // Tạo mã booking
            $bookingCode = 'ABC' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Lấy tỷ lệ thuế từ database
            $taxRate = $this->getTaxRate();
            
            // Ghi log thông tin
            error_log("Booking processed - Code: $bookingCode, Tax rate: $taxRate");
            
            $message = 'Đặt phòng thành công!';
            if ($paymentData['discountAmount'] > 0) {
                $message .= ' Đã áp dụng khuyến mãi giảm ' . 
                           number_format($paymentData['discountAmount']) . ' VND';
            }
            
            return [
                'success' => true,
                'bookingCode' => $bookingCode,
                'message' => $message
            ];
            
        } catch (Exception $e) {
            error_log("Error in processBooking: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi khi đặt phòng: ' . $e->getMessage()
            ];
        }
    }
}