    <?php
    include __DIR__ . '/../layouts/header.php';

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
                            <label class="form-label">Họ tên *</label>
                            <input type="text" class="form-control" name="customerName" required
                                placeholder="Như trên CMND (không dấu)"
                                value="<?php echo htmlspecialchars($customerInfo['HoTen'] ?? ''); ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại *</label>
                                <div class="input-group">
                                    <span class="input-group-text">+84</span>
                                    <input type="tel" class="form-control" name="customerPhone" required
                                        placeholder="901234567"
                                        value="<?php echo htmlspecialchars($customerInfo['SoDienThoai'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
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
                        <div class="mb-3">
                            <label class="form-label">Họ tên *</label>
                            <input type="text" class="form-control" name="guestName" required
                                placeholder="Người Việt: nhập Tên đệm + Tên chính + Họ"
                                value="<?php echo htmlspecialchars($customerInfo['HoTen'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số CMND/CCCD *</label>
                            <input type="text" class="form-control" name="customerIdNumber" required
                                placeholder="Nhập số CMND hoặc CCCD"
                                value="<?php echo htmlspecialchars($customerInfo['CMND'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" name="address"
                                placeholder="Nhập địa chỉ"
                                value="<?php echo htmlspecialchars($customerInfo['DiaChi'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yêu cầu đặc biệt</label>
                            <textarea class="form-control" name="specialRequests" rows="3" placeholder="Bạn cần thêm giường phụ hoặc có yêu cầu đặc biệt?"></textarea>
                            <small class="text-muted">Xin lưu ý yêu cầu đặc biệt không được bảo đảm trước và có thể thu phí</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nonSmoking" id="nonSmoking">
                            <label class="form-check-label" for="nonSmoking">
                                Phòng không hút thuốc
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="payment-section">
                    <div class="section-header">
                        Phương thức thanh toán
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
                                                    data-type="<?php echo $promo['LoaiGiamGia']; ?>">
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

                    <!-- Chi tiết giá - CÓ KHUYẾN MÃI -->
                    <div class="price-breakdown">
                        <h6 class="fw-bold mb-3">Chi tiết giá</h6>

                        <div class="d-flex justify-content-between mb-2">
                            <small>Giá phòng (<?php echo $nights; ?> đêm):</small>
                            <small id="roomPriceDisplay"><?php echo number_format($roomPrice); ?> VND</small>
                        </div>

                        <?php if ($servicesPrice > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <small>Dịch vụ bổ sung:</small>
                                <small id="servicesPriceDisplay"><?php echo number_format($servicesPrice); ?> VND</small>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between mb-2">
                                <small>Dịch vụ bổ sung:</small>
                                <small id="servicesPriceDisplay">0 VND</small>
                            </div>
                        <?php endif; ?>

                        <!-- DÒNG GIẢM GIÁ -->
                        <div class="d-flex justify-content-between mb-2" id="discountSection">
                            <small class="text-success">Khuyến mãi:</small>
                            <small class="text-success" id="discountAmount">-0 VND</small>
                        </div>

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
        // Biến toàn cục - LẤY TỪ PHP
        let originalTotal = <?php echo $totalAmount; ?>; // Tổng đã bao gồm thuế
        let selectedPromotion = null;

        // Hàm helper: Format tiền tệ
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' VND';
        }

        // Tính toán giảm giá DỰA TRÊN TỔNG ĐÃ CÓ THUẾ
        function calculateDiscount() {
            if (!selectedPromotion) return 0;

            const discountType = selectedPromotion.type;
            const discountValue = parseFloat(selectedPromotion.discount);
            let discountAmount = 0;

            if (discountType === 'phantram') {
                // GIẢM THEO PHẦN TRĂM TRÊN TỔNG ĐÃ CÓ THUẾ
                discountAmount = originalTotal * (discountValue / 100);
            } else if (discountType === 'tientruc') {
                // GIẢM THEO TIỀN TRỰC TIẾP
                discountAmount = discountValue;
            }

            // Đảm bảo không giảm quá tổng tiền
            if (discountAmount > originalTotal) {
                discountAmount = originalTotal;
            }

            return Math.round(discountAmount);
        }

        // Tính tổng sau giảm
        function calculateFinalTotal() {
            const discountAmount = calculateDiscount();
            return originalTotal - discountAmount;
        }

        // Cập nhật hiển thị tất cả các dòng giá
        function updateAllPriceDisplay() {
            const discountAmount = calculateDiscount();
            const finalTotal = calculateFinalTotal();

            console.log('Cập nhật giá:', {
                originalTotal: originalTotal,
                discountAmount: discountAmount,
                finalTotal: finalTotal,
                promotion: selectedPromotion
            });

            // Tìm các phần tử
            const discountSection = document.getElementById('discountSection');
            const discountAmountEl = document.getElementById('discountAmount');
            const originalTotalEl = document.getElementById('originalTotal');
            const finalTotalEl = document.getElementById('finalTotal');

            // Cập nhật dòng giảm giá
            if (discountAmountEl && discountSection) {
                if (discountAmount > 0) {
                    discountSection.style.display = 'flex';
                    discountAmountEl.textContent = '-' + formatCurrency(discountAmount);

                    // Thêm thông tin chi tiết về khuyến mãi
                    const discountNameEl = document.getElementById('discountName');
                    if (selectedPromotion && !discountNameEl) {
                        discountSection.querySelector('small.text-success').innerHTML =
                            'Khuyến mãi: <span id="discountName" class="ms-1 fw-semibold">' +
                            getPromotionName(selectedPromotion.id) + '</span>';
                    }
                } else {
                    discountSection.style.display = 'none';
                }
            }

            // Cập nhật tổng cộng
            if (originalTotalEl && finalTotalEl) {
                if (discountAmount > 0) {
                    // Hiển thị giá gốc có gạch ngang
                    originalTotalEl.style.display = 'block';
                    originalTotalEl.textContent = formatCurrency(originalTotal);

                    // Hiển thị giá sau giảm
                    finalTotalEl.textContent = formatCurrency(finalTotal);
                    finalTotalEl.classList.add('text-danger');
                    finalTotalEl.classList.add('new-price');

                    console.log('Đã cập nhật giá:', {
                        goc: formatCurrency(originalTotal),
                        moi: formatCurrency(finalTotal),
                        giam: formatCurrency(discountAmount)
                    });
                } else {
                    // Ẩn giá gốc
                    originalTotalEl.style.display = 'none';

                    // Hiển thị giá gốc
                    finalTotalEl.textContent = formatCurrency(originalTotal);
                    finalTotalEl.classList.remove('text-danger');
                    finalTotalEl.classList.remove('new-price');
                }
            }

            // Cập nhật giá trong form ẩn
            updateHiddenFormValues(discountAmount, finalTotal);

            // Hiệu ứng
            if (discountAmount > 0) {
                animatePriceChange();
            }
        }

        // Lấy tên khuyến mãi từ danh sách
        function getPromotionName(promoId) {
            const promoItems = document.querySelectorAll('.promotion-item');
            for (const item of promoItems) {
                const checkbox = item.querySelector('input[name="promotion"]');
                if (checkbox && checkbox.value == promoId) {
                    const label = item.querySelector('label strong');
                    return label ? label.textContent : 'Khuyến mãi';
                }
            }
            return 'Khuyến mãi';
        }

        // Cập nhật giá trị ẩn trong form
        function updateHiddenFormValues(discountAmount, finalTotal) {
            // Tạo hoặc tìm các input ẩn
            let totalAmountInput = document.querySelector('input[name="totalAmount"]');
            let discountAmountInput = document.querySelector('input[name="discountAmount"]');
            let finalAmountInput = document.querySelector('input[name="finalAmount"]');

            // Nếu chưa có, tạo mới
            const priceBreakdown = document.querySelector('.price-breakdown');
            if (priceBreakdown && !totalAmountInput) {
                priceBreakdown.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="totalAmount" value="${originalTotal}">
                <input type="hidden" name="discountAmount" value="${discountAmount}">
                <input type="hidden" name="finalAmount" value="${finalTotal}">
            `);
            } else if (totalAmountInput) {
                // Cập nhật giá trị nếu đã tồn tại
                totalAmountInput.value = originalTotal;
                discountAmountInput.value = discountAmount;
                finalAmountInput.value = finalTotal;
            }
        }

        // Xử lý chọn khuyến mãi
        function handlePromotionSelect() {
            const selected = document.querySelector('input[name="promotion"]:checked');

            if (selected) {
                selectedPromotion = {
                    id: selected.value,
                    type: selected.dataset.type,
                    discount: selected.dataset.discount
                };

                console.log('Đã chọn khuyến mãi:', selectedPromotion);

                // Thêm class selected cho item
                document.querySelectorAll('.promotion-item').forEach(item => {
                    item.classList.remove('selected');
                });
                selected.closest('.promotion-item').classList.add('selected');
            } else {
                selectedPromotion = null;
                console.log('Đã bỏ chọn khuyến mãi');
            }

            // Cập nhật hiển thị giá
            updateAllPriceDisplay();
        }

        // Bỏ chọn khuyến mãi
        function clearPromotion() {
            document.querySelectorAll('input[name="promotion"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelectorAll('.promotion-item').forEach(item => {
                item.classList.remove('selected');
            });
            selectedPromotion = null;
            updateAllPriceDisplay();
        }

        // Hiệu ứng khi giá thay đổi
        function animatePriceChange() {
            const finalTotalEl = document.getElementById('finalTotal');
            if (finalTotalEl) {
                finalTotalEl.classList.add('price-update');
                setTimeout(() => {
                    finalTotalEl.classList.remove('price-update');
                }, 1000);
            }
        }

        // Chọn phương thức thanh toán
        function selectPaymentMethod(method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            const target = document.querySelector(`#${method}`);
            if (target) {
                target.closest('.payment-method').classList.add('selected');
                target.checked = true;
            }
        }

        // Validation chi tiết hơn
        function validateForm() {
            const requiredFields = [{
                    name: 'customerName',
                    label: 'Họ tên liên hệ'
                },
                {
                    name: 'customerPhone',
                    label: 'Số điện thoại'
                },
                {
                    name: 'customerEmail',
                    label: 'Email'
                },
                {
                    name: 'guestName',
                    label: 'Họ tên khách hàng'
                },
                {
                    name: 'customerIdNumber',
                    label: 'Số CMND/CCCD'
                }
            ];

            for (const field of requiredFields) {
                const input = document.querySelector(`[name="${field.name}"]`);
                if (!input || !input.value.trim()) {
                    alert(`Vui lòng nhập ${field.label}`);
                    if (input) input.focus();
                    return false;
                }
            }

            // Kiểm tra email
            const emailInput = document.querySelector('input[name="customerEmail"]');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailInput && !emailPattern.test(emailInput.value)) {
                alert('Email không hợp lệ');
                emailInput.focus();
                return false;
            }

            // Kiểm tra CMND/CCCD (9 hoặc 12 số)
            const idNumberInput = document.querySelector('input[name="customerIdNumber"]');
            if (idNumberInput && !/^\d{9}$|^\d{12}$/.test(idNumberInput.value.replace(/\s/g, ''))) {
                alert('Số CMND/CCCD phải có 9 hoặc 12 số');
                idNumberInput.focus();
                return false;
            }

            // Kiểm tra điều khoản
            if (!document.getElementById('agreeTerms').checked) {
                alert('Vui lòng đồng ý với điều khoản và điều kiện');
                return false;
            }

            // Kiểm tra phương thức thanh toán
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked');
            if (!paymentMethod) {
                alert('Vui lòng chọn phương thức thanh toán');
                return false;
            }

            return true;
        }

        // Xử lý thanh toán với validation đầy đủ
        async function processPayment() {
            if (!validateForm()) return;

            const submitBtn = document.querySelector('button[onclick="processPayment()"]');
            if (!submitBtn) return;

            const originalText = submitBtn.innerHTML;
            const originalDisabled = submitBtn.disabled;

            // Hiển thị loading
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            submitBtn.disabled = true;

            try {
                const discountAmount = calculateDiscount();
                const finalTotal = calculateFinalTotal();

                // Chuẩn bị dữ liệu
                const formData = new FormData();

                // Thông tin đặt phòng
                formData.append('roomId', '<?php echo $roomId; ?>');
                formData.append('checkin', '<?php echo $checkin; ?>');
                formData.append('checkout', '<?php echo $checkout; ?>');
                formData.append('adults', '<?php echo $adults; ?>');
                formData.append('nights', '<?php echo $nights; ?>');
                formData.append('services', '<?php echo $services; ?>');

                // Thông tin khách hàng
                formData.append('customerName', document.querySelector('input[name="customerName"]').value);
                formData.append('customerPhone', document.querySelector('input[name="customerPhone"]').value);
                formData.append('customerEmail', document.querySelector('input[name="customerEmail"]').value);
                formData.append('customerIdNumber', document.querySelector('input[name="customerIdNumber"]').value);
                formData.append('guestName', document.querySelector('input[name="guestName"]').value);
                formData.append('address', document.querySelector('input[name="address"]').value || '');
                formData.append('specialRequests', document.querySelector('textarea[name="specialRequests"]').value || '');
                formData.append('nonSmoking', document.querySelector('input[name="nonSmoking"]').checked ? 1 : 0);

                // Thông tin thanh toán
                formData.append('paymentMethod', document.querySelector('input[name="paymentMethod"]:checked').value);
                formData.append('totalAmount', originalTotal);
                formData.append('discountAmount', discountAmount);
                formData.append('finalAmount', finalTotal);
                formData.append('promotionId', selectedPromotion ? selectedPromotion.id : '');

                // Gửi request
                const response = await fetch('/ABC-Resort/client/controller/payment.controller.php?action=processPayment', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Khôi phục button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = originalDisabled;

                if (data.success) {
                    alert(data.message);
                    window.location.href = '/ABC-Resort/client/view/payment/payment-success.php?bookingCode=' + data.bookingCode;
                } else {
                    throw new Error(data.message || 'Có lỗi xảy ra khi đặt phòng');
                }
            } catch (error) {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = originalDisabled;
                alert(error.message || 'Có lỗi xảy ra khi kết nối đến server');
            }
        }

        // Khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Trang thanh toán đã load - Tổng tiền:', originalTotal);

            // Khởi tạo phương thức thanh toán mặc định
            selectPaymentMethod('creditCard');

            // Setup event listeners cho khuyến mãi
            document.querySelectorAll('.promotion-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', handlePromotionSelect);
            });

            // Setup nút bỏ chọn khuyến mãi
            const clearPromotionBtn = document.getElementById('btnClearPromotion');
            if (clearPromotionBtn) {
                clearPromotionBtn.addEventListener('click', clearPromotion);
            }

            // Tự động điền thông tin
            const bookForMyselfCheckbox = document.getElementById('bookForMyself');
            if (bookForMyselfCheckbox) {
                bookForMyselfCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        const customerName = document.querySelector('input[name="customerName"]').value;
                        const address = document.querySelector('input[name="address"]').value;

                        if (customerName) {
                            document.querySelector('input[name="guestName"]').value = customerName;
                        }
                        if (address) {
                            document.querySelector('input[name="address"]').value = address;
                        }
                    } else {
                        document.querySelector('input[name="guestName"]').value = '';
                    }
                });
            }

            // Tự động điền nếu đã đăng nhập
            <?php if (!empty($customerInfo['HoTen'])): ?>
                if (bookForMyselfCheckbox) {
                    bookForMyselfCheckbox.checked = true;
                    const customerName = document.querySelector('input[name="customerName"]').value;
                    if (customerName) {
                        document.querySelector('input[name="guestName"]').value = customerName;
                    }
                }
            <?php endif; ?>

            // Cập nhật hiển thị giá ban đầu
            updateAllPriceDisplay();
        });

        // Thêm CSS động cho hiệu ứng
        const style = document.createElement('style');
        style.textContent = `
        @keyframes priceUpdate {
            0% { 
                transform: scale(1);
                color: #dc3545;
            }
            50% { 
                transform: scale(1.1);
                color: #198754;
            }
            100% { 
                transform: scale(1);
                color: #dc3545;
            }
        }
        
        .price-update {
            animation: priceUpdate 0.5s ease;
        }
        
        .promotion-item.selected {
            border-color: #198754 !important;
            background-color: #f0fff4 !important;
            position: relative;
        }
        
        .promotion-item.selected::after {
            content: "✓";
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #198754;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .new-price {
            color: #dc3545;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        #discountName {
            font-weight: 600;
            font-size: 0.9em;
            margin-left: 5px;
        }
    `;
        document.head.appendChild(style);
    </script>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>