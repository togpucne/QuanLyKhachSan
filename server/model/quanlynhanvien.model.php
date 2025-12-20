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

        $sql = "SELECT MaNhanVien FROM nhanvien ORDER BY MaNhanVien DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_maNV = $row['MaNhanVien'];

            // Kiểm tra xem mã có phải dạng NVxxx không
            if (preg_match('/NV(\d+)/', $last_maNV, $matches)) {
                $last_number = intval($matches[1]);
                $new_number = $last_number + 1;
                $new_maNV = 'NV' . sprintf('%03d', $new_number);
            } else {
                // Nếu không phải định dạng NVxxx, tạo mới từ đầu
                $new_maNV = 'NV001';
            }
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

        $sql = "SELECT nv.*, tk.id as tai_khoan_id, tk.Email, tk.VaiTro, tk.TrangThai as TrangThaiTK
                FROM nhanvien nv 
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

    // LẤY TÀI KHOẢN CHƯA GẮN NHÂN VIÊN (Tài khoản nhân viên chưa được gán)
    public function getTaiKhoanChuaGanNhanVien()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT tk.* 
                FROM tai_khoan tk 
                WHERE tk.VaiTro != 'khachhang'
                AND tk.TrangThai = 1
                AND tk.id NOT IN (
                    SELECT MaTaiKhoan FROM nhanvien WHERE MaTaiKhoan IS NOT NULL
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
    public function getChiTietNhanVien($maNhanVien)
    {
        $conn = $this->db->openConnect();

        if (!$conn) {
            error_log("ERROR - Không thể kết nối database");
            return null;
        }

        try {
            $sql = "SELECT nv.*, 
                       tk.id as tai_khoan_id, 
                       tk.Email, 
                       tk.VaiTro, 
                       tk.TrangThai as TrangThaiTK,
                       tk.CMND
                FROM nhanvien nv 
                LEFT JOIN tai_khoan tk ON nv.MaTaiKhoan = tk.id 
                WHERE nv.MaNhanVien = ?";

            error_log("DEBUG - SQL: " . $sql);
            error_log("DEBUG - MaNhanVien: " . $maNhanVien);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("ERROR - Prepare statement failed: " . $conn->error);
                $this->db->closeConnect($conn);
                return null;
            }

            $stmt->bind_param("s", $maNhanVien);

            if (!$stmt->execute()) {
                error_log("ERROR - Execute failed: " . $stmt->error);
                $this->db->closeConnect($conn);
                return null;
            }

            $result = $stmt->get_result();
            if (!$result) {
                error_log("ERROR - Get result failed: " . $stmt->error);
                $this->db->closeConnect($conn);
                return null;
            }

            $nhanVien = $result->fetch_assoc();

            error_log("DEBUG - Kết quả: " . json_encode($nhanVien));
            error_log("DEBUG - Số hàng: " . $result->num_rows);

            $this->db->closeConnect($conn);
            return $nhanVien;
        } catch (Exception $e) {
            error_log("EXCEPTION - getChiTietNhanVien: " . $e->getMessage());
            $this->db->closeConnect($conn);
            return null;
        }
    }

    // TÌM KIẾM NHÂN VIÊN
    public function timKiemNhanVien($keyword)
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT nv.*, tk.id as tai_khoan_id, tk.Email, tk.VaiTro, tk.TrangThai as TrangThaiTK
                FROM nhanvien nv 
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

    // Sửa hàm themNhanVien trong class QuanLyNhanVienModel
    public function themNhanVien($data)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // VALIDATE DỮ LIỆU TRƯỚC KHI THÊM
            $validationErrors = $this->validateThemNhanVien($data);
            if (!empty($validationErrors)) {
                throw new Exception(implode("<br>", $validationErrors));
            }

            // 1. TẠO TÀI KHOẢN MỚI
            $matKhauMd5 = md5($data['mat_khau']);
            $tenDangNhap = $data['HoTen'];
            $emailDangNhap = $data['email'];
            $vaiTro = $this->convertPhongBanToVaiTro($data['PhongBan']);

            // QUAN TRỌNG: Mapping trạng thái nhân viên -> trạng thái tài khoản
            // 'Đang làm' -> TrangThai = '1'
            // 'Đã nghỉ' -> TrangThai = '0'
            $trangThaiTaiKhoan = ($data['TrangThai'] === 'Đang làm') ? '1' : '0';

            $sql_tk = "INSERT INTO tai_khoan 
              (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt_tk = $conn->prepare($sql_tk);
            $cmnd = $data['CMND'] ?? '';
            $stmt_tk->bind_param("ssssss", $tenDangNhap, $matKhauMd5, $vaiTro, $trangThaiTaiKhoan, $emailDangNhap, $cmnd);

            if (!$stmt_tk->execute()) {
                throw new Exception("Lỗi tạo tài khoản: " . $stmt_tk->error);
            }

            $maTaiKhoan = $stmt_tk->insert_id;

            // 2. TẠO MÃ NHÂN VIÊN
            $maNhanVien = $this->generateMaNhanVien();

            // 3. THÊM NHÂN VIÊN
            $sql = "INSERT INTO nhanvien (
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
                $data['TrangThai'], // Giữ nguyên 'Đang làm'/'Đã nghỉ'
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
                'mat_khau' => $data['mat_khau'],
                'maTaiKhoan' => $maTaiKhoan,
                'ten_dang_nhap' => $tenDangNhap,
                'trang_thai_nv' => $data['TrangThai'],
                'trang_thai_tk' => $trangThaiTaiKhoan
            ];
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            error_log("Lỗi thêm nhân viên: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    // KIỂM TRA VÀ CẬP NHẬT TỰ ĐỘNG KHI NHÂN VIÊN ĐẾN NGÀY NGHỈ
    public function kiemTraVaCapNhatTrangThai()
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            $today = date('Y-m-d');

            // 1. Tìm các nhân viên cần kiểm tra
            $sql = "SELECT nv.MaNhanVien, nv.MaTaiKhoan, nv.HoTen, nv.NgayNghiViec, nv.TrangThai
                FROM nhanvien nv
                WHERE nv.NgayNghiViec != '0000-00-00'
                AND nv.NgayNghiViec != ''
                AND nv.NgayNghiViec IS NOT NULL
                AND nv.MaTaiKhoan IS NOT NULL";

            $result = $conn->query($sql);

            $updated = [];
            $reactivated = []; // Những người được kích hoạt lại
            $deactivated = []; // Những người bị khóa

            // 2. Xử lý từng nhân viên
            while ($row = $result->fetch_assoc()) {
                $ngayNghiViec = $row['NgayNghiViec'];
                $trangThaiHienTai = $row['TrangThai'];
                $trangThaiMoi = $trangThaiHienTai;
                $action = '';

                // Logic tương tự như hàm suaNhanVien
                if (strtotime($ngayNghiViec) <= strtotime($today)) {
                    // Đã đến hoặc qua ngày nghỉ
                    if ($trangThaiHienTai === 'Đang làm') {
                        $trangThaiMoi = 'Đã nghỉ';
                        $action = 'deactivate';
                    }
                } else {
                    // Ngày nghỉ trong tương lai
                    if ($trangThaiHienTai === 'Đã nghỉ') {
                        $trangThaiMoi = 'Đang làm';
                        $action = 'reactivate';
                    }
                }

                // Nếu có thay đổi, cập nhật
                if ($trangThaiMoi !== $trangThaiHienTai) {
                    // Cập nhật trạng thái nhân viên
                    $sql_update_nv = "UPDATE nhanvien SET TrangThai = ?, updated_at = NOW() 
                                 WHERE MaNhanVien = ?";
                    $stmt_update_nv = $conn->prepare($sql_update_nv);
                    $stmt_update_nv->bind_param("ss", $trangThaiMoi, $row['MaNhanVien']);
                    $stmt_update_nv->execute();

                    // Cập nhật trạng thái tài khoản
                    $trangThaiTaiKhoan = ($trangThaiMoi === 'Đang làm') ? '1' : '0';
                    $sql_update_tk = "UPDATE tai_khoan SET TrangThai = ?, updated_at = NOW() 
                                 WHERE id = ?";
                    $stmt_update_tk = $conn->prepare($sql_update_tk);
                    $stmt_update_tk->bind_param("si", $trangThaiTaiKhoan, $row['MaTaiKhoan']);
                    $stmt_update_tk->execute();

                    $item = [
                        'ma_nhan_vien' => $row['MaNhanVien'],
                        'ho_ten' => $row['HoTen'],
                        'ngay_nghi_viec' => $ngayNghiViec,
                        'trang_thai_cu' => $trangThaiHienTai,
                        'trang_thai_moi' => $trangThaiMoi,
                        'action' => $action
                    ];

                    if ($action === 'deactivate') {
                        $deactivated[] = $item;
                    } else {
                        $reactivated[] = $item;
                    }
                }
            }

            $conn->commit();
            $this->db->closeConnect($conn);

            return [
                'success' => true,
                'deactivated_count' => count($deactivated),
                'reactivated_count' => count($reactivated),
                'deactivated' => $deactivated,
                'reactivated' => $reactivated
            ];
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            error_log("Lỗi kiểm tra trạng thái: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    // HÀM PHỤ TRỢ: KIỂM TRA NGÀY NGHỈ CÓ HỢP LỆ KHÔNG (phải sau ngày vào làm)
    public function kiemTraNgayNghiHopLe($ngayVaoLam, $ngayNghiViec)
    {
        if (empty($ngayNghiViec) || $ngayNghiViec == '0000-00-00') {
            return true; // Không có ngày nghỉ thì hợp lệ
        }

        return strtotime($ngayNghiViec) >= strtotime($ngayVaoLam);
    }
    public function suaNhanVien($maNhanVien, $data)
    {
        error_log("======================================");
        error_log("🔍 DEBUG suaNhanVien - BẮT ĐẦU");
        error_log("Mã NV: $maNhanVien");
        error_log("Trạng thái từ form: " . ($data['TrangThai'] ?? 'KHÔNG CÓ'));
        error_log("Toàn bộ data: " . json_encode($data));
        error_log("======================================");

        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // 1. Validate dữ liệu
            $validationErrors = $this->validateSuaNhanVien($maNhanVien, $data);
            if (!empty($validationErrors)) {
                throw new Exception(implode("<br>", $validationErrors));
            }

            // 2. Kiểm tra ngày nghỉ hợp lệ
            if (!$this->kiemTraNgayNghiHopLe($data['NgayVaoLam'], $data['NgayNghiViec'])) {
                throw new Exception("Ngày nghỉ việc phải sau ngày vào làm!");
            }

            // 3. Lấy mã tài khoản HIỆN TẠI từ database
            $sql_get_tk = "SELECT MaTaiKhoan FROM nhanvien WHERE MaNhanVien = ?";
            $stmt_get_tk = $conn->prepare($sql_get_tk);
            $stmt_get_tk->bind_param("s", $maNhanVien);
            $stmt_get_tk->execute();
            $result_get_tk = $stmt_get_tk->get_result();
            $currentInfo = $result_get_tk->fetch_assoc();

            $maTaiKhoan = $currentInfo['MaTaiKhoan'] ?? null;

            error_log("🔍 DEBUG - MaTaiKhoan từ database: " . ($maTaiKhoan ?? 'NULL'));
            error_log("🔍 DEBUG - Trạng thái từ form để cập nhật NV: " . $data['TrangThai']);

            // 4. Xử lý logic tự động dựa trên ngày nghỉ
            $today = date('Y-m-d');
            $ngayNghiViec = $data['NgayNghiViec'] ?? null;
            $ngayNghiViecFormatted = ($ngayNghiViec == '0000-00-00' || empty($ngayNghiViec)) ? null : $ngayNghiViec;

            $autoUpdated = false;
            $autoMessage = '';

            // Logic xử lý tự động - GIỮ NGUYÊN
            if (empty($ngayNghiViec) || $ngayNghiViec == '0000-00-00') {
                if ($data['TrangThai'] === 'Đã nghỉ') {
                    $data['TrangThai'] = 'Đang làm';
                    $autoUpdated = true;
                    $autoMessage = "Đã tự động cập nhật trạng thái thành 'Đang làm' vì ngày nghỉ đã được xóa.";
                }
            } elseif ($ngayNghiViecFormatted && strtotime($ngayNghiViecFormatted) > strtotime($today)) {
                if ($data['TrangThai'] === 'Đã nghỉ') {
                    $data['TrangThai'] = 'Đang làm';
                    $autoUpdated = true;
                    $autoMessage = "Đã tự động cập nhật trạng thái thành 'Đang làm' vì ngày nghỉ được gia hạn đến {$ngayNghiViecFormatted}.";
                }
            } elseif ($ngayNghiViecFormatted && strtotime($ngayNghiViecFormatted) <= strtotime($today)) {
                if ($data['TrangThai'] === 'Đang làm') {
                    $data['TrangThai'] = 'Đã nghỉ';
                    $autoUpdated = true;
                    $autoMessage = "Đã tự động cập nhật trạng thái thành 'Đã nghỉ' vì đã đến ngày nghỉ việc: {$ngayNghiViecFormatted}.";
                }
            }

            // 5. XỬ LÝ TÀI KHOẢN NẾU CÓ
            if ($maTaiKhoan) {
                // 5.1. Chuyển đổi trạng thái: "Đang làm" -> '1', "Đã nghỉ" -> '0'
                // SỬ DỤNG TRẠNG THÁI ĐÃ ĐƯỢC TỰ ĐỘNG CẬP NHẬT (nếu có)
                $trangThaiFinal = $data['TrangThai']; // Đã được tự động cập nhật nếu có
                $trangThaiTaiKhoan = ($trangThaiFinal === 'Đang làm') ? '1' : '0';

                error_log("🔍 DEBUG - Trạng thái cuối cùng: $trangThaiFinal");
                error_log("🔍 DEBUG - Trạng thái TK sẽ update: " . $trangThaiTaiKhoan);

                // 5.2. Cập nhật email và CMND nếu có trong $data
                if (isset($data['email']) && !empty($data['email'])) {
                    $email = $data['email'];

                    // Validate email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Email không hợp lệ!");
                    }

                    // Kiểm tra @gmail.com
                    if (!preg_match('/@gmail\.com$/', $email)) {
                        throw new Exception("Email phải có định dạng @gmail.com!");
                    }

                    // Kiểm tra email trùng (trừ chính tài khoản này)
                    $sql_check_email = "SELECT COUNT(*) as count FROM tai_khoan WHERE Email = ? AND id != ?";
                    $stmt_check_email = $conn->prepare($sql_check_email);
                    $stmt_check_email->bind_param("si", $email, $maTaiKhoan);
                    $stmt_check_email->execute();
                    $result_check_email = $stmt_check_email->get_result();
                    $row_email = $result_check_email->fetch_assoc();

                    if ($row_email['count'] > 0) {
                        throw new Exception("Email đã tồn tại trong hệ thống!");
                    }

                    $sql_update_email = "UPDATE tai_khoan SET Email = ?, updated_at = NOW() WHERE id = ?";
                    $stmt_update_email = $conn->prepare($sql_update_email);
                    $stmt_update_email->bind_param("si", $email, $maTaiKhoan);

                    if (!$stmt_update_email->execute()) {
                        throw new Exception("Lỗi cập nhật email: " . $stmt_update_email->error);
                    }

                    error_log("✅ DEBUG - Đã cập nhật email: $email");
                }

                // 5.3. Cập nhật CMND nếu có trong $data
                if (isset($data['cmnd']) && !empty($data['cmnd'])) {
                    $cmnd = $data['cmnd'];

                    // Validate CMND (9-12 số)
                    if (!preg_match('/^\d{9,12}$/', $cmnd)) {
                        throw new Exception("CMND phải có 9-12 chữ số!");
                    }

                    // Kiểm tra CMND trùng
                    $sql_check_cmnd = "SELECT COUNT(*) as count FROM tai_khoan WHERE CMND = ? AND id != ?";
                    $stmt_check_cmnd = $conn->prepare($sql_check_cmnd);
                    $stmt_check_cmnd->bind_param("si", $cmnd, $maTaiKhoan);
                    $stmt_check_cmnd->execute();
                    $result_check_cmnd = $stmt_check_cmnd->get_result();
                    $row_cmnd = $result_check_cmnd->fetch_assoc();

                    if ($row_cmnd['count'] > 0) {
                        throw new Exception("CMND đã tồn tại trong hệ thống!");
                    }

                    $sql_update_cmnd = "UPDATE tai_khoan SET CMND = ?, updated_at = NOW() WHERE id = ?";
                    $stmt_update_cmnd = $conn->prepare($sql_update_cmnd);
                    $stmt_update_cmnd->bind_param("si", $cmnd, $maTaiKhoan);

                    if (!$stmt_update_cmnd->execute()) {
                        throw new Exception("Lỗi cập nhật CMND: " . $stmt_update_cmnd->error);
                    }

                    error_log("✅ DEBUG - Đã cập nhật CMND: $cmnd");
                }

                // 5.4. QUAN TRỌNG: Cập nhật TRẠNG THÁI tài khoản
                $sql_update_tk_status = "UPDATE tai_khoan SET 
                TrangThai = ?, 
                updated_at = NOW() 
                WHERE id = ?";
                $stmt_update_tk_status = $conn->prepare($sql_update_tk_status);
                $stmt_update_tk_status->bind_param("si", $trangThaiTaiKhoan, $maTaiKhoan);

                if (!$stmt_update_tk_status->execute()) {
                    throw new Exception("Lỗi cập nhật trạng thái tài khoản: " . $stmt_update_tk_status->error);
                }

                error_log("✅ DEBUG - Đã cập nhật trạng thái tài khoản thành công!");
                error_log("   • ID tài khoản: $maTaiKhoan");
                error_log("   • Trạng thái TK mới: $trangThaiTaiKhoan");

                // 5.5. Reset mật khẩu nếu có yêu cầu
                if (isset($data['reset_mat_khau']) && $data['reset_mat_khau'] == '1') {
                    $matKhauMoi = $data['mat_khau_moi'] ?? '123456';
                    $matKhauMd5 = md5($matKhauMoi);

                    $sql_reset = "UPDATE tai_khoan SET MatKhau = ?, updated_at = NOW() WHERE id = ?";
                    $stmt_reset = $conn->prepare($sql_reset);
                    $stmt_reset->bind_param("si", $matKhauMd5, $maTaiKhoan);

                    if (!$stmt_reset->execute()) {
                        throw new Exception("Lỗi reset mật khẩu: " . $stmt_reset->error);
                    }

                    // Lưu mật khẩu mới để trả về
                    $matKhauReset = $matKhauMoi;
                    error_log("✅ DEBUG - Đã reset mật khẩu thành: $matKhauMoi");
                }
            } else {
                error_log("🔍 DEBUG - Nhân viên không có tài khoản, không cần cập nhật TK");
            }

            // 6. CẬP NHẬT BẢNG NHANVIEN - SỬA LỖI BIND_PARAM
            $sql_nv = "UPDATE nhanvien SET 
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

            error_log("🔍 DEBUG - SQL UPDATE nhanvien:");
            error_log("   HoTen: " . $data['HoTen']);
            error_log("   DiaChi: " . $data['DiaChi']);
            error_log("   SDT: " . $data['SDT']);
            error_log("   NgayVaoLam: " . $data['NgayVaoLam']);
            error_log("   NgayNghiViec: " . ($data['NgayNghiViec'] ?? 'NULL'));
            error_log("   PhongBan: " . $data['PhongBan']);
            error_log("   LuongCoBan: " . $data['LuongCoBan']);
            error_log("   TrangThai (QUAN TRỌNG): " . $trangThaiFinal); // Dùng trạng thái cuối cùng
            error_log("   MaNhanVien: $maNhanVien");

            $stmt_nv = $conn->prepare($sql_nv);
            $ngayNghiViecValue = $data['NgayNghiViec'] ?? null;

            // QUAN TRỌNG: Sửa bind_param từ "ssssssdss" thành "sssssssss"
            // vì LuongCoBan (decimal) và TrangThai (ENUM) đều bind là string
            $stmt_nv->bind_param(
                "sssssssss",  // 9 tham số string
                $data['HoTen'],
                $data['DiaChi'],
                $data['SDT'],
                $data['NgayVaoLam'],
                $ngayNghiViecValue,
                $data['PhongBan'],
                $data['LuongCoBan'],  // decimal nhưng bind là string
                $trangThaiFinal,      // ENUM nhưng bind là string - DÙNG TRẠNG THÁI CUỐI
                $maNhanVien
            );

            if (!$stmt_nv->execute()) {
                error_log("❌ DEBUG - Lỗi execute UPDATE nhanvien: " . $stmt_nv->error);
                throw new Exception("Lỗi cập nhật nhân viên: " . $stmt_nv->error);
            }

            $affectedRows = $stmt_nv->affected_rows;
            error_log("✅ DEBUG - Đã cập nhật nhân viên thành công!");
            error_log("   • Số dòng bị ảnh hưởng: $affectedRows");
            error_log("   • Trạng thái NV mới: " . $trangThaiFinal);

            // 7. COMMIT TRANSACTION
            $conn->commit();
            error_log("✅ DEBUG - Transaction đã commit thành công!");
            $this->db->closeConnect($conn);

            // 8. TRẢ VỀ KẾT QUẢ
            $result = [
                'success' => true,
                'maTaiKhoan' => $maTaiKhoan,
                'trang_thai_nv' => $trangThaiFinal,  // Trạng thái cuối cùng
            ];

            if ($maTaiKhoan) {
                $result['trang_thai_tk'] = $trangThaiTaiKhoan ?? null;  // '1'/'0'
            }

            if (isset($matKhauReset)) {
                $result['mat_khau_moi'] = $matKhauReset;
            }

            // Thêm thông báo nếu đã tự động cập nhật
            if ($autoUpdated) {
                $result['auto_updated'] = true;
                $result['message'] = $autoMessage;
                error_log("🔍 DEBUG - Đã tự động cập nhật: $autoMessage");
            }

            error_log("======================================");
            error_log("🎉 DEBUG suaNhanVien - KẾT THÚC THÀNH CÔNG");
            error_log("Kết quả: " . json_encode($result));
            error_log("======================================");

            return $result;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("❌ DEBUG - Lỗi trong suaNhanVien: " . $e->getMessage());
            $this->db->closeConnect($conn);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // XÓA NHÂN VIÊN - XÓA LUÔN TÀI KHOẢN THEO YÊU CẦU
    public function xoaNhanVien($maNhanVien)
    {
        $conn = $this->db->openConnect();
        $conn->begin_transaction();

        try {
            // 1. Lấy mã tài khoản trước khi xóa
            $sql_get_tk = "SELECT MaTaiKhoan FROM nhanvien WHERE MaNhanVien = ?";
            $stmt_get_tk = $conn->prepare($sql_get_tk);
            $stmt_get_tk->bind_param("s", $maNhanVien);
            $stmt_get_tk->execute();
            $result_get_tk = $stmt_get_tk->get_result();
            $row = $result_get_tk->fetch_assoc();
            $maTaiKhoan = $row['MaTaiKhoan'] ?? null;

            // 2. Xóa nhân viên
            $sql = "DELETE FROM nhanvien WHERE MaNhanVien = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $maNhanVien);

            if (!$stmt->execute()) {
                throw new Exception("Lỗi xóa nhân viên: " . $stmt->error);
            }

            // 3. XÓA LUÔN TÀI KHOẢN (theo yêu cầu)
            if ($maTaiKhoan) {
                $sql_delete_tk = "DELETE FROM tai_khoan WHERE id = ?";
                $stmt_delete_tk = $conn->prepare($sql_delete_tk);
                $stmt_delete_tk->bind_param("i", $maTaiKhoan);

                if (!$stmt_delete_tk->execute()) {
                    throw new Exception("Lỗi xóa tài khoản: " . $stmt_delete_tk->error);
                }
            }

            $conn->commit();
            $this->db->closeConnect($conn);
            return ['success' => true, 'maTaiKhoan' => $maTaiKhoan];
        } catch (Exception $e) {
            $conn->rollback();
            $this->db->closeConnect($conn);
            error_log("Lỗi xóa nhân viên: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
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

    // LẤY DANH SÁCH PHÒNG BAN
    public function getDanhSachPhongBan()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT DISTINCT PhongBan FROM nhanvien WHERE PhongBan IS NOT NULL AND PhongBan != '' ORDER BY PhongBan";
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
                FROM nhanvien";

        $result = $conn->query($sql);
        $thongKe = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $thongKe;
    }
    // Thêm vào class QuanLyNhanVienModel, sau phương thức getChiTietNhanVien()

    /**
     * Kiểm tra email trùng trong hệ thống (trừ chính tài khoản đó)
     */
    public function kiemTraEmailTrung($email, $taiKhoanID = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM tai_khoan WHERE Email = ?";
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

    /**
     * Cập nhật email cho tài khoản nhân viên
     */
    public function capNhatEmailNhanVien($taiKhoanID, $email)
    {
        $conn = $this->db->openConnect();

        try {
            // Validate email
            if (empty($email)) {
                return ['success' => false, 'error' => 'Email không được để trống'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'Email không hợp lệ'];
            }

            // Kiểm tra email phải kết thúc bằng @gmail.com
            if (!preg_match('/@gmail\.com$/', $email)) {
                return ['success' => false, 'error' => 'Email phải có định dạng @gmail.com'];
            }

            // Kiểm tra email trùng
            if ($this->kiemTraEmailTrung($email, $taiKhoanID)) {
                return ['success' => false, 'error' => 'Email đã tồn tại trong hệ thống'];
            }

            // Cập nhật email
            $sql = "UPDATE tai_khoan SET Email = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $email, $taiKhoanID);

            $result = $stmt->execute();

            if ($result) {
                return ['success' => true, 'message' => 'Cập nhật email thành công'];
            } else {
                return ['success' => false, 'error' => 'Lỗi cập nhật email: ' . $stmt->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()];
        } finally {
            $this->db->closeConnect($conn);
        }
    }

    /**
     * Cập nhật thông tin tài khoản (email và CMND)
     */
    public function capNhatThongTinTaiKhoan($taiKhoanID, $data)
    {
        $conn = $this->db->openConnect();

        try {
            // Validate email nếu có
            if (!empty($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email không hợp lệ");
                }

                // Kiểm tra email phải @gmail.com
                if (!preg_match('/@gmail\.com$/', $data['email'])) {
                    throw new Exception("Email phải có định dạng @gmail.com");
                }

                // Kiểm tra email trùng
                if ($this->kiemTraEmailTrung($data['email'], $taiKhoanID)) {
                    throw new Exception("Email đã tồn tại trong hệ thống");
                }
            }

            // Xây dựng câu lệnh SQL động
            $sql = "UPDATE tai_khoan SET updated_at = NOW()";
            $params = [];
            $types = "";

            if (!empty($data['email'])) {
                $sql .= ", Email = ?";
                $params[] = $data['email'];
                $types .= "s";
            }

            if (!empty($data['cmnd'])) {
                $sql .= ", CMND = ?";
                $params[] = $data['cmnd'];
                $types .= "s";
            }

            $sql .= " WHERE id = ?";
            $params[] = $taiKhoanID;
            $types .= "i";

            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new Exception("Lỗi cập nhật thông tin tài khoản: " . $stmt->error);
            }

            return ['success' => true, 'message' => 'Cập nhật thành công'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        } finally {
            $this->db->closeConnect($conn);
        }
    }
    // Thêm vào class QuanLyNhanVienModel (sau phương thức getChiTietNhanVien)


    public function kiemTraSDTTrung($sdt, $maNhanVien = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM nhanvien WHERE SDT = ?";
        if (!empty($maNhanVien)) {
            $sql .= " AND MaNhanVien != ?";
        }

        $stmt = $conn->prepare($sql);
        if (!empty($maNhanVien)) {
            $stmt->bind_param("si", $sdt, $maNhanVien); // "si": string, integer
        } else {
            $stmt->bind_param("s", $sdt);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->db->closeConnect($conn);
        return $row['count'] > 0;
    }
    /**
     * Kiểm tra CMND trùng
     */
    public function kiemTraCMNDTrung($cmnd, $taiKhoanID = '')
    {
        if (empty($cmnd)) return false;

        $conn = $this->db->openConnect();

        $sql = "SELECT COUNT(*) as count FROM tai_khoan WHERE CMND = ?";
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

    /**
     * Kiểm tra dữ liệu trước khi thêm nhân viên
     */
    public function validateThemNhanVien($data)
    {
        $errors = [];

        // 1. Kiểm tra email
        if (empty($data['email'])) {
            $errors[] = "Email không được để trống";
        } else {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email không hợp lệ";
            }

            if (!preg_match('/@gmail\.com$/', $data['email'])) {
                $errors[] = "Email phải có định dạng @gmail.com";
            }

            if ($this->kiemTraEmailTrung($data['email'])) {
                $errors[] = "Email đã tồn tại trong hệ thống";
            }
        }

        // 2. Kiểm tra SDT
        if (empty($data['SDT'])) {
            $errors[] = "Số điện thoại không được để trống";
        } else {
            if (!preg_match('/^[0-9]{10,11}$/', $data['SDT'])) {
                $errors[] = "Số điện thoại phải có 10-11 chữ số";
            }

            if ($this->kiemTraSDTTrung($data['SDT'])) {
                $errors[] = "Số điện thoại đã tồn tại";
            }
        }

        // 3. Kiểm tra CMND
        if (!empty($data['CMND'])) {
            if (!preg_match('/^\d{9,12}$/', $data['CMND'])) {
                $errors[] = "CMND phải có 9-12 chữ số";
            }

            if ($this->kiemTraCMNDTrung($data['CMND'])) {
                $errors[] = "CMND đã tồn tại trong hệ thống";
            }
        }

        // 4. Kiểm tra lương
        if (empty($data['LuongCoBan']) || $data['LuongCoBan'] <= 0) {
            $errors[] = "Lương cơ bản phải lớn hơn 0";
        }

        // 5. Kiểm tra mật khẩu
        if (strlen($data['mat_khau']) < 6) {
            $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
        }

        return $errors;
    }

    /**
     * Kiểm tra dữ liệu trước khi sửa nhân viên
     */
    /**
     * Kiểm tra dữ liệu trước khi sửa nhân viên
     */
    public function validateSuaNhanVien($maNhanVien, $data)
    {
        $errors = [];

        // 1. Lấy thông tin hiện tại
        $nhanVienHienTai = $this->getChiTietNhanVien($maNhanVien);
        $maTaiKhoanHienTai = $nhanVienHienTai['tai_khoan_id'] ?? null;
        $sdtHienTai = $nhanVienHienTai['SDT'] ?? '';

        // 2. Kiểm tra email (nếu có cập nhật)
        if (isset($data['email']) && !empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email không hợp lệ";
            } elseif (!preg_match('/@gmail\.com$/', $data['email'])) {
                $errors[] = "Email phải có định dạng @gmail.com";
            } elseif ($this->kiemTraEmailTrung($data['email'], $maTaiKhoanHienTai)) {
                $errors[] = "Email đã tồn tại trong hệ thống";
            }
        }

        // 3. Kiểm tra SDT (nếu có cập nhật)
        if (isset($data['SDT'])) {
            if (empty($data['SDT'])) {
                $errors[] = "Số điện thoại không được để trống";
            } elseif (!preg_match('/^[0-9]{10,11}$/', $data['SDT'])) {
                $errors[] = "Số điện thoại phải có 10-11 chữ số";
            } elseif ($data['SDT'] !== $sdtHienTai) {
                // CHỈ KIỂM TRA TRÙNG KHI SDT THAY ĐỔI
                if ($this->kiemTraSDTTrung($data['SDT'], $maNhanVien)) {
                    $errors[] = "Số điện thoại đã tồn tại trong hệ thống";
                }
            }
        }

        // 4. Kiểm tra CMND (nếu có cập nhật)
        if (isset($data['cmnd']) && !empty($data['cmnd'])) {
            if (!preg_match('/^\d{9,12}$/', $data['cmnd'])) {
                $errors[] = "CMND phải có 9-12 chữ số";
            } elseif ($this->kiemTraCMNDTrung($data['cmnd'], $maTaiKhoanHienTai)) {
                $errors[] = "CMND đã tồn tại trong hệ thống";
            }
        }

        // 5. Kiểm tra lương
        if (isset($data['LuongCoBan'])) {
            if (empty($data['LuongCoBan']) || $data['LuongCoBan'] <= 0) {
                $errors[] = "Lương cơ bản phải lớn hơn 0";
            }
        }

        return $errors;
    }
}
