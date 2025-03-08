<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once "WxPay.Api.php";
require_once "WxPay.Data.php";


$verifyToken = '@321.ufgnaw';
if($verifyToken != $_POST['token']) {
    echo 'token验证失败';
    exit();
}
// 获取支付金额
$amount='';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $amount=$_POST['total'];
}else{
    $amount=$_GET['total'];
}
$total = floatval($amount);
$total = round($total*100); // 将元转成分
if(empty($total)){
    $total = 100;
}

// 商品名称
$subject = $_POST['subject'];
// 订单号，示例代码使用时间值作为唯一的订单ID号
$out_trade_no = $_POST['order_no'];

$unifiedOrder = new WxPayUnifiedOrder();
$unifiedOrder->SetBody($subject);//商品或支付单简要描述
$unifiedOrder->SetOut_trade_no($out_trade_no);
$unifiedOrder->SetTotal_fee($total);
$unifiedOrder->SetTrade_type("APP");
$result = WxPayApi::unifiedOrder($unifiedOrder);
if (is_array($result)) {
    ob_clean();
    echo json_encode($result);
    ob_end_flush();
}

?>