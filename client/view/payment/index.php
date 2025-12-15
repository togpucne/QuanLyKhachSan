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
                <!-- Thông tin liên hệ -->
                <div class="payment-section">
                    <div class="section-header">
                        Thông tin liên hệ
                    </div>
                    <div class="section-body">
                        <div class="mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="customerName" required
                                placeholder="Như trên CMND (không dấu)"
                                value="<?php echo htmlspecialchars($customerInfo['HoTen'] ?? ''); ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+84</span>
                                    <input type="tel" class="form-control" name="customerPhone" required
                                        placeholder="901234567"
                                        value="<?php echo htmlspecialchars($customerInfo['SoDienThoai'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="customerEmail" required
                                    placeholder="email@example.com"
                                    value="<?php echo htmlspecialchars($customerInfo['Email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="bookForMyself" checked>
                            <label class="form-check-label" for="bookForMyself">
                                Tôi đặt chỗ cho chính mình
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Thông tin khách hàng -->
                <div class="payment-section">
                    <div class="section-header">
                        Thông tin Khách hàng
                    </div>
                    <div class="section-body">
                        <!-- Khách hàng chính -->
                        <div class="guest-info mb-4">
                            <h6 class="fw-bold mb-3">Khách hàng chính</h6>
                            <div class="mb-3">
                                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="guestName[]" required
                                    placeholder="Người Việt: nhập Tên đệm + Tên chính + Họ"
                                    value="<?php echo htmlspecialchars($customerInfo['HoTen'] ?? ''); ?>">
                                <div class="error-message" id="guestNameError1">Vui lòng nhập họ tên</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số CMND/CCCD <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="guestIdNumber[]" required
                                    placeholder="Nhập số CMND hoặc CCCD"
                                    value="<?php echo htmlspecialchars($customerInfo['CMND'] ?? ''); ?>">
                                <div class="error-message" id="guestIdError1">Vui lòng nhập CMND/CCCD</div>
                            </div>
                        </div>

                        <!-- Khách hàng thứ 2 (chỉ hiển thị nếu có từ 2 người trở lên) -->
                        <?php if ($adults >= 2): ?>
                            <div class="additional-guest-section" id="secondGuestSection">
                                <h6 class="fw-bold mb-3">Khách hàng thứ 2</h6>
                                <div class="mb-3">
                                    <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="guestName[]" required
                                        placeholder="Người Việt: nhập Tên đệm + Tên chính + Họ">
                                    <div class="error-message" id="guestNameError2">Vui lòng nhập họ tên</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">+84</span>
                                        <input type="tel" class="form-control" name="guestPhone[]"
                                            placeholder="901234567">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="guestAddress[]"
                                        placeholder="Nhập địa chỉ">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Số CMND/CCCD <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="guestIdNumber[]" required
                                        placeholder="Nhập số CMND hoặc CCCD">
                                    <div class="error-message" id="guestIdError2">Vui lòng nhập CMND/CCCD</div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Thông tin chung -->
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="address"
                                placeholder="Nhập địa chỉ"
                                value="<?php echo htmlspecialchars($customerInfo['DiaChi'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yêu cầu đặc biệt (không bắt buộc)</label>
                            <textarea class="form-control" name="specialRequests" rows="3" placeholder="Bạn cần thêm giường phụ hoặc có yêu cầu đặc biệt?"></textarea>
                            <small class="text-muted">Xin lưu ý yêu cầu đặc biệt không được bảo đảm trước và có thể thu phí</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nonSmoking" id="nonSmoking" required>
                            <label class="form-check-label" for="nonSmoking">
                                Phòng không hút thuốc <span class="text-danger">*</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="payment-section">
                    <div class="section-header">
                        Phương thức thanh toán <span class="text-danger">*</span>
                    </div>
                    <div class="section-body">
                        <div class="payment-method" onclick="selectPaymentMethod('creditCard')">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" value="creditCard" id="creditCard">
                                <label class="form-check-label fw-bold" for="creditCard">
                                    Thẻ tín dụng/Ghi nợ
                                </label>
                                <div class="mt-2">
                                    <img src="/ABC-Resort/client/assets/images/payments/visa_mastercard.jpg" alt="Visa Mastercard" height="30" class="me-2">
                                    <small class="text-muted">Thanh toán an toàn với thẻ Visa, Mastercard</small>
                                </div>
                            </div>
                        </div>

                        <div class="payment-method" onclick="selectPaymentMethod('bankTransfer')">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" value="bankTransfer" id="bankTransfer">
                                <label class="form-check-label fw-bold" for="bankTransfer">
                                    Chuyển khoản ngân hàng
                                </label>
                                <div class="mt-2">
                                    <img src="/ABC-Resort/client/assets/images/payments/bank.jpg" alt="Banking" height="30" class="me-2">
                                    <small class="text-muted">Chuyển khoản qua Internet Banking, Mobile Banking</small>
                                </div>
                            </div>
                        </div>

                        <div class="payment-method" onclick="selectPaymentMethod('cash')">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" value="cash" id="cash">
                                <label class="form-check-label fw-bold" for="cash">
                                    Thanh toán tại resort
                                </label>
                                <div class="mt-2">
                                    <img src="/ABC-Resort/client/assets/images/payments/cash.jpg" alt="Cash" height="30" class="me-2">
                                    <small class="text-muted">Thanh toán bằng tiền mặt khi nhận phòng</small>
                                </div>
                            </div>
                        </div>
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
                                        <div class="promotion-item mb-2">
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
                                                        <small class="d-block text-success">
                                                            <i class="fas fa-check-circle"></i>
                                                            <?php echo $promo['MoTa']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="d-block text-danger">
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
                            <small>Thuế và phí (10%):</small>
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
        const originalTotalBeforeTax = <?php echo $roomPrice + $servicesPrice; ?>;
        const originalTax = <?php echo $tax; ?>;
        const originalTotal = <?php echo $totalAmount; ?>;
        const taxRate = 0.1;

        // Hàm format tiền
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' VND';
        }

        // Hàm xử lý khi tick khuyến mãi
        function applyPromotion(checkbox) {
            console.log('=== CHỌN KHUYẾN MÃI ===');

            // Lấy thông tin từ checkbox
            const discountPercent = parseFloat(checkbox.dataset.discount);
            const maxDiscount = parseFloat(checkbox.dataset.max) || 0;

            // Tính discount
            let discountAmount = originalTotalBeforeTax * (discountPercent / 100);
            if (maxDiscount > 0 && discountAmount > maxDiscount) {
                discountAmount = maxDiscount;
            }
            discountAmount = Math.round(discountAmount);

            // Tính lại tổng sau discount
            const afterDiscount = originalTotalBeforeTax - discountAmount;
            const newTax = afterDiscount * taxRate;
            const finalTotal = afterDiscount + newTax;

            console.log('Kết quả:', {
                discountAmount: discountAmount,
                newTax: newTax,
                finalTotal: finalTotal
            });
            if (discountAmount > 0) {
                discountSection.style.display = 'flex'; // HIỆN
            } else {
                discountSection.style.display = 'none'; // ẨN
            }

            // CẬP NHẬT GIAO DIỆN
            updateDisplay(discountAmount, newTax, finalTotal);

            console.log('Đã áp dụng khuyến mãi giảm:', discountAmount, 'VND');
        }
        // Hàm validate form
        function validateForm() {
            let isValid = true;

            // Kiểm tra thông tin liên hệ
            const customerName = document.querySelector('input[name="customerName"]').value.trim();
            const customerPhone = document.querySelector('input[name="customerPhone"]').value.trim();
            const customerEmail = document.querySelector('input[name="customerEmail"]').value.trim();

            if (!customerName) {
                showError('customerName', 'Vui lòng nhập họ tên');
                isValid = false;
            } else {
                hideError('customerName');
            }

            if (!customerPhone) {
                showError('customerPhone', 'Vui lòng nhập số điện thoại');
                isValid = false;
            } else {
                hideError('customerPhone');
            }

            if (!customerEmail) {
                showError('customerEmail', 'Vui lòng nhập email');
                isValid = false;
            } else if (!validateEmail(customerEmail)) {
                showError('customerEmail', 'Vui lòng nhập email hợp lệ');
                isValid = false;
            } else {
                hideError('customerEmail');
            }

            // Kiểm tra thông tin khách hàng
            const guestNames = document.querySelectorAll('input[name="guestName[]"]');
            const guestIds = document.querySelectorAll('input[name="guestIdNumber[]"]');

            for (let i = 0; i < guestNames.length; i++) {
                if (!guestNames[i].value.trim()) {
                    showError(`guestName${i+1}`, `Vui lòng nhập họ tên cho khách hàng ${i+1}`);
                    isValid = false;
                } else {
                    hideError(`guestName${i+1}`);
                }

                if (!guestIds[i].value.trim()) {
                    showError(`guestId${i+1}`, `Vui lòng nhập CMND/CCCD cho khách hàng ${i+1}`);
                    isValid = false;
                } else if (!validateIdNumber(guestIds[i].value.trim())) {
                    showError(`guestId${i+1}`, `CMND/CCCD của khách hàng ${i+1} phải có 9 hoặc 12 số`);
                    isValid = false;
                } else {
                    hideError(`guestId${i+1}`);
                }
            }

            // Kiểm tra phương thức thanh toán
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
            if (!paymentMethod) {
                alert('Vui lòng chọn phương thức thanh toán');
                isValid = false;
            }

            // Kiểm tra đồng ý điều khoản
            if (!document.getElementById('agreeTerms').checked) {
                alert('Vui lòng đồng ý với Điều khoản và Điều kiện');
                isValid = false;
            }

            return isValid;
        }

        // Hàm hiển thị lỗi
        function showError(fieldId, message) {
            const errorElement = document.getElementById(`${fieldId}Error`);
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';

                const inputElement = document.querySelector(`[name="${fieldId}"]`);
                if (inputElement) {
                    inputElement.classList.add('is-invalid');
                }
            }
        }

        // Hàm ẩn lỗi
        function hideError(fieldId) {
            const errorElement = document.getElementById(`${fieldId}Error`);
            if (errorElement) {
                errorElement.style.display = 'none';

                const inputElement = document.querySelector(`[name="${fieldId}"]`);
                if (inputElement) {
                    inputElement.classList.remove('is-invalid');
                }
            }
        }

        // Hàm validate email
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Hàm validate CMND/CCCD
        function validateIdNumber(idNumber) {
            const idRegex = /^[0-9]{9}$|^[0-9]{12}$/;
            return idRegex.test(idNumber);
        }

        // Sửa lại hàm processPayment
        function processPayment() {
            console.log('=== BẮT ĐẦU THANH TOÁN ===');

            if (!validateForm()) {
                console.log('Validation failed');
                return;
            }

            // Kiểm tra xem đã đăng nhập chưa
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            if (!isLoggedIn) {
                alert('Vui lòng đăng nhập để thanh toán');
                window.location.href = '/ABC-Resort/client/view/login.php';
                return;
            }

            console.log('Form validation passed');

            // Lấy thông tin form
            const formData = {
                customerName: document.querySelector('input[name="customerName"]').value,
                customerPhone: document.querySelector('input[name="customerPhone"]').value,
                customerEmail: document.querySelector('input[name="customerEmail"]').value,
                guests: [],
                address: document.querySelector('input[name="address"]').value,
                specialRequests: document.querySelector('textarea[name="specialRequests"]').value,
                nonSmoking: document.querySelector('input[name="nonSmoking"]').checked,
                paymentMethod: document.querySelector('input[name="paymentMethod"]:checked')?.value,
                promotion: document.querySelector('input[name="promotion"]:checked')?.value,
                agreeTerms: document.getElementById('agreeTerms').checked,
                roomId: <?php echo $_GET['roomId'] ?? 0; ?>,
                checkin: '<?php echo $_GET['checkin'] ?? ''; ?>',
                checkout: '<?php echo $_GET['checkout'] ?? ''; ?>',
                adults: <?php echo $adults; ?>,
                nights: <?php echo $nights; ?>,
                services: '<?php echo $_GET['services'] ?? ''; ?>'
            };

            // Lấy thông tin khách hàng
            const guestNames = document.querySelectorAll('input[name="guestName[]"]');
            const guestIds = document.querySelectorAll('input[name="guestIdNumber[]"]');
            const guestPhones = document.querySelectorAll('input[name="guestPhone[]"]');
            const guestAddresses = document.querySelectorAll('input[name="guestAddress[]"]');

            for (let i = 0; i < guestNames.length; i++) {
                formData.guests.push({
                    name: guestNames[i].value,
                    idNumber: guestIds[i].value,
                    phone: guestPhones[i] ? guestPhones[i].value : '',
                    address: guestAddresses[i] ? guestAddresses[i].value : ''
                });
            }

            console.log('Dữ liệu thanh toán:', formData);

            // Hiển thị thông báo xác nhận
            const confirmation = confirm('Bạn có chắc chắn muốn thanh toán?\n\nTổng số tiền: ' + document.getElementById('finalTotal').textContent);

            if (confirmation) {
                // Gọi API thanh toán
                processPaymentAPI(formData);
            }
        }

        // Hàm gọi API thanh toán
        function processPaymentAPI(formData) {
            const btn = document.querySelector('button[onclick="processPayment()"]');
            const originalText = btn.textContent;
            btn.textContent = 'ĐANG XỬ LÝ...';
            btn.disabled = true;

            // Tạm thời chuyển đến trang xác nhận
            setTimeout(() => {
                alert('Thanh toán thành công! Chuyển đến trang xác nhận...');
                // window.location.href = '/ABC-Resort/client/view/confirmation.php';

                btn.textContent = originalText;
                btn.disabled = false;
            }, 2000);
        }

        // Thêm sự kiện validation cho các input
        document.addEventListener('DOMContentLoaded', function() {
            // Thêm validation real-time cho các input
            const inputs = document.querySelectorAll('input[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });

                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        validateField(this);
                    }
                });
            });

            // Thêm sự kiện cho phương thức thanh toán
            document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.payment-method').forEach(method => {
                        method.classList.remove('selected');
                    });
                    if (this.checked) {
                        this.closest('.payment-method').classList.add('selected');
                    }
                });
            });
        });

        // Hàm validate từng field
        function validateField(field) {
            const value = field.value.trim();
            const fieldName = field.name;

            if (!value) {
                showError(fieldName, 'Trường này là bắt buộc');
                return false;
            }

            if (fieldName.includes('customerEmail') && !validateEmail(value)) {
                showError(fieldName, 'Vui lòng nhập email hợp lệ');
                return false;
            }

            if (fieldName.includes('guestIdNumber') && !validateIdNumber(value)) {
                showError(fieldName, 'CMND/CCCD phải có 9 hoặc 12 số');
                return false;
            }

            hideError(fieldName);
            return true;
        }

        // Hàm chọn phương thức thanh toán
        function selectPaymentMethod(method) {
            document.getElementById(method).checked = true;
            document.querySelectorAll('.payment-method').forEach(m => {
                m.classList.remove('selected');
            });
            document.getElementById(method).closest('.payment-method').classList.add('selected');
        }
        // Hàm cập nhật giao diện
        function updateDisplay(discountAmount, newTax, finalTotal) {
            // 1. DÒNG KHUYẾN MÃI
            const discountSection = document.getElementById('discountSection');
            const discountAmountEl = document.getElementById('discountAmount');

            if (discountAmount > 0) {
                // Sử dụng setAttribute để ghi đè CSS !important
                discountSection.setAttribute('style', 'display: flex !important');
                discountAmountEl.textContent = '-' + formatCurrency(discountAmount);
            } else {
                discountSection.setAttribute('style', 'display: none !important');
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

            const discountSection = document.getElementById('discountSection');
            const discountAmountEl = document.getElementById('discountAmount');

            // Sử dụng setAttribute để ẩn
            discountSection.setAttribute('style', 'display: none !important');
            discountAmountEl.textContent = '';

            // Reset về giá ban đầu
            updateDisplay(0, originalTax, originalTotal);

            console.log('Đã reset về ban đầu');
        }

        // Khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== TRANG ĐÃ LOAD ===');
            console.log('Tổng trước thuế:', originalTotalBeforeTax);
            console.log('Thuế ban đầu:', originalTax);
            console.log('Tổng ban đầu:', originalTotal);

            // Setup nút bỏ chọn
            const clearBtn = document.getElementById('btnClearPromotion');
            if (clearBtn) {
                clearBtn.addEventListener('click', clearPromotion);
            }


        });
    </script>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>