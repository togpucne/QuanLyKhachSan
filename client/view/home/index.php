<?php
include __DIR__ . '/../layouts/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/model/connectDB.php';

// Tạo kết nối
$connect = new Connect();
$conn = $connect->openConnect();

// LẤY DANH SÁCH PHÒNG TỪ DATABASE - THÊM PHẦN NÀY
$sqlRooms = "SELECT p.*, lp.HangPhong, lp.HinhThuc 
             FROM Phong p 
             LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
             where p.TrangThai = 'Trống'";
$resultRooms = mysqli_query($conn, $sqlRooms);

$rooms = [];
$roomCounts = ['Trống' => 0, 'Đang sử dụng' => 0, 'Đang dọn dẹp' => 0];

if ($resultRooms && mysqli_num_rows($resultRooms) > 0) {
    while ($row = mysqli_fetch_assoc($resultRooms)) {
        $rooms[] = $row;
        // Đếm số phòng theo trạng thái
        if (isset($roomCounts[$row['TrangThai']])) {
            $roomCounts[$row['TrangThai']]++;
        }
    }
}

// Lấy khuyến mãi từ database ptud
$today = date('Y-m-d');
$sql = "SELECT * FROM KhuyenMai 
        WHERE TrangThai = 1 
        AND NgayBatDau <= '$today' 
        AND NgayKetThuc >= '$today'
        ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$khuyenMaiList = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $khuyenMaiList[] = $row;
    }
}

// DEBUG
echo "<!-- DEBUG: Số khuyến mãi: " . count($khuyenMaiList) . " -->";
echo "<!-- DEBUG: Số phòng: " . count($rooms) . " -->";

// Đóng kết nối
$connect->closeConnect($conn);
?>

