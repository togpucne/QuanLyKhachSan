<?php
require_once __DIR__ . '/../model/Room.model.php';


class RoomController {
    private $roomModel;

    public function __construct() {
        $this->roomModel = new RoomModel();
    }

    public function showHomePage() {
        // Lấy từ khóa tìm kiếm (nếu có)
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Lấy dữ liệu từ Model (theo từ khóa)
        $rooms = $this->roomModel->getAllRooms($search);
        $roomCounts = $this->roomModel->countRoomsByStatus();

        // Truyền dữ liệu sang View
        include __DIR__ . '/../view/home/index.php';
    }
}

$controller = new RoomController();
$controller->showHomePage();
?>
