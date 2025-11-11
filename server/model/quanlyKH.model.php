<?php
include_once 'connectDB.php';

class QuanLyKHModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Connect();
    }

    // Trong QuanLyKHModel - sửa hàm generateMaKH()
    public function generateMaKH()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT MaKH FROM KhachHang WHERE MaKH LIKE 'KH%' ORDER BY LENGTH(MaKH), MaKH DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_maKH = $row['MaKH'];
            // Lấy số từ mã cuối cùng (bỏ "KH" và chuyển thành số)
            $last_number = intval(substr($last_maKH, 2));
            $new_number = $last_number + 1;
            $new_maKH = 'KH' . $new_number; // KH1, KH2, KH3,...
        } else {
            $new_maKH = 'KH1'; // Mã đầu tiên
        }

        $this->db->closeConnect($conn);
        return $new_maKH;
    }
    // Lấy danh sách KH với filter
    public function getDanhSachKH($keyword = '', $loaiKH = '', $trangThai = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT * FROM KhachHang WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($keyword)) {
            $sql .= " AND (MaKH LIKE ? OR HoTen LIKE ? OR CMND LIKE ? OR SoDienThoai LIKE ? OR Email LIKE ?)";
            $searchTerm = "%$keyword%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= "sssss";
        }

        if (!empty($loaiKH)) {
            $sql .= " AND LoaiKH = ?";
            $params[] = $loaiKH;
            $types .= "s";
        }

        if (!empty($trangThai)) {
            $sql .= " AND TrangThai = ?";
            $params[] = $trangThai;
            $types .= "s";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // Lấy chi tiết KH
    public function getChiTietKH($maKH)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT * FROM KhachHang WHERE MaKH = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maKH);
        $stmt->execute();
        $khachHang = $stmt->get_result()->fetch_assoc();

        $this->db->closeConnect($conn);
        return $khachHang;
    }

    // Thêm KH
    public function themKH($data)
    {
        $conn = $this->db->openConnect();

        $maKHAuto = $this->generateMaKH();

        $sql = "INSERT INTO KhachHang (MaKH, HoTen, CMND, SoDienThoai, Email, DiaChi, LoaiKH, TrangThai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssss",
            $maKHAuto,
            $data['HoTen'],
            $data['CMND'],
            $data['SoDienThoai'],
            $data['Email'],
            $data['DiaChi'],
            $data['LoaiKH'],
            $data['TrangThai']
        );

        $result = $stmt->execute();
        $this->db->closeConnect($conn);

        return [
            'success' => $result,
            'maKH' => $maKHAuto
        ];
    }

    // Sửa KH
    public function suaKH($maKH, $data)
    {
        $conn = $this->db->openConnect();

        $sql = "UPDATE KhachHang SET 
                HoTen = ?, CMND = ?, SoDienThoai = ?, Email = ?, DiaChi = ?, 
                LoaiKH = ?, TrangThai = ? 
                WHERE MaKH = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssss",
            $data['HoTen'],
            $data['CMND'],
            $data['SoDienThoai'],
            $data['Email'],
            $data['DiaChi'],
            $data['LoaiKH'],
            $data['TrangThai'],
            $maKH
        );

        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // Xóa KH
    public function xoaKH($maKH)
    {
        $conn = $this->db->openConnect();

        $sql = "DELETE FROM KhachHang WHERE MaKH = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maKH);

        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // Xóa nhiều KH
    public function xoaNhieuKH($listMaKH)
    {
        $conn = $this->db->openConnect();

        $placeholders = str_repeat('?,', count($listMaKH) - 1) . '?';
        $sql = "DELETE FROM KhachHang WHERE MaKH IN ($placeholders)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($listMaKH)), ...$listMaKH);

        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // Thống kê
    public function thongKeKH()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
                COUNT(*) as tongKH,
                SUM(CASE WHEN LoaiKH = 'VIP' THEN 1 ELSE 0 END) as tongVIP,
                SUM(CASE WHEN LoaiKH = 'Thuong' THEN 1 ELSE 0 END) as tongThuong,
                SUM(CASE WHEN TrangThai = 'HoatDong' THEN 1 ELSE 0 END) as tongHoatDong,
                SUM(CASE WHEN TrangThai = 'NgungHoatDong' THEN 1 ELSE 0 END) as tongNgungHoatDong
                FROM KhachHang";

        $result = $conn->query($sql);
        $thongKe = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $thongKe;
    }

    // Kiểm tra CMND tồn tại
    public function kiemTraCMND($cmnd, $maKH = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM KhachHang WHERE CMND = ?";
        if (!empty($maKH)) {
            $sql .= " AND MaKH != ?";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($maKH)) {
            $stmt->bind_param("ss", $cmnd, $maKH);
        } else {
            $stmt->bind_param("s", $cmnd);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }
}
