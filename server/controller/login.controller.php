<?php
session_start(); // Phải có session_start() ở đầu file

include_once '../model/login.model.php';

class LoginController {
    private $loginModel;

    public function __construct() {
        $this->loginModel = new LoginModel();
    }

    // Xử lý đăng nhập
    public function processLogin() {
        // Kiểm tra nếu là POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tenDangNhap = $_POST['username'] ?? '';
            $matKhau = $_POST['password'] ?? '';
            $captchaInput = $_POST['captcha'] ?? '';
            $captchaSession = $_SESSION['captcha'] ?? '';

            // Kiểm tra captcha
            if (empty($captchaInput) || $captchaInput !== $captchaSession) {
                $_SESSION['error'] = "Mã xác thực không đúng!";
                header('Location: ../view/login.php');
                exit();
            }

            // Kiểm tra đăng nhập
            $result = $this->loginModel->logIn($tenDangNhap, $matKhau);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Lưu thông tin user vào SESSION
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['TenDangNhap'],
                    'role' => $user['VaiTro'],
                    'email' => $user['Email'],
                    'cmnd' => $user['CMND']
                ];

                // Lưu thông tin vai trò vào SESSION riêng
                $_SESSION['vaitro'] = $user['VaiTro'];
                $_SESSION['username'] = $user['TenDangNhap'];

                // Lưu COOKIE với thời gian 30 ngày
                $cookie_time = time() + (30 * 24 * 60 * 60); // 30 ngày
                
                setcookie('user_role', $user['VaiTro'], $cookie_time, "/");
                setcookie('username', $user['TenDangNhap'], $cookie_time, "/");
                setcookie('user_id', $user['id'], $cookie_time, "/");
                setcookie('is_logged_in', 'true', $cookie_time, "/");

                // Debug: kiểm tra session và cookie
                error_log("Login successful - Role: " . $user['VaiTro']);
                error_log("Session role: " . $_SESSION['vaitro']);
                error_log("Cookie set for role: " . $user['VaiTro']);

                // Chuyển hướng đến dashboard
                header('Location: ../view/dashboard.php');
                exit();
                
            } else {
                $_SESSION['error'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
                header('Location: ../view/login.php');
                exit();
            }
        } else {
            // Nếu không phải POST, quay lại login
            header('Location: ../view/login.php');
            exit();
        }
    }

    // Đăng xuất
    public function logout() {
        // Xóa SESSION
        session_unset();
        session_destroy();
        
        // Xóa COOKIE
        setcookie('user_role', '', time() - 3600, "/");
        setcookie('username', '', time() - 3600, "/");
        setcookie('user_id', '', time() - 3600, "/");
        setcookie('is_logged_in', '', time() - 3600, "/");
        
        header('Location: ../view/login.php');
        exit();
    }

    // Kiểm tra đăng nhập (middleware)
    public function checkAuth() {
        // Ưu tiên kiểm tra SESSION trước
        if (isset($_SESSION['user']) && isset($_SESSION['vaitro'])) {
            return [
                'user' => $_SESSION['user'],
                'role' => $_SESSION['vaitro']
            ];
        }
        
        // Nếu không có SESSION, kiểm tra COOKIE
        if (isset($_COOKIE['is_logged_in']) && $_COOKIE['is_logged_in'] === 'true') {
            if (isset($_COOKIE['user_role']) && isset($_COOKIE['username'])) {
                
                // Khôi phục SESSION từ COOKIE
                $_SESSION['user'] = [
                    'id' => $_COOKIE['user_id'] ?? '',
                    'username' => $_COOKIE['username'],
                    'role' => $_COOKIE['user_role']
                ];
                $_SESSION['vaitro'] = $_COOKIE['user_role'];
                $_SESSION['username'] = $_COOKIE['username'];
                
                return [
                    'user' => $_SESSION['user'],
                    'role' => $_COOKIE['user_role']
                ];
            }
        }
        
        // Nếu không đăng nhập, chuyển hướng về login
        header('Location: ../view/login.php');
        exit();
    }
}

// Xử lý action từ URL
if (isset($_GET['action'])) {
    $controller = new LoginController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ../view/login.php');
        exit();
    }
}
?>