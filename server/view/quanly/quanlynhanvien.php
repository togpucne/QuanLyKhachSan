<?php
// ============================================
// PHẦN 1: KIỂM TRA VÀ KHỞI TẠO
// ============================================
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
  header('Location: ../login/login.php');
  exit();
}

// Gọi Model
include_once '../../model/quanlynhanvien.model.php';
$model = new QuanLyNhanVienModel();

// ============================================
// PHẦN 2: XỬ LÝ CÁC ACTION
// ============================================

// 1. Xử lý các action GET
if (isset($_GET['action'])) {
  $action = $_GET['action'];

  switch ($action) {
    // Xóa nhân viên
    case 'xoa':
      if (isset($_GET['ma_nhan_vien'])) {
        $maNhanVien = $_GET['ma_nhan_vien'];
        if ($model->xoaNhanVien($maNhanVien)) {
          $_SESSION['success'] = "Xóa nhân viên thành công!";
        } else {
          $_SESSION['error'] = "Lỗi khi xóa nhân viên!";
        }
        header('Location: quanlynhanvien.php');
        exit();
      }
      break;

    // AJAX: Lấy chi tiết nhân viên để sửa
    case 'get_nhan_vien_info':
      if (isset($_GET['ma_nhan_vien'])) {
        $maNhanVien = $_GET['ma_nhan_vien'];
        $nhanVien = $model->getChiTietNhanVien($maNhanVien);

        echo json_encode([
          'success' => !empty($nhanVien),
          'data' => $nhanVien ?: null
        ]);
        exit();
      }
      break;

    // AJAX: Lấy danh sách tài khoản chưa gán
    case 'get_tai_khoan_chua_gan':
      $dsTaiKhoan = $model->getTaiKhoanChuaGanNhanVien();

      echo json_encode([
        'success' => true,
        'data' => $dsTaiKhoan
      ]);
      exit();
      break;

    // AJAX: Lấy danh sách phòng ban
    case 'get_ds_phong_ban':
      $dsPhongBan = $model->getDanhSachPhongBan();

      echo json_encode([
        'success' => true,
        'data' => $dsPhongBan
      ]);
      exit();
      break;
  }
}

// 2. Xử lý các action POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
  $action = $_GET['action'];

  switch ($action) {
    // Thêm nhân viên - VỚI EMAIL VÀ MẬT KHẨU TỰ NHẬP
    case 'them':
      $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'],
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? NULL,
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai'],
        'CMND' => $_POST['cmnd'] ?? '',
        'email' => $_POST['email'],
        'mat_khau' => $_POST['mat_khau']
      ];

      $result = $model->themNhanVien($data);

      if ($result['success']) {
        $message = "Thêm nhân viên thành công!<br>";
        $message .= "Mã nhân viên: <strong>" . $result['maNhanVien'] . "</strong><br>";
        $message .= "Tài khoản: <strong>" . $_POST['email'] . "</strong><br>";
        $message .= "Mật khẩu: <strong>" . $_POST['mat_khau'] . "</strong><br>";
        $message .= "<small class='text-danger'>Lưu ý: Ghi nhớ mật khẩu để cung cấp cho nhân viên!</small>";

        $_SESSION['success'] = $message;
      } else {
        $_SESSION['error'] = "Lỗi khi thêm nhân viên! " . ($result['message'] ?? '');
      }
      header('Location: quanlynhanvien.php');
      exit();
      break;

    // Sửa nhân viên - CÓ THỂ RESET MẬT KHẨU
    case 'sua':
      $maNhanVien = $_POST['ma_nhan_vien'];
      $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'],
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? NULL,
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai']
      ];

      // Nếu có yêu cầu reset mật khẩu
      if (isset($_POST['reset_mat_khau']) && $_POST['reset_mat_khau'] == '1') {
        $data['reset_mat_khau'] = '1';
        $data['ma_tai_khoan'] = $_POST['ma_tai_khoan'];
        $data['mat_khau_moi'] = $_POST['mat_khau_moi'] ?? '123456';
      }

      $result = $model->suaNhanVien($maNhanVien, $data);

      if ($result['success']) {
        $message = "Cập nhật nhân viên thành công!";

        // Nếu có reset mật khẩu
        if (isset($result['mat_khau_moi'])) {
          $message .= "<br>Mật khẩu mới: <strong>" . $result['mat_khau_moi'] . "</strong>";
        }

        $_SESSION['success'] = $message;
      } else {
        $_SESSION['error'] = "Lỗi khi cập nhật nhân viên! " . ($result['message'] ?? '');
      }
      header('Location: quanlynhanvien.php');
      exit();
      break;
  }
}


