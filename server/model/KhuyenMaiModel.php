<?php
require_once 'connectDB.php';

class KhuyenMaiModel {
    private $conn;

    public function __construct() {
        $p = new Connect();
        $this->conn = $p->openConnect();
        
        // Kiểm tra kết nối
        if (!$this->conn) {
            die("Kết nối database thất bại");
        }
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Lấy tất cả khuyến mãi
    public function getAllKhuyenMai() {
        $sql = "SELECT km.*, nv.HoTen as TenNhanVienTao
                FROM khuyenmai km
                LEFT JOIN nhanvienkinhdoanh nvkd ON km.MaNhanVienTao = nvkd.MaNhanVien
                LEFT JOIN nhanvien nv ON nvkd.MaNhanVien = nv.MaNhanVien
                ORDER BY km.MaKM DESC";
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            // Debug lỗi SQL
            error_log("SQL Error: " . $this->conn->error);
            return false;
        }
        
        return $result;
    }

    // Thêm khuyến mãi mới
    public function addKhuyenMai($tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $maNVTao) {
        $sql = "INSERT INTO khuyenmai (TenKhuyenMai, MucGiamGia, NgayBatDau, NgayKetThuc, MoTa, MaNhanVienTao) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdsssi", $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $maNVTao);
        
        return $stmt->execute();
    }

    // Các hàm khác giữ nguyên...
    public function updateKhuyenMai($maKM, $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa) {
        $sql = "UPDATE khuyenmai SET TenKhuyenMai=?, MucGiamGia=?, NgayBatDau=?, NgayKetThuc=?, MoTa=?
                WHERE MaKM=?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdsssi", $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $maKM);
        
        return $stmt->execute();
    }

    public function deleteKhuyenMai($maKM) {
        $sql = "DELETE FROM khuyenmai WHERE MaKM=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maKM);
        return $stmt->execute();
    }
}
?>