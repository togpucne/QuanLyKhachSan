<?php
include __DIR__ . '/../layouts/header.php';

// Lấy thông tin khách hàng từ session nếu đã đăng nhập
$customerInfo = [];
if (isset($_SESSION['user_id'])) {
    $customerInfo = getCustomerInfo($_SESSION['user_id']);
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
        /* Điều chỉnh khoảng cách từ top */
        z-index: 10;
        /* Đảm bảo nó nằm trên các phần tử khác */
        max-height: calc(100vh - 120px);
        /* Giới hạn chiều cao */
        overflow-y: auto;
        /* Cho phép cuộn nếu nội dung quá dài */
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
                            value="<?php echo isset($customerInfo['HoTen']) ? htmlspecialchars($customerInfo['HoTen']) : ''; ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại *</label>
                            <div class="input-group">
                                <span class="input-group-text">+84</span>
                                <input type="tel" class="form-control" name="customerPhone" required
                                    placeholder="901234567"
                                    value="<?php echo isset($customerInfo['SoDienThoai']) ? htmlspecialchars($customerInfo['SoDienThoai']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="customerEmail" required
                                placeholder="email@example.com"
                                value="<?php echo isset($customerInfo['Email']) ? htmlspecialchars($customerInfo['Email']) : ''; ?>">
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
                            value="<?php echo isset($customerInfo['HoTen']) ? htmlspecialchars($customerInfo['HoTen']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số CMND/CCCD *</label>
                        <input type="text" class="form-control" name="customerIdNumber" required
                            placeholder="Nhập số CMND hoặc CCCD"
                            value="<?php echo isset($customerInfo['CMND']) ? htmlspecialchars($customerInfo['CMND']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" name="address"
                            placeholder="Nhập địa chỉ"
                            value="<?php echo isset($customerInfo['DiaChi']) ? htmlspecialchars($customerInfo['DiaChi']) : ''; ?>">
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
        </div>

        <!-- Right Column - Tóm tắt đơn hàng -->
        <div class="col-lg-4">
            <div class="sticky-summary"> <!-- Đổi class từ sticky-top thành sticky-summary -->
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

                <!-- Chi tiết giá -->
                <div class="price-breakdown">
                    <h6 class="fw-bold mb-3">Chi tiết giá</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <small>Giá phòng (<?php echo $nights; ?> đêm):</small>
                        <small><?php echo number_format($roomPrice); ?> VND</small>
                    </div>

                    <?php if ($servicesPrice > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <small>Dịch vụ bổ sung:</small>
                            <small><?php echo number_format($servicesPrice); ?> VND</small>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-2">
                        <small>Thuế và phí:</small>
                        <small><?php echo number_format($tax); ?> VND</small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Tổng cộng</div>
                            <small class="text-muted"><?php echo $nights; ?> đêm, <?php echo $adults; ?> khách</small>
                        </div>
                        <div class="total-price"><?php echo number_format($totalAmount); ?> VND</div>
                    </div>
                </div>

                <!-- Nút thanh toán -->
                <button class="btn btn-primary w-100 py-3 fw-bold mt-3" onclick="processPayment()">
                    THANH TOÁN NGAY
                </button>

                <div class="text-center mt-2">
                    <small class="text-muted">
                        Bằng cách tiến hành thanh toán, bạn đã đồng ý với
                        <a href="#">Điều khoản và Điều kiện</a> của ABC Resort
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function selectPaymentMethod(method) {
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        document.querySelector(`#${method}`).closest('.payment-method').classList.add('selected');
        document.querySelector(`#${method}`).checked = true;
    }

    function processPayment() {
        const formData = new FormData();
        formData.append('roomId', '<?php echo $roomId; ?>');
        formData.append('checkin', '<?php echo $checkin; ?>');
        formData.append('checkout', '<?php echo $checkout; ?>');
        formData.append('adults', '<?php echo $adults; ?>');
        formData.append('nights', '<?php echo $nights; ?>');
        formData.append('services', '<?php echo $services; ?>');
        formData.append('customerName', document.querySelector('input[name="customerName"]').value);
        formData.append('customerPhone', document.querySelector('input[name="customerPhone"]').value);
        formData.append('customerEmail', document.querySelector('input[name="customerEmail"]').value);
        formData.append('customerIdNumber', document.querySelector('input[name="customerIdNumber"]').value);
        formData.append('specialRequests', document.querySelector('textarea[name="specialRequests"]').value);
        formData.append('paymentMethod', document.querySelector('input[name="paymentMethod"]:checked').value);
        formData.append('totalAmount', '<?php echo $totalAmount; ?>');

        // Gửi request thanh toán
        fetch('/ABC-Resort/client/controller/payment.controller.php?action=processPayment', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/ABC-Resort/client/view/payment/payment-success.php?bookingCode=' + data.bookingCode;
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xử lý thanh toán');
            });
    }

    // Chọn phương thức thanh toán mặc định
    document.addEventListener('DOMContentLoaded', function() {
        selectPaymentMethod('creditCard');
    });
    // Tự động điền thông tin khi checkbox "đặt cho chính mình" được chọn
    document.getElementById('bookForMyself').addEventListener('change', function() {
        if (this.checked) {
            const customerName = document.querySelector('input[name="customerName"]').value;
            const customerIdNumber = document.querySelector('input[name="customerIdNumber"]').value;

            document.querySelector('input[name="guestName"]').value = customerName;
            // Có thể thêm các trường khác nếu cần
        } else {
            document.querySelector('input[name="guestName"]').value = '';
        }
    });

    // Kiểm tra nếu đã đăng nhập thì tự động check "đặt cho chính mình"
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($customerInfo['HoTen']) && !empty($customerInfo['HoTen'])): ?>
            document.getElementById('bookForMyself').checked = true;
        <?php endif; ?>

        selectPaymentMethod('creditCard');
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>