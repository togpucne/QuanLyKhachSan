<?php
// server/model/letandatphong.model.php
require_once __DIR__ . '/connectDB.php';

class LetanDatPhongModel {
    private $conn;
    
    public function __construct() {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }
    
    // Lấy tất cả hóa đơn
    public function getAllHoaDon() {
        $sql = "SELECT * FROM hoadondatphong ORDER BY NgayTao DESC";
        $result = mysqli_query($this->conn, $sql);
        
        $hoadon = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $hoadon[] = $row;
            }
        }
        return $hoadon;
    }
    
    // Lấy hóa đơn theo ID
    public function getHoaDonById($id) {
        $sql = "SELECT * FROM hoadondatphong WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    // Cập nhật trạng thái thanh toán
    public function updateTrangThai($id, $trangThai) {
        $sql = "UPDATE hoadondatphong SET TrangThai = ? WHERE Id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $trangThai, $id);
        
        return mysqli_stmt_execute($stmt);
    }
    
    // Tìm kiếm hóa đơn
    public function searchHoaDon($keyword) {
        $sql = "SELECT * FROM hoadondatphong 
                WHERE MaKhachHang LIKE ? 
                   OR MaPhong LIKE ?
                   OR Id LIKE ?
                ORDER BY NgayTao DESC";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        $searchKeyword = "%" . $keyword . "%";
        mysqli_stmt_bind_param($stmt, "sss", $searchKeyword, $searchKeyword, $searchKeyword);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $hoadon = [];
        while($row = mysqli_fetch_assoc($result)) {
            $hoadon[] = $row;
        }
        return $hoadon;
    }
    
    // Đóng kết nối
    public function __destruct() {
        if ($this->conn) {
            $db = new Connect();
            $db->closeConnect($this->conn);
        }
    }
}
?>