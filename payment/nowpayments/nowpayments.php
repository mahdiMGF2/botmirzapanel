<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$Pathfile = dirname(dirname($_SERVER['PHP_SELF'], 2));
$Pathfile = $rootPath.$Pathfile;
$Pathfile = $Pathfile.'/config.php';
require_once $Pathfile;
$apinowpayments = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'apinowpayment'"))['ValuePay'];
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
            'price_amount' => $_GET['price'],
            'price_currency' => 'usd',
            'order_id' => $_GET['order_id'],
            'order_description' => $_GET['order_description'],
            'success_url' => "https://".$domainhosts . '/payment/nowpayments/back.php',
            'is_fee_paid_by_user' => true
        ]));

$response = curl_exec($curl);
curl_close($curl);
$res = json_decode($response);
if(isset($res->status) && $res->status == false){
    echo 'An error has occurred: Erorr Code : '.$res->statusCode."</br>Error description : ".$res->message;
    return;
    }
 header('Location: '.$res->invoice_url);
