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

    // TẠO THƯ MỤC PHÒNG - FIXED
    private function taoThuMucPhong($soPhong)
    {
        /** @var string $soPhong */
        $folder_name = 'room' . substr($soPhong, 1); // P101 -> room101
        $base_dir = __DIR__ . "/../../client/assets/images/rooms/";
        $target_dir = $base_dir . $folder_name . "/";

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
    // UPLOAD ẢNH - FIXED (KHÔNG LƯU ĐUÔI FILE)
    private function uploadImageToRoom($file, $soPhong, $is_avatar = false)
    {
        /** @var array $file */
        /** @var bool $is_avatar */

        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File không tồn tại'];
        }

        // Tạo folder
        $folder_name = 'room' . substr($soPhong, 1); // P101 -> room101
        $base_dir = __DIR__ . "/../../client/assets/images/rooms/";
        $target_dir = $base_dir . $folder_name . "/";

        error_log("Tạo folder: " . $target_dir);

        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                return ['success' => false, 'error' => 'Không thể tạo thư mục: ' . $target_dir];
            }
            error_log("✅ Đã tạo thư mục: " . $target_dir);
        }

        // Kiểm tra ảnh
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return ['success' => false, 'error' => 'File không phải là ảnh'];
        }

        if ($file["size"] > 5000000) {
            return ['success' => false, 'error' => 'File quá lớn (tối đa 5MB)'];
        }

        // Kiểm tra định dạng file
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "webp"];

        if (!in_array($file_extension, $allowed_extensions)) {
            return ['success' => false, 'error' => 'Chỉ chấp nhận file ảnh JPG, JPEG, PNG, GIF, WEBP'];
        }

        // Tạo tên file - KHÔNG CÓ ĐUÔI
        if ($is_avatar) {
            $file_name = "avatar1";
        } else {
            // Đếm số file chitiet hiện có
            $existing_files = glob($target_dir . "chitiet*");
            $next_number = count($existing_files) + 1;
            $file_name = "chitiet" . $next_number;
        }

        // Đường dẫn đích LUÔN LÀ .jpeg (trên filesystem)
        $target_file = $target_dir . $file_name . ".jpeg";

        // Upload file
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            error_log("✅ Đã upload ảnh: " . $target_file);
            // QUAN TRỌNG: Chỉ lưu folder_name/file_name (KHÔNG CÓ ĐUÔI)
            return [
                'success' => true,
                'file_path' => $folder_name . "/" . $file_name
            ];
        } else {
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
    // Hàm format tiện nghi - THÊM VÀO MODEL
    public function formatTienNghi($tienNghiChinh, $tienNghiThem)
    {
        $tienNghiArray = [];

        if (!empty($tienNghiChinh)) {
            $tienNghiArray[] = $tienNghiChinh;
        }

        if (!empty($tienNghiThem)) {
            $lines = explode("\n", $tienNghiThem);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $tienNghiArray[] = $line;
                }
            }
        }

        return !empty($tienNghiArray) ? json_encode($tienNghiArray, JSON_UNESCAPED_UNICODE) : '[]';
    }

    // THÊM PHÒNG MỚI - FIXED (TÍNH TONG GIA VÀ SỬA JSON)
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

            // TẠO FOLDER TRƯỚC
            $folder_name = $this->taoThuMucPhong($soPhong);

            // Upload avatar - BẮT BUỘC
            $avatarPath = '';
            if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImageToRoom($avatarFile, $soPhong, true);
                if ($uploadResult['success']) {
                    $avatarPath = $uploadResult['file_path'];
                } else {
                    throw new Exception('Lỗi upload avatar: ' . $uploadResult['error']);
                }
            } else {
                throw new Exception('Ảnh đại diện là bắt buộc');
            }

            // Upload nhiều ảnh chi tiết
            $danhSachAnh = [];
            if ($imageFiles && !empty($imageFiles['name'][0])) {
                $uploadedImages = $this->uploadMultipleImagesToRoom($imageFiles, $soPhong);
                if (!empty($uploadedImages)) {
                    $danhSachAnh = $uploadedImages;
                }
            }

            // Thêm avatar vào ĐẦU danh sách ảnh
            array_unshift($danhSachAnh, $avatarPath);

            // LẤY ĐƠN GIÁ TỪ LOẠI PHÒNG ĐỂ TÍNH TỔNG GIÁ
            $maLoaiPhong = isset($data['MaLoaiPhong']) ? intval($data['MaLoaiPhong']) : 1;
            $donGiaLoaiPhong = $this->getDonGiaLoaiPhong($maLoaiPhong, $conn);

            // Chuẩn bị dữ liệu
            $tang = isset($data['Tang']) ? intval($data['Tang']) : 1;
            $trangThai = isset($data['TrangThai']) ? $data['TrangThai'] : 'Trống';
            $roomName = isset($data['roomName']) ? $data['roomName'] : 'Phòng ' . $soPhong;
            $giaPhong = isset($data['GiaPhong']) ? floatval($data['GiaPhong']) : 0.00;
            $dienTich = isset($data['DienTich']) ? floatval($data['DienTich']) : 0.00;
            $soKhachToiDa = isset($data['SoKhachToiDa']) ? intval($data['SoKhachToiDa']) : 2;
            $huongNha = isset($data['HuongNha']) ? $data['HuongNha'] : '';
            $moTaChiTiet = isset($data['MoTaChiTiet']) ? $data['MoTaChiTiet'] : '';
            $tienNghi = isset($data['TienNghi']) ? $data['TienNghi'] : '[]';

            // TÍNH TỔNG GIÁ = Giá phòng + Đơn giá loại phòng
            $tongGia = $giaPhong + $donGiaLoaiPhong;

            // Thêm vào database
            $sql = "INSERT INTO Phong (SoPhong, Tang, MaLoaiPhong, TrangThai, Avatar, DanhSachPhong, roomName, GiaPhong, TongGia, DienTich, SoKhachToiDa, HuongNha, MoTaChiTiet, TienNghi) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Lỗi prepare SQL: ' . $conn->error);
            }

            // QUAN TRỌNG: JSON_UNESCAPED_SLASHES để không có \/
            $danhSachAnhJson = !empty($danhSachAnh) ? json_encode($danhSachAnh, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]';

            // SỬA LẠI bind_param cho đúng 14 tham số
            $stmt->bind_param(
                "siissssddiisss", // 14 ký tự cho 14 tham số
                $soPhong,           // s
                $tang,              // i
                $maLoaiPhong,       // i
                $trangThai,         // s
                $avatarPath,        // s
                $danhSachAnhJson,   // s
                $roomName,          // s
                $giaPhong,          // d (decimal)
                $tongGia,           // d (decimal)
                $dienTich,          // d (decimal)
                $soKhachToiDa,      // i
                $huongNha,          // s
                $moTaChiTiet,       // s
                $tienNghi           // s (JSON string)
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
                'tongGia' => $tongGia,
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

    // LẤY CHI TIẾT PHÒNG THEO MÃ
    public function getChiTietPhong($maPhong)
    {
        $conn = $this->db->openConnect();

        try {
            $sql = "SELECT p.*, lp.HangPhong, lp.HinhThuc, lp.DonGia 
                FROM Phong p 
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                WHERE p.MaPhong = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $maPhong);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $phong = $result->fetch_assoc();

                // Parse JSON danh sách ảnh và tiện nghi
                if (!empty($phong['DanhSachPhong'])) {
                    $phong['DanhSachPhong'] = json_decode($phong['DanhSachPhong'], true);
                } else {
                    $phong['DanhSachPhong'] = [];
                }

                if (!empty($phong['TienNghi'])) {
                    $phong['TienNghi'] = json_decode($phong['TienNghi'], true);
                } else {
                    $phong['TienNghi'] = [];
                }

                $this->db->closeConnect($conn);
                return $phong;
            } else {
                $this->db->closeConnect($conn);
                return null;
            }
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            error_log("Lỗi getChiTietPhong: " . $e->getMessage());
            return null;
        }
    }
    // CẬP NHẬT PHÒNG
    public function capNhatPhong($maPhong, $data, $avatarFile = null, $imageFiles = null)
    {
        $conn = $this->db->openConnect();

        try {
            // Lấy thông tin phòng hiện tại
            $phongHienTai = $this->getChiTietPhong($maPhong);
            if (!$phongHienTai) {
                throw new Exception('Không tìm thấy phòng');
            }

            $soPhong = $phongHienTai['SoPhong'];

            // Upload avatar mới nếu có
            $avatarPath = $phongHienTai['Avatar'];
            if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImageToRoom($avatarFile, $soPhong, true);
                if ($uploadResult['success']) {
                    $avatarPath = $uploadResult['file_path'];
                }
                // Nếu upload avatar thất bại, vẫn giữ avatar cũ, không throw exception
            }

            // Upload nhiều ảnh chi tiết mới nếu có
            $danhSachAnh = $phongHienTai['DanhSachPhong'] ?? [];
            if ($imageFiles && !empty($imageFiles['name'][0])) {
                $uploadedImages = $this->uploadMultipleImagesToRoom($imageFiles, $soPhong);
                if (!empty($uploadedImages)) {
                    // Thêm ảnh mới vào danh sách hiện tại
                    $danhSachAnh = array_merge($danhSachAnh, $uploadedImages);
                }
            }

            // Đảm bảo avatar có trong danh sách ảnh
            if (!empty($avatarPath) && !in_array($avatarPath, $danhSachAnh)) {
                // Tìm và xóa avatar cũ khỏi danh sách nếu có
                $danhSachAnh = array_filter($danhSachAnh, function ($img) use ($phongHienTai) {
                    return $img !== $phongHienTai['Avatar'];
                });
                // Thêm avatar mới vào đầu danh sách
                array_unshift($danhSachAnh, $avatarPath);
            }

            // Lấy đơn giá từ loại phòng để tính tổng giá
            $maLoaiPhong = isset($data['MaLoaiPhong']) ? intval($data['MaLoaiPhong']) : $phongHienTai['MaLoaiPhong'];
            $donGiaLoaiPhong = $this->getDonGiaLoaiPhong($maLoaiPhong, $conn);

            // Chuẩn bị dữ liệu
            $tang = isset($data['Tang']) ? intval($data['Tang']) : $phongHienTai['Tang'];
            $trangThai = isset($data['TrangThai']) ? $data['TrangThai'] : $phongHienTai['TrangThai'];
            $roomName = isset($data['roomName']) ? $data['roomName'] : $phongHienTai['roomName'];
            $giaPhong = isset($data['GiaPhong']) ? floatval($data['GiaPhong']) : $phongHienTai['GiaPhong'];
            $dienTich = isset($data['DienTich']) ? floatval($data['DienTich']) : $phongHienTai['DienTich'];
            $soKhachToiDa = isset($data['SoKhachToiDa']) ? intval($data['SoKhachToiDa']) : $phongHienTai['SoKhachToiDa'];
            $huongNha = isset($data['HuongNha']) ? $data['HuongNha'] : $phongHienTai['HuongNha'];
            $moTaChiTiet = isset($data['MoTaChiTiet']) ? $data['MoTaChiTiet'] : $phongHienTai['MoTaChiTiet'];
            $tienNghi = isset($data['TienNghi']) ? $data['TienNghi'] : (is_array($phongHienTai['TienNghi']) ? json_encode($phongHienTai['TienNghi']) : $phongHienTai['TienNghi']);

            // Tính tổng giá mới
            $tongGia = $giaPhong + $donGiaLoaiPhong;

            // Cập nhật database
            $sql = "UPDATE Phong SET 
                Tang = ?, MaLoaiPhong = ?, TrangThai = ?, Avatar = ?, 
                DanhSachPhong = ?, roomName = ?, GiaPhong = ?, TongGia = ?, 
                DienTich = ?, SoKhachToiDa = ?, HuongNha = ?, MoTaChiTiet = ?, TienNghi = ? 
                WHERE MaPhong = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Lỗi prepare SQL: ' . $conn->error);
            }

            $danhSachAnhJson = !empty($danhSachAnh) ? json_encode($danhSachAnh, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]';

            $stmt->bind_param(
                "iissssddiisssi",
                $tang,
                $maLoaiPhong,
                $trangThai,
                $avatarPath,
                $danhSachAnhJson,
                $roomName,
                $giaPhong,
                $tongGia,
                $dienTich,
                $soKhachToiDa,
                $huongNha,
                $moTaChiTiet,
                $tienNghi,
                $maPhong
            );

            $result = $stmt->execute();
            if (!$result) {
                throw new Exception('Lỗi cập nhật phòng: ' . $stmt->error);
            }

            $stmt->close();
            $this->db->closeConnect($conn);

            return [
                'success' => true,
                'soPhong' => $soPhong,
                'message' => 'Cập nhật phòng thành công!'
            ];
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            error_log("Lỗi capNhatPhong: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    // HÀM LẤY ĐƠN GIÁ LOẠI PHÒNG
    private function getDonGiaLoaiPhong($maLoaiPhong, $conn)
    {
        $sql = "SELECT DonGia FROM LoaiPhong WHERE MaLoaiPhong = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $maLoaiPhong);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return floatval($row['DonGia']);
        }

        return 0.00; // Mặc định nếu không tìm thấy
    }
    // XÓA PHÒNG
    public function xoaPhong($maPhong)
    {
        $conn = $this->db->openConnect();

        try {
            // Lấy thông tin phòng để xóa folder ảnh
            $sqlSelect = "SELECT SoPhong FROM Phong WHERE MaPhong = ?";
            $stmtSelect = $conn->prepare($sqlSelect);
            $stmtSelect->bind_param("i", $maPhong);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();

            if ($result->num_rows > 0) {
                $phong = $result->fetch_assoc();
                $soPhong = $phong['SoPhong'];

                // BẮT ĐẦU TRANSACTION
                $conn->begin_transaction();

                try {
                    // 1. XÓA DỮ LIỆU TRONG BẢNG CON TRƯỚC
                    $this->xoaDuLieuLienQuan($maPhong, $conn);

                    // 2. XÓA PHÒNG TRONG BẢNG CHA
                    $sqlDelete = "DELETE FROM Phong WHERE MaPhong = ?";
                    $stmtDelete = $conn->prepare($sqlDelete);
                    $stmtDelete->bind_param("i", $maPhong);
                    $resultDelete = $stmtDelete->execute();

                    if ($resultDelete) {
                        // COMMIT TRANSACTION NẾU THÀNH CÔNG
                        $conn->commit();
                        // Xóa folder ảnh
                        $this->xoaThuMucPhong($soPhong);

                        return [
                            'success' => true,
                            'message' => 'Xóa phòng thành công'
                        ];
                    } else {
                        $conn->rollback();
                        return [
                            'success' => false,
                            'error' => 'Lỗi khi xóa phòng'
                        ];
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            } else {
                $this->db->closeConnect($conn);
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy phòng'
                ];
            }
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // XÓA NHIỀU PHÒNG
    public function xoaNhieuPhong($maPhongs)
    {
        $conn = $this->db->openConnect();
        $soLuong = 0;
        $errors = [];

        try {
            // Lấy thông tin các phòng để xóa folder ảnh
            $placeholders = str_repeat('?,', count($maPhongs) - 1) . '?';
            $sqlSelect = "SELECT MaPhong, SoPhong FROM Phong WHERE MaPhong IN ($placeholders)";
            $stmtSelect = $conn->prepare($sqlSelect);
            $stmtSelect->bind_param(str_repeat('i', count($maPhongs)), ...$maPhongs);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();

            $danhSachPhong = [];
            while ($row = $result->fetch_assoc()) {
                $danhSachPhong[] = $row;
            }

            // BẮT ĐẦU TRANSACTION
            $conn->begin_transaction();

            try {
                foreach ($danhSachPhong as $phong) {
                    // 1. XÓA DỮ LIỆU TRONG BẢNG CON TRƯỚC
                    $this->xoaDuLieuLienQuan($phong['MaPhong'], $conn);

                    // 2. XÓA PHÒNG TRONG BẢNG CHA
                    $sqlDelete = "DELETE FROM Phong WHERE MaPhong = ?";
                    $stmtDelete = $conn->prepare($sqlDelete);
                    $stmtDelete->bind_param("i", $phong['MaPhong']);

                    if ($stmtDelete->execute()) {
                        $soLuong++;
                        // Xóa folder ảnh
                        $this->xoaThuMucPhong($phong['SoPhong']);
                    } else {
                        $errors[] = "Lỗi khi xóa phòng " . $phong['SoPhong'];
                    }
                }

                // COMMIT TRANSACTION NẾU THÀNH CÔNG
                $conn->commit();

                return [
                    'success' => $soLuong > 0,
                    'so_luong' => $soLuong,
                    'errors' => $errors,
                    'message' => $soLuong > 0 ? "Đã xóa {$soLuong} phòng thành công" : "Không thể xóa phòng"
                ];
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            $this->db->closeConnect($conn);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // XÓA DỮ LIỆU LIÊN QUAN TRONG CÁC BẢNG CON
    private function xoaDuLieuLienQuan($maPhong, $conn)
    {
        // DANH SÁCH CÁC BẢNG CÓ FOREIGN KEY ĐẾN PHONG
        $cacBangCon = [
            'phong_capnhat_trangthai',
            'thietbi', // nếu có bảng thiết bị
            // Thêm các bảng khác có foreign key đến phong ở đây
        ];

        foreach ($cacBangCon as $bang) {
            try {
                $sql = "DELETE FROM $bang WHERE MaPhong = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $maPhong);
                $stmt->execute();
                error_log("✅ Đã xóa dữ liệu từ bảng: $bang - MaPhong: $maPhong");
            } catch (Exception $e) {
                error_log("⚠️ Không thể xóa từ bảng $bang: " . $e->getMessage());
                // Tiếp tục xử lý các bảng khác, không dừng lại
            }
        }
    }

    // XÓA THƯ MỤC PHÒNG
    private function xoaThuMucPhong($soPhong)
    {
        try {
            $folder_name = 'room' . substr($soPhong, 1); // P101 -> room101
            $base_dir = __DIR__ . "/../../client/assets/images/rooms/";
            $target_dir = $base_dir . $folder_name . "/";

            if (file_exists($target_dir)) {
                // Xóa tất cả file trong thư mục
                $files = glob($target_dir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                // Xóa thư mục
                rmdir($target_dir);
                error_log("✅ Đã xóa thư mục: " . $target_dir);
            }
        } catch (Exception $e) {
            error_log("❌ Lỗi khi xóa thư mục: " . $e->getMessage());
        }
    }
}
