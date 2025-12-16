<?php
// client/controller/xulythanhtoanmomo_atm.php - FIXED VERSION
session_start();

// Debug log
error_log("=== MOMO PAYMENT START ===");

// Lấy thông tin từ GET parameters
$amount = $_GET['amount'] ?? 0;
$bookingCode = $_GET['bookingCode'] ?? '';
$maHoaDon = $_GET['maHoaDon'] ?? 0;

// DEBUG CHI TIẾT
echo "<h2>Debug Momo Payment</h2>";
echo "<p><strong>Amount from URL:</strong> " . htmlspecialchars($_GET['amount'] ?? 'N/A') . "</p>";
echo "<p><strong>Amount type:</strong> " . gettype($_GET['amount']) . "</p>";
echo "<p><strong>Booking Code:</strong> " . htmlspecialchars($bookingCode) . "</p>";
echo "<p><strong>Ma Hoa Don:</strong> " . htmlspecialchars($maHoaDon) . "</p>";

error_log("Amount from GET (raw): " . ($_GET['amount'] ?? 'empty'));
error_log("Amount: $amount");
error_log("Booking Code: $bookingCode");
error_log("Ma Hoa Don: $maHoaDon");

// KIỂM TRA VÀ CHUẨN HÓA SỐ TIỀN
$amount = (int)$amount; // Đảm bảo là số nguyên

echo "<p><strong>Amount after intval:</strong> " . number_format($amount) . " VND</p>";

// Momo yêu cầu: TỐI THIỂU 10,000 VND và TỐI ĐA 50,000,000 VND
if ($amount < 10000) {
    error_log("ERROR: Amount too small ($amount). Minimum is 10000");
    echo "<p style='color: red'>❌ Lỗi: Số tiền quá nhỏ (" . number_format($amount) . " VND). Tối thiểu 10,000 VND</p>";
    // Đặt tối thiểu 10000 VND cho test
    $amount = 10000;
    echo "<p>Đã điều chỉnh thành: " . number_format($amount) . " VND</p>";
}

if ($amount > 50000000) {
    error_log("ERROR: Amount too large ($amount). Maximum is 50000000");
    echo "<p style='color: red'>❌ Lỗi: Số tiền quá lớn (" . number_format($amount) . " VND). Tối đa 50,000,000 VND</p>";
    $amount = 50000000;
    echo "<p>Đã điều chỉnh thành: " . number_format($amount) . " VND</p>";
}

// Kiểm tra dữ liệu
if (!$amount || $amount <= 0) {
    error_log("ERROR: Invalid amount after adjustment: $amount");
    echo "<p style='color: red'>❌ Lỗi: Số tiền không hợp lệ sau điều chỉnh: $amount</p>";
    header('Location: ../controller/momo_callback.php?action=error&message=Số+tiền+không+hợp+lệ');
    exit;
}

echo "<hr><h3>Kiểm tra số tiền với Momo:</h3>";
echo "<p>Số tiền gửi đến Momo: <strong>" . number_format($amount) . " VND</strong></p>";
echo "<p>✅ Đạt tối thiểu 10,000 VND: " . ($amount >= 10000 ? "CÓ" : "KHÔNG") . "</p>";
echo "<p>✅ Không vượt tối đa 50,000,000 VND: " . ($amount <= 50000000 ? "CÓ" : "KHÔNG") . "</p>";

function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        )
    );
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

// Tạo raw hash - ĐÚNG THỨ TỰ
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

echo "<h3>Raw Hash String:</h3>";
echo "<pre>" . htmlspecialchars($rawHash) . "</pre>";

error_log("Raw Hash String: $rawHash");

$signature = hash_hmac("sha256", $rawHash, $secretKey);
echo "<p><strong>Signature:</strong> $signature</p>";
error_log("Signature: $signature");

// Tạo data array
$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "ABC Resort",
    'storeId' => "ABC_Resort_Store",
    'requestId' => $requestId,
    'amount' => $amount, // Đã là số nguyên
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
);

echo "<h3>Data sent to Momo:</h3>";
echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

error_log("Data to Momo: " . json_encode($data));

echo "<hr><h3>Gửi request đến Momo...</h3>";

// Gửi request đến Momo
$result = execPostRequest($endpoint, json_encode($data));
error_log("Momo Response Raw: $result");

echo "<h3>Response from Momo:</h3>";
$jsonResult = json_decode($result, true);

if ($jsonResult) {
    echo "<pre>" . htmlspecialchars(json_encode($jsonResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    
    if (isset($jsonResult['payUrl'])) {
        error_log("Success! Redirect to: " . $jsonResult['payUrl']);
        echo "<p style='color: green; font-weight: bold'>✅ Thành công! Chuyển hướng đến Momo...</p>";
        echo "<p><a href='" . $jsonResult['payUrl'] . "' target='_blank'>Mở trang thanh toán Momo</a></p>";
        
        // Tự động chuyển hướng sau 3 giây
        echo "<script>
            setTimeout(function() {
                window.location.href = '" . $jsonResult['payUrl'] . "';
            }, 3000);
        </script>";
    } else {
        $errorMsg = isset($jsonResult['message']) ? $jsonResult['message'] : 'Không thể tạo liên kết thanh toán';
        error_log("Momo Error: $errorMsg");
        echo "<p style='color: red; font-weight: bold'>❌ Lỗi: " . htmlspecialchars($errorMsg) . "</p>";
        
        // Hiển thị thêm thông tin debug
        echo "<h4>Debug Info:</h4>";
        echo "<p>Amount sent: " . number_format($amount) . " VND</p>";
        echo "<p>Order Info: " . htmlspecialchars($orderInfo) . "</p>";
    }
} else {
    echo "<p style='color: red'>Không nhận được phản hồi từ Momo hoặc JSON không hợp lệ</p>";
    echo "<p>Raw response: " . htmlspecialchars($result) . "</p>";
}

echo "<hr><h3>Test Links:</h3>";
echo '<a href="xulythanhtoanmomo_atm.php?amount=995500&bookingCode=TEST123&maHoaDon=1" class="btn btn-primary">Test với 995,500 VND</a><br><br>';
echo '<a href="xulythanhtoanmomo_atm.php?amount=10000&bookingCode=TEST123&maHoaDon=1" class="btn btn-secondary">Test với 10,000 VND</a>';

exit;
?>