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

    // Sửa method getAllKhuyenMai()
    public function getAllKhuyenMai()
    {
        $sql = "SELECT km.*, nv.HoTen as TenNhanVienTao,
           km.LoaiGiamGia, 
           km.DK_HoaDonTu, 
           km.DK_SoDemTu,
           km.GiamGiaToiDa
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
    // Thêm method này vào class KhuyenMaiModel
    public function getAllActivePromotions()
    {
        $today = date('Y-m-d');

        // Kiểm tra xem các trường mới đã có trong bảng chưa
        // Nếu chưa, dùng query đơn giản trước
        $sql = "SELECT MaKM, TenKhuyenMai, MucGiamGia, LoaiGiamGia, 
                   DK_HoaDonTu, DK_SoDemTu, NgayBatDau, NgayKetThuc, 
                   MoTa, HinhAnh, TrangThai
            FROM khuyenmai 
            WHERE TrangThai = 1 
            AND NgayBatDau <= ? 
            AND NgayKetThuc >= ?
            ORDER BY MucGiamGia DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        $promotions = [];
        while ($row = $result->fetch_assoc()) {
            // Đảm bảo các trường có giá trị mặc định nếu NULL
            $row['LoaiGiamGia'] = $row['LoaiGiamGia'] ?? 'phantram';
            $row['DK_HoaDonTu'] = $row['DK_HoaDonTu'] ?? null;
            $row['DK_SoDemTu'] = $row['DK_SoDemTu'] ?? null;
            $promotions[] = $row;
        }

        return $promotions;
    }
    // Sửa method addKhuyenMai() - THÊM THAM SỐ $giamGiaToiDa
    public function addKhuyenMai($tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $hinhAnh, $maNVTao, $loaiGiamGia = 'phantram', $dkHoaDonTu = null, $dkSoDemTu = null, $giamGiaToiDa = 0)
    {
        // Tự động tính trạng thái
        $today = date('Y-m-d');
        $trangThai = 1;
        if ($ngayKetThuc < $today) {
            $trangThai = 0;
        }

        // KIỂM TRA CHỈ ĐƯỢC NHẬP MỘT TRONG HAI
        // Nếu cả hai đều có giá trị, chỉ lấy một
        if ($dkHoaDonTu !== null && $dkSoDemTu !== null) {
            // Ưu tiên giữ DK_HoaDonTu, xóa DK_SoDemTu
            $dkSoDemTu = null;
        }

        $sql = "INSERT INTO khuyenmai (TenKhuyenMai, MucGiamGia, LoaiGiamGia, DK_HoaDonTu, DK_SoDemTu, NgayBatDau, NgayKetThuc, MoTa, HinhAnh, TrangThai, MaNhanVienTao, GiamGiaToiDa) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("MODEL DEBUG - Prepare failed: " . $this->conn->error);
            return false;
        }

        $types = "sdssdssssiid";
        $stmt->bind_param(
            $types,
            $tenKM,
            $mucGiamGia,
            $loaiGiamGia,
            $dkHoaDonTu,
            $dkSoDemTu,
            $ngayBatDau,
            $ngayKetThuc,
            $moTa,
            $hinhAnh,
            $trangThai,
            $maNVTao,
            $giamGiaToiDa
        );

        return $stmt->execute();
    }

    // Cập nhật method updateKhuyenMai() - THÊM THAM SỐ $giamGiaToiDa
    public function updateKhuyenMai($maKM, $tenKM, $mucGiamGia, $ngayBatDau, $ngayKetThuc, $moTa, $hinhAnh = null, $loaiGiamGia = 'phantram', $dkHoaDonTu = null, $dkSoDemTu = null, $giamGiaToiDa = 0)
    {
        // Tự động tính trạng thái
        $today = date('Y-m-d');
        $trangThai = ($ngayKetThuc < $today) ? 0 : 1;

        // KIỂM TRA LOGIC: Nếu nhập DK mới thì xóa DK cũ
        // Trước tiên, lấy thông tin hiện tại
        $sqlCurrent = "SELECT DK_HoaDonTu, DK_SoDemTu FROM khuyenmai WHERE MaKM = ?";
        $stmtCurrent = $this->conn->prepare($sqlCurrent);
        $stmtCurrent->bind_param("i", $maKM);
        $stmtCurrent->execute();
        $resultCurrent = $stmtCurrent->get_result();
        $currentData = $resultCurrent->fetch_assoc();

        // Xác định giá trị cuối cùng
        $finalHoaDonTu = $currentData['DK_HoaDonTu'];
        $finalSoDemTu = $currentData['DK_SoDemTu'];

        // Trong updateKhuyenMai() - đã có sẵn logic này
        // Nếu nhập DK mới thì xóa DK cũ
        if ($dkHoaDonTu !== null && $dkHoaDonTu !== '') {
            $finalHoaDonTu = $dkHoaDonTu;
            $finalSoDemTu = null; // Xóa số đêm
        } elseif ($dkSoDemTu !== null && $dkSoDemTu !== '') {
            $finalSoDemTu = $dkSoDemTu;
            $finalHoaDonTu = null; // Xóa hóa đơn
        }
        // Nếu không nhập gì => giữ nguyên

        // Xử lý ảnh
        $stmt = null;
        if ($hinhAnh) {
            // Xóa ảnh cũ
            $sqlOld = "SELECT HinhAnh FROM khuyenmai WHERE MaKM = ?";
            $stmtOld = $this->conn->prepare($sqlOld);
            $stmtOld->bind_param("i", $maKM);
            $stmtOld->execute();
            $resultOld = $stmtOld->get_result();

            if ($resultOld && $resultOld->num_rows > 0) {
                $rowOld = $resultOld->fetch_assoc();
                $oldImage = $rowOld['HinhAnh'] ?? '';

                if ($oldImage && $oldImage !== 'assets/images/sales/default_promotion.png') {
                    $oldFileName = basename($oldImage);
                    $oldFilePath = 'C:/xampp/htdocs/ABC-Resort/client/assets/images/sales/' . $oldFileName;

                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
            }

            // Cập nhật với ảnh mới và GiamGiaToiDa
            $sql = "UPDATE khuyenmai SET 
                TenKhuyenMai = ?, 
                MucGiamGia = ?, 
                LoaiGiamGia = ?, 
                DK_HoaDonTu = ?, 
                DK_SoDemTu = ?, 
                NgayBatDau = ?, 
                NgayKetThuc = ?, 
                MoTa = ?, 
                HinhAnh = ?,
                TrangThai = ?,
                GiamGiaToiDa = ?
                WHERE MaKM = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sdssdssssidi",
                $tenKM,
                $mucGiamGia,
                $loaiGiamGia,
                $finalHoaDonTu,
                $finalSoDemTu,
                $ngayBatDau,
                $ngayKetThuc,
                $moTa,
                $hinhAnh,
                $trangThai,
                $giamGiaToiDa,
                $maKM
            );
        } else {
            // Không thay đổi ảnh, có GiamGiaToiDa
            $sql = "UPDATE khuyenmai SET 
                TenKhuyenMai = ?, 
                MucGiamGia = ?, 
                LoaiGiamGia = ?, 
                DK_HoaDonTu = ?, 
                DK_SoDemTu = ?, 
                NgayBatDau = ?, 
                NgayKetThuc = ?, 
                MoTa = ?,
                TrangThai = ?,
                GiamGiaToiDa = ?
                WHERE MaKM = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sdssdsssidi",
                $tenKM,
                $mucGiamGia,
                $loaiGiamGia,
                $finalHoaDonTu,
                $finalSoDemTu,
                $ngayBatDau,
                $ngayKetThuc,
                $moTa,
                $trangThai,
                $giamGiaToiDa,
                $maKM
            );
        }

        if ($stmt) {
            return $stmt->execute();
        }

        error_log("ERROR: Statement not initialized in updateKhuyenMai");
        return false;
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
