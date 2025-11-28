<?php
include __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../../model/connectDB.php';

// Lấy MaPhong từ URL
$maPhong = $_GET['id'] ?? 0;

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

// Xử lý ảnh từ DanhSachPhong - ĐƠN GIẢN SAU KHI UPDATE DATABASE
$danhSachAnh = json_decode($phong['DanhSachPhong'], true) ?: [$phong['Avatar']];

// DEBUG: Kiểm tra dữ liệu ảnh
echo "<!-- DEBUG: Số ảnh: " . count($danhSachAnh) . " -->";
foreach ($danhSachAnh as $index => $anh) {
    echo "<!-- DEBUG Ảnh $index: $anh -->";
    echo "<!-- DEBUG Đường dẫn $index: " . getRoomImagePath($anh) . " -->";
}

// Hàm lấy đường dẫn ảnh đúng - THỬ CÁC ĐUÔI ẢNH
function getRoomImagePath($imagePath)
{
    if (empty($imagePath)) {
        return '/ABC-Resort/client/assets/images/default-room.jpg';
    }

    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }

    $basePath = '/ABC-Resort/client/assets/images/rooms/';

    // Thử các đuôi ảnh phổ biến
    $extensions = ['.jpeg', '.jpg', '.png', '.webp', '.gif'];

    foreach ($extensions as $ext) {
        $fullPath = $basePath . $imagePath . $ext;
        // Có thể kiểm tra file tồn tại ở đây nếu cần
        // Hoặc cứ trả về và để browser tự xử lý
        return $fullPath; // Ưu tiên .jpeg đầu tiên
    }

    return $basePath . $imagePath; // Fallback: không đuôi
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

    .carousel-indicators button {
        background-color: #6c757d;
    }

    .carousel-indicators .active {
        background-color: #0d6efd;
    }
    
