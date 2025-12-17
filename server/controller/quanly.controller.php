<?php
require_once '../../model/quanly.model.php';

class QuanLyController {
    private $model;
    
    public function __construct() {
        $this->model = new QuanLyModel();
    }
    
    public function getThongKeTongQuan() {
        return $this->model->getThongKeTongQuan();
    }
    
    public function getChartDataJson() {
        return $this->model->getChartDataJson();
    }
    
    public function renderView() {
        $data = $this->getThongKeTongQuan();
        $chartData = $this->getChartDataJson();
        
        // Truyền cả dữ liệu và chart data vào view
        require_once '../server/view/quanly/index.php';
    }
}
?>