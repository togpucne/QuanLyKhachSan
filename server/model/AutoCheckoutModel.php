<?php
require_once 'connectDB.php';

class AutoCheckoutModel
{
    private $conn;

    public function __construct()
    {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }

  public function autoUpdateStatus()
{
    $results = [
        'total_invoices' => 0,
        'updated_rooms' => 0,
        'updated_customers' => 0,
        'details' => []
    ];

    try {
        // **LOGIC MỚI: Tìm hóa đơn đã hết hạn, KHÔNG quan tâm trạng thái phòng hiện tại**
        $sql = "SELECT 
                    h.Id,
                    h.MaPhong,
                    h.MaKhachHang,
                    h.DanhSachKhach,
                    h.NgayTra,
                    h.TrangThai as TrangThaiHoaDon,
                    p.SoPhong,
                    p.TrangThai as TrangThaiPhong,
                    kh.TrangThai as TrangThaiKhachHang
                FROM hoadondatphong h
                LEFT JOIN phong p ON h.MaPhong = p.MaPhong
                LEFT JOIN khachhang kh ON h.MaKhachHang = kh.MaKH
                WHERE h.NgayTra <= NOW()
                AND h.TrangThai = 'DaThanhToan'
                ORDER BY h.NgayTra ASC";

        $result = $this->conn->query($sql);
        
        echo "<h3>🔍 Hóa đơn đã hết hạn: " . $result->num_rows . "</h3>";
        
        if ($result->num_rows > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Phòng</th><th>Ngày Trả</th><th>Trạng thái Phòng</th><th>Trạng thái Khách</th><th>Xử lý</th></tr>";
        }
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Id'] . "</td>";
            echo "<td>" . $row['SoPhong'] . "</td>";
            echo "<td>" . $row['NgayTra'] . "</td>";
            echo "<td>" . $row['TrangThaiPhong'] . "</td>";
            echo "<td>" . $row['TrangThaiKhachHang'] . "</td>";
            
            $results['total_invoices']++;
            $invoice_detail = [
                'invoice_id' => $row['Id'],
                'room' => $row['SoPhong'],
                'checkout_time' => $row['NgayTra'],
                'updated_customers' => []
            ];
            
            $room_updated = false;
            $customer_updated = false;
            
            // 1. CẬP NHẬT PHÒNG (nếu cần)
            if ($row['TrangThaiPhong'] == 'Đang sử dụng') {
                $this->updateRoomStatus($row['MaPhong']);
                $results['updated_rooms']++;
                $room_updated = true;
                echo "<td style='color: red;'>✅ Phòng: Đang sử dụng → Trống</td>";
            } else {
                echo "<td style='color: gray;'>⏭️ Phòng đã: " . $row['TrangThaiPhong'] . "</td>";
            }
            
            // 2. CẬP NHẬT KHÁCH HÀNG CHÍNH (nếu cần)
            if ($row['TrangThaiKhachHang'] == 'Đang ở') {
                if ($this->updateCustomerStatus($row['MaKhachHang'])) {
                    $results['updated_customers']++;
                    $invoice_detail['updated_customers'][] = $row['MaKhachHang'];
                    $customer_updated = true;
                    echo "<td style='color: red;'>✅ Khách: Đang ở → Không ở</td>";
                }
            } else {
                echo "<td style='color: gray;'>⏭️ Khách đã: " . $row['TrangThaiKhachHang'] . "</td>";
            }
            
            // 3. CẬP NHẬT KHÁCH Ở CÙNG
            $guest_updates = $this->updateGuestList($row['DanhSachKhach']);
            $results['updated_customers'] += $guest_updates['count'];
            $invoice_detail['updated_customers'] = array_merge(
                $invoice_detail['updated_customers'],
                $guest_updates['list']
            );
            
            if ($guest_updates['count'] > 0) {
                echo "<td style='color: red;'>✅ Khách ở cùng: " . $guest_updates['count'] . " người</td>";
            }
            
            $results['details'][] = $invoice_detail;
            echo "</tr>";
        }
        
        if ($result->num_rows > 0) {
            echo "</table>";
        }

        return $results;
    } catch (Exception $e) {
        error_log("AutoCheckoutModel Error: " . $e->getMessage());
        return false;
    }
}

    /**
     * Cập nhật trạng thái phòng
     */
    private function updateRoomStatus($maPhong)
    {
        $sql = "UPDATE phong SET TrangThai = 'Trống' WHERE MaPhong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maPhong);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Cập nhật trạng thái khách hàng
     */
    private function updateCustomerStatus($maKH)
    {
        $sql = "UPDATE khachhang 
                SET TrangThai = 'Không ở', updated_at = NOW() 
                WHERE MaKH = ? AND TrangThai = 'Đang ở'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $maKH);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Cập nhật danh sách khách ở cùng
     */
    private function updateGuestList($danhSachKhachJson)
    {
        $result = ['count' => 0, 'list' => []];

        if (empty($danhSachKhachJson) || $danhSachKhachJson == '[]') {
            return $result;
        }

        try {
            $guests = json_decode($danhSachKhachJson, true);

            if (is_array($guests)) {
                foreach ($guests as $guest) {
                    if (isset($guest['SoDienThoai']) && !empty($guest['SoDienThoai'])) {
                        // Tìm khách bằng số điện thoại
                        $maKH = $this->findCustomerByPhone($guest['SoDienThoai']);

                        if ($maKH && $this->updateCustomerStatus($maKH)) {
                            $result['count']++;
                            $result['list'][] = $maKH;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Update guest list error: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Tìm MaKH bằng số điện thoại
     */
    private function findCustomerByPhone($phone)
    {
        $sql = "SELECT MaKH FROM khachhang WHERE SoDienThoai = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['MaKH'];
        }

        return null;
    }
}
