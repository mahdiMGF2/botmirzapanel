<?php
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING);
ini_set('error_log', 'error_log');
$Pathfile = dirname(dirname($PHP_SELF, 2));
$Pathfiles = $rootPath.$Pathfile;
require_once $Pathfiles.'/config.php';
require_once $Pathfiles.'/functions.php';
require_once $Pathfiles.'/text.php';
$amount =     htmlspecialchars($_GET['price'], ENT_QUOTES, 'UTF-8');;
$invoice_id = htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8');;
$PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht","select")['ValuePay'];
$checkprice = select("Payment_report", "price", "id_order", $invoice_id,"select")['price'];
// Send Parameter
if($checkprice !=$amount){
    echo $textbotlang['users']['moeny']['invalidprice'];
    return;
}
$data = [
    'ApiKey'    => $PaySetting,
    'Amount'    => $amount *10,
    'CallbackURL' => "https://".$domainhosts."/payment/aqayepardakht/back.php",
    'Hash_id' => $invoice_id,
];

$data = json_encode($data);
$ch = curl_init('https://tetra98.ir/api/create_order');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
);
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode($result);
if ($result->status == "100") {
    header('Location: https://tetra98.ir/pay/' . $result->Authority);
} else {
    $status_pay = [
        '-1' => "amount نمی تواند خالی باشد",
        '-2' => "کد پین درگاه نمی تواند خالی باشد",
        '-3' => "callback نمی تواند خالی باشد",
        '-4' => "amount باید عددی باشد",
        '-5' => "amount باید بین 1,000 تا 100,000,000 تومان باشد",
        '-6' => "کد پین درگاه اشتباه هست",
        '-7' => "transid نمی تواند خالی باشد",
        '-8' => "تراکنش مورد نظر وجود ندارد",
        '-9' => "کد پین درگاه با درگاه تراکنش مطابقت ندارد",
        '-10' => "مبلغ با مبلغ تراکنش مطابقت ندارد",
        '-11' => "درگاه درانتظار تایید و یا غیر فعال است",
        '-12' => "امکان ارسال درخواست برای این پذیرنده وجود ندارد",
        '-13' => "شماره کارت باید 16 رقم چسبیده بهم باشد",
        '-14' => "درگاه برروی سایت دیگری درحال استفاده است"

    ][$result->code];
    echo $status_pay;
}
