<?php
session_start();
// Hiển thị thông báo đăng xuất thành công
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $logout_message = "Đăng xuất thành công!";
}
// Tạo captcha ngẫu nhiên
$captcha = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
$_SESSION['captcha'] = $captcha;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Tỏa Sáng Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../assets/images/logo/logo_toasang-removebg.png">

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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
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

        .login-note {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Đăng nhập hệ thống</h2>
            <p class="mb-0">Hệ thống quản lý Tỏa Sáng Resort Nha Trang</p>
        </div>

        <div class="login-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="login-note">
                <strong>📧 Lưu ý:</strong> Vui lòng sử dụng email nhân viên để đăng nhập
            </div>

            <!-- Thêm JavaScript alert cho thông báo đăng xuất -->
            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                <script>
                    setTimeout(function() {
                        alert('🚪 Đăng xuất thành công!');
                    }, 100);
                </script>
            <?php endif; ?>

            <form action="../../controller/login.controller.php?action=processLogin" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email nhân viên</label>
                    <input type="email" class="form-control" name="email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        placeholder="Nhập email của bạn">
                    <div class="form-text">Ví dụ: letan@talkhoan.com</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" name="password" required
                        placeholder="Nhập mật khẩu">
                </div>

                <div class="mb-3">
                    <label class="form-label">Mã xác thực</label>
                    <div class="captcha-text"><?php echo $captcha; ?></div>
                    <input type="text" class="form-control" name="captcha" placeholder="Nhập mã xác thực" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>

                <div class="text-center mt-3">
                    <small class="text-muted">Liên hệ quản trị viên nếu quên mật khẩu</small>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>