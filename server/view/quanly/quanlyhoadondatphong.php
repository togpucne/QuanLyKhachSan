<?php
// server/view/quanly/quanlyhoadondatphong.php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['vaitro']) || $_SESSION['vaitro'] !== 'quanly') {
    header('Location: ../login/login.php');
    exit();
}
include_once '../layouts/header.php';

require_once __DIR__ . '/../../model/quanlyhoadondatphong.model.php';
// ============================================

// VIEW: Hiển thị giao diện quản lý hóa đơn
class QuanLyHoaDonView
{

    public static function hienThiDanhSachHTML()
    {
        // Tạo model để lấy dữ liệu
        $model = new QuanLyHoaDonDatPhongModel();
        $hoadon = $model->getAllHoaDon();

        ob_start();
?>
        <!DOCTYPE html>
        <html lang="vi">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Quản lý Hóa đơn - ABC Resort</title>
            <!-- Bootstrap 5 -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body {
                    background-color: #f8f9fa;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                }

                .card {
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    margin-bottom: 20px;
                }

                .card-header {
                    border-radius: 10px 10px 0 0 !important;

                }

                .table th {
                    background-color: #f1f5f9;
                    border-bottom: 2px solid #dee2e6;
                }

                .badge {
                    font-size: 0.8em;
                    padding: 5px 10px;
                }

                .btn-group-sm .btn {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.875rem;
                }

                .total-amount {
                    font-size: 1.2em;
                    font-weight: bold;
                }

                .status-paid {
                    color: #28a745;
                }

                .status-pending {
                    color: #ffc107;
                }
            </style>
        </head>

        <body>
            <div class="py-4">
                <!-- Tiêu đề -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-primary">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Quản lý Hóa đơn Đặt phòng
                    </h2>
                    <div class="d-flex gap-2">

                        <button class="btn btn-primary" onclick="hienThiThongKe()">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê
                        </button>
                    </div>
                </div>

                <!-- Bộ lọc và tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc và Tìm kiếm</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" id="tuNgay" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" id="denNgay" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tìm kiếm</label>
                                <div class="input-group">
                                    <input type="text" id="tuKhoaTim" class="form-control" placeholder="Mã KH, mã phòng, dịch vụ...">
                                    <button class="btn btn-outline-primary" type="button" onclick="timKiemHoaDon()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="locHoaDon()">
                                    <i class="fas fa-filter me-2"></i>Lọc
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê nhanh -->
                <?php
                $doanhThu = $model->getTongDoanhThu();
                $tongHoaDon = count($hoadon);
                $daThanhToan = 0;
                $tongTien = 0;

                foreach ($hoadon as $hd) {
                    if ($hd['TrangThai'] == 'DaThanhToan') {
                        $daThanhToan++;
                        $tongTien += $hd['TongTien'];
                    }
                }
                $chuaThanhToan = $tongHoaDon - $daThanhToan;
                ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Tổng hóa đơn</h6>
                                        <h3 class="mb-0 text-primary"><?php echo $tongHoaDon; ?></h3>
                                    </div>
                                    <div class="bg-primary p-3 rounded">
                                        <i class="fas fa-receipt text-white fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Đã thanh toán</h6>
                                        <h3 class="mb-0 text-success"><?php echo $daThanhToan; ?></h3>
                                    </div>
                                    <div class="bg-success p-3 rounded">
                                        <i class="fas fa-check-circle text-white fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Chưa thanh toán</h6>
                                        <h3 class="mb-0 text-warning"><?php echo $chuaThanhToan; ?></h3>
                                    </div>
                                    <div class="bg-warning p-3 rounded">
                                        <i class="fas fa-clock text-white fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Tổng doanh thu</h6>
                                        <h3 class="mb-0 text-info">
                                            <?php echo number_format($tongTien, 0, ',', '.') . ' đ'; ?>
                                        </h3>
                                    </div>
                                    <div class="bg-info p-3 rounded">
                                        <i class="fas fa-money-bill-wave text-white fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bảng danh sách hóa đơn -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách Hóa đơn</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Mã KH</th>
                                        <th>Mã Phòng</th>
                                        <th>Ngày nhận/trả</th>
                                        <th>Số đêm</th>
                                        <th>Tổng tiền</th>
                                        <th>Phương thức TT</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hoadon as $hd): ?>
                                        <tr>
                                            <td><strong>#<?php echo htmlspecialchars($hd['Id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($hd['MaKhachHang']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($hd['MaPhong']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">Nhận:</small>
                                                <?php echo date('d/m/Y', strtotime($hd['NgayNhan'])); ?>
                                                <br>
                                                <small class="text-muted d-block">Trả:</small>
                                                <?php echo date('d/m/Y', strtotime($hd['NgayTra'])); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($hd['SoDem']); ?> đêm
                                                </span>
                                            </td>
                                            <td class="total-amount text-success">
                                                <?php echo number_format($hd['TongTien'], 0, ',', '.') . ' đ'; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $ptClass = $hd['PhuongThucThanhToan'] == 'TienMat' ? 'bg-primary' : 'bg-success';
                                                ?>
                                                <span class="badge <?php echo $ptClass; ?>">
                                                    <?php echo htmlspecialchars($hd['PhuongThucThanhToan']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = $hd['TrangThai'] == 'DaThanhToan' ? 'bg-success' : 'bg-warning';
                                                $statusIcon = $hd['TrangThai'] == 'DaThanhToan' ? 'fa-check-circle' : 'fa-clock';
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <i class="fas <?php echo $statusIcon; ?> me-1"></i>
                                                    <?php echo htmlspecialchars($hd['TrangThai']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y', strtotime($hd['NgayTao'])); ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($hd['NgayTao'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="xemChiTiet(<?php echo $hd['Id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                    <button class="btn btn-outline-danger" onclick="xoaHoaDon(<?php echo $hd['Id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>

            <!-- Modal chi tiết -->
            <div class="modal fade" id="modalChiTiet" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-file-invoice me-2"></i>Chi tiết Hóa đơn
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="modalChiTietBody">
                            <!-- Nội dung sẽ được load bằng JavaScript -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Đang tải dữ liệu...</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary" onclick="inHoaDon()">
                                <i class="fas fa-print me-2"></i>In hóa đơn
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap JS và dependencies -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            <!-- jQuery (cần cho AJAX) -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

            <script>
                // Các hàm JavaScript
                function xemChiTiet(id) {
                    // Hiển thị modal
                    var modal = new bootstrap.Modal(document.getElementById('modalChiTiet'));
                    modal.show();

                    // Load dữ liệu chi tiết
                    $.ajax({
                        url: '../../controller/quanlyhoadondatphong.controller.php',
                        type: 'GET',
                        data: {
                            action: 'chiTiet',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                var hd = response.data;
                                var html = `
                            <div class="row">
                                <div class="col-md-8">
                                    <h5>Thông tin hóa đơn #${hd.Id}</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Mã khách hàng:</strong></td>
                                            <td>${hd.MaKhachHang}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mã phòng:</strong></td>
                                            <td>${hd.MaPhong}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số người:</strong></td>
                                            <td>${hd.SoNguoi}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày nhận:</strong></td>
                                            <td>${formatDate(hd.NgayNhan)}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày trả:</strong></td>
                                            <td>${formatDate(hd.NgayTra)}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số đêm:</strong></td>
                                            <td>${hd.SoDem}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Thanh toán</h6>
                                            <p><strong>Tổng tiền:</strong><br>
                                            <span class="text-success fs-4">${formatCurrency(hd.TongTien)}</span></p>
                                            <p><strong>Phương thức:</strong><br>
                                            <span class="badge bg-info">${hd.PhuongThucThanhToan}</span></p>
                                            <p><strong>Trạng thái:</strong><br>
                                            <span class="badge ${hd.TrangThai == 'DaThanhToan' ? 'bg-success' : 'bg-warning'}">
                                                ${hd.TrangThai}
                                            </span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6>Chi tiết dịch vụ</h6>
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Dịch vụ</th>
                                            <th>Giá phòng</th>
                                            <th>Tiền dịch vụ</th>
                                            <th>Khuyến mãi</th>
                                            <th>Thuế</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>${hd.TenDichVu || 'Không có'}</td>
                                            <td>${formatCurrency(hd.GiaPhong)}</td>
                                            <td>${formatCurrency(hd.TienDichVu)}</td>
                                            <td>${formatCurrency(hd.TienKhuyenMai)}</td>
                                            <td>${formatCurrency(hd.TienThue)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <p><strong>Yêu cầu đặc biệt:</strong> ${hd.YeuCauDacBiet || 'Không có'}</p>
                                <p><strong>Ngày tạo:</strong> ${formatDateTime(hd.NgayTao)}</p>
                            </div>
                        `;
                                $('#modalChiTietBody').html(html);
                            } else {
                                $('#modalChiTietBody').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                ${response.error || 'Có lỗi xảy ra!'}
                            </div>
                        `);
                            }
                        },
                        error: function() {
                            $('#modalChiTietBody').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Lỗi kết nối đến server!
                        </div>
                    `);
                        }
                    });
                }

                function xoaHoaDon(id) {
                    if (confirm('Bạn có chắc chắn muốn xóa hóa đơn #' + id + '?')) {
                        $.ajax({
                            url: '../../controller/quanlyhoadondatphong.controller.php',
                            type: 'POST',
                            data: JSON.stringify({
                                action: 'xoa',
                                id: id
                            }),
                            contentType: 'application/json',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    alert('Đã xóa hóa đơn thành công!');
                                    location.reload();
                                } else {
                                    alert('Lỗi: ' + (response.error || 'Không thể xóa hóa đơn'));
                                }
                            },
                            error: function() {
                                alert('Lỗi kết nối đến server!');
                            }
                        });
                    }
                }

