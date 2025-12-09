<?php
include_once 'connectDB.php';

class LoginModel {
    
    // Đăng nhập bằng EMAIL và MD5, kiểm tra cả tài khoản và nhân viên
    public function logInByEmail($email, $password) {
        $p = new Connect();
        $conn = $p->openConnect();
        
        // 1. Kiểm tra tài khoản bằng email - ĐÃ SỬA TÊN BẢNG
        $sql = "SELECT 
                    tk.id, 
                    tk.TenDangNhap, 
                    tk.VaiTro, 
                    tk.TrangThai as tai_khoan_trang_thai,
                    tk.Email, 
                    tk.CMND,
                    nv.MaNhanVien,
                    nv.HoTen,
                    nv.TrangThai as nhan_vien_trang_thai,
                    nv.PhongBan
                FROM tai_khoan tk
                LEFT JOIN nhanvien nv ON nv.MaTaiKhoan = tk.id
                WHERE tk.Email = '$email' 
                AND tk.MatKhau = MD5('$password')";
                
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $p->closeConnect($conn);
            
            // Kiểm tra các điều kiện
            $error = $this->validateLoginConditions($user);
            
            if ($error !== null) {
                return ['success' => false, 'error' => $error];
            }
            
            return ['success' => true, 'user' => $user];
        }
        
        $p->closeConnect($conn);
        return ['success' => false, 'error' => 'Email hoặc mật khẩu không đúng'];
    }

    // Kiểm tra điều kiện đăng nhập
    private function validateLoginConditions($user) {
        // 1. Kiểm tra tài khoản có tồn tại không
        if (!$user || !isset($user['id'])) {
            return 'Tài khoản không tồn tại';
        }
        
        // 2. Kiểm tra trạng thái tài khoản (1 = hoạt động)
        if ($user['tai_khoan_trang_thai'] != 1) {
            return 'Tài khoản đã bị khóa';
        }
        
        // 3. Kiểm tra vai trò (chỉ cho phép nhân viên, không cho khách hàng)
        if ($user['VaiTro'] == 'khachhang') {
            return 'Tài khoản không có quyền truy cập';
        }
        
        // 4. Kiểm tra nếu có thông tin nhân viên
        if (!$user['MaNhanVien']) {
            return 'Không tìm thấy thông tin nhân viên';
        }
        
        // 5. Kiểm tra trạng thái nhân viên
        if ($user['nhan_vien_trang_thai'] !== 'Đang làm') {
            return 'Nhân viên đã nghỉ việc';
        }
        
        return null; // Không có lỗi
    }

    // Kiểm tra email đã tồn tại chưa
    public function checkEmail($email) {
        if(!empty($email)) {
            $p = new Connect();
            $conn = $p->openConnect();
            
            $sql = "SELECT id FROM tai_khoan WHERE Email = '$email'";    
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
            
            $sql = "SELECT 
                        tk.id, 
                        tk.TenDangNhap, 
                        tk.VaiTro, 
                        tk.TrangThai, 
                        tk.Email, 
                        tk.CMND,
                        nv.MaNhanVien,
                        nv.HoTen,
                        nv.PhongBan
                    FROM tai_khoan tk
                    LEFT JOIN nhanvien nv ON nv.MaTaiKhoan = tk.id
                    WHERE tk.id = '$id' AND tk.TrangThai = 1";
                    
            $result = $conn->query($sql);
            $user = $result->fetch_assoc();
            
            $p->closeConnect($conn);
            return $user;
        }
        return null;
    }

    // Lấy thông tin user bằng email
    public function getUserByEmail($email) {
        if(!empty($email)) {
            $p = new Connect();
            $conn = $p->openConnect();
            
            $sql = "SELECT 
                        tk.id, 
                        tk.TenDangNhap, 
                        tk.VaiTro, 
                        tk.TrangThai, 
                        tk.Email, 
                        tk.CMND,
                        nv.MaNhanVien,
                        nv.HoTen,
                        nv.PhongBan,
                        nv.TrangThai as nhan_vien_trang_thai
                    FROM tai_khoan tk
                    LEFT JOIN nhanvien nv ON nv.MaTaiKhoan = tk.id
                    WHERE tk.Email = '$email'";
                    
            $result = $conn->query($sql);
            $user = $result->fetch_assoc();
            
            $p->closeConnect($conn);
            return $user;
        }
        return null;
    }
}
?>