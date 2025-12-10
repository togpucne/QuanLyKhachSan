<?php
require_once 'connectDB.php';

class KhuyenMaiModel
{
    private $conn;

    public function __construct()
    {
        $p = new Connect();
        $this->conn = $p->openConnect();

        if (!$this->conn) {
            die("Kết nối database thất bại");
        }
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Lấy tất cả khuyến mãi
    public function getAllKhuyenMai()
    {
        $sql = "SELECT km.*, nv.HoTen as TenNhanVienTao
                FROM khuyenmai km
                LEFT JOIN nhanvien nv ON km.MaNhanVienTao = nv.MaNhanVien
                ORDER BY km.MaKM DESC";

        $result = $this->conn->query($sql);

        if (!$result) {
            error_log("SQL Error: " . $this->conn->error);
            return false;
        }

        return $result;
    }

    // Thêm khuyến mãi mới với ảnh
    public function addKhuyenMai($tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $hinhAnh, $maNVTao)
    {
        // Tự động tính trạng thái
        $today = date('Y-m-d');
        $trangThai = 1; // Mặc định là hoạt động

        // Nếu ngày kết thúc đã qua thì đánh dấu là đã kết thúc
        if ($ngayKetThuc < $today) {
            $trangThai = 0; // Đã kết thúc
        }

        $sql = "INSERT INTO khuyenmai (TenKhuyenMai, MucGiamGia, NgayBatDau, NgayKetThuc, MoTa, HinhAnh, TrangThai, MaNhanVienTao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdssssii", $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $hinhAnh, $trangThai, $maNVTao);

        return $stmt->execute();
    }

    public function deleteKhuyenMai($maKM)
    {
        // Lấy thông tin ảnh trước khi xóa để xóa file
        $sql = "SELECT HinhAnh FROM khuyenmai WHERE MaKM = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maKM);
        $stmt->execute();
        $result = $stmt->get_result();

        // Kiểm tra và xóa ảnh nếu có
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hinhAnh = $row['HinhAnh'] ?? '';

            // Chỉ xóa nếu có ảnh và không phải ảnh mặc định
            if ($hinhAnh && $hinhAnh !== 'assets/images/sales/default_promotion.png') {
                // SỬA: Thêm assets/ vào đường dẫn
                $fileName = basename($hinhAnh);
                $filePath = 'C:/xampp/htdocs/ABC-Resort/client/assets/images/sales/' . $fileName;

                // Debug
                error_log("Deleting image: $filePath");
                error_log("File exists: " . (file_exists($filePath) ? 'Yes' : 'No'));

                // Kiểm tra file tồn tại trước khi xóa
                if (file_exists($filePath)) {
                    if (unlink($filePath)) {
                        error_log("Image deleted successfully");
                    } else {
                        error_log("Failed to delete image");
                    }
                }
            }
        }

        // Xóa khuyến mãi trong database
        $sql = "DELETE FROM khuyenmai WHERE MaKM = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maKM);
        return $stmt->execute();
    }
    public function updateKhuyenMai($maKM, $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $hinhAnh = null)
    {
        // Tự động tính trạng thái
        $today = date('Y-m-d');
        $trangThai = ($ngayKetThuc < $today) ? 0 : 1;

        // Khai báo biến $stmt
        $stmt = null;

        // Nếu có upload ảnh mới, xóa ảnh cũ
        if ($hinhAnh) {
            // Lấy đường dẫn ảnh cũ từ database
            $sqlOld = "SELECT HinhAnh FROM khuyenmai WHERE MaKM = ?";
            $stmtOld = $this->conn->prepare($sqlOld);
            $stmtOld->bind_param("i", $maKM);
            $stmtOld->execute();
            $resultOld = $stmtOld->get_result();

            if ($resultOld && $resultOld->num_rows > 0) {
                $rowOld = $resultOld->fetch_assoc();
                $oldImage = $rowOld['HinhAnh'] ?? '';

                // Xóa ảnh cũ nếu tồn tại và không phải ảnh mặc định
                if ($oldImage && $oldImage !== 'assets/images/sales/default_promotion.png') {
                    // SỬA: Thêm assets/ vào đường dẫn
                    $oldFileName = basename($oldImage);
                    $oldFilePath = 'C:/xampp/htdocs/ABC-Resort/client/assets/images/sales/' . $oldFileName;

                    // Debug
                    error_log("Updating - Deleting old image: $oldFilePath");
                    error_log("File exists: " . (file_exists($oldFilePath) ? 'Yes' : 'No'));

                    if (file_exists($oldFilePath)) {
                        if (unlink($oldFilePath)) {
                            error_log("Old image deleted successfully");
                        } else {
                            error_log("Failed to delete old image");
                        }
                    }
                }
            }

            // Cập nhật với ảnh mới
            $sql = "UPDATE khuyenmai SET 
                TenKhuyenMai = ?, 
                MucGiamGia = ?, 
                NgayBatDau = ?, 
                NgayKetThuc = ?, 
                MoTa = ?, 
                HinhAnh = ?,
                TrangThai = ?
                WHERE MaKM = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sdssssii", $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $hinhAnh, $trangThai, $maKM);
        } else {
            // Không thay đổi ảnh
            $sql = "UPDATE khuyenmai SET 
                TenKhuyenMai = ?, 
                MucGiamGia = ?, 
                NgayBatDau = ?, 
                NgayKetThuc = ?, 
                MoTa = ?,
                TrangThai = ?
                WHERE MaKM = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sdsssii", $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $trangThai, $maKM);
        }

        // Kiểm tra và thực thi
        if ($stmt) {
            return $stmt->execute();
        }

        error_log("ERROR: Statement not initialized in updateKhuyenMai");
        return false;
    }
    // Lấy thông tin khuyến mãi theo ID
    public function getById($maKM)
    {
        $sql = "SELECT * FROM khuyenmai WHERE MaKM = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maKM);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
