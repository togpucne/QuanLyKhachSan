<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// XỬ LÝ POST TRƯỚC KHI CÓ BẤT KỲ OUTPUT NÀO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Start output buffering để tránh lỗi header
    ob_start();
    
    require_once __DIR__ . '/../../controller/letanlogon.controller.php';
    
    $controller = new LetanLogonController();
    $redirect = false;
    $redirectUrl = '';
    
    switch ($_POST['action']) {
        case 'register':
            $actionResult = $controller->handleRegister();
            break;
        case 'update':
            $actionResult = $controller->handleUpdate();
            break;
        case 'reset_password':
            $actionResult = $controller->handleResetPassword();
            // Sau khi reset password thành công, chuyển hướng
            if ($actionResult && $actionResult['success']) {
                $redirect = true;
                $redirectUrl = 'letanlogon.php?reset_success=' . urlencode($actionResult['message']);
            }
            break;
        case 'delete':
            $actionResult = $controller->handleDelete();
            // Sau khi xóa thành công, chuyển hướng về trang danh sách
            if ($actionResult && $actionResult['success']) {
                $redirect = true;
                $redirectUrl = 'letanlogon.php?success=' . urlencode($actionResult['message']);
            }
            break;
    }
    
    // Nếu cần redirect, thực hiện ngay
    if ($redirect) {
        ob_end_clean(); // Xóa buffer
        header('Location: ' . $redirectUrl);
        exit();
    } else {
        // Lưu kết quả vào session để hiển thị
        if (isset($actionResult)) {
            
            $_SESSION['action_result'] = $actionResult;
            // Nếu có lỗi, lưu cả POST data để hiển thị lại
            if (!$actionResult['success']) {
                $_SESSION['post_data'] = $_POST;
            }
        }
        ob_end_flush();
    }
}

// BẮT ĐẦU OUTPUT
require_once '../layouts/header.php';

// Lấy thông báo từ session

$actionResult = isset($_SESSION['action_result']) ? $_SESSION['action_result'] : null;
$postData = isset($_SESSION['post_data']) ? $_SESSION['post_data'] : [];

// Xóa session sau khi lấy
unset($_SESSION['action_result']);
unset($_SESSION['post_data']);

// Lấy thông báo thành công từ URL
$successMessage = isset($_GET['success']) ? urldecode($_GET['success']) : null;
$resetSuccessMessage = isset($_GET['reset_success']) ? urldecode($_GET['reset_success']) : null;

// Khởi tạo controller để lấy dữ liệu
require_once __DIR__ . '/../../controller/letanlogon.controller.php';
$controller = new LetanLogonController();

// Lấy danh sách khách hàng
$customers = $controller->getAllCustomers();

