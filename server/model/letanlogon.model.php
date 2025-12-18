<?php
require_once 'connectDB.php';

class LetanLogonModel {
    private $conn;
    
    public function __construct() {
        $connect = new Connect();
        $this->conn = $connect->openConnect();
    }
    
    // Lấy tất cả khách hàng
    public function getAllKhachHang() {
        $query = "SELECT 
                    kh.MaKH,
                    kh.HoTen,
                    kh.SoDienThoai,
                    kh.DiaChi,
                    kh.TrangThai,
                    kh.created_at,
                    kh.updated_at,
                    kh.MaTaiKhoan,
                    tk.Email,
                    tk.CMND
                  FROM khachhang kh
                  LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id
                  ORDER BY kh.created_at DESC";
        
        $result = mysqli_query($this->conn, $query);
        $khachhang = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $khachhang[] = $row;
            }
        }
        
        return $khachhang;
    }
    
    // Lấy 1 khách hàng theo mã
    public function getKhachHangByMaKH($maKH) {
        $query = "SELECT 
                    kh.MaKH,
                    kh.HoTen,
                    kh.SoDienThoai,
                    kh.DiaChi,
                    kh.TrangThai,
                    kh.created_at,
                    kh.updated_at,
                    kh.MaTaiKhoan,
                    tk.Email,
                    tk.CMND,
                    tk.TenDangNhap
                  FROM khachhang kh
                  LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id
                  WHERE kh.MaKH = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $maKH);
        $stmt->execute();
        $result = $stmt->get_result();
        $khachhang = $result->fetch_assoc();
        $stmt->close();
        
        return $khachhang;
    }
    
    // Thêm khách hàng mới - SỬA LẠI PHẦN TẠO MÃ KH
    public function addKhachHang($data) {
        mysqli_begin_transaction($this->conn);
        
        try {
            $maTaiKhoan = 0;
            
            // Nếu có thông tin tài khoản
            if (!empty($data['tendangnhap']) && !empty($data['matkhau'])) {
                // Kiểm tra trùng username
                if ($this->checkDuplicateUsername($data['tendangnhap'])) {
                    throw new Exception('Tên đăng nhập đã tồn tại');
                }
                
                // Mã hóa mật khẩu MD5
                $hashedPassword = md5($data['matkhau']);
                
                // Tạo tài khoản mới
                $queryTK = "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND, created_at) 
                            VALUES (?, ?, 'khachhang', 1, ?, ?, NOW())";
                
                $stmt = $this->conn->prepare($queryTK);
                $stmt->bind_param("ssss", 
                    $data['tendangnhap'],
                    $hashedPassword,
                    $data['email'],
                    $data['cmnd']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Lỗi khi tạo tài khoản: ' . $stmt->error);
                }
                
                $maTaiKhoan = $stmt->insert_id;
                $stmt->close();
            }
            
            // Tạo mã KH mới - FIX LỖI TRÙNG
            $nextMaKH = $this->getNextMaKH();
            
            // Thêm khách hàng
            $queryKH = "INSERT INTO khachhang (MaKH, HoTen, SoDienThoai, DiaChi, TrangThai, MaTaiKhoan, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
            $stmt = $this->conn->prepare($queryKH);
            $stmt->bind_param("sssssi", 
                $nextMaKH,
                $data['hoten'],
                $data['sodienthoai'],
                $data['diachi'],
                $data['trangthai'],
                $maTaiKhoan
            );
            
            if (!$stmt->execute()) {
                // Kiểm tra lỗi duplicate key
                if ($this->conn->errno == 1062) {
                    throw new Exception('Mã KH đã tồn tại. Vui lòng thử lại.');
                }
                throw new Exception('Lỗi khi thêm khách hàng: ' . $stmt->error);
            }
            
            $stmt->close();
            mysqli_commit($this->conn);
            return ['success' => true, 'maKH' => $nextMaKH];
            
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Cập nhật khách hàng - THÊM CHỨC NĂNG SỬA
    public function updateKhachHang($maKH, $data) {
        mysqli_begin_transaction($this->conn);
        
        try {
            // Lấy thông tin cũ
            $khachHangCu = $this->getKhachHangByMaKH($maKH);
            if (!$khachHangCu) {
                throw new Exception('Không tìm thấy khách hàng');
            }
            
            $maTaiKhoanCu = $khachHangCu['MaTaiKhoan'];
            
            // Xử lý tài khoản
            if (!empty($data['tendangnhap']) && !empty($data['matkhau'])) {
                // Nếu chưa có tài khoản -> tạo mới
                if (!$maTaiKhoanCu || $maTaiKhoanCu == 0) {
                    // Kiểm tra trùng username
                    if ($this->checkDuplicateUsername($data['tendangnhap'])) {
                        throw new Exception('Tên đăng nhập đã tồn tại');
                    }
                    
                    $hashedPassword = md5($data['matkhau']);
                    $queryTK = "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND, created_at) 
                                VALUES (?, ?, 'khachhang', 1, ?, ?, NOW())";
                    
                    $stmt = $this->conn->prepare($queryTK);
                    $stmt->bind_param("ssss", 
                        $data['tendangnhap'],
                        $hashedPassword,
                        $data['email'],
                        $data['cmnd']
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Lỗi khi tạo tài khoản: ' . $stmt->error);
                    }
                    
                    $maTaiKhoan = $stmt->insert_id;
                    $stmt->close();
                } else {
                    // Đã có tài khoản -> cập nhật
                    $maTaiKhoan = $maTaiKhoanCu;
                    $hashedPassword = md5($data['matkhau']);
                    
                    $queryTK = "UPDATE tai_khoan 
                                SET TenDangNhap = ?, 
                                    MatKhau = ?,
                                    Email = ?,
                                    CMND = ?,
                                    updated_at = NOW()
                                WHERE id = ?";
                    
                    $stmt = $this->conn->prepare($queryTK);
                    $stmt->bind_param("ssssi", 
                        $data['tendangnhap'],
                        $hashedPassword,
                        $data['email'],
                        $data['cmnd'],
                        $maTaiKhoan
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Lỗi khi cập nhật tài khoản: ' . $stmt->error);
                    }
                    $stmt->close();
                }
            } else {
                // Không cập nhật tài khoản
                $maTaiKhoan = $maTaiKhoanCu;
            }
            
            // Cập nhật thông tin khách hàng
            $queryKH = "UPDATE khachhang 
                        SET HoTen = ?, 
                            SoDienThoai = ?, 
                            DiaChi = ?, 
                            TrangThai = ?,
                            MaTaiKhoan = ?,
                            updated_at = NOW()
                        WHERE MaKH = ?";
            
            $stmt = $this->conn->prepare($queryKH);
            $stmt->bind_param("ssssis", 
                $data['hoten'],
                $data['sodienthoai'],
                $data['diachi'],
                $data['trangthai'],
                $maTaiKhoan,
                $maKH
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Lỗi khi cập nhật khách hàng: ' . $stmt->error);
            }
            
            $stmt->close();
            mysqli_commit($this->conn);
            return ['success' => true, 'message' => 'Cập nhật thành công'];
            
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Xóa khách hàng - FIX LỖI KHÔNG XÓA ĐƯỢC
    public function deleteKhachHang($maKH) {
        mysqli_begin_transaction($this->conn);
        
        try {
            // Kiểm tra xem khách hàng có tồn tại không
            $checkQuery = "SELECT MaTaiKhoan FROM khachhang WHERE MaKH = ?";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->bind_param("s", $maKH);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                throw new Exception('Không tìm thấy khách hàng để xóa');
            }
            
            $row = $result->fetch_assoc();
            $maTaiKhoan = $row['MaTaiKhoan'];
            $stmt->close();
            
            // Xóa khách hàng trước (vì có thể có ràng buộc khóa ngoại)
            $deleteKH = "DELETE FROM khachhang WHERE MaKH = ?";
            $stmt = $this->conn->prepare($deleteKH);
            $stmt->bind_param("s", $maKH);
            
            if (!$stmt->execute()) {
                // Nếu lỗi do khóa ngoại, thử xóa tài khoản trước
                if ($this->conn->errno == 1451) {
                    // Xóa tài khoản trước nếu có
                    if ($maTaiKhoan && $maTaiKhoan > 0) {
                        $deleteTK = "DELETE FROM tai_khoan WHERE id = ?";
                        $stmt2 = $this->conn->prepare($deleteTK);
                        $stmt2->bind_param("i", $maTaiKhoan);
                        if (!$stmt2->execute()) {
                            throw new Exception('Lỗi khi xóa tài khoản: ' . $stmt2->error);
                        }
                        $stmt2->close();
                    }
                    
                    // Thử xóa khách hàng lại
                    $stmt->execute();
                } else {
                    throw new Exception('Lỗi khi xóa khách hàng: ' . $stmt->error);
                }
            }
            $stmt->close();
            
            // Nếu vẫn chưa xóa được tài khoản và có MaTaiKhoan
            if ($maTaiKhoan && $maTaiKhoan > 0) {
                $checkTK = "SELECT COUNT(*) as count FROM khachhang WHERE MaTaiKhoan = ?";
                $stmt = $this->conn->prepare($checkTK);
                $stmt->bind_param("i", $maTaiKhoan);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] == 0) {
                    // Không còn khách hàng nào dùng tài khoản này -> xóa
                    $deleteTK = "DELETE FROM tai_khoan WHERE id = ?";
                    $stmt2 = $this->conn->prepare($deleteTK);
                    $stmt2->bind_param("i", $maTaiKhoan);
                    $stmt2->execute();
                    $stmt2->close();
                }
                $stmt->close();
            }
            
            mysqli_commit($this->conn);
            return ['success' => true, 'message' => 'Xóa thành công'];
            
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Xóa nhiều khách hàng - THÊM CHỨC NĂNG XÓA NHIỀU
    public function deleteMultipleKhachHang($listMaKH) {
        mysqli_begin_transaction($this->conn);
        
        try {
            $successCount = 0;
            $errorMessages = [];
            
            foreach ($listMaKH as $maKH) {
                $result = $this->deleteKhachHang($maKH);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorMessages[] = "Mã $maKH: " . $result['message'];
                }
            }
            
            mysqli_commit($this->conn);
            
            if (count($errorMessages) > 0) {
                return [
                    'success' => false, 
                    'message' => 'Xóa được ' . $successCount . '/'. count($listMaKH) . ' khách hàng',
                    'errors' => $errorMessages
                ];
            }
            
            return [
                'success' => true, 
                'message' => 'Đã xóa thành công ' . $successCount . ' khách hàng'
            ];
            
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Các phương thức check duplicate
    public function checkDuplicatePhone($sodienthoai, $currentMaKH = null) {
        $query = "SELECT MaKH FROM khachhang WHERE SoDienThoai = ?";
        $params = [$sodienthoai];
        
        if ($currentMaKH) {
            $query .= " AND MaKH != ?";
            $params[] = $currentMaKH;
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (count($params) == 1) {
            $stmt->bind_param("s", $params[0]);
        } else {
            $stmt->bind_param("ss", $params[0], $params[1]);
        }
        
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();
        return $count > 0;
    }
    
    public function checkDuplicateUsername($tendangnhap, $currentTaiKhoanId = null) {
        $query = "SELECT id FROM tai_khoan WHERE TenDangNhap = ?";
        $params = [$tendangnhap];
        
        if ($currentTaiKhoanId) {
            $query .= " AND id != ?";
            $params[] = $currentTaiKhoanId;
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (count($params) == 1) {
            $stmt->bind_param("s", $params[0]);
        } else {
            $stmt->bind_param("si", $params[0], $params[1]);
        }
        
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();
        return $count > 0;
    }
    
    public function checkDuplicateEmail($email, $currentTaiKhoanId = null) {
        if (empty($email)) return false;
        
        $query = "SELECT id FROM tai_khoan WHERE Email = ?";
        $params = [$email];
        
        if ($currentTaiKhoanId) {
            $query .= " AND id != ?";
            $params[] = $currentTaiKhoanId;
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (count($params) == 1) {
            $stmt->bind_param("s", $params[0]);
        } else {
            $stmt->bind_param("si", $params[0], $params[1]);
        }
        
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();
        return $count > 0;
    }
    
    public function checkDuplicateCMND($cmnd, $currentTaiKhoanId = null) {
        if (empty($cmnd)) return false;
        
        $query = "SELECT id FROM tai_khoan WHERE CMND = ?";
        $params = [$cmnd];
        
        if ($currentTaiKhoanId) {
            $query .= " AND id != ?";
            $params[] = $currentTaiKhoanId;
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (count($params) == 1) {
            $stmt->bind_param("s", $params[0]);
        } else {
            $stmt->bind_param("si", $params[0], $params[1]);
        }
        
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();
        return $count > 0;
    }
    
    // Tạo mã KH mới - FIX LỖI TRÙNG KHÓA CHÍNH
    private function getNextMaKH() {
        // Lấy mã KH lớn nhất
        $query = "SELECT MaKH FROM khachhang WHERE MaKH REGEXP '^KH[0-9]+$' ORDER BY LENGTH(MaKH) DESC, MaKH DESC LIMIT 1";
        $result = mysqli_query($this->conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $maxMaKH = $row['MaKH'];
            
            // Lấy số từ mã KH
            preg_match('/KH(\d+)/', $maxMaKH, $matches);
            $currentNumber = isset($matches[1]) ? intval($matches[1]) : 0;
            $nextNumber = $currentNumber + 1;
            
            // Tạo mã mới và kiểm tra trùng
            $attempts = 0;
            do {
                $newMaKH = 'KH' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                
                // Kiểm tra xem mã đã tồn tại chưa
                $checkQuery = "SELECT COUNT(*) as count FROM khachhang WHERE MaKH = ?";
                $stmt = $this->conn->prepare($checkQuery);
                $stmt->bind_param("s", $newMaKH);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                
                if ($row['count'] == 0) {
                    return $newMaKH; // Mã không trùng
                }
                
                $nextNumber++; // Tăng số lên nếu trùng
                $attempts++;
                
                if ($attempts > 100) {
                    // Tạo mã ngẫu nhiên nếu vẫn trùng
                    return 'KH' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                }
                
            } while (true);
        } else {
            return 'KH001'; // Mã đầu tiên
        }
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>