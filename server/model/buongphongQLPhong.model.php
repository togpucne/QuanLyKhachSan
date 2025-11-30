<?php
require_once 'connectDB.php';

class PhongModel
{
    private $conn;

    public function __construct()
    {
        $db = new Connect();
        $this->conn = $db->openConnect();
    }


    // Lấy danh sách phòng - CẬP NHẬT THÊM TÊN PHÒNG
    public function getDanhSachPhong()
    {
        $sql = "SELECT 
                p.MaPhong, 
                p.SoPhong, 
                p.Tang, 
                lp.HangPhong, 
                p.roomName,           -- THÊM Tên phòng
                p.GiaPhong,           
                p.TongGia,            
                p.DienTich,           
                p.SoKhachToiDa,       
                p.TrangThai 
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

    // Cập nhật trạng thái phòng VÀ ghi log
    public function capNhatTrangThai($maPhong, $trangThaiMoi, $maNhanVien, $lyDo = '', $ghiChuKyThuat = '')
    {
        error_log("=== MODEL DEBUG ===");
        error_log("MaPhong: $maPhong, TrangThaiMoi: $trangThaiMoi, MaNhanVien: $maNhanVien");
        error_log("LyDo: $lyDo, GhiChuKyThuat: $ghiChuKyThuat");

        // Bắt đầu transaction
        mysqli_begin_transaction($this->conn);

        try {
            // 1. Lấy trạng thái hiện tại
            $sqlCurrent = "SELECT TrangThai FROM phong WHERE MaPhong = ?";
            $stmtCurrent = mysqli_prepare($this->conn, $sqlCurrent);
            mysqli_stmt_bind_param($stmtCurrent, "i", $maPhong);
            mysqli_stmt_execute($stmtCurrent);
            $result = mysqli_stmt_get_result($stmtCurrent);
            $row = mysqli_fetch_assoc($result);

            if (!$row) {
                throw new Exception("Không tìm thấy phòng với mã: $maPhong");
            }

            $currentStatus = $row['TrangThai'];
            mysqli_stmt_close($stmtCurrent);

            // 2. Kiểm tra nếu trạng thái không thay đổi
            if ($currentStatus === $trangThaiMoi) {
                return ['success' => false, 'message' => 'Phòng đã ở trạng thái "' . $trangThaiMoi . '", không cần cập nhật'];
            }

            // 3. Cập nhật trạng thái mới
            $sqlUpdate = "UPDATE phong SET TrangThai = ? WHERE MaPhong = ?";
            $stmtUpdate = mysqli_prepare($this->conn, $sqlUpdate);
            mysqli_stmt_bind_param($stmtUpdate, "si", $trangThaiMoi, $maPhong);
            $updateResult = mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);

            if (!$updateResult) {
                throw new Exception("Lỗi cập nhật trạng thái phòng");
            }

            // 4. Ghi log vào bảng phong_capnhat_trangthai (THÊM GHI CHÚ KỸ THUẬT)
            $sqlLog = "INSERT INTO phong_capnhat_trangthai (MaPhong, TrangThaiCu, TrangThaiMoi, MaNhanVien, GhiChuKyThuat, LyDo) 
              VALUES (?, ?, ?, ?, ?, ?)";

            error_log("SQL INSERT: $sqlLog");

            $stmtLog = mysqli_prepare($this->conn, $sqlLog);
            mysqli_stmt_bind_param($stmtLog, "ississ", $maPhong, $currentStatus, $trangThaiMoi, $maNhanVien, $ghiChuKyThuat, $lyDo);
            $logResult = mysqli_stmt_execute($stmtLog);

            error_log("Kết quả INSERT: " . ($logResult ? "THÀNH CÔNG" : "THẤT BẠI"));
            if (!$logResult) {
                error_log("Lỗi MySQL: " . mysqli_error($this->conn));
            }

            mysqli_stmt_close($stmtLog);

            if (!$logResult) {
                throw new Exception("Lỗi ghi log thay đổi trạng thái");
            }

            // Commit transaction
            mysqli_commit($this->conn);

            return ['success' => true, 'message' => 'Cập nhật trạng thái thành công! Từ "' . $currentStatus . '" sang "' . $trangThaiMoi . '"'];
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            mysqli_rollback($this->conn);
            error_log("Lỗi cập nhật trạng thái: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    // Tạm thời comment các hàm chưa dùng đến
    /*
    public function ghiNhanSuCo($maPhong, $moTaSuCo, $chiPhi) {
        // Sẽ làm sau khi có bảng sự cố
    }

    public function ghiNhanChiPhi($maPhong, $loaiChiPhi, $soTien) {
        // Sẽ làm sau khi có bảng chi phí
    }
    */
}
