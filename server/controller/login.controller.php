<?php
session_start(); // Phải có session_start() ở đầu file

include_once '../model/login.model.php';

class LoginController
{
    private $loginModel;

    public function __construct()
    {
        $this->loginModel = new LoginModel();
    }

    // Xử lý đăng nhập bằng EMAIL
    public function processLogin()
    {
        // Kiểm tra nếu là POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $matKhau = $_POST['password'] ?? '';
            $captchaInput = $_POST['captcha'] ?? '';
            $captchaSession = $_SESSION['captcha'] ?? '';

            // 1. Kiểm tra captcha
            if (empty($captchaInput) || $captchaInput !== $captchaSession) {
                $_SESSION['error'] = "Mã xác thực không đúng!";
                header('Location: ../view/login/login.php');
                exit();
            }

            // 2. Kiểm tra email hợp lệ
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Email không hợp lệ!";
                header('Location: ../view/login/login.php');
                exit();
            }

            // 3. Kiểm tra đăng nhập bằng email
            $result = $this->loginModel->logInByEmail($email, $matKhau);

            if ($result['success']) {
                $user = $result['user'];

                // Lưu thông tin đầy đủ vào SESSION
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['TenDangNhap'],
                    'role' => $user['VaiTro'],
                    'email' => $user['Email'],
                    'cmnd' => $user['CMND'],
                    'ma_nhan_vien' => $user['MaNhanVien'],
                    'ho_ten' => $user['HoTen'],
                    'phong_ban' => $user['PhongBan']
                ];

                // Lưu thông tin vai trò vào SESSION riêng
                $_SESSION['vaitro'] = $user['VaiTro'];
                $_SESSION['username'] = $user['TenDangNhap'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['user_id'] = $user['id']; // THÊM DÒNG NÀY


                // Lưu COOKIE với thời gian 30 ngày
                $cookie_time = time() + (30 * 24 * 60 * 60); // 30 ngày

                setcookie('user_role', $user['VaiTro'], $cookie_time, "/");
                setcookie('username', $user['TenDangNhap'], $cookie_time, "/");
                setcookie('user_id', $user['id'], $cookie_time, "/");
                setcookie('user_email', $user['Email'], $cookie_time, "/");
                setcookie('is_logged_in', 'true', $cookie_time, "/");

                // Cập nhật thời gian đăng nhập cuối
                $this->updateLastLogin($user['id']);

                // Debug: kiểm tra session và cookie
                error_log("Login successful - Email: " . $user['Email'] . ", Role: " . $user['VaiTro']);

                // Chuyển hướng đến dashboard
                header('Location: ../view/home/dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = $result['error'];
                header('Location: ../view/login/login.php');
                exit();
            }
        } else {
            // Nếu không phải POST, quay lại login
            header('Location: ../view/login/login.php');
            exit();
        }
    }

    // Cập nhật thời gian đăng nhập cuối
    private function updateLastLogin($userId)
    {
        $p = new Connect();
        $conn = $p->openConnect();

        $sql = "UPDATE tai_khoan SET updated_at = NOW() WHERE id = '$userId'";
        $conn->query($sql);

        $p->closeConnect($conn);
    }

    // Đăng xuất
    public function logout()
    {
        // Xóa SESSION
        session_unset();
        session_destroy();

        // Xóa COOKIE
        $cookieParams = ['user_role', 'username', 'user_id', 'user_email', 'is_logged_in'];
        foreach ($cookieParams as $cookie) {
            setcookie($cookie, '', time() - 3600, "/");
        }

        // Chuyển hướng về trang login
        header('Location: ../view/login/login.php?logout=success');
        exit();
    }

    // Kiểm tra đăng nhập (middleware) - Updated
    public function checkAuth()
    {
        // Ưu tiên kiểm tra SESSION trước
        if (isset($_SESSION['user']) && isset($_SESSION['vaitro'])) {
            $user = $_SESSION['user'];

            // Kiểm tra thêm trạng thái tài khoản và nhân viên
            $currentUser = $this->loginModel->getUserByEmail($user['email']);

            if (
                !$currentUser ||
                $currentUser['TrangThai'] != 1 ||
                $currentUser['nhan_vien_trang_thai'] !== 'Đang làm'
            ) {

                $this->logout();
                header('Location: ../view/login/login.php?error=account_inactive');
                exit();
            }

            return [
                'user' => $_SESSION['user'],
                'role' => $_SESSION['vaitro']
            ];
        }

        // Nếu không có SESSION, kiểm tra COOKIE
        if (isset($_COOKIE['is_logged_in']) && $_COOKIE['is_logged_in'] === 'true') {
            if (isset($_COOKIE['user_email'])) {
                // Lấy thông tin user từ email trong cookie
                $currentUser = $this->loginModel->getUserByEmail($_COOKIE['user_email']);

                if (
                    $currentUser &&
                    $currentUser['TrangThai'] == 1 &&
                    $currentUser['nhan_vien_trang_thai'] === 'Đang làm'
                ) {

                    // Khôi phục SESSION từ database
                    $_SESSION['user'] = [
                        'id' => $currentUser['id'],
                        'username' => $currentUser['TenDangNhap'],
                        'role' => $currentUser['VaiTro'],
                        'email' => $currentUser['Email'],
                        'cmnd' => $currentUser['CMND'],
                        'ma_nhan_vien' => $currentUser['MaNhanVien'],
                        'ho_ten' => $currentUser['HoTen'],
                        'phong_ban' => $currentUser['PhongBan']
                    ];
                    $_SESSION['vaitro'] = $currentUser['VaiTro'];
                    $_SESSION['username'] = $currentUser['TenDangNhap'];
                    $_SESSION['email'] = $currentUser['Email'];

                    return [
                        'user' => $_SESSION['user'],
                        'role' => $currentUser['VaiTro']
                    ];
                }
            }
        }

        // Nếu không đăng nhập, chuyển hướng về login
        header('Location: ../view/login/login.php');
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
        header('Location: ../view/login/login.php');
        exit();
    }
}
