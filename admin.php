<?php

#----------------[  admin section  ]------------------#
$textadmin = ["panel", "/panel",$textbotlang['Admin']['commendadminmanagment'], $textbotlang['Admin']['commendadmin']];
if (!in_array($from_id, $admin_ids)) {
    if (in_array($text, $textadmin)) {
        sendmessage($from_id, $textbotlang['users']['Invalid-comment'], null, 'HTML');
        foreach ($admin_ids as $admin) {
            $textadmin = sprintf($textbotlang['Admin']['Unauthorized-entry'],$username,$from_id,$first_name);
            sendmessage($admin, $textadmin, null, 'HTML');
        }
    }
    return;
}
if (in_array($text, $textadmin) || $datain == "PANEL") {
    if(!(function_exists('shell_exec') && is_callable('shell_exec'))){
        $cronCommandsendmessage = "*/1 * * * * curl https://$domainhosts/cron/sendmessage.php";
        sendmessage($from_id, sprintf($textbotlang['Admin']['cron']['active_manual_sendmessage'],$cronCommandsendmessage),null, 'HTML');
    }
    $text_admin = sprintf($textbotlang['Admin']['login-admin'],$version);
    sendmessage($from_id, $text_admin, $keyboardadmin, 'HTML');
}
if ($text == $textbotlang['Admin']['Back-Adminment']) {
    sendmessage($from_id, $textbotlang['Admin']['Back-Admin'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    return;
}
elseif ($text == $textbotlang['Admin']['channel']['changechannelbtn']) {
    sendmessage($from_id, $textbotlang['Admin']['channel']['changechannel'] . $channels['link'], $backadmin, 'HTML');
    step('addchannel', $from_id);
} elseif ($user['step'] == "addchannel") {
    sendmessage($from_id, $textbotlang['Admin']['channel']['setchannel'], $channelkeyboard, 'HTML');
    step('home', $from_id);
    $channels_ch = select("channels", "link", null, null, "count");
    $Check_filde = $connect->query("SHOW COLUMNS FROM channels LIKE 'Channel_lock'");
    if (mysqli_num_rows($Check_filde) == 1) {
            $connect->query("ALTER TABLE channels DROP COLUMN Channel_lock;");
            $stmt->execute();
        }
    if ($channels_ch == 0) {
        $stmt = $pdo->prepare("INSERT INTO channels (link) VALUES (?)");
        $stmt->bindParam(1, $text, PDO::PARAM_STR);

        $stmt->execute();
    } else {
        update("channels", "link", $text);
    }
}
if ($text == $textbotlang['Admin']['Addedadmin']) {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
    step('addadmin', $from_id);
}
if ($user['step'] == "addadmin") {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['addadminset'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    $stmt = $pdo->prepare("INSERT INTO admin (id_admin) VALUES (?)");
    $stmt->bindParam(1, $text);
    $stmt->execute();
}
if ($text == $textbotlang['Admin']['Removeedadmin']) {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
    step('deleteadmin', $from_id);
} elseif ($user['step'] == "deleteadmin") {
    if(intval($text) == $adminnumber){
        sendmessage($from_id,$textbotlang['Admin']['manageadmin']['InfoAdd'], null, 'HTML');
        return;
    }
    if (!is_numeric($text) || !in_array($text, $admin_ids))
        return;
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['removedadmin'], $keyboardadmin, 'HTML');
    $stmt = $pdo->prepare("DELETE FROM admin WHERE id_admin = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    step('home', $from_id);
}
elseif (preg_match('/limitusertest_(.*)/', $datain, $dataget)) {
    $id_user = $dataget[1];
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['getid'], $backadmin, 'HTML');
    update("user", "Processing_value", $id_user, "id", $from_id);
    step('get_number_limit', $from_id);
} elseif ($user['step'] == "get_number_limit") {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimit'], $keyboardadmin, 'HTML');
    $id_user_set = $text;
    step('home', $from_id);
    update("user", "limit_usertest", $text, "id", $user['Processing_value']);
}
if ($text == $textbotlang['Admin']['getlimitusertest']['setlimitallbtn']) {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['limitall'], $backadmin, 'HTML');
    step('limit_usertest_allusers', $from_id);
} elseif ($user['step'] == "limit_usertest_allusers") {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimitall'], $keyboard_usertest, 'HTML');
    step('home', $from_id);
    update("setting", "limit_usertest_all", $text);
    update("user", "limit_usertest", $text);
}
if ($text == $textbotlang['Admin']['channel']['setting']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $channelkeyboard, 'HTML');
}
#-------------------------#
if ($text == $textbotlang['Admin']['keyboardadmin']['bot_statistics']) {
    $current_date_time = time();
    $datefirst = $current_date_time - 86400;
    $desired_date_time_start = $current_date_time - 3600;
    $month_date_time_start = $current_date_time - 2592000;
    $datefirstday = time() - 86400;
    $dateacc = jdate('Y/m/d');
    $sql = "SELECT * FROM invoice WHERE  (Status = 'active' OR Status = 'end_of_time'  OR Status = 'end_of_volume' OR status = 'sendedwarn') AND name_product != 'usertest'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $dayListSell = $stmt->rowCount();
    $Balanceall =  select("user","SUM(Balance)",null,null,"select");
    $statistics = select("user","*",null,null,"count");
    $sumpanel = select("marzban_panel","*",null,null,"count");
    $sqlinvoice = "SELECT *  FROM invoice WHERE (Status = 'active' OR Status = 'end_of_time'  OR Status = 'end_of_volume' OR Status = 'sendedwarn') AND name_product != 'usertest'";
    $stmt = $pdo->prepare($sqlinvoice);
    $stmt->execute();
    $invoice =$stmt->rowCount();
    $sql = "SELECT SUM(price_product)  FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn') AND name_product != 'usertest'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $invoicesum =$stmt->fetch(PDO::FETCH_ASSOC)['SUM(price_product)'];
    $sql = "SELECT SUM(price_product) FROM invoice WHERE time_sell > :time_sell AND (Status = 'active' OR Status = 'end_of_time'  OR Status = 'end_of_volume' OR status = 'sendedwarn') AND name_product != 'usertest'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':time_sell', $datefirstday);
    $stmt->execute();
    $dayListSell = $stmt->rowCount();
    $count_usertest = select("invoice","*","name_product","usertest","count");
    $ping = sys_getloadavg();
    $ping = number_format(floatval($ping[0]),2);
    $timeacc = jdate('H:i:s', time());
    $statisticsall = sprintf($textbotlang['Admin']['Statistics']['info'],$statistics,$Balanceall['SUM(Balance)'],$ping,$count_usertest,$invoice ,$invoicesum,$dayListSell,$sumpanel);
    sendmessage($from_id, $statisticsall, null, 'HTML');
}