// Lấy thông tin khách hàng để chỉnh sửa (nếu có)
$editData = null;
if (isset($_GET['edit'])) {
    $editData = $controller->getCustomer($_GET['edit']);
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>
            QUẢN LÝ KHÁCH HÀNG
        </h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-plus-circle me-2"></i>Thêm Khách Hàng
        </button>
    </div>

    <!-- Hiển thị thông báo -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($resetSuccessMessage): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-key me-2"></i>
            <?php echo htmlspecialchars($resetSuccessMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($actionResult && !$actionResult['success']): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php 
            if (isset($actionResult['errors']['system'])) {
                echo htmlspecialchars($actionResult['errors']['system']);
            } else {
                echo 'Vui lòng kiểm tra lại thông tin!';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Thông tin thành công -->
    <?php if ($actionResult && $actionResult['success'] && isset($actionResult['data'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($actionResult['message']); ?>
            <div class="mt-2">
                <small class="text-muted">Mã khách hàng: <strong><?php echo htmlspecialchars($actionResult['data']['maKH']); ?></strong></small><br>
                <small class="text-muted">Tên đăng nhập: <strong><?php echo htmlspecialchars($actionResult['data']['username']); ?></strong></small><br>
                <small class="text-muted">Mật khẩu mặc định: <strong><?php echo htmlspecialchars($actionResult['data']['password']); ?></strong></small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Modal Thêm Khách Hàng -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">
                        <i class="fas fa-user-plus me-2"></i>ĐĂNG KÝ TÀI KHOẢN
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="registrationForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Tên đăng nhập -->
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-1 text-primary"></i>
                                        Tên đăng nhập <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($actionResult['errors']['username']) ? 'is-invalid' : ''; ?>"
                                           id="username" 
                                           name="username" 
                                           value="<?php echo isset($postData['username']) ? htmlspecialchars($postData['username']) : ''; ?>"
                                           placeholder="Nhập tên đăng nhập"
                                           required>
                                    <?php if (isset($actionResult['errors']['username'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['username']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-text">Tối thiểu 4 ký tự (chữ, số, _)</div>
                                </div>

                                <!-- Họ và tên -->
                                <div class="mb-3">
                                    <label for="fullname" class="form-label">
                                        <i class="fas fa-id-card me-1 text-primary"></i>
                                        Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($actionResult['errors']['fullname']) ? 'is-invalid' : ''; ?>"
                                           id="fullname" 
                                           name="fullname" 
                                           value="<?php echo isset($postData['fullname']) ? htmlspecialchars($postData['fullname']) : ''; ?>"
                                           placeholder="Nhập họ và tên đầy đủ"
                                           required>
                                    <?php if (isset($actionResult['errors']['fullname'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['fullname']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-text">Nhập họ và tên đầy đủ (không chứa số)</div>
                                </div>

                                <!-- CMND/CCCD -->
                                <div class="mb-3">
                                    <label for="cmnd" class="form-label">
                                        <i class="fas fa-id-card me-1 text-primary"></i>
                                        CMND/CCCD <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($actionResult['errors']['cmnd']) ? 'is-invalid' : ''; ?>"
                                           id="cmnd" 
                                           name="cmnd" 
                                           value="<?php echo isset($postData['cmnd']) ? htmlspecialchars($postData['cmnd']) : ''; ?>"
                                           placeholder="Nhập 9-12 số"
                                           required>
                                    <?php if (isset($actionResult['errors']['cmnd'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['cmnd']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-text">Nhập số CMND/CCCD (9-12 số)</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Số điện thoại -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1 text-primary"></i>
                                        Số điện thoại <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" 
                                           class="form-control <?php echo isset($actionResult['errors']['phone']) ? 'is-invalid' : ''; ?>"
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo isset($postData['phone']) ? htmlspecialchars($postData['phone']) : ''; ?>"
                                           placeholder="Nhập 10-11 số"
                                           required>
                                    <?php if (isset($actionResult['errors']['phone'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-text">Nhập số điện thoại (10-11 số)</div>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1 text-primary"></i>
                                        Email
                                    </label>
                                    <input type="email" 
                                           class="form-control <?php echo isset($actionResult['errors']['email']) ? 'is-invalid' : ''; ?>"
                                           id="email" 
                                           name="email" 
                                           value="<?php echo isset($postData['email']) ? htmlspecialchars($postData['email']) : ''; ?>"
                                           placeholder="Nhập email của bạn">
                                    <?php if (isset($actionResult['errors']['email'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['email']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-text">Nhập email của bạn</div>
                                </div>

                                <!-- Thông báo mật khẩu mặc định -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Lưu ý:</strong> Mật khẩu mặc định sẽ là <strong>123456</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Địa chỉ -->
                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-home me-1 text-primary"></i>
                                Địa chỉ
                            </label>
                            <textarea class="form-control" 
                                      id="address" 
                                      name="address" 
                                      rows="2"
                                      placeholder="Nhập địa chỉ (nếu có)"><?php echo isset($postData['address']) ? htmlspecialchars($postData['address']) : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Đăng ký
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa Khách Hàng -->
    <?php if ($editData): ?>
    <div class="modal fade show" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="false" style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">
                        <i class="fas fa-edit me-2"></i>CHỈNH SỬA THÔNG TIN KHÁCH HÀNG: <?php echo htmlspecialchars($editData['MaKH']); ?>
                    </h5>
                    <a href="letanlogon.php" class="btn-close"></a>
                </div>
                <form method="POST" action="" id="updateForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="maKH" value="<?php echo htmlspecialchars($editData['MaKH']); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Tên đăng nhập -->
                                <div class="mb-3">
                                    <label for="edit_username" class="form-label">
                                        <i class="fas fa-user me-1 text-primary"></i>
                                        Tên đăng nhập <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($actionResult['errors']['username']) ? 'is-invalid' : ''; ?>"
                                           id="edit_username" 
                                           name="username" 
                                           value="<?php echo htmlspecialchars($editData['TenDangNhap']); ?>"
                                           required>
                                    <?php if (isset($actionResult['errors']['username'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['username']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Họ và tên -->
                                <div class="mb-3">
                                    <label for="edit_fullname" class="form-label">
                                        <i class="fas fa-id-card me-1 text-primary"></i>
                                        Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($actionResult['errors']['fullname']) ? 'is-invalid' : ''; ?>"
                                           id="edit_fullname" 
                                           name="fullname" 
                                           value="<?php echo htmlspecialchars($editData['HoTen']); ?>"
                                           required>
                                    <?php if (isset($actionResult['errors']['fullname'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['fullname']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- CMND/CCCD -->
                                <div class="mb-3">
                                    <label for="edit_cmnd" class="form-label">
                                        <i class="fas fa-id-card me-1 text-primary"></i>
                                        CMND/CCCD <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($actionResult['errors']['cmnd']) ? 'is-invalid' : ''; ?>"
                                           id="edit_cmnd" 
                                           name="cmnd" 
                                           value="<?php echo htmlspecialchars($editData['CMND']); ?>"
                                           required>
                                    <?php if (isset($actionResult['errors']['cmnd'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['cmnd']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Số điện thoại -->
                                <div class="mb-3">
                                    <label for="edit_phone" class="form-label">
                                        <i class="fas fa-phone me-1 text-primary"></i>
                                        Số điện thoại <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" 
                                           class="form-control <?php echo isset($actionResult['errors']['phone']) ? 'is-invalid' : ''; ?>"
                                           id="edit_phone" 
                                           name="phone" 
                                           value="<?php echo htmlspecialchars($editData['SoDienThoai']); ?>"
                                           required>
                                    <?php if (isset($actionResult['errors']['phone'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">
                                        <i class="fas fa-envelope me-1 text-primary"></i>
                                        Email
                                    </label>
                                    <input type="email" 
                                           class="form-control <?php echo isset($actionResult['errors']['email']) ? 'is-invalid' : ''; ?>"
                                           id="edit_email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($editData['Email']); ?>">
                                    <?php if (isset($actionResult['errors']['email'])): ?>
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            <?php echo htmlspecialchars($actionResult['errors']['email']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Trạng thái tài khoản -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-power-off me-1 text-primary"></i>
                                        Trạng thái tài khoản
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="tai_khoan_trangthai" name="tai_khoan_trangthai" 
                                               value="1" <?php echo $editData['tai_khoan_trangthai'] == 1 ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tai_khoan_trangthai">
                                            Tài khoản đang hoạt động
                                        </label>
                                    </div>
                                </div>

                                <!-- Trạng thái khách hàng -->
                                <div class="mb-3">
                                    <label for="edit_trangthai" class="form-label">
                                        <i class="fas fa-bed me-1 text-primary"></i>
                                        Trạng thái lưu trú
                                    </label>
                                    <select class="form-select" id="edit_trangthai" name="trangthai">
                                        <option value="Không ở" <?php echo $editData['TrangThai'] == 'Không ở' ? 'selected' : ''; ?>>Không ở</option>
                                        <option value="Đang ở" <?php echo $editData['TrangThai'] == 'Đang ở' ? 'selected' : ''; ?>>Đang ở</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Địa chỉ -->
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">
                                <i class="fas fa-home me-1 text-primary"></i>
                                Địa chỉ
                            </label>
                            <textarea class="form-control" 
                                      id="edit_address" 
                                      name="address" 
                                      rows="2"><?php echo htmlspecialchars($editData['DiaChi']); ?></textarea>
                        </div>

                        <!-- Mật khẩu mới (chỉ khi cần thay đổi) -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-key me-1"></i>Thay đổi mật khẩu
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Lưu ý:</strong> Để trống nếu không muốn thay đổi mật khẩu
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_password" class="form-label">Mật khẩu mới</label>
                                            <input type="password" 
                                                   class="form-control <?php echo isset($actionResult['errors']['password']) ? 'is-invalid' : ''; ?>"
                                                   id="edit_password" 
                                                   name="password" 
                                                   placeholder="Nhập mật khẩu (ít nhất 6 ký tự)">
                                            <?php if (isset($actionResult['errors']['password'])): ?>
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-circle me-1"></i>
                                                    <?php echo htmlspecialchars($actionResult['errors']['password']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_confirm_password" class="form-label">Nhập lại mật khẩu</label>
                                            <input type="password" 
                                                   class="form-control <?php echo isset($actionResult['errors']['confirm_password']) ? 'is-invalid' : ''; ?>"
                                                   id="edit_confirm_password" 
                                                   name="confirm_password" 
                                                   placeholder="Nhập lại mật khẩu">
                                            <?php if (isset($actionResult['errors']['confirm_password'])): ?>
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-circle me-1"></i>
                                                    <?php echo htmlspecialchars($actionResult['errors']['confirm_password']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-info" onclick="confirmResetPassword('<?php echo htmlspecialchars($editData['MaKH']); ?>')">
                            <i class="fas fa-redo me-2"></i>Reset Mật Khẩu
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo htmlspecialchars($editData['MaKH']); ?>')">
                            <i class="fas fa-trash me-2"></i>Xóa
                        </button>
                        <a href="letanlogon.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Danh sách khách hàng -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-1"></i>DANH SÁCH KHÁCH HÀNG
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($customers)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã KH</th>
                                <th>Tên đăng nhập</th>
                                <th>Họ và tên</th>
                                <th>Số điện thoại</th>
                                <th>CMND/CCCD</th>
                                <th>Email</th>
                                <th>Trạng thái TK</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($customer['MaKH']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($customer['TenDangNhap']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['HoTen']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['SoDienThoai']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['CMND']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['Email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $customer['tai_khoan_trangthai'] == 1 ? 'success' : 'danger'; ?>">
                                            <?php echo $customer['tai_khoan_trangthai'] == 1 ? 'Hoạt động' : 'Vô hiệu'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $customer['TrangThai'] == 'Đang ở' ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($customer['TrangThai']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?></td>
                                    <td>
                                        <a href="letanlogon.php?edit=<?php echo urlencode($customer['MaKH']); ?>" 
                                           class="btn btn-sm btn-warning mb-1">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-info mb-1" 
                                                onclick="confirmResetPassword('<?php echo htmlspecialchars($customer['MaKH']); ?>')">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có khách hàng nào</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fas fa-plus-circle me-2"></i>Thêm Khách Hàng Đầu Tiên
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Form reset password ẩn -->
<form method="POST" action="" id="resetPasswordForm">
    <input type="hidden" name="action" value="reset_password">
    <input type="hidden" name="maKH" id="resetMaKH">
</form>

<!-- Form xóa ẩn -->
<form method="POST" action="" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="maKH" id="deleteMaKH">
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Xác nhận xóa
function confirmDelete(maKH) {
    console.log('Xóa khách hàng:', maKH);
    Swal.fire({
        title: 'Xác nhận xóa',
        text: 'Bạn có chắc chắn muốn xóa khách hàng ' + maKH + '?\nThao tác này sẽ xóa cả tài khoản và không thể hoàn tác!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Đã xác nhận xóa:', maKH);
            document.getElementById('deleteMaKH').value = maKH;
            document.getElementById('deleteForm').submit();
        }
    });
}

// Xác nhận reset password
function confirmResetPassword(maKH) {
    console.log('Reset password khách hàng:', maKH);
    Swal.fire({
        title: 'Reset mật khẩu',
        text: 'Bạn có chắc chắn muốn reset mật khẩu của khách hàng ' + maKH + ' về 123456?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Reset',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Đã xác nhận reset:', maKH);
            document.getElementById('resetMaKH').value = maKH;
            document.getElementById('resetPasswordForm').submit();
        }
    });
}

// Validation client-side
document.addEventListener('DOMContentLoaded', function() {
    // Form đăng ký
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            if (!validateRegistrationForm()) {
                e.preventDefault();
            }
        });
    }

    // Form cập nhật
    const updateForm = document.getElementById('updateForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            if (!validateUpdateForm()) {
                e.preventDefault();
            }
        });
    }

    function validateRegistrationForm() {
        let isValid = true;
        
        // Validate tên đăng nhập
        const username = document.getElementById('username');
        if (username && username.value) {
            if (username.value.length < 4) {
                showError(username, 'Tên đăng nhập phải có ít nhất 4 ký tự');
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
                showError(username, 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới');
                isValid = false;
            }
        }

        // Validate họ tên
        const fullname = document.getElementById('fullname');
        if (fullname && fullname.value) {
            if (fullname.value.length < 6) {
                showError(fullname, 'Họ và tên phải có ít nhất 6 ký tự');
                isValid = false;
            } else if (/\d/.test(fullname.value)) {
                showError(fullname, 'Họ và tên không được chứa số');
                isValid = false;
            }
        }

        // Validate CMND
        const cmnd = document.getElementById('cmnd');
        if (cmnd && cmnd.value && !/^\d{9,12}$/.test(cmnd.value)) {
            showError(cmnd, 'CMND/CCCD phải có 9-12 số');
            isValid = false;
        }

        // Validate số điện thoại
        const phone = document.getElementById('phone');
        if (phone && phone.value && !/^\d{10,11}$/.test(phone.value)) {
            showError(phone, 'Số điện thoại phải có 10-11 số');
            isValid = false;
        }

        // Validate email
        const email = document.getElementById('email');
        if (email && email.value && !isValidEmail(email.value)) {
            showError(email, 'Email không hợp lệ');
            isValid = false;
        }

        return isValid;
    }

    function validateUpdateForm() {
        let isValid = true;
        
        // Validate tên đăng nhập
        const username = document.getElementById('edit_username');
        if (username && username.value) {
            if (username.value.length < 4) {
                showError(username, 'Tên đăng nhập phải có ít nhất 4 ký tự');
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
                showError(username, 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới');
                isValid = false;
            }
        }

        // Validate họ tên
        const fullname = document.getElementById('edit_fullname');
        if (fullname && fullname.value) {
            if (fullname.value.length < 6) {
                showError(fullname, 'Họ và tên phải có ít nhất 6 ký tự');
                isValid = false;
            } else if (/\d/.test(fullname.value)) {
                showError(fullname, 'Họ và tên không được chứa số');
                isValid = false;
            }
        }

        // Validate CMND
        const cmnd = document.getElementById('edit_cmnd');
        if (cmnd && cmnd.value && !/^\d{9,12}$/.test(cmnd.value)) {
            showError(cmnd, 'CMND/CCCD phải có 9-12 số');
            isValid = false;
        }

        // Validate số điện thoại
        const phone = document.getElementById('edit_phone');
        if (phone && phone.value && !/^\d{10,11}$/.test(phone.value)) {
            showError(phone, 'Số điện thoại phải có 10-11 số');
            isValid = false;
        }

        // Validate email
        const email = document.getElementById('edit_email');
        if (email && email.value && !isValidEmail(email.value)) {
            showError(email, 'Email không hợp lệ');
            isValid = false;
        }

        // Validate password match
        const password = document.getElementById('edit_password');
        const confirmPassword = document.getElementById('edit_confirm_password');
        
        if (password && confirmPassword) {
            const hasPasswordValue = password.value.trim() !== '';
            
            if (hasPasswordValue && password.value !== confirmPassword.value) {
                showError(confirmPassword, 'Mật khẩu nhập lại không khớp');
                isValid = false;
            }
            
            if (hasPasswordValue && password.value.length < 6) {
                showError(password, 'Mật khẩu phải có ít nhất 6 ký tự');
                isValid = false;
            }
        }

        return isValid;
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function showError(input, message) {
        input.classList.add('is-invalid');
        let feedback = input.nextElementSibling;
        while (feedback && !feedback.classList.contains('invalid-feedback')) {
            feedback = feedback.nextElementSibling;
        }
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.insertBefore(feedback, input.nextSibling);
        }
        feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + message;
        feedback.style.display = 'block';
    }

    // Clear validation on input
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            let feedback = this.nextElementSibling;
            while (feedback && !feedback.classList.contains('invalid-feedback')) {
                feedback = feedback.nextElementSibling;
            }
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.style.display = 'none';
            }
        });
    });
});
</script>
<?php 
require_once '../layouts/footer.php';
?>