<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');
require_once '../config.php';
require_once '../botapi.php';
require_once '../panels.php';
require_once '../functions.php';
require_once '../jdf.php';
require_once '../text.php';
require '../vendor/autoload.php';
$ManagePanel = new ManagePanel();
$setting = select("setting", "*");
$datatextbotget = select("textbot", "*",null ,null ,"fetchAll");
$datatxtbot = array();
foreach ($datatextbotget as $row) {
    $datatxtbot[] = array(
        'id_text' => $row['id_text'],
        'text' => $row['text']
    );
}
$datatextbot = array(
    'textafterpay' => '',
    'textaftertext' => '',
    'textmanual' => '',
    'textselectlocation' => ''
);
foreach ($datatxtbot as $item) {
    if (isset($datatextbot[$item['id_text']])) {
        $datatextbot[$item['id_text']] = $item['text'];
    }
}
$stmt = $pdo->prepare("SELECT * FROM Payment_report WHERE payment_Status = 'waiting' AND Payment_Method = 'cart to cart'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $since_start = time() - strtotime($row['time']);
    if ($since_start >=3600)continue;
    $Payment_report = select("Payment_report","*","id_order",$row['id_order'],"select");
    $Balance_id = select("user","*","id",$Payment_report['id_user'],"select");
    if ($Payment_report['payment_Status'] == "paid") {
        continue;
    }
    update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
    update("Payment_report","dec_not_confirmed","Confirmed by robot","id_order",$Payment_report['id_order']);
    DirectPayment($Payment_report['id_order'],"../images.jpg");
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage',[
            'chat_id' => $setting['Channel_Report'],
            'text' => sprintf($textbotlang['Admin']['Report']['autocart'],$Balance_id['id'],$Payment_report['price']),
            'parse_mode' => "HTML"
        ]);
    }
}