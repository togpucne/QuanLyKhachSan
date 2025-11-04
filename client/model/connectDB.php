<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
class Connect {
    public function openConnect() {
        $host = "localhost"; 
        $user = "admin1"; 
        $pass = "admin1"; 
        $db   = "ptud"; 
        $port = 3307;   

        $conn = mysqli_connect($host, $user, $pass, $db, $port);

        if (!$conn) {
            die("Kết nối thất bại: " . mysqli_connect_error());
        } 
        
        // THÊM DÒNG NÀY - QUAN TRỌNG!
        mysqli_set_charset($conn, 'utf8mb4');
        
        return $conn;
    }

    public function closeConnect($conn) {
        if ($conn) {
            $conn->close();
        }
    }
}
?>