</style>
<!-- Phần giới thiệu resort -->
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
<section class="room-detail py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Bên trái 7 - Hình ảnh -->
            <div class="col-lg-7 mb-4">
                <!-- Ảnh chính lớn -->
                <div class="main-image mb-4">
                    <img id="mainImage" src="<?php echo getRoomImagePath($danhSachAnh[0]); ?>"
                        class="img-fluid rounded w-100 shadow-sm"
                        alt="<?php echo htmlspecialchars($phong['roomName']); ?>"
                        style="height: 450px; object-fit: cover;"
                        onerror="this.onerror=null; this.src='/ABC-Resort/client/assets/images/default-room.jpg'">
                </div>

                <!-- Carousel ảnh nhỏ -->
                <div class="image-carousel">
                    <div class="d-flex overflow-auto pb-2" style="gap: 12px;">
                        <?php foreach ($danhSachAnh as $index => $anh): ?>
                            <img src="<?php echo getRoomImagePath($anh); ?>"
                                class="carousel-thumb rounded cursor-pointer <?php echo $index === 0 ? 'active' : ''; ?>"
                                alt="Ảnh <?php echo $index + 1; ?>"
                                style="width: 120px; height: 90px; object-fit: cover; border: 2px solid transparent;"
                                onclick="changeMainImage(this.src, this)"
                                onerror="this.onerror=null; this.src='/ABC-Resort/client/assets/images/default-room.jpg'">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Bên phải 3 - Thông tin phòng -->
            <div class="col-lg-5">
                <div class="room-info-card bg-white rounded shadow-sm p-4">
                    <h2 class="h3 fw-bold text-dark mb-4"><?php echo htmlspecialchars($phong['roomName']); ?></h2>

                    <!-- Thông tin cơ bản -->
                    <div class="basic-info mb-4 pb-3 border-bottom">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="room-feature-icon me-3">
                                        <i class="fas fa-ruler-combined text-muted"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo $phong['DienTich']; ?> m²</div>
                                        <small class="text-muted">Diện tích</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="room-feature-icon me-3">
                                        <i class="fas fa-users text-muted"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo $phong['SoKhachToiDa']; ?> khách</div>
                                        <small class="text-muted">Sức chứa</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="room-feature-icon me-3">
                                        <i class="fas fa-compass text-muted"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($phong['HuongNha']); ?></div>
                                        <small class="text-muted">Hướng</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="room-feature-icon me-3">
                                        <i class="fas fa-bed text-muted"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($phong['HangPhong']); ?></div>
                                        <small class="text-muted">Hạng phòng</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tính năng phòng -->
                    <div class="features-section mb-4 pb-3 border-bottom">
                        <h5 class="fw-semibold mb-3">Tiện nghi phòng</h5>
                        <div class="row g-2">
                            <?php if (!empty($tienNghi)): ?>
                                <?php foreach ($tienNghi as $tienNghiItem): ?>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="fas fa-check-circle text-success me-2 small"></i>
                                            <small><?php echo htmlspecialchars($tienNghiItem); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <small class="text-muted">Đang cập nhật thông tin tiện nghi...</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Thiết bị -->
                    <div class="equipment-section mb-4 pb-3 border-bottom">
                        <h5 class="fw-semibold mb-3">Thiết bị có sẵn</h5>
                        <div class="row g-2">
                            <?php if (!empty($thietBiList)): ?>
                                <?php foreach ($thietBiList as $thietBi): ?>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="fas fa-check text-primary me-2 small"></i>
                                            <small><?php echo htmlspecialchars($thietBi); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <small class="text-muted">Đang cập nhật thiết bị...</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Giá và đặt phòng -->
                    <div class="booking-section pt-3">
                        <div class="price-section mb-3">
                            <small class="text-muted d-block mb-1">Giá mỗi đêm</small>
                            <div class="d-flex align-items-baseline">
                                <div class="h3 fw-bold text-dark"><?php echo number_format($phong['TongGia']); ?> VND</div>
                                <small class="text-muted ms-2">/ đêm</small>
                            </div>
                        </div>
                        <button class="btn btn-dark w-100 py-3 fw-semibold" onclick="bookRoom(<?php echo $phong['MaPhong']; ?>)">
                            Đặt Phòng Ngay
                        </button>
                        <div class="text-center mt-2">
                            <small class="text-muted">Miễn phí hủy phòng trong 24 giờ</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mô tả chi tiết -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="description-card bg-white rounded shadow-sm p-4">
                    <h4 class="fw-semibold mb-3">Thông tin chi tiết</h4>
                    <div class="room-description" style="line-height: 1.7;">
                        <?php if (!empty($phong['MoTaChiTiet'])): ?>
                            <?php echo nl2br(htmlspecialchars($phong['MoTaChiTiet'])); ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Thông tin chi tiết về phòng đang được cập nhật. Quý khách vui lòng liên hệ bộ phận lễ tân để biết thêm thông tin.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .room-detail {
        background: #f8f9fa;
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
        position: sticky;
        top: 100px;
        border: 1px solid #e9ecef;
    }

    .booking-section .btn {
        background: #212529;
        border: none;
        transition: all 0.3s ease;
    }

    .booking-section .btn:hover {
        background: #495057;
        transform: translateY(-1px);
    }

    .description-card {
        border: 1px solid #e9ecef;
    }
</style>

<script>
    function changeMainImage(src, element) {
        // Đổi ảnh chính
        document.getElementById('mainImage').src = src;

        // Active thumbnail
        document.querySelectorAll('.carousel-thumb').forEach(thumb => {
            thumb.classList.remove('active');
            thumb.style.borderColor = 'transparent';
        });
        element.classList.add('active');
        element.style.borderColor = '#495057';
    }

    function bookRoom(roomId) {
        if (confirm('Bạn có chắc muốn đặt phòng này?')) {
            // Chuyển hướng đến trang đặt phòng
            alert('Hệ thống đặt phòng đang được hoàn thiện. Vui lòng liên hệ trực tiếp với chúng tôi!');
        }
    }

    // Active thumbnail đầu tiên khi load trang
    document.addEventListener('DOMContentLoaded', function() {
        const firstThumb = document.querySelector('.carousel-thumb');
        if (firstThumb) {
            firstThumb.style.borderColor = '#495057';
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>