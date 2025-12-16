<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_path = '/Toa-Sang-Resort';
$base_url = $protocol . '://' . $host . $project_path;

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || !isset($_SESSION['vaitro'])) {
    header("Location: " . $base_url . "/client/controller/user.controller.php?action=login");
    exit();
}

// SỬA: Cho phép TẤT CẢ vai trò nhân viên
$allowed_roles = ['letan', 'buongphong', 'ketoan', 'kinhdoanh', 'thungan'];
if (!in_array($_SESSION['vaitro'], $allowed_roles)) {
    if ($_SESSION['vaitro'] === 'quanly') {
        header("Location: " . $base_url . "/server/home/dashboard.php");
    } else {
        header("Location: " . $base_url . "/client/index.php");
    }
    exit();
}

require_once __DIR__ . '/../../model/connectDB.php';

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
                
                if (!$user || !password_verify($current_password, $user['MatKhau'])) {
                    $errors['current_password'] = "Mật khẩu hiện tại không đúng";
                } else {
                    // Cập nhật mật khẩu mới
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE tai_khoan SET MatKhau = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Đổi mật khẩu thành công!";
                    } else {
                        $errors['general'] = "Có lỗi xảy ra khi đổi mật khẩu";
                    }
                }
                
                $stmt->close();
                $connect->closeConnect($conn);
            } catch (Exception $e) {
                error_log("Database error: " . $e->getMessage());
                $errors['general'] = "Có lỗi xảy ra khi xử lý yêu cầu";
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['password_errors'] = $errors;
    }
    
    // Redirect về trang profile
    header("Location: " . $base_url . "/server/view/employee/profile.php");
    exit();
}