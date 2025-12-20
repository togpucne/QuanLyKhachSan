<?php
include_once 'connectDB.php';

class QuanLyThietBiModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Connect();
    }

    // LẤY DANH SÁCH PHÒNG VỚI SỐ LƯỢNG THIẾT BỊ
    public function getDanhSachPhong()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
                    p.MaPhong, 
                    p.SoPhong, 
                    p.roomName,
                    p.TrangThai,
                    COUNT(tb.MaThietBi) as so_thiet_bi
                FROM phong p
                LEFT JOIN thietbi tb ON p.MaPhong = tb.MaPhong
                GROUP BY p.MaPhong
                ORDER BY p.SoPhong";

        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // LẤY THIẾT BỊ THEO PHÒNG
    public function getThietBiByPhong($maPhong)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT * FROM thietbi WHERE MaPhong = ? ORDER BY TenThietBi";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $maPhong);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // LẤY TẤT CẢ LOẠI THIẾT BỊ ĐÃ CÓ TRONG CSDL
    public function getDanhSachLoaiThietBi()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT DISTINCT TenThietBi FROM thietbi ORDER BY TenThietBi";
        $result = $conn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row['TenThietBi'];
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // KIỂM TRA THIẾT BỊ ĐÃ CÓ TRONG PHÒNG CHƯA
    public function kiemTraThietBiTrongPhong($maPhong, $tenThietBi)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM thietbi 
                WHERE MaPhong = ? AND TenThietBi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $maPhong, $tenThietBi);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }

    // THÊM THIẾT BỊ VÀO PHÒNG (CÓ KIỂM TRA TRÙNG)
    public function themThietBiVaoPhong($maPhong, $tenThietBi, $tinhTrang, $soLuong = 1)
    {
        $conn = $this->db->openConnect();

        try {
            // KIỂM TRA TRƯỚC KHI THÊM
            if ($this->kiemTraThietBiTrongPhong($maPhong, $tenThietBi)) {
                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => "Thiết bị '{$tenThietBi}' đã có trong phòng này"
                ];
            }

            $conn->begin_transaction();

            $thanhCong = 0;
            $errors = [];

            for ($i = 0; $i < $soLuong; $i++) {
                try {
                    $sql = "INSERT INTO thietbi (TenThietBi, TinhTrang, MaPhong) 
                            VALUES (?, ?, ?)";

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $tenThietBi, $tinhTrang, $maPhong);

                    if ($stmt->execute()) {
                        $thanhCong++;
                    } else {
                        $errors[] = "Lỗi khi thêm thiết bị thứ " . ($i + 1);
                    }
                } catch (Exception $e) {
                    $errors[] = "Lỗi: " . $e->getMessage();
                }
            }

            if ($thanhCong > 0) {
                $conn->commit();
                $this->db->closeConnect($conn);
                return [
                    'success' => true,
                    'so_luong' => $thanhCong,
                    'errors' => $errors,
                    'message' => "Đã thêm {$thanhCong} thiết bị '{$tenThietBi}' vào phòng"
                ];
            } else {
                $conn->rollback();
                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => "Không thể thêm thiết bị. Lỗi: " . implode(", ", $errors)
                ];
            }
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // THÊM THIẾT BỊ MỚI VÀO HỆ THỐNG (KHÔNG GÁN PHÒNG)
    public function themThietBiMoi($tenThietBi)
    {
        $conn = $this->db->openConnect();

        try {
            // Kiểm tra thiết bị đã tồn tại trong hệ thống chưa
            $sqlCheck = "SELECT COUNT(*) as count FROM thietbi WHERE TenThietBi = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("s", $tenThietBi);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $row = $resultCheck->fetch_assoc();

            if ($row['count'] > 0) {
                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => "Thiết bị '{$tenThietBi}' đã có trong hệ thống"
                ];
            }

            $sql = "INSERT INTO thietbi (TenThietBi, TinhTrang, MaPhong) 
                    VALUES (?, 'Tốt', NULL)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $tenThietBi);

            if ($stmt->execute()) {
                $this->db->closeConnect($conn);
                return [
                    'success' => true,
                    'message' => 'Đã thêm thiết bị mới vào hệ thống'
                ];
            } else {
                throw new Exception('Lỗi khi thêm thiết bị');
            }
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // XÓA THIẾT BỊ
    public function xoaThietBi($maThietBi)
    {
        $conn = $this->db->openConnect();

        try {
            // Lấy thông tin thiết bị trước khi xóa
            $sqlSelect = "SELECT TenThietBi FROM thietbi WHERE MaThietBi = ?";
            $stmtSelect = $conn->prepare($sqlSelect);
            $stmtSelect->bind_param("i", $maThietBi);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();

            if ($result->num_rows === 0) {
                $this->db->closeConnect($conn);
                return ['success' => false, 'error' => 'Không tìm thấy thiết bị'];
            }

            $row = $result->fetch_assoc();
            $tenThietBi = $row['TenThietBi'];

            // Xóa thiết bị
            $sql = "DELETE FROM thietbi WHERE MaThietBi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $maThietBi);

            if ($stmt->execute()) {
                $this->db->closeConnect($conn);
                return [
                    'success' => true,
                    'message' => "Đã xóa thiết bị '{$tenThietBi}'"
                ];
            } else {
                throw new Exception('Lỗi khi xóa thiết bị');
            }
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // CHUYỂN THIẾT BỊ SANG PHÒNG KHÁC
    public function chuyenThietBi($maThietBi, $maPhongMoi)
    {
        $conn = $this->db->openConnect();

        try {
            // Lấy thông tin thiết bị hiện tại
            $sqlSelect = "SELECT TenThietBi, MaPhong FROM thietbi WHERE MaThietBi = ?";
            $stmtSelect = $conn->prepare($sqlSelect);
            $stmtSelect->bind_param("i", $maThietBi);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();

            if ($result->num_rows === 0) {
                $this->db->closeConnect($conn);
                return ['success' => false, 'error' => 'Không tìm thấy thiết bị'];
            }

            $thietbi = $result->fetch_assoc();
            $tenThietBi = $thietbi['TenThietBi'];
            $maPhongCu = $thietbi['MaPhong'];

            // Kiểm tra thiết bị đã có trong phòng mới chưa
            if ($this->kiemTraThietBiTrongPhong($maPhongMoi, $tenThietBi)) {
                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => "Thiết bị '{$tenThietBi}' đã có trong phòng này"
                ];
            }

            // Chuyển thiết bị
            $sql = "UPDATE thietbi SET MaPhong = ? WHERE MaThietBi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $maPhongMoi, $maThietBi);

            if ($stmt->execute()) {
                $this->db->closeConnect($conn);

                // Lấy thông tin phòng mới để hiển thị
                $phongInfo = $this->getPhongInfo($maPhongMoi);
                $soPhongMoi = $phongInfo ? $phongInfo['SoPhong'] : $maPhongMoi;

                return [
                    'success' => true,
                    'message' => "Đã chuyển thiết bị '{$tenThietBi}' sang phòng {$soPhongMoi}"
                ];
            } else {
                throw new Exception('Lỗi khi chuyển thiết bị');
            }
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // LẤY THÔNG TIN PHÒNG
    private function getPhongInfo($maPhong)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT SoPhong FROM phong WHERE MaPhong = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $maPhong);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $phong = $result->fetch_assoc();
            $this->db->closeConnect($conn);
            return $phong;
        }

        $this->db->closeConnect($conn);
        return null;
    }

    // LẤY THÔNG TIN THIẾT BỊ
    public function getThietBiById($maThietBi)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT tb.*, p.SoPhong, p.roomName 
                FROM thietbi tb 
                LEFT JOIN phong p ON tb.MaPhong = p.MaPhong 
                WHERE tb.MaThietBi = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $maThietBi);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $thietbi = $result->fetch_assoc();
            $this->db->closeConnect($conn);
            return $thietbi;
        }

        $this->db->closeConnect($conn);
        return null;
    }

    // THỐNG KÊ
    public function getThongKe()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
                    COUNT(*) as tong_thiet_bi,
                    COUNT(DISTINCT MaPhong) as so_phong_co_thiet_bi,
                    COUNT(DISTINCT TenThietBi) as so_loai_thiet_bi
                FROM thietbi";

        $result = $conn->query($sql);
        $thongKe = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $thongKe;
    }
    // THÊM THIẾT BỊ MỚI VÀO PHÒNG LUÔN
    public function themThietBiMoiVaoPhong($maPhong, $tenThietBi, $tinhTrang, $soLuong = 1)
    {
        $conn = $this->db->openConnect();

        try {
            // Kiểm tra thiết bị đã có trong phòng chưa
            if ($this->kiemTraThietBiTrongPhong($maPhong, $tenThietBi)) {
                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => "Thiết bị '{$tenThietBi}' đã có trong phòng này"
                ];
            }

            $conn->begin_transaction();

            $thanhCong = 0;
            $errors = [];

            for ($i = 0; $i < $soLuong; $i++) {
                try {
                    $sql = "INSERT INTO thietbi (TenThietBi, TinhTrang, MaPhong) 
                        VALUES (?, ?, ?)";

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $tenThietBi, $tinhTrang, $maPhong);

                    if ($stmt->execute()) {
                        $thanhCong++;
                    } else {
                        $errors[] = "Lỗi khi thêm thiết bị thứ " . ($i + 1);
                    }
                } catch (Exception $e) {
                    $errors[] = "Lỗi: " . $e->getMessage();
                }
            }

            if ($thanhCong > 0) {
                $conn->commit();
                $this->db->closeConnect($conn);

                // Thêm vào danh sách loại thiết bị nếu chưa có
                if (!$this->kiemTraThietBiTonTaiTrongHeThong($tenThietBi)) {
                    $this->themThietBiMoi($tenThietBi);
                }

                return [
                    'success' => true,
                    'so_luong' => $thanhCong,
                    'errors' => $errors,
                    'message' => "Đã thêm {$thanhCong} thiết bị '{$tenThietBi}' vào phòng"
                ];
            } else {
                $conn->rollback();
                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => "Không thể thêm thiết bị. Lỗi: " . implode(", ", $errors)
                ];
            }
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // KIỂM TRA THIẾT BỊ CÓ TỒN TẠI TRONG HỆ THỐNG KHÔNG
    public function kiemTraThietBiTonTaiTrongHeThong($tenThietBi)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM thietbi WHERE TenThietBi = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tenThietBi);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }
    // XÓA LOẠI THIẾT BỊ KHỎI HỆ THỐNG (XÓA TẤT CẢ THIẾT BỊ CÙNG LOẠI)
    public function xoaLoaiThietBi($tenThietBi)
    {
        $conn = $this->db->openConnect();

        try {
            // Kiểm tra xem có thiết bị nào đang được sử dụng trong phòng không
            $sqlCheckPhong = "SELECT COUNT(*) as count FROM thietbi WHERE TenThietBi = ? AND MaPhong IS NOT NULL";
            $stmtCheck = $conn->prepare($sqlCheckPhong);
            $stmtCheck->bind_param("s", $tenThietBi);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $row = $resultCheck->fetch_assoc();

            if ($row['count'] > 0) {
                // Đếm số phòng đang sử dụng
                $sqlCountPhong = "SELECT COUNT(DISTINCT MaPhong) as so_phong FROM thietbi WHERE TenThietBi = ? AND MaPhong IS NOT NULL";
                $stmtCount = $conn->prepare($sqlCountPhong);
                $stmtCount->bind_param("s", $tenThietBi);
                $stmtCount->execute();
                $resultCount = $stmtCount->get_result();
                $rowCount = $resultCount->fetch_assoc();

                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => "Không thể xóa! Thiết bị '$tenThietBi' đang được sử dụng trong {$rowCount['so_phong']} phòng."
                ];
            }

            // Xóa tất cả thiết bị cùng loại
            $sql = "DELETE FROM thietbi WHERE TenThietBi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $tenThietBi);

            if ($stmt->execute()) {
                $rowsAffected = $stmt->affected_rows;
                $this->db->closeConnect($conn);
                return [
                    'success' => true,
                    'message' => "Đã xóa loại thiết bị '$tenThietBi' khỏi hệ thống",
                    'so_luong_xoa' => $rowsAffected
                ];
            } else {
                throw new Exception('Lỗi khi xóa thiết bị');
            }
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
