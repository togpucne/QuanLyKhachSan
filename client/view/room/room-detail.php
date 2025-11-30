<?php
include __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../../model/connectDB.php';

// Lấy MaPhong từ URL
$maPhong = $_GET['id'] ?? 0;

// Lấy thông tin ngày và số người từ URL
$checkin = $_GET['checkin'] ?? date('Y-m-d');
$checkout = $_GET['checkout'] ?? date('Y-m-d', strtotime('+1 day'));
$adults = $_GET['adults'] ?? 1;
$nights = $_GET['nights'] ?? 1;

// Tính số đêm nếu không có từ URL
if (!isset($_GET['nights']) && $checkin && $checkout) {
    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    $interval = $checkinDate->diff($checkoutDate);
    $nights = $interval->days ?: 1;
}

if (!$maPhong) {
    header('Location: index.php');
    exit;
}

// Kết nối database
$connect = new Connect();
$conn = $connect->openConnect();

// Lấy thông tin phòng
$sqlPhong = "SELECT p.*, lp.HangPhong, lp.HinhThuc 
             FROM Phong p 
             LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
             WHERE p.MaPhong = ?";
$stmt = $conn->prepare($sqlPhong);
$stmt->bind_param("i", $maPhong);
$stmt->execute();
$result = $stmt->get_result();
$phong = $result->fetch_assoc();

if (!$phong) {
    die("Phòng không tồn tại");
}

// Lấy thiết bị của phòng (chỉ thiết bị tốt)
$sqlThietBi = "SELECT TenThietBi FROM ThietBi WHERE MaPhong = ? AND TinhTrang = 'Tốt'";
$stmtThietBi = $conn->prepare($sqlThietBi);
$stmtThietBi->bind_param("i", $maPhong);
$stmtThietBi->execute();
$thietBiResult = $stmtThietBi->get_result();
$thietBiList = [];
while ($row = $thietBiResult->fetch_assoc()) {
    $thietBiList[] = $row['TenThietBi'];
}

// Lấy danh sách dịch vụ khả dụng
$sqlDichVu = "SELECT * FROM dichvu WHERE TrangThai = 'Khả dụng' ORDER BY LoaiDV, TenDV";
$resultDichVu = $conn->query($sqlDichVu);
$dichVuList = [];
$dichVuTheoLoai = [];

if ($resultDichVu && $resultDichVu->num_rows > 0) {
    while ($row = $resultDichVu->fetch_assoc()) {
        $dichVuList[] = $row;
        $loaiDV = $row['LoaiDV'];
        if (!isset($dichVuTheoLoai[$loaiDV])) {
            $dichVuTheoLoai[$loaiDV] = [];
        }
        $dichVuTheoLoai[$loaiDV][] = $row;
    }
}

// Xử lý ảnh từ DanhSachPhong
$danhSachAnh = json_decode($phong['DanhSachPhong'], true) ?: [$phong['Avatar']];

// Hàm lấy đường dẫn ảnh đúng
function getRoomImagePath($imagePath)
{
    if (empty($imagePath)) {
        return '/ABC-Resort/client/assets/images/default-room.jpg';
    }

    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }

    $basePath = '/ABC-Resort/client/assets/images/rooms/';
    $extensions = ['.jpeg', '.jpg', '.png', '.webp', '.gif'];

    foreach ($extensions as $ext) {
        $fullPath = $basePath . $imagePath . $ext;
        return $fullPath;
    }

    return $basePath . $imagePath;
}

// Xử lý tiện nghi
$tienNghi = json_decode($phong['TienNghi'] ?? '[]', true) ?: [];

// Đóng kết nối
$connect->closeConnect($conn);
?>

