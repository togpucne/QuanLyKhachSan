<?php
// view/letan/letanlogon.php - FILE NÀY CHỈ CHỨA CONTENT

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

// GỌI HEADER (chỉ có navbar và sidebar)
require_once '../layouts/header.php';
?>

<style>
    /* Validation styles - CHỈ HIỂN THỊ SAU KHI SUBMIT (was-validated) */
    .was-validated .form-control:valid,
    .was-validated .form-select:valid {
        border-color: #28a745 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .was-validated .form-control:invalid,
    .was-validated .form-select:invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    /* MẶC ĐỊNH: KHÔNG HIỂN THỊ VALIDATION */
    .form-control:not(.was-validated),
    .form-select:not(.was-validated) {
        border-color: #ced4da !important;
        background-image: none !important;
    }

    /* Focus state - áp dụng cho tất cả */
    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    /* Ẩn feedback mặc định */
    .invalid-feedback {
        display: none;
    }

    /* Chỉ hiện feedback khi form đã validate */
    .was-validated .invalid-feedback {
        display: block;
    }

    .valid-feedback {
        display: none;
    }

    .was-validated .form-control:valid~.valid-feedback {
        display: block;
    }

    /* Đảm bảo input không bị đỏ mặc định */
    .form-control {
        background-image: none !important;
    }
</style>

<style>
    /* Các style khác giữ nguyên */
    .required-label {
        color: #dc3545 !important;
        font-weight: bold;
    }

    .real-time-error {
        color: #dc3545;
        font-size: 0.875em;
        display: block;
        margin-top: 0.25rem;
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon .status-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.2em;
    }

    .status-valid {
        color: #28a745;
    }

    .status-invalid {
        color: #dc3545;
    }

    /* Style cho testcase button */
    #runTestCases {
        margin-left: 10px;
        font-size: 0.8rem;
    }

    /* Highlight cho input đang focus với lỗi - chỉ khi đã validate */
    .was-validated .form-control.is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }

    /* Real-time validation message */
    .realtime-feedback {
        display: block;
        min-height: 24px;
        font-size: 0.875em;
    }

    /* Validation styles - chỉ áp dụng khi đã validate */
    .was-validated .form-control.is-invalid {
        border-color: #dc3545 !important;
    }

    .was-validated .form-control.is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }

    .invalid-feedback {
        display: none;
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    /* Highlight search results */
    mark {
        background-color: #ffc107;
        padding: 0.1em 0.2em;
        border-radius: 0.2em;
    }

    /* Filter section */
    .filter-section {
        transition: all 0.3s ease;
    }

    /* Responsive table */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }

        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }
    }
</style>


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

