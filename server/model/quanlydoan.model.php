<?php
include_once 'connectDB.php';

class QuanLyDoanModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Connect();
    }

    // HÀM TẠO MÃ ĐOÀN TỰ ĐỘNG
    public function generateMaDoan()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT MaDoan FROM Doan WHERE MaDoan LIKE 'MD%' ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_maDoan = $row['MaDoan'];
            $last_number = intval(substr($last_maDoan, 2));
            $new_number = $last_number + 1;
            $new_maDoan = 'MD' . $new_number;
        } else {
            $new_maDoan = 'MD1';
        }

        $this->db->closeConnect($conn);
        return $new_maDoan;
    }



    // Lấy chi tiết đoàn - SỬA LẠI ĐỂ LẤY ĐÚNG TRƯỞNG ĐOÀN
    public function getChiTietDoan($maDoan)
    {
        $conn = $this->db->openConnect();

        // SỬA LẠI: Dùng INNER JOIN thay vì LEFT JOIN cho trưởng đoàn
        $sql = "SELECT 
            d.*, 
            kh.HoTen as TenTruongDoan,
            kh.SoDienThoai as SDTTruongDoan,
            (SELECT COUNT(*) FROM Doan_KhachHang WHERE MaDoan = d.MaDoan) as SoLuongThanhVien
            FROM Doan d 
            INNER JOIN KhachHang kh ON d.MaTruongDoan = kh.MaKH
            WHERE d.MaDoan = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maDoan);
        $stmt->execute();
        $doan = $stmt->get_result()->fetch_assoc();

        $this->db->closeConnect($conn);
        return $doan;
    }


    // Lấy danh sách đoàn - SỬA LẠI ĐỂ HIỂN THỊ ĐÚNG TRƯỞNG ĐOÀN
    public function getDanhSachDoan()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
            d.*, 
            kh.HoTen as TenTruongDoan,
            kh.SoDienThoai as SDTTruongDoan,
            (SELECT COUNT(*) FROM Doan_KhachHang WHERE MaDoan = d.MaDoan) as SoLuongThanhVien
            FROM Doan d 
            INNER JOIN KhachHang kh ON d.MaTruongDoan = kh.MaKH
            ORDER BY d.created_at DESC";

        $result = $conn->query($sql);

        if (!$result) {
            error_log("Lỗi SQL: " . $conn->error);
            $this->db->closeConnect($conn);
            return [];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }
    // LẤY DANH SÁCH KHÁCH HÀNG ĐỂ CHỌN TRƯỞNG ĐOÀN - CHO MODAL SỬA
    public function getDanhSachKhachHang()
    {
        $conn = $this->db->openConnect();

        // LẤY TẤT CẢ KHÁCH HÀNG (cho modal sửa đoàn)
        $sql = "SELECT kh.MaKH, kh.HoTen, kh.SoDienThoai, tk.Email 
            FROM KhachHang kh 
            LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
            ORDER BY kh.HoTen";

        $result = $conn->query($sql);

        if (!$result) {
            error_log("Lỗi SQL getDanhSachKhachHang: " . $conn->error);
            $this->db->closeConnect($conn);
            return [];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }



    // HÀM MỚI: Lấy danh sách khách hàng cho modal thêm đoàn
    public function getKhachHangChuaCoDoan()
    {
        $conn = $this->db->openConnect();

        // Lấy khách hàng CHƯA CÓ ĐOÀN (cho modal thêm đoàn)
        $sql = "SELECT kh.MaKH, kh.HoTen, kh.SoDienThoai, tk.Email 
            FROM KhachHang kh 
            LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
            WHERE kh.MaKH NOT IN (
                SELECT DISTINCT MaKH FROM Doan_KhachHang
            ) 
            ORDER BY kh.HoTen";

        $result = $conn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }
    // HÀM MỚI: Lấy danh sách thành viên của đoàn (cho modal sửa)
    public function getThanhVienDoanChoDropdown($maDoan)
    {
        $conn = $this->db->openConnect();

        // Lấy tất cả thành viên của đoàn này (bao gồm cả trưởng đoàn)
        $sql = "SELECT dk.MaKH, kh.HoTen, kh.SoDienThoai, tk.Email, dk.VaiTro
            FROM Doan_KhachHang dk 
            JOIN KhachHang kh ON dk.MaKH = kh.MaKH 
            LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
            WHERE dk.MaDoan = ? 
            ORDER BY dk.VaiTro DESC, kh.HoTen";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maDoan);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }
    // Tìm kiếm đoàn - SỬA LẠI
    public function timKiemDoan($keyword)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
            d.*, 
            kh.HoTen as TenTruongDoan,
            kh.SoDienThoai as SDTTruongDoan,
            (SELECT COUNT(*) FROM Doan_KhachHang WHERE MaDoan = d.MaDoan) as SoLuongThanhVien
            FROM Doan d 
            INNER JOIN KhachHang kh ON d.MaTruongDoan = kh.MaKH
            WHERE d.MaDoan LIKE ? 
            OR d.TenDoan LIKE ? 
            OR d.MaTruongDoan LIKE ? 
            OR kh.HoTen LIKE ?
            ORDER BY d.created_at DESC";

        $stmt = $conn->prepare($sql);
        $searchTerm = "%$keyword%";
        $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }
    // THÊM ĐOÀN - THÊM TRƯỞNG ĐOÀN VÀO BẢNG Doan_KhachHang
    public function themDoan($data)
    {
        $conn = $this->db->openConnect();

        // Tạo mã đoàn tự động
        $maDoanAuto = $this->generateMaDoan();

        // Bắt đầu transaction
        $conn->begin_transaction();

        try {
            // 1. Thêm vào bảng Doan
            $sql_doan = "INSERT INTO Doan (MaDoan, MaTruongDoan, TenDoan, NgayDen, NgayDi, GhiChu) 
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_doan = $conn->prepare($sql_doan);
            $stmt_doan->bind_param(
                "ssssss",
                $maDoanAuto,
                $data['MaTruongDoan'],
                $data['TenDoan'],
                $data['NgayDen'],
                $data['NgayDi'],
                $data['GhiChu']
            );
            $result_doan = $stmt_doan->execute();

            if (!$result_doan) {
                throw new Exception("Lỗi thêm đoàn");
            }

            // 2. Thêm trưởng đoàn vào bảng Doan_KhachHang
            $sql_member = "INSERT INTO Doan_KhachHang (MaDoan, MaKH, VaiTro) VALUES (?, ?, 'TruongDoan')";
            $stmt_member = $conn->prepare($sql_member);
            $stmt_member->bind_param("ss", $maDoanAuto, $data['MaTruongDoan']);
            $result_member = $stmt_member->execute();

            if (!$result_member) {
                throw new Exception("Lỗi thêm trưởng đoàn");
            }

            // Commit transaction
            $conn->commit();
            $success = true;
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $conn->rollback();
            $success = false;
            error_log("Lỗi thêm đoàn: " . $e->getMessage());
        }

        $this->db->closeConnect($conn);

        return [
            'success' => $success,
            'maDoan' => $maDoanAuto
        ];
    }

    // CẬP NHẬT TRƯỞNG ĐOÀN (QUAN TRỌNG)
    public function suaDoan($maDoan, $data)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction(); // Bắt đầu transaction

        try {
            // 1. Lấy thông tin đoàn cũ để biết trưởng đoàn hiện tại
            $sql_get = "SELECT MaTruongDoan FROM Doan WHERE MaDoan = ?";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->bind_param("s", $maDoan);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            $oldData = $result->fetch_assoc();
            $oldTruongDoan = $oldData['MaTruongDoan'];
            $newTruongDoan = $data['MaTruongDoan'];

            // 2. Nếu trưởng đoàn thay đổi
            if ($oldTruongDoan != $newTruongDoan) {
                // 2a. Đổi trưởng đoàn cũ thành thành viên thường (nếu còn trong đoàn)
                $sql_downgrade = "UPDATE Doan_KhachHang SET VaiTro = 'ThanhVien' 
                              WHERE MaDoan = ? AND MaKH = ? AND VaiTro = 'TruongDoan'";
                $stmt_downgrade = $conn->prepare($sql_downgrade);
                $stmt_downgrade->bind_param("ss", $maDoan, $oldTruongDoan);
                $stmt_downgrade->execute();

                // 2b. Kiểm tra xem trưởng đoàn mới đã có trong đoàn chưa
                $sql_check = "SELECT COUNT(*) as count FROM Doan_KhachHang 
                          WHERE MaDoan = ? AND MaKH = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("ss", $maDoan, $newTruongDoan);
                $stmt_check->execute();
                $check_result = $stmt_check->get_result();
                $row = $check_result->fetch_assoc();

                if ($row['count'] > 0) {
                    // Nếu đã có, nâng cấp lên trưởng đoàn
                    $sql_upgrade = "UPDATE Doan_KhachHang SET VaiTro = 'TruongDoan' 
                                WHERE MaDoan = ? AND MaKH = ?";
                    $stmt_upgrade = $conn->prepare($sql_upgrade);
                    $stmt_upgrade->bind_param("ss", $maDoan, $newTruongDoan);
                    $stmt_upgrade->execute();
                } else {
                    // Nếu chưa có, thêm mới với vai trò trưởng đoàn
                    $sql_insert = "INSERT INTO Doan_KhachHang (MaDoan, MaKH, VaiTro) 
                               VALUES (?, ?, 'TruongDoan')";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("ss", $maDoan, $newTruongDoan);
                    $stmt_insert->execute();
                }
            }

            // 3. Cập nhật thông tin đoàn trong bảng Doan
            $sql_doan = "UPDATE Doan SET 
                    MaTruongDoan = ?, 
                    TenDoan = ?, 
                    NgayDen = ?, 
                    NgayDi = ?, 
                    GhiChu = ? 
                    WHERE MaDoan = ?";

            $stmt_doan = $conn->prepare($sql_doan);
            $stmt_doan->bind_param(
                "ssssss",
                $data['MaTruongDoan'],
                $data['TenDoan'],
                $data['NgayDen'],
                $data['NgayDi'],
                $data['GhiChu'],
                $maDoan
            );

            if (!$stmt_doan->execute()) {
                throw new Exception("Lỗi cập nhật đoàn!");
            }

            $conn->commit(); // Commit transaction
            $this->db->closeConnect($conn);
            return true;
        } catch (Exception $e) {
            $conn->rollback(); // Rollback nếu có lỗi
            $this->db->closeConnect($conn);
            error_log("Lỗi sửa đoàn: " . $e->getMessage());
            return false;
        }
    }

    // XÓA ĐOÀN (sẽ tự động xóa trong Doan_KhachHang nhờ CASCADE)
    public function xoaDoan($maDoan)
    {
        $conn = $this->db->openConnect();

        $sql = "DELETE FROM Doan WHERE MaDoan = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maDoan);

        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // THỐNG KÊ
    public function thongKeDoan()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
                COUNT(DISTINCT d.MaDoan) as tongDoan,
                COUNT(dk.MaKH) as tongNguoi,
                ROUND(COUNT(dk.MaKH) / COUNT(DISTINCT d.MaDoan), 1) as trungBinhNguoi
                FROM Doan d 
                LEFT JOIN Doan_KhachHang dk ON d.MaDoan = dk.MaDoan";

        $result = $conn->query($sql);
        $thongKe = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $thongKe;
    }
    // LẤY DANH SÁCH THÀNH VIÊN CỦA ĐOÀN - ĐÃ SỬA
    public function getThanhVienDoan($maDoan)
    {
        $conn = $this->db->openConnect();

        // SỬA: Lấy Email từ bảng tai_khoan thay vì KhachHang
        $sql = "SELECT dk.*, kh.HoTen, kh.SoDienThoai, tk.Email 
            FROM Doan_KhachHang dk 
            JOIN KhachHang kh ON dk.MaKH = kh.MaKH 
            LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
            WHERE dk.MaDoan = ? 
            ORDER BY dk.VaiTro DESC, kh.HoTen";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maDoan);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // LẤY DANH SÁCH KHÁCH HÀNG CHƯA CÓ TRONG ĐOÀN - SỬA LẠI
    public function getKhachHangChuaTrongDoan($maDoan = null)
    {
        $conn = $this->db->openConnect();

        // Lấy tất cả khách hàng CHƯA Ở ĐOÀN NÀO
        $sql = "SELECT kh.MaKH, kh.HoTen, kh.SoDienThoai, tk.Email 
            FROM KhachHang kh 
            LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
            WHERE kh.MaKH NOT IN (
                SELECT DISTINCT MaKH FROM Doan_KhachHang  -- TẤT CẢ ĐOÀN
            ) 
            ORDER BY kh.HoTen";

        $stmt = $conn->prepare($sql);
        // KHÔNG CẦN bind_param vì không có tham số
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }
    // THÊM THÀNH VIÊN VÀO ĐOÀN - THÊM KIỂM TRA
    public function themThanhVienDoan($maDoan, $maKH, $vaiTro = 'ThanhVien')
    {
        $conn = $this->db->openConnect();

        // KIỂM TRA TRƯỚC KHI THÊM
        $sql_check = "SELECT COUNT(*) as count FROM Doan_KhachHang WHERE MaKH = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $maKH);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $row = $result->fetch_assoc();

        // Nếu đã có trong đoàn khác, KHÔNG cho thêm
        if ($row['count'] > 0) {
            $this->db->closeConnect($conn);
            return false;
        }

        $sql = "INSERT INTO Doan_KhachHang (MaDoan, MaKH, VaiTro) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $maDoan, $maKH, $vaiTro);

        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }
    // XÓA THÀNH VIÊN KHỎI ĐOÀN
    public function xoaThanhVienDoan($maDoan, $maKH)
    {
        $conn = $this->db->openConnect();

        $sql = "DELETE FROM Doan_KhachHang WHERE MaDoan = ? AND MaKH = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $maDoan, $maKH);

        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }
}
