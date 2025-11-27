<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header py-3 border-0 text-center" 
                     style="background: linear-gradient(90deg, #37353E, #435663); color: #fff;">
                    <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>ĐĂNG NHẬP</h3>
                </div>
                
                <div class="card-body p-4">
                    <!-- Hiển thị thông báo thành công từ đăng ký -->
                    <?php if (isset($_SESSION['register_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['register_success']; ?>
                            <?php unset($_SESSION['register_success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Hiển thị lỗi -->
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php 
                            if (is_array($errors)) {
                                foreach($errors as $error) {
                                    if (is_string($error)) {
                                        echo $error . '<br>';
                                    }
                                }
                            } else {
                                echo $errors;
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="../controller/user.controller.php?action=doLogin">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" 
                                   class="form-control rounded-3 <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   id="email" 
                                   name="email"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="Nhập email của bạn">
                        </div>
                        
                        <!-- Mật khẩu -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Mật khẩu
                            </label>
                            <input type="password" 
                                   class="form-control rounded-3 <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                   id="password" 
                                   name="password"
                                   placeholder="Nhập mật khẩu">
                        </div>
                        
                        <!-- Nút đăng nhập -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn text-white py-2 rounded-3 fw-semibold"
                                    style="background: linear-gradient(90deg, #37353E, #435663);">
                                <i class="fas fa-sign-in-alt me-2"></i>ĐĂNG NHẬP
                            </button>
                        </div>
                    </form>
                    
                    <!-- Link đăng ký -->
                    <div class="text-center">
                        <p class="mb-0">Chưa có tài khoản? 
                            <a href="../controller/user.controller.php?action=register" class="text-decoration-none fw-semibold" style="color: #37353E;">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký ngay
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>