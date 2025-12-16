<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/model/connectDB.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Vui lòng đăng nhập để xem chi tiết.</div>';
    exit();
}

// Lấy ID hóa đơn từ GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">ID hóa đơn không hợp lệ.</div>';
    exit();
}

$bookingId = intval($_GET['id']);
$userId = $_SESSION['user_id'];

// DEBUG: Kiểm tra session
// echo "<!-- DEBUG: User ID: $userId -->";

// Kết nối database
$connect = new Connect();
$conn = $connect->openConnect();

if (!$conn) {
    echo '<div class="alert alert-danger">Lỗi kết nối database.</div>';
    exit();
}

// Lấy MaKH của user từ bảng khachhang
$sqlCustomer = "SELECT MaKH FROM khachhang WHERE MaTaiKhoan = ?";
$stmtCustomer = $conn->prepare($sqlCustomer);

if (!$stmtCustomer) {
    echo '<div class="alert alert-danger">Lỗi prepare statement: ' . $conn->error . '</div>';
    $connect->closeConnect($conn);
    exit();
}

$stmtCustomer->bind_param("s", $userId);
$stmtCustomer->execute();
$resultCustomer = $stmtCustomer->get_result();

if ($resultCustomer->num_rows > 0) {
    $customer = $resultCustomer->fetch_assoc();
    $maKhachHang = $customer['MaKH'];
    
    // DEBUG: Kiểm tra MaKH
    // echo "<!-- DEBUG: MaKH: $maKhachHang -->";
    
    // Lấy chi tiết hóa đơn với kiểm tra chủ sở hữu
    $sql = "SELECT h.*, 
                   p.SoPhong, p.roomName, p.DienTich, p.HuongNha,
                   lp.HangPhong, lp.HinhThuc,
                   k.HoTen, k.SoDienThoai, k.DiaChi
            FROM hoadondatphong h 
            LEFT JOIN phong p ON h.MaPhong = p.MaPhong 
            LEFT JOIN loaiphong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            LEFT JOIN khachhang k ON h.MaKhachHang = k.MaKH
            WHERE h.Id = ? AND h.MaKhachHang = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo '<div class="alert alert-danger">Lỗi prepare statement: ' . $conn->error . '</div>';
    } else {
        $stmt->bind_param("is", $bookingId, $maKhachHang);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            
            // DEBUG: Kiểm tra dữ liệu
            // echo "<!-- DEBUG: Found booking ID: " . $booking['Id'] . " -->";
            
            // Format dữ liệu
            $ngayNhan = date('d/m/Y H:i', strtotime($booking['NgayNhan']));
            $ngayTra = date('d/m/Y H:i', strtotime($booking['NgayTra']));
            $ngayTao = date('d/m/Y H:i', strtotime($booking['NgayTao']));
            
            // Xử lý JSON DanhSachKhach
            $danhSachKhach = '';
            if (!empty($booking['DanhSachKhach'])) {
                try {
                    $khachArray = json_decode($booking['DanhSachKhach'], true);
                    if (is_array($khachArray)) {
                        $danhSachKhach = '<ul class="list-unstyled mb-0">';
                        foreach ($khachArray as $khach) {
                            $danhSachKhach .= '<li>';
                            $danhSachKhach .= '<strong>' . htmlspecialchars($khach['HoTen'] ?? '') . '</strong>';
                            if (!empty($khach['SoDienThoai'])) {
                                $danhSachKhach .= ' - ' . htmlspecialchars($khach['SoDienThoai']);
                            }
                            $danhSachKhach .= '</li>';
                        }
                        $danhSachKhach .= '</ul>';
                    } else {
                        $danhSachKhach = htmlspecialchars($booking['DanhSachKhach']);
                    }
                } catch (Exception $e) {
                    $danhSachKhach = htmlspecialchars($booking['DanhSachKhach']);
                }
            }
            
            ?>
            <div class="invoice-detail">
                <!-- Thông tin khách hàng -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5><i class="fas fa-user me-2"></i>Thông tin khách hàng</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Họ tên:</strong></td>
                                <td><?php echo htmlspecialchars($booking['HoTen'] ?? 'Chưa có'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>SĐT:</strong></td>
                                <td><?php echo htmlspecialchars($booking['SoDienThoai'] ?? 'Chưa có'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Địa chỉ:</strong></td>
                                <td><?php echo htmlspecialchars($booking['DiaChi'] ?? 'Chưa có'); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-file-invoice me-2"></i>Thông tin hóa đơn</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Mã hóa đơn:</strong></td>
                                <td><span class="badge bg-secondary">#<?php echo str_pad($booking['Id'], 6, '0', STR_PAD_LEFT); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Ngày đặt:</strong></td>
                                <td><?php echo $ngayTao; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Trạng thái:</strong></td>
                                <td>
                                    <?php if ($booking['TrangThai'] == 'DaThanhToan'): ?>
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>PT thanh toán:</strong></td>
                                <td><?php echo htmlspecialchars($booking['PhuongThucThanhToan'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Thông tin phòng -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5><i class="fas fa-bed me-2"></i>Thông tin phòng</h5>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tên phòng:</strong> <?php echo htmlspecialchars($booking['roomName'] ?? 'Chưa có'); ?></p>
                                        <p><strong>Số phòng:</strong> <?php echo htmlspecialchars($booking['SoPhong'] ?? 'Chưa có'); ?></p>
                                        <p><strong>Hạng phòng:</strong> <?php echo htmlspecialchars($booking['HangPhong'] ?? 'Chưa có'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Diện tích:</strong> <?php echo $booking['DienTich'] ?? 'Chưa có'; ?> m²</p>
                                        <p><strong>Hướng:</strong> <?php echo htmlspecialchars($booking['HuongNha'] ?? 'Chưa có'); ?></p>
                                        <p><strong>Hình thức:</strong> <?php echo htmlspecialchars($booking['HinhThuc'] ?? 'Chưa có'); ?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-0"><strong>Ngày nhận:</strong> <?php echo $ngayNhan; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-0"><strong>Ngày trả:</strong> <?php echo $ngayTra; ?></p>
                                    </div>
                                </div>
                                <p class="mt-2 mb-0"><strong>Số đêm:</strong> <span class="badge bg-info"><?php echo $booking['SoDem']; ?> đêm</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Danh sách khách -->
                <?php if (!empty($danhSachKhach)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5><i class="fas fa-users me-2"></i>Danh sách khách</h5>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <p><strong>Tổng số người:</strong> <span class="badge bg-primary"><?php echo $booking['SoNguoi']; ?> người</span></p>
                                <?php echo $danhSachKhach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Yêu cầu đặc biệt -->
                <?php if (!empty($booking['YeuCauDacBiet'])): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5><i class="fas fa-star me-2"></i>Yêu cầu đặc biệt</h5>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($booking['YeuCauDacBiet'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Dịch vụ kèm theo -->
                <?php if (!empty($booking['TenDichVu'])): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5><i class="fas fa-concierge-bell me-2"></i>Dịch vụ kèm theo</h5>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <p class="mb-1"><strong>Tên dịch vụ:</strong> <?php echo htmlspecialchars($booking['TenDichVu']); ?></p>
                                <?php if (!empty($booking['MaDichVu'])): ?>
                                <p class="mb-0"><strong>Mã dịch vụ:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($booking['MaDichVu']); ?></span></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Chi tiết thanh toán -->
                <div class="row">
                    <div class="col-12">
                        <h5><i class="fas fa-money-bill-wave me-2"></i>Chi tiết thanh toán</h5>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <tbody>
                                        <tr>
                                            <td width="70%"><strong>Giá phòng (<?php echo $booking['SoDem']; ?> đêm)</strong></td>
                                            <td align="right"><?php echo number_format($booking['GiaPhong'], 0, ',', '.'); ?> VND</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tiền dịch vụ</strong></td>
                                            <td align="right"><?php echo number_format($booking['TienDichVu'], 0, ',', '.'); ?> VND</td>
                                        </tr>
                                        <?php if ($booking['TienKhuyenMai'] > 0): ?>
                                        <tr>
                                            <td><strong>Tiền khuyến mãi</strong></td>
                                            <td align="right" class="text-success">- <?php echo number_format($booking['TienKhuyenMai'], 0, ',', '.'); ?> VND</td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if ($booking['TienThue'] > 0): ?>
                                        <tr>
                                            <td><strong>Tiền thuế</strong></td>
                                            <td align="right"><?php echo number_format($booking['TienThue'], 0, ',', '.'); ?> VND</td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="table-active">
                                            <td><h5 class="mb-0"><strong>TỔNG CỘNG</strong></h5></td>
                                            <td align="right">
                                                <h4 class="mb-0 text-success">
                                                    <strong><?php echo number_format($booking['TongTien'], 0, ',', '.'); ?> VND</strong>
                                                </h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
               
            </div>
            <?php
        } else {
            echo '<div class="alert alert-warning text-center">';
            echo '<i class="fas fa-exclamation-triangle fa-2x mb-3"></i><br>';
            echo '<h5>Không tìm thấy thông tin hóa đơn</h5>';
            echo '<p class="mb-0">Hóa đơn không tồn tại hoặc bạn không có quyền xem.</p>';
            echo '</div>';
        }
        
        $stmt->close();
    }
} else {
    echo '<div class="alert alert-danger text-center">';
    echo '<i class="fas fa-user-times fa-2x mb-3"></i><br>';
    echo '<h5>Không tìm thấy thông tin khách hàng</h5>';
    echo '<p class="mb-0">Vui lòng kiểm tra thông tin tài khoản.</p>';
    echo '</div>';
}

$stmtCustomer->close();
$connect->closeConnect($conn);
?>