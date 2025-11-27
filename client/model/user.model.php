<?php
// SỬA ĐƯỜNG DẪN
require_once __DIR__ . '/connectDB.php';
class UserModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Kiểm tra email đã tồn tại chưa
    public function checkEmailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM tai_khoan WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    // Kiểm tra CMND đã tồn tại chưa
    public function checkCMNDExists($cmnd) {
        $stmt = $this->conn->prepare("SELECT id FROM tai_khoan WHERE CMND = ?");
        $stmt->bind_param("s", $cmnd);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    // Kiểm tra username đã tồn tại chưa
    public function checkUsernameExists($username) {
        $stmt = $this->conn->prepare("SELECT id FROM tai_khoan WHERE TenDangNhap = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    // Tạo tài khoản mới
    public function createUser($userData) {
        $stmt = $this->conn->prepare(
            "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND) 
             VALUES (?, ?, 'khachhang', 1, ?, ?)"
        );
        
        $stmt->bind_param(
            "ssss", 
            $userData['username'],
            $userData['password'],
            $userData['email'],
            $userData['cmnd']
        );
        
        return $stmt->execute();
    }
    
    // Đăng nhập
    public function login($email, $password) {
        $hashedPassword = md5($password);
        $stmt = $this->conn->prepare(
            "SELECT id, TenDangNhap, VaiTro, Email, TrangThai 
             FROM tai_khoan 
             WHERE Email = ? AND MatKhau = ? AND TrangThai = 1"
        );
        $stmt->bind_param("ss", $email, $hashedPassword);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Lấy thông tin user bằng email
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM tai_khoan WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>