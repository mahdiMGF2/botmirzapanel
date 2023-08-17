<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$Pathfile = dirname(dirname($_SERVER['PHP_SELF'], 2));
$Pathfiles = $rootPath.$Pathfile;
$Pathfile = $Pathfiles.'/config.php';
$jdf = $Pathfiles.'/jdf.php';
$botapi = $Pathfiles.'/botapi.php';
require_once $Pathfile;
require_once $jdf;
require_once $botapi;
$invoice_id = htmlspecialchars($_POST['invoice_id'], ENT_QUOTES, 'UTF-8');
$PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'merchant_id_aqayepardakht'"))['ValuePay'];
$Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$invoice_id' LIMIT 1"))['price'];

// verify Transaction

$data = [
'pin'    => $PaySetting,
'amount'    => $Payment_report,
'transid' => $_POST['transid'],
];
$data = json_encode($data);
$ch = curl_init('https://panel.aqayepardakht.ir/api/v2/verify');
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
if ($result->code == "1") {
    $payment_status = "پرداخت موفق";
    $price = $Payment_report;
    $dec_payment_status = "از انجام تراکنش متشکریم!";
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$invoice_id' LIMIT 1"));
    if($Payment_report['payment_Status'] != "paid"){
    $Balance_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$Payment_report['id_user']}' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_confrim = intval($Balance_id['Balance']) + $price;
    $stmt->bind_param("ss", $Balance_confrim, $Payment_report['id_user']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "paid";
    $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
    $stmt->execute();
    sendmessage($Payment_report['id_user'],"💎 کاربر گرامی مبلغ $price تومان به کیف پول شما واریز گردید با تشکر از پرداخت شما.
    
    🛒 کد پیگیری شما: {$Payment_report['id_order']}",null,'HTML');
    $setting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
$text_report = "💵 پرداخت جدید
        
آیدی عددی کاربر : $from_id
مبلغ تراکنش $price
روش پرداخت :  درگاه آقای پرداخت";
    if (strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
}
}else {
        $payment_status = [
        '0' => "پرداخت انجام نشد",
        '2' => "تراکنش قبلا وریفای و پرداخت شده است",

    ][$result->code];
     $dec_payment_status = "";
}
?>
<html>
<head>
    <title>فاکتور پرداخت</title>
    <style>
    @font-face {
    font-family: 'vazir';
    src: url('/Vazir.eot');
    src: local('☺'), url('../fonts/Vazir.woff') format('woff'), url('../fonts/Vazir.ttf') format('truetype');
}

        body {
            font-family:vazir;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .confirmation-box {
            background-color: #ffffff;
            border-radius: 8px;
            width:25%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #333333;
            margin-bottom: 20px;
        }

        p {
            color: #666666;
            margin-bottom: 10px;
        }
        .btn{
            display:block;
            margin : 10px 0;
            padding:10px 20px;
            background-color:#49b200;
            color:#fff;
            text-decoration :none;
            border-radius:10px;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h1><?php echo $payment_status ?></h1>
        <p>شماره تراکنش:<span><?php echo $invoice_id ?></span></p>
        <p>مبلغ پرداختی:  <span><?php echo  $Payment_report; ?></span>تومان</p>
        <p>تاریخ: <span>  <?php echo jdate('Y/m/d')  ?>  </span></p>
        <p><?php echo $dec_payment_status ?></p>
        <a class = "btn" href = "https://t.me/<?php echo $usernamebot ?>">بازگشت به ربات</a>
    </div>
</body>
</html>
