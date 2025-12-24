<?php
require_once __DIR__ . '/connectDB.php';
class UserModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Kiểm tra email đã tồn tại chưa
    public function checkEmailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT id FROM tai_khoan WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Kiểm tra CMND đã tồn tại chưa
    public function checkCMNDExists($cmnd)
    {
        $stmt = $this->conn->prepare("SELECT id FROM tai_khoan WHERE CMND = ?");
        $stmt->bind_param("s", $cmnd);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // KIỂM TRA SỐ ĐIỆN THOẠI ĐÃ TỒN TẠI CHƯA
    public function checkPhoneExists($phone)
    {
        $stmt = $this->conn->prepare("SELECT MaKH FROM KhachHang WHERE SoDienThoai = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Tạo tài khoản mới - LƯU VÀO CẢ 2 BẢNG
    public function createUser($userData)
    {
        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        $this->conn->begin_transaction();

        try {
            // 1. TẠO TÀI KHOẢN TRONG BẢNG tai_khoan
            $stmtAccount = $this->conn->prepare(
                "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND) 
                 VALUES (?, ?, 'khachhang', 1, ?, ?)"
            );

            $stmtAccount->bind_param(
                "ssss",
                $userData['username'],
                $userData['password'],
                $userData['email'],
                $userData['cmnd']
            );

            if (!$stmtAccount->execute()) {
                throw new Exception("Lỗi khi tạo tài khoản: " . $stmtAccount->error);
            }

            $accountId = $this->conn->insert_id;
            $stmtAccount->close();

            // 2. TẠO KHÁCH HÀNG TRONG BẢNG KhachHang
            $customerId = "KH" . str_pad($accountId, 4, '0', STR_PAD_LEFT);

            $stmtCustomer = $this->conn->prepare(
                "INSERT INTO KhachHang (MaKH, HoTen, SoDienThoai, DiaChi, TrangThai, MaTaiKhoan) 
                 VALUES (?, ?, ?, NULL, 'Không ở', ?)"
            );

            $stmtCustomer->bind_param(
                "sssi",
                $customerId,
                $userData['fullname'],
                $userData['phone'],
                $accountId
            );

            if (!$stmtCustomer->execute()) {
                throw new Exception("Lỗi khi tạo khách hàng: " . $stmtCustomer->error);
            }

            $stmtCustomer->close();

            // Commit transaction nếu cả 2 đều thành công
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->conn->rollback();
            error_log("Error in createUser: " . $e->getMessage());
            return false;
        }
    }

    // Đăng nhập
    public function login($email, $password)
    {
        $hashedPassword = md5($password);
        $stmt = $this->conn->prepare(
            "SELECT id, TenDangNhap, VaiTro, Email, TrangThai 
             FROM tai_khoan 
             WHERE Email = ? AND MatKhau = ? AND TrangThai = 1 AND VaiTro = 'khachhang'"
        );
        $stmt->bind_param("ss", $email, $hashedPassword);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Lấy thông tin user bằng email
    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tai_khoan WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    // Thêm phương thức changePassword
    public function changePassword($userId, $current_password, $new_password)
    {
        // Kiểm tra mật khẩu hiện tại
        $hashedCurrentPassword = md5($current_password);
        $checkStmt = $this->conn->prepare("SELECT id FROM tai_khoan WHERE id = ? AND MatKhau = ?");
        $checkStmt->bind_param("is", $userId, $hashedCurrentPassword);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            return false; // Mật khẩu hiện tại không đúng
        }

        // Cập nhật mật khẩu mới
        $hashedNewPassword = md5($new_password);
        $updateStmt = $this->conn->prepare("UPDATE tai_khoan SET MatKhau = ? WHERE id = ?");
        $updateStmt->bind_param("si", $hashedNewPassword, $userId);
        return $updateStmt->execute();
    }
}
