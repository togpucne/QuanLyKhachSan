<?php
include __DIR__ . '/../layouts/header.php';

// DÙNG include_once THAY VÌ include
include_once $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/model/connectDB.php';

// Tạo kết nối
$connect = new Connect();
$conn = $connect->openConnect();

// Lấy khuyến mãi từ database ptud
$sql = "SELECT * FROM KhuyenMai";
$result = mysqli_query($conn, $sql);

$khuyenMaiList = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $khuyenMaiList[] = $row;
    }
}

// DEBUG
echo "<!-- DEBUG: Số khuyến mãi: " . count($khuyenMaiList) . " -->";

// Đóng kết nối
$connect->closeConnect($conn);
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

                    <!-- Lọc theo trạng thái -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Trạng Thái</label>
                        <select class="form-select rounded-3 border-0 shadow-sm" id="statusFilter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Trống">Phòng trống</option>
                            <option value="Đang sử dụng">Đang sử dụng</option>
                            <option value="Đang dọn dẹp">Đang dọn dẹp</option>
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
                        <input type="range" class="form-range" id="priceRange"
                            min="0" max="10000000" step="100000" value="10000000"
                            style="color: red ;accent-color: #37353E;">
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
    <div class="row mb-5">
        <div class="">
            <img src="assets/images/banner/banner_khuyenmai.webp" height="250px" width="100%" alt=""
                style="border-radius: 10px;">
        </div>

    </div>


    <!-- Thống kê nhanh -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-success"><?php echo isset($roomCounts['Trống']) ? $roomCounts['Trống'] : 0; ?></h3>
                    <p class="text-muted mb-0">Phòng Trống</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-danger"><?php echo isset($roomCounts['Đang sử dụng']) ? $roomCounts['Đang sử dụng'] : 0; ?></h3>
                    <p class="text-muted mb-0">Đang Sử Dụng</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-warning"><?php echo isset($roomCounts['Đang dọn dẹp']) ? $roomCounts['Đang dọn dẹp'] : 0; ?></h3>
                    <p class="text-muted mb-0">Đang Dọn Dẹp</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-info"><?php echo count($rooms); ?></h3>
                    <p class="text-muted mb-0">Tổng Số Phòng</p>
                </div>
            </div>
        </div>
    </div>


    <!-- Danh sách phòng -->
    <h2 id="room-list" class="text-center mb-4">DANH SÁCH PHÒNG RESORT</h2>

    <?php
    // ĐƯA HÀM RA NGOÀI VÒNG LẶP
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
        // Database: room001/avatar1 → Đường dẫn: assets/images/rooms/room001/avatar1.jpeg
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
        <div class="row" id="roomList">
            <?php foreach ($rooms as $room): ?>
                <div class="col-lg-3 col-md-6 mb-4 room-item"
                    data-floor="<?php echo htmlspecialchars($room['Tang']); ?>"
                    data-class="<?php echo htmlspecialchars($room['HangPhong']); ?>"
                    data-status="<?php echo htmlspecialchars($room['TrangThai']); ?>"
                    data-price="<?php echo $room['TongGia']; ?>">
                    <div class="card room-card h-100 shadow-sm">
                        <!-- Ảnh phòng - LẤY TỪ CỘT AVATAR -->
                        <div class="room-image position-relative">
                            <?php
                            $roomImage = getRoomImagePath($room['Avatar'] ?? '');
                            ?>

                            <img src="<?php echo $roomImage; ?>"
                                class="card-img-top"
                                alt="Phòng <?php echo htmlspecialchars($room['SoPhong']); ?>"
                                style="height: 200px; object-fit: cover;"
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
                                ?>">
                                    <?php echo htmlspecialchars($room['TrangThai']); ?>
                                </span>
                            </div>

                            <!-- Badge hạng phòng -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($room['HangPhong']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Thông tin phòng -->
                        <div class="card-body">
                            <h5 class="card-title text-primary tx-center">
                                <?php echo htmlspecialchars($room['roomName']); ?>
                            </h5>

                            <!-- Thông tin cơ bản -->
                            <div class="room-info mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-layer-group text-muted me-2"></i>
                                    <span><strong>Tầng:</strong> <?php echo htmlspecialchars($room['Tang']); ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    <span><strong>Hình thức:</strong> <?php echo htmlspecialchars($room['HinhThuc']); ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                                    <span><strong>Giá:</strong>
                                        <span class="text-success fw-bold">
                                            <?php echo number_format($room['TongGia']); ?> VND
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Nút đặt phòng -->
                        <div class="card-footer bg-light">
                            <?php if ($room['TrangThai'] === 'Trống'): ?>
                                <button class="btn btn-success w-100" onclick="bookRoom(<?php echo $room['MaPhong']; ?>)">
                                    <i class="fas fa-calendar-plus me-2"></i>Đặt Phòng Ngay
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-ban me-2"></i>Không Khả Dụng
                                </button>
                            <?php endif; ?>
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
                                    <span class="badge bg-primary mb-2"><?php echo $km['LoaiKhuyenMai']; ?></span>
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
    function bookRoom(roomId) {
        alert('Đặt phòng: ' + roomId + '\nTính năng đang phát triển...');
    }

    // Xử lý bộ lọc
    document.addEventListener('DOMContentLoaded', function() {
        const priceRange = document.getElementById('priceRange');
        const priceRangeValue = document.getElementById('priceRangeValue');
        const filterForm = document.getElementById('filterForm');
        const roomItems = document.querySelectorAll('.room-item');

        // Cập nhật hiển thị giá
        priceRange.addEventListener('input', function() {
            const maxPrice = parseInt(this.value);
            priceRangeValue.textContent = `0 - ${maxPrice.toLocaleString()} VND`;
        });

        // Xử lý tìm kiếm và lọc
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            filterRooms();
        });

        filterForm.addEventListener('reset', function() {
            setTimeout(filterRooms, 100);
        });

        // Lọc real-time khi thay đổi
        document.getElementById('floorFilter').addEventListener('change', filterRooms);
        document.getElementById('roomClassFilter').addEventListener('change', filterRooms);
        document.getElementById('statusFilter').addEventListener('change', filterRooms);
        document.getElementById('priceRange').addEventListener('input', filterRooms);
        document.getElementById('roomNumber').addEventListener('input', filterRooms);

        function filterRooms() {
            const floorFilter = document.getElementById('floorFilter').value;
            const classFilter = document.getElementById('roomClassFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const maxPrice = parseInt(document.getElementById('priceRange').value);
            const roomNumber = document.getElementById('roomNumber').value.toLowerCase();

            roomItems.forEach(room => {
                const floor = room.getAttribute('data-floor');
                const roomClass = room.getAttribute('data-class');
                const status = room.getAttribute('data-status');
                const price = parseInt(room.getAttribute('data-price'));
                const roomNum = room.querySelector('.card-title').textContent.toLowerCase();

                const matchFloor = !floorFilter || floor === floorFilter;
                const matchClass = !classFilter || roomClass === classFilter;
                const matchStatus = !statusFilter || status === statusFilter;
                const matchPrice = price <= maxPrice;
                const matchRoomNumber = !roomNumber || roomNum.includes(roomNumber);

                if (matchFloor && matchClass && matchStatus && matchPrice && matchRoomNumber) {
                    room.style.display = 'block';
                } else {
                    room.style.display = 'none';
                }
            });
        }
    });

    // Auto-play carousel
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = new bootstrap.Carousel(document.getElementById('resortCarousel'), {
            interval: 3000,
            wrap: true
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>