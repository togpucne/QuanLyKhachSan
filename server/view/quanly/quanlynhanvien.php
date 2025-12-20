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
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Quản Lý Nhân Viên</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#themNhanVienModal">
      Thêm Nhân Viên Mới
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
            <div class="col-md-6 mb-3">
              <label class="form-label">Lương Cơ Bản <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="luong_co_ban" required min="0" step="100000" placeholder="Nhập lương cơ bản">
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
  function showChiTietModal(maNhanVien) {
    fetch(`quanlynhanvien.php?action=get_nhan_vien_info&ma_nhan_vien=${maNhanVien}`)
      .then(response => response.json())
      .then(data => {
        if (!data.success || !data.data) {
          alert('Không tìm thấy thông tin nhân viên!');
          return;
        }

        const nv = data.data;
        const ngayVaoLam = nv.NgayVaoLam ? new Date(nv.NgayVaoLam).toLocaleDateString('vi-VN') : '--';
        const ngayNghiViec = nv.NgayNghiViec ? new Date(nv.NgayNghiViec).toLocaleDateString('vi-VN') : '--';
        const luongFormatted = new Intl.NumberFormat('vi-VN').format(nv.LuongCoBan) + ' đ';

        let taiKhoanInfo = 'Chưa có tài khoản';
        if (nv.Email) {
          taiKhoanInfo = `
            <div><strong>${nv.Email}</strong></div>
            <div>Vai trò: ${nv.VaiTro}</div>
            <div>Trạng thái: ${nv.TrangThaiTK == '1' ? 'Đang hoạt động' : 'Không hoạt động'}</div>
          `;
        }

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
                  <div>${luongFormatted}</div>
                </div>
                <div class="col-md-6">
                  <div class="fw-bold">Ngày vào làm</div>
                  <div>${ngayVaoLam}</div>
                </div>
              </div>
              <div class="mb-3">
                <div class="fw-bold">Tài khoản hệ thống</div>
                ${taiKhoanInfo}
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

      // Trong hàm showSuaNhanVienModal, sửa phần form HTML:

      const formHTML = `
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Họ Tên <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="ho_ten" required value="${nv.HoTen || ''}">
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
        <input type="number" class="form-control" name="luong_co_ban" required min="0" step="100000" value="${nv.LuongCoBan || 0}">
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
                    <div id="email-error" class="invalid-feedback" style="display: none;"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">CMND/CCCD</label>
                    <input type="text" class="form-control" name="cmnd" 
                           value="${nv.CMND || ''}"  <!-- SỬA TỪ nv.CMND THÀNH nv.CMND -->
                           placeholder="Nhập số CMND/CCCD" maxlength="12">
                    <small class="text-muted">9-12 chữ số</small>
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

  // Validate form
  document.getElementById('formThemNhanVien').addEventListener('submit', function(e) {
    const luong = document.querySelector('input[name="luong_co_ban"]').value;
    if (luong < 0) {
      alert('Lương cơ bản không được âm!');
      e.preventDefault();
      return false;
    }
    return true;
  });

  document.getElementById('formSuaNhanVien').addEventListener('submit', function(e) {
    const luong = document.querySelector('#suaFormContent input[name="luong_co_ban"]').value;
    if (luong < 0) {
      alert('Lương cơ bản không được âm!');
      e.preventDefault();
      return false;
    }
    return true;
  });

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
  });
  // Trong hàm showSuaNhanVienModal, thêm phần xử lý khi chọn trạng thái
  function handleTrangThaiChange() {
    const trangThaiSelect = document.querySelector('#suaFormContent select[name="trang_thai"]');
    if (trangThaiSelect) {
      trangThaiSelect.addEventListener('change', function() {
        if (this.value === 'Đã nghỉ') {
          // Hiển thị cảnh báo
          const warningDiv = document.createElement('div');
          warningDiv.className = 'alert alert-warning mt-2';
          warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Lưu ý: Khi chọn trạng thái "Đã nghỉ", tài khoản sẽ tự động bị khóa (không hoạt động)';

          // Kiểm tra xem đã có cảnh báo chưa
          if (!document.querySelector('#trangThaiWarning')) {
            warningDiv.id = 'trangThaiWarning';
            this.parentNode.appendChild(warningDiv);
          }
        } else {
          // Xóa cảnh báo nếu có
          const warningDiv = document.querySelector('#trangThaiWarning');
          if (warningDiv) {
            warningDiv.remove();
          }
        }
      });

      // Kích hoạt sự kiện khi load modal
      trangThaiSelect.dispatchEvent(new Event('change'));
    }
  }

  // Trong hàm showSuaNhanVienModal, sau khi load form xong, gọi hàm xử lý
  // Thêm dòng này sau phần document.getElementById('suaFormContent').innerHTML = formHTML;
  setTimeout(() => {
    handleTrangThaiChange();
  }, 100);
  // Kiểm tra ngày nghỉ hợp lệ
  function kiemTraNgayNghi() {
    const ngayVaoLam = document.querySelector('input[name="ngay_vao_lam"]').value;
    const ngayNghiViec = document.querySelector('input[name="ngay_nghi_viec"]').value;

    if (ngayNghiViec && ngayVaoLam) {
      const ngayVL = new Date(ngayVaoLam);
      const ngayNV = new Date(ngayNghiViec);

      if (ngayNV < ngayVL) {
        alert('❌ Ngày nghỉ việc phải sau ngày vào làm!');
        document.querySelector('input[name="ngay_nghi_viec"]').value = '';
        return false;
      }

      // Kiểm tra nếu đã qua ngày nghỉ
      const today = new Date();
      if (ngayNV <= today) {
        const confirmUpdate = confirm('⚠️ Ngày nghỉ việc đã qua hoặc là hôm nay.\nBạn có muốn tự động cập nhật trạng thái thành "Đã nghỉ" không?');
        if (confirmUpdate) {
          document.querySelector('select[name="trang_thai"]').value = 'Đã nghỉ';
        }
      }
    }
    return true;
  }

  // Gắn sự kiện cho form
  document.addEventListener('DOMContentLoaded', function() {
    // Form thêm nhân viên
    const formThem = document.getElementById('formThemNhanVien');
    if (formThem) {
      const ngayNghiInput = formThem.querySelector('input[name="ngay_nghi_viec"]');
      if (ngayNghiInput) {
        ngayNghiInput.addEventListener('change', kiemTraNgayNghi);
      }
    }

    // Form sửa nhân viên (xử lý động)
    document.addEventListener('change', function(e) {
      if (e.target.name === 'ngay_nghi_viec' && e.target.closest('#suaFormContent')) {
        kiemTraNgayNghi();
      }
    });

    // Form submit validation
    document.getElementById('formThemNhanVien')?.addEventListener('submit', function(e) {
      if (!kiemTraNgayNghi()) {
        e.preventDefault();
        return false;
      }
      return true;
    });

    document.getElementById('formSuaNhanVien')?.addEventListener('submit', function(e) {
      const ngayNghiInput = document.querySelector('#suaFormContent input[name="ngay_nghi_viec"]');
      if (ngayNghiInput && !kiemTraNgayNghi()) {
        e.preventDefault();
        return false;
      }
      return true;
    });
  });
  // Kiểm tra ngày nghỉ hợp lệ và thông báo
  function kiemTraNgayNghiVaThongBao() {
    const ngayVaoLam = document.querySelector('input[name="ngay_vao_lam"]').value;
    const ngayNghiViecInput = document.querySelector('input[name="ngay_nghi_viec"]');
    const trangThaiSelect = document.querySelector('select[name="trang_thai"]');

    if (!ngayNghiViecInput || !ngayVaoLam) return true;

    const ngayNghiViec = ngayNghiViecInput.value;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (ngayNghiViec) {
      const ngayVL = new Date(ngayVaoLam);
      const ngayNV = new Date(ngayNghiViec);

      // Kiểm tra hợp lệ
      if (ngayNV < ngayVL) {
        alert('❌ Ngày nghỉ việc phải sau ngày vào làm!');
        ngayNghiViecInput.value = '';
        return false;
      }

      // Kiểm tra các trường hợp
      if (ngayNV <= today) {
        // Đã qua hoặc đến ngày nghỉ
        if (trangThaiSelect && trangThaiSelect.value === 'Đang làm') {
          const confirmUpdate = confirm('⚠️ Ngày nghỉ việc đã qua hoặc là hôm nay.\nBạn có muốn tự động cập nhật trạng thái thành "Đã nghỉ" không?');
          if (confirmUpdate) {
            trangThaiSelect.value = 'Đã nghỉ';
          }
        }
      } else {
        // Ngày nghỉ trong tương lai
        if (trangThaiSelect && trangThaiSelect.value === 'Đã nghỉ') {
          const confirmUpdate = confirm('⚠️ Ngày nghỉ việc là trong tương laí.\nBạn có muốn tự động cập nhật trạng thái thành "Đang làm" không?');
          if (confirmUpdate) {
            trangThaiSelect.value = 'Đang làm';
          }
        }
      }
    } else {
      // Clear ngày nghỉ
      if (trangThaiSelect && trangThaiSelect.value === 'Đã nghỉ') {
        const confirmUpdate = confirm('⚠️ Ngày nghỉ việc đã được xóa.\nBạn có muốn tự động cập nhật trạng thái thành "Đang làm" không?');
        if (confirmUpdate) {
          trangThaiSelect.value = 'Đang làm';
        }
      }
    }
    return true;
  }

  // Gắn sự kiện cho form
  document.addEventListener('DOMContentLoaded', function() {
    // Form thêm nhân viên
    const formThem = document.getElementById('formThemNhanVien');
    if (formThem) {
      const ngayNghiInput = formThem.querySelector('input[name="ngay_nghi_viec"]');
      const trangThaiSelect = formThem.querySelector('select[name="trang_thai"]');

      if (ngayNghiInput) {
        ngayNghiInput.addEventListener('change', kiemTraNgayNghiVaThongBao);
      }
      if (trangThaiSelect) {
        trangThaiSelect.addEventListener('change', function() {
          // Nếu chuyển từ "Đã nghỉ" sang "Đang làm", kiểm tra ngày nghỉ
          if (this.value === 'Đang làm') {
            kiemTraNgayNghiVaThongBao();
          }
        });
      }
    }

    // Form sửa nhân viên (xử lý động)
    document.addEventListener('change', function(e) {
      if ((e.target.name === 'ngay_nghi_viec' || e.target.name === 'trang_thai') &&
        e.target.closest('#suaFormContent')) {
        kiemTraNgayNghiVaThongBao();
      }
    });
    // Validate email khi submit form
    document.getElementById('formSuaNhanVien').addEventListener('submit', function(e) {
      const emailInput = document.querySelector('#suaFormContent input[name="email"]');
      const cmndInput = document.querySelector('#suaFormContent input[name="cmnd"]');

      if (emailInput) {
        const email = emailInput.value.trim();

        // Kiểm tra email không trống
        if (!email) {
          alert('Vui lòng nhập email!');
          e.preventDefault();
          return false;
        }

        // Kiểm tra định dạng email
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          alert('Email không hợp lệ!');
          e.preventDefault();
          return false;
        }

        // Kiểm tra phải là @gmail.com
        if (!email.endsWith('@gmail.com')) {
          alert('Email phải có định dạng @gmail.com!');
          e.preventDefault();
          return false;
        }
      }

      if (cmndInput && cmndInput.value) {
        const cmnd = cmndInput.value.trim();
        if (!/^\d{9,12}$/.test(cmnd)) {
          alert('CMND phải có 9-12 chữ số!');
          e.preventDefault();
          return false;
        }
      }

      // Kiểm tra các validation khác
      const luong = document.querySelector('#suaFormContent input[name="luong_co_ban"]').value;
      if (luong < 0) {
        alert('Lương cơ bản không được âm!');
        e.preventDefault();
        return false;
      }

      // Confirm trước khi update
      if (!confirm('Bạn có chắc muốn cập nhật thông tin nhân viên này?')) {
        e.preventDefault();
        return false;
      }

      return true;
    });

    // Kiểm tra email real-time (tùy chọn)
    function setupEmailValidation() {
      const emailInput = document.querySelector('#suaFormContent input[name="email"]');
      if (!emailInput) return;

      emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        const emailInput = this;
        const taiKhoanID = <?php echo $nhanVien['MaTaiKhoan'] ?? 0; ?>;

        if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          // Kiểm tra định dạng @gmail.com
          if (!email.endsWith('@gmail.com')) {
            showEmailError(emailInput, 'Email phải có định dạng @gmail.com');
            return;
          }

          // Gửi AJAX để kiểm tra email trùng
          const xhr = new XMLHttpRequest();
          xhr.open('POST', 'check_email_nhanvien.php', true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

          xhr.onload = function() {
            if (xhr.status === 200) {
              const response = JSON.parse(xhr.responseText);
              if (response.exists) {
                showEmailError(emailInput, 'Email đã tồn tại trong hệ thống!');
              } else {
                showEmailSuccess(emailInput);
              }
            }
          };

          xhr.send(`email=${encodeURIComponent(email)}&id=${taiKhoanID}`);
        } else if (email) {
          showEmailError(emailInput, 'Email không hợp lệ!');
        }
      });
    }

    function showEmailError(input, message) {
      input.classList.remove('is-valid');
      input.classList.add('is-invalid');
      const errorDiv = input.parentNode.querySelector('#email-error');
      if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
      }
    }

    function showEmailSuccess(input) {
      input.classList.remove('is-invalid');
      input.classList.add('is-valid');
      const errorDiv = input.parentNode.querySelector('#email-error');
      if (errorDiv) {
        errorDiv.style.display = 'none';
      }
    }

    // Gọi setupEmailValidation khi modal hiển thị
    document.getElementById('suaNhanVienModal').addEventListener('shown.bs.modal', function() {
      setTimeout(setupEmailValidation, 500);
    });

    // Form submit validation
    document.getElementById('formThemNhanVien')?.addEventListener('submit', function(e) {
      if (!kiemTraNgayNghiVaThongBao()) {
        e.preventDefault();
        return false;
      }
      return true;
    });

    document.getElementById('formSuaNhanVien')?.addEventListener('submit', function(e) {
      const ngayNghiInput = document.querySelector('#suaFormContent input[name="ngay_nghi_viec"]');
      if (ngayNghiInput && !kiemTraNgayNghiVaThongBao()) {
        e.preventDefault();
        return false;
      }
      return true;
    });
  });
</script>

<?php include_once '../layouts/footer.php'; ?>