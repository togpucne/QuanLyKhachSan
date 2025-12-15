<?php
include __DIR__ . '/../layouts/header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /ABC-Resort/client/view/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Lấy thông tin từ URL
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : 1;

// Lấy thông tin khách hàng từ session nếu đã đăng nhập
if (!isset($customerInfo)) {
    $customerInfo = [
        'HoTen' => $_SESSION['user_name'] ?? '',
        'SoDienThoai' => $_SESSION['phone'] ?? '',
        'Email' => $_SESSION['email'] ?? '',
        'CMND' => '',
        'DiaChi' => ''
    ];
}
?>


<style>
    /* Thêm vào phần CSS */
    .duplicate-error {
        border-color: #dc3545 !important;
        background-color: #fff8f8 !important;
    }

    .duplicate-warning {
        background-color: #fff3cd !important;
        border-color: #ffeaa7 !important;
        color: #856404 !important;
    }

    .error-list {
        list-style-type: none;
        padding-left: 0;
        margin-top: 10px;
    }

    .error-list li {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }

    /* Thêm style cho input bị trùng */
    input.is-duplicate {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    /* Style cho phần khách hàng bổ sung */
    .main-guest-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        border-left: 4px solid #0d6efd;
    }

    .additional-guest-section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #dee2e6;
        border-left: 4px solid #20c997;
    }

    .guest-section-header {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
    }

    .common-info-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }

    .required-field::after {
        content: " *";
        color: #dc3545;
    }

    /* Hiệu ứng focus cho input */
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Badge style */
    .badge {
        font-size: 0.7rem;
        padding: 0.3em 0.6em;
    }

    /* Thêm CSS cho form thông tin khách hàng thứ 2 */
    .additional-guest-section {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-top: 15px;
        background: #f8f9fa;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
        display: none;
    }

    .was-validated .form-control:invalid {
        border-color: #dc3545;
    }

    .was-validated .form-control:valid {
        border-color: #198754;
    }
    
    /* Style cho hiển thị lỗi trùng */
    .duplicate-notice {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
        font-size: 0.9rem;
        color: #856404;
    }
    
    .duplicate-notice ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
</style>
<style>
    .payment-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .booking-summary {
        background: #f8f9fa;
        border-radius: 8px;
    }

    .hotel-rating {
        background: #198754;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .payment-section {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .section-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
    }

    .section-body {
        padding: 20px;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 5px;
    }

    .price-breakdown {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
    }

    .total-price {
        font-size: 1.3rem;
        font-weight: bold;
        color: #dc3545;
    }

    .payment-method {
        border: 2px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method:hover {
        border-color: #0d6efd;
    }

    .payment-method.selected {
        border-color: #0d6efd;
        background: #f8f9ff;
    }

    .room-info {
        background: white;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }

    .sticky-summary {
        position: sticky;
        top: 100px;
        z-index: 10;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }

    /* THÊM CSS CHO KHUYẾN MÃI */
    .promotion-item {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
        background: white;
    }

    .promotion-item:hover {
        border-color: #0d6efd;
        background-color: #f8f9ff;
    }

    .promotion-checkbox:disabled+label {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .text-decoration-line-through {
        text-decoration: line-through !important;
    }

    #discountSection {
        display: none;
    }

    #originalTotal {
        display: none;
    }
</style>

