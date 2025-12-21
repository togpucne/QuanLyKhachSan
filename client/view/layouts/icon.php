
<style>
    /* Floating Button */
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
        /* Animation rung lắc nhẹ */
        animation: gentleFloat 3s ease-in-out infinite;
    }

    .floating-btn:hover {
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        animation: none; /* Dừng animation khi hover */
    }

    /* Animation nổi nhẹ */
    @keyframes gentleFloat {
        0%, 100% {
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

    /* Messenger Button - Màu xanh Messenger */
    .floating-messenger {
        background: linear-gradient(135deg, #0099FF, #0066FF);
        /* Hiệu ứng sáng cho Messenger */
        box-shadow: 0 4px 15px rgba(0, 153, 255, 0.4);
    }

    .floating-messenger:hover {
        box-shadow: 0 6px 20px rgba(0, 153, 255, 0.6);
    }

    /* Phone Button - Màu đỏ nóng */
    .floating-phone {
        background: linear-gradient(135deg, #FF416C, #FF4B2B);
        /* Hiệu ứng rung cho điện thoại */
        animation: phoneRing 2s ease-in-out infinite;
    }

    /* Hiệu ứng chuông điện thoại rung */
    @keyframes phoneRing {
        0%, 100% {
            transform: rotate(0deg);
        }
        10%, 30%, 50%, 70%, 90% {
            transform: rotate(-5deg);
        }
        20%, 40%, 60%, 80% {
            transform: rotate(5deg);
        }
    }

    .floating-phone:hover {
        animation: none;
    }

    /* Tooltip hiển thị khi hover */
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
        pointer-events: none;
        font-weight: 500;
    }

    .floating-tooltip::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -5px;
        transform: translateY(-50%);
        border-width: 5px 0 5px 5px;
        border-style: solid;
        border-color: transparent transparent transparent #333;
    }

    .floating-btn:hover .floating-tooltip {
        opacity: 1;
        visibility: visible;
        right: 75px;
    }

    /* Badge thông báo */
    .message-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #FFD700;
        color: #333;
        font-size: 10px;
        padding: 3px 6px;
        border-radius: 10px;
        font-weight: bold;
        animation: badgePulse 2s infinite;
    }

    .hotline-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        font-size: 10px;
        padding: 3px 6px;
        border-radius: 10px;
        font-weight: bold;
        animation: badgePulse 1s infinite;
    }

    @keyframes badgePulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.8;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Sóng lan tỏa cho điện thoại */
    .floating-phone::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        opacity: 0.5;
        animation: rippleWave 1.5s ease-out infinite;
        z-index: -1;
    }

    @keyframes rippleWave {
        0% {
            transform: scale(1);
            opacity: 0.6;
        }
        100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }

    /* Sóng lan tỏa cho Messenger */
    .floating-messenger::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: inherit;
        opacity: 0.3;
        animation: messengerWave 2s ease-out infinite;
        z-index: -1;
    }

    @keyframes messengerWave {
        0% {
            transform: scale(1);
            opacity: 0.4;
        }
        100% {
            transform: scale(1.4);
            opacity: 0;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
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
        
        .message-badge,
        .hotline-badge {
            font-size: 9px;
            padding: 2px 5px;
        }
    }

    /* Cho màn hình rất nhỏ */
    @media (max-width: 480px) {
        .floating-btn-container {
            bottom: 70px;
            right: 15px;
            gap: 12px;
        }

        .floating-btn {
            width: 50px;
            height: 50px;
        }

        .floating-btn i {
            font-size: 20px;
        }
    }
</style>

<!-- Floating Buttons Container -->
<div class="floating-btn-container">
    <!-- Button Messenger (trên cùng) -->
    <a href="https://www.facebook.com/profile.php?id=61575748661377" target="_blank" class="floating-btn floating-messenger">
        <i class="fa-brands fa-facebook-messenger"></i>
        <span class="floating-tooltip">Chat với chúng tôi qua Messenger</span>
        <span class="message-badge">NEW</span>
    </a>
    
    <!-- Button Điện Thoại (dưới) -->
    <a href="tel:02439998877" class="floating-btn floating-phone">
        <i class="fa-solid fa-phone-volume"></i>
        <span class="floating-tooltip">Gọi ngay: 0243.999.8877</span>
        <span class="hotline-badge">HOT</span>
    </a>
</div>

<script>
    // Hiệu ứng thêm khi click vào Messenger
    document.querySelector('.floating-messenger').addEventListener('click', function(e) {
        // Thêm hiệu ứng nhấp nháy
        this.style.animation = 'gentleFloat 0.3s ease-in-out 2';
        setTimeout(() => {
            this.style.animation = 'gentleFloat 3s ease-in-out infinite';
        }, 600);
    });
    
    // Hiệu ứng thêm khi click vào Điện thoại
    document.querySelector('.floating-phone').addEventListener('click', function(e) {
        // Thêm hiệu ứng rung mạnh
        this.style.animation = 'phoneRing 0.2s ease-in-out 3';
        setTimeout(() => {
            this.style.animation = 'phoneRing 2s ease-in-out infinite';
        }, 600);
    });
</script>
<!--  -->