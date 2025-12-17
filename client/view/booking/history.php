<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/model/connectDB.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Lấy MaKhachHang từ session
$userId = $_SESSION['user_id'];

// Kết nối database
$connect = new Connect();
$conn = $connect->openConnect();

// Lấy thông tin khách hàng
$sqlCustomer = "SELECT MaKH, HoTen FROM khachhang WHERE MaTaiKhoan = ?";
$stmtCustomer = $conn->prepare($sqlCustomer);
$stmtCustomer->bind_param("s", $userId);
$stmtCustomer->execute();
$resultCustomer = $stmtCustomer->get_result();

$customerName = "";
$bookings = [];

if ($resultCustomer->num_rows > 0) {
    $customer = $resultCustomer->fetch_assoc();
    $maKhachHang = $customer['MaKH'];
    $customerName = $customer['HoTen'];

    // Lấy lịch sử đặt phòng
    $sql = "SELECT h.*, p.SoPhong, p.roomName, lp.HangPhong
            FROM hoadondatphong h 
            LEFT JOIN phong p ON h.MaPhong = p.MaPhong 
            LEFT JOIN loaiphong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            WHERE h.MaKhachHang = ? 
            ORDER BY h.NgayTao DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $maKhachHang);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }

    $stmt->close();
}

$stmtCustomer->close();
$connect->closeConnect($conn);

// Include header
include __DIR__ . '/../layouts/header.php';
?>