                function locHoaDon() {
                    var tuNgay = $('#tuNgay').val();
                    var denNgay = $('#denNgay').val();

                    if (!tuNgay || !denNgay) {
                        alert('Vui lòng chọn khoảng thời gian!');
                        return;
                    }

                    $.ajax({
                        url: '../../controller/quanlyhoadondatphong.controller.php',
                        type: 'GET',
                        data: {
                            action: 'loc',
                            tu_ngay: tuNgay,
                            den_ngay: denNgay
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Tìm thấy ' + response.data.length + ' hóa đơn');
                                // Cập nhật bảng ở đây (cần thêm hàm cập nhật bảng)
                            } else {
                                alert('Lỗi: ' + (response.error || 'Không thể lọc dữ liệu'));
                            }
                        }
                    });
                }

                function timKiemHoaDon() {
                    var tuKhoa = $('#tuKhoaTim').val();

                    if (!tuKhoa.trim()) {
                        alert('Vui lòng nhập từ khóa tìm kiếm!');
                        return;
                    }

                    $.ajax({
                        url: '../../controller/quanlyhoadondatphong.controller.php',
                        type: 'GET',
                        data: {
                            action: 'timKiem',
                            tu_khoa: tuKhoa
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Tìm thấy ' + response.data.length + ' kết quả');
                                // Cập nhật bảng ở đây
                            } else {
                                alert('Lỗi: ' + (response.error || 'Không thể tìm kiếm'));
                            }
                        }
                    });
                }

                function hienThiThongKe() {
                    $.ajax({
                        url: '../../controller/quanlyhoadondatphong.controller.php',
                        type: 'GET',
                        data: {
                            action: 'doanhThu'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                var stats = response.data;
                                var html = `
                            <div class="alert alert-info">
                                <h5><i class="fas fa-chart-bar me-2"></i>Thống kê Doanh thu</h5>
                                <hr>
                                <p><strong>Tổng doanh thu:</strong> ${formatCurrency(stats.TongDoanhThu || 0)}</p>
                                <p><strong>Số hóa đơn:</strong> ${stats.SoHoaDon || 0}</p>
                                <p><strong>Trung bình/hóa đơn:</strong> ${formatCurrency(stats.TrungBinh || 0)}</p>
                            </div>
                        `;
                                alert(html.replace(/<[^>]*>/g, '')); // Hiển thị thông báo đơn giản
                            }
                        }
                    });
                }


                function formatDate(dateString) {
                    if (!dateString) return '';
                    var date = new Date(dateString);
                    return date.toLocaleDateString('vi-VN');
                }

                function formatDateTime(dateTimeString) {
                    if (!dateTimeString) return '';
                    var date = new Date(dateTimeString);
                    return date.toLocaleString('vi-VN');
                }

                function formatCurrency(amount) {
                    if (!amount) return '0 đ';
                    return new Intl.NumberFormat('vi-VN', {
                        style: 'currency',
                        currency: 'VND'
                    }).format(amount).replace('₫', 'đ');
                }
            </script>
        </body>

        </html>
<?php
        return ob_get_clean();
    }
}

// Hiển thị trang
echo QuanLyHoaDonView::hienThiDanhSachHTML();
include_once '../layouts/footer.php';
?>