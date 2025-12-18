<?php
session_start();

// Kiểm tra đăng nhập theo hệ thống của bạn
if (!isset($_SESSION['user']) || !isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'letan') {
    header('Location: ../../../client/view/login.php?error=Vui lòng đăng nhập với vai trò lễ tân');
    exit;
}

// Lấy thông tin user
$user = $_SESSION['user'];
$role = $_SESSION['vaitro'];

// Kết nối database và lấy danh sách khách hàng
require_once '../../model/connectDB.php';

$connect = new Connect();
$conn = $connect->openConnect();

$query = "SELECT 
            kh.MaKH,
            kh.HoTen,
            kh.SoDienThoai,
            kh.DiaChi,
            kh.TrangThai,
            kh.created_at,
            kh.updated_at,
            kh.MaTaiKhoan,
            tk.Email,
            tk.CMND
          FROM khachhang kh
          LEFT JOIN tai_khoan tk ON kh.MaTaiKhoan = tk.id
          ORDER BY kh.created_at DESC";

$result = mysqli_query($conn, $query);
$dsKhachHang = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dsKhachHang[] = $row;
    }
}

$connect->closeConnect($conn);

require_once '../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quản lý khách hàng</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-danger" id="btnDeleteMultiple" disabled>
                            <i class="fas fa-trash-alt"></i> Xóa đã chọn
                        </button>
                    </div>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                    </button>
                </div>

            </div>


            <!-- Thống kê nhanh -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Tổng khách hàng</h6>
                            <h3 class="card-text"><?php echo count($dsKhachHang); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Có tài khoản</h6>
                            <h3 class="card-text">
                                <?php
                                $coTaiKhoan = 0;
                                foreach ($dsKhachHang as $kh) {
                                    if ($kh['MaTaiKhoan'] != 0) $coTaiKhoan++;
                                }
                                echo $coTaiKhoan;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title">Đang ở</h6>
                            <h3 class="card-text">
                                <?php
                                $dangO = 0;
                                foreach ($dsKhachHang as $kh) {
                                    if ($kh['TrangThai'] == 'Đang ở') $dangO++;
                                }
                                echo $dangO;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Không ở</h6>
                            <h3 class="card-text">
                                <?php
                                $khongO = 0;
                                foreach ($dsKhachHang as $kh) {
                                    if ($kh['TrangThai'] == 'Không ở') $khongO++;
                                }
                                echo $khongO;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách khách hàng -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Danh sách khách hàng</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <!-- Trong phần thead -->
                            <thead class="table-dark">
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th width="50">STT</th>
                                    <th>Mã KH</th>
                                    <th>Họ tên</th>
                                    <th>SĐT</th>
                                    <th>Email/CMND</th>
                                    <th>Địa chỉ</th>
                                    <th>Trạng thái</th>
                                    <th width="120">Tài khoản</th>
                                    <th width="200">Hành động</th>
                                </tr>
                            </thead>

                            <!-- Trong phần tbody -->
                            <tbody>
                                <?php if (empty($dsKhachHang)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-3">
                                            <i class="fas fa-users-slash fa-2x mb-2"></i><br>
                                            Chưa có khách hàng nào
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $stt = 1; ?>
                                    <?php foreach ($dsKhachHang as $kh): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="select-customer" value="<?php echo htmlspecialchars($kh['MaKH']); ?>">
                                            </td>
                                            <td class="text-center"><?php echo $stt++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($kh['MaKH']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($kh['HoTen']); ?></td>
                                            <td><?php echo htmlspecialchars($kh['SoDienThoai']); ?></td>
                                            <td>
                                                <?php if ($kh['Email']): ?>
                                                    <div><small class="text-primary"><?php echo htmlspecialchars($kh['Email']); ?></small></div>
                                                <?php endif; ?>
                                                <?php if ($kh['CMND']): ?>
                                                    <div><small class="text-muted">CMND: <?php echo htmlspecialchars($kh['CMND']); ?></small></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(mb_strlen($kh['DiaChi']) > 30 ? mb_substr($kh['DiaChi'], 0, 30) . '...' : $kh['DiaChi']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($kh['TrangThai'] == 'Đang ở'): ?>
                                                    <span class="badge bg-success">Đang ở</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Không ở</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($kh['MaTaiKhoan'] != 0): ?>
                                                    <span class="badge bg-success" title="Đã có tài khoản">
                                                        <i class="fas fa-check-circle"></i> Có TK
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning" title="Chưa có tài khoản">
                                                        <i class="fas fa-times-circle"></i> Chưa có
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-warning btn-edit" title="Chỉnh sửa"
                                                        onclick="editKhachHang('<?php echo $kh['MaKH']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger" title="Xóa"
                                                        onclick="deleteKhachHang('<?php echo $kh['MaKH']; ?>', '<?php echo htmlspecialchars(addslashes($kh['HoTen'])); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<!-- Modal sửa khách hàng -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Chỉnh sửa Khách Hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCustomerForm">
                <input type="hidden" id="editMaKH" name="maKH">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Đang chỉnh sửa khách hàng: <strong id="editMaKHText"></strong>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">Thông tin cá nhân</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ tên *</label>
                                <input type="text" class="form-control" id="editHoTen" name="hoten" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại *</label>
                                <input type="tel" class="form-control" id="editSoDienThoai" name="sodienthoai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái *</label>
                                <select class="form-select" id="editTrangThai" name="trangthai" required>
                                    <option value="Không ở">Không ở</option>
                                    <option value="Đang ở">Đang ở</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" class="form-control" id="editDiaChi" name="diachi">
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">Thông tin tài khoản</h6>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i> Để trống nếu không muốn thay đổi tài khoản
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" id="editTenDangNhap" name="tendangnhap">
                                <div class="form-text">Để trống nếu không đổi</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="editMatKhau" name="matkhau">
                                <div class="form-text">Để trống nếu không đổi</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CMND/CCCD</label>
                                <input type="text" class="form-control" id="editCMND" name="cmnd">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal thêm khách hàng -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Thêm Khách Hàng Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="addCustomerForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Mã KH</strong> sẽ được tạo tự động (KH1, KH2, KH3,...)
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">Thông tin cá nhân</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ tên *</label>
                                <input type="text" class="form-control" name="hoten" required
                                    placeholder="Nhập họ tên đầy đủ">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại *</label>
                                <input type="tel" class="form-control" name="sodienthoai" required
                                    placeholder="Nhập số điện thoại" pattern="[0-9]{9,11}">
                                <div class="form-text">Ví dụ: 0909123456</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái *</label>
                                <select class="form-select" name="trangthai" required>
                                    <option value="Không ở" selected>Không ở</option>
                                    <option value="Đang ở">Đang ở</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" class="form-control" name="diachi"
                                    placeholder="Nhập địa chỉ đầy đủ">
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">Thông tin tài khoản (tùy chọn)</h6>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i> Chỉ điền thông tin nếu muốn tạo/liên kết tài khoản cho khách hàng
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" name="tendangnhap"
                                    placeholder="Tên đăng nhập (nếu tạo TK)">
                                <div class="form-text">Dùng để đăng nhập hệ thống</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" name="matkhau"
                                    placeholder="Mật khẩu (nếu tạo TK)" minlength="6">
                                <div class="form-text">Ít nhất 6 ký tự</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email"
                                    placeholder="Email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CMND/CCCD</label>
                                <input type="text" class="form-control" name="cmnd"
                                    placeholder="Số CMND/CCCD">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm khách hàng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Xử lý form thêm khách hàng
    document.getElementById('addCustomerForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate
        const sodienthoai = this.sodienthoai.value.trim();
        const tendangnhap = this.tendangnhap.value.trim();
        const matkhau = this.matkhau.value.trim();
        const email = this.email.value.trim();
        const cmnd = this.cmnd.value.trim();

        // Validate số điện thoại
        if (!/^[0-9]{9,11}$/.test(sodienthoai)) {
            alert('Số điện thoại phải có 9-11 chữ số');
            this.sodienthoai.focus();
            return;
        }

        // Validate CMND
        if (cmnd && !/^[0-9]{9,12}$/.test(cmnd)) {
            alert('CMND/CCCD phải có 9-12 chữ số');
            this.cmnd.focus();
            return;
        }

        // Validate email
        if (email && !validateEmail(email)) {
            alert('Email không hợp lệ');
            this.email.focus();
            return;
        }

        // Validate tên đăng nhập và mật khẩu
        if ((tendangnhap && !matkhau) || (!tendangnhap && matkhau)) {
            alert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu');
            return;
        }

        if (matkhau && matkhau.length < 6) {
            alert('Mật khẩu phải có ít nhất 6 ký tự');
            this.matkhau.focus();
            return;
        }

        // Lấy dữ liệu form
        const formData = new FormData(this);

        // Hiển thị loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        // Gọi AJAX đến controller
        fetch('../../controller/letanlogon.controller.php?action=add', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text()) // Dùng .text() trước để debug
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('✅ Thêm khách hàng thành công! Mã KH: ' + data.maKH);
                        // Đóng modal và reload trang
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
                        modal.hide();
                        setTimeout(() => location.reload(), 500);
                    } else {
                        alert('❌ ' + data.message);
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    alert('⚠️ Lỗi server: ' + text.substring(0, 100));
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('⚠️ Có lỗi xảy ra, vui lòng thử lại');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // Validate email function
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    // Xử lý xóa khách hàng
    function deleteKhachHang(maKH, hoten) {
        if (!confirm(`Bạn có chắc chắn muốn xóa khách hàng "${hoten}" (${maKH})?\n\n⚠️ Hành động này sẽ xóa cả thông tin tài khoản liên quan!`)) {
            return;
        }

        const formData = new FormData();
        formData.append('maKH', maKH);

        fetch('../../controller/letanlogon.controller.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Xóa khách hàng thành công!');
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('⚠️ Có lỗi xảy ra khi xóa khách hàng');
            });
    }

    // Reset form khi modal đóng
    document.getElementById('addCustomerModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('addCustomerForm').reset();
    });
    // Hàm mở modal sửa
    function editKhachHang(maKH) {
        // Hiển thị loading
        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        modal.show();

        document.getElementById('editMaKHText').textContent = maKH;
        document.getElementById('editMaKH').value = maKH;

        // Lấy thông tin khách hàng
        fetch(`../../controller/letanlogon.controller.php?action=get&maKH=${encodeURIComponent(maKH)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const kh = data.data;

                    // Điền thông tin vào form
                    document.getElementById('editHoTen').value = kh.HoTen || '';
                    document.getElementById('editSoDienThoai').value = kh.SoDienThoai || '';
                    document.getElementById('editDiaChi').value = kh.DiaChi || '';
                    document.getElementById('editTrangThai').value = kh.TrangThai || 'Không ở';
                    document.getElementById('editTenDangNhap').value = kh.TenDangNhap || '';
                    document.getElementById('editEmail').value = kh.Email || '';
                    document.getElementById('editCMND').value = kh.CMND || '';

                    // Clear password field
                    document.getElementById('editMatKhau').value = '';
                } else {
                    alert('Không thể lấy thông tin khách hàng: ' + data.message);
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi lấy thông tin khách hàng');
                modal.hide();
            });
    }

    // Xử lý form sửa
    document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate
        const sodienthoai = this.sodienthoai.value.trim();
        const tendangnhap = this.tendangnhap.value.trim();
        const matkhau = this.matkhau.value.trim();
        const email = this.email.value.trim();
        const cmnd = this.cmnd.value.trim();

        // Validate số điện thoại
        if (!/^[0-9]{9,11}$/.test(sodienthoai)) {
            alert('Số điện thoại phải có 9-11 chữ số');
            this.sodienthoai.focus();
            return;
        }

        // Validate CMND
        if (cmnd && !/^[0-9]{9,12}$/.test(cmnd)) {
            alert('CMND/CCCD phải có 9-12 chữ số');
            this.cmnd.focus();
            return;
        }

        // Validate email
        if (email && !validateEmail(email)) {
            alert('Email không hợp lệ');
            this.email.focus();
            return;
        }

        // Validate tên đăng nhập và mật khẩu
        if (tendangnhap && !matkhau) {
            alert('Vui lòng nhập mật khẩu nếu đổi tên đăng nhập');
            this.matkhau.focus();
            return;
        }

        if (matkhau && matkhau.length < 6) {
            alert('Mật khẩu phải có ít nhất 6 ký tự');
            this.matkhau.focus();
            return;
        }

        // Lấy dữ liệu form
        const formData = new FormData(this);

        // Hiển thị loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        // Gọi AJAX để cập nhật
        fetch('../../controller/letanlogon.controller.php?action=edit', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Cập nhật khách hàng thành công!');
                    // Đóng modal và reload trang
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
                    modal.hide();
                    setTimeout(() => location.reload(), 500);
                } else {
                    alert('❌ ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('⚠️ Có lỗi xảy ra khi cập nhật');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // Xử lý chọn tất cả
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.select-customer');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateDeleteButton();
    });

    // Xử lý chọn từng cái
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('select-customer')) {
            updateDeleteButton();
        }
    });

    // Cập nhật trạng thái nút xóa nhiều
    function updateDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.select-customer:checked');
        const deleteBtn = document.getElementById('btnDeleteMultiple');

        if (checkedBoxes.length > 0) {
            deleteBtn.disabled = false;
            deleteBtn.textContent = `Xóa đã chọn (${checkedBoxes.length})`;
        } else {
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Xóa đã chọn';
        }
    }

    // Xóa nhiều khách hàng
    document.getElementById('btnDeleteMultiple').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.select-customer:checked');
        const listMaKH = Array.from(checkedBoxes).map(cb => cb.value);

        if (listMaKH.length === 0) {
            alert('Vui lòng chọn ít nhất một khách hàng để xóa');
            return;
        }

        if (!confirm(`Bạn có chắc chắn muốn xóa ${listMaKH.length} khách hàng đã chọn?\n\n⚠️ Hành động này không thể hoàn tác!`)) {
            return;
        }

        const formData = new FormData();
        listMaKH.forEach(maKH => {
            formData.append('listMaKH[]', maKH);
        });

        // Hiển thị loading
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
        this.disabled = true;

        fetch('../../controller/letanlogon.controller.php?action=delete-multiple', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    let errorMsg = '❌ ' + data.message;
                    if (data.errors && data.errors.length > 0) {
                        errorMsg += '\n\nChi tiết lỗi:\n' + data.errors.join('\n');
                    }
                    alert(errorMsg);
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('⚠️ Có lỗi xảy ra khi xóa nhiều khách hàng');
                this.innerHTML = originalText;
                this.disabled = false;
            });
    });
</script>

<?php
require_once '../layouts/footer.php';
?>