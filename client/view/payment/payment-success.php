<?php
include __DIR__ . '/../layouts/header.php';
$bookingCode = $_GET['bookingCode'] ?? '';
?>

<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body py-5">
                    <div class="text-success mb-4">
                        <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-success mb-3">Đặt Phòng Thành Công!</h2>
                    <p class="text-muted mb-4">Cảm ơn bạn đã đặt phòng tại ABC Resort</p>
                    
                    <div class="booking-info bg-light rounded p-4 mb-4">
                        <h5 class="fw-bold">Mã đặt phòng: <?php echo htmlspecialchars($bookingCode); ?></h5>
                        <p class="text-muted mb-0">Vui lòng giữ mã này để nhận phòng</p>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/ABC-Resort/client/view/home/index.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Về Trang Chủ
                        </a>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>In Hóa Đơn
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>