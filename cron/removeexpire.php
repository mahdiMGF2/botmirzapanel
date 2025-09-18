<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');
require_once '../config.php';
require_once '../botapi.php';
require_once '../panels.php';
require_once '../functions.php';
require_once '../text.php';
$ManagePanel = new ManagePanel();


$setting = select("setting", "*");
// buy service 
$stmt = $pdo->prepare("SELECT * FROM invoice WHERE (status = 'active' OR status = 'end_of_time' OR status = 'end_of_volume' OR status = 'sendedwarn') AND name_product != 'usertest' ORDER BY RAND() LIMIT 10");
$stmt->execute();
        while ($resultss = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $line  = trim($resultss['username']);
        $marzban_list_get =select("marzban_panel","*","name_panel",$resultss['Service_location'],"select");
        if($marzban_list_get == false)continue;
        $get_username_Check = $ManagePanel->DataUser($resultss['Service_location'],$resultss['username']);
        if($get_username_Check['status'] != "Unsuccessful"){
        if(in_array($get_username_Check['status'],['limited','expired'])){
        $timeservice = $get_username_Check['expire'] - time();
        $day = floor($timeservice / 86400);
        $output =  $get_username_Check['data_limit'] - $get_username_Check['used_traffic'];
        $textservice = select("textbot","text","id_text","text_Purchased_services","select");
        $RemainingVolume = formatBytes($output);
        $status_var = [
        'active' => $textbotlang['users']['status']['active'],
        'limited' => $textbotlang['users']['status']['limited'],
        'disabled' => $textbotlang['users']['status']['disabled'],
        'expired' => $textbotlang['users']['status']['expired'],
        'on_hold' => $textbotlang['users']['status']['onhold'],
    ][$get_username_Check['status']];
    
        if ($day <= intval("-".$setting['removedayc'])) {
            sendmessage($resultss['id_user'], sprintf($textbotlang['users']['cron']['removeexpire'],$resultss['username']), null, 'HTML');
            update("invoice","status","removeTime", "username",$line);
            $ManagePanel->RemoveUser($resultss['Service_location'], $line);
            if (strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], sprintf($textbotlang['Admin']['Report']['reportremovecron'],$line,$status_var), null, 'HTML');
        }
            }
        }
        }
    }
