<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../model/letanlogon.model.php';

class LetanLogonController {
    private $model;

    public function __construct() {
        $this->model = new LetanLogonModel();
    }

    // Xử lý đăng ký mới
    public function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
            $errors = $this->validateRegistrationData($_POST);
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Kiểm tra tên đăng nhập
            if ($this->model->checkUsernameExists($_POST['username'])) {
                $errors['username'] = 'Tên đăng nhập đã được sử dụng';
                return ['success' => false, 'errors' => $errors];
            }

            // Kiểm tra CMND
            if ($this->model->checkCMNDExists($_POST['cmnd'])) {
                $errors['cmnd'] = 'CMND/CCCD đã được đăng ký';
                return ['success' => false, 'errors' => $errors];
            }

            // Kiểm tra số điện thoại
            if ($this->model->checkPhoneExists($_POST['phone'])) {
                $errors['phone'] = 'Số điện thoại đã được đăng ký';
                return ['success' => false, 'errors' => $errors];
            }

            // Kiểm tra email
            if (!empty($_POST['email']) && $this->model->checkEmailExists($_POST['email'])) {
                $errors['email'] = 'Email đã được sử dụng';
                return ['success' => false, 'errors' => $errors];
            }

            // Chuẩn bị dữ liệu
            $data = [
                'username'  => trim($_POST['username']),
                'fullname'  => trim($_POST['fullname']),
                'phone'     => trim($_POST['phone']),
                'address'   => isset($_POST['address']) ? trim($_POST['address']) : '',
                'email'     => isset($_POST['email']) ? trim($_POST['email']) : '',
                'cmnd'      => trim($_POST['cmnd'])
            ];

            // Đăng ký
            $result = $this->model->registerAccount($data);
            
