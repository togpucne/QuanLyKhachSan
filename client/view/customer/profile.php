<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_path = '/ABC-Resort';
$base_url = $protocol . '://' . $host . $project_path;

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/client/controller/user.controller.php?action=login");
    exit();
}
// Hiển thị lỗi đổi mật khẩu nếu có
if (isset($_SESSION['password_errors'])) {
    $password_errors = $_SESSION['password_errors'];
    unset($_SESSION['password_errors']);
}

// Hiển thị thông báo thành công đổi mật khẩu
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
include __DIR__ . '/../layouts/header.php';

$customerInfo = getCustomerInfo($_SESSION['user_id']);

$success = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // VALIDATE HỌ TÊN
    if (empty($fullname)) {
        $errors['fullname'] = "Họ tên không được để trống";
    } elseif (strlen($fullname) < 2) {
        $errors['fullname'] = "Họ tên phải có ít nhất 2 ký tự";
    } elseif (preg_match('/[0-9]/', $fullname)) {
        $errors['fullname'] = "Họ tên không được chứa số";
    } elseif (!preg_match('/^[a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂẾưăạảấầẩẫậắằẳẵặẹẻẽềềểếỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ\s\.]+$/u', $fullname)) {
        $errors['fullname'] = "Họ tên không được chứa ký tự đặc biệt";
    }

    // VALIDATE SỐ ĐIỆN THOẠI
    if (empty($phone)) {
        $errors['phone'] = "Số điện thoại không được để trống";
    } elseif (!preg_match('/^(03|05|07|08|09)[0-9]{8}$/', $phone)) {
        $errors['phone'] = "Số điện thoại phải bắt đầu bằng 03,05,07,08,09 và có 10 số";
    } elseif (isPhoneExists($phone, $_SESSION['user_id'])) {
        $errors['phone'] = "Số điện thoại này đã được sử dụng bởi tài khoản khác";
    }

    if (empty($errors)) {
        if (updateCustomerInfo($_SESSION['user_id'], $fullname, $phone, $address)) {
            $success = "Cập nhật thông tin thành công!";
            $customerInfo = getCustomerInfo($_SESSION['user_id']);
        } else {
            $errors['general'] = "Có lỗi xảy ra khi cập nhật thông tin";
        }
    }
}
function isPhoneExists($phone, $userId) {
    require_once __DIR__ . '/../../model/connectDB.php';
    try {
        $connect = new Connect();
        $conn = $connect->openConnect();

        $sql = "SELECT COUNT(*) as count FROM KhachHang 
                WHERE SoDienThoai = ? AND MaTaiKhoan != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $phone, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        $connect->closeConnect($conn);
        
        return $row['count'] > 0;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}
function updateCustomerInfo($userId, $fullname, $phone, $address)
{
    require_once __DIR__ . '/../../model/connectDB.php';
    try {
        $connect = new Connect();
        $conn = $connect->openConnect();

        // Kiểm tra trùng SĐT trước khi cập nhật
        $checkSql = "SELECT COUNT(*) as count FROM KhachHang 
                    WHERE SoDienThoai = ? AND MaTaiKhoan != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $phone, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkRow = $checkResult->fetch_assoc();
        
        if ($checkRow['count'] > 0) {
            $checkStmt->close();
            $connect->closeConnect($conn);
            return false; // SĐT đã tồn tại
        }
        $checkStmt->close();

        // Thực hiện cập nhật
        $sql = "UPDATE KhachHang 
                SET HoTen = ?, SoDienThoai = ?, DiaChi = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE MaTaiKhoan = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $fullname, $phone, $address, $userId);
        $result = $stmt->execute();

        $stmt->close();
        $connect->closeConnect($conn);
        return $result;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}
?>
<?php 
    require_once __DIR__ ."../../layouts/icon.php";

?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check me-2"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $errors['general']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Thông tin cá nhân -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin cá nhân</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($customerInfo)): ?>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control <?php echo isset($errors['fullname']) ? 'is-invalid' : ''; ?>"
                                            name="fullname" 
                                            value="<?php echo htmlspecialchars($customerInfo['HoTen']); ?>"
                                            required>
                                        <?php if (isset($errors['fullname'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['fullname']; ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Số điện thoại  <span class="text-danger">*</span></label>
                                        <input type="tel"
                                            class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                            name="phone"
                                            value="<?php echo htmlspecialchars($customerInfo['SoDienThoai']); ?>"
                                            required>
                                        <?php if (isset($errors['phone'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email  <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($customerInfo['Email']); ?>" readonly>
                                        <small class="text-muted">Email không thể thay đổi</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">CMND/CCCD  <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($customerInfo['CMND']); ?>" readonly>
                                        <small class="text-muted">CMND không thể thay đổi</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea class="form-control" name="address" rows="2" placeholder="Nhập địa chỉ của bạn"><?php echo htmlspecialchars($customerInfo['DiaChi'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo $base_url; ?>/client/index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Cập nhật
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Không tìm thấy thông tin khách hàng.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thông tin tài khoản -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Thông tin tài khoản</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Tên đăng nhập:</strong>
                                <div class="text-muted"><?php echo htmlspecialchars($customerInfo['TenDangNhap'] ?? 'Chưa có'); ?></div>
                            </div>
                            <div class="mb-2">
                                <strong>Mật khẩu:</strong>
                                <div class="text-muted">••••••••</div>
                                <small class="text-muted">Sử dụng form bên dưới để đổi mật khẩu</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Ngày tạo tài khoản:</strong>
                                <div class="text-muted"><?php echo date('d/m/Y H:i', strtotime($customerInfo['created_at'] ?? '')); ?></div>
                            </div>
                            <div class="mb-2">
                                <strong>Trạng thái:</strong>
                                <span class="badge bg-success">Hoạt động</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Đổi mật khẩu -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Đổi mật khẩu </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo $base_url; ?>/client/controller/user.controller.php?action=changePassword">
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại  <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?php echo isset($password_errors['current_password']) ? 'is-invalid' : ''; ?>"
                                name="current_password" required>
                            <?php if (isset($password_errors['current_password'])): ?>
                                <div class="invalid-feedback"><?php echo $password_errors['current_password']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới  <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?php echo isset($password_errors['new_password']) ? 'is-invalid' : ''; ?>"
                                name="new_password" required>
                            <?php if (isset($password_errors['new_password'])): ?>
                                <div class="invalid-feedback"><?php echo $password_errors['new_password']; ?></div>
                            <?php endif; ?>
                            <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nhập lại mật khẩu mới  <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?php echo isset($password_errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                                name="confirm_password" required>
                            <?php if (isset($password_errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?php echo $password_errors['confirm_password']; ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($password_errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $password_errors['general']; ?></div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i>Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>