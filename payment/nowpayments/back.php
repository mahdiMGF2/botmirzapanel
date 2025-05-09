<?php
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING);
$Pathfile = dirname(dirname($PHP_SELF, 2));
ini_set('error_log', 'error_log');
$Pathfiles = $rootPath.$Pathfile;
require_once $Pathfiles.'/config.php';
require_once $Pathfiles.'/functions.php';
require_once $Pathfiles.'/jdf.php';
require_once $Pathfiles.'/botapi.php';
require_once $Pathfiles.'/text.php';
$apinowpayments = select("PaySetting", "ValuePay", "NamePay", "apinowpayment","select")['ValuePay'];
$NP_id = htmlspecialchars($_GET['NP_id'], ENT_QUOTES, 'UTF-8');
$price_rate = tronratee();
$usd = $price_rate['result']['USD'];
if(isset($NP_id)){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/'.$NP_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'x-api-key:'.$apinowpayments
        ),
    ));
    $response = curl_exec($curl);
    $response = json_decode($response,true);
    curl_close($curl);
}
if($response['payment_status'] == "finished"){
    $setting = select("setting", "*");
    $payment_status = $textbotlang['users']['moeny']['payment_success'];
    $price = intval($usd*$response['price_amount']);
    $dec_payment_status = $textbotlang['users']['moeny']['payment_success_dec'];
    $Payment_report = select("Payment_report", "*", "id_order", $response['order_id'],"select");
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
    if($Payment_report['payment_Status'] != "paid"){
        DirectPayment($Payment_report['id_order']);
        update("user","Processing_value","0", "id",$Balance_id['id']);
        update("user","Processing_value_one","0", "id",$Balance_id['id']);
        update("user","Processing_value_tow","0", "id",$Balance_id['id']);
        update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
        if (strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], sprintf($textbotlang['Admin']['Report']['nowpayment'],$Payment_report['id_user'],$price), null, 'HTML');
        }
    }
}
else{
    $payment_status = $textbotlang['users']['moeny']['payment_failed'];
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
            font-family:vazir;
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
    <p><?php echo $textbotlang['users']['moeny']['transaction_number']; ?><span><?php echo $Payment_report['id_order']?></span></p>
    <p><?php echo $textbotlang['users']['moeny']['payment_amount']; ?> <span><?php echo $price; ?></span><?php echo $textbotlang['users']['moeny']['currency']; ?></p>
    <p><?php echo $textbotlang['users']['moeny']['date_label']; ?> <span><?php echo jdate('Y/m/d') ?></span></p>
    <p><?php echo $dec_payment_status ?></p>
    <a class="btn" href="https://t.me/<?php echo $usernamebot ?>"><?php echo $textbotlang['users']['moeny']['back_to_bot']; ?></a>
</div>
</body>
</html>
