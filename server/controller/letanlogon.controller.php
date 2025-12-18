<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'letan') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

require_once '../model/letanlogon.model.php';

$model = new LetanLogonModel();

// Xử lý action
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addKhachHang($model);
        break;
        
    case 'edit':
        editKhachHang($model);
        break;
        
    case 'get':
        getKhachHang($model);
        break;
        
    case 'delete':
        deleteKhachHang($model);
        break;
        
    case 'delete-multiple':
        deleteMultipleKhachHang($model);
        break;
        
    case 'check':
        checkDuplicates($model);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

function addKhachHang($model) {
    try {
        // Lấy dữ liệu từ form
        $data = [
            'hoten' => trim($_POST['hoten'] ?? ''),
            'sodienthoai' => trim($_POST['sodienthoai'] ?? ''),
            'diachi' => trim($_POST['diachi'] ?? ''),
            'trangthai' => trim($_POST['trangthai'] ?? 'Không ở'),
            'tendangnhap' => trim($_POST['tendangnhap'] ?? ''),
            'matkhau' => trim($_POST['matkhau'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'cmnd' => trim($_POST['cmnd'] ?? '')
        ];
        
        // Validate dữ liệu bắt buộc
        if (empty($data['hoten']) || empty($data['sodienthoai'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc']);
            return;
        }
        
        // Kiểm tra trùng số điện thoại
        if ($model->checkDuplicatePhone($data['sodienthoai'])) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại đã tồn tại']);
            return;
        }
        
        // Nếu có tạo tài khoản thì kiểm tra thêm
        if (!empty($data['tendangnhap']) && !empty($data['matkhau'])) {
            if ($model->checkDuplicateUsername($data['tendangnhap'])) {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại']);
                return;
            }
            
            if (!empty($data['email']) && $model->checkDuplicateEmail($data['email'])) {
                echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
                return;
            }
            
            if (!empty($data['cmnd']) && $model->checkDuplicateCMND($data['cmnd'])) {
                echo json_encode(['success' => false, 'message' => 'CMND đã tồn tại']);
                return;
            }
        }
        
        // Thêm khách hàng
        $result = $model->addKhachHang($data);
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function editKhachHang($model) {
    try {
        $maKH = trim($_POST['maKH'] ?? '');
        
        if (empty($maKH)) {
            echo json_encode(['success' => false, 'message' => 'Không có mã khách hàng']);
            return;
        }
        
        // Lấy dữ liệu từ form
        $data = [
            'hoten' => trim($_POST['hoten'] ?? ''),
            'sodienthoai' => trim($_POST['sodienthoai'] ?? ''),
            'diachi' => trim($_POST['diachi'] ?? ''),
            'trangthai' => trim($_POST['trangthai'] ?? 'Không ở'),
            'tendangnhap' => trim($_POST['tendangnhap'] ?? ''),
            'matkhau' => trim($_POST['matkhau'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'cmnd' => trim($_POST['cmnd'] ?? '')
        ];
        
        // Validate dữ liệu bắt buộc
        if (empty($data['hoten']) || empty($data['sodienthoai'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc']);
            return;
        }
        
        // Lấy thông tin khách hàng cũ để check duplicate
        $khachHangCu = $model->getKhachHangByMaKH($maKH);
        if (!$khachHangCu) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
            return;
        }
        
        // Kiểm tra trùng số điện thoại (trừ chính nó)
        if ($model->checkDuplicatePhone($data['sodienthoai'], $maKH)) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại đã tồn tại cho khách hàng khác']);
            return;
        }
        
        // Kiểm tra trùng tên đăng nhập/email/cmnd nếu có
        $maTaiKhoanCu = $khachHangCu['MaTaiKhoan'];
        
        if (!empty($data['tendangnhap'])) {
            if ($model->checkDuplicateUsername($data['tendangnhap'], $maTaiKhoanCu)) {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại']);
                return;
            }
        }
        
        if (!empty($data['email'])) {
            if ($model->checkDuplicateEmail($data['email'], $maTaiKhoanCu)) {
                echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
                return;
            }
        }
        
        if (!empty($data['cmnd'])) {
            if ($model->checkDuplicateCMND($data['cmnd'], $maTaiKhoanCu)) {
                echo json_encode(['success' => false, 'message' => 'CMND đã tồn tại']);
                return;
            }
        }
        
        // Cập nhật khách hàng
        $result = $model->updateKhachHang($maKH, $data);
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function getKhachHang($model) {
    try {
        $maKH = trim($_GET['maKH'] ?? '');
        
        if (empty($maKH)) {
            echo json_encode(['success' => false, 'message' => 'Không có mã khách hàng']);
            return;
        }
        
        $khachhang = $model->getKhachHangByMaKH($maKH);
        
        if ($khachhang) {
            echo json_encode(['success' => true, 'data' => $khachhang]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function deleteKhachHang($model) {
    try {
        $maKH = trim($_POST['maKH'] ?? '');
        
        if (empty($maKH)) {
            echo json_encode(['success' => false, 'message' => 'Không có mã khách hàng']);
            return;
        }
        
        $result = $model->deleteKhachHang($maKH);
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function deleteMultipleKhachHang($model) {
    try {
        $listMaKH = $_POST['listMaKH'] ?? [];
        
        if (empty($listMaKH) || !is_array($listMaKH)) {
            echo json_encode(['success' => false, 'message' => 'Không có danh sách khách hàng để xóa']);
            return;
        }
        
        // Chỉ lấy tối đa 100 bản ghi để tránh quá tải
        $listMaKH = array_slice($listMaKH, 0, 100);
        
        $result = $model->deleteMultipleKhachHang($listMaKH);
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function checkDuplicates($model) {
    $type = $_POST['type'] ?? '';
    $value = $_POST['value'] ?? '';
    $currentId = $_POST['currentId'] ?? null;
    
    if (empty($type) || empty($value)) {
        echo json_encode(['exists' => false]);
        return;
    }
    
    $exists = false;
    switch ($type) {
        case 'phone':
            $exists = $model->checkDuplicatePhone($value, $currentId);
            break;
        case 'username':
            $exists = $model->checkDuplicateUsername($value, $currentId);
            break;
        case 'email':
            $exists = $model->checkDuplicateEmail($value, $currentId);
            break;
        case 'cmnd':
            $exists = $model->checkDuplicateCMND($value, $currentId);
            break;
    }
    
    echo json_encode(['exists' => $exists]);
}
?>