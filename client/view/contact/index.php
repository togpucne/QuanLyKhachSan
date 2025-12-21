<?php
session_start();
include __DIR__ . '/../layouts/header.php';

// Dữ liệu 5 thành viên nhóm với đường dẫn avatar ĐÚNG
$teamMembers = [
    [
        'id' => '001',
        'name' => 'Nguyễn Trọng Phúc',
        'student_id' => '22655111',
        'role' => 'Trưởng Nhóm',
        'role_desc' => 'Fullstack Developer & Project Manager',
        'email' => 'phuc.nt@toasangresort.com',
        'phone' => '0912 345 678',
        'avatar' => 'avatar1.jpg',
        'facebook' => 'https://facebook.com/trongphuc.nguyen',
        'github' => 'https://github.com/trongphuc22655111',
        'linkedin' => 'https://linkedin.com/in/trongphuc-nguyen'
    ],
    [
        'id' => '002',
        'name' => 'Bùi Thu Thảo',
        'student_id' => '22661151',
        'role' => 'Thư Ký',
        'role_desc' => 'System Analyst & Documentation',
        'email' => 'thao.bt@toasangresort.com',
        'phone' => '0913 456 789',
        'avatar' => 'avatar2.jpg',
        'facebook' => 'https://facebook.com/thuthao.bui',
        'github' => 'https://github.com/thuthao22661151',
        'linkedin' => 'https://linkedin.com/in/thuthao-bui'
    ],
    [
        'id' => '003',
        'name' => 'Lê Trọng Phúc',
        'student_id' => '22679331',
        'role' => 'Timekeeper',
        'role_desc' => 'Database Administrator & Tester',
        'email' => 'phuc.lt@toasangresort.com',
        'phone' => '0914 567 890',
        'avatar' => 'avatar3.jpg',
        'facebook' => 'https://facebook.com/trongphuc.le',
        'github' => 'https://github.com/trongphuc22679331',
        'linkedin' => 'https://linkedin.com/in/trongphuc-le'
    ],
    [
        'id' => '004',
        'name' => 'Phạm Công Thành',
        'student_id' => '22725811',
        'role' => 'Người lưu hồ sơ',
        'role_desc' => 'Backend Developer & Data Manager',
        'email' => 'thanh.pc@toasangresort.com',
        'phone' => '0915 678 901',
        'avatar' => 'avatar4.jpg',
        'facebook' => 'https://facebook.com/congthanh.pham',
        'github' => 'https://github.com/congthanh22725811',
        'linkedin' => 'https://linkedin.com/in/congthanh-pham'
    ],
    [
        'id' => '005',
        'name' => 'Trương Hoàng Lâm',
        'student_id' => '20048901',
        'role' => 'Thành viên',
        'role_desc' => 'Frontend Developer & UI/UX Designer',
        'email' => 'lam.th@toasangresort.com',
        'phone' => '0916 789 012',
        'avatar' => 'avatar5.jpg',
        'facebook' => 'https://facebook.com/hoanglam.truong',
        'github' => 'https://github.com/hoanglam20048901',
        'linkedin' => 'https://linkedin.com/in/hoanglam-truong'
    ]
];
?>

<!-- DEBUG: Kiểm tra chính xác -->
<?php


// Test đường dẫn cho avatar1
$testPaths = [
    'relative1' => '../../assets/images/team/avatar1.jpg',
    'relative2' => '../assets/images/team/avatar1.jpg',
    'relative3' => 'assets/images/team/avatar1.jpg',
    'absolute' => $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/assets/images/team/avatar1.jpg',
    'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/ABC-Resort/client/assets/images/team/avatar1.jpg'
];

foreach ($testPaths as $key => $path) {
    if (strpos($path, 'http') === 0) {
        // Kiểm tra URL
        $headers = @get_headers($path);
        $exists = ($headers && strpos($headers[0], '200')) ? 'YES' : 'NO';
        echo "<!-- $key: $path -> $exists -->\n";
    } else if (strpos($path, $_SERVER['DOCUMENT_ROOT']) === 0) {
        // Kiểm tra file local
        $exists = file_exists($path) ? 'YES' : 'NO';
        echo "<!-- $key: " . basename($path) . " -> $exists -->\n";
    } else {
        // Kiểm tra relative path
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/view/contact/' . $path;
        $exists = file_exists($fullPath) ? 'YES' : 'NO';
        echo "<!-- $key: $path -> $exists -->\n";
    }
}

