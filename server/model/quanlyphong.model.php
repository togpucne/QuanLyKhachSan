<?php
include_once 'connectDB.php';

class QuanLyPhongModel {
    private $db;

    public function __construct() {
        $this->db = new Connect();
    }

    // LẤY DANH SÁCH PHÒNG VỚI THÔNG TIN LOẠI PHÒNG
    public function getDanhSachPhong($keyword = '', $tang = '', $loaiPhong = '', $trangThai = '') {
        $conn = $this->db->openConnect();
        
        $sql = "SELECT p.*, lp.HangPhong, lp.HinhThuc, lp.DonGia 
                FROM Phong p 
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($keyword)) {
            $sql .= " AND (p.SoPhong LIKE ? OR p.roomName LIKE ?)";
            $searchTerm = "%$keyword%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
            $types .= "ss";
        }
        
        if (!empty($tang)) {
            $sql .= " AND p.Tang = ?";
            $params[] = $tang;
            $types .= "s";
        }
        
        if (!empty($loaiPhong)) {
            $sql .= " AND p.MaLoaiPhong = ?";
            $params[] = $loaiPhong;
            $types .= "s";
        }
        
        if (!empty($trangThai)) {
            $sql .= " AND p.TrangThai = ?";
            $params[] = $trangThai;
            $types .= "s";
        }
        
        $sql .= " ORDER BY p.Tang, p.SoPhong";
        
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

    // LẤY CHI TIẾT PHÒNG
    public function getChiTietPhong($maPhong) {
        $conn = $this->db->openConnect();
        
        $sql = "SELECT p.*, lp.HangPhong, lp.HinhThuc, lp.DonGia 
                FROM Phong p 
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                WHERE p.MaPhong = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maPhong);
        $stmt->execute();
        $phong = $stmt->get_result()->fetch_assoc();
        
        $this->db->closeConnect($conn);
        return $phong;
    }

    // LẤY DANH SÁCH LOẠI PHÒNG
    public function getDanhSachLoaiPhong() {
        $conn = $this->db->openConnect();
        
        $sql = "SELECT * FROM LoaiPhong ORDER BY DonGia";
        $result = $conn->query($sql);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $this->db->closeConnect($conn);
        return $data;
    }

    // LẤY THIẾT BỊ CỦA PHÒNG
    public function getThietBiPhong($maPhong) {
        $conn = $this->db->openConnect();
        
        $sql = "SELECT * FROM ThietBi WHERE MaPhong = ? ORDER BY TenThietBi";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maPhong);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $this->db->closeConnect($conn);
        return $data;
    }

    // THÊM PHÒNG MỚI
    public function themPhong($data) {
        $conn = $this->db->openConnect();
        
        $sql = "INSERT INTO Phong (SoPhong, Tang, MaLoaiPhong, TrangThai, Avatar, DanhSachPhong, roomName) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", 
            $data['SoPhong'],
            $data['Tang'],
            $data['MaLoaiPhong'],
            $data['TrangThai'],
            $data['Avatar'],
            $data['DanhSachPhong'],
            $data['roomName']
        );
        
        $result = $stmt->execute();
        $maPhong = $conn->insert_id;
        
        $this->db->closeConnect($conn);
        
        return [
            'success' => $result,
            'maPhong' => $maPhong
        ];
    }

    // SỬA THÔNG TIN PHÒNG
    public function suaPhong($maPhong, $data) {
        $conn = $this->db->openConnect();
        
        $sql = "UPDATE Phong SET 
                SoPhong = ?, Tang = ?, MaLoaiPhong = ?, TrangThai = ?, 
                Avatar = ?, DanhSachPhong = ?, roomName = ? 
                WHERE MaPhong = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $data['SoPhong'],
            $data['Tang'],
            $data['MaLoaiPhong'],
            $data['TrangThai'],
            $data['Avatar'],
            $data['DanhSachPhong'],
            $data['roomName'],
            $maPhong
        );
        
        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // XÓA PHÒNG
    public function xoaPhong($maPhong) {
        $conn = $this->db->openConnect();
        
        // Xóa thiết bị trước (nếu có foreign key constraint)
        $sql_thietbi = "DELETE FROM ThietBi WHERE MaPhong = ?";
        $stmt_thietbi = $conn->prepare($sql_thietbi);
        $stmt_thietbi->bind_param("s", $maPhong);
        $stmt_thietbi->execute();
        
        // Xóa phòng
        $sql_phong = "DELETE FROM Phong WHERE MaPhong = ?";
        $stmt_phong = $conn->prepare($sql_phong);
        $stmt_phong->bind_param("s", $maPhong);
        
        $result = $stmt_phong->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // THÊM THIẾT BỊ CHO PHÒNG
    public function themThietBi($data) {
        $conn = $this->db->openConnect();
        
        $sql = "INSERT INTO ThietBi (TenThietBi, TinhTrang, MaPhong) 
                VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", 
            $data['TenThietBi'],
            $data['TinhTrang'],
            $data['MaPhong']
        );
        
        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // SỬA THIẾT BỊ
    public function suaThietBi($maThietBi, $data) {
        $conn = $this->db->openConnect();
        
        $sql = "UPDATE ThietBi SET 
                TenThietBi = ?, TinhTrang = ? 
                WHERE MaThietBi = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", 
            $data['TenThietBi'],
            $data['TinhTrang'],
            $maThietBi
        );
        
        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // XÓA THIẾT BỊ
    public function xoaThietBi($maThietBi) {
        $conn = $this->db->openConnect();
        
        $sql = "DELETE FROM ThietBi WHERE MaThietBi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maThietBi);
        
        $result = $stmt->execute();
        $this->db->closeConnect($conn);
        return $result;
    }

    // THỐNG KÊ PHÒNG
    public function thongKePhong() {
        $conn = $this->db->openConnect();
        
        $sql = "SELECT 
                COUNT(*) as tongPhong,
                SUM(CASE WHEN TrangThai = 'Trống' THEN 1 ELSE 0 END) as tongTrong,
                SUM(CASE WHEN TrangThai = 'Đang sử dụng' THEN 1 ELSE 0 END) as tongDangSuDung,
                SUM(CASE WHEN TrangThai = 'Bảo trì' THEN 1 ELSE 0 END) as tongBaoTri
                FROM Phong";
        
        $result = $conn->query($sql);
        $thongKe = $result->fetch_assoc();
        
        $this->db->closeConnect($conn);
        return $thongKe;
    }

    // KIỂM TRA SỐ PHÒNG ĐÃ TỒN TẠI
    public function kiemTraSoPhong($soPhong, $maPhong = '') {
        $conn = $this->db->openConnect();
        
        $sql = "SELECT COUNT(*) as count FROM Phong WHERE SoPhong = ?";
        if (!empty($maPhong)) {
            $sql .= " AND MaPhong != ?";
        }
        
        $stmt = $conn->prepare($sql);
        if (!empty($maPhong)) {
            $stmt->bind_param("ss", $soPhong, $maPhong);
        } else {
            $stmt->bind_param("s", $soPhong);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }
}
?>