if ($text == $textbotlang['Admin']['managepanel']['btnshowconnect']) {
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($marzban_list_get['type'] == "marzban") {
        $Check_token = token_panel($marzban_list_get['id']);
        if (isset ($Check_token['access_token'])) {
            $System_Stats = Get_System_Stats($user['Processing_value']);
            $active_users = $System_Stats['users_active'];
            $total_user = $System_Stats['total_user'];
            $mem_total = formatBytes($System_Stats['mem_total']);
            $mem_used = formatBytes($System_Stats['mem_used']);
            $bandwidth = formatBytes($System_Stats['outgoing_bandwidth'] + $System_Stats['incoming_bandwidth']);
            $Condition_marzban = "";
            $text_marzban = sprintf($textbotlang['Admin']['managepanel']['infomarzban'],$total_user,$active_users,$System_Stats['version'],$mem_total,$mem_used,$bandwidth);
            sendmessage($from_id, $text_marzban, null, 'HTML');
        } elseif (isset ($Check_token['detail']) && $Check_token['detail'] == "Incorrect username or password") {
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['Incorrectinfo'], null, 'HTML');
        } else {
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'] . json_encode($Check_token);
            sendmessage($from_id, $text_marzban, null, 'HTML');
        }
    }elseif ($marzban_list_get['type'] == "marzneshin") {
        $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
        if (isset($Check_token['access_token'])) {
            $System_Stats = Get_System_Statsm($user['Processing_value']);
            $active_users = $System_Stats['active'];
            $total_user = $System_Stats['total'];
            $text_marzban = sprintf($textbotlang['Admin']['managepanel']['infomarzneshin'],$total_user,$active_users);
            sendmessage($from_id, $text_marzban, null, 'HTML');
        } elseif (isset ($Check_token['detail']) && $Check_token['detail'] == "Incorrect username or password") {
            $text_marzban = $textbotlang['Admin']['managepanel']['Incorrectinfo'];
            sendmessage($from_id, $text_marzban, null, 'HTML');
        } else {
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'] . json_encode($Check_token);
            sendmessage($from_id, $text_marzban, null, 'HTML');
        }
    } elseif ($marzban_list_get['type'] == "x-ui_single") {
        $x_ui_check_connect = login($marzban_list_get['id'],false);
        if ($x_ui_check_connect['success']) {
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['connectx-ui'], null, 'HTML');
        } elseif ($x_ui_check_connect['msg'] == "Invalid username or password.") {
            $text_marzban = $textbotlang['Admin']['managepanel']['Incorrectinfo'];
            sendmessage($from_id, $text_marzban, null, 'HTML');
        } else {
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'];
            sendmessage($from_id, $text_marzban, null, 'HTML');
        }
    }elseif ($marzban_list_get['type'] == "alireza") {
        $x_ui_check_connect = loginalireza($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
        if ($x_ui_check_connect['success']) {
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['connectx-ui'], null, 'HTML');
        } elseif ($x_ui_check_connect['msg'] == "Invalid username or password.") {
            $text_marzban = $textbotlang['Admin']['managepanel']['Incorrectinfo'];
            sendmessage($from_id, $text_marzban, null, 'HTML');
        } else {
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'];
            sendmessage($from_id, $text_marzban, null, 'HTML');
        }
    }
    elseif ($marzban_list_get['type'] == "wgdashboard") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionwgdashboard, 'HTML');
    }
    elseif($marzban_list_get['type'] == "mikrotik"){
        $result = login_mikrotik($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
        if(isset($result['error'])){
            sendmessage($from_id,$textbotlang['Admin']['managepanel']['notconnect'], $optionmikrotik, 'HTML');
        }else{
            sendmessage($from_id,$textbotlang['Admin']['managepanel']['connectx-ui'], $optionmikrotik, 'HTML');
        }
    }
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['manageadmin']['showlistbtn']) {
    $List_admin = null;
    $admin_ids = array_filter($admin_ids);
    foreach ($admin_ids as $admin) {
        $List_admin .= "$admin\n";
    }
    $list_admin_text = sprintf($textbotlang['Admin']['manageadmin']['showlist'],$List_admin);
    sendmessage($from_id, $list_admin_text, $admin_section_panel, 'HTML');
}
if ($text == $textbotlang['Admin']['keyboardadmin']['add_panel']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['selecttypepanel'], $typepanel, 'HTML');
    step('gettyppepanel', $from_id);
}elseif($user['step'] == "gettyppepanel"){
    savedata("clear","type",$text);
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addpanelname'], $backadmin, 'HTML');
    step('add_name_panel', $from_id);
} elseif ($user['step'] == "add_name_panel") {
    if (in_array($text, $marzban_list)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Repeatpanel'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addpanelurl'], $backadmin, 'HTML');
    savedata("save","name",$text);
    step('add_link_panel', $from_id);
} elseif ($user['step'] == "add_link_panel") {
    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    savedata("save","url_panel",$text);
    $userdata = json_decode($user['Processing_value'],true);
    if($userdata['type'] == "s_ui" || $userdata['type'] == "wgdashboard"){
        sendmessage($from_id,$textbotlang['Admin']['managepanel']['settoken'], $backadmin, 'HTML');
        step('add_password_panel', $from_id);
        savedata("save","username_panel","none");
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['usernameset'], $backadmin, 'HTML');
    step('add_username_panel', $from_id);
} elseif ($user['step'] == "add_username_panel") {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getpassword'], $backadmin, 'HTML');
    step('add_password_panel', $from_id);
    savedata("save","username_panel",$text);
}elseif ($user['step'] == "add_password_panel") {
    $userdata = json_decode($user['Processing_value'],true);
    $inboundid = "0";
    $sublink = "onsublink";
    $config = "offconfig";
    $valueteststatus = "ontestshowpanel";
    $stauts = "activepanel";
    $on_hold = "offonhold";
    $stmt = $pdo->prepare("INSERT INTO marzban_panel (name_panel,url_panel,username_panel,password_panel,type,inboundid,sublink,configManual,MethodUsername,statusTest,status,onholdstatus) VALUES (?, ?, ?, ?, ?,?,?,?,?,?,?,?)");
    $stmt->execute([$userdata['name'],$userdata['url_panel'],$userdata['username_panel'],$text,$userdata['type'],$inboundid, $sublink, $config,$textbotlang['users']['customidAndRandom'],$valueteststatus,$stauts,$on_hold]);
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addedpanel'], $backadmin, 'HTML');
    sendmessage($from_id, "🥳", $keyboardadmin, 'HTML');
    if($userdata['type'] == "x-ui_single" or $userdata['type'] == "alireza") {
        sendmessage($from_id,$textbotlang['Admin']['managepanel']['notex-ui'], null, 'HTML');
    }elseif($userdata['type'] == "marzban" || $userdata['type'] == "s_ui" || $userdata['type'] == "marzneshin"){
        sendmessage($from_id,$textbotlang['Admin']['managepanel']['notemarzban'], null, 'HTML');
    }elseif($userdata['type'] == "wgdashboard"){
        sendmessage($from_id,$textbotlang['Admin']['managepanel']['wgdashboard'], null, 'HTML');
    }elseif($userdata['type'] == "mikrotik"){
        sendmessage($from_id,$textbotlang['Admin']['managepanel']['mikrotik'], null, 'HTML');
    }
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['keyboardadmin']['send_message']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $sendmessageuser, 'HTML');
} elseif ($text == $textbotlang['Admin']['systemsms']['sendbulkbtn']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetText'], $backadmin, 'HTML');
    step('getconfirmsendall', $from_id);
}elseif($user['step'] == "getconfirmsendall"){
    if(!$text){
        sendmessage($from_id,$textbotlang['Admin']['systemsms']['allowsendtext'], $backadmin, 'HTML');
        return;
    }
    savedata("clear","text",$text);
    savedata("save","id_admin",$from_id);
    sendmessage($from_id,$textbotlang['Admin']['systemsms']['acceptsend'] , $backadmin, 'HTML');
    step("gettextforsendall",$from_id);
} elseif ($user['step'] == "gettextforsendall") {
    $userdata  = json_decode($user['Processing_value'],true);
    if($text == $textbotlang['Admin']['accept']){
        step('home', $from_id);
        $result = select("user","id","User_Status","Active","fetchAll");
        $Respuseronse = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['Admin']['systemsms']['cancelsend'], 'callback_data' => 'cancel_sendmessage'],
                ],
            ]
        ]);
        file_put_contents('cron/users.json',json_encode($result));
        file_put_contents('cron/info',$user['Processing_value']);
        sendmessage($from_id, $textbotlang['Admin']['systemsms']['sendingmessage'], $Respuseronse, 'HTML');
    }
}elseif($datain == "cancel_sendmessage"){
    unlink('cron/users.json');
    unlink('cron/info');
    deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['Admin']['systemsms']['canceledmessage'], null, 'HTML');
} elseif ($text == $textbotlang['Admin']['systemsms']['forwardbulkbtn']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ForwardGetext'], $backadmin, 'HTML');
    step('gettextforwardMessage', $from_id);
} elseif ($user['step'] == "gettextforwardMessage") {
    sendmessage($from_id,$textbotlang['Admin']['systemsms']['sendingforward'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    $filename = 'user.txt';
    $stmt = $pdo->prepare("SELECT id FROM user");
    $stmt->execute();
    if ($result) {
        $ids = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ids[] = $row['id'];
        }
        $idsText = implode("\n", $ids);
        file_put_contents($filename, $idsText);
    }
    $file = fopen($filename, 'r');
    if ($file) {
        while (($line = fgets($file)) !== false) {
            $line = trim($line);
            forwardMessage($from_id, $message_id, $line);
            usleep(2000000);
        }
        sendmessage($from_id,$textbotlang['Admin']['systemsms']['sendforwardtousers'], $keyboardadmin, 'HTML');
        fclose($file);
    }
    unlink($filename);
}
//_________________________________________________
if ($text == $textbotlang['Admin']['keyboardadmin']['bot_text_settings']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $textbot, 'HTML');
} elseif ($text == $textbotlang['Admin']['changetext']['textstart']) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_start'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextstart', $from_id);
} elseif ($user['step'] == "changetextstart") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_start");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_Purchased_services']) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Purchased_services'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextinfo', $from_id);
} elseif ($user['step'] == "changetextinfo") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_Purchased_services");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_usertest'] ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_usertest'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextusertest', $from_id);
} elseif ($user['step'] == "changetextusertest") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_usertest");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_help']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_help'], $backadmin, 'HTML');
    step('text_help', $from_id);
} elseif ($user['step'] == "text_help") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_help");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_support']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_support'], $backadmin, 'HTML');
    step('text_support', $from_id);
} elseif ($user['step'] == "text_support") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_support");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_fq']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_fq'], $backadmin, 'HTML');
    step('text_fq', $from_id);
} elseif ($user['step'] == "text_fq") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_fq");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_dec_fq']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_dec_fq'], $backadmin, 'HTML');
    step('text_dec_fq', $from_id);
} elseif ($user['step'] == "text_dec_fq") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_dec_fq");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_channel']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_channel'], $backadmin, 'HTML');
    step('text_channel', $from_id);
} elseif ($user['step'] == "text_channel") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_channel");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_account']) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_account'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('text_account', $from_id);
} elseif ($user['step'] == "text_account") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_account");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_Add_Balance']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Add_Balance'], $backadmin, 'HTML');
    step('text_Add_Balance', $from_id);
} elseif ($user['step'] == "text_Add_Balance") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_Add_Balance");
    step('home', $from_id);
} elseif ($text == $textbotlang['users']['changetext']['buy_subscription_button']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_sell'], $backadmin, 'HTML');
    step('text_sell', $from_id);
} elseif ($user['step'] == "text_sell") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_sell");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_Tariff_list']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Tariff_list'], $backadmin, 'HTML');
    step('text_Tariff_list', $from_id);
} elseif ($user['step'] == "text_Tariff_list") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_Tariff_list");
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['changetext']['text_dec_Tariff_list']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_dec_Tariff_list'], $backadmin, 'HTML');
    step('text_dec_Tariff_list', $from_id);
} elseif ($user['step'] == "text_dec_Tariff_list") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_dec_Tariff_list");
    step('home', $from_id);
}
//_________________________________________________
if ($text == $textbotlang['Admin']['systemsms']['sendmessageauser']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetText'], $backadmin, 'HTML');
    step('sendmessagetext', $from_id);
} elseif ($user['step'] == "sendmessagetext") {
    update("user", "Processing_value", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIDMessage'], $backadmin, 'HTML');
    step('sendmessagetid', $from_id);
} elseif ($user['step'] == "sendmessagetid") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $textsendadmin = sprintf($textbotlang['Admin']['systemsms']['sendedmessagetouser'],$user['Processing_value']);
    sendmessage($text, $textsendadmin, null, 'HTML');
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['MessageSent'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}
//_________________________________________________
if ($text == $textbotlang['Admin']['Help']['titlebtn']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardhelpadmin, 'HTML');
} elseif ($text == $textbotlang['Admin']['Help']['addhelp']) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddNameHelp'], $backadmin, 'HTML');
    step('add_name_help', $from_id);
} elseif ($user['step'] == "add_name_help") {
    $stmt = $pdo->prepare("INSERT IGNORE INTO help (name_os) VALUES (?)");
    $stmt->bindParam(1, $text, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddDecHelp'], $backadmin, 'HTML');
    step('add_dec', $from_id);
    update("user", "Processing_value", $text, "id", $from_id);
} elseif ($user['step'] == "add_dec") {
    if ($photo) {
        update("help", "Media_os", $photoid, "name_os", $user['Processing_value']);
        update("help", "Description_os", $caption, "name_os", $user['Processing_value']);
        update("help", "type_Media_os", "photo", "name_os", $user['Processing_value']);
    } elseif ($text) {
        update("help", "Description_os", $text, "name_os", $user['Processing_value']);
    } elseif ($video) {
        update("help", "Media_os", $videoid, "name_os", $user['Processing_value']);
        update("help", "Description_os", $caption, "name_os", $user['Processing_value']);
        update("help", "type_Media_os", "video", "name_os", $user['Processing_value']);
    }
    sendmessage($from_id, $textbotlang['Admin']['Help']['SaveHelp'], $keyboardadmin, 'HTML');
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['Help']['removehelpbtn']) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['SelectName'], $json_list_help, 'HTML');
    step('remove_help', $from_id);
} elseif ($user['step'] == "remove_help") {
    $stmt = $pdo->prepare("DELETE FROM help WHERE name_os = ?");
    $stmt->execute([$text]);
    sendmessage($from_id, $textbotlang['Admin']['Help']['RemoveHelp'], $keyboardhelpadmin, 'HTML');
    step('home', $from_id);
}
//_________________________________________________
if (preg_match('/Response_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    update("user", "Processing_value", $iduser, "id", $from_id);
    step('getmessageAsAdmin', $from_id);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetTextResponse'], $backadmin, 'HTML');
} elseif ($user['step'] == "getmessageAsAdmin") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SendMessageuser'], null, 'HTML');
    if ($text) {
        $textSendAdminToUser = sprintf($textbotlang['Admin']['systemsms']['sendedmessagetouser'],$text);
        sendmessage($user['Processing_value'], $textSendAdminToUser, null, 'HTML');
    }
    if ($photo) {
        $textSendAdminToUser = sprintf($textbotlang['Admin']['systemsms']['sendedmessagetouser'],$caption);
        telegram('sendphoto', [
            'chat_id' => $user['Processing_value'],
            'photo' => $photoid,
            'reply_markup' => $Response,
            'caption' => $textSendAdminToUser,
            'parse_mode' => "HTML",
        ]);
    }
    step('home', $from_id);
}
//_________________________________________________
if ($text == $textbotlang['Admin']['managepanel']['showpanelbtn']) {
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $view_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['status'], 'callback_data' => $panel['status']],
            ],
        ]
    ]);
    sendmessage($from_id,$textbotlang['Admin']['managepanel']['showpaneldec'], $view_Status, 'HTML');
}
if ($datain == "activepanel") {
    update("marzban_panel", "status", "disablepanel", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $view_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['status'], 'callback_data' => $panel['status']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['managepanel']['offpanel'], $view_Status);
} elseif ($datain == "disablepanel") {
    update("marzban_panel", "status", "activepanel", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $view_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['status'], 'callback_data' => $panel['status']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['managepanel']['onpanel'], $view_Status);
}
//_________________________________________________
if ($text == $textbotlang['Admin']['managepanel']['showpaneltestbtn']) {
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $view_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['statusTest'], 'callback_data' => $panel['statusTest']],
            ],
        ]
    ]);
    sendmessage($from_id,$textbotlang['Admin']['managepanel']['showpaneldec'], $view_Status, 'HTML');
}
if ($datain == "ontestshowpanel") {
    update("marzban_panel", "statusTest", "offtestshowpanel", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $view_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['statusTest'], 'callback_data' => $panel['statusTest']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['managepanel']['offpanel'], $view_Status);
} elseif ($datain == "offtestshowpanel") {
    update("marzban_panel", "statusTest", "ontestshowpanel", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $view_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['statusTest'], 'callback_data' => $panel['statusTest']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['managepanel']['onpanel'], $view_Status);
}
//_________________________________________________
elseif (preg_match('/banuserlist_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    $userblock = select("user", "*", "id", $iduser, "select");
    if ($userblock['User_Status'] == "block") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockedUser'], $backadmin, 'HTML');
        return;
    }
    update("user", "Processing_value", $iduser, "id", $from_id);
    update("user", "User_Status", "block", "id", $iduser);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockUser'], $backadmin, 'HTML');
    step('adddecriptionblock', $from_id);
} elseif ($user['step'] == "adddecriptionblock") {
    update("user", "description_blocking", $text, "id", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['DescriptionBlock'], $keyboardadmin, 'HTML');
    step('home', $from_id);
} elseif (preg_match('/unbanuserr_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    $userunblock = select("user", "*", "id", $iduser, "select");
    if ($userunblock['User_Status'] == "Active") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserNotBlock'], $backadmin, 'HTML');
        return;
    }
    update("user", "User_Status", "Active", "id", $iduser);
    update("user", "description_blocking", "", "id", $iduser);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserUnblocked'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}
//_________________________________________________
elseif ($text == $textbotlang['Admin']['changetext']['ruletext']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_roll'], $backadmin, 'HTML');
    step('text_roll', $from_id);
} elseif ($user['step'] == "text_roll") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_roll");
    step('home', $from_id);
}
//_________________________________________________
if ($text == $textbotlang['Admin']['keyboardadmin']['user_services']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $User_Services, 'HTML');
}
#-------------------------#
elseif (preg_match('/confirmnumber_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    update("user", "number", "confrim number by admin", "id", $iduser);
    step('home', $iduser);
    sendmessage($from_id, $textbotlang['Admin']['phone']['active'], $User_Services, 'HTML');
}
if ($text == $textbotlang['Admin']['channel']['channelreport']) {
    sendmessage($from_id, $textbotlang['Admin']['Channel']['ReportChannel'] . $setting['Channel_Report'], $backadmin, 'HTML');
    step('addchannelid', $from_id);
} elseif ($user['step'] == "addchannelid") {
    sendmessage($from_id, $textbotlang['Admin']['Channel']['SetChannelReport'], $keyboardadmin, 'HTML');
    update("setting", "Channel_Report", $text);
    step('home', $from_id);
    sendmessage($setting['Channel_Report'], $textbotlang['Admin']['Channel']['TestChannel'], null, 'HTML');
}
#-------------------------#
if ($text == $textbotlang['Admin']['keyboardadmin']['shop_section']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $shopkeyboard, 'HTML');
} elseif ($text == $textbotlang['Admin']['Product']['addproduct']) {
    $locationproduct = select("marzban_panel", "*", null, null, "count");
    if ($locationproduct == 0) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpaneladmin'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Product']['AddProductStepOne'], $backadmin, 'HTML');
    step('get_limit', $from_id);
} elseif ($user['step'] == "get_limit") {
    $randomString = bin2hex(random_bytes(2));
    $stmt = $pdo->prepare("INSERT IGNORE INTO product (name_product, code_product) VALUES (?, ?)");
    $stmt->bindParam(1, $text);
    $stmt->bindParam(2, $randomString);

    $stmt->execute();
    update("user", "Processing_value", $randomString, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['Service_location'], $json_list_marzban_panel, 'HTML');
    step('get_location', $from_id);
} elseif ($user['step'] == "get_location") {
    update("product", "Location", $text, "code_product", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['Getcategory'], KeyboardCategory(), 'HTML');
    step('get_category', $from_id);
} elseif ($user['step'] == "get_category") {
    $category = select("category","*","remark",$text,"select");
    if($category == false){
        sendmessage($from_id, $textbotlang['Admin']['Product']['invalidcategory'], $backadmin, 'HTML');
        return;
    }
    update("product", "category", $category['id'], "code_product", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetLimit'], $backadmin, 'HTML');
    step('get_time', $from_id);
} elseif ($user['step'] == "get_time") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    update("product", "Volume_constraint", $text, "code_product", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GettIime'], $backadmin, 'HTML');
    step('get_price', $from_id);
} elseif ($user['step'] == "get_price") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    update("product", "Service_time", $text, "code_product", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetPrice'], $backadmin, 'HTML');
    step('endstep', $from_id);
} elseif ($user['step'] == "endstep") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    update("product", "price_product", $text, "code_product", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['SaveProduct'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['keyboardadmin']['admin_section']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $admin_section_panel, 'HTML');
}
#-------------------------#
if ($text == $textbotlang['Admin']['keyboardadmin']['settings']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $setting_panel, 'HTML');
}
#-------------------------#
if ($text == $textbotlang['Admin']['keyboardadmin']['test_account_settings']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard_usertest, 'HTML');
}
#-------------------------#
if (preg_match('/Confirm_pay_(\w+)/', $datain, $dataget)) {
    $order_id = $dataget[1];
    $Payment_report = select("Payment_report", "*", "id_order", $order_id, "select");
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
    if ($Payment_report['payment_Status'] == "paid" || $Payment_report['payment_Status'] == "reject") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
        return;
    }
    DirectPayment($order_id);
    update("user","Processing_value","0", "id",$Balance_id['id']);
    update("user","Processing_value_one","0", "id",$Balance_id['id']);
    update("user","Processing_value_tow","0", "id",$Balance_id['id']);
    update("Payment_report","payment_Status","paid","id_order",$order_id);
    if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], sprintf($textbotlang['Admin']['Report']['acceptcartresid'],$from_id,$Payment_report['price']), null, 'HTML');
    }
}
#-------------------------#
if (preg_match('/reject_pay_(\w+)/', $datain, $datagetr)) {
    $id_order = $datagetr[1];
    $Payment_report = select("Payment_report", "*", "id_order", $id_order, "select");
    update("user", "Processing_value", $Payment_report['id_user'], "id", $from_id);
    update("user", "Processing_value_one", $id_order, "id", $from_id);
    if ($Payment_report['payment_Status'] == "reject" || $Payment_report['payment_Status'] == "paid") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
        return;
    }
    update("Payment_report", "payment_Status", "reject", "id_order", $id_order);
    sendmessage($from_id, $textbotlang['Admin']['Payment']['Reasonrejecting'], $backadmin, 'HTML');
    step('reject-dec', $from_id);
    Editmessagetext($from_id, $message_id, $text_callback, null);
} elseif ($user['step'] == "reject-dec") {
    update("Payment_report", "dec_not_confirmed", $text, "id_order", $user['Processing_value_one']);
    sendmessage($from_id, $textbotlang['Admin']['Payment']['Rejected'], $keyboardadmin, 'HTML');
    sendmessage($user['Processing_value'], sprintf($textbotlang['users']['moeny']['rejectresid'],$text,$user['Processing_value_one']), null, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Product']['titlebtnremove']) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['Rmove_location'], $json_list_marzban_panel, 'HTML');
    step('selectloc', $from_id);
} elseif ($user['step'] == "selectloc") {
    update("user", "Processing_value", $text, "id", $from_id);
    step('remove-product', $from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectRemoveProduct'], $json_list_product_list_admin, 'HTML');
} elseif ($user['step'] == "remove-product") {
    if (!in_array($text, $name_product)) {
        sendmessage($from_id, $textbotlang['users']['sell']['error-product'], null, 'HTML');
        return;
    }
    $ydf = '/all';
    $stmt = $pdo->prepare("DELETE FROM product WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $stmt->execute([$text, $user['Processing_value'], $ydf]);
    sendmessage($from_id, $textbotlang['Admin']['Product']['RemoveedProduct'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Product']['titlebtnedit']) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['Rmove_location'], $json_list_marzban_panel, 'HTML');
    step('selectlocedite', $from_id);
} elseif ($user['step'] == "selectlocedite") {
    update("user", "Processing_value_one", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectEditProduct'], $json_list_product_list_admin, 'HTML');
    step('change_filde', $from_id);
} elseif ($user['step'] == "change_filde") {
    if (!in_array($text, $name_product)) {
        sendmessage($from_id, $textbotlang['users']['sell']['error-product'], null, 'HTML');
        return;
    }
    update("user", "Processing_value", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectfieldProduct'], $change_product, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Product']['editprice']) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['sendnewprice'], $backadmin, 'HTML');
    step('change_price', $from_id);
} elseif ($user['step'] == "change_price") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    $location = '/all';
    $stmtFirst = $pdo->prepare("UPDATE product SET price_product = ? WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $stmtFirst->execute([$text, $user['Processing_value'], $user['Processing_value_one'], $location]);
    $stmtSecond = $pdo->prepare("UPDATE invoice SET price_product = ? WHERE name_product = ? AND Service_location = ?");
    $stmtSecond->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    sendmessage($from_id, $textbotlang['Admin']['Product']['updatedprice'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Product']['editcategory']) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['sendnewcategory'], KeyboardCategory(), 'HTML');
    step('change_category', $from_id);
} elseif ($user['step'] == "change_category") {
    $category = select("category","*","remark",$text,"select");
    if($category == false){
        sendmessage($from_id,$textbotlang['Admin']['Product']['invalidcategory'], $backadmin, 'HTML');
        return;
    }
    $location = "/all";
    $stmtFirst = $pdo->prepare("UPDATE product SET category = ? WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $stmtFirst->execute([$category['id'], $user['Processing_value'], $user['Processing_value_one'], $location]);
    sendmessage($from_id, $textbotlang['Admin']['Product']['updatedcategory'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Product']['editname']) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['sendnewname'], $backadmin, 'HTML');
    step('change_name', $from_id);
} elseif ($user['step'] == "change_name") {
    $value = "/all";
    $stmtFirst = $pdo->prepare("UPDATE product SET name_product = ? WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $stmtFirst->execute([$text, $user['Processing_value'], $user['Processing_value_one'], $value]);
    $sqlSecond = "UPDATE invoice SET name_product = ? WHERE name_product = ? AND Service_location = ?";
    $stmtSecond = $pdo->prepare($sqlSecond);
    $stmtSecond->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    sendmessage($from_id, $textbotlang['Admin']['Product']['updatedname'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Product']['editvolume']) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['sendnewvolume'], $backadmin, 'HTML');
    step('change_val', $from_id);
} elseif ($user['step'] == "change_val") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    $sqlInvoice = "UPDATE invoice SET Volume = ? WHERE name_product = ? AND Service_location = ?";
    $stmtInvoice = $pdo->prepare($sqlInvoice);
    $stmtInvoice->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    $sqlProduct = "UPDATE product SET Volume_constraint = ? WHERE name_product = ? AND Location = ?";
    $stmtProduct = $pdo->prepare($sqlProduct);
    $stmtProduct->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    sendmessage($from_id, $textbotlang['Admin']['Product']['updatedvolume'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Product']['edittime']) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['NewTime'], $backadmin, 'HTML');
    step('change_time', $from_id);
} elseif ($user['step'] == "change_time") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    $stmtInvoice = $pdo->prepare("UPDATE invoice SET Service_time = ? WHERE name_product = ? AND Service_location = ?");
    $stmtInvoice->bindParam(1, $text);
    $stmtInvoice->bindParam(2, $user['Processing_value']);
    $stmtInvoice->bindParam(3, $user['Processing_value_one']);
    $stmtInvoice->execute();
    $stmtProduct = $pdo->prepare("UPDATE product SET Service_time = ? WHERE name_product = ? AND Location = ?");
    $stmtProduct->bindParam(1, $text);
    $stmtProduct->bindParam(2, $user['Processing_value']);
    $stmtProduct->bindParam(3, $user['Processing_value_one']);
    $stmtProduct->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['TimeUpdated'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Usertest']['settimeusertest']) {
    sendmessage($from_id,sprintf($textbotlang['Admin']['Usertest']['sendtimeusertest'],$setting['time_usertest']), $backadmin, 'HTML');
    step('updatetime', $from_id);
} elseif ($user['step'] == "updatetime") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    update("setting", "time_usertest", $text);
    sendmessage($from_id, $textbotlang['Admin']['Usertest']['TimeUpdated'], $keyboard_usertest, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Usertest']['setvolumeusertest']) {
    sendmessage($from_id, sprintf($textbotlang['Admin']['Usertest']['sendvoluemusertest'],$setting['val_usertest']), $backadmin, 'HTML');
    step('val_usertest', $from_id);
} elseif ($user['step'] == "val_usertest") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    update("setting", "val_usertest", $text);
    sendmessage($from_id, $textbotlang['Admin']['Usertest']['VolumeUpdated'], $keyboard_usertest, 'HTML');
    step('home', $from_id);
}
#-------------------------#
elseif (preg_match('/addbalanceuser_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    update("user","Processing_value",$iduser, "id",$from_id);
    sendmessage($from_id, $textbotlang['Admin']['Balance']['PriceBalance'], $backadmin, 'HTML');
    step('get_price_add', $from_id);
} elseif ($user['step'] == "get_price_add") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    if(intval($text) > 100000000){
        sendmessage($from_id, $textbotlang['Admin']['Balance']['maxpricebalance'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalanceUser'], $User_Services, 'HTML');
    $Balance_user = select("user", "*", "id", $user['Processing_value'], "select");
    $Balance_add_user = $Balance_user['Balance'] + $text;
    update("user", "Balance", $Balance_add_user, "id", $user['Processing_value']);
    $text = number_format($text);
    sendmessage($user['Processing_value'], sprintf($textbotlang['Admin']['Balance']['AddedBalance'] ,$text), null, 'HTML');
    step('home', $from_id);
}
#-------------------------#
elseif (preg_match('/lowbalanceuser_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    update("user","Processing_value",$iduser, "id",$from_id);
    sendmessage($from_id, $textbotlang['Admin']['Balance']['PriceBalancek'], $backadmin, 'HTML');
    step('get_price_Negative', $from_id);
} elseif ($user['step'] == "get_price_Negative") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    if(intval($text) > 100000000){
        sendmessage($from_id, $textbotlang['Admin']['Balance']['maxpricebalance'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['NegativeBalanceUser'], $User_Services, 'HTML');
    $Balance_user = select("user", "*", "id", $user['Processing_value'], "select");
    $Balance_Low_user = $Balance_user['Balance'] - $text;
    update("user", "Balance", $Balance_Low_user, "id", $user['Processing_value']);
    $text = number_format($text);
    sendmessage($user['Processing_value'], sprintf($textbotlang['Admin']['Balance']['ReduceBalance'],$text), null, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Discount']['titlebtn']) {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['GetCode'], $backadmin, 'HTML');
    step('get_code', $from_id);
} elseif ($user['step'] == "get_code") {
    if (!preg_match('/^[A-Za-z]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['ErrorCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("INSERT INTO Discount (code) VALUES (?)");
    $stmt->bindParam(1, $text);
    $stmt->execute();

    sendmessage($from_id, $textbotlang['Admin']['Discount']['PriceCode'], null, 'HTML');
    step('get_price_code', $from_id);
    update("user", "Processing_value", $text, "id", $from_id);
} elseif ($user['step'] == "get_price_code") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    update("Discount", "price", $text, "code", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['SaveCode'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['managepanel']['sublinkstatus']) {
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($panel['sublink'] == null) {
        update("marzban_panel", "sublink", "onsublink", "name_panel", $user['Processing_value']);
    }
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $sublinkkeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['sublink'], 'callback_data' => $panel['sublink']],
            ],
        ]
    ]);
    if ($panel['configManual'] == "onconfig") {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['checkoffconfig'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Status']['subTitle'], $sublinkkeyboard, 'HTML');
}
if ($datain == "onsublink") {
    update("marzban_panel", "sublink", "offsublink", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $sublinkkeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['sublink'], 'callback_data' => $panel['sublink']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['subStatusOff'], $sublinkkeyboard);

} elseif ($datain == "offsublink") {
    update("marzban_panel", "sublink", "onsublink", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $sublinkkeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['sublink'], 'callback_data' => $panel['sublink']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['subStatuson'], $sublinkkeyboard);
}
#-------------------------#
if ($text == $textbotlang['Admin']['managepanel']['configstatus']) {
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($panel['configManual'] == null) {
        update("marzban_panel", "configManual", "offconfig", "name_panel", $user['Processing_value']);
    }
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $configkeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['configManual'], 'callback_data' => $panel['configManual']],
            ],
        ]
    ]);
    if ($panel['sublink'] == "onsublink") {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['notoffsublink'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Status']['configTitle'], $configkeyboard, 'HTML');
}
if ($datain == "onconfig") {
    update("marzban_panel", "configManual", "offconfig", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $configkeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['configManual'], 'callback_data' => $panel['configManual']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['configStatusOff'], $configkeyboard);
} elseif ($datain == "offconfig") {
    update("marzban_panel", "configManual", "onconfig", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $configkeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['configManual'], 'callback_data' => $panel['configManual']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['configStatuson'], $configkeyboard);
}
#----------------[  view order user  ]------------------#
elseif (preg_match('/vieworderall_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    $OrderUsers = select("invoice", "*", "id_user", $iduser, "fetchAll");
    foreach ($OrderUsers as $OrderUser) {
        $timeacc = jdate('Y/m/d H:i:s', $OrderUser['time_sell']);
        sendmessage($from_id, sprintf($textbotlang['Admin']['ManageUser']['Datails'],$OrderUser['id_invoice'],$OrderUser['Status'],$OrderUser['id_user'],$OrderUser['username'],$OrderUser['Service_location'],$OrderUser['name_product'],$OrderUser['price_product'],$OrderUser['Volume'],$OrderUser['Service_time'],$timeacc), null, 'HTML');
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SendOrder'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}
#----------------[  remove Discount   ]------------------#
if ($text == $textbotlang['Admin']['Discount']['titlebtnremove']) {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemoveCode'], $json_list_Discount_list_admin, 'HTML');
    step('remove-Discount', $from_id);
} elseif ($user['step'] == "remove-Discount") {
    if (!in_array($text, $code_Discount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['NotCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM Discount WHERE code = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemovedCode'], $shopkeyboard, 'HTML');
}
if ($text == $textbotlang['Admin']['ManageUser']['removeorderbtn']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemoveService'], $backadmin, 'HTML');
    step('removeservice', $from_id);
} elseif ($user['step'] == "removeservice") {
    $info_product = select("invoice", "*", "username", $text, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $info_product['Service_location'], "select");
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $text);
    if (isset ($DataUserOut['status'])) {
        $ManagePanel->RemoveUser($marzban_list_get['name_panel'], $text);
    }
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE username = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemovedService'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['managepanel']['methodusername']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['decmthodusername'], $MethodUsername, 'HTML');
    step('updatemethodusername', $from_id);
} elseif ($user['step'] == "updatemethodusername") {
    update("marzban_panel", "MethodUsername", $text, "name_panel", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['AlgortimeUsername']['SaveData'], $keyboardadmin, 'HTML');
    if ($text == $textbotlang['users']['customtextandrandom']) {
        step('getnamecustom', $from_id);
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['customnamesend'], $backuser, 'HTML');
        return;
    }
    step('home', $from_id);
} elseif ($user['step'] == "getnamecustom") {
    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['invalidname'], $backadmin, 'html');
        return;
    }
    update("setting", "namecustome", $text);
    step('home', $from_id);
    $listpanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    update("user", "Processing_value", $text, "id", $from_id);
    outtypepanel($listpanel['type'],$textbotlang['Admin']['managepanel']['savedname']);
}
#----------------[  MANAGE PAYMENT   ]------------------#

if ($text == $textbotlang['Admin']['keyboardadmin']['finance']) {
    $sqlstatus_cart = select("PaySetting", "ValuePay", "NamePay", "Cartstatus", "select")['ValuePay'];
    $sqlstatus_nowpayment = select("PaySetting", "ValuePay", "NamePay", "nowpaymentstatus", "select")['ValuePay'];
    $sqlstatus_iranpay = select("PaySetting", "ValuePay", "NamePay", "digistatus", "select")['ValuePay'];
    $sqlstatus_aqayepardakht = select("PaySetting", "ValuePay", "NamePay", "statusaqayepardakht", "select")['ValuePay'];
    $status_cart = [
        'oncard' => $textbotlang['Admin']['turnon'],
        'offcard' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_cart];
    $status_nowpayment = [
        'onnowpayment' => $textbotlang['Admin']['turnon'],
        'offnowpayment' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_nowpayment];
    $status_iranpay = [
        'ondigi' => $textbotlang['Admin']['turnon'],
        'offdigi' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_iranpay];
    $status_qayepardakht = [
        'onaqayepardakht' => $textbotlang['Admin']['turnon'],
        'offaqayepardakht' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_aqayepardakht];
    $keyboardmoeny = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['moeny']['setting'], 'callback_data' => "settingcart"],
                ['text' => $status_cart, 'callback_data' => "editpay-cart-".$sqlstatus_cart],
                ['text' => $textbotlang['users']['moeny']['cart_to_Cart_btn'], 'callback_data' => "none"],
            ],
            [
                ['text' => $textbotlang['users']['moeny']['setting'], 'callback_data' => "SettingnowPayment"],
                ['text' => $status_nowpayment, 'callback_data' => "editpay-nowpayment-".$sqlstatus_nowpayment],
                ['text' => $textbotlang['users']['moeny']['nowpayment_gateway_status'], 'callback_data' => "none"],
            ],
            [
                ['text' => $textbotlang['users']['moeny']['setting'], 'callback_data' => "Settingaqayepardakht"],
                ['text' => $status_qayepardakht, 'callback_data' => "editpay-aqayepardakht-".$sqlstatus_aqayepardakht],
                ['text' => $textbotlang['users']['moeny']['mr_payment_gateway'], 'callback_data' => "none"],
            ],
            [
                ['text' => $status_iranpay, 'callback_data' => "editpay-iranpay-".$sqlstatus_iranpay],
                ['text' => $textbotlang['users']['moeny']['currency_rial_gateway'], 'callback_data' => "none"],
            ],
        ]
    ]);
    sendmessage($from_id,$textbotlang['users']['moeny']['settingpay'], $keyboardmoeny, 'HTML');
}elseif(preg_match('/^editpay-(.*)-(.*)/', $datain, $dataget)) {
    $methodpay = $dataget[1];
    $status = $dataget[2];
    if($methodpay == "cart"){
        if($status == "oncard"){
            $value = "offcard";
        }else{
            $value = "oncard";
        }
     update("PaySetting", "ValuePay", $value, "NamePay", "Cartstatus");
    }elseif($methodpay == "nowpayment"){
        if($status == "onnowpayment"){
            $value = "offnowpayment";
        }else{
            $value = "onnowpayment";
        }
     update("PaySetting", "ValuePay", $value, "NamePay", "nowpaymentstatus");
    }elseif($methodpay == "iranpay"){
        if($status == "ondigi"){
            $value = "offdigi";
        }else{
            $value = "ondigi";
        }
     update("PaySetting", "ValuePay", $value, "NamePay", "digistatus");
    }
    elseif($methodpay == "aqayepardakht"){
        if($status == "onaqayepardakht"){
            $value = "offaqayepardakht";
        }else{
            $value = "onaqayepardakht";
        }
     update("PaySetting", "ValuePay", $value, "NamePay", "statusaqayepardakht");
    }
    $sqlstatus_cart = select("PaySetting", "ValuePay", "NamePay", "Cartstatus", "select")['ValuePay'];
    $sqlstatus_nowpayment = select("PaySetting", "ValuePay", "NamePay", "nowpaymentstatus", "select")['ValuePay'];
    $sqlstatus_iranpay = select("PaySetting", "ValuePay", "NamePay", "digistatus", "select")['ValuePay'];
    $sqlstatus_aqayepardakht = select("PaySetting", "ValuePay", "NamePay", "statusaqayepardakht", "select")['ValuePay'];
    $status_cart = [
        'oncard' => $textbotlang['Admin']['turnon'],
        'offcard' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_cart];
    $status_nowpayment = [
        'onnowpayment' => $textbotlang['Admin']['turnon'],
        'offnowpayment' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_nowpayment];
    $status_iranpay = [
        'ondigi' => $textbotlang['Admin']['turnon'],
        'offdigi' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_iranpay];
    $status_qayepardakht = [
        'onaqayepardakht' => $textbotlang['Admin']['turnon'],
        'offaqayepardakht' => $textbotlang['Admin']['turnoff'],
        ][$sqlstatus_aqayepardakht];
    $keyboardmoeny = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['moeny']['setting'], 'callback_data' => "settingcart"],
                ['text' => $status_cart, 'callback_data' => "editpay-cart-".$sqlstatus_cart],
                ['text' => $textbotlang['users']['moeny']['cart_to_Cart_btn'], 'callback_data' => "none"],
            ],
            [
                ['text' => $textbotlang['users']['moeny']['setting'], 'callback_data' => "SettingnowPayment"],
                ['text' => $status_nowpayment, 'callback_data' => "editpay-nowpayment-".$sqlstatus_nowpayment],
                ['text' => $textbotlang['users']['moeny']['nowpayment_gateway_status'], 'callback_data' => "none"],
            ],
            [
                ['text' => $textbotlang['users']['moeny']['setting'], 'callback_data' => "Settingaqayepardakht"],
                ['text' => $status_qayepardakht, 'callback_data' => "editpay-aqayepardakht-".$sqlstatus_aqayepardakht],
                ['text' => $textbotlang['users']['moeny']['mr_payment_gateway'], 'callback_data' => "none"],
            ],
            [
                ['text' => $status_iranpay, 'callback_data' => "editpay-iranpay-".$sqlstatus_iranpay],
                ['text' => $textbotlang['users']['moeny']['currency_rial_gateway'], 'callback_data' => "none"],
            ],
        ]
    ]);
    Editmessagetext($from_id,$message_id,$textbotlang['users']['moeny']['settingpay'], $keyboardmoeny);
}
elseif ($datain == "settingcart") {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $CartManage, 'HTML');
}
if ($text == $textbotlang['users']['moeny']['card_number_settings']) {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "CartDescription", "select");
    sendmessage($from_id, sprintf($textbotlang['users']['moeny']['sendcart'] ,$PaySetting['ValuePay']), $backadmin, 'HTML');
    step('changecard', $from_id);
} elseif ($user['step'] == "changecard") {
    sendmessage($from_id, $textbotlang['Admin']['SettingPayment']['Savacard'], $CartManage, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "CartDescription");
    step('home', $from_id);
}
if ($datain == "SettingnowPayment") {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $NowPaymentsManage, 'HTML');
}
if ($text == $textbotlang['users']['moeny']['nowpayment_api'] ) {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "apinowpayment", "select")['ValuePay'];
    sendmessage($from_id, sprintf($textbotlang['users']['moeny']['getapinowpayment'],$PaySetting), $backadmin, 'HTML');
    step('apinowpayment', $from_id);
} elseif ($user['step'] == "apinowpayment") {
    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $NowPaymentsManage, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "apinowpayment");
    step('home', $from_id);
}
if ($datain == "Settingaqayepardakht") {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $aqayepardakht, 'HTML');
}
if ($text == $textbotlang['users']['moeny']['mr_payment_merchant_settings']) {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht", "select");
    sendmessage($from_id, sprintf($textbotlang['users']['moeny']['getmarchent'],$PaySetting['ValuePay']), $backadmin, 'HTML');
    step('merchant_id_aqayepardakht', $from_id);
} elseif ($user['step'] == "merchant_id_aqayepardakht") {
    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $aqayepardakht, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "merchant_id_aqayepardakht");
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['keyboardadmin']['manage_panel']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getloc'], $json_list_marzban_panel, 'HTML');
    step('GetLocationEdit', $from_id);
} elseif ($user['step'] == "GetLocationEdit") {
    $listpanel = select("marzban_panel", "*", "name_panel", $text, "select");
    update("user", "Processing_value", $text, "id", $from_id);
    outtypepanel($listpanel['type'],$textbotlang['users']['selectoption']);
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['namepanel']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['GetNameNew'], $backadmin, 'HTML');
    step('GetNameNew', $from_id);
} elseif ($user['step'] == "GetNameNew") {
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'],$textbotlang['Admin']['managepanel']['ChangedNmaePanel']);
    update("marzban_panel", "name_panel", $text, "name_panel", $user['Processing_value']);
    update("invoice", "Service_location", $text, "Service_location", $user['Processing_value']);
    update("product", "Location", $text, "Location", $user['Processing_value']);
    update("user", "Processing_value", $text, "id", $from_id);
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['editurl']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['geturlnew'], $backadmin, 'HTML');
    step('GeturlNew', $from_id);
} elseif ($user['step'] == "GeturlNew") {
    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'],$textbotlang['Admin']['managepanel']['ChangedurlPanel']);
    update("marzban_panel", "url_panel", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['editusername']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getusernamenew'], $backadmin, 'HTML');
    step('GetusernameNew', $from_id);
} elseif ($user['step'] == "GetusernameNew") {
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'],$textbotlang['Admin']['managepanel']['ChangedusernamePanel']);
    update("marzban_panel", "username_panel", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['editpassword']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getpasswordnew'], $backadmin, 'HTML');
    step('GetpaawordNew', $from_id);
} elseif ($user['step'] == "GetpaawordNew") {
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'],$textbotlang['Admin']['managepanel']['ChangedpasswordPanel']);
    update("marzban_panel", "password_panel", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['editinound'] || $text == $textbotlang['Admin']['managepanel']['setgroup']) {
    if($text == $textbotlang['Admin']['managepanel']['setgroup']){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['keyboardpanel']['getgroup'], $backadmin, 'HTML');
    }else{
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['keyboardpanel']['getidinbound'], $backadmin, 'HTML');
    }
    step('getinboundiid', $from_id);
} elseif ($user['step'] == "getinboundiid") {
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'],$textbotlang['Admin']['managepanel']['keyboardpanel']['setinbound']);
    update("marzban_panel", "inboundid", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['linksub']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['geturlnew'], $backadmin, 'HTML');
    step('GeturlNewx', $from_id);
} elseif ($user['step'] == "GeturlNewx") {
    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    $panel = select("marzban_panel","*","name_panel",$user['Processing_value'],"select");
    if($panel['type'] == "x-ui_single"){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $text);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpCode != 200){
            sendmessage($from_id,$textbotlang['Admin']['managepanel']['subinvalidDomain'], null, 'HTML');
            return;
        }
        if (curl_error($ch)) {
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['subinvalidDomain'], null, 'HTML');
            return;
        }
        $protocol = ['vmess','vless','trojan','ss'];
        if(isBase64($response)){
           $response =  base64_decode($response);
        }
        $sub_check = explode('://',$response)[0];
        if(!in_array($sub_check,$protocol)){
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['subinvalid'], null, 'HTML');
            return;
        }
        $text = dirname($text);
    }
    outtypepanel($panel['type'],$textbotlang['Admin']['managepanel']['ChangedurlPanel']);
    update("marzban_panel", "linksubx", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
}elseif ($user['step'] == "GetpaawordNew") {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedpasswordPanel'], $optionMarzban, 'HTML');
    update("marzban_panel", "password_panel", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['removepanel']) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['RemovedPanel'], $keyboardadmin, 'HTML');
    $stmt = $pdo->prepare("DELETE FROM marzban_panel WHERE name_panel = ?");
    $stmt->bindParam(1, $user['Processing_value']);
    $stmt->execute();
}
if ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['setvolume']) {
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['SetPrice'] . $setting['Extra_volume'], $backadmin, 'HTML');
    step('GetPriceExtra', $from_id);
} elseif ($user['step'] == "GetPriceExtra") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    update("setting", "Extra_volume", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['ChangedPrice'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
#-------------------------#
if ($text == $textbotlang['Admin']['Balance']['SendBalanceAll']) {
    sendmessage($from_id, $textbotlang['Admin']['Balance']['addallbalance'], $backadmin, 'HTML');
    step('add_Balance_all', $from_id);
} elseif ($user['step'] == "add_Balance_all") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalanceUsers'], $User_Services, 'HTML');
    $Balance_user = select("user", "*", null, null, "fetchAll");
    foreach ($Balance_user as $balance) {
        $Balance_add_user = $balance['Balance'] + $text;
        update("user", "Balance", $Balance_add_user, "id", $balance['id']);
    }
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['Discountsell']['create']) {
    sendmessage($from_id, $textbotlang['Admin']['Discountsell']['GetCode'], $backadmin, 'HTML');
    step('get_codesell', $from_id);
} elseif ($user['step'] == "get_codesell") {
    if (in_array($text, $SellDiscount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['Discountused'], $backadmin, 'HTML');
        return;
    }
    if (!preg_match('/^[A-Za-z\d]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['ErrorCode'], null, 'HTML');
        return;
    }
    $values = "0";
    $stmt = $pdo->prepare("INSERT INTO DiscountSell (codeDiscount, usedDiscount, price, limitDiscount, usefirst) VALUES (?, ?, ?, ?,?)");
    $stmt->bindParam(1, $text);
    $stmt->bindParam(2, $values);
    $stmt->bindParam(3, $values);
    $stmt->bindParam(4, $values);
    $stmt->bindParam(5, $values);
    $stmt->execute();

    sendmessage($from_id, $textbotlang['Admin']['Discount']['PriceCodesell'], null, 'HTML');
    step('get_price_codesell', $from_id);
    update("user", "Processing_value", $text, "id", $from_id);
} elseif ($user['step'] == "get_price_codesell") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    update("DiscountSell", "price", $text, "codeDiscount", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discountsell']['getlimit'], $backadmin, 'HTML');
    step('getlimitcode', $from_id);
} elseif ($user['step'] == "getlimitcode") {
    update("DiscountSell", "limitDiscount", $text, "codeDiscount", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['typediscount'], $backadmin, 'HTML');
    step('getusefirst', $from_id);
} elseif ($user['step'] == "getusefirst") {
    update("DiscountSell", "usefirst", $text, "codeDiscount", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['SaveCode'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['Discountsell']['remove']) {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemoveCode'], $json_list_Discount_list_admin_sell, 'HTML');
    step('remove-Discountsell', $from_id);
} elseif ($user['step'] == "remove-Discountsell") {
    if (!in_array($text, $SellDiscount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['NotCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM DiscountSell WHERE codeDiscount = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemovedCode'], $shopkeyboard, 'HTML');
    step('home', $from_id);
}
if ($text == $textbotlang['Admin']['keyboardadmin']['affiliate_settings']) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $affiliates, 'HTML');
} elseif ($text == $textbotlang['Admin']['affiliate']['status']) {
    $affiliatesvalue = select("affiliates", "*", null, null, "select")['affiliatesstatus'];
    $keyboardaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $affiliatesvalue, 'callback_data' => $affiliatesvalue],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['affiliates'], $keyboardaffiliates, 'HTML');
} elseif ($datain == "onaffiliates") {
    update("affiliates", "affiliatesstatus", "offaffiliates");
    $affiliatesvalue = select("affiliates", "*", null, null, "select")['affiliatesstatus'];
    $keyboardaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $affiliatesvalue, 'callback_data' => $affiliatesvalue],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['affiliatesStatusOff'], $keyboardaffiliates);
} elseif ($datain == "offaffiliates") {
    update("affiliates", "affiliatesstatus", "onaffiliates");
    $affiliatesvalue = select("affiliates", "*", null, null, "select")['affiliatesstatus'];
    $keyboardaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $affiliatesvalue, 'callback_data' => $affiliatesvalue],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['affiliatesStatuson'], $keyboardaffiliates);
}
if ($text == $textbotlang['Admin']['affiliate']['Percentageset']) {
    sendmessage($from_id, $textbotlang['users']['affiliates']['setpercentage'], $backadmin, 'HTML');
    step('setpercentage', $from_id);
} elseif ($user['step'] == "setpercentage") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['invalidvalue'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['affiliates']['changedpercentage'], $affiliates, 'HTML');
    update("affiliates", "affiliatespercentage", $text);
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['affiliate']['setbaner']) {
    sendmessage($from_id, $textbotlang['users']['affiliates']['banner'], $backadmin, 'HTML');
    step('setbanner', $from_id);
} elseif ($user['step'] == "setbanner") {
    if (!$photo) {
        sendmessage($from_id, $textbotlang['users']['affiliates']['invalidbanner'], $backadmin, 'HTML');
        return;
    }
    update("affiliates", "description", $caption);
    update("affiliates", "id_media", $photoid);
    sendmessage($from_id, $textbotlang['users']['affiliates']['insertbanner'], $affiliates, 'HTML');
    step('home', $from_id);
} elseif ($text == $textbotlang['Admin']['affiliate']['porsantafterbuy']) {
    $marzbancommission = select("affiliates", "*", null, null, "select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['commission'], $keyboardcommission, 'HTML');
} elseif ($datain == "oncommission") {
    update("affiliates", "status_commission", "offcommission");
    $marzbancommission = select("affiliates", "*", null, null, "select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['commissionStatusOff'], $keyboardcommission);
} elseif ($datain == "offcommission") {
    update("affiliates", "status_commission", "oncommission");
    $marzbancommission = select("affiliates", "*", null, null, "select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['commissionStatuson'], $keyboardcommission);
} elseif ($text == $textbotlang['Admin']['affiliate']['gift']) {
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['Discountaffiliates'], $keyboardDiscountaffiliates, 'HTML');
} elseif ($datain == "onDiscountaffiliates") {
    update("affiliates", "Discount", "offDiscountaffiliates");
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['DiscountaffiliatesStatusOff'], $keyboardDiscountaffiliates);
} elseif ($datain == "offDiscountaffiliates") {
    update("affiliates", "Discount", "onDiscountaffiliates");
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['DiscountaffiliatesStatuson'], $keyboardDiscountaffiliates);
}
if ($text == $textbotlang['Admin']['affiliate']['giftstart']) {
    sendmessage($from_id, $textbotlang['users']['affiliates']['priceDiscount'], $backadmin, 'HTML');
    step('getdiscont', $from_id);
} elseif ($user['step'] == "getdiscont") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['invalidvalue'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['affiliates']['changedpriceDiscount'], $affiliates, 'HTML');
    update("affiliates", "price_Discount", $text);
    step('home', $from_id);
} elseif (preg_match('/rejectremoceserviceadmin-(\w+)/', $datain, $dataget)) {
    $usernamepanel = $dataget[1];
    $requestcheck = select("cancel_service", "*", "username", $usernamepanel, "select");
    if ($requestcheck['status'] == "accept" || $requestcheck['status'] == "reject") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['stateus']['residaccepted'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
        return;
    }
    step("descriptionsrequsts", $from_id);
    update("user", "Processing_value", $usernamepanel, "id", $from_id);
    sendmessage($from_id,$textbotlang['users']['stateus']['rejectrequest'], $backuser, 'HTML');

} elseif ($user['step'] == "descriptionsrequsts") {
    sendmessage($from_id, $textbotlang['users']['stateus']['acceptrequestnote'], $keyboardadmin, 'HTML');
    $nameloc = select("invoice", "*", "username", $user['Processing_value'], "select");
    update("cancel_service", "status", "reject", "username", $user['Processing_value']);
    update("cancel_service", "description", $text, "username", $user['Processing_value']);
    step("home", $from_id);
    sendmessage($nameloc['id_user'], sprintf($textbotlang['users']['stateus']['rejectsendtouser'],$user['Processing_value'],$text), null, 'HTML');

} elseif (preg_match('/remoceserviceadmin-(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $requestcheck = select("cancel_service", "*", "username", $username, "select");
    if ($requestcheck['status'] == "accept" || $requestcheck['status'] == "reject") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' =>$textbotlang['users']['stateus']['residaccepted'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
        return;
    }
    step("getpricerequests", $from_id);
    update("user", "Processing_value", $username, "id", $from_id);
    sendmessage($from_id, $textbotlang['users']['stateus']['getpriceforadd'], $backuser, 'HTML');

} elseif ($user['step'] == "getpricerequests") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['invalidvalue'], null, 'HTML');
    }
    $nameloc = select("invoice", "*", "username", $user['Processing_value'], "select");
    if ($nameloc['price_product'] < $text) {
        sendmessage($from_id, $textbotlang['Admin']['maxvalue'], $backuser, 'HTML');
        return;
    }
    sendmessage($from_id,$textbotlang['users']['stateus']['acceptrequestnote'], $keyboardadmin, 'HTML');
    step("home", $from_id);
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $user['Processing_value']);
    if (isset ($DataUserOut['status'])) {
        $ManagePanel->RemoveUser($marzban_list_get['name_panel'], $user['Processing_value']);
    }
    update("cancel_service", "status", "accept", "username", $user['Processing_value']);
    update("invoice", "status", "removedbyadmin", "username", $user['Processing_value']);
    step("home", $from_id);
    sendmessage($nameloc['id_user'],sprintf($textbotlang['users']['stateus']['acceptrequest'],$user['Processing_value']), null, 'HTML');
    $pricecancel = number_format(intval($text));
    if (intval($text) != 0) {
        $Balance_id_cancel = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$nameloc['id_user']}' LIMIT 1"));
        $Balance_id_cancel_fee = intval($Balance_id_cancel['Balance']) + intval($text);
        update("user", "Balance", $Balance_id_cancel_fee, "id", $nameloc['id_user']);
        sendmessage($nameloc['id_user'],sprintf($textbotlang['users']['stateus']['addedbalanceremove'],$pricecancel), null, 'HTML');
    }
    if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], sprintf($textbotlang['Admin']['Report']['reportremove'],$from_id,$pricecancel,$username,$nameloc['id_user']), null, 'HTML');
    }
}
if ($text == $textbotlang['Admin']['managepanel']['keyboardpanel']['on_hold_status']) {
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($panel['onholdstatus'] == null) {
        update("marzban_panel", "onholdstatus", "offonhold", "name_panel", $user['Processing_value']);
    }
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $onhold_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['onholdstatus'], 'callback_data' => $panel['onholdstatus']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['onhold'], $onhold_Status, 'HTML');
}
if ($datain == "ononhold") {
    update("marzban_panel", "onholdstatus", "offonhold", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $onhold_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['onholdstatus'], 'callback_data' => $panel['onholdstatus']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['offstatus'], $onhold_Status);
} elseif ($datain == "offonhold") {
    update("marzban_panel", "onholdstatus", "ononhold", "name_panel", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $onhold_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $panel['onholdstatus'], 'callback_data' => $panel['onholdstatus']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['onstatus'], $onhold_Status);
}
if ($text == $textbotlang['Admin']['keyboardadmin']['settingscron']) {
    if(!(function_exists('shell_exec') && is_callable('shell_exec'))){
        $crontest = "*/15 * * * * curl https://$domainhosts/cron/configtest.php";
        $cronvolume = "*/1 * * * *  curl https://$domainhosts/cron/cronvolume.php";
        $crontime = "*/1 * * * *  curl https://$domainhosts/cron/cronday.php";
        $cronremove = "*/1 * * * *  curl https://$domainhosts/cron/removeexpire.php";
        sendmessage($from_id, sprintf($textbotlang['Admin']['cron']['active_manual'],$crontest,$cronvolume,$crontime,$cronremove), null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardcronjob, 'HTML');
}
if($text == $textbotlang['Admin']['cron']['test']['active']){
    sendmessage($from_id,$textbotlang['Admin']['cron']['test']['dec'], null, 'HTML');
    $phpFilePath = "https://$domainhosts/cron/configtest.php";
    $cronCommand = "*/15 * * * * curl $phpFilePath";
    $existingCronCommands = shell_exec('crontab -l');
    if (strpos($existingCronCommands, $cronCommand) === false) {
        $command = "(crontab -l ; echo '$cronCommand') | crontab -";
        shell_exec($command);
    }
}
if($text == $textbotlang['Admin']['cron']['test']['disable']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['test']['disabled'], null, 'HTML');
    $currentCronJobs = shell_exec("crontab -l");
    $jobToRemove = "*/15 * * * * curl https://$domainhosts/cron/configtest.php";
    $newCronJobs = preg_replace('/'.preg_quote($jobToRemove, '/').'/', '', $currentCronJobs);
    file_put_contents('/tmp/crontab.txt', $newCronJobs);
    shell_exec('crontab /tmp/crontab.txt');
    unlink('/tmp/crontab.txt');
}
if($text == $textbotlang['Admin']['cron']['volume']['active']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['volume']['dec'], null, 'HTML');
    $phpFilePath = "https://$domainhosts/cron/cronvolume.php";
    $cronCommand = "*/1 * * * * curl $phpFilePath";
    $existingCronCommands = shell_exec('crontab -l');
    if (strpos($existingCronCommands, $cronCommand) === false) {
        $command = "(crontab -l ; echo '$cronCommand') | crontab -";
        shell_exec($command);
    }
}
if($text == $textbotlang['Admin']['cron']['volume']['disable']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['test']['disabled'], null, 'HTML');
    $currentCronJobs = shell_exec("crontab -l");
    $jobToRemove = "*/1 * * * * curl https://$domainhosts/cron/cronvolume.php";
    $newCronJobs = preg_replace('/'.preg_quote($jobToRemove, '/').'/', '', $currentCronJobs);
    file_put_contents('/tmp/crontab.txt', $newCronJobs);
    shell_exec('crontab /tmp/crontab.txt');
    unlink('/tmp/crontab.txt');
}
if($text == $textbotlang['Admin']['cron']['time']['active']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['time']['dec'], null, 'HTML');
    $phpFilePath = "https://$domainhosts/cron/cronday.php";
    $cronCommand = "*/1 * * * * curl $phpFilePath";
    $existingCronCommands = shell_exec('crontab -l');
    if (strpos($existingCronCommands, $cronCommand) === false) {
        $command = "(crontab -l ; echo '$cronCommand') | crontab -";
        shell_exec($command);
    }
}
if($text == $textbotlang['Admin']['cron']['time']['disable']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['test']['disabled'], null, 'HTML');
    $currentCronJobs = shell_exec("crontab -l");
    $jobToRemove = "*/1 * * * * curl https://$domainhosts/cron/cronday.php";
    $newCronJobs = preg_replace('/'.preg_quote($jobToRemove, '/').'/', '', $currentCronJobs);
    file_put_contents('/tmp/crontab.txt', $newCronJobs);
    shell_exec('crontab /tmp/crontab.txt');
    unlink('/tmp/crontab.txt');
}
if($text == $textbotlang['Admin']['cron']['remove']['active']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['remove']['dec'], null, 'HTML');
    $phpFilePath = "https://$domainhosts/cron/removeexpire.php";
    $cronCommand = "*/1 * * * * curl $phpFilePath";
    $existingCronCommands = shell_exec('crontab -l');
    if (strpos($existingCronCommands, $cronCommand) === false) {
        $command = "(crontab -l ; echo '$cronCommand') | crontab -";
        shell_exec($command);
    }
}
if($text == $textbotlang['Admin']['cron']['remove']['disable']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['test']['disabled'], null, 'HTML');
    $currentCronJobs = shell_exec("crontab -l");
    $jobToRemove = "*/1 * * * * curl https://$domainhosts/cron/removeexpire.php";
    $newCronJobs = preg_replace('/'.preg_quote($jobToRemove, '/').'/', '', $currentCronJobs);
    file_put_contents('/tmp/crontab.txt', $newCronJobs);
    shell_exec('crontab /tmp/crontab.txt');
    unlink('/tmp/crontab.txt');
}
if ($text == $textbotlang['Admin']['keyboardadmin']['user_search']) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockUserId'], $backadmin, 'HTML');
    step('show_infos', $from_id);
} elseif ($user['step'] == "show_infos") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $date = date("Y-m-d");
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn') AND id_user = :id_user");
    $stmt->bindParam(':id_user', $text);
    $stmt->execute();
    $dayListSell = $stmt->rowCount();
    $stmt = $pdo->prepare("SELECT SUM(price) FROM Payment_report WHERE payment_Status = 'paid' AND id_user = :id_user");
    $stmt->bindParam(':id_user', $text);
    $stmt->execute();
    $balanceall = $stmt->fetch(PDO::FETCH_ASSOC)['SUM(price)'];
    $stmt = $pdo->prepare("SELECT SUM(price_product) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn') AND id_user = :id_user");
    $stmt->bindParam(':id_user', $text);
    $stmt->execute();
    $subbuyuser = $stmt->fetch(PDO::FETCH_ASSOC)['SUM(price_product)'];
    $user = select("user","*","id",$text,"select");
    $roll_Status = [
        '1' => $textbotlang['Admin']['ManageUser']['Acceptedphone'],
        '0' => $textbotlang['Admin']['ManageUser']['Failedphone'],
    ][$user['roll_Status']];
    if($subbuyuser == null )$subbuyuser = 0;
    $keyboardmanage = [
        'inline_keyboard' => [
            [['text' => $textbotlang['Admin']['ManageUser']['addbalanceuser'], 'callback_data' => "addbalanceuser_" . $text], ['text' => $textbotlang['Admin']['ManageUser']['lowbalanceuser'], 'callback_data' => "lowbalanceuser_" . $text],],
            [['text' => $textbotlang['Admin']['ManageUser']['banuserlist'], 'callback_data' => "banuserlist_" . $text], ['text' => $textbotlang['Admin']['ManageUser']['unbanuserlist'], 'callback_data' => "unbanuserr_" . $text]],
            [['text' => $textbotlang['Admin']['ManageUser']['confirmnumber'], 'callback_data' => "confirmnumber_" . $text]],
            [['text' => $textbotlang['Admin']['getlimitusertest']['setlimitbtn'], 'callback_data' => "limitusertest_" . $text]],
            [['text' => $textbotlang['Admin']['ManageUser']['verify'], 'callback_data' => "verify_" . $text],['text' =>$textbotlang['Admin']['ManageUser']['removeverify'], 'callback_data' => "verifyun_" . $text]],
            [['text' => $textbotlang['Admin']['ManageUser']['vieworderuser'], 'callback_data' => "vieworderall_" . $text],['text' => $textbotlang['Admin']['ManageUser']['addorder'], 'callback_data' => "addordermanualـ" . $text]],
        ]
    ];
    $keyboardmanage = json_encode($keyboardmanage);
    $user['Balance'] = number_format($user['Balance']);
    $lastmessage = jdate('Y/m/d H:i:s',$user['last_message_time']);
    sendmessage($from_id, sprintf($textbotlang['Admin']['ManageUser']['infouser'],$user['User_Status'],$user['username'],$text,$text,$lastmessage,$user['limit_usertest'],$roll_Status,$user['number'],$user['Balance'],$dayListSell,$balanceall,$subbuyuser,$user['affiliatescount'],$user['affiliates'],$user['verify']), $keyboardmanage, 'HTML');
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}
if($text == $textbotlang['Admin']['cron']['remove']['timeset']){
    sendmessage($from_id, $textbotlang['Admin']['cron']['remove']['dectime'], $backadmin, 'HTML');
    step("gettimeremove",$from_id);
}elseif($user['step'] == "gettimeremove"){
    if (!ctype_digit($text)) {
        sendmessage($from_id,$textbotlang['Admin']['cron']['remove']['invalidtime'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['cron']['remove']['timeseted'], $keyboardcronjob, 'HTML');
    step("home",$from_id);
    update("setting","removedayc",$text,null,null);
}
if ($text == $textbotlang['users']['stateus']['manageService']) {
    sendmessage($from_id, $textbotlang['users']['stateus']['manageServicedec'], $backadmin, 'HTML');
    step('getservceid',$from_id);
} elseif ($user['step'] == "getservceid") {
    $userdata = getuserm($text,$user['Processing_value']);
    if(isset($userdata['detail']) and $userdata['detail'] == "User not found"){
        sendmessage($from_id,$textbotlang['Admin']['managepanel']['keyboardpanel']['usernotfount'], null, 'HTML');
        return;
    }
    update("marzban_panel","proxies",json_encode($userdata['service_ids']),"name_panel",$user['Processing_value']);
    step("home",$from_id);
    sendmessage($from_id,$textbotlang['Admin']['managepanel']['setsetting'], $optionMarzneshin, 'HTML');
}
elseif($text == $textbotlang['Admin']['Help']['edithelp']){
    sendmessage($from_id,$textbotlang['Admin']['Help']['selecthelpforedit'], $json_list_help, 'HTML');
    step("getnameforedite",$from_id);
}elseif($user['step'] == "getnameforedite"){
    sendmessage($from_id, $textbotlang['users']['selectoption'], $helpedit, 'HTML');
    update("user","Processing_value",$text, "id",$from_id);
    step("home",$from_id);

}
elseif($text == $textbotlang['Admin']['Help']['change']['name']) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['change']['sendnewname'], $backadmin, 'HTML');
    step('changenamehelp', $from_id);
}elseif($user['step'] == "changenamehelp") {
    if(strlen($text) >= 150){
        sendmessage($from_id, $textbotlang['Admin']['Help']['change']['namemax'], null, 'HTML');
        return;
    }
    update("help","name_os",$text,"name_os",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Help']['change']['updated'], $json_list_helpkey, 'HTML');
    step('home', $from_id);
}elseif($text == $textbotlang['Admin']['Help']['change']['dec']) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['change']['newdec'], $backadmin, 'HTML');
    step('changedeshelp', $from_id);
}elseif($user['step'] == "changedeshelp") {
    update("help","Description_os",$text,"name_os",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Help']['change']['updated'], $helpedit, 'HTML');
    step('home', $from_id);
}
elseif($text == $textbotlang['Admin']['Help']['change']['editmedia']) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['change']['editmedianew'], $backadmin, 'HTML');
    step('changemedia', $from_id);
}elseif($user['step'] == "changemedia") {
    if ($photo) {
        if(isset($photoid))update("help","Media_os",$photoid, "name_os",$user['Processing_value']);
        update("help","type_Media_os","photo", "name_os",$user['Processing_value']);
    }elseif($video) {
        if(isset($videoid))update("help","Media_os",$videoid, "name_os",$user['Processing_value']);
        update("help","type_Media_os","video", "name_os",$user['Processing_value']);
    }
    sendmessage($from_id, $textbotlang['Admin']['Help']['change']['updated'], $helpedit, 'HTML');
    step('home', $from_id);
}elseif($text == $textbotlang['Admin']['managepanel']['setinbound']){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['setinbounddec'], $backadmin, 'HTML');
    step("setinboundandprotocol",$from_id);
}elseif($user['step'] == "setinboundandprotocol"){
    $panel = select("marzban_panel","*","name_panel",$user['Processing_value'],"select");
    if($panel['type'] == "marzban"){
    $DataUserOut = getuser($text,$user['Processing_value']);
    if ((isset($DataUserOut['msg']) && $DataUserOut['msg'] == "User not found") or !isset($DataUserOut['proxies'])) {
        sendmessage($from_id,$textbotlang['users']['stateus']['usernotfound'], null, 'html');
        return;
    }
    foreach ($DataUserOut['proxies'] as $key => &$value){
        if($key == "shadowsocks"){
            unset($DataUserOut['proxies'][$key]['password']);
        }
        elseif($key == "trojan"){
            unset($DataUserOut['proxies'][$key]['password']);
        }
        else{
            unset($DataUserOut['proxies'][$key]['id']);
        }
        if(count($DataUserOut['proxies'][$key]) == 0){
            $DataUserOut['proxies'][$key] = new stdClass();
        }
    }
    update("marzban_panel","inbounds",json_encode($DataUserOut['inbounds']),"name_panel",$user['Processing_value']);
    update("marzban_panel","proxies",json_encode($DataUserOut['proxies']),"name_panel",$user['Processing_value']);
    }else{
        $data = GetClientsS_UI($text,$panel['name_panel']);
        if(count($data) == 0){
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['keyboardpanel']['usernotfount'], $options_ui, 'HTML');
            return;
        }
        $servies = [];
        foreach ($data['inbounds'] as $service){
        $servies[] = $service;
            }
        update("marzban_panel","proxies",json_encode($servies,true),"name_panel",$user['Processing_value']);  
        }
    sendmessage($from_id,$textbotlang['Admin']['managepanel']['setedinbound'], $optionMarzban, 'HTML');
    step("home",$from_id);
}elseif($text == $textbotlang['Admin']['keyboardadmin']['seetingstatus']) {
    if($setting['Bot_Status'] == "✅  ربات روشن است") {
        update("setting","Bot_Status","1");
    }elseif($setting['Bot_Status'] == "❌ ربات خاموش است") {
        update("setting","Bot_Status","0");
    }
    
    if($setting['roll_Status'] == "✅ تایید قانون روشن است") {
        update("setting","roll_Status","1");
    }elseif($setting['roll_Status'] == "❌ تایید قوانین خاموش است") {
        update("setting","roll_Status","0");
    }
    
    if($setting['NotUser'] == "onnotuser") {
        update("setting","NotUser","1");
    }elseif($setting['NotUser'] == "offnotuser") {
        update("setting","NotUser","0");
    }
    
    if($setting['help_Status'] == "✅ آموزش فعال است") {
        update("setting","help_Status","1");
    }elseif($setting['help_Status'] == "❌ آموزش غیرفعال است") {
        update("setting","help_Status","0");
    }
    
    if($setting['get_number'] == "✅ تایید شماره موبایل روشن است") {
        update("setting","get_number","1");
    }elseif($setting['get_number'] == "❌ احرازهویت شماره تماس غیرفعال است") {
        update("setting","get_number","0");
    }
    
    if($setting['iran_number'] == "✅ احرازشماره ایرانی روشن است") {
        update("setting","iran_number","1");
    }elseif($setting['iran_number'] == "❌ بررسی شماره ایرانی غیرفعال است") {
        update("setting","iran_number","0");
    }
    if(!(function_exists('shell_exec') && is_callable('shell_exec'))){
        $cronstatus = 1;
        $cronCommand = "*/4 * * * * curl https://$domainhosts/cron/croncard.php";
       sendmessage($from_id, sprintf($textbotlang['Admin']['cron']['active_manual_card'],$cronCommand), null, 'HTML');
    }else{
    $cronCommand = "*/4 * * * * curl https://$domainhosts/cron/croncard.php";
    $existingCronCommands = shell_exec('crontab -l');
    if (strpos($existingCronCommands, $cronCommand) === false) {
        $cronstatus = 0;
    }else{
        $cronstatus = 1;
    }
    }
    $setting = select("setting", "*");
    $name_status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['Bot_Status']];
    $roll_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['roll_Status']];
    $NotUser_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['NotUser']];
    $help_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['help_Status']];
    $get_number_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['get_number']];
    $get_number_iran   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['iran_number']];
    $statusv_verify   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['status_verify']];
    $statusv_category  = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statuscategory']];
    $status_Automatic_confirmation  = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$cronstatus];
    $status_copy_cart  = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['copy_cart']];
    $Bot_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['Status']['statussubject'], 'callback_data' => "subjectde"],
                ['text' => $textbotlang['Admin']['Status']['subject'], 'callback_data' => "subject"],
            ],
            [
                ['text' => $name_status, 'callback_data' => "editstsuts-statusbot-{$setting['Bot_Status']}"],
                ['text' => $textbotlang['Admin']['Status']['stautsbot'], 'callback_data' => "statusbot"],
            ],[
                ['text' => $roll_Status, 'callback_data' => "editstsuts-roll_Status-{$setting['roll_Status']}"],
                ['text' => $textbotlang['users']['Rulesbtn'], 'callback_data' => "roll_Status"],
            ],[
                ['text' => $NotUser_Status, 'callback_data' => "editstsuts-NotUser-{$setting['NotUser']}"],
                ['text' => $textbotlang['users']['maangeuser'], 'callback_data' => "NotUser"],
            ],[
                ['text' => $help_Status, 'callback_data' => "editstsuts-help_Status-{$setting['help_Status']}"],
                ['text' => $textbotlang['Admin']['Help']['statushelp'], 'callback_data' => "help_Status"],
            ],[
                ['text' => $get_number_Status, 'callback_data' => "editstsuts-get_number-{$setting['get_number']}"],
                ['text' => $textbotlang['Admin']['ManageUser']['verifynumber'], 'callback_data' => "get_number"],
            ],[
                ['text' => $get_number_iran, 'callback_data' => "editstsuts-iran_number-{$setting['iran_number']}"],
                ['text' => $textbotlang['Admin']['ManageUser']['verifynumberirani'], 'callback_data' => "iran_number"],
            ],[
                ['text' => $statusv_verify, 'callback_data' => "editstsuts-verify-{$setting['status_verify']}"],
                ['text' => $textbotlang['Admin']['ManageUser']['verify'], 'callback_data' => "status_verify"],
            ],[
                ['text' => $statusv_category, 'callback_data' => "editstsuts-category-{$setting['statuscategory']}"],
                ['text' => $textbotlang['Admin']['category']['status'] , 'callback_data' => "statuscategory"],
            ],[
                ['text' => $status_Automatic_confirmation, 'callback_data' => "editstsuts-Automatic_confirmation-$cronstatus"],
                ['text' => $textbotlang['Admin']['Automatic_confirmation']['title'] , 'callback_data' => "Automatic_confirmation"],
            ],[
                ['text' => $status_copy_cart, 'callback_data' => "editstsuts-copycart-{$setting['copy_cart']}"],
                ['text' => $textbotlang['users']['moeny']['copy_cart_status'], 'callback_data' => "copycart"],
            ]
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status, 'HTML');
}
elseif(preg_match('/^editstsuts-(.*)-(.*)/', $datain, $dataget)) {
    $type = $dataget[1];
    $value = $dataget[2];
    if($type == "statusbot"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","Bot_Status",$valuenew);
    }elseif($type == "roll_Status"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","roll_Status",$valuenew);
    }elseif($type == "NotUser"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","NotUser",$valuenew);
    }elseif($type == "help_Status"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","help_Status",$valuenew);
    }elseif($type == "get_number"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","get_number",$valuenew);
    }elseif($type == "iran_number"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","iran_number",$valuenew);
    }elseif($type == "verify"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","status_verify",$valuenew);
    }elseif($type == "category"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","statuscategory",$valuenew);
    }elseif($type == "copycart"){
        if($value == "1"){
            $valuenew = "0";
        }else{
            $valuenew = "1";
        }
        update("setting","copy_cart",$valuenew);
    }elseif($type == "Automatic_confirmation"){
        if(!(function_exists('shell_exec') && is_callable('shell_exec'))){
        $cronstatus = 1;
        $cronCommand = "*/4 * * * * curl https://$domainhosts/cron/croncard.php";
       sendmessage($from_id, sprintf($textbotlang['Admin']['cron']['active_manual_card'],$cronCommand), null, 'HTML');
    }else{
        if($value == "1"){
            $currentCronJobs = shell_exec("crontab -l");
            $jobToRemove = "*/4 * * * * curl https://$domainhosts/cron/croncard.php";
            $newCronJobs = preg_replace('/'.preg_quote($jobToRemove, '/').'/', '', $currentCronJobs);
            file_put_contents('/tmp/crontab.txt', $newCronJobs);
            shell_exec('crontab /tmp/crontab.txt');
            unlink('/tmp/crontab.txt');
        }else{
            $existingCronCommands = shell_exec('crontab -l');
            $phpFilePath = "https://$domainhosts/cron/croncard.php";
            $cronCommand = "*/4 * * * * curl $phpFilePath";
            if (strpos($existingCronCommands, $cronCommand) === false) {
                $command = "(crontab -l ; echo '$cronCommand') | crontab -";
                shell_exec($command);
            }
        }
    }
    }
    $cronCommand = "*/4 * * * * curl https://$domainhosts/cron/croncard.php";
    if(!(function_exists('shell_exec') && is_callable('shell_exec'))){
        $cronstatus = 1;
    }else{
        $existingCronCommands = shell_exec('crontab -l');
        if (strpos($existingCronCommands, $cronCommand) === false) {
            $cronstatus = 0;
        }else{
            $cronstatus = 1;
        }
    }
    $setting = select("setting", "*");
    $name_status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['Bot_Status']];
    $roll_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['roll_Status']];
    $NotUser_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['NotUser']];
    $help_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['help_Status']];
    $get_number_Status   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['get_number']];
    $get_number_iran   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['iran_number']];
    $statusv_verify   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['status_verify']];
    $statusv_category   = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statuscategory']];
    $status_Automatic_confirmation  = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$cronstatus];
    $status_copy_cart  = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['copy_cart']];
    $Bot_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['Status']['statussubject'], 'callback_data' => "subjectde"],
                ['text' => $textbotlang['Admin']['Status']['subject'], 'callback_data' => "subject"],
            ],
            [
                ['text' => $name_status, 'callback_data' => "editstsuts-statusbot-{$setting['Bot_Status']}"],
                ['text' => $textbotlang['Admin']['Status']['stautsbot'], 'callback_data' => "statusbot"],
            ],[
                ['text' => $roll_Status, 'callback_data' => "editstsuts-roll_Status-{$setting['roll_Status']}"],
                ['text' => $textbotlang['users']['Rulesbtn'], 'callback_data' => "roll_Status"],
            ],[
                ['text' => $NotUser_Status, 'callback_data' => "editstsuts-NotUser-{$setting['NotUser']}"],
                ['text' => $textbotlang['users']['maangeuser'], 'callback_data' => "NotUser"],
            ],[
                ['text' => $help_Status, 'callback_data' => "editstsuts-help_Status-{$setting['help_Status']}"],
                ['text' => $textbotlang['Admin']['Help']['statushelp'], 'callback_data' => "help_Status"],
            ],[
                ['text' => $get_number_Status, 'callback_data' => "editstsuts-get_number-{$setting['get_number']}"],
                ['text' => $textbotlang['Admin']['ManageUser']['verifynumber'], 'callback_data' => "get_number"],
            ],[
                ['text' => $get_number_iran, 'callback_data' => "editstsuts-iran_number-{$setting['iran_number']}"],
                ['text' => $textbotlang['Admin']['ManageUser']['verifynumberirani'], 'callback_data' => "iran_number"],
            ],[
                ['text' => $statusv_verify, 'callback_data' => "editstsuts-verify-{$setting['status_verify']}"],
                ['text' => $textbotlang['Admin']['ManageUser']['verify'], 'callback_data' => "status_verify"],
            ],[
                ['text' => $statusv_category, 'callback_data' => "editstsuts-category-{$setting['statuscategory']}"],
                ['text' => $textbotlang['Admin']['category']['status'] , 'callback_data' => "statuscategory"],
            ],[
                ['text' => $status_Automatic_confirmation, 'callback_data' => "editstsuts-Automatic_confirmation-$cronstatus"],
                ['text' => $textbotlang['Admin']['Automatic_confirmation']['title'] , 'callback_data' => "Automatic_confirmation"],
            ],[
                ['text' => $status_copy_cart, 'callback_data' => "editstsuts-copycart-{$setting['copy_cart']}"],
                ['text' => $textbotlang['users']['moeny']['copy_cart_status'], 'callback_data' => "copycart"],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status);
}elseif (preg_match('/verify_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    $userunverify = select("user", "*", "id", $iduser, "select");
    if ($userunverify['verify'] == "1") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['verifyeduser'], $backadmin, 'HTML');
        return;
    }
    update("user", "verify", "1", "id", $iduser);
    sendmessage($from_id,$textbotlang['Admin']['ManageUser']['verifyeduser'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}elseif (preg_match('/verifyun_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    $userunverify = select("user", "*", "id", $iduser, "select");
    if ($userunblock['verify'] == "0") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['verifyed'], $backadmin, 'HTML');
        return;
    }
    update("user", "verify", "0", "id", $iduser);
    sendmessage($from_id,$textbotlang['Admin']['ManageUser']['unverifyed'], $keyboardadmin, 'HTML');
    step('home', $from_id);
}elseif($text == $textbotlang['Admin']['category']['add']){
    sendmessage($from_id,$textbotlang['Admin']['category']['getname'], $backadmin, 'HTML');
    step("getremarkcategory",$from_id);
}elseif($user['step'] == "getremarkcategory"){
    sendmessage($from_id,$textbotlang['Admin']['category']['addedcategry'], $shopkeyboard, 'HTML');
    step("home",$from_id);
    $stmt = $pdo->prepare("INSERT INTO category (remark) VALUES (?)");
    $stmt->bindParam(1, $text);
    $stmt->execute();
}elseif($text == $textbotlang['Admin']['category']['remove']){
    sendmessage($from_id,$textbotlang['Admin']['category']['getcatgory'],KeyboardCategory(), 'HTML');
    step("removecategory",$from_id);
}elseif($user['step'] == "removecategory"){
    sendmessage($from_id,$textbotlang['Admin']['category']['removedcategory'], $shopkeyboard, 'HTML');
    step("home",$from_id);   
    $stmt = $pdo->prepare("DELETE FROM category WHERE remark = :remark ");
    $stmt->bindParam(':remark', $text);
    $stmt->execute();
}
elseif($text == $textbotlang['Admin']['ManageUser']['searchorder']){
    sendmessage($from_id,$textbotlang['Admin']['ManageUser']['ViewOrder'], $backadmin, 'HTML');
    step("getidfororder",$from_id);
}elseif($user['step'] == "getidfororder"){
    $OrderUser = select("invoice", "*", "username", $text, "select");
    if(!$OrderUser){
            sendmessage($from_id, $textbotlang['Admin']['ManageUser']['OrderNotFound'], null, 'HTML');
            return;
    }
    $timeacc = jdate('Y/m/d H:i:s', $OrderUser['time_sell']);
    sendmessage($from_id, sprintf($textbotlang['Admin']['ManageUser']['Datails'],$OrderUser['id_invoice'],$OrderUser['Status'],$OrderUser['id_user'],$OrderUser['username'],$OrderUser['Service_location'],$OrderUser['name_product'],$OrderUser['price_product'],$OrderUser['Volume'],$OrderUser['Service_time'],$timeacc), $User_Services, 'HTML');
    step('home', $from_id);   
}elseif(preg_match('/addordermanualـ(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    savedata("clear","userid",$iduser);
    sendmessage($from_id, $textbotlang['Admin']['addorder']['onestep'] , $backadmin, 'HTML');
    step('getusernameconfig',$from_id);
}elseif($user['step'] == "getusernameconfig"){
    $text = strtolower($text);
    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['users']['stateus']['Invalidusername'], $backuser, 'html');
        return;
    }
    if(in_array($text,$usernameinvoice)){
        sendmessage($from_id,$textbotlang['Admin']['addorder']['user_exits'], null, 'HTML');
        return;
    }
    savedata("save","username",$text);
    sendmessage($from_id, $textbotlang['Admin']['addorder']['getname_panel'] , $json_list_marzban_panel, 'HTML');
    step('getnamepanelconfig',$from_id);
}elseif($user['step'] == "getnamepanelconfig"){
    savedata("save","name_panel",$text);
    sendmessage($from_id, $textbotlang['Admin']['addorder']['get_product'] , $json_list_product_list_admin, 'HTML');
    step('stependforaddorder',$from_id);
}elseif($user['step'] == "stependforaddorder"){
    $userdata = json_decode($user['Processing_value'],true);
    $sql = "SELECT * FROM product  WHERE name_product = :name_product AND (Location = :location OR Location = '/all') LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name_product', $text, PDO::PARAM_STR);
    $stmt->bindParam(':location', $userdata['name_panel'], PDO::PARAM_STR);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $panel = select("marzban_panel","*","name_panel",$userdata['name_panel'],"select");
    $date = time();
    $randomString = bin2hex(random_bytes(2));
    $stmt = $pdo->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status) VALUES (:id_user, :id_invoice, :username, :time_sell, :Service_location, :name_product, :price_product, :Volume, :Service_time, :Status)");
    $Status = "active";
    $stmt->bindParam(':id_user', $userdata['userid'], PDO::PARAM_STR);
    $stmt->bindParam(':id_invoice', $randomString, PDO::PARAM_STR);
    $stmt->bindParam(':username', $userdata['username'], PDO::PARAM_STR);
    $stmt->bindParam(':time_sell', $date, PDO::PARAM_STR);
    $stmt->bindParam(':Service_location', $userdata['name_panel'], PDO::PARAM_STR);
    $stmt->bindParam(':name_product', $info_product['name_product'], PDO::PARAM_STR);
    $stmt->bindParam(':price_product', $info_product['price_product'], PDO::PARAM_STR);
    $stmt->bindParam(':Volume', $info_product['Volume_constraint'], PDO::PARAM_STR);
    $stmt->bindParam(':Service_time', $info_product['Service_time'], PDO::PARAM_STR);
    $stmt->bindParam(':Status', $Status, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['addorder']['added_order'] , $keyboardadmin, 'HTML');
    step('home',$from_id);
}