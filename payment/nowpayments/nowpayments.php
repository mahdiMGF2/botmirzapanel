<?php
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING);
$Pathfile = dirname(dirname($PHP_SELF, 2));
$Pathfiles = $rootPath.$Pathfile;
$Pathfile = $Pathfiles.'/config.php';
$functions = $Pathfiles.'/functions.php';
require_once $functions;
require_once $Pathfile;
$apinowpayments = select("PaySetting", "ValuePay", "NamePay", "apinowpayment","select")['ValuePay'];
$amount =     htmlspecialchars($_GET['price'], ENT_QUOTES, 'UTF-8');
$invoice_id = htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8');
$order_description = htmlspecialchars($_GET['order_description'], ENT_QUOTES, 'UTF-8');
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.nowpayments.io/v1/invoice',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => array(
        'x-api-key:'.$apinowpayments,
        'Content-Type: application/json'
    ),
));
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
    'price_amount' => $amount,
    'price_currency' => 'usd',
    'order_id' => $invoice_id,
    'order_description' => $order_description,
    'success_url' => "https://".$domainhosts . '/payment/nowpayments/back.php',
    'is_fee_paid_by_user' => false
]));

$response = curl_exec($curl);
curl_close($curl);
$res = json_decode($response);
if(isset($res->status) && $res->status == false){
    echo 'An error has occurred: Erorr Code : '.$res->statusCode."</br>Error description : ".$res->message;
    return;
}
header('Location: '.$res->invoice_url);