// ============================================
// PHẦN 3: LẤY DỮ LIỆU CHO VIEW
// ============================================

/// Lấy danh sách nhân viên (có tìm kiếm)
$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
  $danhSachNhanVien = $model->timKiemNhanVien($keyword);
} else {
  $danhSachNhanVien = $model->getDanhSachNhanVien();
}

// Lấy thống kê
$thongKe = $model->thongKeNhanVien();

// Lấy danh sách phòng ban
$dsPhongBan = $model->getDanhSachPhongBan();

// Lấy tài khoản chưa gán (chỉ nhân viên và quản lý)
$dsTaiKhoanChuaGan = $model->getTaiKhoanChuaGanNhanVien();

// ============================================
// PHẦN 4: HIỂN THỊ VIEW
// ============================================
include_once '../layouts/header.php';
?>

<div class="container-fluid">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">
      <i class="fas fa-users-cog me-2"></i>Quản Lý Nhân Viên
    </h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#themNhanVienModal">
      <i class="fas fa-plus me-2"></i>Thêm Nhân Viên Mới
    </button>
  </div>

  <!-- Thống kê nhanh -->
  <?php if ($thongKe): ?>
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                  Tổng nhân viên
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <?= $thongKe['tongNhanVien'] ?> người
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-users fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-left-success shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                  Đang làm việc
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <?= $thongKe['dangLam'] ?> người
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-user-check fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-left-warning shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                  Đã nghỉ việc
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <?= $thongKe['daNghi'] ?> người
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-user-times fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-left-info shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                  Lương trung bình
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <?= number_format($thongKe['luongTrungBinh'], 0, ',', '.') ?> đ
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Search and Filter -->
  <div class="card shadow mb-4">
    <div class="card-body">
      <form method="GET" action="">
        <div class="row">
          <div class="col-md-8">
            <input type="text" class="form-control" name="keyword"
              placeholder="Tìm kiếm theo mã NV, tên, SĐT, email, phòng ban..."
              value="<?= htmlspecialchars($keyword) ?>">
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="fas fa-search me-1"></i>Tìm Kiếm
            </button>
            <a href="quanlynhanvien.php" class="btn btn-secondary">
              <i class="fas fa-refresh me-1"></i>Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Alert Messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle me-2"></i>
      <?= $_SESSION['success'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-circle me-2"></i>
      <?= $_SESSION['error'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Danh sách nhân viên -->
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h6 class="m-0 font-weight-bold">
        <i class="fas fa-list me-2"></i>Danh Sách Nhân Viên
        <span class="badge bg-light text-dark ms-2"><?= count($danhSachNhanVien) ?> người</span>
      </h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
          <thead class="table-dark">
            <tr>
              <th width="50">STT</th>
              <th width="120">Mã NV</th>
              <th>Họ Tên</th>
              <th width="120">SĐT</th>
              <th width="150">Phòng Ban</th>
              <th width="120">Ngày vào làm</th>
              <th width="150">Lương cơ bản</th>
              <th width="100">Trạng thái</th>
              <th width="150" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($danhSachNhanVien)): ?>
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  <i class="fas fa-inbox fa-2x mb-3"></i><br>
                  Không có dữ liệu nhân viên
                </td>
              </tr>
            <?php else: ?>
              <?php $stt = 1; ?>
              <?php foreach ($danhSachNhanVien as $nv): ?>
                <tr>
                  <td><?= $stt++ ?></td>
                  <td><span class="badge bg-success fs-6"><?= htmlspecialchars($nv['MaNhanVien']) ?></span></td>
                  <td>
                    <strong><?= htmlspecialchars($nv['HoTen']) ?></strong>
                    <?php if (!empty($nv['Email'])): ?>
                      <br><small class="text-muted"><?= htmlspecialchars($nv['Email']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($nv['SDT']) ?></td>
                  <td>
                    <span class="badge bg-info"><?= htmlspecialchars($nv['PhongBan']) ?></span>
                    <?php if (!empty($nv['VaiTro'])): ?>
                      <br><small class="text-muted"><?= htmlspecialchars($nv['VaiTro']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?= date('d/m/Y', strtotime($nv['NgayVaoLam'])) ?></td>
                  <td class="text-end fw-bold text-success">
                    <?= number_format($nv['LuongCoBan'], 0, ',', '.') ?> đ
                  </td>
                  <td>
                    <?php if ($nv['TrangThai'] === 'Đang làm'): ?>
                      <span class="badge bg-success">Đang làm</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Đã nghỉ</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm w-100">
                      <button type="button" class="btn btn-info"
                        onclick="showChiTietModal('<?= $nv['MaNhanVien'] ?>')"
                        title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button type="button" class="btn btn-warning"
                        onclick="showSuaNhanVienModal('<?= $nv['MaNhanVien'] ?>')"
                        title="Sửa thông tin">
                        <i class="fas fa-edit"></i>
                      </button>
                      <a href="quanlynhanvien.php?action=xoa&ma_nhan_vien=<?= $nv['MaNhanVien'] ?>"
                        class="btn btn-danger"
                        onclick="return confirm('Bạn có chắc muốn xóa nhân viên <?= $nv['MaNhanVien'] ?>?\n\nLưu ý: Tài khoản liên kết sẽ KHÔNG bị xóa.')"
                        title="Xóa nhân viên">
                        <i class="fas fa-trash"></i>
                      </a>
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
</div>

<!-- ============================================
PHẦN 5: CÁC MODAL
============================================ -->

<!-- Modal Thêm Nhân Viên -->
<div class="modal fade" id="themNhanVienModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Thêm Nhân Viên Mới</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="quanlynhanvien.php?action=them" id="formThemNhanVien">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Nhập thông tin tài khoản cho nhân viên</strong>
          </div>

          <div class="row">
            <!-- THÔNG TIN TÀI KHOẢN -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Email (Tên đăng nhập) <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" required
                placeholder="nhanvien@company.com">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" name="mat_khau" id="mat_khau_moi" required
                  placeholder="Nhập mật khẩu" minlength="6">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <small class="text-muted">Mật khẩu tối thiểu 6 ký tự</small>
            </div>

            <!-- THÔNG TIN NHÂN VIÊN -->
            <hr class="my-3">
            <h6 class="fw-bold mb-3"><i class="fas fa-id-card me-2"></i>Thông tin nhân viên</h6>

            <div class="col-md-6 mb-3">
              <label class="form-label">Họ Tên <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="ho_ten" required placeholder="Nhập họ tên đầy đủ">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="sdt" required placeholder="Nhập số điện thoại">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">Địa Chỉ</label>
              <textarea class="form-control" name="dia_chi" rows="2" placeholder="Nhập địa chỉ"></textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Ngày Vào Làm <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="ngay_vao_lam" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Ngày Nghỉ Việc</label>
              <input type="date" class="form-control" name="ngay_nghi_viec">
              <small class="text-muted">Chỉ điền nếu nhân viên đã nghỉ</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Phòng Ban <span class="text-danger">*</span></label>
              <select class="form-control" name="phong_ban" required>
                <option value="">-- Chọn phòng ban --</option>
                <option value="Kinh Doanh">Kinh Doanh</option>
                <option value="Lễ Tân">Lễ Tân</option>
                <option value="Buồng Phòng">Buồng Phòng</option>
                <option value="Kế Toán">Kế Toán</option>
                <option value="Quản Lý">Quản Lý</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Lương Cơ Bản <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="luong_co_ban" required
                min="0" step="100000" placeholder="Nhập lương cơ bản">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
              <select class="form-control" name="trang_thai" required>
                <option value="Đang làm" selected>Đang làm</option>
                <option value="Đã nghỉ">Đã nghỉ</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">CMND/CCCD</label>
              <input type="text" class="form-control" name="cmnd" placeholder="Nhập số CMND/CCCD">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Đóng
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Thêm Nhân Viên
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Modal Sửa Nhân Viên -->
<div class="modal fade" id="suaNhanVienModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa Thông Tin Nhân Viên</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="quanlynhanvien.php?action=sua" id="formSuaNhanVien">
        <input type="hidden" name="ma_nhan_vien" id="sua_ma_nhan_vien">
        <div class="modal-body">
          <div class="alert alert-info" id="sua_alert_info">
            <i class="fas fa-info-circle me-2"></i>Sửa thông tin nhân viên
          </div>

          <div id="suaFormContent">
            <!-- Nội dung form sẽ được load bằng JavaScript -->
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
              <p class="mt-2">Đang tải thông tin nhân viên...</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Đóng
          </button>
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save me-1"></i>Cập nhật
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Xem Chi Tiết -->
<div class="modal fade" id="chiTietModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Chi Tiết Nhân Viên</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="chiTietContent">
        <!-- Nội dung sẽ được load bằng JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Đóng
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================
PHẦN 6: JAVASCRIPT
============================================ -->
<script>
  // Tạo mật khẩu ngẫu nhiên
  function generatePassword() {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 8; i++) {
      password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('mat_khau_moi').value = password;
  }

  // Hiện/ẩn mật khẩu
  document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('mat_khau_moi');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
  });

  // Trong hàm showChiTietModal
  function showChiTietModal(maNhanVien) {
    fetch(`quanlynhanvien.php?action=get_nhan_vien_info&ma_nhan_vien=${maNhanVien}`)
      .then(response => response.json())
      .then(data => {
        if (!data.success || !data.data) {
          alert('Không tìm thấy thông tin nhân viên!');
          return;
        }

        const nv = data.data;

        // Format ngày
        const ngayVaoLam = nv.NgayVaoLam ? new Date(nv.NgayVaoLam).toLocaleDateString('vi-VN') : '--';
        const ngayNghiViec = nv.NgayNghiViec ? new Date(nv.NgayNghiViec).toLocaleDateString('vi-VN') : '--';
        const luongFormatted = new Intl.NumberFormat('vi-VN').format(nv.LuongCoBan) + ' đ';

        // Hiển thị thông tin tài khoản
        let taiKhoanInfo = 'Chưa gắn tài khoản';
        if (nv.Email) {
          taiKhoanInfo = `
                    <strong>${nv.Email}</strong><br>
                    <small class="text-muted">
                        Vai trò: <span class="badge bg-info">${nv.VaiTro}</span> |
                        Trạng thái: <span class="badge ${nv.TrangThaiTK == '1' ? 'bg-success' : 'bg-warning'}">
                            ${nv.TrangThaiTK == '1' ? 'Đang hoạt động' : 'Không hoạt động'}
                        </span>
                    </small>
                `;
        }

        // Tạo HTML chi tiết
        const html = `
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="avatar-circle mb-3">
                            <i class="fas fa-user fa-4x text-primary"></i>
                        </div>
                        <h4 class="fw-bold">${nv.MaNhanVien}</h4>
                        <span class="badge ${nv.TrangThai === 'Đang làm' ? 'bg-success' : 'bg-danger'} fs-6">
                            ${nv.TrangThai}
                        </span>
                    </div>
                    <div class="col-md-8">
                        <h4 class="fw-bold text-primary mb-3">${nv.HoTen}</h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-phone me-2 text-success"></i>Số điện thoại</h6>
                                <p class="ms-4">${nv.SDT || '--'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-user-tag me-2 text-warning"></i>Phòng ban</h6>
                                <p class="ms-4"><span class="badge bg-info">${nv.PhongBan}</span></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-money-bill-wave me-2 text-success"></i>Lương cơ bản</h6>
                                <p class="ms-4 fw-bold text-success">${luongFormatted}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-calendar-check me-2 text-primary"></i>Ngày vào làm</h6>
                                <p class="ms-4">${ngayVaoLam}</p>
                            </div>
                        </div>
                        
                        <h6><i class="fas fa-envelope me-2 text-secondary"></i>Tài khoản hệ thống</h6>
                        <div class="ms-4 mb-3 p-3 bg-light rounded">
                            ${taiKhoanInfo}
                        </div>
                        
                        <h6><i class="fas fa-map-marker-alt me-2 text-secondary"></i>Địa chỉ</h6>
                        <p class="ms-4">${nv.DiaChi || '--'}</p>
                    </div>
                </div>
            `;

        document.getElementById('chiTietContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('chiTietModal')).show();
      })
      .catch(error => {
        console.error('Lỗi:', error);
        alert('Lỗi khi tải thông tin!');
      });
  }

  async function showSuaNhanVienModal(maNhanVien) {
    try {
      document.getElementById('sua_ma_nhan_vien').value = maNhanVien;

      // 1. Lấy thông tin nhân viên
      const responseNV = await fetch(`quanlynhanvien.php?action=get_nhan_vien_info&ma_nhan_vien=${maNhanVien}`);
      const dataNV = await responseNV.json();

      if (!dataNV.success || !dataNV.data) {
        alert('Không tìm thấy thông tin nhân viên!');
        return;
      }

      const nv = dataNV.data;

      // Tạo form HTML với chức năng reset mật khẩu
      const formHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Họ Tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ho_ten" required 
                        value="${nv.HoTen || ''}" placeholder="Nhập họ tên đầy đủ">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="sdt" required 
                        value="${nv.SDT || ''}" placeholder="Nhập số điện thoại">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Địa Chỉ</label>
                    <textarea class="form-control" name="dia_chi" rows="2" 
                        placeholder="Nhập địa chỉ">${nv.DiaChi || ''}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày Vào Làm <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="ngay_vao_lam" required 
                        value="${nv.NgayVaoLam || ''}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày Nghỉ Việc</label>
                    <input type="date" class="form-control" name="ngay_nghi_viec" 
                        value="${nv.NgayNghiViec || ''}">
                    <small class="text-muted">Chỉ điền nếu nhân viên đã nghỉ</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phòng Ban <span class="text-danger">*</span></label>
                    <select class="form-control" name="phong_ban" required>
                        <option value="">-- Chọn phòng ban --</option>
                        <option value="Kinh Doanh" ${nv.PhongBan === 'Kinh Doanh' ? 'selected' : ''}>Kinh Doanh</option>
                        <option value="Lễ Tân" ${nv.PhongBan === 'Lễ Tân' ? 'selected' : ''}>Lễ Tân</option>
                        <option value="Buồng Phòng" ${nv.PhongBan === 'Buồng Phòng' ? 'selected' : ''}>Buồng Phòng</option>
                        <option value="Kế Toán" ${nv.PhongBan === 'Kế Toán' ? 'selected' : ''}>Kế Toán</option>
                        <option value="Quản Lý" ${nv.PhongBan === 'Quản Lý' ? 'selected' : ''}>Quản Lý</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lương Cơ Bản <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="luong_co_ban" required 
                        min="0" step="100000" value="${nv.LuongCoBan || 0}" placeholder="Nhập lương cơ bản">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
                    <select class="form-control" name="trang_thai" required>
                        <option value="Đang làm" ${nv.TrangThai === 'Đang làm' ? 'selected' : ''}>Đang làm</option>
                        <option value="Đã nghỉ" ${nv.TrangThai === 'Đã nghỉ' ? 'selected' : ''}>Đã nghỉ</option>
                    </select>
                </div>
                
                <!-- THÔNG TIN TÀI KHOẢN VÀ RESET MẬT KHẨU -->
                ${nv.Email ? `
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-key me-2"></i>Quản lý tài khoản</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Tài khoản hiện tại</label>
                                    <div class="form-control bg-light">
                                        <strong>${nv.Email}</strong>
                                        <span class="badge bg-info ms-2">${nv.VaiTro}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reset mật khẩu</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="reset_mat_khau" value="1" id="resetPassword">
                                        <label class="form-check-label" for="resetPassword">
                                            Reset mật khẩu về mặc định
                                        </label>
                                    </div>
                                    <div id="passwordField" style="display: none;">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="mat_khau_moi" value="123456" placeholder="Mật khẩu mới">
                                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPassword()">
                                                <i class="fas fa-random"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Để trống sẽ reset về: 123456</small>
                                    </div>
                                    <input type="hidden" name="ma_tai_khoan" value="${nv.MaTaiKhoan}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ` : `
                <div class="col-12 mb-3">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Nhân viên chưa có tài khoản hệ thống
                    </div>
                </div>
                `}
            </div>
        `;

      document.getElementById('suaFormContent').innerHTML = formHTML;
      document.getElementById('sua_alert_info').innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            Sửa thông tin nhân viên: <strong>${nv.MaNhanVien} - ${nv.HoTen}</strong>
        `;

      // Thêm sự kiện cho checkbox reset mật khẩu
      setTimeout(() => {
        const resetCheckbox = document.querySelector('input[name="reset_mat_khau"]');
        const passwordField = document.getElementById('passwordField');

        if (resetCheckbox && passwordField) {
          resetCheckbox.addEventListener('change', function() {
            passwordField.style.display = this.checked ? 'block' : 'none';
          });
        }
      }, 100);

      new bootstrap.Modal(document.getElementById('suaNhanVienModal')).show();

    } catch (error) {
      console.error('Lỗi:', error);
      alert('Lỗi khi tải dữ liệu!');
    }
  }

  // Hàm tạo mật khẩu ngẫu nhiên
  function generateRandomPassword() {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    let password = '';
    for (let i = 0; i < 8; i++) {
      password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.querySelector('input[name="mat_khau_moi"]').value = password;
  }

  // ==================== EVENT LISTENERS ====================
  // Validate form thêm nhân viên
  document.getElementById('formThemNhanVien').addEventListener('submit', function(e) {
    const luong = document.querySelector('input[name="luong_co_ban"]').value;
    if (luong < 0) {
      alert('Lương cơ bản không được âm!');
      e.preventDefault();
      return false;
    }

    // Kiểm tra tab đang active
    const activeTab = document.querySelector('#themNhanVienTab .nav-link.active');
    const tabId = activeTab.getAttribute('data-bs-target');

    if (tabId === '#taotk') {
      const email = document.querySelector('input[name="email_tai_khoan"]').value;
      const matKhau = document.getElementById('mat_khau_moi').value;

      if (!email || !matKhau) {
        alert('Vui lòng nhập đầy đủ thông tin tài khoản!');
        e.preventDefault();
        return false;
      }

      if (matKhau.length < 6) {
        alert('Mật khẩu phải có ít nhất 6 ký tự!');
        e.preventDefault();
        return false;
      }
    } else if (tabId === '#chontk') {
      const maTaiKhoan = document.getElementById('selectTaiKhoanSanCo').value;

      if (!maTaiKhoan) {
        alert('Vui lòng chọn tài khoản cho nhân viên!');
        e.preventDefault();
        return false;
      }
    }

    return true;
  });

  // Validate form sửa nhân viên
  document.getElementById('formSuaNhanVien').addEventListener('submit', function(e) {
    const luong = document.querySelector('#suaFormContent input[name="luong_co_ban"]').value;
    if (luong < 0) {
      alert('Lương cơ bản không được âm!');
      e.preventDefault();
      return false;
    }
    return true;
  });

  // ==================== INITIALIZATION ====================
  document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Quản lý nhân viên đã sẵn sàng');

    // Format số tiền trong bảng
    document.querySelectorAll('td.text-end').forEach(td => {
      const text = td.textContent.trim();
      if (text.includes('đ')) {
        const number = text.replace(/[^\d]/g, '');
        if (number) {
          td.textContent = new Intl.NumberFormat('vi-VN').format(number) + ' đ';
        }
      }
    });
  });
</script>

<style>
  .avatar-circle {
    width: 120px;
    height: 120px;
    background-color: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #dee2e6;
    margin: 0 auto;
  }

  .avatar-circle i {
    color: #6c757d;
  }

  .nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
  }

  .nav-tabs .nav-link.active {
    background-color: #e7f1ff;
    border-color: #dee2e6 #dee2e6 #fff;
    font-weight: bold;
  }
</style>

<?php include_once '../layouts/footer.php'; ?>