<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$Pathfile = dirname(dirname($_SERVER['PHP_SELF'], 2));
$Pathfiles = $rootPath.$Pathfile;
$Pathfile = $Pathfiles.'/config.php';
require_once $Pathfile;
$amount = $_GET['price'];
$invoice_id = $_GET['order_id'];
$PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'merchant_id_aqayepardakht'"))['ValuePay'];
$checkprice = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (price) FROM Payment_report WHERE id_order = '$invoice_id'"))['price'];
// Send Parameter
if($checkprice !=$amount){
    echo "مبلغ ارسال شده نامعتبر است";
    return;
}
$data = [
'pin'    => $PaySetting,
'amount'    => $amount,
'callback' => $domainhosts."/payment/aqayepardakht/back.php",
'invoice_id' => $invoice_id,
];

$data = json_encode($data);
$ch = curl_init('https://panel.aqayepardakht.ir/api/v2/create');
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
if ($result->status == "success") {
    header('Location: https://panel.aqayepardakht.ir/startpay/' . $result->transid);
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

