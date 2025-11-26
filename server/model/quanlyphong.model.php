    <?php
    include_once 'connectDB.php';

    class QuanLyPhongModel
    {
        private $db;

        public function __construct()
        {
            $this->db = new Connect();
        }

        // TẠO MÃ PHÒNG 3 SỐ - KHÔNG TRÙNG
        public function taoMaPhongNgauNhien()
        {
            $conn = $this->db->openConnect();

            try {
                // Lấy tất cả mã phòng hiện có
                $sql = "SELECT SoPhong FROM Phong WHERE SoPhong LIKE 'P%'";
                $result = $conn->query($sql);

                $maPhongDaCo = [];
                while ($row = $result->fetch_assoc()) {
                    $maPhongDaCo[] = $row['SoPhong'];
                }

                // Tạo danh sách tất cả mã có thể (P100 đến P999)
                $tatCaMaCoThe = [];
                for ($i = 100; $i <= 999; $i++) {
                    $tatCaMaCoThe[] = 'P' . $i;
                }

                // Loại bỏ các mã đã tồn tại
                $maConTrong = array_diff($tatCaMaCoThe, $maPhongDaCo);

                // Nếu còn mã trống, chọn ngẫu nhiên
                if (!empty($maConTrong)) {
                    $maPhong = $maConTrong[array_rand($maConTrong)];
                } else {
                    // Nếu hết mã 3 số, dùng mã 4 số
                    for ($i = 1000; $i <= 9999; $i++) {
                        $maThu = 'P' . $i;
                        if (!in_array($maThu, $maPhongDaCo)) {
                            $maPhong = $maThu;
                            break;
                        }
                    }
                }

                $this->db->closeConnect($conn);
                return $maPhong;
            } catch (Exception $e) {
                $this->db->closeConnect($conn);
                // Fallback: phương pháp cũ
                return 'P' . rand(100, 999);
            }
        }

        // UPLOAD ẢNH ĐƠN GIẢN - CHỈ LƯU ĐƯỜNG DẪN
        public function uploadImageSimple($file, $folder, $soPhong)
        {
            // KIỂM TRA FILE CÓ TỒN TẠI KHÔNG
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'error' => 'File không tồn tại hoặc lỗi upload'];
            }

            $base_dir = "../../assets/images/";
            $target_dir = $base_dir . $folder . "/";

            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Kiểm tra file ảnh
            $check = getimagesize($file["tmp_name"]);
            if ($check === false) {
                return ['success' => false, 'error' => 'File không phải là ảnh'];
            }

            // Giới hạn kích thước file (5MB)
            if ($file["size"] > 5000000) {
                return ['success' => false, 'error' => 'File quá lớn (tối đa 5MB)'];
            }

            // Cho phép các định dạng ảnh
            $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            $allowed_extensions = ["jpg", "jpeg", "png", "gif", "webp"];
            if (!in_array($file_extension, $allowed_extensions)) {
                return ['success' => false, 'error' => 'Chỉ chấp nhận file JPG, JPEG, PNG, GIF & WEBP'];
            }

            // Tạo tên file đơn giản
            $file_name = $soPhong . "_" . $folder . "." . $file_extension;
            $target_file = $target_dir . $file_name;

            // Upload file
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                return [
                    'success' => true,
                    'file_path' => $folder . "/" . $file_name  // SỬA: chỉ lưu folder/filename
                ];
            } else {
                return ['success' => false, 'error' => 'Lỗi khi upload file'];
            }
        }

        // UPLOAD NHIỀU ẢNH ĐƠN GIẢN
        public function uploadMultipleImagesSimple($files, $soPhong)
        {
            $uploaded_files = [];

            // KIỂM TRA CÓ FILE NÀO ĐƯỢC UPLOAD KHÔNG
            if (!isset($files) || empty($files['name'][0])) {
                return $uploaded_files;
            }

            foreach ($files['tmp_name'] as $key => $tmp_name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    ];

                    $result = $this->uploadImageSimple($file, 'rooms', $soPhong);
                    if ($result['success']) {
                        $uploaded_files[] = $result['file_path'];
                    }
                }
            }

            return $uploaded_files;
        }

        // THÊM PHÒNG MỚI - PHIÊN BẢN HOÀN CHỈNH
        public function themPhongMoi($data, $avatarFile = null, $imageFiles = null)
        {
            $conn = $this->db->openConnect();

            // Kiểm tra kết nối
            if ($conn->connect_error) {
                return ['success' => false, 'error' => 'Kết nối database thất bại: ' . $conn->connect_error];
            }

            try {
                // TẠO MÃ PHÒNG NGẪU NHIÊN
                $soPhong = $this->taoMaPhongNgauNhien();

                // Xử lý upload ảnh avatar
                $avatarPath = '';
                if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->uploadImageSimple($avatarFile, 'avatar', $soPhong);
                    if ($uploadResult['success']) {
                        $avatarPath = $uploadResult['file_path'];
                    }
                }

                // Xử lý upload nhiều ảnh
                $danhSachAnh = '';
                if ($imageFiles && !empty($imageFiles['name'][0])) {
                    $uploadedImages = $this->uploadMultipleImagesSimple($imageFiles, $soPhong);
                    if (!empty($uploadedImages)) {
                        $danhSachAnh = json_encode($uploadedImages);
                    }
                }

                // Kiểm tra và chuẩn hóa dữ liệu
                $tang = isset($data['Tang']) ? intval($data['Tang']) : 1;
                $maLoaiPhong = isset($data['MaLoaiPhong']) ? intval($data['MaLoaiPhong']) : 1;
                $trangThai = isset($data['TrangThai']) ? $data['TrangThai'] : 'Trống';
                $roomName = isset($data['roomName']) ? $data['roomName'] : 'Phòng ' . $soPhong;
                $giaPhong = isset($data['GiaPhong']) ? floatval($data['GiaPhong']) : 0.00;

                // Câu SQL INSERT
                $sql = "INSERT INTO Phong (SoPhong, Tang, MaLoaiPhong, TrangThai, Avatar, DanhSachPhong, roomName, GiaPhong) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Lỗi prepare SQL: ' . $conn->error);
                }

                // Bind parameters
                $stmt->bind_param(
                    "siissssd",
                    $soPhong,
                    $tang,
                    $maLoaiPhong,
                    $trangThai,
                    $avatarPath,
                    $danhSachAnh,
                    $roomName,
                    $giaPhong
                );

                // Thực thi
                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception('Lỗi execute: ' . $stmt->error);
                }

                $maPhong = $conn->insert_id;

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
                    'error' => $e->getMessage(),
                    'soPhong' => $soPhong ?? ''
                ];
            }
        }

        // LẤY DANH SÁCH PHÒNG
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

        // LẤY CHI TIẾT PHÒNG
        public function getChiTietPhong($maPhong)
        {
            $conn = $this->db->openConnect();

            $sql = "SELECT p.*, lp.HangPhong, lp.HinhThuc, lp.DonGia 
                    FROM Phong p 
                    JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                    WHERE p.MaPhong = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $maPhong);
            $stmt->execute();
            $result = $stmt->get_result();
            $phong = $result->fetch_assoc();

            $this->db->closeConnect($conn);
            return $phong;
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

        // LẤY THIẾT BỊ PHÒNG
        public function getThietBiPhong($maPhong)
        {
            $conn = $this->db->openConnect();

            $sql = "SELECT * FROM ThietBi WHERE MaPhong = ? ORDER BY TenThietBi";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $maPhong);
            $stmt->execute();

            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            $this->db->closeConnect($conn);
            return $data;
        }

        // SỬA THÔNG TIN PHÒNG
        public function suaPhong($maPhong, $data, $avatarFile = null, $imageFiles = null)
        {
            $conn = $this->db->openConnect();

            try {
                // Lấy thông tin phòng hiện tại
                $phongHienTai = $this->getChiTietPhong($maPhong);
                if (!$phongHienTai) {
                    throw new Exception('Không tìm thấy phòng');
                }

                $soPhong = $phongHienTai['SoPhong'];

                // Xử lý upload ảnh avatar mới
                $avatarPath = $phongHienTai['Avatar'];
                if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->uploadImageSimple($avatarFile, 'avatar', $soPhong);
                    if ($uploadResult['success']) {
                        $avatarPath = $uploadResult['file_path'];
                    }
                }

                // Xử lý upload nhiều ảnh mới
                $danhSachAnh = $phongHienTai['DanhSachPhong'];
                if ($imageFiles && !empty($imageFiles['name'][0])) {
                    $uploadedImages = $this->uploadMultipleImagesSimple($imageFiles, $soPhong);
                    if (!empty($uploadedImages)) {
                        $danhSachAnh = json_encode($uploadedImages);
                    }
                }

                // Chuẩn hóa dữ liệu
                $tang = isset($data['Tang']) ? intval($data['Tang']) : $phongHienTai['Tang'];
                $maLoaiPhong = isset($data['MaLoaiPhong']) ? intval($data['MaLoaiPhong']) : $phongHienTai['MaLoaiPhong'];
                $trangThai = isset($data['TrangThai']) ? $data['TrangThai'] : $phongHienTai['TrangThai'];
                $roomName = isset($data['roomName']) ? $data['roomName'] : $phongHienTai['roomName'];
                $giaPhong = isset($data['GiaPhong']) ? floatval($data['GiaPhong']) : $phongHienTai['GiaPhong'];

                $sql = "UPDATE Phong SET 
                        Tang = ?, MaLoaiPhong = ?, TrangThai = ?, 
                        Avatar = ?, DanhSachPhong = ?, roomName = ?, GiaPhong = ?
                        WHERE MaPhong = ?";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Lỗi prepare SQL: ' . $conn->error);
                }

                $stmt->bind_param(
                    "iissssdi",
                    $tang,
                    $maLoaiPhong,
                    $trangThai,
                    $avatarPath,
                    $danhSachAnh,
                    $roomName,
                    $giaPhong,
                    $maPhong
                );

                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception('Lỗi execute: ' . $stmt->error);
                }

                $this->db->closeConnect($conn);
                return ['success' => true, 'message' => 'Cập nhật phòng thành công!'];
            } catch (Exception $e) {
                $this->db->closeConnect($conn);
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // XÓA PHÒNG
        public function xoaPhong($maPhong)
        {
            $conn = $this->db->openConnect();

            try {
                // Xóa thiết bị trước
                $sql_thietbi = "DELETE FROM ThietBi WHERE MaPhong = ?";
                $stmt_thietbi = $conn->prepare($sql_thietbi);
                $stmt_thietbi->bind_param("i", $maPhong);
                $stmt_thietbi->execute();

                // Xóa phòng
                $sql_phong = "DELETE FROM Phong WHERE MaPhong = ?";
                $stmt_phong = $conn->prepare($sql_phong);
                $stmt_phong->bind_param("i", $maPhong);
                $result = $stmt_phong->execute();

                if (!$result) {
                    throw new Exception('Lỗi xóa phòng: ' . $stmt_phong->error);
                }

                $this->db->closeConnect($conn);
                return ['success' => true, 'message' => 'Xóa phòng thành công!'];
            } catch (Exception $e) {
                $this->db->closeConnect($conn);
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // THÊM THIẾT BỊ
        public function themThietBi($data)
        {
            $conn = $this->db->openConnect();

            try {
                $sql = "INSERT INTO ThietBi (TenThietBi, TinhTrang, MaPhong) 
                        VALUES (?, ?, ?)";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Lỗi prepare SQL: ' . $conn->error);
                }

                $stmt->bind_param(
                    "ssi",
                    $data['TenThietBi'],
                    $data['TinhTrang'],
                    $data['MaPhong']
                );

                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception('Lỗi execute: ' . $stmt->error);
                }

                $this->db->closeConnect($conn);
                return ['success' => true, 'message' => 'Thêm thiết bị thành công!'];
            } catch (Exception $e) {
                $this->db->closeConnect($conn);
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // SỬA THIẾT BỊ
        public function suaThietBi($maThietBi, $data)
        {
            $conn = $this->db->openConnect();

            try {
                $sql = "UPDATE ThietBi SET 
                        TenThietBi = ?, TinhTrang = ? 
                        WHERE MaThietBi = ?";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Lỗi prepare SQL: ' . $conn->error);
                }

                $stmt->bind_param(
                    "ssi",
                    $data['TenThietBi'],
                    $data['TinhTrang'],
                    $maThietBi
                );

                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception('Lỗi execute: ' . $stmt->error);
                }

                $this->db->closeConnect($conn);
                return ['success' => true, 'message' => 'Cập nhật thiết bị thành công!'];
            } catch (Exception $e) {
                $this->db->closeConnect($conn);
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // XÓA THIẾT BỊ
        public function xoaThietBi($maThietBi)
        {
            $conn = $this->db->openConnect();

            try {
                $sql = "DELETE FROM ThietBi WHERE MaThietBi = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Lỗi prepare SQL: ' . $conn->error);
                }

                $stmt->bind_param("i", $maThietBi);
                $result = $stmt->execute();

                if (!$result) {
                    throw new Exception('Lỗi xóa thiết bị: ' . $stmt->error);
                }

                $this->db->closeConnect($conn);
                return ['success' => true, 'message' => 'Xóa thiết bị thành công!'];
            } catch (Exception $e) {
                $this->db->closeConnect($conn);
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // THỐNG KÊ PHÒNG
        public function thongKePhong()
        {
            $conn = $this->db->openConnect();

            $sql = "SELECT 
                    COUNT(*) as tongPhong,
                    SUM(CASE WHEN TrangThai = 'Trống' THEN 1 ELSE 0 END) as tongTrong,
                    SUM(CASE WHEN TrangThai = 'Đang sử dụng' THEN 1 ELSE 0 END) as tongDangSuDung,
                    SUM(CASE WHEN TrangThai = 'Bảo trì' THEN 1 ELSE 0 END) as tongBaoTri
                    FROM Phong";

            $result = $conn->query($sql);
            $thongKe = $result->fetch_assoc();

            $this->db->closeConnect($conn);
            return $thongKe;
        }

        // KIỂM TRA SỐ PHÒNG
        public function kiemTraSoPhong($soPhong, $maPhong = '')
        {
            $conn = $this->db->openConnect();

            $sql = "SELECT COUNT(*) as count FROM Phong WHERE SoPhong = ?";
            if (!empty($maPhong)) {
                $sql .= " AND MaPhong != ?";
            }

            $stmt = $conn->prepare($sql);
            if (!empty($maPhong)) {
                $stmt->bind_param("si", $soPhong, $maPhong);
            } else {
                $stmt->bind_param("s", $soPhong);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $this->db->closeConnect($conn);
            return $row['count'] > 0;
        }
    }
    ?>