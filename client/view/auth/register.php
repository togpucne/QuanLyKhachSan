<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header py-3 border-0 text-center" 
                     style="background: linear-gradient(90deg, #37353E, #435663); color: #fff;">
                    <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>ĐĂNG KÝ TÀI KHOẢN</h3>
                </div>
                
                <div class="card-body p-4">
                    <!-- HIỂN THỊ LỖI TỔNG -->
                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $errors['general']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="../controller/user.controller.php?action=doRegister">
                        <!-- Họ tên -->
                        <div class="mb-3">
                            <label for="fullname" class="form-label fw-semibold">
                                <i class="fas fa-user me-2"></i>Họ và tên
                            </label>
                            <input type="text" 
                                   class="form-control rounded-3 <?php echo isset($errors['fullname']) ? 'is-invalid' : ''; ?>" 
                                   id="fullname" 
                                   name="fullname"
                                   value="<?php echo isset($oldInput['fullname']) ? htmlspecialchars($oldInput['fullname']) : ''; ?>"
                                   placeholder="Nhập họ và tên đầy đủ (không chứa số)">
                            <?php if (isset($errors['fullname'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['fullname']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- CMND -->
                        <div class="mb-3">
                            <label for="cmnd" class="form-label fw-semibold">
                                <i class="fas fa-id-card me-2"></i>CMND/CCCD
                            </label>
                            <input type="text" 
                                   class="form-control rounded-3 <?php echo isset($errors['cmnd']) ? 'is-invalid' : ''; ?>" 
                                   id="cmnd" 
                                   name="cmnd"
                                   value="<?php echo isset($oldInput['cmnd']) ? htmlspecialchars($oldInput['cmnd']) : ''; ?>"
                                   placeholder="Nhập số CMND/CCCD (9-12 số)">
                            <?php if (isset($errors['cmnd'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['cmnd']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" 
                                   class="form-control rounded-3 <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   id="email" 
                                   name="email"
                                   value="<?php echo isset($oldInput['email']) ? htmlspecialchars($oldInput['email']) : ''; ?>"
                                   placeholder="Nhập email của bạn">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Mật khẩu -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Mật khẩu
                            </label>
                            <input type="password" 
                                   class="form-control rounded-3 <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                   id="password" 
                                   name="password"
                                   placeholder="Nhập mật khẩu (ít nhất 6 ký tự)">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Nhập lại mật khẩu -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Nhập lại mật khẩu
                            </label>
                            <input type="password" 
                                   class="form-control rounded-3 <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                   id="confirm_password" 
                                   name="confirm_password"
                                   placeholder="Nhập lại mật khẩu">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['confirm_password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Nút đăng ký -->
                        <div class="d-grid">
                            <button type="submit" class="btn text-white py-2 rounded-3 fw-semibold"
                                    style="background: linear-gradient(90deg, #37353E, #435663);">
                                <i class="fas fa-user-plus me-2"></i>ĐĂNG KÝ NGAY
                            </button>
                        </div>
                    </form>
                    
                    <!-- Link đăng nhập -->
                    <div class="text-center mt-4">
                        <p class="mb-0">Đã có tài khoản? 
                            <a href="../controller/user.controller.php?action=login" class="text-decoration-none fw-semibold" style="color: #37353E;">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập ngay
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>