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

    case 'get_tai_khoan_chua_gan':
      $dsTaiKhoan = $model->getTaiKhoanChuaGanNhanVien();

      echo json_encode([
        'success' => true,
        'data' => $dsTaiKhoan
      ]);
      exit();
      break;

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
        $message .= "Mã nhân viên: " . $result['maNhanVien'] . "<br>";
        $message .= "Tài khoản: " . $_POST['email'] . "<br>";
        $message .= "Mật khẩu: " . $_POST['mat_khau'] . "<br>";
        $message .= "<small>Lưu ý: Ghi nhớ mật khẩu để cung cấp cho nhân viên!</small>";

        $_SESSION['success'] = $message;
      } else {
        $_SESSION['error'] = "Lỗi khi thêm nhân viên! " . ($result['message'] ?? '');
      }
      header('Location: quanlynhanvien.php');
      exit();
      break;

    case 'sua':
      $maNhanVien = $_POST['ma_nhan_vien'];

      // THÊM 2 DÒNG NÀY ĐỂ LẤY EMAIL VÀ CMND
      $email = $_POST['email'] ?? '';
      $cmnd = $_POST['cmnd'] ?? '';

      $data = [
        'HoTen' => $_POST['ho_ten'],
        'DiaChi' => $_POST['dia_chi'],
        'SDT' => $_POST['sdt'],
        'NgayVaoLam' => $_POST['ngay_vao_lam'],
        'NgayNghiViec' => $_POST['ngay_nghi_viec'] ?? NULL,
        'PhongBan' => $_POST['phong_ban'],
        'LuongCoBan' => $_POST['luong_co_ban'],
        'TrangThai' => $_POST['trang_thai'],
        'email' => $email,  // <=== THÊM DÒNG NÀY
        'cmnd' => $cmnd     // <=== THÊM DÒNG NÀY
      ];

      if (isset($_POST['reset_mat_khau']) && $_POST['reset_mat_khau'] == '1') {
        $data['reset_mat_khau'] = '1';
        $data['ma_tai_khoan'] = $_POST['ma_tai_khoan'];
        $data['mat_khau_moi'] = $_POST['mat_khau_moi'] ?? '123456';
      }

      $result = $model->suaNhanVien($maNhanVien, $data);

      if ($result['success']) {
        $message = "Cập nhật nhân viên thành công!";

        // THÊM PHẦN NÀY ĐỂ HIỂN THỊ EMAIL ĐÃ UPDATE
        if ($email) {
          $message .= "<br>Email đã cập nhật: " . $email;
        }

        if ($cmnd) {
          $message .= "<br>CMND đã cập nhật: " . $cmnd;
        }

        if (isset($result['mat_khau_moi'])) {
          $message .= "<br>Mật khẩu mới: " . $result['mat_khau_moi'];
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

$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
  $danhSachNhanVien = $model->timKiemNhanVien($keyword);
} else {
  $danhSachNhanVien = $model->getDanhSachNhanVien();
}

$thongKe = $model->thongKeNhanVien();
$dsPhongBan = $model->getDanhSachPhongBan();
$dsTaiKhoanChuaGan = $model->getTaiKhoanChuaGanNhanVien();

// ============================================
// PHẦN 4: HIỂN THỊ VIEW
// ============================================
include_once '../layouts/header.php';
?>

<div class="container-fluid">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center py-4">
    <div>
      <h1 class="h3 mb-1">Quản Lý Nhân Viên</h1>
      <p class="text-muted">Thêm/sửa/xóa nhân viên Resort</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#themNhanVienModal">
      <i class="fas fa-plus me-2"></i>Thêm Nhân Viên Mới
    </button>
  </div>

  <!-- Search and Filter -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="">
        <div class="row">
          <div class="col-md-8">
            <input type="text" class="form-control" name="keyword"
              placeholder="Tìm kiếm theo mã NV, tên, SĐT, email, phòng ban..."
              value="<?= htmlspecialchars($keyword) ?>">
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary me-2">Tìm Kiếm</button>
            <a href="quanlynhanvien.php" class="btn btn-secondary">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Alert Messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= $_SESSION['success'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= $_SESSION['error'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Danh sách nhân viên -->
  <div class="card">
    <div class="card-header bg-light">
      <h6 class="mb-0">Danh Sách Nhân Viên (<?= count($danhSachNhanVien) ?> người)</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead class="table-light">
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
                <td colspan="9" class="text-center py-4">
                  Không có dữ liệu nhân viên
                </td>
              </tr>
            <?php else: ?>
              <?php $stt = 1; ?>
              <?php foreach ($danhSachNhanVien as $nv): ?>
                <tr>
                  <td><?= $stt++ ?></td>
                  <td><span class="badge bg-secondary"><?= htmlspecialchars($nv['MaNhanVien']) ?></span></td>
                  <td>
                    <div><?= htmlspecialchars($nv['HoTen']) ?></div>
                    <?php if (!empty($nv['Email'])): ?>
                      <small class="text-muted"><?= htmlspecialchars($nv['Email']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($nv['SDT']) ?></td>
                  <td>
                    <div><?= htmlspecialchars($nv['PhongBan']) ?></div>
                    <?php if (!empty($nv['VaiTro'])): ?>
                      <small class="text-muted"><?= htmlspecialchars($nv['VaiTro']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?= date('d/m/Y', strtotime($nv['NgayVaoLam'])) ?></td>
                  <td class="text-end">
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
                      <button type="button" class="btn btn-outline-info"
                        onclick="showChiTietModal('<?= $nv['MaNhanVien'] ?>')">
                        Xem
                      </button>
                      <button type="button" class="btn btn-outline-warning"
                        onclick="showSuaNhanVienModal('<?= $nv['MaNhanVien'] ?>')">
                        Sửa
                      </button>
                      <a href="quanlynhanvien.php?action=xoa&ma_nhan_vien=<?= $nv['MaNhanVien'] ?>"
                        class="btn btn-outline-danger"
                        onclick="return confirm('Xóa nhân viên <?= $nv['MaNhanVien'] ?>?')">
                        Xóa
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

<!-- Modal Thêm Nhân Viên -->
<div class="modal fade" id="themNhanVienModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Thêm Nhân Viên Mới</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="quanlynhanvien.php?action=them" id="formThemNhanVien">
        <div class="modal-body">
          <div class="mb-3">
            <h6>Thông tin tài khoản</h6>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" required placeholder="nhanvien@company.com">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="mat_khau" required placeholder="Nhập mật khẩu" minlength="6">
                <small class="text-muted">Tối thiểu 6 ký tự</small>
              </div>
            </div>
          </div>

          <hr>
          <h6 class="mb-3">Thông tin nhân viên</h6>

          <div class="row">
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
            <!-- Thay thế dòng input lương này -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Lương Cơ Bản <span class="text-danger">*</span></label>
              <!-- SỬA: XÓA step="100000" -->
              <input type="number" class="form-control" name="luong_co_ban" required min="1"
                placeholder="Nhập lương cơ bản">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
              <select class="form-control" name="trang_thai" required>
                <option value="Đang làm" selected>Đang làm</option>
                <option value="Đã nghỉ">Đã nghỉ</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">CMND/CCCD <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="cmnd" required placeholder="Nhập số CMND/CCCD">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary">Thêm Nhân Viên</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Sửa Nhân Viên -->
<div class="modal fade" id="suaNhanVienModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Sửa Thông Tin Nhân Viên</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="quanlynhanvien.php?action=sua" id="formSuaNhanVien">
        <input type="hidden" name="ma_nhan_vien" id="sua_ma_nhan_vien">
        <div class="modal-body">
          <div id="suaFormContent">
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
              <p class="mt-2">Đang tải thông tin nhân viên...</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-warning">Cập nhật</button>
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
        <h5 class="modal-title">Chi Tiết Nhân Viên</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="chiTietContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<script>
  // ============================================
  // BIẾN TOÀN CỤC
  // ============================================
  let isSubmitting = false; // Tránh submit nhiều lần

  // ============================================
  // MODAL CHI TIẾT NHÂN VIÊN (giữ nguyên)
  // ============================================
  function showChiTietModal(maNhanVien) {
    fetch(`quanlynhanvien.php?action=get_nhan_vien_info&ma_nhan_vien=${maNhanVien}`)
      .then(response => response.json())
      .then(data => {
        if (!data.success || !data.data) {
          alert('Không tìm thấy thông tin nhân viên!');
          return;
        }

        const nv = data.data;
        const html = `
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <div class="fw-bold">Mã NV</div>
                <div>${nv.MaNhanVien}</div>
              </div>
              <div class="mb-3">
                <div class="fw-bold">Trạng thái</div>
                <div>${nv.TrangThai}</div>
              </div>
            </div>
            <div class="col-md-8">
              <div class="mb-3">
                <div class="fw-bold">Họ Tên</div>
                <div>${nv.HoTen}</div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <div class="fw-bold">Số điện thoại</div>
                  <div>${nv.SDT || '--'}</div>
                </div>
                <div class="col-md-6">
                  <div class="fw-bold">Phòng ban</div>
                  <div>${nv.PhongBan}</div>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <div class="fw-bold">Lương cơ bản</div>
                  <div>${new Intl.NumberFormat('vi-VN').format(nv.LuongCoBan)} đ</div>
                </div>
                <div class="col-md-6">
                  <div class="fw-bold">Ngày vào làm</div>
                  <div>${nv.NgayVaoLam ? new Date(nv.NgayVaoLam).toLocaleDateString('vi-VN') : '--'}</div>
                </div>
              </div>
              <div class="mb-3">
                <div class="fw-bold">Tài khoản hệ thống</div>
                ${nv.Email ? `
                  <div><strong>${nv.Email}</strong></div>
                  <div>Vai trò: ${nv.VaiTro}</div>
                  <div>Trạng thái: ${nv.TrangThaiTK == '1' ? 'Đang hoạt động' : 'Không hoạt động'}</div>
                ` : 'Chưa có tài khoản'}
              </div>
              <div class="mb-3">
                <div class="fw-bold">Địa chỉ</div>
                <div>${nv.DiaChi || '--'}</div>
              </div>
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

  // ============================================
  // MODAL SỬA NHÂN VIÊN - ĐƠN GIẢN HÓA
  // ============================================
  async function showSuaNhanVienModal(maNhanVien) {
    try {
      document.getElementById('sua_ma_nhan_vien').value = maNhanVien;

      const responseNV = await fetch(`quanlynhanvien.php?action=get_nhan_vien_info&ma_nhan_vien=${maNhanVien}`);
      const dataNV = await responseNV.json();

      if (!dataNV.success || !dataNV.data) {
        alert('Không tìm thấy thông tin nhân viên!');
        return;
      }

      const nv = dataNV.data;

      const formHTML = `
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Họ Tên <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="ho_ten" required 
               value="${nv.HoTen || ''}" 
               placeholder="Ví dụ: Nguyễn Văn A">
        <small class="text-muted">Chỉ nhập chữ cái, không nhập số hay ký tự đặc biệt</small>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="sdt" required value="${nv.SDT || ''}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Địa Chỉ</label>
        <textarea class="form-control" name="dia_chi" rows="2">${nv.DiaChi || ''}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Ngày Vào Làm <span class="text-danger">*</span></label>
        <input type="date" class="form-control" name="ngay_vao_lam" required value="${nv.NgayVaoLam || ''}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Ngày Nghỉ Việc</label>
        <input type="date" class="form-control" name="ngay_nghi_viec" value="${nv.NgayNghiViec || ''}">
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
        <input type="number" class="form-control" name="luong_co_ban" required  min="1" value="${nv.LuongCoBan || 0}" oninput="formatCurrencyInput(this)">
        <small class="text-muted">Nhập số nguyên (VD: 9200000)</small>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
        <select class="form-control" name="trang_thai" required>
            <option value="Đang làm" ${nv.TrangThai === 'Đang làm' ? 'selected' : ''}>Đang làm</option>
            <option value="Đã nghỉ" ${nv.TrangThai === 'Đã nghỉ' ? 'selected' : ''}>Đã nghỉ</option>
        </select>
    </div>
    
    ${nv.Email ? `
    <div class="col-12 mb-3">
        <div class="border p-3 rounded">
            <h6>Thông tin tài khoản</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" 
                           value="${nv.Email || ''}" required
                           placeholder="example@gmail.com">
                    <small class="text-muted">Phải có định dạng @gmail.com</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">CMND/CCCD <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="cmnd" required
                           value="${nv.CMND || ''}"  
                           placeholder="Nhập số CMND/CCCD" maxlength="12">
                    <small class="text-muted">CMND/CCCD 9-12 chữ số</small>
                </div>
                <div class="col-12">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="reset_mat_khau" value="1" id="resetPassword">
                        <label class="form-check-label" for="resetPassword">
                            Reset mật khẩu
                        </label>
                    </div>
                    <div id="passwordField" style="display: none;">
                        <input type="text" class="form-control mb-2" name="mat_khau_moi" value="123456">
                        <small class="text-muted">Để trống sẽ reset về: 123456</small>
                    </div>
                    <input type="hidden" name="ma_tai_khoan" value="${nv.MaTaiKhoan}">
                </div>
            </div>
        </div>
    </div>
    ` : `
    <div class="col-12 mb-3">
        <div class="alert alert-warning">
            Nhân viên chưa có tài khoản hệ thống
        </div>
    </div>
    `}
</div>
`;

      document.getElementById('suaFormContent').innerHTML = formHTML;

      // Xử lý checkbox reset mật khẩu
      const resetCheckbox = document.querySelector('#suaFormContent input[name="reset_mat_khau"]');
      const passwordField = document.getElementById('passwordField');

      if (resetCheckbox && passwordField) {
        resetCheckbox.addEventListener('change', function() {
          passwordField.style.display = this.checked ? 'block' : 'none';
        });
      }

      // THÊM REAL-TIME VALIDATION CHO MODAL SỬA
      setTimeout(() => {
        // Lương
        const luongInput = document.querySelector('#suaFormContent input[name="luong_co_ban"]');
        if (luongInput) {
          luongInput.removeAttribute('step');

          luongInput.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            value = parseInt(value) || 0;
            this.value = value;
          });

          luongInput.addEventListener('blur', function() {
            const luong = parseInt(this.value) || 0;
            if (luong <= 0) {
              this.value = '';
              setTimeout(() => {
                alert('❌ Lương cơ bản phải lớn hơn 0!');
                this.focus();
              }, 10);
            }
          });
        }

        // Họ tên
        const hoTenInput = document.querySelector('#suaFormContent input[name="ho_ten"]');
        if (hoTenInput) {
          hoTenInput.addEventListener('blur', function() {
            const name = this.value.trim();

            // Kiểm tra số
            if (/\d/.test(name)) {
              this.value = '';
              setTimeout(() => {
                alert('❌ Họ tên không được chứa số!');
                this.focus();
              }, 10);
              return;
            }

            // Kiểm tra ký tự đặc biệt
            if (/[!@#$%^&*()_+=\[\]{};:"\\|<>\/?~`]/.test(name)) {
              this.value = '';
              setTimeout(() => {
                alert('❌ Họ tên không được chứa ký tự đặc biệt!');
                this.focus();
              }, 10);
              return;
            }

            // Kiểm tra độ dài
            if (name.length > 0 && name.length < 2) {
              this.value = '';
              setTimeout(() => {
                alert('❌ Họ tên phải có ít nhất 2 ký tự!');
                this.focus();
              }, 10);
              return;
            }
          });
        }
      }, 100);

      // Hiển thị modal
      new bootstrap.Modal(document.getElementById('suaNhanVienModal')).show();

    } catch (error) {
      console.error('Lỗi:', error);
      alert('Lỗi khi tải dữ liệu!');
    }
  }
  // ============================================
  // VALIDATE ĐƠN GIẢN - CHỈ CẦN BẤM 1 LẦN
  // ============================================

  function validateForm(formId, isUpdate = false) {
    if (isSubmitting) {
      console.log('Đang submit, bỏ qua...');
      return false;
    }

    isSubmitting = true;
    console.log('Bắt đầu validate form...');

    try {
      let form;
      if (isUpdate) {
        form = document.querySelector('#suaFormContent');
        if (!form) {
          console.log('Không tìm thấy form sửa');
          isSubmitting = false;
          return false;
        }
      } else {
        form = document.getElementById(formId);
      }

      // 1. Kiểm tra Họ tên bằng hàm chung
      const hoTenInput = form.querySelector('input[name="ho_ten"]');
      if (hoTenInput) {
        const validation = validateHoTen(hoTenInput);
        if (!validation.valid) {
          alert(validation.message);
          hoTenInput.focus();
          isSubmitting = false;
          return false;
        }
      }

      // 2. Kiểm tra lương
      const luongInput = form.querySelector('input[name="luong_co_ban"]');
      if (luongInput) {
        const luong = parseInt(luongInput.value) || 0;
        if (luong <= 0) {
          alert('❌ Lương cơ bản phải lớn hơn 0!');
          luongInput.focus();
          isSubmitting = false;
          return false;
        }
      }

      // 3. Kiểm tra SDT
      const sdtInput = form.querySelector('input[name="sdt"]');
      if (sdtInput) {
        const sdt = sdtInput.value.trim();
        if (!sdt) {
          alert('❌ Số điện thoại không được để trống!');
          sdtInput.focus();
          isSubmitting = false;
          return false;
        }

        if (!/^[0-9]{10,11}$/.test(sdt)) {
          alert('❌ Số điện thoại phải có 10-11 chữ số!');
          sdtInput.focus();
          isSubmitting = false;
          return false;
        }
      }

      // 4. Kiểm tra email @gmail.com
      const emailInput = form.querySelector('input[name="email"]');
      if (emailInput) {
        const email = emailInput.value.trim();
        if (!email) {
          alert('❌ Email không được để trống!');
          emailInput.focus();
          isSubmitting = false;
          return false;
        }

        if (!email.endsWith('@gmail.com')) {
          alert('❌ Email phải có định dạng @gmail.com!');
          emailInput.focus();
          isSubmitting = false;
          return false;
        }
      }

      // 5. Kiểm tra CMND (9-12 số)
      const cmndInput = form.querySelector('input[name="cmnd"]');
      if (cmndInput) {
        const cmnd = cmndInput.value.trim();
        if (!cmnd) {
          alert('❌ CMND/CCCD không được để trống!');
          cmndInput.focus();
          isSubmitting = false;
          return false;
        }

        if (!/^\d{9,12}$/.test(cmnd)) {
          alert('❌ CMND/CCCD phải có 9-12 chữ số!');
          cmndInput.focus();
          isSubmitting = false;
          return false;
        }
      }

      // 6. Kiểm tra mật khẩu (chỉ form thêm)
      if (!isUpdate) {
        const matKhauInput = form.querySelector('input[name="mat_khau"]');
        if (matKhauInput) {
          const matKhau = matKhauInput.value;
          if (matKhau.length < 6) {
            alert('❌ Mật khẩu phải có ít nhất 6 ký tự!');
            matKhauInput.focus();
            isSubmitting = false;
            return false;
          }
        }
      }

      // 7. Kiểm tra ngày nghỉ hợp lệ
      const ngayVaoLamInput = form.querySelector('input[name="ngay_vao_lam"]');
      const ngayNghiViecInput = form.querySelector('input[name="ngay_nghi_viec"]');

      if (ngayVaoLamInput && ngayNghiViecInput && ngayNghiViecInput.value) {
        const ngayVL = new Date(ngayVaoLamInput.value);
        const ngayNV = new Date(ngayNghiViecInput.value);

        if (ngayNV < ngayVL) {
          alert('❌ Ngày nghỉ việc phải sau ngày vào làm!');
          ngayNghiViecInput.focus();
          isSubmitting = false;
          return false;
        }
      }

      // 8. Confirm - CHỈ 1 LẦN DUY NHẤT
      const message = isUpdate ?
        'Bạn có chắc muốn cập nhật thông tin nhân viên này?' :
        'Bạn có chắc muốn thêm nhân viên này?';

      if (!confirm(message)) {
        console.log('Người dùng hủy confirm');
        isSubmitting = false;
        return false;
      }

      console.log('Validation thành công, cho phép submit');
      return true;

    } catch (error) {
      console.error('Lỗi validate:', error);
      isSubmitting = false;
      return false;
    }
  }
  // ============================================
  // HÀM XỬ LÝ NHẬP LIỆU LƯƠNG
  // ============================================

  // Định dạng số khi nhập
  function formatCurrencyInput(input) {
    // Lấy giá trị và loại bỏ ký tự không phải số
    let value = input.value.replace(/[^\d]/g, '');

    // Chuyển thành số nguyên
    value = parseInt(value) || 0;

    // Giữ nguyên giá trị (không làm tròn theo step)
    input.value = value;

    // Format hiển thị nhưng giữ giá trị thực
    const formatted = new Intl.NumberFormat('vi-VN').format(value);
    input.setAttribute('data-formatted', formatted);
  }

  // Validate lương khi submit
  function validateLuong(input) {
    const value = parseInt(input.value) || 0;

    if (value <= 0) {
      alert('❌ Lương cơ bản phải lớn hơn 0!');
      input.focus();
      return false;
    }

    // Kiểm tra xem có phải là số nguyên không
    if (!Number.isInteger(value)) {
      alert('❌ Lương phải là số nguyên!');
      input.focus();
      return false;
    }

    return true;
  }
  // ============================================
  // REAL-TIME VALIDATION CHO HỌ TÊN
  // ============================================

  document.addEventListener('DOMContentLoaded', function() {
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

    // Real-time validation cho họ tên (form thêm) - FIXED
    const formThem = document.getElementById('formThemNhanVien');
    if (formThem) {
      const hoTenInput = formThem.querySelector('input[name="ho_ten"]');
      if (hoTenInput) {
        // Xóa event listener cũ nếu có
        const newHoTenInput = hoTenInput.cloneNode(true);
        hoTenInput.parentNode.replaceChild(newHoTenInput, hoTenInput);

        // Thêm event listener mới với xử lý đúng
        newHoTenInput.addEventListener('blur', function(e) {
          const name = this.value.trim();

          // Kiểm tra số
          if (/\d/.test(name)) {
            // FIX: Xóa giá trị và focus
            this.value = '';
            // Delay một chút để alert không block focus
            setTimeout(() => {
              alert('❌ Họ tên không được chứa số!');
              this.focus();
            }, 10);
            return;
          }

          // Kiểm tra ký tự đặc biệt (cho phép dấu câu tiếng Việt cơ bản)
          // Cho phép: dấu cách, dấu phẩy, dấu chấm, dấu gạch ngang, dấu nháy đơn
          if (/[!@#$%^&*()_+=\[\]{};:"\\|<>\/?~`]/.test(name)) {
            this.value = '';
            setTimeout(() => {
              alert('❌ Họ tên không được chứa ký tự đặc biệt (chỉ cho phép chữ cái, dấu cách, dấu phẩy, dấu chấm)!');
              this.focus();
            }, 10);
            return;
          }

          // Kiểm tra độ dài
          if (name.length > 0 && name.length < 2) {
            this.value = '';
            setTimeout(() => {
              alert('❌ Họ tên phải có ít nhất 2 ký tự!');
              this.focus();
            }, 10);
            return;
          }
        });
      }

      // Real-time validation cho lương
      const luongInput = formThem.querySelector('input[name="luong_co_ban"]');
      if (luongInput) {
        // Xóa step attribute để không bị làm tròn
        luongInput.removeAttribute('step');

        // Clone để tránh event listener trùng
        const newLuongInput = luongInput.cloneNode(true);
        luongInput.parentNode.replaceChild(newLuongInput, luongInput);

        newLuongInput.addEventListener('blur', function() {
          const luong = parseInt(this.value) || 0;
          if (luong <= 0) {
            this.value = '';
            setTimeout(() => {
              alert('❌ Lương cơ bản phải lớn hơn 0!');
              this.focus();
            }, 10);
          }
        });

        // Format khi nhập
        newLuongInput.addEventListener('input', function() {
          let value = this.value.replace(/[^\d]/g, '');
          value = parseInt(value) || 0;
          this.value = value;
        });
      }
    }

    // Reset biến submitting khi modal đóng
    const modals = ['themNhanVienModal', 'suaNhanVienModal'];
    modals.forEach(modalId => {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
          isSubmitting = false;
        });
      }
    });
  });
  // ============================================
  // HÀM VALIDATE HỌ TÊN CHUNG (Dùng chung cho cả form)
  // ============================================

  function validateHoTen(inputElement) {
    const name = inputElement.value.trim();

    // Kiểm tra trống
    if (!name) {
      return {
        valid: false,
        message: "Họ tên không được để trống!"
      };
    }

    // Kiểm tra số
    if (/\d/.test(name)) {
      return {
        valid: false,
        message: "Họ tên không được chứa số!"
      };
    }

    // Kiểm tra ký tự đặc biệt (cho phép một số dấu câu cơ bản)
    // Cho phép: khoảng trắng, dấu phẩy, dấu chấm, dấu gạch ngang, dấu nháy đơn
    if (/[!@#$%^&*()_+=\[\]{};:"\\|<>\/?~`]/.test(name)) {
      return {
        valid: false,
        message: "Họ tên không được chứa ký tự đặc biệt!"
      };
    }

    // Kiểm tra độ dài
    if (name.length < 2) {
      return {
        valid: false,
        message: "Họ tên phải có ít nhất 2 ký tự!"
      };
    }

    return {
      valid: true,
      message: ""
    };
  }


  // ============================================
  // XỬ LÝ FORM SUBMIT - ĐƠN GIẢN
  // ============================================

  // Form thêm nhân viên
  document.getElementById('formThemNhanVien').addEventListener('submit', function(e) {
    if (!validateForm('formThemNhanVien', false)) {
      e.preventDefault();
    }
  });

  // Form sửa nhân viên
  document.getElementById('formSuaNhanVien').addEventListener('submit', function(e) {
    if (!validateForm('formSuaNhanVien', true)) {
      e.preventDefault();
    }
  });

  // ============================================
  // XỬ LÝ NGÀY NGHỈ - ĐƠN GIẢN
  // ============================================

  // Kiểm tra ngày nghỉ khi thay đổi
  document.addEventListener('change', function(e) {
    if (e.target.name === 'ngay_nghi_viec') {
      const ngayVaoLam = document.querySelector('input[name="ngay_vao_lam"]');
      const trangThaiSelect = document.querySelector('select[name="trang_thai"]');

      if (!ngayVaoLam || !ngayVaoLam.value || !e.target.value) return;

      const ngayVL = new Date(ngayVaoLam.value);
      const ngayNV = new Date(e.target.value);

      // Kiểm tra hợp lệ
      if (ngayNV < ngayVL) {
        alert('❌ Ngày nghỉ việc phải sau ngày vào làm!');
        e.target.value = '';
        return;
      }

      // Tự động đề xuất đổi trạng thái nếu cần
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (ngayNV <= today && trangThaiSelect && trangThaiSelect.value === 'Đang làm') {
        if (confirm('⚠️ Ngày nghỉ việc đã qua hoặc là hôm nay.\nBạn có muốn tự động cập nhật trạng thái thành "Đã nghỉ" không?')) {
          trangThaiSelect.value = 'Đã nghỉ';
        }
      }
    }
  });

  // ============================================
  // INITIALIZATION
  // ============================================

  document.addEventListener('DOMContentLoaded', function() {
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

    // Reset biến submitting khi modal đóng
    const modals = ['themNhanVienModal', 'suaNhanVienModal'];
    modals.forEach(modalId => {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
          isSubmitting = false;
        });
      }
    });
  });
</script>

<?php include_once '../layouts/footer.php'; ?>