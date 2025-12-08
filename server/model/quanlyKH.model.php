<?php
include_once 'connectDB.php';

class QuanLyKHModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Connect();
    }

    // ========== KHÁCH HÀNG ==========

    // Tạo mã KH tự động
    public function generateMaKH()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT MaKH FROM KhachHang WHERE MaKH LIKE 'KH%' ORDER BY LENGTH(MaKH), MaKH DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_maKH = $row['MaKH'];
            $last_number = intval(substr($last_maKH, 2));
            $new_number = $last_number + 1;
            $new_maKH = 'KH' . $new_number;
        } else {
            $new_maKH = 'KH1';
        }

        $this->db->closeConnect($conn);
        return $new_maKH;
    }

    // Lấy danh sách KH với thông tin tài khoản
    public function getDanhSachKH($keyword = '', $trangThai = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT kh.*, tk.Email, tk.CMND 
                FROM KhachHang kh 
                LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($keyword)) {
            $sql .= " AND (kh.MaKH LIKE ? OR kh.HoTen LIKE ? OR kh.SoDienThoai LIKE ? OR kh.DiaChi LIKE ? OR tk.Email LIKE ? OR tk.CMND LIKE ?)";
            $searchTerm = "%$keyword%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= "ssssss";
        }

        if (!empty($trangThai)) {
            $sql .= " AND kh.TrangThai = ?";
            $params[] = $trangThai;
            $types .= "s";
        }

        $sql .= " ORDER BY kh.created_at DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // Lấy chi tiết KH với thông tin tài khoản
    public function getChiTietKH($maKH)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT kh.*, tk.id as TaiKhoanID, tk.TenDangNhap, tk.Email, tk.CMND, tk.TrangThai as TKTrangThai
                FROM KhachHang kh 
                LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id 
                WHERE kh.MaKH = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maKH);
        $stmt->execute();
        $khachHang = $stmt->get_result()->fetch_assoc();

        $this->db->closeConnect($conn);
        return $khachHang;
    }

    // Thêm KH và tạo tài khoản - Phiên bản đơn giản
    public function themKH($data)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            $maKHAuto = $this->generateMaKH();
            $taiKhoanID = null;

            // 1. Tạo tài khoản nếu có
            if (!empty($data['TenDangNhap']) && !empty($data['MatKhau'])) {
                // Kiểm tra trùng
                if ($this->kiemTraTenDangNhap($data['TenDangNhap'])) {
                    throw new Exception("Tên đăng nhập đã tồn tại!");
                }

                if (!empty($data['Email']) && $this->kiemTraEmail($data['Email'])) {
                    throw new Exception("Email đã tồn tại!");
                }

                if (!empty($data['CMND']) && $this->kiemTraCMND($data['CMND'])) {
                    throw new Exception("CMND đã tồn tại!");
                }

                // Chuẩn bị giá trị
                $tenDangNhap = $data['TenDangNhap'];
                $matKhauHash = md5($data['MatKhau']);
                $email = $data['Email'] ?? '';
                $cmnd = $data['CMND'] ?? '';

                $sql_tk = "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND) 
                       VALUES (?, ?, 'khachhang', 1, ?, ?)";

                $stmt_tk = $conn->prepare($sql_tk);
                $stmt_tk->bind_param("ssss", $tenDangNhap, $matKhauHash, $email, $cmnd);

                if (!$stmt_tk->execute()) {
                    throw new Exception("Lỗi tạo tài khoản!");
                }

                $taiKhoanID = $conn->insert_id;
            }

            // 2. Thêm khách hàng
            $sql_kh = "INSERT INTO KhachHang (MaKH, HoTen, SoDienThoai, DiaChi, TrangThai, MaTaiKhoan) 
                   VALUES (?, ?, ?, ?, ?, ?)";

            $stmt_kh = $conn->prepare($sql_kh);

            // Chuẩn bị giá trị
            $maKH = $maKHAuto;
            $hoTen = $data['HoTen'];
            $soDienThoai = $data['SoDienThoai'];
            $diaChi = $data['DiaChi'] ?? '';
            $trangThai = $data['TrangThai'];

            // Xử lý MaTaiKhoan (có thể là NULL)
            if ($taiKhoanID === null) {
                // Nếu NULL, dùng bind_param với chuỗi format 'ssssss'
                $stmt_kh->bind_param("ssssss", $maKH, $hoTen, $soDienThoai, $diaChi, $trangThai, $taiKhoanID);
            } else {
                // Nếu có giá trị, dùng 'sssssi' (i cho integer)
                if ($taiKhoanID === null) {
                    // Truyền NULL trực tiếp
                    $stmt_kh->bind_param("ssssss", $maKH, $hoTen, $soDienThoai, $diaChi, $trangThai, $taiKhoanID);
                } else {
                    $stmt_kh->bind_param("sssssi", $maKH, $hoTen, $soDienThoai, $diaChi, $trangThai, $taiKhoanID);
                }
            }

            if (!$stmt_kh->execute()) {
                throw new Exception("Lỗi thêm khách hàng!");
            }

            $conn->commit();

            return [
                'success' => true,
                'maKH' => $maKHAuto,
                'taiKhoanID' => $taiKhoanID
            ];
        } catch (Exception $e) {
            $conn->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } finally {
            $this->db->closeConnect($conn);
        }
    }

    // Sửa KH - Cập nhật toàn bộ hàm
    public function suaKH($maKH, $data)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // 1. Lấy thông tin hiện tại của KH
            $sql_get = "SELECT MaTaiKhoan FROM KhachHang WHERE MaKH = ?";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->bind_param("s", $maKH);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            $current = $result->fetch_assoc();
            $currentTaiKhoanID = $current['MaTaiKhoan'] ?? null;

            // 2. Xử lý tài khoản
            $hasTaiKhoanUpdate = false;

            // Nếu có thông tin tài khoản để cập nhật
            if (!empty($data['Email']) || !empty($data['CMND']) || !empty($data['TenDangNhap'])) {

                if ($currentTaiKhoanID) {
                    // CẬP NHẬT tài khoản hiện có
                    $sql_update_tk = "UPDATE tai_khoan SET ";
                    $params = [];
                    $types = "";

                    if (!empty($data['Email'])) {
                        // Kiểm tra email trùng (trừ chính nó)
                        if ($this->kiemTraEmail($data['Email'], $currentTaiKhoanID)) {
                            throw new Exception("Email đã tồn tại trong hệ thống!");
                        }
                        $sql_update_tk .= "Email = ?, ";
                        $params[] = $data['Email'];
                        $types .= "s";
                        $hasTaiKhoanUpdate = true;
                    }

                    if (!empty($data['CMND'])) {
                        // Kiểm tra CMND trùng (trừ chính nó)
                        if ($this->kiemTraCMND($data['CMND'], $currentTaiKhoanID)) {
                            throw new Exception("CMND đã tồn tại trong hệ thống!");
                        }
                        $sql_update_tk .= "CMND = ?, ";
                        $params[] = $data['CMND'];
                        $types .= "s";
                        $hasTaiKhoanUpdate = true;
                    }

                    if (!empty($data['MatKhau'])) {
                        $sql_update_tk .= "MatKhau = ?, ";
                        $params[] = md5($data['MatKhau']); // QUAN TRỌNG: MÃ HÓA MD5
                        $types .= "s";
                        $hasTaiKhoanUpdate = true;
                    }

                    if (!empty($data['TenDangNhap'])) {
                        // Kiểm tra tên đăng nhập trùng (trừ chính nó)
                        if ($this->kiemTraTenDangNhap($data['TenDangNhap'], $currentTaiKhoanID)) {
                            throw new Exception("Tên đăng nhập đã tồn tại!");
                        }
                        $sql_update_tk .= "TenDangNhap = ?, ";
                        $params[] = $data['TenDangNhap'];
                        $types .= "s";
                        $hasTaiKhoanUpdate = true;
                    }

                    // Nếu có thông tin cần cập nhật
                    if ($hasTaiKhoanUpdate) {
                        $sql_update_tk = rtrim($sql_update_tk, ", ");
                        $sql_update_tk .= " WHERE id = ?";
                        $params[] = $currentTaiKhoanID;
                        $types .= "i";

                        $stmt_update_tk = $conn->prepare($sql_update_tk);
                        $stmt_update_tk->bind_param($types, ...$params);

                        if (!$stmt_update_tk->execute()) {
                            throw new Exception("Lỗi cập nhật tài khoản!");
                        }
                    }
                } else if (!empty($data['TenDangNhap']) && !empty($data['MatKhau'])) {
                    // TẠO MỚI tài khoản nếu chưa có

                    // Kiểm tra tên đăng nhập
                    if ($this->kiemTraTenDangNhap($data['TenDangNhap'])) {
                        throw new Exception("Tên đăng nhập đã tồn tại!");
                    }

                    // Kiểm tra email nếu có
                    if (!empty($data['Email']) && $this->kiemTraEmail($data['Email'])) {
                        throw new Exception("Email đã tồn tại!");
                    }

                    // Kiểm tra CMND nếu có
                    if (!empty($data['CMND']) && $this->kiemTraCMND($data['CMND'])) {
                        throw new Exception("CMND đã tồn tại!");
                    }

                    $sql_tk = "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND) 
                           VALUES (?, ?, 'khachhang', 1, ?, ?)";

                    $stmt_tk = $conn->prepare($sql_tk);

                    $tenDangNhap = $data['TenDangNhap'];
                    $matKhauHash = md5($data['MatKhau']); // QUAN TRỌNG: MÃ HÓA MD5
                    $email = $data['Email'] ?? '';
                    $cmnd = $data['CMND'] ?? '';

                    $stmt_tk->bind_param("ssss", $tenDangNhap, $matKhauHash, $email, $cmnd);

                    if (!$stmt_tk->execute()) {
                        throw new Exception("Lỗi tạo tài khoản mới!");
                    }

                    $currentTaiKhoanID = $conn->insert_id;
                    $hasTaiKhoanUpdate = true;
                }
            }

            // 3. Cập nhật khách hàng
            // Kiểm tra SĐT trùng (trừ chính nó)
            if ($this->kiemTraSDT($data['SoDienThoai'], $maKH)) {
                throw new Exception("Số điện thoại đã tồn tại trong hệ thống!");
            }

            $sql_kh = "UPDATE KhachHang SET 
                  HoTen = ?, SoDienThoai = ?, DiaChi = ?, TrangThai = ?";

            // Thêm MaTaiKhoan nếu có cập nhật
            if ($hasTaiKhoanUpdate && $currentTaiKhoanID !== null) {
                $sql_kh .= ", MaTaiKhoan = ?";
            }

            $sql_kh .= " WHERE MaKH = ?";

            $stmt_kh = $conn->prepare($sql_kh);

            // Chuẩn bị giá trị
            $hoTen = $data['HoTen'];
            $soDienThoai = $data['SoDienThoai'];
            $diaChi = $data['DiaChi'] ?? '';
            $trangThai = $data['TrangThai'];

            if ($hasTaiKhoanUpdate && $currentTaiKhoanID !== null) {
                $stmt_kh->bind_param("ssssis", $hoTen, $soDienThoai, $diaChi, $trangThai, $currentTaiKhoanID, $maKH);
            } else {
                $stmt_kh->bind_param("sssss", $hoTen, $soDienThoai, $diaChi, $trangThai, $maKH);
            }

            if (!$stmt_kh->execute()) {
                throw new Exception("Lỗi cập nhật khách hàng!");
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception($e->getMessage()); // Truyền lỗi lên trên
        } finally {
            $this->db->closeConnect($conn);
        }
    }
    // Xóa KH (và tài khoản nếu không có bản ghi nào khác tham chiếu)
    public function xoaKH($maKH)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // 1. Lấy MaTaiKhoan
            $sql_get = "SELECT MaTaiKhoan FROM KhachHang WHERE MaKH = ?";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->bind_param("s", $maKH);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            $row = $result->fetch_assoc();
            $taiKhoanID = $row['MaTaiKhoan'] ?? null;

            // 2. Kiểm tra KH có thuộc đoàn không
            $sql_check_doan = "SELECT COUNT(*) as count FROM doan_khachhang WHERE MaKH = ?";
            $stmt_check_doan = $conn->prepare($sql_check_doan);
            $stmt_check_doan->bind_param("s", $maKH);
            $stmt_check_doan->execute();
            $result_check = $stmt_check_doan->get_result();
            $row_check = $result_check->fetch_assoc();

            if ($row_check['count'] > 0) {
                throw new Exception("Không thể xóa! Khách hàng đang thuộc đoàn.");
            }

            // 3. Xóa khách hàng
            $sql_delete_kh = "DELETE FROM KhachHang WHERE MaKH = ?";
            $stmt_delete_kh = $conn->prepare($sql_delete_kh);
            $stmt_delete_kh->bind_param("s", $maKH);

            if (!$stmt_delete_kh->execute()) {
                throw new Exception("Lỗi xóa khách hàng!");
            }

            // 4. Xóa tài khoản nếu có và không còn bản ghi nào tham chiếu
            if ($taiKhoanID) {
                // Kiểm tra xem tài khoản có được sử dụng ở bảng khác không
                $sql_check_tk = "SELECT COUNT(*) as count FROM KhachHang WHERE MaTaiKhoan = ?";
                $stmt_check_tk = $conn->prepare($sql_check_tk);
                $stmt_check_tk->bind_param("i", $taiKhoanID);
                $stmt_check_tk->execute();
                $result_tk = $stmt_check_tk->get_result();
                $row_tk = $result_tk->fetch_assoc();

                if ($row_tk['count'] == 0) {
                    $sql_delete_tk = "DELETE FROM tai_khoan WHERE id = ?";
                    $stmt_delete_tk = $conn->prepare($sql_delete_tk);
                    $stmt_delete_tk->bind_param("i", $taiKhoanID);
                    $stmt_delete_tk->execute();
                }
            }

            $conn->commit();
            $this->db->closeConnect($conn);
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            throw $e; // Truyền lỗi lên trên
        }
    }

    // Thống kê
    public function thongKeKH()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT 
                COUNT(*) as tongKH,
                SUM(CASE WHEN TrangThai = 'Không ở' THEN 1 ELSE 0 END) as tongKhongO,
                SUM(CASE WHEN TrangThai = 'Đang ở' THEN 1 ELSE 0 END) as tongDangO,
                SUM(CASE WHEN DiaChi IS NOT NULL THEN 1 ELSE 0 END) as tongCoDiaChi,
                SUM(CASE WHEN MaTaiKhoan IS NOT NULL THEN 1 ELSE 0 END) as tongCoTaiKhoan
                FROM KhachHang";

        $result = $conn->query($sql);
        if ($result) {
            $thongKe = $result->fetch_assoc();
        } else {
            $thongKe = [
                'tongKH' => 0,
                'tongKhongO' => 0,
                'tongDangO' => 0,
                'tongCoDiaChi' => 0,
                'tongCoTaiKhoan' => 0
            ];
        }

        $this->db->closeConnect($conn);
        return $thongKe;
    }

    // Kiểm tra SĐT tồn tại
    public function kiemTraSDT($sdt, $maKH = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM KhachHang WHERE SoDienThoai = ?";
        if (!empty($maKH)) {
            $sql .= " AND MaKH != ?";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($maKH)) {
            $stmt->bind_param("ss", $sdt, $maKH);
        } else {
            $stmt->bind_param("s", $sdt);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }

    // Kiểm tra Email tồn tại
    public function kiemTraEmail($email, $taiKhoanID = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM tai_khoan WHERE Email = ? AND VaiTro = 'khachhang'";
        if (!empty($taiKhoanID)) {
            $sql .= " AND id != ?";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($taiKhoanID)) {
            $stmt->bind_param("si", $email, $taiKhoanID);
        } else {
            $stmt->bind_param("s", $email);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }

    // Kiểm tra CMND tồn tại
    public function kiemTraCMND($cmnd, $taiKhoanID = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM tai_khoan WHERE CMND = ? AND VaiTro = 'khachhang'";
        if (!empty($taiKhoanID)) {
            $sql .= " AND id != ?";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($taiKhoanID)) {
            $stmt->bind_param("si", $cmnd, $taiKhoanID);
        } else {
            $stmt->bind_param("s", $cmnd);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }

    // Kiểm tra tên đăng nhập tồn tại
    public function kiemTraTenDangNhap($tenDangNhap, $taiKhoanID = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM tai_khoan WHERE TenDangNhap = ?";
        if (!empty($taiKhoanID)) {
            $sql .= " AND id != ?";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($taiKhoanID)) {
            $stmt->bind_param("si", $tenDangNhap, $taiKhoanID);
        } else {
            $stmt->bind_param("s", $tenDangNhap);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }

    // ========== TÀI KHOẢN ==========

    // Lấy danh sách tài khoản khách hàng
    public function getDanhSachTaiKhoanKH()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT tk.*, kh.MaKH, kh.HoTen, kh.SoDienThoai 
                FROM tai_khoan tk 
                LEFT JOIN KhachHang kh ON tk.id = kh.MaTaiKhoan 
                WHERE tk.VaiTro = 'khachhang' 
                ORDER BY tk.created_at DESC";

        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // Tạo tài khoản mới cho KH
    public function taoTaiKhoanKH($data)
    {
        $conn = $this->db->openConnect();

        // Kiểm tra tên đăng nhập
        if ($this->kiemTraTenDangNhap($data['TenDangNhap'])) {
            return ['success' => false, 'error' => 'Tên đăng nhập đã tồn tại'];
        }

        // Kiểm tra email
        if ($this->kiemTraEmail($data['Email'])) {
            return ['success' => false, 'error' => 'Email đã tồn tại'];
        }

        // Kiểm tra CMND
        if ($this->kiemTraCMND($data['CMND'])) {
            return ['success' => false, 'error' => 'CMND đã tồn tại'];
        }

        $sql = "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND) 
                VALUES (?, ?, 'khachhang', 1, ?, ?)";

        $stmt = $conn->prepare($sql);
        $matKhauHash = md5($data['MatKhau']);
        $stmt->bind_param(
            "ssss",
            $data['TenDangNhap'],
            $matKhauHash,
            $data['Email'],
            $data['CMND']
        );

        $result = $stmt->execute();
        $taiKhoanID = $conn->insert_id;

        $this->db->closeConnect($conn);

        return [
            'success' => $result,
            'taiKhoanID' => $taiKhoanID
        ];
    }

    // Cập nhật tài khoản KH
    public function capNhatTaiKhoanKH($taiKhoanID, $data)
    {
        $conn = $this->db->openConnect();

        // Kiểm tra email (trừ chính nó)
        if ($this->kiemTraEmail($data['Email'], $taiKhoanID)) {
            return ['success' => false, 'error' => 'Email đã tồn tại'];
        }

        // Kiểm tra CMND (trừ chính nó)
        if ($this->kiemTraCMND($data['CMND'], $taiKhoanID)) {
            return ['success' => false, 'error' => 'CMND đã tồn tại'];
        }

        $sql = "UPDATE tai_khoan SET 
                Email = ?, CMND = ?, TrangThai = ?
                WHERE id = ? AND VaiTro = 'khachhang'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssii",
            $data['Email'],
            $data['CMND'],
            $data['TrangThai'] ?? 1,
            $taiKhoanID
        );

        $result = $stmt->execute();

        $this->db->closeConnect($conn);
        return ['success' => $result];
    }

    // Reset mật khẩu
    public function resetMatKhau($taiKhoanID, $matKhauMoi)
    {
        $conn = $this->db->openConnect();

        $sql = "UPDATE tai_khoan SET MatKhau = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $matKhauHash = md5($matKhauMoi);
        $stmt->bind_param("si", $matKhauHash, $taiKhoanID);

        $result = $stmt->execute();

        $this->db->closeConnect($conn);
        return $result;
    }
}
