<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tỏa Sáng Resort - Trang Chủ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .room-card {
            transition: all 0.3s ease;
            border: none;
        }
        .room-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        .jumbotron {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        .equipment-section {
            border-top: 1px dashed #dee2e6;
            padding-top: 15px;
            margin-top: 15px;
        }
        /* Dropdown styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-umbrella-beach me-2"></i>Tỏa Sáng RESORT
            </a>
            
            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link active" href="#">Trang Chủ</a>
                    <a class="nav-link" href="#room-list">Phòng</a>
                    
                    <!-- Dropdown Khuyến mãi -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-gift me-1"></i>Khuyến mãi
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-percentage me-2"></i>Ưu đãi đặc biệt
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-tag me-2"></i>Combo tiết kiệm
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-star me-2"></i>Ưu đãi thành viên
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Dropdown Hỗ trợ -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-headset me-1"></i>Hỗ trợ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-question-circle me-2"></i>Trợ giúp
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-phone me-2"></i>Liên hệ chúng tôi
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-envelope me-2"></i>Hộp thư của tôi
                            </a></li>
                        </ul>
                    </div>
                    
                    <a class="nav-link" href="#">Đặt Phòng</a>
                    <a class="nav-link" href="#">Liên Hệ</a>
                    
                    <!-- Đặt chỗ & Tài khoản -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>Tài khoản
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-suitcase me-2"></i>Đặt chỗ của tôi
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>