<style>
    .resort-intro {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid #e9ecef;
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .room-feature-icon {
        width: 36px;
        height: 36px;
        background: #f8f9fa;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .carousel-thumb {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .carousel-thumb:hover {
        transform: scale(1.05);
        border-color: #495057 !important;
    }

    .carousel-thumb.active {
        border-color: #495057 !important;
        transform: scale(1.05);
    }

    .room-info-card {
        border: 1px solid #e9ecef;
    }

    .service-card {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .service-card:hover {
        border-color: #0d6efd;
    }

    .service-card.selected {
        border-color: #0d6efd;
        background-color: #f8f9ff;
    }

    .service-checkbox {
        display: none;
    }

    .price-breakdown {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
    }

    .total-price {
        font-size: 1.4rem;
        font-weight: bold;
        color: #dc3545;
    }

    .section-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #0d6efd;
    }
</style>

<!-- Phần giới thiệu resort - GIỮ NGUYÊN -->
<section class="resort-intro py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h2 fw-bold text-dark mb-4">Tỏa Sáng Resort Nha Trang</h1>
                <p class="text-muted mb-4">Khu nghỉ dưỡng 5 sao với view biển tuyệt đẹp, tọa lạc tại trung tâm thành phố biển Nha Trang. Trải nghiệm sự hoàn hảo giữa thiên nhiên và tiện nghi hiện đại.</p>

                <div class="resort-features mb-4">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon me-3">
                                    <i class="fas fa-wifi text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">WiFi miễn phí</div>
                                    <small class="text-muted">Toàn khu nghỉ dưỡng</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon me-3">
                                    <i class="fas fa-swimming-pool text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Hồ bơi vô cực</div>
                                    <small class="text-muted">View biển</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon me-3">
                                    <i class="fas fa-utensils text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Nhà hàng</div>
                                    <small class="text-muted">Ẩm thực đa dạng</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon me-3">
                                    <i class="fas fa-spa text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Spa & Massage</div>
                                    <small class="text-muted">Thư giãn tuyệt đối</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-info">
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span>123 Bãi biển Nha Trang, Khánh Hòa</span>
                    </div>
                    <div class="d-flex align-items-center text-muted mt-2">
                        <i class="fas fa-phone me-2"></i>
                        <span>+84 258 123 456</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Hiển thị ảnh resort khoa học -->
                <div class="resort-gallery">
                    <div class="row g-2">
                        <div class="col-8">
                            <!-- Carousel cho resort1 và resort4 -->
                            <div id="resortCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="0" class="active"></button>
                                    <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="1"></button>
                                </div>
                                <div class="carousel-inner rounded">
                                    <div class="carousel-item active">
                                        <img src="/ABC-Resort/client/assets/images/resort/resort1.jpg"
                                            class="d-block w-100 img-fluid"
                                            alt="Tỏa Sáng Resort chính"
                                            style="height: 200px; object-fit: cover;"
                                            onerror="this.src='/ABC-Resort/client/assets/images/default-resort.jpg'">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="/ABC-Resort/client/assets/images/resort/resort4.jpg"
                                            class="d-block w-100 img-fluid"
                                            alt="Tỏa Sáng Resort 4"
                                            style="height: 200px; object-fit: cover;"
                                            onerror="this.src='/ABC-Resort/client/assets/images/default-resort.jpg'">
                                    </div>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#resortCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#resortCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="row g-2 h-100">
                                <div class="col-12">
                                    <img src="/ABC-Resort/client/assets/images/resort/resort2.jpg"
                                        class="img-fluid rounded w-100"
                                        alt="Tỏa Sáng Resort 2"
                                        style="height: 96px; object-fit: cover;"
                                        onerror="this.src='/ABC-Resort/client/assets/images/default-resort.jpg'">
                                </div>
                                <div class="col-12">
                                    <img src="/ABC-Resort/client/assets/images/resort/resort3.jpg"
                                        class="img-fluid rounded w-100"
                                        alt="Tỏa Sáng Resort 3"
                                        style="height: 96px; object-fit: cover;"
                                        onerror="this.src='/ABC-Resort/client/assets/images/default-resort.jpg'">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần mô tả resort -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="resort-description bg-light rounded p-4">
                    <p class="text-muted mb-0" style="line-height: 1.7;">
                        Tỏa Sáng Resort Nha Trang là khu nghỉ dưỡng cao cấp nằm ở vị trí đắc địa tại trung tâm thành phố biển Nha Trang.
                        Quầy lễ tân 24/24 luôn sẵn sàng phục vụ quý khách, từ thủ tục nhận phòng đến trả phòng, hoặc bất kỳ sự hỗ trợ nào bạn cần.
                        Nếu bạn mong muốn nhiều hơn, đừng ngần ngại liên hệ với quầy lễ tân, chúng tôi luôn sẵn sàng đáp ứng mọi nhu cầu của bạn.
                        WiFi miễn phí có sẵn tại các khu vực công cộng của resort, giúp bạn luôn kết nối với gia đình và bạn bè.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chi tiết phòng -->
<section class="room-detail py-4">
    <div class="container">
        <div class="row">
            <!-- Bên trái - Hình ảnh phòng -->
            <div class="col-lg-8">
                <!-- Ảnh chính lớn -->
                <div class="main-image mb-3">
                    <img id="mainImage" src="<?php echo getRoomImagePath($danhSachAnh[0]); ?>"
                        class="img-fluid rounded w-100"
                        alt="<?php echo htmlspecialchars($phong['roomName']); ?>"
                        style="height: 400px; object-fit: cover;"
                        onerror="this.onerror=null; this.src='/ABC-Resort/client/assets/images/default-room.jpg'">
                </div>

                <!-- Carousel ảnh nhỏ -->
                <div class="image-carousel mb-4">
                    <div class="d-flex overflow-auto pb-2" style="gap: 10px;">
                        <?php foreach ($danhSachAnh as $index => $anh): ?>
                            <img src="<?php echo getRoomImagePath($anh); ?>"
                                class="carousel-thumb rounded cursor-pointer <?php echo $index === 0 ? 'active' : ''; ?>"
                                alt="Ảnh <?php echo $index + 1; ?>"
                                style="width: 100px; height: 75px; object-fit: cover; border: 2px solid transparent;"
                                onclick="changeMainImage(this.src, this)"
                                onerror="this.onerror=null; this.src='/ABC-Resort/client/assets/images/default-room.jpg'">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Thông tin chi tiết phòng -->
                <div class="room-description-card bg-white rounded p-4 mb-4">
                    <h4 class="fw-semibold mb-3">Thông tin chi tiết</h4>
                    <div class="room-description" style="line-height: 1.7;">
                        <?php if (!empty($phong['MoTaChiTiet'])): ?>
                            <?php echo nl2br(htmlspecialchars($phong['MoTaChiTiet'])); ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Thông tin chi tiết về phòng đang được cập nhật. Quý khách vui lòng liên hệ bộ phận lễ tân để biết thêm thông tin.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tiện nghi & Thiết bị -->
                <div class="row">
                    <!-- Tiện nghi phòng -->
                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded p-4 h-100">
                            <h5 class="fw-semibold mb-3">Tiện nghi phòng</h5>
                            <div>
                                <?php if (!empty($tienNghi)): ?>
                                    <?php foreach ($tienNghi as $tienNghiItem): ?>
                                        <div class="d-flex align-items-center text-muted mb-2">
                                            <i class="fas fa-check me-2 text-muted"></i>
                                            <span><?php echo htmlspecialchars($tienNghiItem); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted">
                                        Đang cập nhật thông tin tiện nghi...
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Thiết bị -->
                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded p-4 h-100">
                            <h5 class="fw-semibold mb-3">Thiết bị có sẵn</h5>
                            <div>
                                <?php if (!empty($thietBiList)): ?>
                                    <?php foreach ($thietBiList as $thietBi): ?>
                                        <div class="d-flex align-items-center text-muted mb-2">
                                            <i class="fas fa-check me-2 text-muted"></i>
                                            <span><?php echo htmlspecialchars($thietBi); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted">
                                        Đang cập nhật thiết bị...
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bên phải - Thông tin đặt phòng -->
            <div class="col-lg-4">
                <div class="room-info-card bg-white rounded p-4">
                    <h3 class="h4 fw-semibold text-dark mb-3"><?php echo htmlspecialchars($phong['roomName']); ?></h3>

                    <!-- Thông tin cơ bản -->
                    <div class="basic-info mb-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="room-feature-icon me-2">
                                        <i class="fas fa-ruler-combined text-muted small"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?php echo $phong['DienTich']; ?> m²</div>
                                        <small class="text-muted">Diện tích</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="room-feature-icon me-2">
                                        <i class="fas fa-users text-muted small"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?php echo $phong['SoKhachToiDa']; ?> khách</div>
                                        <small class="text-muted">Sức chứa</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="room-feature-icon me-2">
                                        <i class="fas fa-compass text-muted small"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?php echo htmlspecialchars($phong['HuongNha']); ?></div>
                                        <small class="text-muted">Hướng</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="room-feature-icon me-2">
                                        <i class="fas fa-bed text-muted small"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?php echo htmlspecialchars($phong['HangPhong']); ?></div>
                                        <small class="text-muted">Hạng phòng</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dịch vụ bổ sung -->
                    <div class="services-section mb-3">
                        <h6 class="fw-semibold mb-2">Dịch vụ bổ sung</h6>
                        <div class="services-list" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($dichVuList)): ?>
                                <?php foreach ($dichVuTheoLoai as $loaiDV => $dichVus): ?>
                                    <small class="text-muted d-block mt-2 mb-1"><?php echo htmlspecialchars($loaiDV); ?></small>
                                    <?php foreach ($dichVus as $dichVu): ?>
                                        <div class="service-card p-2 mb-1" onclick="toggleService(this, <?php echo $dichVu['MaDV']; ?>, <?php echo $dichVu['DonGia']; ?>)">
                                            <div class="form-check mb-0">
                                                <input type="checkbox" class="form-check-input service-checkbox"
                                                    id="service-<?php echo $dichVu['MaDV']; ?>"
                                                    value="<?php echo $dichVu['MaDV']; ?>"
                                                    data-price="<?php echo $dichVu['DonGia']; ?>">
                                                <label class="form-check-label w-100 mb-0" for="service-<?php echo $dichVu['MaDV']; ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="small"><?php echo htmlspecialchars($dichVu['TenDV']); ?></div>
                                                        </div>
                                                        <div class="text-end">
                                                            <div class="small fw-semibold"><?php echo number_format($dichVu['DonGia']); ?> đ</div>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($dichVu['MoTa']); ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted text-center py-2">
                                    <small>Hiện không có dịch vụ nào khả dụng</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Giá và đặt phòng -->
                    <div class="booking-section">
                        <div class="price-section mb-3">
                            <!-- Hiển thị thông tin đặt phòng -->
                            <div class="booking-info mb-3 p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2"><strong>Thông tin đặt phòng</strong></small>
                                <div class="d-flex justify-content-between">
                                    <small><strong>Ngày đến:</strong><br><?php echo date('d/m/Y', strtotime($checkin)); ?></small>
                                    <small><strong>Ngày đi:</strong><br><?php echo date('d/m/Y', strtotime($checkout)); ?></small>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <small><strong>Số đêm:</strong> <?php echo $nights; ?></small>
                                    <small><strong>Số người:</strong> <?php echo $adults; ?></small>
                                </div>
                            </div>

                            <div class="price-breakdown">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Giá <?php echo $nights; ?> đêm:</small>
                                    <small class="text-muted" id="roomPriceTotal"><?php echo number_format($phong['TongGia'] * $nights); ?> đ</small>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Dịch vụ:</small>
                                    <small class="text-muted" id="servicesPrice">0 đ</small>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <div>
                                        <small class="text-muted d-block mb-1">Tổng cộng:</small>
                                        <div class="fw-bold fs-5" id="totalPrice" style="color: #dc3545;"><?php echo number_format($phong['TongGia'] * $nights); ?> đ</div>
                                    </div>
                                    <small class="text-muted">/ <?php echo $nights; ?> đêm</small>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-dark w-100 py-2 fw-semibold" onclick="bookRoom(<?php echo $phong['MaPhong']; ?>)">
                            Đặt Phòng Ngay
                        </button>
                        <div class="text-center mt-2">
                            <small class="text-muted">Miễn phí hủy phòng trong 24 giờ</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .room-feature-icon {
        width: 36px;
        height: 36px;
        background: #f8f9fa;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .carousel-thumb {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .carousel-thumb:hover {
        transform: scale(1.05);
        border-color: #495057 !important;
    }

    .carousel-thumb.active {
        border-color: #495057 !important;
        transform: scale(1.05);
    }

    .room-info-card {
        border: 1px solid #e9ecef;
    }

    .service-card {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .service-card:hover {
        border-color: #0d6efd;
    }

    .service-card.selected {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }

    .service-checkbox {
        display: none;
    }

    .price-breakdown {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
    }
</style>

<script>
    let selectedServices = [];
    const roomPricePerNight = <?php echo $phong['TongGia']; ?>;
    const nights = <?php echo $nights; ?>;

    function changeMainImage(src, element) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.carousel-thumb').forEach(thumb => {
            thumb.classList.remove('active');
            thumb.style.borderColor = 'transparent';
        });
        element.classList.add('active');
        element.style.borderColor = '#495057';
    }

    function toggleService(card, serviceId, servicePrice) {
        const checkbox = card.querySelector('.service-checkbox');
        checkbox.checked = !checkbox.checked;

        if (checkbox.checked) {
            const existingIndex = selectedServices.findIndex(service => service.id === serviceId);
            if (existingIndex === -1) {
                card.classList.add('selected');
                selectedServices.push({
                    id: serviceId,
                    price: servicePrice,
                    name: card.querySelector('.small').textContent.trim()
                });
            }
        } else {
            card.classList.remove('selected');
            selectedServices = selectedServices.filter(service => service.id !== serviceId);
        }

        updateTotalPrice();
    }

    function updateTotalPrice() {
        let servicesTotal = selectedServices.reduce((total, service) => total + service.price, 0);
        let roomTotal = roomPricePerNight * nights;
        let total = roomTotal + servicesTotal;

        document.getElementById('roomPriceTotal').textContent = roomTotal.toLocaleString() + ' đ';
        document.getElementById('servicesPrice').textContent = servicesTotal.toLocaleString() + ' đ';
        document.getElementById('totalPrice').textContent = total.toLocaleString() + ' đ';
    }

    function bookRoom(roomId) {
        const selectedServiceIds = selectedServices.map(service => service.id);

        const queryParams = new URLSearchParams({
            roomId: roomId,
            checkin: '<?php echo $checkin; ?>',
            checkout: '<?php echo $checkout; ?>',
            adults: '<?php echo $adults; ?>',
            nights: '<?php echo $nights; ?>'
        });

        if (selectedServiceIds.length > 0) {
            queryParams.append('services', selectedServiceIds.join(','));
        }

        if (confirm('Bạn có chắc muốn đặt phòng này?' + (selectedServiceIds.length > 0 ? '\n\nCác dịch vụ đã chọn:\n' + selectedServices.map(s => '- ' + s.name).join('\n') : ''))) {
            // CHUYỂN ĐẾN TRANG THANH TOÁN
            window.location.href = `/ABC-Resort/client/controller/payment.controller.php?${queryParams.toString()}`;
        }
    }

    // Active thumbnail đầu tiên khi load trang
    document.addEventListener('DOMContentLoaded', function() {
        const firstThumb = document.querySelector('.carousel-thumb');
        if (firstThumb) {
            firstThumb.style.borderColor = '#495057';
        }
        updateTotalPrice();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>