<div class="payment-container">
    <!-- Progress Steps -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                <div class="text-center mx-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">1</div>
                    <div class="mt-1 small">Xem lại</div>
                </div>
                <div class="text-center mx-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</div>
                    <div class="mt-1 small fw-bold">Thanh toán</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Thông tin thanh toán -->
        <div class="col-lg-8">
            <!-- THÔNG TIN LIÊN HỆ VÀ KHÁCH HÀNG CHÍNH (ĐÃ GỘP) -->
            <div class="payment-section">
                <div class="section-header">
                    Thông tin liên hệ & Khách hàng chính
                    <span class="text-muted fs-6 ms-2">(<?php echo $adults; ?> người)</span>
                </div>
                <div class="section-body">
                            <!-- THÔNG TIN LIÊN HỆ -->
                    <div class="common-info-section mb-4">
                        <h6 class="guest-section-header">
                            <i class="fas fa-address-book me-2"></i> Thông tin liên hệ & Khách hàng chính
                            <span class="badge bg-primary ms-2">Bắt buộc</span>
                        </h6>
                        <div class="mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contactName" name="customerName" required
                                placeholder="Như trên CMND (không dấu)"
                                value="<?php echo htmlspecialchars($customerInfo['HoTen'] ?? ''); ?>">
                            <div class="error-message" id="contactNameError"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+84</span>
                                    <input type="tel" class="form-control" id="contactPhone" name="customerPhone" required
                                        placeholder="901234567"
                                        value="<?php echo htmlspecialchars($customerInfo['SoDienThoai'] ?? ''); ?>">
                                </div>
                                <div class="error-message" id="contactPhoneError"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="contactEmail" name="customerEmail" required
                                    placeholder="email@example.com"
                                    value="<?php echo htmlspecialchars($customerInfo['Email'] ?? ''); ?>">
                                <div class="error-message" id="contactEmailError"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required-field">Số CMND/CCCD/Hộ chiếu</label>
                                    <input type="text" class="form-control" id="contactCMND" name="customerCMND" required
                                        placeholder="Nhập CMND/CCCD/Hộ chiếu"
                                        value="<?php echo htmlspecialchars($customerInfo['CMND'] ?? ''); ?>">
                                    <div class="error-message" id="contactCMNDError">Vui lòng nhập CMND/CCCD/Hộ chiếu</div>
                                    <small class="text-muted">CMND (9 số) hoặc CCCD (12 số) hoặc Hộ chiếu</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required-field">Địa chỉ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contactAddress" name="address" required
                                        placeholder="192-126 Đ.Nguyễn Văn Nghi, Phường 1, Gò Vấp, Thành phố Hồ Chí Minh"
                                        value="<?php echo htmlspecialchars($customerInfo['DiaChi'] ?? ''); ?>">
                                    <div class="error-message" id="contactAddressError">Vui lòng nhập địa chỉ</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KHÁCH HÀNG BỔ SUNG -->
                    <?php for ($i = 1; $i < $adults; $i++):
                        $guestNumber = $i + 1;
                    ?>
                        <div class="additional-guest-section mb-4" id="guestSection<?php echo $guestNumber; ?>">
                            <h6 class="guest-section-header">
                                <i class="fas fa-user-friends me-2"></i> Khách hàng thứ <?php echo $guestNumber; ?>
                                <span class="badge bg-primary ms-2">Bắt buộc</span>
                            </h6>

                            <div class="mb-3">
                                <label class="form-label required-field">Họ tên</label>
                                <input type="text" class="form-control guest-input" name="guestName[]" required
                                    placeholder="Nhập họ và tên đầy đủ">
                                <div class="error-message" id="guestNameError<?php echo $guestNumber; ?>">Vui lòng nhập họ tên</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required-field">Số điện thoại</label>
                                        <div class="input-group">
                                            <span class="input-group-text">+84</span>
                                            <input type="tel" class="form-control guest-input phone-input" 
                                                name="guestPhone[]" required placeholder="901234567">
                                        </div>
                                        <div class="error-message" id="guestPhoneError<?php echo $guestNumber; ?>">Vui lòng nhập số điện thoại</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required-field">Địa chỉ</label>
                                        <input type="text" class="form-control guest-input address-input" name="guestAddress[]" required
                                            placeholder="Nhập địa chỉ đầy đủ">
                                        <div class="error-message" id="guestAddressError<?php echo $guestNumber; ?>">Vui lòng nhập địa chỉ</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <!-- THÔNG TIN BỔ SUNG -->
                    <div class="common-info-section mt-4 pt-4 border-top">
                        <div class="mb-3">
                            <label class="form-label">Yêu cầu đặc biệt (không bắt buộc)</label>
                            <textarea class="form-control" name="specialRequests" rows="3" placeholder="Bạn cần thêm giường phụ hoặc có yêu cầu đặc biệt?"></textarea>
                            <small class="text-muted">Xin lưu ý yêu cầu đặc biệt không được bảo đảm trước và có thể thu phí</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nonSmoking" id="nonSmoking" required>
                            <label class="form-check-label required-field" for="nonSmoking">
                                Phòng không hút thuốc
                            </label>
                            <div class="error-message" id="nonSmokingError">Vui lòng đồng ý với điều khoản phòng không hút thuốc</div>
                        </div>
                    </div>
                    
                    <!-- HIỂN THỊ LỖI TRÙNG SDT -->
                    <div class="duplicate-notice mt-3 d-none" id="duplicatePhoneNotice">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Cảnh báo:</strong> Có số điện thoại bị trùng lặp
                        <ul id="duplicatePhoneList"></ul>
                    </div>
                </div>
            </div>

            <!-- Phương thức thanh toán -->
            <div class="payment-section">
                <div class="section-header">
                    Phương thức thanh toán <span class="text-danger">*</span>
                </div>
                <div class="section-body">
                    <!-- Cách 1: Dùng label bao toàn bộ -->
                    <label class="payment-method cursor-pointer mb-3 d-block" for="creditCard">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="radio" name="paymentMethod" value="creditCard" id="creditCard">
                            <div class="form-check-label fw-bold">
                                Thẻ tín dụng/Ghi nợ
                            </div>
                            <div class="mt-2">
                                <img src="/ABC-Resort/client/assets/images/payments/visa_mastercard.jpg" alt="Visa Mastercard" height="30" class="me-2">
                                <small class="text-muted">Thanh toán an toàn với thẻ Visa, Mastercard</small>
                            </div>
                        </div>
                    </label>

                    <label class="payment-method cursor-pointer mb-3 d-block" for="bankTransfer">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="radio" name="paymentMethod" value="bankTransfer" id="bankTransfer">
                            <div class="form-check-label fw-bold">
                                Chuyển khoản ngân hàng
                            </div>
                            <div class="mt-2">
                                <img src="/ABC-Resort/client/assets/images/payments/bank.jpg" alt="Banking" height="30" class="me-2">
                                <small class="text-muted">Chuyển khoản qua Internet Banking, Mobile Banking</small>
                            </div>
                        </div>
                    </label>

                    <label class="payment-method cursor-pointer mb-3 d-block" for="cash">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="radio" name="paymentMethod" value="cash" id="cash">
                            <div class="form-check-label fw-bold">
                                Thanh toán tại resort
                            </div>
                            <div class="mt-2">
                                <img src="/ABC-Resort/client/assets/images/payments/cash.jpg" alt="Cash" height="30" class="me-2">
                                <small class="text-muted">Thanh toán bằng tiền mặt khi nhận phòng</small>
                            </div>
                        </div>
                    </label>
                    <div class="error-message" id="paymentMethodError"></div>
                </div>
            </div>
            <!-- PHẦN KHUYẾN MÃI MỚI -->
            <div class="payment-section">
                <div class="section-header">
                    <i class="fas fa-gift me-2"></i> Khuyến mãi
                </div>
                <div class="section-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn khuyến mãi:</label>
                        <div id="promotionList">
                            <?php if (!empty($promotions)): ?>
                                <?php foreach ($promotions as $promo): ?>
                                    <div class="promotion-item mb-2" style="cursor: pointer">
                                        <div class="form-check">
                                            <input class="form-check-input promotion-checkbox"
                                                type="radio"
                                                name="promotion"
                                                value="<?php echo $promo['MaKM']; ?>"
                                                id="promo<?php echo $promo['MaKM']; ?>"
                                                <?php echo !$promo['is_available'] ? 'disabled' : ''; ?>
                                                data-discount="<?php echo $promo['MucGiamGia']; ?>"
                                                data-type="<?php echo $promo['LoaiGiamGia']; ?>"
                                                data-max="<?php echo $promo['GiamGiaToiDa']; ?>"
                                                onchange="applyPromotion(this)">
                                            <label class="form-check-label <?php echo !$promo['is_available'] ? 'text-muted' : ''; ?>"
                                                for="promo<?php echo $promo['MaKM']; ?>">
                                                <strong><?php echo htmlspecialchars($promo['TenKhuyenMai']); ?></strong>
                                                <span class="badge <?php echo $promo['is_available'] ? 'bg-success' : 'bg-secondary'; ?> ms-2">
                                                    <?php if ($promo['LoaiGiamGia'] == 'phantram'): ?>
                                                        Giảm <?php echo $promo['MucGiamGia']; ?>%

                                                    <?php else: ?>
                                                        Giảm <?php echo number_format($promo['MucGiamGia']); ?> VND
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($promo['is_available']): ?>
                                                    <small class="d-block text-success mt-1">
                                                        <i class="fas fa-check-circle"></i>
                                                        <?php echo $promo['MoTa']; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="d-block text-danger mt-1">
                                                        <i class="fas fa-times-circle"></i>
                                                        <?php echo $promo['reason']; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Không có khuyến mãi khả dụng</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearPromotion">
                        <i class="fas fa-times me-1"></i> Bỏ chọn khuyến mãi
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column - Tóm tắt đơn hàng -->
        <div class="col-lg-4">
            <div class="sticky-summary">
                <!-- Thông tin khách sạn -->
                <div class="room-info mb-3">
                    <h5 class="fw-bold mb-2">Tỏa Sáng Resort Nha Trang</h5>
                    <div class="d-flex align-items-center mb-2">
                        <span class="hotel-rating me-2">8.6/10</span>
                        <small class="text-muted">(306 đánh giá)</small>
                    </div>

                    <div class="room-details">
                        <div class="fw-semibold"><?php echo htmlspecialchars($room['roomName']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($room['HangPhong']); ?> - <?php echo $room['DienTich']; ?>m²</small>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Nhận phòng:</small>
                                <small class="fw-semibold"><?php echo date('d/m/Y', strtotime($checkin)); ?></small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small>Trả phòng:</small>
                                <small class="fw-semibold"><?php echo date('d/m/Y', strtotime($checkout)); ?></small>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <small>Số đêm:</small>
                                <small class="fw-semibold"><?php echo $nights; ?> đêm</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small>Số khách:</small>
                                <small class="fw-semibold"><?php echo $adults; ?> người</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="price-breakdown">
                    <h6 class="fw-bold mb-3">Chi tiết giá</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <small>Giá phòng (<?php echo $nights; ?> đêm):</small>
                        <small id="roomPriceDisplay"><?php echo number_format($roomPrice); ?> VND</small>
                    </div>

                    <?php if ($servicesPrice > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <small>Dịch vụ bổ sung (<?php echo $adults; ?> người):</small>
                            <small id="servicesPriceDisplay"><?php echo number_format($servicesPrice); ?> VND</small>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between mb-2">
                            <small>Dịch vụ bổ sung:</small>
                            <small id="servicesPriceDisplay">0 VND</small>
                        </div>
                    <?php endif; ?>

                    <!-- DÒNG KHUYẾN MÃI - BAN ĐẦU ẨN -->
                    <div class="d-flex justify-content-between mb-2 d-none" id="discountSection">
                        <small class="text-success">Khuyến mãi:</small>
                        <small class="text-success" id="discountAmount"></small>
                    </div>
                    <!-- DÒNG THUẾ - THÊM ID -->
                    <div class="d-flex justify-content-between mb-2">
                        <small>Thuế và phí (<?php echo isset($taxRate) ? ($taxRate * 100) : '10'; ?>%):</small>
                        <small id="taxDisplay"><?php echo number_format($tax); ?> VND</small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Tổng cộng</div>
                            <small class="text-muted"><?php echo $nights; ?> đêm, <?php echo $adults; ?> khách</small>
                        </div>
                        <div class="text-end">
                            <!-- Giá gốc có gạch ngang -->
                            <div class="text-muted text-decoration-line-through small" id="originalTotal">
                                <?php echo number_format($totalAmount); ?> VND
                            </div>
                            <!-- Giá sau giảm -->
                            <div class="total-price" id="finalTotal"><?php echo number_format($totalAmount); ?> VND</div>
                        </div>
                    </div>
                </div>

                <!-- Điều khoản -->
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                    <label class="form-check-label" for="agreeTerms">
                        Tôi đồng ý với <a href="#" target="_blank">Điều khoản và Điều kiện</a>
                    </label>
                    <div class="error-message" id="termsError"></div>
                </div>

                <!-- Nút thanh toán -->
                <button class="btn btn-primary w-100 py-3 fw-bold mt-3" onclick="processPayment()">
                    THANH TOÁN NGAY
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    // CHUYỂN BIẾN NÀY LÊN ĐẦU
    const originalTotalBeforeTax = <?php echo $roomPrice + $servicesPrice; ?>;
    const originalTax = <?php echo $tax; ?>;
    const originalTotal = <?php echo $totalAmount; ?>;
    const taxRate = <?php echo isset($taxRate) ? $taxRate : 0.1; ?>; // Lấy từ database

    // Chuyển các biến promotions từ PHP sang JS
    const promotionsData = <?php echo json_encode($promotions); ?>;

    // Khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== TRANG ĐÃ LOAD ===');
        console.log('Tổng trước thuế:', originalTotalBeforeTax);
        console.log('Thuế ban đầu:', originalTax);
        console.log('Tổng ban đầu:', originalTotal);
        console.log('Tỷ lệ thuế:', taxRate);
        console.log('Danh sách khuyến mãi:', promotionsData);

        // Setup nút bỏ chọn
        const clearBtn = document.getElementById('btnClearPromotion');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearPromotion);
        }

        // Thêm sự kiện kiểm tra trùng SDT real-time
        document.querySelectorAll('.phone-input').forEach(input => {
            input.addEventListener('blur', function() {
                checkPhoneDuplicatesRealTime();
            });
            input.addEventListener('input', function() {
                // Clear duplicate highlight khi người dùng sửa
                this.classList.remove('is-duplicate');
                hideDuplicateNotice();
            });
        });

        // Thêm sự kiện cho SDT khách hàng chính
        document.getElementById('contactPhone').addEventListener('blur', function() {
            checkPhoneDuplicatesRealTime();
        });
        document.getElementById('contactPhone').addEventListener('input', function() {
            this.classList.remove('is-duplicate');
            hideDuplicateNotice();
        });
        
        // Thêm sự kiện validate địa chỉ real-time
        document.querySelectorAll('.address-input').forEach(input => {
            input.addEventListener('blur', function() {
                validateAddressField(this);
            });
        });
        
        // Validate địa chỉ khách hàng chính
        document.getElementById('contactAddress').addEventListener('blur', function() {
            validateAddressField(this);
        });
    });
    
    // Hàm validate địa chỉ real-time
    function validateAddressField(inputElement) {
        const value = inputElement.value.trim();
        const guestNumber = getGuestNumberFromInput(inputElement);
        const errorId = guestNumber === 'contact' ? 'contactAddressError' : `guestAddressError${guestNumber}`;
        
        if (!value) {
            showError(inputElement, errorId, 'Vui lòng nhập địa chỉ');
            return false;
        } else if (value.length < 10) {
            showError(inputElement, errorId, 'Địa chỉ quá ngắn (ít nhất 10 ký tự)');
            return false;
        } else {
            clearError(inputElement, errorId);
            return true;
        }
    }
    
    // Hàm lấy số thứ tự khách hàng từ input
    function getGuestNumberFromInput(inputElement) {
        if (inputElement.id === 'contactAddress') {
            return 'contact';
        }
        
        const parentSection = inputElement.closest('.additional-guest-section');
        if (parentSection && parentSection.id) {
            const match = parentSection.id.match(/guestSection(\d+)/);
            if (match) {
                return match[1];
            }
        }
        return '';
    }
    
    // Hàm xóa lỗi
    function clearError(inputElement, errorId) {
        inputElement.classList.remove('is-invalid');
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    // Hàm kiểm tra trùng SDT real-time
    function checkPhoneDuplicatesRealTime() {
        const phoneMap = {};
        const duplicates = [];
        
        // Lấy SDT khách hàng chính
        const mainPhone = document.getElementById('contactPhone').value.trim();
        if (mainPhone) {
            phoneMap[mainPhone] = 'Khách hàng chính';
        }
        
        // Lấy SDT khách hàng bổ sung
        const guestPhones = document.querySelectorAll('input[name="guestPhone[]"]');
        guestPhones.forEach((input, index) => {
            const value = input.value.trim();
            if (value) {
                if (phoneMap[value]) {
                    duplicates.push({
                        phone: value,
                        guest1: phoneMap[value],
                        guest2: `Khách hàng ${index + 2}`
                    });
                } else {
                    phoneMap[value] = `Khách hàng ${index + 2}`;
                }
            }
        });
        
        // Highlight và hiển thị thông báo
        if (duplicates.length > 0) {
            showDuplicateNotice(duplicates);
            highlightDuplicatePhones(duplicates, phoneMap);
            return true;
        } else {
            hideDuplicateNotice();
            removeDuplicateHighlights();
            return false;
        }
    }
    
    // Hàm hiển thị thông báo trùng SDT
    function showDuplicateNotice(duplicates) {
        const notice = document.getElementById('duplicatePhoneNotice');
        const list = document.getElementById('duplicatePhoneList');
        
        list.innerHTML = '';
        duplicates.forEach(dup => {
            const li = document.createElement('li');
            li.textContent = `Số điện thoại ${dup.phone} được sử dụng bởi ${dup.guest1} và ${dup.guest2}`;
            list.appendChild(li);
        });
        
        notice.classList.remove('d-none');
    }
    
    // Hàm ẩn thông báo trùng SDT
    function hideDuplicateNotice() {
        document.getElementById('duplicatePhoneNotice').classList.add('d-none');
    }
    
    // Hàm highlight các SDT trùng
    function highlightDuplicatePhones(duplicates, phoneMap) {
        // Reset highlight
        removeDuplicateHighlights();
        
        // Tìm tất cả các SDT trùng
        const duplicatePhones = duplicates.map(d => d.phone);
        
        // Highlight SDT khách hàng chính nếu trùng
        const mainPhone = document.getElementById('contactPhone').value.trim();
        if (duplicatePhones.includes(mainPhone)) {
            document.getElementById('contactPhone').classList.add('is-duplicate');
        }
        
        // Highlight SDT khách hàng bổ sung nếu trùng
        const guestPhones = document.querySelectorAll('input[name="guestPhone[]"]');
        guestPhones.forEach(input => {
            if (duplicatePhones.includes(input.value.trim())) {
                input.classList.add('is-duplicate');
            }
        });
    }
    
    // Hàm xóa highlight trùng
    function removeDuplicateHighlights() {
        document.getElementById('contactPhone').classList.remove('is-duplicate');
        document.querySelectorAll('input[name="guestPhone[]"]').forEach(input => {
            input.classList.remove('is-duplicate');
        });
    }

    // Hàm validate thông tin
    function validateGuests() {
        let isValid = true;

        // Reset all error messages
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        document.querySelectorAll('.form-control').forEach(el => {
            el.classList.remove('is-invalid');
        });

        // Validate thông tin liên hệ (khách hàng chính)
        const contactName = document.getElementById('contactName').value.trim();
        const contactPhone = document.getElementById('contactPhone').value.trim();
        const contactEmail = document.getElementById('contactEmail').value.trim();
        const contactCMND = document.getElementById('contactCMND').value.trim();
        const contactAddress = document.getElementById('contactAddress').value.trim();

        // Validate họ tên liên hệ
        if (!contactName) {
            showError(document.getElementById('contactName'), 'contactNameError', 'Vui lòng nhập họ tên');
            isValid = false;
        }

        // Validate số điện thoại liên hệ
        if (!contactPhone) {
            showError(document.getElementById('contactPhone'), 'contactPhoneError', 'Vui lòng nhập số điện thoại');
            isValid = false;
        } else if (!/^[0-9]{9,10}$/.test(contactPhone)) {
            showError(document.getElementById('contactPhone'), 'contactPhoneError', 'Số điện thoại không hợp lệ (9-10 số)');
            isValid = false;
        }

        // Validate email liên hệ
        if (!contactEmail) {
            showError(document.getElementById('contactEmail'), 'contactEmailError', 'Vui lòng nhập email');
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contactEmail)) {
            showError(document.getElementById('contactEmail'), 'contactEmailError', 'Email không hợp lệ');
            isValid = false;
        }

        // Validate CMND liên hệ
        if (!contactCMND) {
            showError(document.getElementById('contactCMND'), 'contactCMNDError', 'Vui lòng nhập CMND/CCCD/Hộ chiếu');
            isValid = false;
        }

        // Validate địa chỉ liên hệ (BẮT BUỘC)
        if (!contactAddress) {
            showError(document.getElementById('contactAddress'), 'contactAddressError', 'Vui lòng nhập địa chỉ');
            isValid = false;
        } else if (contactAddress.length < 10) {
            showError(document.getElementById('contactAddress'), 'contactAddressError', 'Địa chỉ quá ngắn (ít nhất 10 ký tự)');
            isValid = false;
        }

        // Validate khách hàng bổ sung
        const guestNames = document.querySelectorAll('input[name="guestName[]"]');
        const guestPhones = document.querySelectorAll('input[name="guestPhone[]"]');
        const guestAddresses = document.querySelectorAll('input[name="guestAddress[]"]');

        // Mảng lưu các giá trị đã nhập để kiểm tra trùng
        const usedPhones = [contactPhone]; // Bắt đầu với SDT khách hàng chính

        // Validate tất cả khách hàng bổ sung
        for (let i = 0; i < guestNames.length; i++) {
            const guestNumber = i + 2; // Bắt đầu từ khách hàng thứ 2
            const guestNameValue = guestNames[i].value.trim();
            const guestPhoneValue = guestPhones[i] ? guestPhones[i].value.trim() : '';
            const guestAddressValue = guestAddresses[i] ? guestAddresses[i].value.trim() : '';

            // Validate họ tên
            if (!guestNameValue) {
                showError(guestNames[i], `guestNameError${guestNumber}`, `Vui lòng nhập họ tên khách hàng ${guestNumber}`);
                isValid = false;
            }

            // Validate số điện thoại (BẮT BUỘC)
            if (!guestPhoneValue) {
                showError(guestPhones[i], `guestPhoneError${guestNumber}`, `Vui lòng nhập số điện thoại khách hàng ${guestNumber}`);
                isValid = false;
            } else if (!/^[0-9]{9,10}$/.test(guestPhoneValue)) {
                showError(guestPhones[i], `guestPhoneError${guestNumber}`, `Số điện thoại không hợp lệ (9-10 số)`);
                isValid = false;
            } else {
                // Kiểm tra trùng số điện thoại
                if (usedPhones.includes(guestPhoneValue)) {
                    showError(guestPhones[i], `guestPhoneError${guestNumber}`, `Số điện thoại này đã được sử dụng bởi khách hàng khác`);
                    isValid = false;
                } else {
                    usedPhones.push(guestPhoneValue);
                }
            }

            // Validate địa chỉ (BẮT BUỘC)
            if (!guestAddressValue) {
                showError(guestAddresses[i], `guestAddressError${guestNumber}`, `Vui lòng nhập địa chỉ khách hàng ${guestNumber}`);
                isValid = false;
            } else if (guestAddressValue.length < 10) {
                showError(guestAddresses[i], `guestAddressError${guestNumber}`, `Địa chỉ quá ngắn (ít nhất 10 ký tự)`);
                isValid = false;
            }
        }

        // Validate phòng không hút thuốc
        const nonSmoking = document.getElementById('nonSmoking');
        if (!nonSmoking.checked) {
            document.getElementById('nonSmokingError').style.display = 'block';
            isValid = false;
        }

        // Validate phương thức thanh toán
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
        if (!paymentMethod) {
            document.getElementById('paymentMethodError').textContent = 'Vui lòng chọn phương thức thanh toán';
            document.getElementById('paymentMethodError').style.display = 'block';
            isValid = false;
        }

        // Validate điều khoản
        const agreeTerms = document.getElementById('agreeTerms');
        if (!agreeTerms.checked) {
            document.getElementById('termsError').textContent = 'Vui lòng đồng ý với điều khoản và điều kiện';
            document.getElementById('termsError').style.display = 'block';
            isValid = false;
        }

        return isValid;
    }

    // Hàm kiểm tra trùng số điện thoại chi tiết
    function checkPhoneDuplicates() {
        const phoneMap = {};
        const duplicates = [];
        
        // Lấy SDT khách hàng chính
        const mainPhone = document.getElementById('contactPhone').value.trim();
        if (mainPhone) {
            phoneMap[mainPhone] = 'Khách hàng chính';
        }
        
        // Lấy SDT khách hàng bổ sung
        const guestPhones = document.querySelectorAll('input[name="guestPhone[]"]');
        guestPhones.forEach((input, index) => {
            const value = input.value.trim();
            if (value) {
                if (phoneMap[value]) {
                    duplicates.push({
                        phone: value,
                        guest1: phoneMap[value],
                        guest2: `Khách hàng ${index + 2}`
                    });
                } else {
                    phoneMap[value] = `Khách hàng ${index + 2}`;
                }
            }
        });
        
        return duplicates;
    }

    // Hàm hiển thị lỗi
    function showError(inputElement, errorId, message) {
        inputElement.classList.add('is-invalid');
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        } else {
            // Tạo error element nếu chưa có
            const newError = document.createElement('div');
            newError.id = errorId;
            newError.className = 'error-message';
            newError.textContent = message;
            newError.style.display = 'block';
            inputElement.parentNode.appendChild(newError);
        }
    }

    // Hàm xử lý thanh toán
    function processPayment() {
        // Reset all errors
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });
        
        hideDuplicateNotice();
        removeDuplicateHighlights();

        // Kiểm tra trùng SDT real-time trước
        const hasDuplicates = checkPhoneDuplicatesRealTime();
        if (hasDuplicates) {
            alert('Có số điện thoại bị trùng lặp. Vui lòng kiểm tra và sửa lại.');
            return;
        }

        // Validate form
        if (!validateGuests()) {
            alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
            return;
        }

        // Kiểm tra trùng số điện thoại chi tiết
        const phoneDuplicates = checkPhoneDuplicates();
        if (phoneDuplicates.length > 0) {
            const duplicateMessage = phoneDuplicates.map(dup =>
                `• Số điện thoại ${dup.phone} được sử dụng bởi ${dup.guest1} và ${dup.guest2}`
            ).join('\n');
            
            alert(`CÓ THÔNG TIN TRÙNG LẶP:\n\n${duplicateMessage}\n\nVui lòng kiểm tra và sửa lại.`);
            return;
        }

        // Nếu tất cả hợp lệ, tiếp tục với thanh toán
        alert('Thông tin hợp lệ. Tiến hành thanh toán...');
        // Thêm code xử lý thanh toán ở đây
    }

    // Hàm format tiền
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' VND';
    }

    // Hàm xử lý khi tick khuyến mãi
    function applyPromotion(checkbox) {
        console.log('=== CHỌN KHUYẾN MÃI ===');

        // Lấy thông tin khuyến mãi từ dataset
        const discountValue = parseFloat(checkbox.dataset.discount);
        const discountType = checkbox.dataset.type;
        const maxDiscount = parseFloat(checkbox.dataset.max) || 0;
        const promotionId = checkbox.value;

        console.log('Thông tin khuyến mãi:', {
            promotionId,
            discountType,
            discountValue,
            maxDiscount,
            originalTotalBeforeTax,
            taxRate
        });

        // Tính discount theo đúng logic database
        let discountAmount = 0;

        if (discountType === 'phantram') {
            // Giảm theo phần trăm: Tính % của tổng trước thuế
            discountAmount = originalTotalBeforeTax * (discountValue / 100);
            console.log('Discount theo %:', discountValue + '% = ' + discountAmount);

            // Áp dụng giới hạn tối đa nếu có
            if (maxDiscount > 0 && discountAmount > maxDiscount) {
                console.log('Áp dụng giới hạn tối đa:', maxDiscount);
                discountAmount = maxDiscount;
            }
        } else {
            // Giảm theo số tiền cố định
            discountAmount = discountValue;
            console.log('Discount theo số tiền:', discountAmount);

            // Áp dụng giới hạn tối đa nếu có
            if (maxDiscount > 0 && discountAmount > maxDiscount) {
                console.log('Áp dụng giới hạn tối đa:', maxDiscount);
                discountAmount = maxDiscount;
            }
        }

        discountAmount = Math.round(discountAmount);

        // Tính lại tổng sau discount
        const afterDiscount = originalTotalBeforeTax - discountAmount;
        const newTax = afterDiscount * taxRate;
        const finalTotal = afterDiscount + newTax;

        console.log('Kết quả tính toán:', {
            discountAmount: discountAmount,
            afterDiscount: afterDiscount,
            newTax: newTax,
            finalTotal: finalTotal,
            originalTotalBeforeTax: originalTotalBeforeTax
        });

        // CẬP NHẬT GIAO DIỆN
        updateDisplay(discountAmount, newTax, finalTotal);

        console.log('Đã áp dụng khuyến mãi giảm:', discountAmount, 'VND');
    }

    // Hàm cập nhật giao diện
    function updateDisplay(discountAmount, newTax, finalTotal) {
        // 1. DÒNG KHUYẾN MÃI
        const discountSection = document.getElementById('discountSection');
        const discountAmountEl = document.getElementById('discountAmount');

        if (discountAmount > 0) {
            discountSection.classList.remove('d-none');
            discountSection.style.display = 'flex';
            discountAmountEl.textContent = '-' + formatCurrency(discountAmount);
        } else {
            discountSection.classList.add('d-none');
            discountSection.style.display = 'none';
            discountAmountEl.textContent = '';
        }

        // 2. DÒNG THUẾ
        const taxDisplay = document.getElementById('taxDisplay');
        taxDisplay.textContent = formatCurrency(newTax);

        // 3. TỔNG CỘNG
        const originalTotalEl = document.getElementById('originalTotal');
        const finalTotalEl = document.getElementById('finalTotal');

        if (discountAmount > 0) {
            // Hiển thị giá gốc có gạch ngang
            originalTotalEl.style.display = 'block';
            originalTotalEl.textContent = formatCurrency(originalTotal);

            // Hiển thị giá sau giảm
            finalTotalEl.textContent = formatCurrency(finalTotal);
            finalTotalEl.classList.add('text-danger');
        } else {
            // Ẩn giá gốc
            originalTotalEl.style.display = 'none';

            // Hiển thị giá gốc
            finalTotalEl.textContent = formatCurrency(originalTotal);
            finalTotalEl.classList.remove('text-danger');
        }
    }

    // Bỏ chọn khuyến mãi
    function clearPromotion() {
        console.log('=== BỎ CHỌN KHUYẾN MÃI ===');

        // Bỏ tick tất cả checkbox
        document.querySelectorAll('input[name="promotion"]').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Reset về giá ban đầu
        updateDisplay(0, originalTax, originalTotal);

        console.log('Đã reset về ban đầu');
    }
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>