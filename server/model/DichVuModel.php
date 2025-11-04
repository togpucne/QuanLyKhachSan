<?php
require_once 'connectDB.php';

class DichVuModel
{
    private $conn;

    public function __construct()
    {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }

    // Lấy danh sách tất cả dịch vụ - BỎ DEBUG COMMENTS
    public function getDanhSachDichVu()
    {
        $sql = "SELECT * FROM dichvu ORDER BY LoaiDV, TenDV";
        $result = mysqli_query($this->conn, $sql);
        
        if (!$result) {
            error_log("Lỗi SQL: " . mysqli_error($this->conn)); // Dùng error_log thay vì echo
            return array();
        }
        
        $dsDichVu = array();
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dsDichVu[] = $row;
            }
        }
        
        return $dsDichVu;
    }

    // Lấy danh sách loại dịch vụ - BỎ DEBUG
    public function getDichVuTheoLoai()
    {
        $sql = "SELECT DISTINCT LoaiDV FROM dichvu WHERE TrangThai = 'Khả dụng' ORDER BY LoaiDV";
        $result = mysqli_query($this->conn, $sql);
        $loaiDV = array();

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $loaiDV[] = $row['LoaiDV'];
            }
        }

        return $loaiDV;
    }
    // Lấy thông tin dịch vụ theo mã
    public function getDichVuByMa($maDV)
    {
        $sql = "SELECT * FROM dichvu WHERE MaDV = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $maDV);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);
    }


    // Lấy danh sách loại dịch vụ
    public function getLoaiDichVu()
    {
        $sql = "SELECT DISTINCT LoaiDV FROM dichvu WHERE TrangThai = 'Khả dụng' ORDER BY LoaiDV";
        $result = mysqli_query($this->conn, $sql);
        $loaiDV = array();

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $loaiDV[] = $row['LoaiDV'];
            }
        }

        return $loaiDV;
    }

    // Thêm dịch vụ mới
    public function themDichVu($tenDV, $donGia, $donViTinh, $moTa, $loaiDV)
    {
        $sql = "INSERT INTO dichvu (TenDV, DonGia, DonViTinh, MoTa, LoaiDV) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "sdsss", $tenDV, $donGia, $donViTinh, $moTa, $loaiDV);
        return mysqli_stmt_execute($stmt);
    }

    // Cập nhật dịch vụ
    public function capNhatDichVu($maDV, $tenDV, $donGia, $donViTinh, $moTa, $loaiDV, $trangThai)
    {
        $sql = "UPDATE dichvu SET TenDV = ?, DonGia = ?, DonViTinh = ?, MoTa = ?, LoaiDV = ?, TrangThai = ? WHERE MaDV = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "sdssssi", $tenDV, $donGia, $donViTinh, $moTa, $loaiDV, $trangThai, $maDV);
        return mysqli_stmt_execute($stmt);
    }

    // Xóa dịch vụ (chuyển trạng thái)
    public function xoaDichVu($maDV)
    {
        $sql = "DELETE FROM dichvu  WHERE MaDV = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $maDV);
        return mysqli_stmt_execute($stmt);
    }

    // Tìm kiếm dịch vụ
    public function timKiemDichVu($keyword)
    {
        $sql = "SELECT * FROM dichvu 
                WHERE (TenDV LIKE ? OR MoTa LIKE ? OR LoaiDV LIKE ?) 
                ORDER BY LoaiDV, TenDV";
        $stmt = mysqli_prepare($this->conn, $sql);
        $searchTerm = "%$keyword%";
        mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $dsDichVu = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $dsDichVu[] = $row;
        }

        return $dsDichVu;
    }
}
