<?php
session_start();
include __DIR__ . '/../layouts/header.php';
?>

<style>
    /* === SUPPORT PAGE STYLES === */

    /* Hero Banner */
    .support-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
            url('../../assets/images/banner/support-banner.jpg');
        background-size: cover;
        background-position: center;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-bottom: 60px;
        border-radius: 10px;
        position: relative;
    }

    .support-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        animation: fadeInUp 1s ease;
    }

    .support-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(0, 104, 255, 0.3), rgba(46, 204, 113, 0.3));
        border-radius: 10px;
    }

    /* FAQ Section */
    .faq-section {
        padding: 80px 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .faq-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border: none;
        transition: all 0.3s ease;
        border-left: 5px solid #0068FF;
    }

    .faq-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .faq-question {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .faq-question i {
        color: #0068FF;
        transition: transform 0.3s ease;
    }

    .faq-card.active .faq-question i {
        transform: rotate(180deg);
    }

    .faq-answer {
        color: #7f8c8d;
        font-size: 1rem;
        line-height: 1.6;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .faq-card.active .faq-answer {
        max-height: 500px;
        margin-top: 15px;
    }

    /* Support Categories */
    .categories-section {
        padding: 80px 0;
        background: white;
    }

    .category-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        margin-bottom: 30px;
    }

    .category-card:hover {
        background: white;
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .category-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #0068FF, #2ecc71);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .category-icon i {
        font-size: 35px;
        color: white;
    }

    .category-card h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .category-card p {
        color: #7f8c8d;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .category-btn {
        display: inline-block;
        background: #0068FF;
        color: white;
        padding: 10px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid #0068FF;
    }

    .category-btn:hover {
        background: white;
        color: #0068FF;
        transform: translateY(-2px);
    }

    /* Contact Form */
    .contact-form-section {
        padding: 80px 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .form-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 30px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-control:focus {
        outline: none;
        border-color: #0068FF;
        background: white;
        box-shadow: 0 0 0 3px rgba(0, 104, 255, 0.1);
    }

    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }

    .submit-btn {
        background: linear-gradient(135deg, #0068FF, #2ecc71);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 20px;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 104, 255, 0.3);
    }

    .submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Quick Help Section */
    .quick-help-section {
        padding: 80px 0;
        background: white;
    }

    .help-item {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .help-item:hover {
        background: white;
        transform: translateX(10px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .help-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #0068FF, #2ecc71);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .help-icon i {
        font-size: 25px;
        color: white;
    }

    .help-content h4 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .help-content p {
        color: #7f8c8d;
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    /* Section Title */
    .section-title {
        text-align: center;
        margin-bottom: 60px;
    }

    .section-title h2 {
        font-size: 2.5rem;
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
        padding-bottom: 15px;
    }

    .section-title h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        background: linear-gradient(90deg, #0068FF, #2ecc71);
        border-radius: 2px;
    }

    .section-title p {
        color: #7f8c8d;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Floating Buttons */
    .floating-btn-container {
        position: fixed;
        bottom: 100px;
        right: 30px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .floating-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
        text-decoration: none;
        animation: gentleFloat 3s ease-in-out infinite;
    }

    .floating-btn:hover {
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        animation: none;
    }

    @keyframes gentleFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    .floating-btn i {
        font-size: 26px;
        color: white;
        transition: transform 0.3s ease;
    }

    .floating-btn:hover i {
        transform: scale(1.2);
    }

    .floating-phone {
        background: linear-gradient(135deg, #FF416C, #FF4B2B);
        animation: phoneRing 2s ease-in-out infinite;
    }

    @keyframes phoneRing {

        0%,
        100% {
            transform: rotate(0deg);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: rotate(-5deg);
        }

        20%,
        40%,
        60%,
        80% {
            transform: rotate(5deg);
        }
    }

    .floating-phone:hover {
        animation: none;
    }

    .floating-messenger {
        background: linear-gradient(135deg, #0099FF, #0066FF);
    }

    /* Tooltip */
    .floating-tooltip {
        position: absolute;
        right: 70px;
        background: #333;
        color: white;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 14px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .floating-btn:hover .floating-tooltip {
        opacity: 1;
        visibility: visible;
        right: 75px;
    }

    /* Badges */
    .hotline-badge,
    .message-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 10px;
        padding: 3px 6px;
        border-radius: 10px;
        font-weight: bold;
        animation: pulse 1s infinite;
    }

    .hotline-badge {
        background: #e74c3c;
        color: white;
    }

    .message-badge {
        background: #FFD700;
        color: #333;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .support-hero h1 {
            font-size: 2.2rem;
        }

        .section-title h2 {
            font-size: 2rem;
        }

        .form-card {
            padding: 25px;
        }

        .help-item {
            padding: 20px;
        }

        .floating-btn-container {
            bottom: 80px;
            right: 20px;
        }

        .floating-btn {
            width: 55px;
            height: 55px;
        }

        .floating-btn i {
            font-size: 22px;
        }

        .floating-tooltip {
            display: none;
        }
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .faq-card,
    .category-card,
    .help-item {
        animation: fadeInUp 0.6s ease;
        animation-fill-mode: both;
    }

    .faq-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .faq-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .faq-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .faq-card:nth-child(4) {
        animation-delay: 0.4s;
    }

    .faq-card:nth-child(5) {
        animation-delay: 0.5s;
    }

    .category-card:nth-child(1) {
        animation-delay: 0.2s;
    }

    .category-card:nth-child(2) {
        animation-delay: 0.3s;
    }

    .category-card:nth-child(3) {
        animation-delay: 0.4s;
    }

    .help-item:nth-child(1) {
        animation-delay: 0.3s;
    }

    .help-item:nth-child(2) {
        animation-delay: 0.4s;
    }

    .help-item:nth-child(3) {
        animation-delay: 0.5s;
    }

    .help-item:nth-child(4) {
        animation-delay: 0.6s;
    }
</style>

<div class="container-fluid px-0">
    <!-- ========== HERO BANNER ========== -->
    <div class="support-hero">
        <div class="text-center">
            <h1>Trung Tâm Hỗ Trợ</h1>
            <p class="lead mt-3" style="font-size: 1.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7
            </p>
        </div>
    </div>

    <!-- ========== QUICK HELP ========== -->
    <div class="quick-help-section">
        <div class="container">
            <div class="section-title">
                <h2>Hỗ Trợ Nhanh</h2>
                <p>Giải đáp các vấn đề thường gặp một cách nhanh chóng</p>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="help-item">
                        <div class="help-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="help-content">
                            <h4>Đặt Phòng & Đổi Lịch</h4>
                            <p>Hướng dẫn đặt phòng, thay đổi ngày nhận phòng, hủy đặt phòng và chính sách hoàn tiền.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="help-item">
                        <div class="help-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="help-content">
                            <h4>Thanh Toán & Hóa Đơn</h4>
                            <p>Giải đáp về phương thức thanh toán, xuất hóa đơn VAT, mã khuyến mãi và ưu đãi.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="help-item">
                        <div class="help-icon">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <div class="help-content">
                            <h4>Dịch Vụ Resort</h4>
                            <p>Thông tin về dịch vụ ăn uống, spa, hồ bơi, gym và các tiện ích khác tại resort.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="help-item">
                        <div class="help-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="help-content">
                            <h4>Tài Khoản & Bảo Mật</h4>
                            <p>Hỗ trợ đăng ký, đăng nhập, quên mật khẩu và bảo mật tài khoản.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== FAQ SECTION ========== -->
    <div class="faq-section">
        <div class="container">
            <div class="section-title">
                <h2>Câu Hỏi Thường Gặp (FAQ)</h2>
                <p>Tìm câu trả lời cho những thắc mắc phổ biến nhất</p>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="faq-card" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>Làm thế nào để đặt phòng online?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>1. Chọn ngày đến và ngày đi trên trang chủ<br>
                                2. Chọn số người và số phòng<br>
                                3. Xem danh sách phòng có sẵn và chọn phòng ưa thích<br>
                                4. Nhập thông tin cá nhân và thanh toán<br>
                                5. Nhận email xác nhận đặt phòng</p>
                        </div>
                    </div>

                    <div class="faq-card" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>Thời gian check-in/check-out là khi nào?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>• Check-in: Từ 14:00<br>
                                • Check-out: Trước 12:00<br>
                                • Check-out muộn (nếu có phòng trống): Phụ thu 30% giá phòng/giờ</p>
                        </div>
                    </div>

                    <div class="faq-card" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>Chính sách hủy phòng như thế nào?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>• Hủy trước 7 ngày: Hoàn 100% tiền cọc<br>
                                • Hủy trước 3-7 ngày: Hoàn 50% tiền cọc<br>
                                • Hủy dưới 3 ngày: Không hoàn tiền cọc<br>
                                • Trường hợp bất khả kháng: Liên hệ bộ phận hỗ trợ</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="faq-card" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>Resort có những phương thức thanh toán nào?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>• Thẻ tín dụng/ghi nợ (Visa, MasterCard, JCB)<br>
                                • Chuyển khoản ngân hàng<br>
                                • Ví điện tử (Momo, ZaloPay, VNPay)<br>
                                • Tiền mặt tại resort<br>
                                • Thanh toán qua PayPal (cho khách quốc tế)</p>
                        </div>
                    </div>

                    <div class="faq-card" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>Có thể mang thú cưng đến resort không?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Resort cho phép mang thú cưng với các điều kiện:<br>
                                • Chỉ áp dụng cho phòng villa<br>
                                • Phụ thu 500.000 VND/đêm<br>
                                • Thú cưng phải có giấy tờ tiêm phòng đầy đủ<br>
                                • Không được để thú cưng ở phòng một mình</p>
                        </div>
                    </div>

                    <div class="faq-card" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>Resort có dịch vụ đưa đón sân bay không?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Có, resort cung cấp dịch vụ đưa đón sân bay:<br>
                                • Sân bay Vũng Tàu: Miễn phí<br>
                                • Sân bay Tân Sơn Nhất (HCM): 800.000 VND/lượt<br>
                                • Sân bay Long Thành (tương lai): 1.200.000 VND/lượt<br>
                                • Xe 4-7 chỗ, đặt trước 24 giờ</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== SUPPORT CATEGORIES ========== -->
    <div class="categories-section">
        <div class="container">
            <div class="section-title">
                <h2>Danh Mục Hỗ Trợ</h2>
                <p>Chọn danh mục bạn cần được hỗ trợ</p>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <h3>Đặt Phòng & Lưu Trú</h3>
                        <p>Hỗ trợ đặt phòng, check-in/out, yêu cầu đặc biệt, nâng cấp phòng</p>
                        <a href="#booking-help" class="category-btn">Xem chi tiết</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3>Dịch Vụ Ăn Uống</h3>
                        <p>Đặt bàn, thực đơn đặc biệt, dị ứng thực phẩm, tiệc & sự kiện</p>
                        <a href="#dining-help" class="category-btn">Xem chi tiết</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-spa"></i>
                        </div>
                        <h3>Dịch Vụ Spa & Thư Giãn</h3>
                        <p>Đặt lịch spa, gói dịch vụ, massage, trị liệu và chăm sóc sức khỏe</p>
                        <a href="#spa-help" class="category-btn">Xem chi tiết</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-swimming-pool"></i>
                        </div>
                        <h3>Tiện Ích Resort</h3>
                        <p>Hồ bơi, phòng gym, khu vui chơi trẻ em, dịch vụ giặt là</p>
                        <a href="#facilities-help" class="category-btn">Xem chi tiết</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h3>Công Nghệ & Kết Nối</h3>
                        <p>Wi-Fi, TV, hệ thống âm thanh, sạc thiết bị, hỗ trợ kỹ thuật</p>
                        <a href="#tech-help" class="category-btn">Xem chi tiết</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Hỗ Trợ Khẩn Cấp</h3>
                        <p>Sức khỏe, an ninh, mất đồ, sự cố phòng, hỗ trợ 24/7</p>
                        <a href="#emergency-help" class="category-btn">Xem chi tiết</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== CONTACT FORM ========== -->
    <div class="contact-form-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-card">
                        <h2 class="form-title">Gửi Yêu Cầu Hỗ Trợ</h2>
                        <p class="text-center text-muted mb-4">Chúng tôi sẽ phản hồi trong vòng 24 giờ</p>

                        <form id="supportForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Họ và tên *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="tel" class="form-control" name="phone">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Danh mục hỗ trợ *</label>
                                        <select class="form-control" name="category" required>
                                            <option value="">Chọn danh mục</option>
                                            <option value="booking">Đặt phòng & Lưu trú</option>
                                            <option value="payment">Thanh toán & Hóa đơn</option>
                                            <option value="service">Dịch vụ resort</option>
                                            <option value="account">Tài khoản & Bảo mật</option>
                                            <option value="other">Vấn đề khác</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Tiêu đề *</label>
                                        <input type="text" class="form-control" name="subject" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Nội dung chi tiết *</label>
                                        <textarea class="form-control" name="message" required></textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Tệp đính kèm (nếu có)</label>
                                        <input type="file" class="form-control" name="attachment">
                                        <small class="text-muted">Hỗ trợ: JPG, PNG, PDF (tối đa 5MB)</small>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="submit-btn" id="submitBtn">
                                        <span id="btnText">Gửi Yêu Cầu</span>
                                        <div id="loadingSpinner" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i> Đang xử lý...
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== CONTACT INFO ========== -->
    <div class="contact-info-section">
        <div class="container">
            <div class="section-title">
                <h2>Liên Hệ Trực Tiếp</h2>
                <p>Nhiều cách để liên hệ với đội ngũ hỗ trợ của chúng tôi</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4>Điện Thoại</h4>
                        <p>Hỗ trợ 24/7<br>
                            <strong>1900 1234</strong><br>
                            Quốc tế: +84 243 999 8877
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email</h4>
                        <p>Hỗ trợ chung:<br>
                            <strong>support@toasangresort.com</strong><br>
                            Đặt phòng: booking@toasangresort.com
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Giờ Làm Việc</h4>
                        <p>Trung tâm hỗ trợ:<br>
                            Thứ 2 - CN: 24/7<br>
                            Văn phòng: 8:00 - 22:00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== FLOATING BUTTONS ========== -->
<div class="floating-btn-container">
   

    <a href="https://m.me/ToaSangResort" target="_blank" class="floating-btn floating-messenger">
        <i class="fa-brands fa-facebook-messenger"></i>
        <span class="floating-tooltip">Chat Messenger</span>
        <span class="message-badge">NEW</span>
    </a>
     <a href="tel:19001234" class="floating-btn floating-phone">
        <i class="fa-solid fa-phone-volume"></i>
        <span class="floating-tooltip">Gọi hỗ trợ: 1900.1234</span>
        <span class="hotline-badge">HOT/span>
    </a>
</div>

<script>
    // Toggle FAQ
    function toggleFAQ(card) {
        card.classList.toggle('active');
    }

    // Form submission
    document.getElementById('supportForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        // Show loading
        btnText.style.display = 'none';
        loadingSpinner.style.display = 'block';
        submitBtn.disabled = true;

        // Simulate API call
        setTimeout(() => {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: 'Yêu cầu hỗ trợ đã được gửi. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0068FF'
            });

            // Reset form
            this.reset();

            // Reset button
            btnText.style.display = 'inline';
            loadingSpinner.style.display = 'none';
            submitBtn.disabled = false;
        }, 1500);
    });

    // Smooth scroll for category links
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');

            Swal.fire({
                icon: 'info',
                title: 'Thông tin chi tiết',
                html: `Tính năng đang được phát triển. Vui lòng liên hệ hotline <strong>1900.1234</strong> để được hỗ trợ chi tiết về ${this.parentElement.querySelector('h3').textContent}.`,
                confirmButtonText: 'Đã hiểu',
                confirmButtonColor: '#0068FF'
            });
        });
    });

    // Auto-expand FAQ on page load (first one)
    document.addEventListener('DOMContentLoaded', function() {
        const firstFAQ = document.querySelector('.faq-card');
        if (firstFAQ) {
            firstFAQ.classList.add('active');
        }

        // Add animation on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        // Observe elements
        document.querySelectorAll('.faq-card, .category-card, .help-item').forEach(el => {
            el.style.opacity = 0;
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    });

    // Live chat simulation
    function openLiveChat() {
        Swal.fire({
            title: 'Chat Hỗ Trợ Trực Tuyến',
            html: `
                <div style="text-align: left; margin: 20px 0;">
                    <p><strong>Chào bạn! Tôi có thể giúp gì cho bạn?</strong></p>
                    <div style="background: #f0f0f0; padding: 10px; border-radius: 5px; margin: 10px 0;">
                        <p>Xin chào! Tôi cần hỏi về chính sách hủy phòng.</p>
                        <small style="color: #666;">Vừa xong</small>
                    </div>
                </div>
                <input type="text" id="chatMessage" class="form-control" placeholder="Nhập tin nhắn của bạn..." style="margin: 10px 0;">
            `,
            showCancelButton: true,
            confirmButtonText: 'Gửi tin nhắn',
            cancelButtonText: 'Đóng',
            confirmButtonColor: '#0068FF',
            preConfirm: () => {
                const message = document.getElementById('chatMessage').value;
                if (!message) {
                    Swal.showValidationMessage('Vui lòng nhập tin nhắn');
                    return false;
                }
                return message;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Tin nhắn đã gửi!',
                    text: 'Nhân viên hỗ trợ sẽ phản hồi trong vài phút.',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>