<style>
    /* Thêm vào phần CSS */
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    .fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .btn-danger:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<div class="container booking-history-container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title">
                <i class="fas fa-history me-2"></i>Lịch Sử Đặt Phòng
                <?php if ($customerName): ?>
                    <small class="text-muted fs-5"> - <?php echo htmlspecialchars($customerName); ?></small>
                <?php endif; ?>
            </h1>
        </div>
    </div>

    <!-- Thông báo -->
    <div id="messageContainer"></div>

    <!-- Thống kê -->
    <?php if (!empty($bookings)): ?>
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="total-summary">
                    <div class="summary-item">
                        <div class="summary-value"><?php echo count($bookings); ?></div>
                        <div class="summary-label">Tổng số đơn</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="total-summary">
                    <?php
                    $totalPaid = 0;
                    foreach ($bookings as $booking) {
                        if ($booking['TrangThai'] == 'DaThanhToan') {
                            $totalPaid += $booking['TongTien'];
                        }
                    }
                    ?>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo number_format($totalPaid); ?></div>
                        <div class="summary-label">Tổng tiền</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="total-summary">
                    <?php
                    $paidCount = 0;
                    foreach ($bookings as $booking) {
                        if ($booking['TrangThai'] == 'DaThanhToan') {
                            $paidCount++;
                        }
                    }
                    ?>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo $paidCount; ?></div>
                        <div class="summary-label">Đã thanh toán</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="total-summary">
                    <?php
                    $pendingCount = 0;
                    foreach ($bookings as $booking) {
                        if ($booking['TrangThai'] == 'ChuaThanhToan') {
                            $pendingCount++;
                        }
                    }
                    ?>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo $pendingCount; ?></div>
                        <div class="summary-label">Chưa thanh toán</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Danh sách hóa đơn -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <div class="no-bookings-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h4 class="text-muted">Chưa có đơn đặt phòng nào</h4>
                    <p class="text-muted">Bạn chưa có lịch sử đặt phòng nào tại ABC Resort.</p>
                    <a href="../../index.php" class="btn btn-primary mt-3">
                        <i class="fas fa-bed me-2"></i>Đặt phòng ngay
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Phòng</th>
                                <th>Ngày nhận/trả</th>
                                <th>Số đêm</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr id="booking-row-<?php echo $booking['Id']; ?>">
                                    <td>#<?php echo str_pad($booking['Id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['roomName']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['HangPhong']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($booking['NgayNhan'])); ?><br>
                                        <small>→ <?php echo date('d/m/Y', strtotime($booking['NgayTra'])); ?></small>
                                    </td>
                                    <td><?php echo $booking['SoDem']; ?> đêm</td>
                                    <td class="price-highlight"><?php echo number_format($booking['TongTien']); ?> VND</td>
                                    <td>
                                        <?php if ($booking['TrangThai'] == 'DaThanhToan'): ?>
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Chưa thanh toán</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($booking['NgayTao'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info me-2"
                                            onclick="viewBookingDetail(<?php echo $booking['Id']; ?>)"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal">
                                            <i class="fas fa-eye"></i> Xem
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="deleteBooking(<?php echo $booking['Id']; ?>)">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Chi Tiết -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Chi Tiết Hóa Đơn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Nội dung sẽ được load bằng AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Đang tải thông tin...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="printBooking()">
                    <i class="fas fa-print me-2"></i>In hóa đơn
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .booking-history-container {
        min-height: 80vh;
        padding-bottom: 40px;
    }

    .page-title {
        color: #333;
        font-weight: 700;
        margin-bottom: 30px;
        position: relative;
        padding-bottom: 15px;
    }

    .page-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }

    .total-summary {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }

    .total-summary:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .summary-item {
        text-align: center;
        padding: 10px;
    }

    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 5px;
    }

    .summary-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .no-bookings {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .no-bookings-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .price-highlight {
        color: #28a745;
        font-weight: 700;
    }

    .table th {
        background-color: #2c3e50;
        color: white;
        border: none;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:hover {
        background-color: #138496;
        border-color: #117a8b;
    }

    #messageContainer {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 350px;
    }

    .alert {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: none;
    }
</style>

<script>
    // Hiển thị thông báo
    function showMessage(message, type = 'success') {
        const messageContainer = document.getElementById('messageContainer');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        messageContainer.innerHTML = '';
        messageContainer.appendChild(alertDiv);

        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.classList.remove('show');
                setTimeout(() => {
                    if (alertDiv.parentElement) {
                        alertDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Sửa hàm viewBookingDetail
    function viewBookingDetail(bookingId) {
        const modalBody = document.getElementById('detailModalBody');
        modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải thông tin...</p>
        </div>
    `;

        // SỬA ĐƯỜNG DẪN Ở ĐÂY
        const url = window.location.pathname.includes('booking') ?
            'get_booking_detail.php' :
            'booking/get_booking_detail.php';

        // Gọi AJAX để lấy chi tiết
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `${url}?id=${bookingId}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                modalBody.innerHTML = xhr.responseText;
            } else {
                modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Lỗi khi tải thông tin. Vui lòng thử lại.<br>
                    <small>Mã lỗi: ${xhr.status}</small>
                </div>
            `;
            }
        };

        xhr.onerror = function() {
            modalBody.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Không thể kết nối đến server. Vui lòng kiểm tra đường dẫn.
            </div>
        `;
        };

        xhr.send();
    }

    // Sửa hàm deleteBooking
    function deleteBooking(bookingId) {
        if (!confirm('Bạn có chắc chắn muốn xóa hóa đơn này?\n\n⚠️ Lưu ý: Chỉ có thể xóa hóa đơn CHƯA THANH TOÁN.\nPhòng sẽ được cập nhật lại trạng thái "Trống".')) {
            return;
        }

        // Đường dẫn đúng
        const url = 'delete_booking.php';

        // Gọi AJAX để xóa
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Hiển thị loading
        const row = document.getElementById(`booking-row-${bookingId}`);
        if (row) {
            const deleteBtn = row.querySelector('.btn-danger');
            if (deleteBtn) {
                const originalHtml = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
                deleteBtn.disabled = true;
            }
        }

        xhr.onload = function() {
            // Khôi phục nút
            if (row) {
                const deleteBtn = row.querySelector('.btn-danger');
                if (deleteBtn) {
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Xóa';
                    deleteBtn.disabled = false;
                }
            }

            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Xóa hàng khỏi bảng
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(100px)';
                            setTimeout(() => {
                                row.remove();
                                showMessage('✅ Đã xóa hóa đơn thành công! Phòng đã được cập nhật trạng thái.', 'success');

                                // Cập nhật lại thống kê sau 1 giây
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            }, 300);
                        }
                    } else {
                        showMessage('❌ ' + response.message, 'danger');
                    }
                } catch (e) {
                    showMessage('❌ Lỗi xử lý phản hồi từ server', 'danger');
                }
            } else {
                showMessage('❌ Lỗi kết nối đến server', 'danger');
            }
        };

        xhr.onerror = function() {
            // Khôi phục nút
            if (row) {
                const deleteBtn = row.querySelector('.btn-danger');
                if (deleteBtn) {
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Xóa';
                    deleteBtn.disabled = false;
                }
            }
            showMessage('❌ Không thể kết nối đến server', 'danger');
        };

        xhr.send(`id=${bookingId}`);
    }
    // In hóa đơn
    function printBooking() {
        const modalBody = document.getElementById('detailModalBody');
        const printContent = modalBody.innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload(); // Reload để khôi phục
    }

    // Đóng modal khi click ra ngoài
    document.getElementById('detailModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('detailModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Đang tải thông tin...</p>
        </div>
    `;
    });
</script>

<?php
// Include footer
include __DIR__ . '/../layouts/footer.php';
?>