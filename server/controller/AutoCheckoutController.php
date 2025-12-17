<?php
// Cho phép truy cập từ client (CORS)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../model/AutoCheckoutModel.php';

class AutoCheckoutController {
    private $model;
    private $lock_file;
    
    public function __construct() {
        $this->model = new AutoCheckoutModel();
        $this->lock_file = __DIR__ . '/../temp/auto_checkout.lock';
        
        // Tạo thư mục temp nếu chưa có
        if (!is_dir(dirname($this->lock_file))) {
            mkdir(dirname($this->lock_file), 0755, true);
        }
    }
    
    /**
     * Kiểm tra và tạo lock
     */
    private function checkLock() {
        if (file_exists($this->lock_file)) {
            $lock_time = time() - filemtime($this->lock_file);
            // Lock trong 5 phút
            if ($lock_time < 300) {
                return [
                    'success' => false,
                    'message' => 'Hệ thống đang xử lý, vui lòng đợi ' . (300 - $lock_time) . ' giây',
                    'locked' => true
                ];
            }
        }
        
        // Tạo lock mới
        file_put_contents($this->lock_file, date('Y-m-d H:i:s'));
        return null;
    }
    
    /**
     * Xóa lock
     */
    private function clearLock() {
        if (file_exists($this->lock_file)) {
            unlink($this->lock_file);
        }
    }
    
    /**
     * Chạy tự động cập nhật
     */
    public function runAutoUpdate() {
        // Kiểm tra lock
        $lock_check = $this->checkLock();
        if ($lock_check !== null) {
            return $lock_check;
        }
        
        try {
            $result = $this->model->autoUpdateStatus();
            
            // Xóa lock
            $this->clearLock();
            
            if ($result === false) {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xử lý database'
                ];
            }
            
            return [
                'success' => true,
                'data' => $result,
                'message' => sprintf(
                    'Đã xử lý %d hóa đơn, cập nhật %d phòng và %d khách hàng',
                    $result['total_invoices'],
                    $result['updated_rooms'],
                    $result['updated_customers']
                )
            ];
            
        } catch (Exception $e) {
            // Xóa lock nếu có lỗi
            $this->clearLock();
            
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * API endpoint cho JavaScript gọi
     */
    public function apiRun() {
        // Chỉ chạy 20% request để tránh overload
        if (rand(1, 100) > 20) {
            echo json_encode([
                'success' => true,
                'message' => 'Không có gì để cập nhật',
                'skipped' => true,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit;
        }
        
        $result = $this->runAutoUpdate();
        echo json_encode($result);
        exit;
    }
}

// ========== TỰ ĐỘNG CHẠY KHI TRUY CẬP FILE NÀY ==========
if (basename($_SERVER['PHP_SELF']) == 'AutoCheckoutController.php') {
    $controller = new AutoCheckoutController();
    $controller->apiRun();
}
?>