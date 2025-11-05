<?php
$pageTitle = "Tổng quan - ABC Resort";
include_once 'layouts/header.php';

// Content khác nhau theo vai trò
$roleContents = [
    'quanly' => [
        'title' => 'QUẢN LÝ HỆ THỐNG',
        'description' => 'Toàn quyền quản lý và giám sát hệ thống ABC Resort'
    ],
    'ketoan' => [
        'title' => 'QUẢN LÝ TÀI CHÍNH',
        'description' => 'Theo dõi doanh thu, chi phí và báo cáo tài chính'
    ],
    'letan' => [
        'title' => 'QUẢN Lý ĐẶT PHÒNG',
        'description' => 'Xử lý đặt phòng, đăng ký tài khoản, check-in và dịch vụ khách hàng'
    ],
    'buongphong' => [
        'title' => 'QUẢN LÝ BUỒNG PHÒNG', 
        'description' => 'Quản lý tình trạng phòng và dịch vụ buồng phòng'
    ],
    'kinhdoanh' => [
        'title' => 'QUẢN LÝ KINH DOANH',
        'description' => 'Phát triển chiến dịch marketing và quan hệ khách hàng'
    ],
    'thungan' => [
        'title' => 'QUẢN LÝ THU NGÂN',
        'description' => 'Xử lý thanh toán và lập báo cáo thu chi'
    ]
];

$content = $roleContents[$role] ?? $roleContents['quanly'];
?>

<!-- Welcome Message based on Role -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-card p-4">
            <h2 class="mb-2"><?php echo $content['title']; ?></h2>
            <p class="mb-0"><?php echo $content['description']; ?></p>
        </div>
    </div>
</div>

<!-- Statistics Cards (khác nhau theo role) -->
<div class="row mb-4">
    <?php
    // Thống kê khác nhau theo vai trò
    $stats = [
        'quanly' => [
            ['count' => '156', 'label' => 'Tổng phòng', 'icon' => 'fas fa-bed', 'color' => 'primary'],
            ['count' => '42', 'label' => 'Phòng trống', 'icon' => 'fas fa-door-open', 'color' => 'success'],
            ['count' => '89', 'label' => 'Khách hôm nay', 'icon' => 'fas fa-users', 'color' => 'warning'],
            ['count' => '12.5M', 'label' => 'Doanh thu', 'icon' => 'fas fa-chart-line', 'color' => 'info']
        ],
        'ketoan' => [
            ['count' => '45.2M', 'label' => 'Doanh thu tháng', 'icon' => 'fas fa-money-bill-wave', 'color' => 'success'],
            ['count' => '156', 'label' => 'Hóa đơn', 'icon' => 'fas fa-receipt', 'color' => 'primary'],
            ['count' => '23.8M', 'label' => 'Chi phí', 'icon' => 'fas fa-credit-card', 'color' => 'danger'],
            ['count' => '21.4M', 'label' => 'Lợi nhuận', 'icon' => 'fas fa-chart-pie', 'color' => 'info']
        ],
        'letan' => [
            ['count' => '15', 'label' => 'Check-in hôm nay', 'icon' => 'fas fa-sign-in-alt', 'color' => 'success'],
            ['count' => '8', 'label' => 'Check-out hôm nay', 'icon' => 'fas fa-sign-out-alt', 'color' => 'warning'],
            ['count' => '23', 'label' => 'Đặt phòng mới', 'icon' => 'fas fa-calendar-plus', 'color' => 'primary'],
            ['count' => '5', 'label' => 'Yêu cầu dịch vụ', 'icon' => 'fas fa-concierge-bell', 'color' => 'info']
        ]
        // ... thêm stats cho các role khác
    ];

    $currentStats = $stats[$role] ?? $stats['quanly'];
    
    foreach ($currentStats as $stat) {
        echo '
        <div class="col-md-3">
            <div class="stat-card p-3 bg-' . $stat['color'] . ' text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>' . $stat['count'] . '</h4>
                        <p class="mb-0">' . $stat['label'] . '</p>
                    </div>
                    <div class="align-self-center">
                        <i class="' . $stat['icon'] . ' fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>';
    }
    ?>
</div>

<!-- Nội dung tiếp theo tùy theo vai trò -->
<?php if ($role === 'quanly'): ?>
<!-- Quản lý khách hàng Section (chỉ hiển thị cho quản lý) -->
<div class="row">
    <div class="col-12">
        <div class="table-container p-4">
            <!-- ... phần quản lý khách hàng như trước ... -->
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once 'layouts/footer.php'; ?>