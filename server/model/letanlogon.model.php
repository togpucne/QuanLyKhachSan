<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/connectDB.php';

class LetanLogonModel {
    private $conn;

    public function __construct() {
        $connect = new Connect();
        $this->conn = $connect->openConnect();
    }

    // Kiểm tra tên đăng nhập đã tồn tại chưa
    public function checkUsernameExists($username, $excludeAccountId = null) {
        $sql = "SELECT id FROM tai_khoan WHERE TenDangNhap = ?";
        if ($excludeAccountId) {
            $sql .= " AND id != ?";
        }
        $stmt = $this->conn->prepare($sql);
        
        if ($excludeAccountId) {
            $stmt->bind_param("si", $username, $excludeAccountId);
        } else {
            $stmt->bind_param("s", $username);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Kiểm tra CMND đã tồn tại chưa
    public function checkCMNDExists($cmnd, $excludeAccountId = null) {
        $sql = "SELECT id FROM tai_khoan WHERE CMND = ?";
        if ($excludeAccountId) {
            $sql .= " AND id != ?";
        }
        $stmt = $this->conn->prepare($sql);
        
        if ($excludeAccountId) {
            $stmt->bind_param("si", $cmnd, $excludeAccountId);
        } else {
            $stmt->bind_param("s", $cmnd);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Kiểm tra số điện thoại đã tồn tại chưa
    public function checkPhoneExists($phone, $excludeMaKH = null) {
        $sql = "SELECT MaKH FROM khachhang WHERE SoDienThoai = ?";
        if ($excludeMaKH) {
            $sql .= " AND MaKH != ?";
        }
        $stmt = $this->conn->prepare($sql);
        
        if ($excludeMaKH) {
            $stmt->bind_param("ss", $phone, $excludeMaKH);
        } else {
            $stmt->bind_param("s", $phone);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Kiểm tra email đã tồn tại chưa
    public function checkEmailExists($email, $excludeAccountId = null) {
        if (empty($email)) return false;
        
        $sql = "SELECT id FROM tai_khoan WHERE Email = ?";
        if ($excludeAccountId) {
            $sql .= " AND id != ?";
        }
        $stmt = $this->conn->prepare($sql);
        
        if ($excludeAccountId) {
            $stmt->bind_param("si", $email, $excludeAccountId);
        } else {
            $stmt->bind_param("s", $email);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Đăng ký tài khoản mới
    public function registerAccount($data) {
        $this->conn->begin_transaction();

        try {
            // Mật khẩu mặc định là 123456
            $defaultPassword = '123456';
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
            
            // Thêm vào bảng tai_khoan
            $sqlAccount = "INSERT INTO tai_khoan (TenDangNhap, MatKhau, VaiTro, TrangThai, Email, CMND) 
                          VALUES (?, ?, 'khachhang', 1, ?, ?)";
            $stmtAccount = $this->conn->prepare($sqlAccount);
            $stmtAccount->bind_param("ssss", 
                $data['username'],
                $hashedPassword, 
                $data['email'], 
                $data['cmnd']
            );
            
            if (!$stmtAccount->execute()) {
                throw new Exception("Lỗi thêm tài khoản: " . $stmtAccount->error);
            }

            $accountId = $this->conn->insert_id;

            // Tạo MaKH tự động
            $maKH = 'KH' . date('Ymd') . str_pad($accountId, 4, '0', STR_PAD_LEFT);

            // Thêm vào bảng khachhang
            $sqlCustomer = "INSERT INTO khachhang (MaKH, HoTen, SoDienThoai, DiaChi, TrangThai, MaTaiKhoan) 
                           VALUES (?, ?, ?, ?, 'Không ở', ?)";
            $stmtCustomer = $this->conn->prepare($sqlCustomer);
            $stmtCustomer->bind_param("ssssi", 
                $maKH, 
                $data['fullname'], 
                $data['phone'], 
                $data['address'], 
                $accountId
            );
            
            if (!$stmtCustomer->execute()) {
                throw new Exception("Lỗi thêm khách hàng: " . $stmtCustomer->error);
            }

            $this->conn->commit();
            return [
                'success' => true, 
                'account_id' => $accountId, 
                'maKH' => $maKH,
                'username' => $data['username'],
                'password' => $defaultPassword // Trả về mật khẩu mặc định
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Lấy danh sách tất cả khách hàng
    public function getAllCustomers() {
        $sql = "SELECT k.MaKH, k.HoTen, k.SoDienThoai, k.DiaChi, k.TrangThai, 
                       t.id as account_id, t.TenDangNhap, t.Email, t.CMND, t.created_at,
                       t.VaiTro, t.TrangThai as tai_khoan_trangthai
                FROM khachhang k 
                JOIN tai_khoan t ON k.MaTaiKhoan = t.id 
                WHERE t.VaiTro = 'khachhang' 
                ORDER BY k.created_at DESC";
        
        $result = $this->conn->query($sql);
        $customers = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
        }
        
        return $customers;
    }

    // Lấy thông tin khách hàng theo MaKH
    public function getCustomerByMaKH($maKH) {
        $sql = "SELECT k.MaKH, k.HoTen, k.SoDienThoai, k.DiaChi, k.TrangThai, 
                       t.id as account_id, t.TenDangNhap, t.Email, t.CMND, t.VaiTro,
                       t.TrangThai as tai_khoan_trangthai
                FROM khachhang k 
                JOIN tai_khoan t ON k.MaTaiKhoan = t.id 
                WHERE k.MaKH = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $maKH);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $row = $result->fetch_assoc();
        return $row ? $row : null;
    }

    // Cập nhật thông tin khách hàng
    public function updateCustomer($maKH, $data) {
        $this->conn->begin_transaction();

        try {
            // Lấy thông tin hiện tại
            $customer = $this->getCustomerByMaKH($maKH);
            if (!$customer) {
                throw new Exception("Không tìm thấy khách hàng");
            }

            $accountId = $customer['account_id'];

            // Cập nhật bảng khachhang
            $sqlCustomer = "UPDATE khachhang 
                           SET HoTen = ?, SoDienThoai = ?, DiaChi = ?, TrangThai = ?
                           WHERE MaKH = ?";
            $stmtCustomer = $this->conn->prepare($sqlCustomer);
            $stmtCustomer->bind_param("sssss", 
                $data['fullname'], 
                $data['phone'], 
                $data['address'],
                $data['trangthai'],
                $maKH
            );
            
            if (!$stmtCustomer->execute()) {
                throw new Exception("Lỗi cập nhật khách hàng: " . $stmtCustomer->error);
            }

            // Cập nhật bảng tai_khoan
            $sqlAccount = "UPDATE tai_khoan 
                          SET TenDangNhap = ?, Email = ?, CMND = ?, TrangThai = ?
                          WHERE id = ?";
            $stmtAccount = $this->conn->prepare($sqlAccount);
            $stmtAccount->bind_param("sssii", 
                $data['username'],
                $data['email'], 
                $data['cmnd'],
                $data['tai_khoan_trangthai'],
                $accountId
            );
            
            if (!$stmtAccount->execute()) {
                throw new Exception("Lỗi cập nhật tài khoản: " . $stmtAccount->error);
            }

            // Cập nhật mật khẩu nếu có
            if (!empty($data['password'])) {
                $sqlPassword = "UPDATE tai_khoan SET MatKhau = ? WHERE id = ?";
                $stmtPassword = $this->conn->prepare($sqlPassword);
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmtPassword->bind_param("si", $hashedPassword, $accountId);
                
                if (!$stmtPassword->execute()) {
                    throw new Exception("Lỗi cập nhật mật khẩu: " . $stmtPassword->error);
                }
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật thành công'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Reset mật khẩu về 123456
    public function resetPassword($maKH) {
        try {
            $customer = $this->getCustomerByMaKH($maKH);
            if (!$customer) {
                throw new Exception("Không tìm thấy khách hàng");
            }

            $accountId = $customer['account_id'];
            $defaultPassword = '123456';
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

            // Reset mật khẩu
            $sql = "UPDATE tai_khoan SET MatKhau = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $accountId);
            
            if (!$stmt->execute()) {
                throw new Exception("Lỗi reset mật khẩu: " . $stmt->error);
            }

            return ['success' => true, 'message' => 'Reset mật khẩu thành công!', 'password' => $defaultPassword];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Xóa khách hàng và tài khoản
    public function deleteCustomer($maKH) {
        $this->conn->begin_transaction();

        try {
            $customer = $this->getCustomerByMaKH($maKH);
            if (!$customer) {
                throw new Exception("Không tìm thấy khách hàng");
            }

            $accountId = $customer['account_id'];

            // Xóa từ bảng khachhang
            $sqlCustomer = "DELETE FROM khachhang WHERE MaKH = ?";
            $stmtCustomer = $this->conn->prepare($sqlCustomer);
            $stmtCustomer->bind_param("s", $maKH);
            
            if (!$stmtCustomer->execute()) {
                throw new Exception("Lỗi xóa khách hàng: " . $stmtCustomer->error);
            }

            // Xóa từ bảng tai_khoan
            $sqlAccount = "DELETE FROM tai_khoan WHERE id = ?";
            $stmtAccount = $this->conn->prepare($sqlAccount);
            $stmtAccount->bind_param("i", $accountId);
            
            if (!$stmtAccount->execute()) {
                throw new Exception("Lỗi xóa tài khoản: " . $stmtAccount->error);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa thành công'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>