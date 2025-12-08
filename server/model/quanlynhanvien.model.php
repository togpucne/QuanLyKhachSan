<?php
include_once 'connectDB.php';

class QuanLyNhanVienModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Connect();
    }

    // TẠO MÃ NHÂN VIÊN TỰ ĐỘNG
    public function generateMaNhanVien()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT MaNhanVien FROM NhanVien WHERE MaNhanVien LIKE 'NV%' ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_maNV = $row['MaNhanVien'];
            $last_number = intval(substr($last_maNV, 2));
            $new_number = $last_number + 1;
            $new_maNV = 'NV' . sprintf('%03d', $new_number);
        } else {
            $new_maNV = 'NV001';
        }

        $this->db->closeConnect($conn);
        return $new_maNV;
    }

    // LẤY DANH SÁCH TẤT CẢ NHÂN VIÊN (KÈM TÀI KHOẢN)
    public function getDanhSachNhanVien()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT nv.*, tk.Email, tk.VaiTro, tk.TrangThai as TrangThaiTK
                FROM NhanVien nv 
                LEFT JOIN tai_khoan tk ON nv.MaTaiKhoan = tk.id 
                ORDER BY nv.created_at DESC";

        $result = $conn->query($sql);

        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // LẤY TÀI KHOẢN CHƯA GẮN NHÂN VIÊN (SỬA LẠI)
    public function getTaiKhoanChuaGanNhanVien()
    {
        $conn = $this->db->openConnect();

        // Sửa: Lấy tất cả tài khoản KHÔNG PHẢI khách hàng và chưa gán
        $sql = "SELECT tk.* 
                FROM tai_khoan tk 
                WHERE tk.VaiTro != 'khachhang'
                AND tk.id NOT IN (
                    SELECT MaTaiKhoan FROM NhanVien WHERE MaTaiKhoan IS NOT NULL
                )
                ORDER BY tk.VaiTro, tk.Email";

        $result = $conn->query($sql);

        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // LẤY CHI TIẾT NHÂN VIÊN (KÈM TÀI KHOẢN)
    public function getChiTietNhanVien($maNhanVien)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT nv.*, tk.Email, tk.VaiTro, tk.TrangThai as TrangThaiTK
                FROM NhanVien nv 
                LEFT JOIN tai_khoan tk ON nv.MaTaiKhoan = tk.id 
                WHERE nv.MaNhanVien = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maNhanVien);
        $stmt->execute();
        $result = $stmt->get_result();

        $nhanVien = $result->fetch_assoc();
        $this->db->closeConnect($conn);
        return $nhanVien;
    }

    // TÌM KIẾM NHÂN VIÊN
    public function timKiemNhanVien($keyword)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT nv.*, tk.Email, tk.VaiTro, tk.TrangThai as TrangThaiTK
                FROM NhanVien nv 
                LEFT JOIN tai_khoan tk ON nv.MaTaiKhoan = tk.id 
                WHERE nv.MaNhanVien LIKE ? 
                OR nv.HoTen LIKE ? 
                OR nv.SDT LIKE ? 
                OR tk.Email LIKE ? 
                OR nv.PhongBan LIKE ? 
                ORDER BY nv.created_at DESC";

        $stmt = $conn->prepare($sql);
        $searchTerm = "%$keyword%";
        $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // THÊM NHÂN VIÊN MỚI (VỚI EMAIL VÀ MẬT KHẨU TỰ NHẬP)
    public function themNhanVien($data)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // 1. KIỂM TRA EMAIL ĐÃ TỒN TẠI CHƯA
            $sql_check = "SELECT id FROM tai_khoan WHERE Email = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $data['email']);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                throw new Exception("Email đã tồn tại trong hệ thống!");
            }

            // 2. TẠO TÀI KHOẢN MỚI
            $matKhauMd5 = md5($data['mat_khau']);
            $tenDangNhap = $data['email'];
            $vaiTro = $this->convertPhongBanToVaiTro($data['PhongBan']);

            $sql_tk = "INSERT INTO tai_khoan 
                      (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND, created_at, updated_at) 
                      VALUES (?, ?, ?, '1', ?, ?, NOW(), NOW())";

            $stmt_tk = $conn->prepare($sql_tk);
            $cmnd = $data['CMND'] ?? '';
            $stmt_tk->bind_param("sssss", $tenDangNhap, $matKhauMd5, $vaiTro, $data['email'], $cmnd);

            if (!$stmt_tk->execute()) {
                throw new Exception("Lỗi tạo tài khoản: " . $stmt_tk->error);
            }

            $maTaiKhoan = $stmt_tk->insert_id;

            // 3. TẠO MÃ NHÂN VIÊN
            $maNhanVien = $this->generateMaNhanVien();

            // 4. THÊM NHÂN VIÊN
            $sql = "INSERT INTO NhanVien (
                    MaNhanVien, HoTen, DiaChi, SDT, NgayVaoLam, 
                    NgayNghiViec, PhongBan, LuongCoBan, TrangThai, MaTaiKhoan,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssdss",
                $maNhanVien,
                $data['HoTen'],
                $data['DiaChi'],
                $data['SDT'],
                $data['NgayVaoLam'],
                $data['NgayNghiViec'],
                $data['PhongBan'],
                $data['LuongCoBan'],
                $data['TrangThai'],
                $maTaiKhoan
            );

            if (!$stmt->execute()) {
                throw new Exception("Lỗi thêm nhân viên: " . $stmt->error);
            }

            $conn->commit();
            $this->db->closeConnect($conn);

            return [
                'success' => true,
                'maNhanVien' => $maNhanVien,
                'email' => $data['email'],
                'maTaiKhoan' => $maTaiKhoan
            ];
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            error_log("Lỗi thêm nhân viên: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // TẠO TÀI KHOẢN MỚI CHO NHÂN VIÊN (HÀM MỚI)
    public function taoTaiKhoanChoNhanVien($data)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // Kiểm tra email đã tồn tại chưa
            $sql_check = "SELECT id FROM tai_khoan WHERE Email = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $data['email']);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                throw new Exception("Email đã tồn tại trong hệ thống!");
            }

            // Tạo tài khoản mới
            $matKhauMd5 = md5($data['mat_khau']); // Mã hóa mật khẩu
            $tenDangNhap = $data['email']; // Dùng email làm tên đăng nhập
            $vaiTro = strtolower($data['vai_tro']); // Chuyển về chữ thường (kinhdoanh, letan, etc)

            $sql = "INSERT INTO tai_khoan 
                    (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND, created_at, updated_at) 
                    VALUES (?, ?, ?, '1', ?, ?, NOW(), NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssss",
                $tenDangNhap,
                $matKhauMd5,
                $vaiTro,
                $data['email'],
                $data['cmnd']
            );

            if (!$stmt->execute()) {
                throw new Exception("Lỗi tạo tài khoản: " . $stmt->error);
            }

            $maTaiKhoan = $stmt->insert_id;
            $conn->commit();

            $this->db->closeConnect($conn);
            return ['success' => true, 'maTaiKhoan' => $maTaiKhoan];
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            error_log("Lỗi tạo tài khoản: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // SỬA NHÂN VIÊN (CÓ THỂ RESET MẬT KHẨU)
    public function suaNhanVien($maNhanVien, $data)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // 1. CẬP NHẬT THÔNG TIN NHÂN VIÊN
            $sql = "UPDATE NhanVien SET 
                    HoTen = ?, 
                    DiaChi = ?, 
                    SDT = ?, 
                    NgayVaoLam = ?, 
                    NgayNghiViec = ?, 
                    PhongBan = ?, 
                    LuongCoBan = ?, 
                    TrangThai = ?,
                    updated_at = NOW()
                    WHERE MaNhanVien = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssdss",
                $data['HoTen'],
                $data['DiaChi'],
                $data['SDT'],
                $data['NgayVaoLam'],
                $data['NgayNghiViec'],
                $data['PhongBan'],
                $data['LuongCoBan'],
                $data['TrangThai'],
                $maNhanVien
            );

            if (!$stmt->execute()) {
                throw new Exception("Lỗi cập nhật nhân viên: " . $stmt->error);
            }

            // 2. NẾU CÓ YÊU CẦU RESET MẬT KHẨU
            if (isset($data['reset_mat_khau']) && $data['reset_mat_khau'] == '1' && !empty($data['ma_tai_khoan'])) {
                $matKhauMoi = $data['mat_khau_moi'] ?? '123456'; // Mặc định reset về 123456
                $matKhauMd5 = md5($matKhauMoi);

                $sql_reset = "UPDATE tai_khoan SET MatKhau = ?, updated_at = NOW() WHERE id = ?";
                $stmt_reset = $conn->prepare($sql_reset);
                $stmt_reset->bind_param("si", $matKhauMd5, $data['ma_tai_khoan']);

                if (!$stmt_reset->execute()) {
                    throw new Exception("Lỗi reset mật khẩu: " . $stmt_reset->error);
                }

                // Lưu mật khẩu mới để trả về
                $matKhauReset = $matKhauMoi;
            }

            $conn->commit();
            $this->db->closeConnect($conn);

            $result = ['success' => true];
            if (isset($matKhauReset)) {
                $result['mat_khau_moi'] = $matKhauReset;
            }

            return $result;
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            error_log("Lỗi sửa nhân viên: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    // LẤY THÔNG TIN TÀI KHOẢN CỦA NHÂN VIÊN (HÀM MỚI)
    public function getTaiKhoanNhanVien($maNhanVien)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT tk.id, tk.Email, tk.VaiTro, tk.TrangThai 
                FROM tai_khoan tk
                INNER JOIN NhanVien nv ON tk.id = nv.MaTaiKhoan
                WHERE nv.MaNhanVien = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maNhanVien);
        $stmt->execute();
        $result = $stmt->get_result();

        $taiKhoan = $result->fetch_assoc();
        $this->db->closeConnect($conn);
        return $taiKhoan;
    }

    // XÓA NHÂN VIÊN (CHỈ XÓA NHÂN VIÊN, KHÔNG XÓA TÀI KHOẢN)
    public function xoaNhanVien($maNhanVien)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // 1. Lấy mã tài khoản trước khi xóa (nếu cần)
            $sql_get_tk = "SELECT MaTaiKhoan FROM NhanVien WHERE MaNhanVien = ?";
            $stmt_get_tk = $conn->prepare($sql_get_tk);
            $stmt_get_tk->bind_param("s", $maNhanVien);
            $stmt_get_tk->execute();
            $result_get_tk = $stmt_get_tk->get_result();
            $row = $result_get_tk->fetch_assoc();
            $maTaiKhoan = $row['MaTaiKhoan'] ?? null;

            // 2. Xóa nhân viên
            $sql = "DELETE FROM NhanVien WHERE MaNhanVien = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $maNhanVien);

            if (!$stmt->execute()) {
                throw new Exception("Lỗi xóa nhân viên: " . $stmt->error);
            }

            // 3. CẬP NHẬT TÀI KHOẢN: Đổi vai trò thành 'khachhang' hoặc giữ nguyên
            if ($maTaiKhoan) {
                $sql_update_tk = "UPDATE tai_khoan SET VaiTro = 'khachhang' WHERE id = ?";
                $stmt_update_tk = $conn->prepare($sql_update_tk);
                $stmt_update_tk->bind_param("i", $maTaiKhoan);
                $stmt_update_tk->execute();
            }

            $conn->commit();
            $this->db->closeConnect($conn);
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            error_log("Lỗi xóa nhân viên: " . $e->getMessage());
            return false;
        }
    }

    // HÀM PHỤ TRỢ: TẠO EMAIL TỰ ĐỘNG
    private function generateEmail($hoTen)
    {
        $conn = $this->db->openConnect();

        // Tạo email từ tên
        $tenKhongDau = $this->removeAccents($hoTen);
        $parts = explode(' ', $tenKhongDau);
        $ten = strtolower(end($parts)); // Lấy tên
        $ho = strtolower($parts[0]); // Lấy họ

        $baseEmail = $ten . '.' . $ho . '@company.com';
        $email = $baseEmail;

        // Kiểm tra email đã tồn tại chưa, nếu có thì thêm số
        $counter = 1;
        while (true) {
            $sql_check = "SELECT id FROM tai_khoan WHERE Email = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows == 0) {
                break;
            }

            $email = $ten . '.' . $ho . $counter . '@company.com';
            $counter++;
        }

        $this->db->closeConnect($conn);
        return $email;
    }

    // HÀM PHỤ TRỢ: TẠO MẬT KHẨU NGẪU NHIÊN
    private function generateRandomPassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    // HÀM PHỤ TRỢ: CHUYỂN PHÒNG BAN THÀNH VAI TRÒ
    private function convertPhongBanToVaiTro($phongBan)
    {
        $mapping = [
            'Kinh Doanh' => 'kinhdoanh',
            'Lễ Tân' => 'letan',
            'Buồng Phòng' => 'buongphong',
            'Kế Toán' => 'ketoan',
            'Quản Lý' => 'quanly'
        ];

        return $mapping[$phongBan] ?? strtolower(str_replace(' ', '', $phongBan));
    }

    // HÀM PHỤ TRỢ: XÓA DẤU TIẾNG VIỆT
    private function removeAccents($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        return $str;
    }

    // LẤY DANH SÁCH PHÒNG BAN
    public function getDanhSachPhongBan()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT DISTINCT PhongBan FROM NhanVien WHERE PhongBan IS NOT NULL AND PhongBan != '' ORDER BY PhongBan";
        $result = $conn->query($sql);

        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row['PhongBan'];
            }
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // THỐNG KÊ NHÂN VIÊN
    public function thongKeNhanVien()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
                COUNT(*) as tongNhanVien,
                SUM(CASE WHEN TrangThai = 'Đang làm' THEN 1 ELSE 0 END) as dangLam,
                SUM(CASE WHEN TrangThai = 'Đã nghỉ' THEN 1 ELSE 0 END) as daNghi,
                COUNT(DISTINCT PhongBan) as soPhongBan,
                AVG(LuongCoBan) as luongTrungBinh,
                COUNT(DISTINCT MaTaiKhoan) as coTaiKhoan
                FROM NhanVien";

        $result = $conn->query($sql);
        $thongKe = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $thongKe;
    }
}