            if ($result['success']) {
                return [
                    'success' => true, 
                    'message' => 'Đăng ký khách hàng thành công!', 
                    'data' => [
                        'maKH' => $result['maKH'],
                        'username' => $result['username'],
                        'password' => $result['password'] // Trả về mật khẩu mặc định
                    ]
                ];
            } else {
                return ['success' => false, 'errors' => ['system' => $result['error']]];
            }
        }
        
        return null;
    }

    // Validate đăng ký
    private function validateRegistrationData($data) {
        $errors = [];

        // Tên đăng nhập
        $username = trim($data['username'] ?? '');
        if (empty($username)) {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập';
        } elseif (strlen($username) < 4) {
            $errors['username'] = 'Tên đăng nhập phải có ít nhất 4 ký tự';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới';
        }

        // Họ và tên
        $fullname = trim($data['fullname'] ?? '');
        if (empty($fullname)) {
            $errors['fullname'] = 'Vui lòng nhập họ và tên đầy đủ';
        } elseif (strlen($fullname) < 6) {
            $errors['fullname'] = 'Họ và tên phải có ít nhất 6 ký tự';
        } elseif (preg_match('/[0-9]/', $fullname)) {
            $errors['fullname'] = 'Họ và tên không được chứa số';
        }

        // CMND/CCCD
        $cmnd = trim($data['cmnd'] ?? '');
        if (empty($cmnd)) {
            $errors['cmnd'] = 'Vui lòng nhập CMND/CCCD';
        } elseif (!preg_match('/^[0-9]{9,12}$/', $cmnd)) {
            $errors['cmnd'] = 'CMND/CCCD phải có 9-12 số';
        }

        // Số điện thoại
        $phone = trim($data['phone'] ?? '');
        if (empty($phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại phải có 10-11 số';
        }

        // Email
        $email = trim($data['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }

        return $errors;
    }

    // Lấy danh sách khách hàng
    public function getAllCustomers() {
        return $this->model->getAllCustomers();
    }

    // Lấy thông tin khách hàng theo MaKH
    public function getCustomer($maKH) {
        return $this->model->getCustomerByMaKH($maKH);
    }

    // Xử lý cập nhật
    public function handleUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
            $maKH = $_POST['maKH'] ?? '';
            
            if (empty($maKH)) {
                return ['success' => false, 'errors' => ['system' => 'Thiếu mã khách hàng']];
            }

            $errors = $this->validateUpdateData($_POST);
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Lấy thông tin hiện tại
            $currentData = $this->model->getCustomerByMaKH($maKH);
            if (!$currentData) {
                return ['success' => false, 'errors' => ['system' => 'Không tìm thấy khách hàng']];
            }

            $accountId = $currentData['account_id'];

            // Kiểm tra tên đăng nhập
            $newUsername = trim($_POST['username']);
            if ($newUsername !== $currentData['TenDangNhap'] && $this->model->checkUsernameExists($newUsername, $accountId)) {
                $errors['username'] = 'Tên đăng nhập đã được sử dụng bởi tài khoản khác';
                return ['success' => false, 'errors' => $errors];
            }

            // Kiểm tra CMND
            $newCMND = trim($_POST['cmnd']);
            if ($newCMND !== $currentData['CMND'] && $this->model->checkCMNDExists($newCMND, $accountId)) {
                $errors['cmnd'] = 'CMND/CCCD đã được đăng ký bởi tài khoản khác';
                return ['success' => false, 'errors' => $errors];
            }

            // Kiểm tra số điện thoại
            $newPhone = trim($_POST['phone']);
            if ($newPhone !== $currentData['SoDienThoai'] && $this->model->checkPhoneExists($newPhone, $maKH)) {
                $errors['phone'] = 'Số điện thoại đã được đăng ký bởi khách hàng khác';
                return ['success' => false, 'errors' => $errors];
            }

            // Kiểm tra email
            $newEmail = trim($_POST['email'] ?? '');
            if ($newEmail !== $currentData['Email'] && $this->model->checkEmailExists($newEmail, $accountId)) {
                $errors['email'] = 'Email đã được sử dụng bởi tài khoản khác';
                return ['success' => false, 'errors' => $errors];
            }

            // Chuẩn bị dữ liệu
            $data = [
                'username'  => $newUsername,
                'fullname'  => trim($_POST['fullname']),
                'phone'     => $newPhone,
                'address'   => isset($_POST['address']) ? trim($_POST['address']) : '',
                'email'     => $newEmail,
                'cmnd'      => $newCMND,
                'password'  => $_POST['password'] ?? '',
                'trangthai' => $_POST['trangthai'] ?? 'Không ở',
                'tai_khoan_trangthai' => isset($_POST['tai_khoan_trangthai']) ? 1 : 0
            ];

            // Cập nhật
            $result = $this->model->updateCustomer($maKH, $data);
            
            if ($result['success']) {
                return ['success' => true, 'message' => 'Cập nhật thông tin thành công!'];
            } else {
                return ['success' => false, 'errors' => ['system' => $result['error']]];
            }
        }
        
        return null;
    }

    // Validate cập nhật
    private function validateUpdateData($data) {
        $errors = [];

        // Tên đăng nhập
        $username = trim($data['username'] ?? '');
        if (empty($username)) {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập';
        } elseif (strlen($username) < 4) {
            $errors['username'] = 'Tên đăng nhập phải có ít nhất 4 ký tự';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới';
        }

        // Họ và tên
        $fullname = trim($data['fullname'] ?? '');
        if (empty($fullname)) {
            $errors['fullname'] = 'Vui lòng nhập họ và tên đầy đủ';
        } elseif (strlen($fullname) < 6) {
            $errors['fullname'] = 'Họ và tên phải có ít nhất 6 ký tự';
        } elseif (preg_match('/[0-9]/', $fullname)) {
            $errors['fullname'] = 'Họ và tên không được chứa số';
        }

        // CMND/CCCD
        $cmnd = trim($data['cmnd'] ?? '');
        if (empty($cmnd)) {
            $errors['cmnd'] = 'Vui lòng nhập CMND/CCCD';
        } elseif (!preg_match('/^[0-9]{9,12}$/', $cmnd)) {
            $errors['cmnd'] = 'CMND/CCCD phải có 9-12 số';
        }

        // Số điện thoại
        $phone = trim($data['phone'] ?? '');
        if (empty($phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại phải có 10-11 số';
        }

        // Email
        $email = trim($data['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }

        // Mật khẩu (chỉ validate nếu có nhập)
        $password = $data['password'] ?? '';
        if (!empty($password) && strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }

        // Nhập lại mật khẩu (chỉ validate nếu có nhập mật khẩu mới)
        if (!empty($password)) {
            $confirm_password = $data['confirm_password'] ?? '';
            if (empty($confirm_password)) {
                $errors['confirm_password'] = 'Vui lòng nhập lại mật khẩu';
            } elseif ($password !== $confirm_password) {
                $errors['confirm_password'] = 'Mật khẩu nhập lại không khớp';
            }
        }

        return $errors;
    }

    // Xử lý reset mật khẩu
    public function handleResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
            $maKH = $_POST['maKH'] ?? '';
            
            if (empty($maKH)) {
                return ['success' => false, 'message' => 'Thiếu mã khách hàng'];
            }

            $result = $this->model->resetPassword($maKH);
            
            if ($result['success']) {
                return ['success' => true, 'message' => 'Reset mật khẩu thành công! Mật khẩu mới: ' . $result['password']];
            } else {
                return ['success' => false, 'message' => $result['error']];
            }
        }
        
        return null;
    }

    // Xử lý xóa
    public function handleDelete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
            $maKH = $_POST['maKH'] ?? '';
            
            if (empty($maKH)) {
                return ['success' => false, 'message' => 'Thiếu mã khách hàng'];
            }

            $result = $this->model->deleteCustomer($maKH);
            
            if ($result['success']) {
                return ['success' => true, 'message' => 'Xóa khách hàng thành công!'];
            } else {
                return ['success' => false, 'message' => $result['error']];
            }
        }
        
        return null;
    }
}

// KHÔNG chạy xử lý ở đây nữa - chuyển xuống cuối file
?>