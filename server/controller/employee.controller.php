<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_path = '/ABC-Resort';
$base_url = $protocol . '://' . $host . $project_path;

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || !isset($_SESSION['vaitro'])) {
    header("Location: " . $base_url . "/server/view/login/login.php");
    exit();
}

// Cho phép TẤT CẢ vai trò nhân viên, bao gồm quản lý
$allowed_roles = ['letan', 'buongphong', 'ketoan', 'kinhdoanh', 'thungan', 'quanly'];
if (!in_array($_SESSION['vaitro'], $allowed_roles)) {
    header("Location: " . $base_url . "/client/index.php");
    exit();
}

require_once __DIR__ . '/../model/connectDB.php';

$action = $_GET['action'] ?? '';

if ($action === 'changePassword') {
    changePassword();
}

function changePassword() {
    global $base_url;
    
    $errors = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($current_password)) {
            $errors['current_password'] = "Vui lòng nhập mật khẩu hiện tại";
        }
        
        if (empty($new_password)) {
            $errors['new_password'] = "Vui lòng nhập mật khẩu mới";
        } elseif (strlen($new_password) < 6) {
            $errors['new_password'] = "Mật khẩu phải có ít nhất 6 ký tự";
        }
        
        if (empty($confirm_password)) {
            $errors['confirm_password'] = "Vui lòng nhập lại mật khẩu mới";
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = "Mật khẩu nhập lại không khớp";
        }
        
        if (empty($errors)) {
            try {
                $connect = new Connect();
                $conn = $connect->openConnect();
                
                $user_id = $_SESSION['user']['id'] ?? 0;
                
                // Kiểm tra mật khẩu hiện tại
                $sql = "SELECT MatKhau FROM tai_khoan WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if (!$user) {
                    $errors['current_password'] = "Tài khoản không tồn tại";
                } else {
                    // SỬA: So sánh MD5 hash thay vì password_verify
                    $current_password_md5 = md5($current_password);
                    if ($current_password_md5 !== $user['MatKhau']) {
                        $errors['current_password'] = "Mật khẩu hiện tại không đúng";
                    } else {
                        // SỬA: Lưu mật khẩu mới dưới dạng MD5
                        $new_password_md5 = md5($new_password);
                        $sql = "UPDATE tai_khoan SET MatKhau = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $new_password_md5, $user_id);
                        
                        if ($stmt->execute()) {
                            $_SESSION['success_message'] = "Đổi mật khẩu thành công!";
                            
                            // Ghi log đổi mật khẩu
                            logPasswordChange($conn, $user_id);
                        } else {
                            $errors['general'] = "Có lỗi xảy ra khi đổi mật khẩu. Vui lòng thử lại.";
                        }
                    }
                }
                
                $stmt->close();
                $connect->closeConnect($conn);
                
            } catch (Exception $e) {
                error_log("Database error in changePassword: " . $e->getMessage());
                $errors['general'] = "Có lỗi xảy ra khi xử lý yêu cầu. Vui lòng thử lại sau.";
            }
        }
    } else {
        // Nếu không phải POST request
        $errors['general'] = "Phương thức không hợp lệ";
    }
    
    if (!empty($errors)) {
        $_SESSION['password_errors'] = $errors;
    }
    
    // Redirect về trang profile
    header("Location: " . $base_url . "/server/view/employee/profile.php");
    exit();
}

function logPasswordChange($conn, $user_id) {
    try {
        $sql = "SHOW TABLES LIKE 'activity_log'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $sql = "INSERT INTO activity_log (action_type, description, user_id, user_type, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $action_type = 'password_change';
            $description = 'Người dùng đã đổi mật khẩu tài khoản';
            $user_type = $_SESSION['vaitro'] ?? 'employee';
            
            $stmt->bind_param("ssis", $action_type, $description, $user_id, $user_type);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Log error: " . $e->getMessage());
    }
}

// Nếu không có action nào được chỉ định
if (empty($action)) {
    header("Location: " . $base_url . "/server/view/employee/profile.php");
    exit();
}
?>