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

    // LẤY DANH SÁCH KHÁCH HÀNG ĐỂ CHỌN TRƯỞNG ĐOÀN
    public function getDanhSachKhachHang()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT MaKH, HoTen, SoDienThoai FROM KhachHang WHERE TrangThai = 'HoatDong' ORDER BY HoTen";
        $result = $conn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // Lấy danh sách đoàn - DÙNG JOIN để lấy số lượng thành viên
    public function getDanhSachDoan()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT d.*, COUNT(dk.MaKH) as SoLuongThanhVien, kh.HoTen as TenTruongDoan
                FROM Doan d 
                LEFT JOIN Doan_KhachHang dk ON d.MaDoan = dk.MaDoan 
                LEFT JOIN KhachHang kh ON d.MaTruongDoan = kh.MaKH
                GROUP BY d.MaDoan 
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

    // Lấy chi tiết đoàn - THÊM SỐ LƯỢNG THÀNH VIÊN
    public function getChiTietDoan($maDoan)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT d.*, COUNT(dk.MaKH) as SoLuongThanhVien, kh.HoTen as TenTruongDoan
                FROM Doan d 
                LEFT JOIN Doan_KhachHang dk ON d.MaDoan = dk.MaDoan 
                LEFT JOIN KhachHang kh ON d.MaTruongDoan = kh.MaKH
                WHERE d.MaDoan = ? 
                GROUP BY d.MaDoan";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maDoan);
        $stmt->execute();
        $doan = $stmt->get_result()->fetch_assoc();

        $this->db->closeConnect($conn);
        return $doan;
    }

    // Tìm kiếm đoàn - THÊM SỐ LƯỢNG THÀNH VIÊN
    public function timKiemDoan($keyword)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT d.*, COUNT(dk.MaKH) as SoLuongThanhVien, kh.HoTen as TenTruongDoan
                FROM Doan d 
                LEFT JOIN Doan_KhachHang dk ON d.MaDoan = dk.MaDoan 
                LEFT JOIN KhachHang kh ON d.MaTruongDoan = kh.MaKH
                WHERE d.MaDoan LIKE ? OR d.TenDoan LIKE ? OR d.MaTruongDoan LIKE ? OR kh.HoTen LIKE ?
                GROUP BY d.MaDoan 
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

    // SỬA ĐOÀN
    public function suaDoan($maDoan, $data)
    {
        $conn = $this->db->openConnect();

        $sql = "UPDATE Doan SET 
                MaTruongDoan = ?, 
                TenDoan = ?, 
                NgayDen = ?, 
                NgayDi = ?, 
                GhiChu = ? 
                WHERE MaDoan = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssss",
            $data['MaTruongDoan'],
            $data['TenDoan'],
            $data['NgayDen'],
            $data['NgayDi'],
            $data['GhiChu'],
            $maDoan
        );

        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
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
    // LẤY DANH SÁCH THÀNH VIÊN CỦA ĐOÀN
    public function getThanhVienDoan($maDoan)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT dk.*, kh.HoTen, kh.SoDienThoai, kh.Email 
            FROM Doan_KhachHang dk 
            JOIN KhachHang kh ON dk.MaKH = kh.MaKH 
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

    // LẤY DANH SÁCH KHÁCH HÀNG CHƯA CÓ TRONG ĐOÀN
    public function getKhachHangChuaTrongDoan($maDoan)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT kh.MaKH, kh.HoTen, kh.SoDienThoai, kh.Email 
            FROM KhachHang kh 
            WHERE kh.TrangThai = 'HoatDong' 
            AND kh.MaKH NOT IN (
                SELECT MaKH FROM Doan_KhachHang WHERE MaDoan = ?
            ) 
            ORDER BY kh.HoTen";

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

    // THÊM THÀNH VIÊN VÀO ĐOÀN
    public function themThanhVienDoan($maDoan, $maKH, $vaiTro = 'ThanhVien')
    {
        $conn = $this->db->openConnect();

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
