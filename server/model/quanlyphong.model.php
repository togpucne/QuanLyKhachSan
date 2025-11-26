<?php
include_once 'connectDB.php';

class QuanLyPhongModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Connect();
    }

    // LẤY DANH SÁCH PHÒNG VỚI BỘ LỌC
    public function getDanhSachPhong($keyword = '', $tang = '', $loaiPhong = '', $trangThai = '')
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT p.*, lp.HangPhong, lp.HinhThuc, lp.DonGia 
                FROM Phong p 
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($keyword)) {
            $sql .= " AND (p.SoPhong LIKE ? OR p.roomName LIKE ?)";
            $searchTerm = "%$keyword%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
            $types .= "ss";
        }

        if (!empty($tang)) {
            $sql .= " AND p.Tang = ?";
            $params[] = $tang;
            $types .= "i";
        }

        if (!empty($loaiPhong)) {
            $sql .= " AND p.MaLoaiPhong = ?";
            $params[] = $loaiPhong;
            $types .= "i";
        }

        if (!empty($trangThai)) {
            $sql .= " AND p.TrangThai = ?";
            $params[] = $trangThai;
            $types .= "s";
        }

        $sql .= " ORDER BY p.Tang, p.SoPhong";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // LẤY DANH SÁCH LOẠI PHÒNG
    public function getDanhSachLoaiPhong()
    {
        $conn = $this->db->openConnect();

        $sql = "SELECT * FROM LoaiPhong ORDER BY DonGia";
        $result = $conn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->db->closeConnect($conn);
        return $data;
    }

    // TẠO MÃ PHÒNG TỰ ĐỘNG
    public function taoMaPhongTuDong()
    {
        $conn = $this->db->openConnect();

        try {
            $sql = "SELECT SoPhong FROM Phong ORDER BY MaPhong DESC LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $lastSoPhong = $row['SoPhong'];
                $lastNumber = intval(substr($lastSoPhong, 1));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 101;
            }

            $this->db->closeConnect($conn);
            return 'P' . $newNumber;
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return 'P' . rand(100, 999);
        }
    }

    // TẠO THƯ MỤC PHÒNG - FIXED (THÊM KIỂM TRA VÀ DEBUG)
    private function taoThuMucPhong($soPhong)
    {
        /** @var string $soPhong */
        $folder_name = 'room' . substr($soPhong, 1); // P101 -> room101
        $base_dir = __DIR__ . "/../../assets/images/rooms/";
        $target_dir = $base_dir . $folder_name . "/";

        // DEBUG
        error_log("=== TẠO THƯ MỤC PHÒNG ===");
        error_log("Số phòng: " . $soPhong);
        error_log("Đường dẫn: " . $target_dir);

        // Kiểm tra thư mục gốc tồn tại chưa
        if (!file_exists($base_dir)) {
            error_log("Tạo thư mục gốc: " . $base_dir);
            if (!mkdir($base_dir, 0777, true)) {
                throw new Exception('Không thể tạo thư mục gốc: ' . $base_dir);
            }
        }

        // Tạo thư mục phòng
        if (!file_exists($target_dir)) {
            error_log("Tạo thư mục phòng: " . $target_dir);
            if (!mkdir($target_dir, 0777, true)) {
                throw new Exception('Không thể tạo thư mục phòng: ' . $target_dir);
            }
            error_log("✅ Đã tạo thư mục: " . $target_dir);
        } else {
            error_log("ℹ️ Thư mục đã tồn tại: " . $target_dir);
        }

        // Kiểm tra quyền ghi
        if (!is_writable($target_dir)) {
            throw new Exception('Thư mục không có quyền ghi: ' . $target_dir);
        }

        return $folder_name;
    }
    // UPLOAD ẢNH - FIXED (GỌI taoThuMucPhong)
    private function uploadImageToRoom($file, $soPhong, $is_avatar = false)
    {
        /** @var string $soPhong */
        /** @var array $file */
        /** @var bool $is_avatar */

        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File không tồn tại'];
        }

        // QUAN TRỌNG: GỌI METHOD taoThuMucPhong để tạo folder
        $folder_name = $this->taoThuMucPhong($soPhong);
        $base_dir = __DIR__ . "/../../assets/images/rooms/";
        $target_dir = $base_dir . $folder_name . "/";

        error_log("Upload ảnh vào: " . $target_dir);

        // Kiểm tra ảnh
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return ['success' => false, 'error' => 'File không phải là ảnh'];
        }

        if ($file["size"] > 5000000) {
            return ['success' => false, 'error' => 'File quá lớn (tối đa 5MB)'];
        }

        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "webp"];
        if (!in_array($file_extension, $allowed_extensions)) {
            return ['success' => false, 'error' => 'Chỉ chấp nhận file ảnh'];
        }

        // Tạo tên file ĐÚNG FORMAT - KHÔNG CÓ ĐUÔI FILE
        if ($is_avatar) {
            $file_name = "avatar1"; // Chỉ lưu "avatar1" không có đuôi
        } else {
            // Tạo tên chitiet1, chitiet2... 
            $existing_files = glob($target_dir . "chitiet*");
            $next_number = count($existing_files) + 1;
            $file_name = "chitiet" . $next_number;
        }

        // Upload file có đuôi
        $target_file = $target_dir . $file_name . "." . $file_extension;

        error_log("Lưu file: " . $target_file);

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            error_log("✅ Upload thành công: " . $file_name);
            // CHỈ LƯU TÊN KHÔNG ĐUÔI: "room101/avatar1", "room101/chitiet1"
            return [
                'success' => true,
                'file_path' => $folder_name . "/" . $file_name
            ];
        } else {
            error_log("❌ Upload thất bại");
            return ['success' => false, 'error' => 'Lỗi khi upload file'];
        }
    }
    // UPLOAD NHIỀU ẢNH - FIXED
    private function uploadMultipleImagesToRoom($files, $soPhong)
    {
        /** @var string $soPhong */
        /** @var array $files */

        $uploaded_files = [];

        if (!isset($files) || empty($files['name'][0])) {
            return $uploaded_files;
        }

        // Đảm bảo thư mục tồn tại
        $this->taoThuMucPhong($soPhong);

        foreach ($files['tmp_name'] as $key => $tmp_name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                ];

                $result = $this->uploadImageToRoom($file, $soPhong, false);
                if ($result['success']) {
                    $uploaded_files[] = $result['file_path'];
                }
            }
        }

        return $uploaded_files;
    }

    // THÊM PHÒNG MỚI - FIXED INTELEPHENSE
    public function themPhongMoi($data, $avatarFile = null, $imageFiles = null)
    {
        /** @var array $data */
        /** @var array|null $avatarFile */
        /** @var array|null $imageFiles */

        $conn = $this->db->openConnect();

        try {
            // Tạo mã phòng
            $soPhong = $this->taoMaPhongTuDong();
            /** @var string $soPhong */

            // Upload avatar - LUÔN TẠO AVATAR
            $avatarPath = '';
            if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImageToRoom($avatarFile, $soPhong, true);
                if ($uploadResult['success']) {
                    $avatarPath = $uploadResult['file_path'];
                }
            }

            // Nếu không upload avatar, vẫn tạo avatar mặc định - FIXED INTELEPHENSE
            if (empty($avatarPath)) {
                /** @var string $soPhong */
                $avatarPath = 'room' . substr($soPhong, 1) . '/avatar1';
            }

            // Upload nhiều ảnh
            $danhSachAnh = [];
            if ($imageFiles && !empty($imageFiles['name'][0])) {
                $uploadedImages = $this->uploadMultipleImagesToRoom($imageFiles, $soPhong);
                if (!empty($uploadedImages)) {
                    $danhSachAnh = $uploadedImages;
                }
            }

            // Thêm avatar vào đầu danh sách ảnh nếu có
            if (!empty($avatarPath) && !in_array($avatarPath, $danhSachAnh)) {
                array_unshift($danhSachAnh, $avatarPath);
            }

            // Chuẩn bị dữ liệu
            $tang = isset($data['Tang']) ? intval($data['Tang']) : 1;
            $maLoaiPhong = isset($data['MaLoaiPhong']) ? intval($data['MaLoaiPhong']) : 1;
            $trangThai = isset($data['TrangThai']) ? $data['TrangThai'] : 'Trống';
            $roomName = isset($data['roomName']) ? $data['roomName'] : 'Phòng ' . $soPhong;
            $giaPhong = isset($data['GiaPhong']) ? floatval($data['GiaPhong']) : 0.00;

            // Thêm vào database - ĐÚNG FORMAT JSON
            $sql = "INSERT INTO Phong (SoPhong, Tang, MaLoaiPhong, TrangThai, Avatar, DanhSachPhong, roomName, GiaPhong) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Lỗi prepare SQL: ' . $conn->error);
            }

            $danhSachAnhJson = !empty($danhSachAnh) ? json_encode($danhSachAnh, JSON_PRETTY_PRINT) : '[]';

            $stmt->bind_param(
                "siissssd",
                $soPhong,
                $tang,
                $maLoaiPhong,
                $trangThai,
                $avatarPath,
                $danhSachAnhJson,
                $roomName,
                $giaPhong
            );

            $result = $stmt->execute();
            if (!$result) {
                throw new Exception('Lỗi thêm phòng: ' . $stmt->error);
            }

            $maPhong = $conn->insert_id;
            $stmt->close();
            $this->db->closeConnect($conn);

            return [
                'success' => true,
                'maPhong' => $maPhong,
                'soPhong' => $soPhong,
                'message' => 'Thêm phòng thành công!'
            ];
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
