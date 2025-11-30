<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../model/connectDB.php';
require_once __DIR__ . '/../model/user.model.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $connectDB = new Connect();
        $conn = $connectDB->openConnect();
        $this->userModel = new UserModel($conn);
    }

    public function handleRequest()
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'register':
                $this->showRegisterForm();
                break;
            case 'doRegister':
                $this->processRegister();
                break;
            case 'login':
                $this->showLoginForm();
                break;
            case 'doLogin':
                $this->processLogin();
                break;
            case 'logout':
                $this->handleLogout();
                break;
            default:
                $this->showLoginForm();
                break;
        }
    }

    // HIỂN THỊ FORM ĐĂNG KÝ
    private function showRegisterForm()
    {
        require_once __DIR__ . '/../view/auth/register.php';
    }

    private function processRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = trim($_POST['fullname'] ?? '');
            $cmnd = trim($_POST['cmnd'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // VALIDATE DỮ LIỆU
            $errors = $this->validateRegisterData($fullname, $cmnd, $email, $phone, $password, $confirm_password);

            if (empty($errors)) {
                // SỬA: DÙNG HỌ TÊN LÀM TÊN ĐĂNG NHẬP
                $username = $fullname;

                $hashedPassword = md5($password);

                $userData = [
                    'username' => $username,
                    'password' => $hashedPassword,
                    'email' => $email,
                    'cmnd' => $cmnd,
                    'fullname' => $fullname,
                    'phone' => $phone
                ];

                if ($this->userModel->createUser($userData)) {
                    $_SESSION['register_success'] = "🎉 Đăng ký thành công! Tài khoản đã được tạo. Bạn có thể đăng nhập ngay.";
                    $_SESSION['show_alert'] = "success";
                    $_SESSION['alert_message'] = "🎉 Đăng ký thành công!";
                    header("Location: user.controller.php?action=login");
                    exit();
                } else {
                    $errors['general'] = "❌ Có lỗi xảy ra khi đăng ký. Vui lòng thử lại!";
                }
            }

            // TRUYỀN LỖI VÀ DỮ LIỆU CŨ VỀ VIEW
            $oldInput = [
                'fullname' => $fullname,
                'cmnd' => $cmnd,
                'email' => $email,
                'phone' => $phone
            ];

            require_once __DIR__ . '/../view/auth/register.php';
        } else {
            header("Location: user.controller.php?action=register");
            exit();
        }
    }


    // VALIDATE DỮ LIỆU ĐĂNG KÝ - ĐẦY ĐỦ TEST CASE
    private function validateRegisterData($fullname, $cmnd, $email, $phone, $password, $confirm_password)
    {
        $errors = [];

        // VALIDATE HỌ TÊN
        if (empty($fullname)) {
            $errors['fullname'] = "⛔ Họ tên không được để trống";
        } elseif (strlen($fullname) < 2) {
            $errors['fullname'] = "⛔ Họ tên phải có ít nhất 2 ký tự";
        } elseif (preg_match('/[0-9]/', $fullname)) {
            $errors['fullname'] = "⛔ Họ tên không được chứa số";
        }

        // VALIDATE CMND
        if (empty($cmnd)) {
            $errors['cmnd'] = "⛔ CMND không được để trống";
        } elseif (!preg_match('/^\d{9,12}$/', $cmnd)) {
            $errors['cmnd'] = "⛔ CMND phải có 9-12 chữ số";
        } elseif ($this->userModel->checkCMNDExists($cmnd)) {
            $errors['cmnd'] = "⛔ CMND đã được đăng ký trong hệ thống";
        }

        // VALIDATE EMAIL
        if (empty($email)) {
            $errors['email'] = "⛔ Email không được để trống";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "⛔ Email không đúng định dạng";
        } elseif ($this->userModel->checkEmailExists($email)) {
            $errors['email'] = "⛔ Email đã được đăng ký trong hệ thống";
        }

        // VALIDATE SỐ ĐIỆN THOẠI
        if (empty($phone)) {
            $errors['phone'] = "⛔ Số điện thoại không được để trống";
        } elseif (!preg_match('/^(03|05|07|08|09)[0-9]{8}$/', $phone)) {
            $errors['phone'] = "⛔ Số điện thoại không hợp lệ (phải bắt đầu bằng 03,05,07,08,09 và có 10 số)";
        } elseif ($this->userModel->checkPhoneExists($phone)) {
            $errors['phone'] = "⛔ Số điện thoại đã được đăng ký trong hệ thống";
        }

        // VALIDATE PASSWORD
        if (empty($password)) {
            $errors['password'] = "⛔ Mật khẩu không được để trống";
        } elseif (strlen($password) < 6) {
            $errors['password'] = "⛔ Mật khẩu phải có ít nhất 6 ký tự";
        }

        // VALIDATE CONFIRM PASSWORD - SỬA LẠI PHẦN NÀY
        if (empty($confirm_password)) {
            $errors['confirm_password'] = "⛔ Vui lòng nhập lại mật khẩu";
        } elseif (trim($password) !== trim($confirm_password)) {
            $errors['confirm_password'] = "⛔ Mật khẩu nhập lại không trùng khớp";
        }

        return $errors;
    }

    // CÁC PHƯƠNG THỨC KHÁC GIỮ NGUYÊN...
    private function showLoginForm()
    {
        require_once __DIR__ . '/../view/auth/login.php';
    }

    private function processLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $errors = $this->validateLoginData($email, $password);

            if (empty($errors)) {
                $user = $this->userModel->login($email, $password);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['TenDangNhap'];
                    $_SESSION['vaitro'] = $user['VaiTro'];
                    $_SESSION['email'] = $user['Email'];

                    // THÊM SESSION CHO ALERT ĐĂNG NHẬP THÀNH CÔNG
                    $_SESSION['show_alert'] = "success";
                    $_SESSION['alert_message'] = "🎉 Đăng nhập thành công! Chào mừng " . $user['TenDangNhap'] . "!";

                    header("Location: ../../client/index.php");
                    exit();
                } else {
                    $errors['general'] = "❌ Email hoặc mật khẩu không đúng!";
                }
            }

            require_once __DIR__ . '/../view/auth/login.php';
        } else {
            header("Location: user.controller.php?action=login");
            exit();
        }
    }

    private function validateLoginData($email, $password)
    {
        $errors = [];

        if (empty($email)) {
            $errors['email'] = "⛔ Email không được để trống";
        }

        if (empty($password)) {
            $errors['password'] = "⛔ Mật khẩu không được để trống";
        }

        return $errors;
    }

    private function handleLogout()
    {
        session_destroy();
        // SỬA: REDIRECT VỀ TRANG CHỦ (qua controller)
        header("Location: ../../client/index.php");
        exit();
    }
}

// CHẠY CONTROLLER
$userController = new UserController();
$userController->handleRequest();