<style>
    .custom-range {
        -webkit-appearance: none;
        appearance: none;
        background: transparent;
        cursor: pointer;
    }

    .custom-range::-webkit-slider-track {
        background: #e9ecef;
        height: 8px;
        border-radius: 4px;
    }

    .custom-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        height: 20px;
        width: 20px;
        border-radius: 50%;
        background: #37353E;
        cursor: pointer;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .custom-range::-moz-range-track {
        background: #e9ecef;
        height: 8px;
        border-radius: 4px;
        border: none;
    }

    .custom-range::-moz-range-thumb {
        height: 20px;
        width: 20px;
        border-radius: 50%;
        background: #37353E;
        cursor: pointer;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .room-card {
        transition: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
    }

    .room-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .card-img-top {
        transition: transform 0.3s ease;
    }

    .room-card:hover .card-img-top {
        transform: scale(1.05);
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .price-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        margin: 0 -12px -12px -12px;
        padding: 15px 12px 12px 12px;
    }

    .btn-dark {
        background: #2c3e50;
        border: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-dark:hover {
        background: #34495e;
        transform: translateY(-1px);
    }
</style>
<!-- Dace book  -->
<?php 
    require_once __DIR__ ."../../layouts/icon.php";

?>
<!-- Banner Carousel -->
<div id="resortCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="2"></button>
    </div>
    <div class="carousel-inner rounded">
        <div class="carousel-item active">
            <!-- SỬA: assets/images/banner/ (không có ../ phía trước) -->
            <img src="assets/images/banner/banner1.jpg" class="d-block w-100" alt="Resort View" style="height: 500px; object-fit: cover;">
            <div class="carousel-caption d-none d-md-block">
                <h3>Thiên Đường Nghỉ Dưỡng</h3>
                <p>Trải nghiệm không gian sang trọng và thoải mái</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="assets/images/banner/banner2.jpg" class="d-block w-100" alt="Luxury Room" style="height: 500px; object-fit: cover;">
            <div class="carousel-caption d-none d-md-block">
                <h3>Phòng Nghỉ Đẳng Cấp</h3>
                <p>Tiện nghi hiện đại, dịch vụ 5 sao</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="assets/images/banner/banner3.jpg" class="d-block w-100" alt="Beach View" style="height: 500px; object-fit: cover;">
            <div class="carousel-caption d-none d-md-block">
                <h3>View Biển Tuyệt Đẹp</h3>
                <p>Ngắm bình minh và hoàng hôn từ phòng</p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#resortCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#resortCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>


<div class="container">
    <!-- Bộ lọc & tìm kiếm phòng -->
    <div class="card mb-5 shadow-lg border-0 rounded-4" style="overflow:hidden; background-color: #1B1B1B;">
        <div class="card-header py-3 border-0"
            style="background: linear-gradient(90deg, #435663, #313647); color: #fff;">
        </div>

        <div class="card-body p-4" style="background-color: #F8F9FA;">
            <form id="filterForm">
                <!-- Phần mới: Ngày đến, ngày đi, số người -->
                <div class="row g-3 mb-4">
                    <!-- Ngày đến -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Ngày đến</label>
                        <input type="date" class="form-control rounded-3 border-0 shadow-sm"
                            id="checkinDate"
                            min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <!-- Ngày đi -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Ngày đi</label>
                        <input type="date" class="form-control rounded-3 border-0 shadow-sm"
                            id="checkoutDate"
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>

                    <!-- Số người - Dropdown -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Số người</label>
                        <div class="dropdown" id="guestDropdownContainer">
                            <button class="btn btn-light w-100 text-start rounded-3 border-0 shadow-sm dropdown-toggle"
                                type="button"
                                id="guestDropdown"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false">
                                <span id="guestDisplay">1 Adult(s), 1 Room</span>
                            </button>
                            <div class="dropdown-menu p-3" style="width: 280px;">
                                <div class="row g-3">
                                    <!-- Adult -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="fw-semibold">Adult</label>
                                            <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="changeGuestCount('adult', -1)">-</button>
                                                <span class="mx-3 fw-semibold" id="adultCount">1</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="changeGuestCount('adult', 1)">+</button>
                                            </div>
                                        </div>
                                        <small class="text-muted">Từ 13 tuổi trở lên</small>
                                    </div>

                                    <!-- Room -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center" >
                                            <label class="fw-semibold">Room</label>
                                            <div class="d-flex align-items-center" >
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="changeGuestCount('room', -1)">-</button>
                                                <span class="mx-3 fw-semibold" id="roomCount">1</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="changeGuestCount('room', 1)">+</button>
                                            </div>
                                        </div>
                                        <small class="text-muted">Số phòng</small>
                                    </div>

                                    <!-- Nút áp dụng và đóng -->
                                    <div class="col-12 pt-2">
                                        <button type="button" class="btn btn-primary w-100 mb-2"
                                            onclick="applyGuestSelection()">Áp dụng</button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hiển thị số đêm -->
                    <div class="col-md-3">
                        <div class="text-center p-2 bg-white rounded-3 border-0 shadow-sm">
                            <small class="text-muted d-block">Số đêm</small>
                            <span class="fw-bold" style="color: #37353E;" id="nightCount">1</span>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Tìm kiếm theo số phòng -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Tìm kiếm:</label>
                        <input type="text" class="form-control rounded-3 border-0 shadow-sm"
                            id="roomNumber" placeholder="Nhập tên phòng...">
                    </div>

                    <!-- Lọc theo tầng -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Tầng</label>
                        <select class="form-select rounded-3 border-0 shadow-sm" id="floorFilter">
                            <option value="">Tất cả tầng</option>
                            <option value="1">Tầng 1</option>
                            <option value="2">Tầng 2</option>
                        </select>
                    </div>

                    <!-- Lọc theo hạng phòng -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Hạng Phòng</label>
                        <select class="form-select rounded-3 border-0 shadow-sm" id="roomClassFilter">
                            <option value="">Tất cả hạng</option>
                            <option value="Cao cấp">Cao cấp</option>
                            <option value="Thương gia">Thương gia</option>
                            <option value="Tiêu chuẩn">Tiêu chuẩn</option>
                        </select>
                    </div>

                    <!-- Lọc theo giá -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">
                            Khoảng giá:
                            <span id="priceRangeValue" class="fw-bold" style="color: #37353E;">
                                0 - 10,000,000 VND
                            </span>
                        </label>
                        <input type="range" class="form-range custom-range" id="priceRange"
                            min="0" max="10000000" step="100000" value="10000000"
                            style="width: 100%; height: 8px; background: #e9ecef; border-radius: 4px;">
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">0 VND</small>
                            <small class="text-muted">10,000,000 VND</small>
                        </div>
                    </div>

                    <!-- Nút tìm kiếm -->
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit"
                                class="btn text-white flex-fill shadow-sm"
                                style="background: linear-gradient(90deg, #37353E, #37353E); font-weight:600;">
                                <i class="fas fa-search me-2"></i>Tìm Kiếm
                            </button>
                            <button type="reset"
                                class="btn flex-fill fw-semibold border-2"
                                style="border-color: #37353E; color: #37353E;">
                                <i class="fas fa-redo me-2"></i>Đặt Lại
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!--  Banner Khuyến Mãi  -->
    <div class="row mb-5 mt-5">
        <div class="">
            <img src="assets/images/banner/banner_khuyenmai.webp" height="250px" width="100%" alt=""
                style="border-radius: 10px;">
        </div>

    </div>


    <!-- Danh sách phòng -->
    <h2 id="room-list" class="text-center mb-4 mt-5">DANH SÁCH PHÒNG RESORT</h2>

    <?php
    // ĐỊNH NGHĨA HÀM GET ROOM IMAGE PATH - THÊM LẠI PHẦN NÀY
    function getRoomImagePath($avatar)
    {
        if (empty($avatar)) {
            return 'assets/images/default-room.jpg';
        }

        // Nếu là URL đầy đủ
        if (strpos($avatar, 'http') === 0) {
            return $avatar;
        }

        // Nếu là đường dẫn tương đối trong database
        $basePath = 'assets/images/rooms/';
        $extensions = ['.jpeg', '.jpg', '.png', '.webp'];

        foreach ($extensions as $ext) {
            $fullPath = $basePath . $avatar . $ext;
            return $fullPath;
        }

        return $basePath . $avatar . '.jpeg'; // Mặc định .jpeg
    }
    ?>

    <?php if (empty($rooms)): ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Không có phòng nào trong hệ thống
        </div>
    <?php else: ?>
        <div class="row mb-5 mt-5" id="roomList">
            <?php foreach ($rooms as $room): ?>
                <div class="col-lg-4 col-md-6 mb-4 room-item"
                    data-floor="<?php echo htmlspecialchars($room['Tang']); ?>"
                    data-class="<?php echo htmlspecialchars($room['HangPhong']); ?>"
                    data-status="<?php echo htmlspecialchars($room['TrangThai']); ?>"
                    data-price="<?php echo $room['TongGia']; ?>"
                    data-sokhachtoida="<?php echo $room['SoKhachToiDa']; ?>">

                    <div class="card room-card h-100 border-0 shadow-sm">
                        <!-- Ảnh phòng -->
                        <div class="room-image position-relative">
                            <?php
                            $roomImage = getRoomImagePath($room['Avatar'] ?? '');
                            ?>
                            <img src="<?php echo $roomImage; ?>"
                                class="card-img-top"
                                alt="Phòng <?php echo htmlspecialchars($room['SoPhong']); ?>"
                                style="height: 220px; object-fit: cover; border-radius: 8px 8px 0 0;"
                                onerror="this.src='assets/images/default-room.jpg'">

                            <!-- Badge trạng thái -->
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge 
                                <?php
                                switch ($room['TrangThai']) {
                                    case 'Trống':
                                        echo 'bg-success';
                                        break;
                                    case 'Đang sử dụng':
                                        echo 'bg-danger';
                                        break;
                                    case 'Đang dọn dẹp':
                                        echo 'bg-warning';
                                        break;
                                    default:
                                        echo 'bg-secondary';
                                }
                                ?> px-2 py-1">
                                    <?php echo htmlspecialchars($room['TrangThai']); ?>
                                </span>
                            </div>

                            <!-- Badge hạng phòng -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-dark px-2 py-1">
                                    <?php echo htmlspecialchars($room['HangPhong']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Thông tin phòng -->
                        <div class="card-body d-flex flex-column p-3">
                            <!-- Tên phòng -->
                            <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 1.1rem;">
                                <?php echo htmlspecialchars($room['roomName']); ?>
                            </h5>

                            <!-- Thông tin cơ bản - ĐƠN GIẢN HÓA -->
                            <div class="room-info mb-3 flex-grow-1">
                                <!-- Diện tích và Sức chứa -->
                                <div class="d-flex justify-content-between text-muted mb-2">
                                    <span>
                                        <strong>Diện tích:</strong>
                                        <?php echo $room['DienTich']; ?> m²
                                    </span>
                                    <span>
                                        <strong>Sức chứa:</strong>
                                        <?php echo $room['SoKhachToiDa']; ?> người
                                    </span>
                                </div>

                                <!-- Tầng và Hướng -->
                                <div class="d-flex justify-content-between text-muted mb-2">
                                    <span>
                                        <strong>Tầng:</strong>
                                        <?php echo htmlspecialchars($room['Tang']); ?>
                                    </span>
                                    <span>
                                        <strong>Hướng:</strong>
                                        <?php echo htmlspecialchars($room['HuongNha']); ?>
                                    </span>
                                </div>

                                <!-- Hình thức -->
                                <div class="text-muted mb-3">
                                    <strong>Hình thức:</strong>
                                    <?php echo htmlspecialchars($room['HinhThuc']); ?>
                                </div>
                            </div>

                            <!-- Giá phòng - NỔI BẬT -->
                            <div class="price-section border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted d-block">Giá mỗi đêm</small>
                                        <div class="h5 fw-bold text-primary mb-0">
                                            <?php echo number_format($room['TongGia']); ?> VND
                                        </div>
                                    </div>

                                    <!-- Nút xem chi tiết -->
                                    <?php if ($room['TrangThai'] === 'Trống'): ?>
                                        <button class="btn btn-dark px-3"
                                            onclick="viewRoomDetail(<?php echo $room['MaPhong']; ?>)">
                                            Chi tiết
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary px-3" disabled>
                                            Đã đặt
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>




    <!-- Hiển thị khuyến mãi từ CSDL -->
    <div class="row mb-5" id="khuyen-mai"> <!-- THÊM ID Ở ĐÂY -->
        <div class="col-12">
            <h2 class="text-center mb-4">KHUYẾN MÃI ĐANG DIỄN RA</h2>

            <div class="row">
                <?php if (!empty($khuyenMaiList)): ?>
                    <?php foreach ($khuyenMaiList as $km): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="<?php echo $km['HinhAnh']; ?>"
                                    class="card-img-top"
                                    alt="<?php echo $km['TenKhuyenMai']; ?>"
                                    style="height: 200px; object-fit: cover;"
                                    onerror="this.src='assets/images/default-room.jpg'">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo $km['TenKhuyenMai']; ?></h5>
                                    <p class="card-text flex-grow-1"><?php echo $km['MoTa']; ?></p>
                                    <div class="mt-auto">
                                        <p class="text-success fw-bold mb-1">Giảm: <?php echo $km['MucGiamGia']; ?>%</p>
                                        <small class="text-muted d-block">
                                            <?php echo date('d/m/Y', strtotime($km['NgayBatDau'])); ?> -
                                            <?php echo date('d/m/Y', strtotime($km['NgayKetThuc'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Hiện không có khuyến mãi nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    // Biến toàn cục để lưu số lượng
    let guestCounts = {
        adult: 1,
        room: 1
    };

    // Biến lưu thông tin tìm kiếm
    let searchParams = {
        checkin: '',
        checkout: '',
        nights: 1,
        adults: 1,
        rooms: 1
    };

    // Hàm thay đổi số lượng
    function changeGuestCount(type, change) {
        let newCount = guestCounts[type] + change;

        // Đặt giới hạn
        if (type === 'adult') {
            if (newCount >= 1 && newCount <= 10) {
                guestCounts.adult = newCount;
                document.getElementById('adultCount').textContent = newCount;
            }
        } else if (type === 'room') {
            if (newCount >= 1 && newCount <= 5) {
                guestCounts.room = newCount;
                document.getElementById('roomCount').textContent = newCount;
            }
        }
    }

    // Hàm đóng dropdown
    function closeGuestDropdown() {
        const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('guestDropdown'));
        if (dropdown) {
            dropdown.hide();
        }
    }

    // Hàm áp dụng lựa chọn - CÓ ĐÓNG DROPDOWN
    function applyGuestSelection() {
        let displayText = `${guestCounts.adult} Adult(s), ${guestCounts.room} Room`;
        document.getElementById('guestDisplay').textContent = displayText;

        // Cập nhật search params
        searchParams.adults = guestCounts.adult;
        searchParams.rooms = guestCounts.room;

        // Đóng dropdown sau khi áp dụng
        closeGuestDropdown();

        // Lọc phòng
        filterRooms();
    }
    // Hàm tính số đêm
    function calculateNights(checkin, checkout) {
        const checkinDate = new Date(checkin);
        const checkoutDate = new Date(checkout);
        const timeDiff = checkoutDate - checkinDate;
        const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
        return nights > 0 ? nights : 1;
    }

    // Hàm cập nhật số đêm
    function updateNightCount() {
        const checkin = document.getElementById('checkinDate').value;
        const checkout = document.getElementById('checkoutDate').value;

        if (checkin && checkout) {
            const nights = calculateNights(checkin, checkout);
            document.getElementById('nightCount').textContent = nights;
            searchParams.nights = nights;
        }
    }

    // Xử lý form tìm kiếm
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const checkinDate = document.getElementById('checkinDate');
        const checkoutDate = document.getElementById('checkoutDate');
        const priceRange = document.getElementById('priceRange');
        const priceRangeValue = document.getElementById('priceRangeValue');

        // Đặt ngày mặc định
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        checkinDate.value = today.toISOString().split('T')[0];
        checkoutDate.value = tomorrow.toISOString().split('T')[0];

        // Cập nhật search params mặc định
        searchParams.checkin = checkinDate.value;
        searchParams.checkout = checkoutDate.value;
        searchParams.nights = calculateNights(checkinDate.value, checkoutDate.value);

        // Cập nhật số đêm ban đầu
        updateNightCount();

        // Validate ngày
        checkinDate.addEventListener('change', function() {
            const checkin = new Date(this.value);
            const newCheckout = new Date(checkin);
            newCheckout.setDate(newCheckout.getDate() + 1);

            checkoutDate.min = newCheckout.toISOString().split('T')[0];

            if (new Date(checkoutDate.value) <= checkin) {
                checkoutDate.value = newCheckout.toISOString().split('T')[0];
            }

            searchParams.checkin = this.value;
            updateNightCount();
            filterRooms();
        });

        checkoutDate.addEventListener('change', function() {
            searchParams.checkout = this.value;
            updateNightCount();
            filterRooms();
        });

        // Cập nhật hiển thị giá
        priceRange.addEventListener('input', function() {
            const maxPrice = parseInt(this.value);
            priceRangeValue.textContent = `0 - ${maxPrice.toLocaleString()} VND`;
            filterRooms();
        });

        // Xử lý submit form
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            filterRooms();
        });

        filterForm.addEventListener('reset', function() {
            setTimeout(function() {
                // Reset về mặc định
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);

                checkinDate.value = today.toISOString().split('T')[0];
                checkoutDate.value = tomorrow.toISOString().split('T')[0];

                guestCounts = {
                    adult: 1,
                    room: 1
                };
                document.getElementById('guestDisplay').textContent = '1 Adult(s), 1 Room';
                document.getElementById('adultCount').textContent = '1';
                document.getElementById('roomCount').textContent = '1';

                searchParams = {
                    checkin: checkinDate.value,
                    checkout: checkoutDate.value,
                    nights: 1,
                    adults: 1,
                    rooms: 1
                };

                updateNightCount();
                filterRooms();
            }, 100);
        });

        // Lọc real-time khi thay đổi
        document.getElementById('floorFilter').addEventListener('change', filterRooms);
        document.getElementById('roomClassFilter').addEventListener('change', filterRooms);
        document.getElementById('roomNumber').addEventListener('input', filterRooms);
    });

    // Hàm lọc phòng
    function filterRooms() {
        const floorFilter = document.getElementById('floorFilter').value;
        const classFilter = document.getElementById('roomClassFilter').value;
        const maxPrice = parseInt(document.getElementById('priceRange').value);
        const roomNumber = document.getElementById('roomNumber').value.toLowerCase();

        const roomItems = document.querySelectorAll('.room-item');
        const selectedAdults = searchParams.adults;

        let visibleCount = 0;

        roomItems.forEach(room => {
            const floor = room.getAttribute('data-floor');
            const roomClass = room.getAttribute('data-class');
            const status = room.getAttribute('data-status');
            const price = parseInt(room.getAttribute('data-price'));
            const roomNum = room.querySelector('.card-title').textContent.toLowerCase();
            const maxGuests = parseInt(room.getAttribute('data-sokhachtoida') || 2); // Lấy từ attribute

            console.log('Phòng:', roomNum, 'Max guests:', maxGuests, 'Selected adults:', selectedAdults); // Debug

            const matchFloor = !floorFilter || floor === floorFilter;
            const matchClass = !classFilter || roomClass === classFilter;
            const matchPrice = price <= maxPrice;
            const matchRoomNumber = !roomNumber || roomNum.includes(roomNumber);
            const matchGuests = maxGuests >= selectedAdults; // So sánh: sức chứa >= số người chọn
            const matchStatus = status === 'Trống'; // Chỉ hiển thị phòng trống

            if (matchFloor && matchClass && matchStatus && matchPrice && matchRoomNumber && matchGuests) {
                room.style.display = 'block';
                visibleCount++;
                console.log('HIỆN:', roomNum); // Debug
            } else {
                room.style.display = 'none';
                console.log('ẨN:', roomNum, 'Lý do:', {
                    floor: !matchFloor,
                    class: !matchClass,
                    status: !matchStatus,
                    price: !matchPrice,
                    roomNumber: !matchRoomNumber,
                    guests: !matchGuests
                }); // Debug
            }
        });

        // Hiển thị thông báo nếu không có phòng nào
        const roomList = document.getElementById('roomList');
        let noResults = roomList.querySelector('.no-results-message');

        if (visibleCount === 0) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.className = 'col-12 no-results-message';
                noResults.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Không tìm thấy phòng nào phù hợp với tiêu chí tìm kiếm<br>
                    <small>Số người: ${searchParams.adults} | Số đêm: ${searchParams.nights}</small>
                </div>
            `;
                roomList.appendChild(noResults);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }

    // Auto-play carousel và các hàm khác giữ nguyên
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = new bootstrap.Carousel(document.getElementById('resortCarousel'), {
            interval: 3000,
            wrap: true
        });
    });

    // Trong file view/home/index.php
    function viewRoomDetail(roomId) {
        const checkin = document.getElementById('checkinDate').value;
        const checkout = document.getElementById('checkoutDate').value;
        const adults = searchParams.adults;
        const nights = searchParams.nights;

        const url = `../client/view/room/room-detail.php?id=${roomId}&checkin=${checkin}&checkout=${checkout}&adults=${adults}&nights=${nights}`;
        window.location.href = url;
    }

    function bookRoom(roomId) {
        alert('Đặt phòng: ' + roomId + '\nTính năng đang phát triển...');
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>