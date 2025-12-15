<?php
session_start();

class KhuyenMaiController
{
    private $khuyenMaiModel;

    public function __construct()
    {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['vaitro'])) {
            header('Location: ../view/login/login.php');
            exit();
        }

        // Kiểm tra vai trò - chỉ cho phép kinhdoanh
        if ($_SESSION['vaitro'] !== 'kinhdoanh') {
            $_SESSION['error'] = "Bạn không có quyền truy cập chức năng này!";
            header('Location: ../view/home/dashboard.php');
            exit();
        }

        // Include model
        require_once '../model/KhuyenMaiModel.php';
        $this->khuyenMaiModel = new KhuyenMaiModel();
    }

    // Hiển thị trang quản lý khuyến mãi
    public function index()
    {
        // Lấy dữ liệu khuyến mãi từ model
        $khuyenMais = $this->khuyenMaiModel->getAllKhuyenMai();

        // Include view và truyền dữ liệu
        require_once '../view/kinhdoanh/khuyenmai.php';
    }

    // Trong controller/khuyenmaiController.php - method add()
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // DEBUG: Log tất cả dữ liệu POST
            error_log("DEBUG - POST Data: " . print_r($_POST, true));

            $tenKM = $_POST['ten_khuyenmai'] ?? '';
            $mucGiamGia = $_POST['muc_giamgia'] ?? '';
            $ngayBatDau = $_POST['ngay_batdau'] ?? '';
            $ngayKetThuc = $_POST['ngay_ketthuc'] ?? '';
            $moTa = $_POST['mo_ta'] ?? '';
            $loaiGiamGia = $_POST['loai_giamgia'] ?? 'phantram';
            $giamGiaToiDa = !empty($_POST['giamgia_toida']) ? (float) $_POST['giamgia_toida'] : 0;

            // KIỂM TRA CHỈ ĐƯỢC NHẬP MỘT TRONG HAI
            $dkHoaDonTu = null;
            $dkSoDemTu = null;

            if (!empty($_POST['dk_hoadon_tu'])) {
                $dkHoaDonTu = (float) $_POST['dk_hoadon_tu'];
            } elseif (!empty($_POST['dk_sodem_tu'])) {
                $dkSoDemTu = (int) $_POST['dk_sodem_tu'];
            }

            // Nếu nhập cả hai, hiển thị lỗi
            if (!empty($_POST['dk_hoadon_tu']) && !empty($_POST['dk_sodem_tu'])) {
                $_SESSION['error'] = "Chỉ được nhập một điều kiện: HOẶC hóa đơn từ, HOẶC số đêm từ!";
                header('Location: ../view/kinhdoanh/khuyenmai.php');
                exit();
            }
            // DEBUG: Kiểm tra giá trị nhận được
            error_log("DEBUG - DK_HoaDonTu raw: " . ($_POST['dk_hoadon_tu'] ?? 'empty'));
            error_log("DEBUG - DK_SoDemTu raw: " . ($_POST['dk_sodem_tu'] ?? 'empty'));

            // Sửa: Chuyển sang int/float đúng cách
            $dkHoaDonTu = isset($_POST['dk_hoadon_tu']) && $_POST['dk_hoadon_tu'] !== ''
                ? (float) $_POST['dk_hoadon_tu']
                : null;

            $dkSoDemTu = isset($_POST['dk_sodem_tu']) && $_POST['dk_sodem_tu'] !== ''
                ? (int) $_POST['dk_sodem_tu']
                : null;

            // DEBUG: Giá trị sau khi xử lý
            error_log("DEBUG - DK_HoaDonTu processed: " . var_export($dkHoaDonTu, true));
            error_log("DEBUG - DK_SoDemTu processed: " . var_export($dkSoDemTu, true));

            // Lấy mã nhân viên từ session
            $maNVTao = $_SESSION['user']['ma_nhan_vien'] ?? 3;

            // Xử lý upload ảnh
            $hinhAnh = $this->handleImageUpload();

            if (!empty($tenKM) && !empty($mucGiamGia) && !empty($ngayBatDau) && !empty($ngayKetThuc)) {
                $result = $this->khuyenMaiModel->addKhuyenMai(
                    $tenKM,
                    $mucGiamGia,
                    $ngayBatDau,
                    $ngayKetThuc,
                    $moTa,
                    $hinhAnh,
                    $maNVTao,
                    $loaiGiamGia,
                    $dkHoaDonTu,
                    $dkSoDemTu,
                    $giamGiaToiDa
                );

                // DEBUG: Kết quả insert
                error_log("DEBUG - Insert result: " . ($result ? 'true' : 'false'));

                if ($result) {
                    $_SESSION['success'] = "Thêm khuyến mãi thành công!";
                } else {
                    $_SESSION['error'] = "Thêm khuyến mãi thất bại!";
                    // DEBUG: Thêm lỗi SQL nếu có
                    error_log("DEBUG - SQL Error in addKhuyenMai");
                }
            } else {
                $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
            }

            header('Location: ../view/kinhdoanh/khuyenmai.php');
            exit();
        }
    }
    // Sửa khuyến mãi với upload ảnh
    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maKM = $_POST['ma_km'] ?? '';
            $tenKM = $_POST['ten_khuyenmai'] ?? '';
            $mucGiamGia = $_POST['muc_giamgia'] ?? '';
            $ngayBatDau = $_POST['ngay_batdau'] ?? '';
            $ngayKetThuc = $_POST['ngay_ketthuc'] ?? '';
            $moTa = $_POST['mo_ta'] ?? '';
            $loaiGiamGia = $_POST['loai_giamgia'] ?? 'phantram';

            // THÊM: Lấy giá trị giảm giá tối đa
            $giamGiaToiDa = isset($_POST['giamgia_toida']) && $_POST['giamgia_toida'] !== ''
                ? (float) $_POST['giamgia_toida']
                : 0;

            // XỬ LÝ ĐIỀU KIỆN CHỈ MỘT TRONG HAI
            $dkHoaDonTu = null;
            $dkSoDemTu = null;

            // Nếu nhập hóa đơn mới => xóa số đêm
            if (!empty($_POST['dk_hoadon_tu'])) {
                $dkHoaDonTu = (float) $_POST['dk_hoadon_tu'];
                $dkSoDemTu = null; // Xóa số đêm
            }
            // Nếu nhập số đêm mới => xóa hóa đơn
            elseif (!empty($_POST['dk_sodem_tu'])) {
                $dkSoDemTu = (int) $_POST['dk_sodem_tu'];
                $dkHoaDonTu = null; // Xóa hóa đơn
            }
            // Nếu không nhập gì => giữ nguyên giá trị cũ

            // Xử lý upload ảnh (có thể không có ảnh mới)
            $hinhAnh = null;
            if (!empty($_FILES['hinh_anh']['name'])) {
                $hinhAnh = $this->handleImageUpload();
            }

            if (!empty($maKM) && !empty($tenKM) && !empty($mucGiamGia)) {
                $result = $this->khuyenMaiModel->updateKhuyenMai(
                    $maKM,
                    $tenKM,
                    $mucGiamGia,
                    $ngayBatDau,
                    $ngayKetThuc,
                    $moTa,
                    $hinhAnh,
                    $loaiGiamGia,
                    $dkHoaDonTu,
                    $dkSoDemTu,
                    $giamGiaToiDa // THÊM THAM SỐ NÀY
                );

                if ($result) {
                    $_SESSION['success'] = "Cập nhật khuyến mãi thành công!";
                } else {
                    $_SESSION['error'] = "Cập nhật khuyến mãi thất bại!";
                }
            } else {
                $_SESSION['error'] = "Thiếu thông tin cần thiết!";
            }

            header('Location: ../view/kinhdoanh/khuyenmai.php');
            exit();
        }
    }
    public function delete()
    {
        if (isset($_GET['id'])) {
            $maKM = $_GET['id'];
            $result = $this->khuyenMaiModel->deleteKhuyenMai($maKM);

            // Hiển thị debug nếu có
            if (isset($_SESSION['debug_output'])) {
                echo $_SESSION['debug_output'];
                unset($_SESSION['debug_output']);

                // Chờ 3 giây rồi redirect
                echo "<script>
                setTimeout(function() {
                    window.location.href = '../view/kinhdoanh/khuyenmai.php';
                }, 3000);
            </script>";
                exit();
            }

            if ($result) {
                $_SESSION['success'] = "Xóa khuyến mãi thành công!";
            } else {
                $_SESSION['error'] = "Xóa khuyến mãi thất bại!";
            }
        } else {
            $_SESSION['error'] = "Không tìm thấy khuyến mãi để xóa!";
        }

        header('Location: ../view/kinhdoanh/khuyenmai.php');
        exit();
    }

    // Xóa nhiều khuyến mãi
    public function deleteMultiple()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids'])) {
            $selectedIds = $_POST['selected_ids'];
            $successCount = 0;

            foreach ($selectedIds as $id) {
                $result = $this->khuyenMaiModel->deleteKhuyenMai($id);
                if ($result) $successCount++;
            }

            if ($successCount > 0) {
                $_SESSION['success'] = "Đã xóa thành công $successCount khuyến mãi!";
            } else {
                $_SESSION['error'] = "Xóa khuyến mãi thất bại!";
            }
        } else {
            $_SESSION['error'] = "Vui lòng chọn khuyến mãi để xóa!";
        }

        header('Location: ../view/kinhdoanh/khuyenmai.php');
        exit();
    }

    // Xử lý upload ảnh
    private function handleImageUpload()
    {
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ABC-Resort/client/assets/images/sales/';

            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['hinh_anh']['name']);
            $uploadFile = $uploadDir . $fileName;

            // Kiểm tra loại file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['hinh_anh']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['error'] = "Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP)!";
                return null;
            }

            // Kiểm tra kích thước file (max 5MB)
            if ($_FILES['hinh_anh']['size'] > 5 * 1024 * 1024) {
                $_SESSION['error'] = "Kích thước file quá lớn (tối đa 5MB)!";
                return null;
            }

            // Di chuyển file
            if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $uploadFile)) {
                // Trả về đường dẫn lưu trong database
                return 'assets/images/sales/' . $fileName;
            } else {
                $_SESSION['error'] = "Upload ảnh thất bại!";
                return null;
            }
        }

        return 'assets/images/sales/default_promotion.png'; // Ảnh mặc định
    }
}

// Xử lý request
$action = $_GET['action'] ?? 'index';
$controller = new KhuyenMaiController();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    $controller->index();
}
