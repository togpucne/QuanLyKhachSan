<?php
session_start();
// Tạo captcha ngẫu nhiên
$captcha = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
$_SESSION['captcha'] = $captcha;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống ABC Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .captcha-text {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Đăng nhập</h2>
            <p class="mb-0">Hệ thống quản lý khách sạn ABC Resort Nha Trang</p>
        </div>
        
        <div class="login-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Sửa action cho chính xác -->
            <form action="../../controller/login.controller.php?action=processLogin" method="POST">
                <div class="mb-3">
                    <label class="form-label">Tên tài khoản</label>
                    <input type="text" class="form-control" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mã xác thực</label>
                    <div class="captcha-text"><?php echo $captcha; ?></div>
                    <input type="text" class="form-control" name="captcha" placeholder="Nhập mã xác thực" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                
                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none">Quên mật khẩu?</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>