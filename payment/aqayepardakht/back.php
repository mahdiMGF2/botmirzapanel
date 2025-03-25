<?php
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF');
$Pathfile = dirname(dirname($PHP_SELF, 2));
$Pathfiles = $rootPath.$Pathfile;
require_once $Pathfiles.'/config.php';
require_once $Pathfiles.'/jdf.php';
require_once $Pathfiles.'/botapi.php';
require_once $Pathfiles.'/functions.php';
require_once $Pathfiles.'/panels.php';
require_once $Pathfiles.'/text.php';
$invoice_id = htmlspecialchars($_POST['invoice_id'], ENT_QUOTES, 'UTF-8');
$PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht","select")['ValuePay'];
$price = select("Payment_report", "price", "id_order", $invoice_id,"select")['price'];
$ManagePanel = new ManagePanel();

// verify Transaction

$data = [
    'pin'    => $PaySetting,
    'amount'    => $price,
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
    $setting = select("setting", "*");
    $payment_status = $textbotlang['users']['moeny']['payment_success'];
    $dec_payment_status = $textbotlang['users']['moeny']['payment_success_dec'];
    $Payment_report = select("Payment_report", "*", "id_order", $invoice_id,"select");
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
    if($Payment_report['payment_Status'] != "paid"){
        DirectPayment($Payment_report['id_order']);
        update("user","Processing_value","0", "id",$Balance_id['id']);
        update("user","Processing_value_one","0", "id",$Balance_id['id']);
        update("user","Processing_value_tow","0", "id",$Balance_id['id']);
        update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
        if (strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], sprintf($textbotlang['Admin']['Report']['aqayepardakht'],$Payment_report['id_user'],$price), null, 'HTML');
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
    <title><?php echo $textbotlang['users']['moeny']['invoice_title']; ?></title>
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
            direction: rtl;
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
    <p><?php echo $textbotlang['users']['moeny']['transaction_number']; ?><span><?php echo $invoice_id ?></span></p>
    <p><?php echo $textbotlang['users']['moeny']['payment_amount']; ?> <span><?php echo $price; ?></span><?php echo $textbotlang['users']['moeny']['currency']; ?></p>
    <p><?php echo $textbotlang['users']['moeny']['date_label']; ?> <span><?php echo jdate('Y/m/d') ?></span></p>
    <p><?php echo $dec_payment_status ?></p>
    <a class="btn" href="https://t.me/<?php echo $usernamebot ?>"><?php echo $textbotlang['users']['moeny']['back_to_bot']; ?></a>
</div>
</body>
</html>