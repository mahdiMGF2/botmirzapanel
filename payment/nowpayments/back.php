<?php
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING);
$Pathfile = dirname(dirname($PHP_SELF, 2));
ini_set('error_log', 'error_log');
$Pathfiles = $rootPath.$Pathfile;
require_once $Pathfiles.'/config.php';
require_once $Pathfiles.'/functions.php';
require_once $Pathfiles.'/jdf.php';
require_once $Pathfiles.'/panels.php';
require_once $Pathfiles.'/botapi.php';
require_once $Pathfiles.'/text.php';
$ManagePanel = new ManagePanel();
$apinowpayments = select("PaySetting", "ValuePay", "NamePay", "apinowpayment","select")['ValuePay'];
$data = json_decode(file_get_contents("php://input"),true);
if(isset($data['payment_status']) && $data['payment_status'] == "finished"){
    $pay = StatusPayment($data['payment_id']);
    if($pay['payment_status'] != "finished")return;
    $Payment_report = select("Payment_report","*","id_order",$pay['order_id'],"select");
    if($Payment_report){
    if ($Payment_report['payment_Status'] == "paid")return;
    $setting = select("setting", "*");
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
    DirectPayment($Payment_report['id_order']);
    update("user","Processing_value","0", "id",$Balance_id['id']);
    update("user","Processing_value_one","0", "id",$Balance_id['id']);
    update("user","Processing_value_tow","0", "id",$Balance_id['id']);
    update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
    if (strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], sprintf($textbotlang['Admin']['Report']['nowpayment'],$Payment_report['id_user'],$Payment_report['price']), null, 'HTML');
        }
}
}
