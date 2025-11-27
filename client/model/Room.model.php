<?php
require_once __DIR__ . '/connectDB.php';

class RoomModel
{
    private $connect;

    public function __construct()
    {
        $this->connect = new Connect();
    }

    public function getAllRooms($search = '')
    {
        $conn = $this->connect->openConnect();

        $sql = "SELECT p.MaPhong, p.SoPhong, p.Tang, p.TrangThai, p.roomName,
                   p.GiaPhong, p.TongGia,  -- THÊM TONG GIA
                   lp.MaLoaiPhong, lp.HangPhong, lp.HinhThuc, lp.DonGia, 
                   p.Avatar, p.DanhSachPhong
            FROM phong p
            INNER JOIN loaiphong lp ON p.MaLoaiPhong = lp.MaLoaiPhong";

        // Nếu có từ khóa tìm kiếm
        if (!empty($search)) {
            $search = "%{$search}%";
            $sql .= " WHERE p.roomName LIKE ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $search);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql .= " ORDER BY p.TrangThai, p.SoPhong";
            $result = $conn->query($sql);
        }

        $rooms = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rooms[] = $row;
            }
            $result->free();
        }

        $this->connect->closeConnect($conn);
        return $rooms;
    }


    // Lấy thiết bị của phòng - ĐÃ SỬA LỖI VÒNG LẶP
    public function getRoomEquipment($maPhong)
    {
        // KIỂM TRA MA PHONG HỢP LỆ
        if (empty($maPhong) || !is_numeric($maPhong)) {
            return [];
        }

        $conn = $this->connect->openConnect();

        $sql = "SELECT * FROM thietbi WHERE MaPhong = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $this->connect->closeConnect($conn);
            return [];
        }

        $stmt->bind_param("i", $maPhong);
        $stmt->execute();
        $result = $stmt->get_result();

        $equipment = [];
        while ($row = $result->fetch_assoc()) {
            $equipment[] = $row;
        }

        $stmt->close();
        $this->connect->closeConnect($conn);

        return $equipment;
    }

    // Đếm số phòng theo trạng thái
    public function countRoomsByStatus()
    {
        $conn = $this->connect->openConnect();

        $sql = "SELECT TrangThai, COUNT(*) as count FROM phong GROUP BY TrangThai";
        $result = $conn->query($sql);
        $counts = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $counts[$row['TrangThai']] = $row['count'];
            }
            $result->free();
        }

        $this->connect->closeConnect($conn);
        return $counts;
    }
}
