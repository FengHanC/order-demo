<?php
putenv('HTTP_PROXY=');   // 🔴 清掉环境变量
putenv('HTTPS_PROXY=');


$concurrency = 50;

$mh = curl_multi_init();
$handles = [];

for ($i = 0; $i < $concurrency; $i++) {
    $requestId = 'req_' . uniqid() . '_' . mt_rand(1000, 9999);
    $url = 'http://order.test/order/create?user_id=1&product_id=1&request_id=' . $requestId;
    echo "本次压测使用的 request_id: {$requestId}\n\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_PROXY          => '',                                // 🔴 清空代理
        CURLOPT_NOPROXY        => '127.0.0.1,localhost,order.test',  // 🔴 绕过代理
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}

$startTime = microtime(true);

$running = null;
do {
    curl_multi_exec($mh, $running);
    if ($running) curl_multi_select($mh, 1.0);
} while ($running > 0);

$duration = microtime(true) - $startTime;

$orderNoStats = [];
$msgStats = [];

foreach ($handles as $ch) {
    $response = curl_multi_getcontent($ch);
    $data = json_decode($response, true);
    if (isset($data['code']) && $data['code'] == 0) {
        $orderNo = $data['data']['order_no'];
        $msg = $data['msg'];
        $orderNoStats[$orderNo] = ($orderNoStats[$orderNo] ?? 0) + 1;
        $msgStats[$msg] = ($msgStats[$msg] ?? 0) + 1;
    }
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo "耗时: " . round($duration, 2) . " 秒\n\n";
echo "===== 返回的订单号分布 =====\n";
print_r($orderNoStats);
echo "\n===== 返回的 msg 分布 =====\n";
print_r($msgStats);