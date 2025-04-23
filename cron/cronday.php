<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');
require_once '../config.php';
require_once '../botapi.php';
require_once '../panels.php';
require_once '../functions.php';
require_once '../text.php';
$ManagePanel = new ManagePanel();


// buy service 
$stmt = $pdo->prepare("SELECT * FROM invoice WHERE (status = 'active' OR status = 'end_of_volume') AND name_product != 'usertest' ORDER BY RAND() LIMIT 5");
$stmt->execute();
    while ($resultss = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $marzban_list_get = select("marzban_panel","*","name_panel",$resultss['Service_location'],"select");
        if($marzban_list_get == false)continue;
        $get_username_Check = $ManagePanel->DataUser($resultss['Service_location'],$resultss['username']);
        if($get_username_Check['status'] != "Unsuccessful"){
        if(in_array($get_username_Check['status'],['active','on_hold'])){
        $timeservice = $get_username_Check['expire'] - time();
        $day = floor($timeservice / 86400)+1;
        $output =  $get_username_Check['data_limit'] - $get_username_Check['used_traffic'];
        $textservice = select("textbot","text","id_text","text_Purchased_services","select")['text'];
        $RemainingVolume = formatBytes($output);
        $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['extend']['title'], 'callback_data' => 'extend_' . $resultss['username']],
            ],
        ]
    ]);
        if ($timeservice <= "167000" && $timeservice > 0) {
            sendmessage($resultss['id_user'], sprintf($textbotlang['users']['cron']['cronday'],$resultss['username'],$day,$textservice), $Response, 'HTML');
            if($resultss['Status'] === "end_of_volume"){
                update("invoice","Status","sendedwarn", "username",$resultss['username']);    
                }else{
            update("invoice","Status","end_of_time", "username",$resultss['username']);
                }
            }
            if($get_username_Check && !in_array($get_username_Check['status'],['active','on_hold'])){
            update("invoice","status","disabled", "username",$resultss['username']);
            }
        }
        }
    }