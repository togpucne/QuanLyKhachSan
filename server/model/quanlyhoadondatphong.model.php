<?php
// server/model/quanlyhoadondatphong.model.php
require_once __DIR__ . '/connectDB.php';

class QuanLyHoaDonDatPhongModel
{
    private $conn;

    public function __construct()
    {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }

    // Lấy tất cả hóa đơn
    public function getAllHoaDon()
    {
        $sql = "SELECT * FROM hoadondatphong ORDER BY NgayTao DESC";
        $result = mysqli_query($this->conn, $sql);

        $hoadon = [];
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $hoadon[] = $row;
            }
        }
        return $hoadon;
    }

    // Lấy hóa đơn theo ID
    public function getHoaDonById($id)
    {
        $sql = "SELECT * FROM hoadondatphong WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // Xóa hóa đơn
    public function deleteHoaDon($id)
    {
        try {
            $sql = "DELETE FROM hoadondatphong WHERE Id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            error_log("Lỗi xóa hóa đơn: " . $e->getMessage());
            return false;
        }
    }



    // Tính tổng doanh thu
    public function getTongDoanhThu()
    {
        $sql = "SELECT 
                    SUM(TongTien) as TongDoanhThu,
                    COUNT(*) as SoHoaDon,
                    AVG(TongTien) as TrungBinh
                FROM hoadondatphong 
                WHERE TrangThai = 'DaThanhToan'";

        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }

    // Lọc hóa đơn theo ngày
    public function filterHoaDonByDate($tuNgay, $denNgay)
    {
        $sql = "SELECT * FROM hoadondatphong 
                WHERE NgayTao BETWEEN ? AND ? 
                ORDER BY NgayTao DESC";

        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $tuNgay, $denNgay);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $hoadon = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $hoadon[] = $row;
        }
        return $hoadon;
    }

    // Thống kê theo phương thức thanh toán
    public function getThongKeThanhToan()
    {
        $sql = "SELECT 
                    PhuongThucThanhToan,
                    COUNT(*) as SoLuong,
                    SUM(TongTien) as TongTien
                FROM hoadondatphong 
                GROUP BY PhuongThucThanhToan";

        $result = mysqli_query($this->conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // Tìm kiếm hóa đơn
    public function searchHoaDon($keyword)
    {
        $sql = "SELECT * FROM hoadondatphong 
                WHERE MaKhachHang LIKE ? 
                   OR MaPhong LIKE ?
                   OR TenDichVu LIKE ?
                ORDER BY NgayTao DESC";

        $stmt = mysqli_prepare($this->conn, $sql);
        $searchKeyword = "%" . $keyword . "%";
        mysqli_stmt_bind_param($stmt, "sss", $searchKeyword, $searchKeyword, $searchKeyword);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $hoadon = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $hoadon[] = $row;
        }
        return $hoadon;
    }

    // Cập nhật trạng thái hóa đơn
    public function updateTrangThai($id, $trangThai)
    {
        $sql = "UPDATE hoadondatphong 
                SET TrangThai = ? 
                WHERE Id = ?";

        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $trangThai, $id);

        return mysqli_stmt_execute($stmt);
    }

    // Lấy thống kê theo tháng
    public function getThongKeTheoThang($nam)
    {
        $sql = "SELECT 
                    MONTH(NgayTao) as Thang,
                    COUNT(*) as SoHoaDon,
                    SUM(TongTien) as DoanhThu
                FROM hoadondatphong 
                WHERE YEAR(NgayTao) = ?
                GROUP BY MONTH(NgayTao)
                ORDER BY Thang";

        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $nam);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // Đóng kết nối
    public function __destruct()
    {
        if ($this->conn) {
            $db = new Connect();
            $db->closeConnect($this->conn);
        }
    }
}