<?php
$txn_id = htmlspecialchars($_POST['txn_id'], ENT_QUOTES, 'UTF-8');
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING);
$Pathfile = dirname(dirname($PHP_SELF, 2));
$Pathfiles = $rootPath . $Pathfile;
$Pathfile = $Pathfiles . '/config.php';
$jdf = $Pathfiles . '/jdf.php';
$botapi = $Pathfiles . '/botapi.php';
$functions = $Pathfiles . '/functions.php';
require_once $functions;
require_once $Pathfile;
require_once $jdf;
require_once $botapi;
$apiplisio = select("PaySetting", "ValuePay", "NamePay", "apiplisio", "select")['ValuePay'];
$txn_id = htmlspecialchars($_POST['txn_id'], ENT_QUOTES, 'UTF-8');
$usd = 1;
if (isset($txn_id)) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://plisio.net/api/v1/operations/' . $txn_id . "?api_key=" . $apiplisio,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'x-api-key:' . $apiplisio
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response, true);
}

// pending, completed, error, new, expired, mismatch, cancelled
if ($response['data']['status'] == "completed") {
    $payment_status = "پرداخت موفق";
    $price = number_format($usd * $_POST['source_amount'], 2);
    $dec_payment_status = "از انجام تراکنش متشکریم!";
    $Payment_report = select("Payment_report", "*", "id_order", $_POST['order_number'], "select");
    if ($Payment_report['payment_Status'] != "paid") {
        $Balance_id = select("user", "Balance", "id", $Payment_report['id_user'], "select");
        $Balance_confrim = number_format($Balance_id['Balance'] + $price, 2);
        update("user", "Balance", $Balance_confrim, "id", $Payment_report['id_user']);
        update("Payment_report", "payment_Status", "paid", "id_order", $Payment_report['id_order']);
        sendmessage($Payment_report['id_user'], "💎 کاربر گرامی مبلغ $price USD به کیف پول شما واریز گردید با تشکر از پرداخت شما.
    
    🛒 کد پیگیری شما: {$Payment_report['id_order']}", $keyboard, 'HTML');
        $setting = select("setting", "*");
        $text_report = "💵 پرداخت جدید
        
آیدی عددی کاربر : {$Payment_report['id_user']}
مبلغ تراکنش $price
روش پرداخت :  درگاه آقای پرداخت";
        if (strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
        }
    }
} else {
    $payment_status = "پرداخت ناموفق بوده است";
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
            font-family: vazir;
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
            width: 25%;
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

        .btn {
            display: block;
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #49b200;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="confirmation-box">
    <h1><?php echo $payment_status ?></h1>
    <p>شماره تراکنش:<span><?php echo $txn_id ?></span></p>
    <p>مبلغ پرداختی: <span><?php echo $response['price_amount'] ?></span> USD</p>
    <p>تاریخ: <span>  <?php echo jdate('Y/m/d') ?>  </span></p>
    <p><?php echo $dec_payment_status ?></p>
    <a class="btn" href="https://t.me/<?php echo $usernamebot ?>">بازگشت به ربات</a>
</div>
</body>
</html>
