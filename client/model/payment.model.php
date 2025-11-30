<?php
require_once 'connectDB.php';

class PaymentModel {
    private $conn;
    
    public function __construct() {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }
    
    public function getBookingInfo($roomId, $checkin, $checkout, $adults, $nights, $services) {
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
        
        // Tính toán giá
        $roomPrice = $room['TongGia'] * $nights;
        $servicesPrice = 0;
        $servicesList = [];
        
        if (!empty($services)) {
            $serviceIds = explode(',', $services);
            if (!empty($serviceIds)) {
                $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
                $sqlServices = "SELECT * FROM dichvu WHERE MaDV IN ($placeholders)";
                $stmtServices = $this->conn->prepare($sqlServices);
                $types = str_repeat('i', count($serviceIds));
                $stmtServices->bind_param($types, ...$serviceIds);
                $stmtServices->execute();
                $servicesResult = $stmtServices->get_result();
                
                while ($service = $servicesResult->fetch_assoc()) {
                    $servicesPrice += $service['DonGia'];
                    $servicesList[] = $service;
                }
            }
        }
        
        $tax = ($roomPrice + $servicesPrice) * 0.1; // 10% VAT
        $totalAmount = $roomPrice + $servicesPrice + $tax;
        
        return [
            'room' => $room,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'adults' => $adults,
            'nights' => $nights,
            'services' => $servicesList,
            'roomPrice' => $roomPrice,
            'servicesPrice' => $servicesPrice,
            'tax' => $tax,
            'totalAmount' => $totalAmount,
            'roomId' => $roomId
        ];
    }
    
    public function processBooking($paymentData) {
        // Tạo mã booking
        $bookingCode = 'ABC' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // TODO: Lưu vào database (cần tạo bảng datphong, hoadon)
        // Tạm thời return success
        return [
            'success' => true,
            'bookingCode' => $bookingCode,
            'message' => 'Đặt phòng thành công!'
        ];
    }
}
?>