<!-- Thanh tìm kiếm và lọc nhanh -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-0"><i class="fas fa-filter"></i> Tìm kiếm & Lọc nhanh</h6>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-sm btn-outline-secondary" onclick="resetFilter()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" id="filterKeyword"
                    placeholder="Tìm theo tên, SĐT, mã...">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterStatus">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Đang ở">Đang ở</option>
                    <option value="Không ở">Không ở</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterAccount">
                    <option value="">Tất cả tài khoản</option>
                    <option value="1">Có tài khoản</option>
                    <option value="0">Không có tài khoản</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterSort">
                    <option value="newest">Mới nhất</option>
                    <option value="oldest">Cũ nhất</option>
                    <option value="name_asc">Tên A-Z</option>
                    <option value="name_desc">Tên Z-A</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="date" class="form-control" id="filterDate">
                    <button class="btn btn-primary" onclick="filterTable()">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
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
                <thead class="table-dark">
                    <tr>
                        <th width="30"><input type="checkbox" id="selectAll"></th>
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
                                <div class="input-group">
                                    <input type="password" class="form-control" id="editMatKhau" name="matkhau" placeholder="Để trống nếu không đổi">
                                    <button type="button" class="btn btn-outline-warning" onclick="resetEditPassword()" id="btnResetPassword">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                                <div class="form-text">Để trống nếu không đổi. Reset về: 123456</div>
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

            <form id="addCustomerForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Mã KH</strong> sẽ được tạo tự động (KH1, KH2, KH3,...)
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">Thông tin cá nhân <span class="required-label">*</span></h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ tên <span class="required-label">*</span></label>
                                <input type="text" class="form-control" name="hoten" required
                                    placeholder="Nhập họ tên đầy đủ">
                                <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại <span class="required-label">*</span></label>
                                <input type="text" class="form-control" name="sodienthoai" pattern="^0[0-9]{9}$" required
                                    placeholder="Nhập số điện thoại 10 số">
                                <div class="invalid-feedback">Số điện thoại phải có 10 số và bắt đầu bằng 0</div>
                                <div class="form-text">Ví dụ: 0909123456 - Phải có 10 số</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái <span class="required-label">*</span></label>
                                <select class="form-select" name="trangthai" required>
                                    <option value="" selected disabled>Chọn trạng thái</option>
                                    <option value="Không ở">Không ở</option>
                                    <option value="Đang ở">Đang ở</option>
                                </select>
                                <div class="invalid-feedback">Vui lòng chọn trạng thái</div>
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

                    <h6 class="border-bottom pb-2 mb-3 mt-4">Thông tin tài khoản <span class="required-label">*</span></h6>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>TẤT CẢ</strong> thông tin tài khoản là bắt buộc
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập <span class="required-label">*</span></label>
                                <input type="text" class="form-control" name="tendangnhap" id="tendangnhapInput"
                                    pattern="^[a-z0-9_]{3,20}$" required
                                    placeholder="Sẽ tự động tạo từ Họ tên">
                                <div class="invalid-feedback">Tên đăng nhập phải có 3-20 ký tự (chữ thường, số, dấu _)</div>
                                <div class="form-text">Tự động tạo từ Họ tên</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu <span class="required-label">*</span></label>
                                <input type="password" class="form-control" name="matkhau" id="matkhauInput"
                                    minlength="6" required value="123456">
                                <div class="invalid-feedback">Mật khẩu phải có ít nhất 6 ký tự</div>
                                <div class="form-text">Mật khẩu mặc định: 123456</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="required-label">*</span></label>
                                <input type="email" class="form-control" name="email" id="emailInput"
                                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required
                                    placeholder="Email">
                                <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CMND/CCCD <span class="required-label">*</span></label>
                                <input type="text" class="form-control" name="cmnd" id="cmndInput"
                                    pattern="^[0-9]{9,12}$" required
                                    placeholder="Số CMND/CCCD">
                                <div class="invalid-feedback">CMND/CCCD phải có 9-12 chữ số</div>
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
    // ==================== GLOBAL UTILITY FUNCTIONS ====================
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    function validateVietnamesePhone(phone) {
        return /^0[0-9]{9}$/.test(phone);
    }

    function validateCMND(cmnd) {
        return /^[0-9]{9,12}$/.test(cmnd);
    }

    function validatePassword(password) {
        return password.length >= 6;
    }

    function generateUsernameFromName(fullName) {
        if (!fullName) return '';

        let username = fullName.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd').replace(/Đ/g, 'D')
            .replace(/[^a-z0-9\s]/g, '')
            .trim();

        const words = username.split(/\s+/);
        if (words.length === 1) {
            username = words[0];
        } else {
            const firstWord = words[0];
            const lastWord = words[words.length - 1];
            username = firstWord + lastWord.charAt(0);
        }

        return username.substring(0, 15);
    }

    async function checkDuplicate(type, value) {
        if (!value) return false;

        try {
            const formData = new FormData();
            formData.append('type', type);
            formData.append('value', value);

            const response = await fetch('../../controller/letanlogon.controller.php?action=check', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            return data.exists || false;
        } catch (error) {
            console.error('Error checking duplicate:', error);
            return false;
        }
    }

    function showFieldError(field, message) {
        if (!field) return;

        field.classList.add('is-invalid');
        let feedback = field.nextElementSibling;

        while (feedback && !feedback.classList.contains('invalid-feedback')) {
            feedback = feedback.nextElementSibling;
        }

        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }
    }

    function resetEditPassword() {
        const passwordInput = document.getElementById('editMatKhau');
        if (passwordInput) {
            passwordInput.value = '123456';
            alert('Đã reset mật khẩu về 123456');
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ==================== ADD CUSTOMER FORM ====================
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate username from name
        const hotenInput = document.querySelector('#addCustomerForm input[name="hoten"]');
        if (hotenInput) {
            hotenInput.addEventListener('input', function() {
                const usernameInput = document.getElementById('tendangnhapInput');
                if (usernameInput && !usernameInput.dataset.manual) {
                    usernameInput.value = generateUsernameFromName(this.value);
                }
            });
        }

        // Mark username as manual when user types
        const usernameInput = document.getElementById('tendangnhapInput');
        if (usernameInput) {
            usernameInput.addEventListener('input', function() {
                this.dataset.manual = 'true';
            });
        }

        // Form submission
        const addForm = document.getElementById('addCustomerForm');
        if (addForm) {
            addForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!await validateAddForm(this)) {
                    return;
                }

                await submitAddForm(this);
            });
        }
        const addModal = document.getElementById('addCustomerModal');
        const editModal = document.getElementById('editCustomerModal');
        if (addModal) {
            addModal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('addCustomerForm');
                if (form) {
                    form.reset();
                    // QUAN TRỌNG: Xóa class was-validated
                    form.classList.remove('was-validated');

                    // Reset tất cả validation state
                    form.querySelectorAll('.form-control, .form-select').forEach(input => {
                        input.classList.remove('is-valid', 'is-invalid');
                        // Reset border về mặc định
                        input.style.borderColor = '';
                        input.style.backgroundImage = 'none';
                    });

                    // Ẩn tất cả feedback messages
                    form.querySelectorAll('.invalid-feedback, .valid-feedback').forEach(feedback => {
                        feedback.style.display = 'none';
                    });

                    // Reset manual flag
                    const usernameInput = document.getElementById('tendangnhapInput');
                    if (usernameInput) {
                        delete usernameInput.dataset.manual;
                    }
                }
            });
        }
        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('editCustomerForm');
                if (form) {
                    form.reset();
                    // Xóa class was-validated nếu có
                    form.classList.remove('was-validated');

                    // Reset tất cả validation state
                    form.querySelectorAll('.form-control, .form-select').forEach(input => {
                        input.classList.remove('is-valid', 'is-invalid');
                        input.style.borderColor = '';
                        input.style.backgroundImage = 'none';
                    });
                }
            });
        }

        // Hàm setup real-time validation (tùy chọn)
        function setupRealTimeValidation() {
            const addForm = document.getElementById('addCustomerForm');
            if (!addForm) return;

            // Validate khi blur (focus out)
            const inputs = addForm.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    // Chỉ validate nếu đã nhập gì đó
                    if (this.value.trim() !== '') {
                        validateSingleField(this);
                    }
                });
            });
        }

        function validateSingleField(field) {
            // Chỉ validate khi form đã từng submit (có was-validated)
            // hoặc khi field đã blur và có giá trị
            if (field.value.trim() === '') return;

            // Check validity
            const isValid = field.checkValidity();

            if (isValid) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
            }
        }

    });

    async function validateAddForm(form) {
        // Reset validation
        form.classList.remove('was-validated');

        // Required fields
        const requiredFields = [{
                name: 'hoten',
                message: 'Vui lòng nhập họ tên'
            },
            {
                name: 'sodienthoai',
                message: 'Vui lòng nhập số điện thoại'
            },
            {
                name: 'trangthai',
                message: 'Vui lòng chọn trạng thái'
            },
            {
                name: 'tendangnhap',
                message: 'Vui lòng nhập tên đăng nhập'
            },
            {
                name: 'matkhau',
                message: 'Vui lòng nhập mật khẩu'
            },
            {
                name: 'email',
                message: 'Vui lòng nhập email'
            },
            {
                name: 'cmnd',
                message: 'Vui lòng nhập CMND/CCCD'
            }
        ];

        let isValid = true;
        let firstInvalidField = null;

        // Check required fields
        for (const field of requiredFields) {
            const element = form.elements[field.name];
            if (!element || !element.value.trim()) {
                showFieldError(element, field.message);
                isValid = false;
                if (!firstInvalidField) firstInvalidField = element;
            } else {
                element.classList.remove('is-invalid');
            }
        }

        // Validate specific fields
        const phone = form.sodienthoai?.value.trim();
        if (phone && !validateVietnamesePhone(phone)) {
            showFieldError(form.sodienthoai, 'Số điện thoại phải có 10 số và bắt đầu bằng 0');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = form.sodienthoai;
        }

        const email = form.email?.value.trim();
        if (email && !validateEmail(email)) {
            showFieldError(form.email, 'Email không hợp lệ');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = form.email;
        }

        const cmnd = form.cmnd?.value.trim();
        if (cmnd && !validateCMND(cmnd)) {
            showFieldError(form.cmnd, 'CMND phải có 9-12 chữ số');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = form.cmnd;
        }

        const password = form.matkhau?.value.trim();
        if (password && !validatePassword(password)) {
            showFieldError(form.matkhau, 'Mật khẩu phải có ít nhất 6 ký tự');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = form.matkhau;
        }

        if (!isValid) {
            form.classList.add('was-validated');
            alert('Vui lòng nhập đầy đủ và chính xác tất cả thông tin!');
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            return false;
        }

        // Check duplicates
        const duplicates = await Promise.all([
            checkDuplicate('phone', phone),
            checkDuplicate('username', form.tendangnhap.value.trim()),
            checkDuplicate('email', email),
            checkDuplicate('cmnd', cmnd)
        ]);

        const [phoneDup, userDup, emailDup, cmndDup] = duplicates;

        if (phoneDup) {
            alert('❌ Số điện thoại đã tồn tại trong hệ thống!');
            form.sodienthoai.focus();
            return false;
        }
        if (userDup) {
            alert('❌ Tên đăng nhập đã được sử dụng!');
            form.tendangnhap.focus();
            return false;
        }
        if (emailDup) {
            alert('❌ Email đã được đăng ký!');
            form.email.focus();
            return false;
        }
        if (cmndDup) {
            alert('❌ CMND/CCCD đã tồn tại!');
            form.cmnd.focus();
            return false;
        }

        return true;
    }

    async function submitAddForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('../../controller/letanlogon.controller.php?action=add', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                let message = '✅ Thêm khách hàng thành công!\n';
                message += 'Mã KH: ' + data.maKH + '\n\n';
                message += '📋 Thông tin tài khoản:\n';
                message += '👤 Tên đăng nhập: ' + form.tendangnhap.value + '\n';
                message += '🔐 Mật khẩu: ' + form.matkhau.value + '\n';
                message += '📧 Email: ' + form.email.value + '\n';
                message += '🆔 CMND: ' + form.cmnd.value;

                alert(message);

                const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
                if (modal) modal.hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('❌ ' + data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('⚠️ Có lỗi xảy ra, vui lòng thử lại');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    // ==================== EDIT CUSTOMER FORM ====================
    function editKhachHang(maKH) {
        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        modal.show();

        document.getElementById('editMaKHText').textContent = maKH;
        document.getElementById('editMaKH').value = maKH;

        fetch(`../../controller/letanlogon.controller.php?action=get&maKH=${encodeURIComponent(maKH)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const kh = data.data;
                    document.getElementById('editHoTen').value = kh.HoTen || '';
                    document.getElementById('editSoDienThoai').value = kh.SoDienThoai || '';
                    document.getElementById('editDiaChi').value = kh.DiaChi || '';
                    document.getElementById('editTrangThai').value = kh.TrangThai || 'Không ở';
                    document.getElementById('editTenDangNhap').value = kh.TenDangNhap || '';
                    document.getElementById('editEmail').value = kh.Email || '';
                    document.getElementById('editCMND').value = kh.CMND || '';
                    document.getElementById('editMatKhau').value = '';

                    // Lưu thông tin cũ để check duplicate
                    document.getElementById('editCustomerForm').dataset.originalPhone = kh.SoDienThoai || '';
                    document.getElementById('editCustomerForm').dataset.originalEmail = kh.Email || '';
                    document.getElementById('editCustomerForm').dataset.originalCMND = kh.CMND || '';
                    document.getElementById('editCustomerForm').dataset.originalUsername = kh.TenDangNhap || '';
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

    document.getElementById('editCustomerForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!await validateEditForm(this)) {
            return;
        }

        await submitEditForm(this);
    });

    async function validateEditForm(form) {
        const phone = form.sodienthoai?.value.trim();
        const email = form.email?.value.trim();
        const cmnd = form.cmnd?.value.trim();
        const password = form.matkhau?.value.trim();
        const username = form.tendangnhap?.value.trim();

        // Lấy thông tin cũ
        const originalPhone = form.dataset.originalPhone || '';
        const originalEmail = form.dataset.originalEmail || '';
        const originalCMND = form.dataset.originalCMND || '';
        const originalUsername = form.dataset.originalUsername || '';

        // 1. Validate thông tin bắt buộc (cá nhân)
        if (!form.hoten?.value.trim()) {
            alert('Vui lòng nhập họ tên');
            form.hoten.focus();
            return false;
        }

        if (!phone) {
            alert('Vui lòng nhập số điện thoại');
            form.sodienthoai.focus();
            return false;
        }

        if (!validateVietnamesePhone(phone)) {
            alert('Số điện thoại phải có 10 số và bắt đầu bằng 0');
            form.sodienthoai.focus();
            return false;
        }

        // 2. Validate thông tin tài khoản (chỉ validate nếu có nhập)

        // Nếu có nhập username
        if (username) {
            if (username.length < 3) {
                alert('Tên đăng nhập phải có ít nhất 3 ký tự');
                form.tendangnhap.focus();
                return false;
            }

            if (!/^[a-z0-9_]+$/.test(username)) {
                alert('Tên đăng nhập chỉ cho phép chữ thường, số và dấu gạch dưới (_)');
                form.tendangnhap.focus();
                return false;
            }

            // Nếu đổi username thì phải có password
            if (username !== originalUsername && !password) {
                alert('Vui lòng nhập mật khẩu nếu đổi tên đăng nhập');
                form.matkhau.focus();
                return false;
            }
        }

        // Nếu có nhập password
        if (password && password.length < 6) {
            alert('Mật khẩu phải có ít nhất 6 ký tự');
            form.matkhau.focus();
            return false;
        }

        // Nếu có nhập email
        if (email && !validateEmail(email)) {
            alert('Email không hợp lệ');
            form.email.focus();
            return false;
        }

        // Nếu có nhập CMND
        if (cmnd && !validateCMND(cmnd)) {
            alert('CMND/CCCD phải có 9-12 chữ số');
            form.cmnd.focus();
            return false;
        }

        // 3. Check duplicates (chỉ check nếu thay đổi)

        // Check duplicate phone (nếu thay đổi)
        if (phone !== originalPhone) {
            const phoneDup = await checkDuplicate('phone', phone);
            if (phoneDup) {
                alert('❌ Số điện thoại đã tồn tại trong hệ thống!');
                form.sodienthoai.focus();
                return false;
            }
        }

        // Check duplicate username (nếu thay đổi và có nhập)
        if (username && username !== originalUsername) {
            const userDup = await checkDuplicate('username', username);
            if (userDup) {
                alert('❌ Tên đăng nhập đã được sử dụng!');
                form.tendangnhap.focus();
                return false;
            }
        }

        // Check duplicate email (nếu thay đổi và có nhập)
        if (email && email !== originalEmail) {
            const emailDup = await checkDuplicate('email', email);
            if (emailDup) {
                alert('❌ Email đã được đăng ký!');
                form.email.focus();
                return false;
            }
        }

        // Check duplicate CMND (nếu thay đổi và có nhập)
        if (cmnd && cmnd !== originalCMND) {
            const cmndDup = await checkDuplicate('cmnd', cmnd);
            if (cmndDup) {
                alert('❌ CMND/CCCD đã tồn tại!');
                form.cmnd.focus();
                return false;
            }
        }

        return true;
    }

    async function submitEditForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('../../controller/letanlogon.controller.php?action=edit', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('✅ Cập nhật khách hàng thành công!');
                const modal = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
                if (modal) modal.hide();
                setTimeout(() => location.reload(), 500);
            } else {
                alert('❌ ' + data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('⚠️ Có lỗi xảy ra khi cập nhật');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    // Thêm event listener reset form khi modal đóng
    document.getElementById('editCustomerModal')?.addEventListener('hidden.bs.modal', function() {
        const form = document.getElementById('editCustomerForm');
        if (form) {
            form.reset();
            // Xóa dữ liệu cũ
            delete form.dataset.originalPhone;
            delete form.dataset.originalEmail;
            delete form.dataset.originalCMND;
            delete form.dataset.originalUsername;
        }
    });

    // ==================== DELETE FUNCTIONS ====================
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

    // ==================== BULK DELETE ====================
    document.addEventListener('change', function(e) {
        if (e.target.id === 'selectAll') {
            document.querySelectorAll('.select-customer').forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
            updateDeleteButton();
        } else if (e.target.classList.contains('select-customer')) {
            updateDeleteButton();
        }
    });

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

    document.getElementById('btnDeleteMultiple')?.addEventListener('click', function() {
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
                    if (data.errors?.length > 0) {
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

    // ==================== FILTER FUNCTIONS ====================
    function filterTable() {
        const keyword = document.getElementById('filterKeyword')?.value.toLowerCase() || '';
        const status = document.getElementById('filterStatus')?.value || '';
        const account = document.getElementById('filterAccount')?.value || '';

        const rows = document.querySelectorAll('tbody tr');
        let visibleCount = 0;

        rows.forEach(row => {
            if (row.classList.contains('text-muted')) return;

            const maKH = row.cells[2]?.textContent.toLowerCase() || '';
            const hoTen = row.cells[3]?.textContent.toLowerCase() || '';
            const sdt = row.cells[4]?.textContent || '';
            const trangThai = row.cells[7]?.querySelector('.badge')?.textContent || '';
            const hasAccount = row.cells[8]?.querySelector('.badge')?.classList.contains('bg-success') || false;

            let match = true;

            // Keyword filter
            if (keyword && !maKH.includes(keyword) && !hoTen.includes(keyword) && !sdt.includes(keyword)) {
                match = false;
            }

            // Status filter
            if (status && trangThai !== status) {
                match = false;
            }

            // Account filter
            if (account !== '') {
                const hasAccountBool = account === '1';
                if (hasAccount !== hasAccountBool) {
                    match = false;
                }
            }

            if (match) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        showFilterResults(visibleCount);
    }

    function showFilterResults(count) {
        const total = <?php echo count($dsKhachHang); ?>;
        const cardText = document.querySelector('.card.bg-primary .card-text');
        if (cardText) {
            cardText.textContent = count;
        }
    }

    function resetFilter() {
        ['filterKeyword', 'filterStatus', 'filterAccount', 'filterSort', 'filterDate'].forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });

        document.querySelectorAll('tbody tr').forEach(row => {
            if (!row.classList.contains('text-muted')) {
                row.style.display = '';
            }
        });

        showFilterResults(<?php echo count($dsKhachHang); ?>);
    }

    // ==================== EVENT LISTENERS ====================
    document.addEventListener('DOMContentLoaded', function() {
        // Filter events
        const filterInputs = ['filterKeyword', 'filterStatus', 'filterAccount', 'filterSort'];
        filterInputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', filterTable);
            }
        });

        // Filter keyword input event
        const filterKeyword = document.getElementById('filterKeyword');
        if (filterKeyword) {
            filterKeyword.addEventListener('input', debounce(filterTable, 300));
            filterKeyword.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') filterTable();
            });
        }

        // Modal reset events
        const addModal = document.getElementById('addCustomerModal');
        const editModal = document.getElementById('editCustomerModal');

        if (addModal) {
            addModal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('addCustomerForm');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                    form.querySelectorAll('.is-invalid').forEach(field => {
                        field.classList.remove('is-invalid');
                    });

                    // Reset manual flag
                    const usernameInput = document.getElementById('tendangnhapInput');
                    if (usernameInput) {
                        delete usernameInput.dataset.manual;
                    }
                }
            });
        }

        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('editCustomerForm');
                if (form) form.reset();
            });
        }
    });
</script>

<?php
// GỌI FOOTER
require_once '../layouts/footer.php';
?>