// Kiểm tra thư mục team
$teamDir = $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/assets/images/team/';
echo "<!-- Team directory: $teamDir -->\n";
if (is_dir($teamDir)) {
    $files = scandir($teamDir);
    echo "<!-- Files found in team directory: -->\n";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<!-- - $file -->\n";
        }
    }
} else {
    echo "<!-- ERROR: Team directory not found! -->\n";
}
?>

<!-- DEBUG: Kiểm tra file tồn tại -->
<?php
echo "<!-- DEBUG INFO -->\n";
echo "<!-- Current directory: " . __DIR__ . " -->\n";
$teamDir = $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/assets/images/team/';
echo "<!-- Team directory: $teamDir -->\n";

if (is_dir($teamDir)) {
    echo "<!-- Files in team directory: -->\n";
    $files = scandir($teamDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $teamDir . $file;
            $exists = file_exists($filePath) ? 'YES' : 'NO';
            echo "<!-- - $file: $exists -->\n";
        }
    }
}
?>

<style>
    /* === CONTACT PAGE STYLES === */
    
    /* Hero Banner */
    .contact-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                    url('../assets/images/banner/contact-banner.jpg');
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

    .contact-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        animation: fadeInUp 1s ease;
    }

    .contact-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(0, 104, 255, 0.3), rgba(46, 204, 113, 0.3));
        border-radius: 10px;
    }

    /* Team Section */
    .team-section {
        padding: 80px 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    /* Member Card */
    .member-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        height: 100%;
        text-align: center;
        border: none;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .member-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #0068FF, #2ecc71);
    }

    .member-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    /* Avatar Container */
    .member-avatar {
        width: 180px;
        height: 180px;
        margin: 0 auto 25px;
        position: relative;
    }

    .member-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #f0f0f0, #e0e0e0); /* Fallback color */
    }

    .member-card:hover .member-avatar img {
        transform: scale(1.05);
        border-color: #0068FF;
    }

    /* Member Badge */
    .member-number {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: #0068FF;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        box-shadow: 0 4px 10px rgba(0, 104, 255, 0.3);
    }

    /* Member Info */
    .member-name {
        font-size: 1.6rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .member-id {
        background: linear-gradient(90deg, #0068FF, #2ecc71);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
        margin-bottom: 15px;
    }

    .member-role {
        color: #0068FF;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .member-desc {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 20px;
        line-height: 1.5;
        min-height: 40px;
    }

    /* Contact Info */
    .contact-details {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .contact-item {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        margin-bottom: 10px;
        color: #555;
    }

    .contact-item i {
        color: #0068FF;
        width: 20px;
        font-size: 16px;
    }

    /* Social Links */
    .social-icons {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 25px;
    }

    .social-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .social-icon:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .social-fb { background: linear-gradient(135deg, #1877F2, #0D5FAD); }
    .social-email { background: linear-gradient(135deg, #EA4335, #C23321); }
    .social-github { background: linear-gradient(135deg, #333, #000); }
    .social-linkedin { background: linear-gradient(135deg, #0077B5, #005582); }

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

    /* Contact Info Cards */
    .contact-info-section {
        padding: 80px 0;
        background: white;
    }

    .info-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 30px;
        height: 100%;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .info-card:hover {
        background: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transform: translateY(-5px);
    }

    .info-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #0068FF, #2ecc71);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .info-icon i {
        font-size: 30px;
        color: white;
    }

    .info-card h4 {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .info-card p {
        color: #7f8c8d;
        font-size: 1rem;
        line-height: 1.6;
        margin: 0;
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
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
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
        0%, 100% { transform: rotate(0deg); }
        10%, 30%, 50%, 70%, 90% { transform: rotate(-5deg); }
        20%, 40%, 60%, 80% { transform: rotate(5deg); }
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
    .hotline-badge, .message-badge {
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
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .contact-hero h1 {
            font-size: 2.2rem;
        }
        
        .member-card {
            padding: 20px;
        }
        
        .member-avatar {
            width: 150px;
            height: 150px;
        }
        
        .section-title h2 {
            font-size: 2rem;
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

    .member-card {
        animation: fadeInUp 0.6s ease;
        animation-fill-mode: both;
    }

    .member-card:nth-child(1) { animation-delay: 0.1s; }
    .member-card:nth-child(2) { animation-delay: 0.2s; }
    .member-card:nth-child(3) { animation-delay: 0.3s; }
    .member-card:nth-child(4) { animation-delay: 0.4s; }
    .member-card:nth-child(5) { animation-delay: 0.5s; }
</style>
<div class="container-fluid px-0">
    <!-- ========== HERO BANNER ========== -->
    <div class="contact-hero">
        <div class="text-center">
            <h1>Đội Ngũ Phát Triển</h1>
            <p class="lead mt-3" style="font-size: 1.2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                Nhóm 5 thành viên - Dự án Tỏa Sáng Resort
            </p>
        </div>
    </div>

    <!-- ========== TEAM MEMBERS ========== -->
    <div class="team-section">
        <div class="container">
            <div class="section-title">
                <h2>Thành Viên Nhóm</h2>
                <p>Đội ngũ phát triển website Tỏa Sáng Resort với 5 thành viên tài năng và nhiệt huyết</p>
            </div>
            
            <div class="row">
                <?php foreach ($teamMembers as $index => $member): 
                    // THỬ CÁC ĐƯỜNG DẪN KHÁC NHAU
                    $avatarPaths = [
                        // Đường dẫn từ vị trí hiện tại (contact/index.php) đến team/
                        '../../assets/images/team/' . $member['avatar'],
                        
                        // Hoặc dùng URL tuyệt đối
                        'http://' . $_SERVER['HTTP_HOST'] . '/ABC-Resort/client/assets/images/team/' . $member['avatar'],
                        
                        // Hoặc từ root
                        '/ABC-Resort/client/assets/images/team/' . $member['avatar']
                    ];
                    
                    $avatarUrl = $avatarPaths[0]; // Dùng đường dẫn đầu tiên
                    
                    // DEBUG từng thành viên
                    echo "<!-- DEBUG Member {$member['name']}: Trying {$avatarPaths[0]} -->\n";
                    $testPath = $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/view/contact/' . $avatarPaths[0];
                    echo "<!-- Full path: $testPath -->\n";
                    echo "<!-- File exists: " . (file_exists($testPath) ? 'YES' : 'NO') . " -->\n";
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="member-card">
                        <!-- Avatar - SỬA ĐƯỜNG DẪN -->
                        <div class="member-avatar">
                            <img src="<?php echo $avatarUrl; ?>" 
                                 alt="<?php echo htmlspecialchars($member['name']); ?>"
                                 onerror="
                                    console.log('Avatar failed for: <?php echo $member['name']; ?>');
                                    this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($member['name']); ?>&background=0068FF&color=fff&size=180';
                                 ">
                            <div class="member-number"><?php echo $index + 1; ?></div>
                        </div>
                        
                        <!-- Thông tin -->
                        <h3 class="member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                        <div class="member-id">MSSV: <?php echo $member['student_id']; ?></div>
                        
                        <div class="member-role"><?php echo htmlspecialchars($member['role']); ?></div>
                        <div class="member-desc"><?php echo htmlspecialchars($member['role_desc']); ?></div>
                        
                        <!-- Liên hệ -->
                        <div class="contact-details">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo $member['email']; ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo $member['phone']; ?></span>
                            </div>
                        </div>
                        
                        <!-- Social Links -->
                        <div class="social-icons">
                            <a href="<?php echo $member['facebook']; ?>" target="_blank" class="social-icon social-fb" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="mailto:<?php echo $member['email']; ?>" class="social-icon social-email" title="Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="<?php echo $member['github']; ?>" target="_blank" class="social-icon social-github" title="GitHub">
                                <i class="fab fa-github"></i>
                            </a>
                            <a href="<?php echo $member['linkedin']; ?>" target="_blank" class="social-icon social-linkedin" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ... (Phần còn lại giữ nguyên) ... -->
</div>




<!-- ========== FLOATING BUTTONS ========== -->
<div class="floating-btn-container">
        <a href="https://m.me/ToaSangResort" target="_blank" class="floating-btn floating-messenger">
        <i class="fa-brands fa-facebook-messenger"></i>
        <span class="floating-tooltip">Chat với chúng tôi</span>
        <span class="message-badge">NEW</span>
    </a>
    <a href="tel:02439998877" class="floating-btn floating-phone">
        <i class="fa-solid fa-phone-volume"></i>
        <span class="floating-tooltip">Gọi ngay: 0243.999.8877</span>
        <span class="hotline-badge">HOT</span>
    </a>
    

</div>

<script>
    // Animation khi scroll
    document.addEventListener('DOMContentLoaded', function() {
        // Hiệu ứng fade in khi scroll
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

        // Áp dụng cho các card
        document.querySelectorAll('.member-card, .info-card').forEach(card => {
            card.style.opacity = 0;
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Kiểm tra avatar load lỗi
        document.querySelectorAll('.member-avatar img').forEach(img => {
            img.addEventListener('error', function() {
                this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(this.alt) + '&background=0068FF&color=fff&size=180';
            });
        });
    });
</script>

<?php
// Include footer
include __DIR__ . '/../layouts/footer.php';
?>