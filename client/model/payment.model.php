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

    // Sửa đổi phương thức processBooking để thêm cập nhật trạng thái:

    public function processBooking($paymentData)
    {
        try {
            // Bắt đầu transaction
            $this->conn->begin_transaction();

            error_log("=== BẮT ĐẦU LƯU CSDL ===");
            error_log("Phương thức: " . $paymentData['paymentMethod']);
            error_log("User ID: " . $paymentData['userId']);

            // 1. LƯU THÔNG TIN KHÁCH HÀNG CHÍNH
            $maKhachHangChinh = $this->luuKhachHangChinh($paymentData);
            error_log("Mã KH chính: $maKhachHangChinh");

            // 2. LƯU THÔNG TIN NGƯỜI Ở CÙNG VÀO BẢNG khachhang
            $danhSachKhach = [$maKhachHangChinh];

            if (isset($paymentData['guestName']) && is_array($paymentData['guestName'])) {
                foreach ($paymentData['guestName'] as $index => $tenKhach) {
                    if (!empty($tenKhach)) {
                        $guestData = [
                            'HoTen' => $tenKhach,
                            'SoDienThoai' => $paymentData['guestPhone'][$index] ?? '',
                            'DiaChi' => $paymentData['guestAddress'][$index] ?? '',
                            'MaTaiKhoan' => $paymentData['userId']
                        ];

                        $maKhachHang = $this->luuKhachHangBoSung($guestData);
                        if ($maKhachHang) {
                            $danhSachKhach[] = $maKhachHang;
                            error_log("Đã lưu khách bổ sung: $maKhachHang");
                        }
                    }
                }
            }

            // 3. TẠO ĐOÀN
            $tenDoan = "Đoàn của " . $paymentData['customerName'];
            $maDoan = $this->taoDoan($maKhachHangChinh, $tenDoan, $paymentData);
            error_log("Mã đoàn: $maDoan");

            // 4. THÊM KHÁCH HÀNG VÀO ĐOÀN
            foreach ($danhSachKhach as $maKH) {
                $vaiTro = ($maKH == $maKhachHangChinh) ? 'TruongDoan' : 'ThanhVien';
                $this->themKhachVaoDoan($maDoan, $maKH, $vaiTro);
                error_log("Thêm $maKH vào đoàn với vai trò: $vaiTro");
            }

            // 5. TÍNH SỐ ĐÊM
            $soDem = $this->tinhSoDem($paymentData['checkin'], $paymentData['checkout']);

            // 6. LƯU HÓA ĐƠN ĐẶT PHÒNG
            $maHoaDon = $this->luuHoaDonDatPhong($paymentData, $maKhachHangChinh, $danhSachKhach, $soDem);
            error_log("Mã hóa đơn: $maHoaDon");

            // 7. CẬP NHẬT TRẠNG THÁI KHÁCH HÀNG THÀNH "Đang ở"
            $this->capNhatTrangThaiKhachHang($danhSachKhach);

            // 8. CẬP NHẬT TRẠNG THÁI PHÒNG THÀNH "Đang sử dụng"
            $this->capNhatTrangThaiPhong($paymentData['roomId']);

            // 9. XỬ LÝ THEO PHƯƠNG THỨC THANH TOÁN
            $phuongThuc = ($paymentData['paymentMethod'] === 'bankTransfer') ? 'Momo' : 'TienMat';
            $trangThai = 'ChuaThanhToan';

            // Cập nhật phương thức thanh toán vào hóa đơn
            $this->capNhatPhuongThucThanhToan($maHoaDon, $phuongThuc);

            // Commit transaction
            $this->conn->commit();

            error_log("=== LƯU CSDL THÀNH CÔNG ===");
            error_log("Đã cập nhật: " . count($danhSachKhach) . " khách hàng sang 'Đang ở'");
            error_log("Đã cập nhật phòng " . $paymentData['roomId'] . " sang 'Đang sử dụng'");

            // TRẢ KẾT QUẢ
            if ($paymentData['paymentMethod'] === 'cash') {
                return [
                    'success' => true,
                    'paymentMethod' => 'cash',
                    'message' => '🎉 Đặt phòng thành công! Vui lòng thanh toán tại quầy khi nhận phòng.',
                    'bookingCode' => 'HD' . str_pad($maHoaDon, 6, '0', STR_PAD_LEFT),
                    'maHoaDon' => $maHoaDon,
                    'status' => $trangThai,
                    'stats' => [
                        'khachHangUpdated' => count($danhSachKhach),
                        'phongUpdated' => $paymentData['roomId']
                    ]
                ];
            } else {
                // Momo
                return [
                    'success' => true,
                    'paymentMethod' => 'bankTransfer',
                    'message' => 'Đang chuyển hướng đến cổng thanh toán...',
                    'bookingCode' => 'HD' . str_pad($maHoaDon, 6, '0', STR_PAD_LEFT),
                    'maHoaDon' => $maHoaDon,
                    'status' => $trangThai,
                    'stats' => [
                        'khachHangUpdated' => count($danhSachKhach),
                        'phongUpdated' => $paymentData['roomId']
                    ]
                ];
            }
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            if (method_exists($this->conn, 'rollback')) {
                $this->conn->rollback();
            }
            error_log("❌ LỖI LƯU CSDL: " . $e->getMessage());
            throw new Exception("Lỗi đặt phòng: " . $e->getMessage());
        }
    }

    // ==================== CÁC HÀM PHỤ TRỢ ====================

    // 1. LƯU KHÁCH HÀNG CHÍNH
    private function luuKhachHangChinh($paymentData)
    {
        $userId = $paymentData['userId'];

        // Kiểm tra xem đã có khách hàng chưa
        $sql = "SELECT MaKH FROM khachhang WHERE MaTaiKhoan = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['MaKH'];
        }

        // Nếu chưa có, tạo mới
        $maKH = 'KH' . date('YmdHis') . rand(100, 999);

        $sql = "INSERT INTO khachhang (
                    MaKH, HoTen, SoDienThoai, DiaChi, CMND, Email, 
                    TrangThai, MaTaiKhoan, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'Không ở', ?, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssi",
            $maKH,
            $paymentData['customerName'],
            $paymentData['customerPhone'],
            $paymentData['address'],
            $paymentData['customerIdNumber'] ?? '',
            $paymentData['customerEmail'],
            $userId
        );

        if ($stmt->execute()) {
            return $maKH;
        }

        throw new Exception("Không thể lưu thông tin khách hàng chính: " . $stmt->error);
    }

    // 2. LƯU KHÁCH HÀNG BỔ SUNG
    private function luuKhachHangBoSung($guestData)
    {
        $maKH = 'KH' . date('YmdHis') . rand(100, 999);

        $sql = "INSERT INTO khachhang (
                    MaKH, HoTen, SoDienThoai, DiaChi, 
                    TrangThai, MaTaiKhoan, created_at, updated_at
                ) VALUES (?, ?, ?, ?, 'Không ở', ?, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssi",
            $maKH,
            $guestData['HoTen'],
            $guestData['SoDienThoai'],
            $guestData['DiaChi'],
            $guestData['MaTaiKhoan']
        );

        if ($stmt->execute()) {
            return $maKH;
        }

        error_log("Lỗi lưu khách bổ sung: " . $stmt->error);
        return null;
    }

    // 3. TẠO ĐOÀN
    private function taoDoan($maTruongDoan, $tenDoan, $paymentData)
    {
        $maDoan = 'MD' . date('YmdHis') . rand(100, 999);

        $sql = "INSERT INTO doan (
                    MaDoan, MaTruongDoan, TenDoan, NgayDen, NgayDi, 
                    GhiChu, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);

        $ghiChu = "Đoàn đặt phòng " . $paymentData['roomId'] .
            ", Số người: " . $paymentData['adults'] .
            ", Yêu cầu: " . ($paymentData['specialRequests'] ?? 'Không có');

        $stmt->bind_param(
            "ssssss",
            $maDoan,
            $maTruongDoan,
            $tenDoan,
            $paymentData['checkin'],
            $paymentData['checkout'],
            $ghiChu
        );

        if (!$stmt->execute()) {
            throw new Exception("Không thể tạo đoàn: " . $stmt->error);
        }

        return $maDoan;
    }

    // 4. THÊM KHÁCH VÀO ĐOÀN
    private function themKhachVaoDoan($maDoan, $maKH, $vaiTro)
    {
        $sql = "INSERT INTO doan_khachhang (MaDoan, MaKH, VaiTro, created_at) 
                VALUES (?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $maDoan, $maKH, $vaiTro);

        if (!$stmt->execute()) {
            error_log("Lỗi thêm khách vào đoàn: " . $stmt->error);
        }
    }

    // 5. TÍNH SỐ ĐÊM
    private function tinhSoDem($checkin, $checkout)
    {
        try {
            $start = new DateTime($checkin);
            $end = new DateTime($checkout);
            $interval = $start->diff($end);
            return $interval->days;
        } catch (Exception $e) {
            error_log("Lỗi tính số đêm: " . $e->getMessage());
            return 1; // Mặc định 1 đêm
        }
    }

    // HÀM LƯU HÓA ĐƠN ĐẶT PHÒNG
    private function luuHoaDonDatPhong($paymentData, $maKhachHang, $danhSachKhach, $soDem)
    {
        // Lấy thông tin khách hàng để lưu vào DanhSachKhach
        $danhSachKhachInfo = [];
        foreach ($danhSachKhach as $maKH) {
            $khachInfo = $this->layThongTinKhachHang($maKH);
            if ($khachInfo) {
                $danhSachKhachInfo[] = $khachInfo;
            }
        }

        $danhSachKhachJson = json_encode($danhSachKhachInfo, JSON_UNESCAPED_UNICODE);

        // Xử lý dịch vụ
        $maDichVu = '';
        $tenDichVu = '';
        $tienDichVu = 0;

        if (!empty($paymentData['services']) && $paymentData['services'] !== '') {
            $serviceIds = explode(',', $paymentData['services']);
            $serviceIds = array_filter($serviceIds);

            if (!empty($serviceIds)) {
                $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
                $sqlServices = "SELECT MaDV, TenDV, DonGia FROM dichvu WHERE MaDV IN ($placeholders)";
                $stmtServices = $this->conn->prepare($sqlServices);
                $types = str_repeat('i', count($serviceIds));
                $stmtServices->bind_param($types, ...$serviceIds);
                $stmtServices->execute();
                $servicesResult = $stmtServices->get_result();

                $serviceNames = [];
                $totalServicePrice = 0;

                while ($service = $servicesResult->fetch_assoc()) {
                    $serviceNames[] = $service['TenDV'];
                    // Tính giá dịch vụ cho tất cả người
                    $totalServicePrice += $service['DonGia'] * $paymentData['adults'];
                }

                $maDichVu = implode(',', $serviceIds);
                $tenDichVu = implode(', ', $serviceNames);
                $tienDichVu = $totalServicePrice;
            }
        }

        // Tính toán giá cả
        $giaPhong = $paymentData['roomPrice'] ?? 0;
        $tienKhuyenMai = $paymentData['discountAmount'] ?? 0;

        // Tính thuế (10% mặc định)
        $tienThue = ($giaPhong + $tienDichVu - $tienKhuyenMai) * 0.1;

        // Tổng tiền
        $tongTien = $giaPhong + $tienDichVu - $tienKhuyenMai + $tienThue;

        // Lưu vào database
        $sql = "INSERT INTO hoadondatphong (
                MaKhachHang, MaPhong, NgayNhan, NgayTra, SoDem, SoNguoi,
                DanhSachKhach, YeuCauDacBiet, MaDichVu, TenDichVu,
                GiaPhong, TienDichVu, TienKhuyenMai, TienThue, TongTien,
                PhuongThucThanhToan, TrangThai, NgayTao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);

        $phuongThuc = ($paymentData['paymentMethod'] === 'bankTransfer') ? 'Momo' : 'TienMat';
        $trangThai = 'ChuaThanhToan';

        // TẤT CẢ CÁC GIÁ TRỊ PHẢI ĐƯỢC GÁN VÀO BIẾN TRƯỚC KHI bind_param
        $maPhong = $paymentData['roomId'];
        $ngayNhan = $paymentData['checkin'];
        $ngayTra = $paymentData['checkout'];
        $soNguoi = $paymentData['adults'];
        $yeuCauDacBiet = $paymentData['specialRequests'] ?? '';

        // Gán tất cả giá trị vào biến
        $stmt->bind_param(
            "sisssississddddss",
            $maKhachHang,
            $maPhong,
            $ngayNhan,
            $ngayTra,
            $soDem,
            $soNguoi,
            $danhSachKhachJson,
            $yeuCauDacBiet,
            $maDichVu,
            $tenDichVu,
            $giaPhong,
            $tienDichVu,
            $tienKhuyenMai,
            $tienThue,
            $tongTien,
            $phuongThuc,
            $trangThai
        );

        if (!$stmt->execute()) {
            error_log("Lỗi SQL: " . $stmt->error);
            throw new Exception("Không thể lưu hóa đơn: " . $stmt->error);
        }

        return $stmt->insert_id;
    }
    // 9. CẬP NHẬT TRẠNG THÁI KHÁCH HÀNG THÀNH "Đang ở"
    private function capNhatTrangThaiKhachHang($danhSachMaKH)
    {
        try {
            $placeholders = str_repeat('?,', count($danhSachMaKH) - 1) . '?';
            $sql = "UPDATE khachhang SET TrangThai = 'Đang ở', updated_at = NOW() WHERE MaKH IN ($placeholders)";

            $stmt = $this->conn->prepare($sql);

            // Tạo types string (tất cả đều là string)
            $types = str_repeat('s', count($danhSachMaKH));

            // Bind parameters
            $stmt->bind_param($types, ...$danhSachMaKH);

            if (!$stmt->execute()) {
                error_log("Lỗi cập nhật trạng thái khách hàng: " . $stmt->error);
                return false;
            }

            error_log("Đã cập nhật trạng thái " . count($danhSachMaKH) . " khách hàng thành 'Đang ở'");
            return true;
        } catch (Exception $e) {
            error_log("Lỗi trong capNhatTrangThaiKhachHang: " . $e->getMessage());
            return false;
        }
    }

    // 10. CẬP NHẬT TRẠNG THÁI PHÒNG THÀNH "Đang sử dụng"
    private function capNhatTrangThaiPhong($maPhong)
    {
        try {
            $sql = "UPDATE phong SET TrangThai = 'Đang sử dụng', updated_at = NOW() WHERE MaPhong = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $maPhong);

            if (!$stmt->execute()) {
                error_log("Lỗi cập nhật trạng thái phòng: " . $stmt->error);
                return false;
            }

            error_log("Đã cập nhật trạng thái phòng $maPhong thành 'Đang sử dụng'");
            return true;
        } catch (Exception $e) {
            error_log("Lỗi trong capNhatTrangThaiPhong: " . $e->getMessage());
            return false;
        }
    }
    // 7. LẤY THÔNG TIN KHÁCH HÀNG
    private function layThongTinKhachHang($maKH)
    {
        $sql = "SELECT HoTen, SoDienThoai, DiaChi FROM khachhang WHERE MaKH = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $maKH);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return [
                'HoTen' => $row['HoTen'],
                'SoDienThoai' => $row['SoDienThoai'],
                'DiaChi' => $row['DiaChi']
            ];
        }

        return null;
    }

    // 8. CẬP NHẬT PHƯƠNG THỨC THANH TOÁN
    private function capNhatPhuongThucThanhToan($maHoaDon, $phuongThuc)
    {
        $sql = "UPDATE hoadondatphong 
                SET PhuongThucThanhToan = ? 
                WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $phuongThuc, $maHoaDon);
        $stmt->execute();
    }

    // Hàm lấy thông tin đặt phòng (giữ nguyên từ code cũ)
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

        // Tính toán giá
        $roomPrice = $room['TongGia'] * $nights;
        $servicesPrice = 0;
        $servicesList = [];

        if (!empty($services) && $services !== '') {
            $serviceIds = explode(',', $services);
            $serviceIds = array_filter($serviceIds);

            if (!empty($serviceIds)) {
                $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
                $sqlServices = "SELECT * FROM dichvu WHERE MaDV IN ($placeholders)";
                $stmtServices = $this->conn->prepare($sqlServices);
                $types = str_repeat('i', count($serviceIds));
                $stmtServices->bind_param($types, ...$serviceIds);
                $stmtServices->execute();
                $servicesResult = $stmtServices->get_result();

                while ($service = $servicesResult->fetch_assoc()) {
                    $servicePricePerPerson = $service['DonGia'];
                    $serviceTotalForAll = $servicePricePerPerson * $adults;
                    $servicesPrice += $serviceTotalForAll;

                    $service['DonGia_DaNhan'] = $serviceTotalForAll;
                    $service['SoNguoi'] = $adults;
                    $service['DonGia_PerPerson'] = $servicePricePerPerson;
                    $servicesList[] = $service;
                }
            }
        }

        // Tính thuế
        $taxRate = 0.1; // 10%
        $tax = ($roomPrice + $servicesPrice) * $taxRate;
        $totalAmount = $roomPrice + $servicesPrice + $tax;

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
            'taxRate' => $taxRate,
            'totalAmount' => $totalAmount,
            'roomId' => $roomId
        ];
    }
}
