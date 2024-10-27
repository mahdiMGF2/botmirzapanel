<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');
require_once '../config.php';
require_once '../botapi.php';
require_once '../panels.php';
require_once '../functions.php';
$ManagePanel = new ManagePanel();

// buy service 
$stmt = $pdo->prepare("SELECT * FROM invoice WHERE (status = 'active' OR status = 'end_of_time') AND name_product != 'usertest' ORDER BY RAND() LIMIT 5");
$stmt->execute();
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $line  = trim($result['username']);
    $resultss = $result;
    $marzban_list_get = select("marzban_panel","*","name_panel",$resultss['Service_location'],"select");
    $get_username_Check = $ManagePanel->DataUser($resultss['Service_location'],$resultss['username']);
    if($get_username_Check){
        if($get_username_Check['status'] == "Unsuccessful")continue;
        $output =  $get_username_Check['data_limit'] - $get_username_Check['used_traffic'];
        $textservice = select("textbot","text","id_text","text_Purchased_services","select")['text'];
        $RemainingVolume = formatBytes($output);
        $Response = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "💊 تمدید سرویس", 'callback_data' => 'extend_' . $resultss['username']],
                ],
            ]
        ]);
        $bytewarn = 1  * pow(1024, 3);
        if ($output <= $bytewarn && $output > 0 && $get_username_Check['status'] == "active") {
            $text = "با سلام خدمت شما کاربر گرامی 👋
🚨 از حجم سرویس $line تنها $RemainingVolume باقی مانده است. لطفاً در صورت تمایل برای خرید حجم اضافه و یا تمدید سرویستون از طریق بخش «{$textservice}» اقدام بفرمایین";
            sendmessage($resultss['id_user'], $text, $Response, 'HTML');
            if($resultss['Status'] === "end_of_time"){
                update("invoice","Status","sendedwarn", "username",$line);
            }else{
                update("invoice","Status","end_of_volume", "username",$line);
            }
        }
    }
}