<?php
include_once 'connectDB.php';

class LoginModel {
    
    // Đăng nhập với MD5
    public function logIn($name, $password) {
        $p = new Connect();
        $conn = $p->openConnect();
        
        $sql = "SELECT id, TenDangNhap, VaiTro, TrangThai, Email, CMND 
                FROM tai_khoan 
                WHERE TenDangNhap = '$name' 
                AND MatKhau = MD5('$password') 
                AND TrangThai = 1";
                
        $result = $conn->query($sql);
        $p->closeConnect($conn);
        return $result;
    }

    // Kiểm tra username đã tồn tại chưa
    public function checkUsername($name) {
        if(!empty($name)) {
            $p = new Connect();
            $conn = $p->openConnect();
            
            $sql = "SELECT id FROM tai_khoan WHERE TenDangNhap = '$name'";    
            $result = $conn->query($sql);
            $exists = $result->num_rows > 0;
            
            $p->closeConnect($conn);
            return $exists;
        }
        return false;
    }

    // Lấy thông tin user bằng ID
    public function getUserById($id) {
        if(!empty($id)) {
            $p = new Connect();
            $conn = $p->openConnect();
            
            $sql = "SELECT id, TenDangNhap, VaiTro, TrangThai, Email, CMND 
                    FROM tai_khoan 
                    WHERE id = '$id' AND TrangThai = 1";
                    
            $result = $conn->query($sql);
            $user = $result->fetch_assoc();
            
            $p->closeConnect($conn);
            return $user;
        }
        return null;
    }
}
?>