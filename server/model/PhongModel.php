<?php
require_once 'connectDB.php';

class PhongModel
{
    private $conn;

    public function __construct()
    {
        require_once 'connectDB.php';
        $db = new Connect();
        $this->conn = $db->openConnect();
    }

    // Lấy danh sách phòng - BỎ DEBUG COMMENTS
    public function getDanhSachPhong()
    {
        $sql = "SELECT p.MaPhong, p.SoPhong, p.Tang, lp.HangPhong, lp.DonGia, p.TrangThai 
            FROM phong p 
            JOIN loaiphong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            ORDER BY p.SoPhong";

        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            return array();
        }

        $dsPhong = array();
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dsPhong[] = $row;
            }
        }

        return $dsPhong;
    }

    // Cập nhật trạng thái phòng - THÊM DEBUG LOG
    public function capNhatTrangThai($maPhong, $trangThai)
    {
        error_log("Cập nhật trạng thái: MaPhong=$maPhong, TrangThai=$trangThai");
        
        $sql = "UPDATE Phong SET TrangThai = ? WHERE MaPhong = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("Lỗi prepare: " . mysqli_error($this->conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $trangThai, $maPhong);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            error_log("Cập nhật THÀNH CÔNG");
        } else {
            error_log("Cập nhật THẤT BẠI: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }


    // Ghi nhận sự cố
    public function ghiNhanSuCo($maPhong, $moTaSuCo, $chiPhi)
    {
        $sql = "INSERT INTO SuCo (MaPhong, MoTaSuCo, ChiPhi, ThoiGian) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "isd", $maPhong, $moTaSuCo, $chiPhi);
        return mysqli_stmt_execute($stmt);
    }

    // Ghi nhận chi phí
    public function ghiNhanChiPhi($maPhong, $loaiChiPhi, $soTien)
    {
        $sql = "INSERT INTO ChiPhi (MaPhong, LoaiChiPhi, SoTien, ThoiGian) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "isd", $maPhong, $loaiChiPhi, $soTien);
        return mysqli_stmt_execute($stmt);
    }

    // Tìm kiếm phòng
    public function timKiemPhong($keyword)
    {
        $sql = "SELECT p.MaPhong, p.SoPhong, p.Tang, lp.HangPhong, lp.DonGia, p.TrangThai 
                FROM Phong p 
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                WHERE p.SoPhong LIKE ? OR lp.HangPhong LIKE ? OR p.TrangThai LIKE ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        $searchTerm = "%$keyword%";
        mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $dsPhong = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $dsPhong[] = $row;
        }
        return $dsPhong;
    }
}
