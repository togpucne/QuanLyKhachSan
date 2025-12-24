<?php
// client/controller/xulythanhtoanmomo_atm.php
session_start();

// Lấy thông tin từ GET parameters
$amount = $_GET['amount'] ?? 0;
$bookingCode = $_GET['bookingCode'] ?? '';
$maHoaDon = $_GET['maHoaDon'] ?? 0;

// KIỂM TRA VÀ CHUẨN HÓA SỐ TIỀN
$amount = (int)$amount;

// Momo yêu cầu: TỐI THIỂU 10,000 VND và TỐI ĐA 50,000,000 VND
if ($amount < 10000) {
    $amount = 10000;
}

if ($amount > 50000000) {
    $amount = 50000000;
}

// Kiểm tra dữ liệu
if (!$amount || $amount <= 0 || !$bookingCode || !$maHoaDon) {
    header('Location: ../controller/momo_callback.php?action=error&message=Thông+tin+không+hợp+lệ');
    exit;
}

function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

// Thông tin test của Momo
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

// Tạo thông tin đơn hàng
$orderInfo = "Thanh toán đặt phòng " . $bookingCode;
$orderId = time() . rand(1000, 9999);
$redirectUrl = "http://localhost/ABC-Resort/client/controller/momo_callback.php?action=success&maHoaDon=" . $maHoaDon . "&bookingCode=" . urlencode($bookingCode) . "&amount=" . $amount;
$ipnUrl = "http://localhost/ABC-Resort/client/controller/momo_callback.php?action=ipn";
$extraData = "";
$requestId = time() . rand(1000, 9999);
$requestType = "payWithATM";

// Tạo raw hash
$rawHash = "accessKey=" . $accessKey . 
           "&amount=" . $amount . 
           "&extraData=" . $extraData . 
           "&ipnUrl=" . $ipnUrl . 
           "&orderId=" . $orderId . 
           "&orderInfo=" . $orderInfo . 
           "&partnerCode=" . $partnerCode . 
           "&redirectUrl=" . $redirectUrl . 
           "&requestId=" . $requestId . 
           "&requestType=" . $requestType;

$signature = hash_hmac("sha256", $rawHash, $secretKey);

// Tạo data array
$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "ABC Resort",
    'storeId' => "ABC_Resort_Store",
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
);

// Gửi request đến Momo
$result = execPostRequest($endpoint, json_encode($data));
$jsonResult = json_decode($result, true);

if ($jsonResult && isset($jsonResult['payUrl'])) {
    // Chuyển hướng ngay đến Momo
    header("Location: " . $jsonResult['payUrl']);
} else {
    $errorMsg = isset($jsonResult['message']) ? $jsonResult['message'] : 'Không thể tạo liên kết thanh toán';
    header('Location: ../controller/momo_callback.php?action=error&message=' . urlencode($errorMsg));
}
exit;
?>