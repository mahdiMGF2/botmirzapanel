<?php

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} elseif (function_exists('litespeed_finish_request')) {
    litespeed_finish_request();
} else {
    error_log('Neither fastcgi_finish_request nor litespeed_finish_request is available.');
}

ini_set('error_log', 'error_log');
$version = "4.13.1";
date_default_timezone_set('Asia/Tehran');
require_once 'config.php';
require_once 'botapi.php';
require_once 'apipanel.php';
require_once 'jdf.php';
require_once 'keyboard.php';
require_once 'text.php';
require_once 'functions.php';
require_once 'panels.php';
require_once 'vendor/autoload.php';
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
$first_name = sanitizeUserName($first_name);
if(!in_array($Chat_type,["private"]))return;
#-----------telegram_ip_ranges------------#
if (!checktelegramip()) die("Unauthorized access");
#-------------Variable----------#
$users_ids = select("user", "id",null,null,"FETCH_COLUMN");
$setting = select("setting", "*");
$admin_ids = select("admin", "id_admin", null, null, "FETCH_COLUMN");
if(!in_array($from_id,$users_ids) && intval($from_id) != 0){
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['ManageUser']['sendmessageUser'], 'callback_data' => 'Response_' . $from_id],
            ]
        ]
    ]);
    $newuser = sprintf($textbotlang['Admin']['ManageUser']['NewUserMessage'],$first_name,$username,$from_id,$from_id);
    foreach ($admin_ids as $admin) {
        sendmessage($admin, $newuser, $Response, 'html');
    }
}
if (intval($from_id) != 0) {
    if(intval($setting['status_verify']) == 1){
        $verify = 0;
    }else{
        $verify = 1;
    }
    $stmt = $pdo->prepare("INSERT IGNORE INTO user (id, step, limit_usertest, User_Status, number, Balance, pagenumber, username, message_count, last_message_time, affiliatescount, affiliates,verify) VALUES (:from_id, 'none', :limit_usertest_all, 'Active', 'none', '0', '1', :username, '0', '0', '0', '0',:verify)");
    $stmt->bindParam(':verify', $verify);
    $stmt->bindParam(':from_id', $from_id);
    $stmt->bindParam(':limit_usertest_all', $setting['limit_usertest_all']);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}
$user = select("user", "*", "id", $from_id, "select");
if ($user == false) {
    $user = array();
    $user = array(
        'step' => '',
        'Processing_value' => '',
        'User_Status' => '',
        'username' => '',
        'limit_usertest' => '',
        'last_message_time' => '',
        'affiliates' => '',
    );
}
if(($setting['status_verify'] == "1" && intval($user['verify']) == 0) && !in_array($from_id,$admin_ids)){
    sendmessage($from_id,$textbotlang['users']['VerifyUser'], null, 'html');
    return;
};
$channels = array();
$helpdata = select("help", "*");
$datatextbotget = select("textbot", "*", null, null, "fetchAll");
$id_invoice = select("invoice", "id_invoice", null, null, "FETCH_COLUMN");
$channels = select("channels", "*");
$usernameinvoice = select("invoice", "username", null, null, "FETCH_COLUMN");
$code_Discount = select("Discount", "code", null, null, "FETCH_COLUMN");
$users_ids = select("user", "id", null, null, "FETCH_COLUMN");
$marzban_list = select("marzban_panel", "name_panel", null, null, "FETCH_COLUMN");
$name_product = select("product", "name_product", null, null, "FETCH_COLUMN");
$SellDiscount = select("DiscountSell", "codeDiscount", null, null, "FETCH_COLUMN");
$ManagePanel = new ManagePanel();
$datatxtbot = array();
foreach ($datatextbotget as $row) {
    $datatxtbot[] = array(
        'id_text' => $row['id_text'],
        'text' => $row['text']
    );
}

$datatextbot = array(
    'text_usertest' => '',
    'text_Purchased_services' => '',
    'text_support' => '',
    'text_help' => '',
    'text_start' => '',
    'text_bot_off' => '',
    'text_roll' => '',
    'text_fq' => '',
    'text_dec_fq' => '',
    'text_account' => '',
    'text_sell' => '',
    'text_Add_Balance' => '',
    'text_channel' => '',
    'text_Discount' => '',
    'text_Tariff_list' => '',
    'text_dec_Tariff_list' => '',
);
foreach ($datatxtbot as $item) {
    if (isset ($datatextbot[$item['id_text']])) {
        $datatextbot[$item['id_text']] = $item['text'];
    }
}

$existingCronCommands = shell_exec('crontab -l');
$phpFilePath = "https://$domainhosts/cron/sendmessage.php";
$cronCommand = "*/1 * * * * curl $phpFilePath";
if (strpos($existingCronCommands, $cronCommand) === false) {
    $command = "(crontab -l ; echo '$cronCommand') | crontab -";
    shell_exec($command);
}
#---------channel--------------#
$tch = '';
if (isset ($channels['link']) && $from_id != 0) {
    $response = json_decode(file_get_contents('https://api.telegram.org/bot' . $APIKEY . "/getChatMember?chat_id=@{$channels['link']}&user_id=$from_id"));
    $tch = $response->result->status;
}
if ($user['username'] == "none" || $user['username'] == null) {
    update("user", "username", $username, "id", $from_id);
}
#-----------User_Status------------#
if ($user['User_Status'] == "block") {
    $textblock = sprintf($textbotlang['Admin']['ManageUser']['BlockedUser'],$user['description_blocking']);
    sendmessage($from_id, $textblock, null, 'html');
    return;
}
if (strpos($text, "/start ") !== false) {
    if ($user['affiliates'] != 0) {
        sendmessage($from_id, sprintf($textbotlang['users']['affiliates']['affiliateseduser'],$user['affiliates']), null, 'html');
        return;
    }
    $affiliatesvalue = select("affiliates", "*", null, null, "select")['affiliatesstatus'];
    if ($affiliatesvalue == "offaffiliates") {
        sendmessage($from_id, $textbotlang['users']['affiliates']['offaffiliates'], $keyboard, 'HTML');
        return;
    }
    $affiliatesid = str_replace("/start ", "", $text);
    if (ctype_digit($affiliatesid)){
        if (!in_array($affiliatesid, $users_ids)) {
            sendmessage($from_id,$textbotlang['users']['affiliates']['affiliatesyou'], null, 'html');
            return;
        }
        if ($affiliatesid == $from_id) {
            sendmessage($from_id, $textbotlang['users']['affiliates']['invalidaffiliates'], null, 'html');
            return;
        }
        $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
        if ($marzbanDiscountaffiliates['Discount'] == "onDiscountaffiliates") {
            $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
            $Balance_user = select("user", "*", "id", $affiliatesid, "select");
            $Balance_add_user = $Balance_user['Balance'] + $marzbanDiscountaffiliates['price_Discount'];
            update("user", "Balance", $Balance_add_user, "id", $affiliatesid);
            $addbalancediscount = number_format($marzbanDiscountaffiliates['price_Discount'], 0);
            sendmessage($affiliatesid, sprintf($textbotlang['users']['affiliates']['giftuser'],$addbalancediscount,$from_id), null, 'html');
        }
        sendmessage($from_id, $datatextbot['text_start'], $keyboard, 'html');
        $useraffiliates = select("user", "*", "id", $affiliatesid, "select");
        $addcountaffiliates = intval($useraffiliates['affiliatescount']) + 1;
        update("user", "affiliates", $affiliatesid, "id", $from_id);
        update("user", "affiliatescount", $addcountaffiliates, "id", $affiliatesid);
    }
}
$timebot = time();
$TimeLastMessage = $timebot - intval($user['last_message_time']);
if (floor($TimeLastMessage / 60) >= 1) {
    update("user", "last_message_time", $timebot, "id", $from_id);
    update("user", "message_count", "1", "id", $from_id);
} else {
    if (!in_array($from_id, $admin_ids)) {
        $addmessage = intval($user['message_count']) + 1;
        update("user", "message_count", $addmessage, "id", $from_id);
        if ($user['message_count'] >= "35") {
            $User_Status = "block";
            update("user", "User_Status", $User_Status, "id", $from_id);
            update("user", "description_blocking", $textbotlang['users']['spamtext'], "id", $from_id);
            sendmessage($from_id, $textbotlang['users']['spam']['spamedmessage'], null, 'html');
            return;
        }

    }
    if($setting['Bot_Status'] == "✅  ربات روشن است" and !in_array($from_id, $admin_ids)) {
        sendmessage($from_id, $textbotlang['users']['updatingbot'], null, 'html');
        foreach ($admin_ids as $admin) {
            sendmessage($admin, "❌ ادمین عزیز ربات فعال نیست جهت فعالسازی به منوی تنظیمات عمومی > وضعیت قابلیت ها بروید تا رباتتان فعال شود.", null, 'html');
        }
        return;}

}#-----------Channel------------#
if ($datain == "confirmchannel") {
    if (!in_array($tch, ['member', 'creator', 'administrator'])) {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['channel']['notconfirmed'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
    } else {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['channel']['confirmed'], $keyboard, 'html');
    }
    return;
}
if ($channels == false) {
    unset($channels);
    $channels['Channel_lock'] = "off";
    $channels['link'] = $textbotlang['users']['channel']['link'];
}
if (!in_array($tch, ['member', 'creator', 'administrator']) && $channels['Channel_lock'] == "on" && !in_array($from_id, $admin_ids)) {
    $link_channel = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['channel']['text_join'], 'url' => "https://t.me/" . $channels['link']],
            ],
            [
                ['text' => $textbotlang['users']['channel']['confirmjoin'], 'callback_data' => "confirmchannel"],
            ],
        ]
    ]);
    sendmessage($from_id, $datatextbot['text_channel'], $link_channel, 'html');
    return;
}
#-----------roll------------#
if ($setting['roll_Status'] == "1" && $user['roll_Status'] == 0 && $text != "✅ قوانین را می پذیرم" && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_roll'], $confrimrolls, 'html');
    return;
}
if ($text == "✅ قوانین را می پذیرم") {
    sendmessage($from_id, $textbotlang['users']['Rules'], $keyboard, 'html');
    $confrim = true;
    update("user", "roll_Status", $confrim, "id", $from_id);
}

#-----------Bot_Status------------#
if ($setting['Bot_Status'] == "0"  && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_bot_off'], null, 'html');
    return;
}
#-----------clear_data------------#
$stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND status = 'unpaid'");
$stmt->bindParam(':id_user', $from_id);
$stmt->execute();
if($stmt->rowCount() != 0){
    $list_invoice = $stmt->fetchAll();
    foreach ($list_invoice as $invoice){
        $timecurrent = time();
        if(ctype_digit($invoice['time_sell'])){
            $timelast = $timecurrent - $invoice['time_sell'];
            if($timelast > 86400){
                $stmt = $pdo->prepare("DELETE FROM invoice WHERE id_invoice = :id_invoice ");
                $stmt->bindParam(':id_invoice', $invoice['id_invoice']);
                $stmt->execute();
            }
        }
    }
}
#-----------/start------------#
if ($text == "/start") {
    update("user","Processing_value","0", "id",$from_id);
    update("user","Processing_value_one","0", "id",$from_id);
    update("user","Processing_value_tow","0", "id",$from_id);
    sendmessage($from_id, $datatextbot['text_start'], $keyboard, 'html');
    step('home', $from_id);
    return;
}
#-----------back------------#
if ($text == $textbotlang['users']['backhome'] || $datain == "backuser") {
    update("user","Processing_value","0", "id",$from_id);
    update("user","Processing_value_one","0", "id",$from_id);
    update("user","Processing_value_tow","0", "id",$from_id);
    if ($datain == "backuser")
        deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['users']['back'], $keyboard, 'html');
    step('home', $from_id);
    return;
}
#-----------get_number------------#
if ($user['step'] == 'get_number') {
    if (empty ($user_phone)) {
        sendmessage($from_id, $textbotlang['users']['number']['false'], $request_contact, 'html');
        return;
    }
    if ($contact_id != $from_id) {
        sendmessage($from_id, $textbotlang['users']['number']['Warning'], $request_contact, 'html');
        return;
    }
    if ($setting['iran_number'] == "1" && !preg_match("/989[0-9]{9}$/", $user_phone)) {
        sendmessage($from_id, $textbotlang['users']['number']['erroriran'], $request_contact, 'html');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['number']['active'], $keyboard, 'html');
    update("user", "number", $user_phone, "id", $from_id);
    step('home', $from_id);
}

#-----------Purchased services------------#
if ($text == $datatextbot['text_Purchased_services'] || $datain == "backorder" || $text == "/services") {
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn')");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $invoices = $stmt->rowCount();
    if ($invoices == 0 && $setting['NotUser'] == "offnotuser") {
        sendmessage($from_id, $textbotlang['users']['sell']['service_not_available'], null, 'html');
        return;
    }
    update("user", "pagenumber", "1", "id", $from_id);
    $page = 1;
    $items_per_page = 5;
    $start_index = ($page - 1) * $items_per_page;
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn') ORDER BY username ASC LIMIT $start_index, $items_per_page");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "🌟" . $row['username'] . "🌟",
                'callback_data' => "product_" . $row['username']
            ],
        ];
    }
    $usernotlist = [
        [
            'text' => $textbotlang['Admin']['Status']['notusenameinbot'],
            'callback_data' => 'usernotlist'
        ]
    ];
    $pagination_buttons = [
        [
            'text' => $textbotlang['users']['page']['next'],
            'callback_data' => 'next_page'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_page'
        ]
    ];
    if ($setting['NotUser'] == "1") {
        $keyboardlists['inline_keyboard'][] = $usernotlist;
    }
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    if ($datain == "backorder") {
        Editmessagetext($from_id, $message_id, $textbotlang['users']['sell']['service_sell'], $keyboard_json);
    } else {
        sendmessage($from_id, $textbotlang['users']['sell']['service_sell'], $keyboard_json, 'html');
    }
}
if ($datain == "usernotlist") {
    sendmessage($from_id, $textbotlang['users']['stateus']['SendUsername'], $backuser, 'html');
    step('getusernameinfo', $from_id);
}
if ($user['step'] == "getusernameinfo") {
    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['users']['stateus']['Invalidusername'], $backuser, 'html');
        return;
    }
    update("user", "Processing_value", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_panel_user, 'html');
    step('getdata', $from_id);
} elseif (preg_match('/locationnotuser_(.*)/', $datain, $dataget)) {
    $locationid = $dataget[1];
    $marzban_list_get = select("marzban_panel", "name_panel", "id", $locationid, "select");
    $location = $marzban_list_get['name_panel'];
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $user['Processing_value']);
    if ($DataUserOut['status'] == "Unsuccessful") {
        if ($DataUserOut['msg'] == "User not found") {
            sendmessage($from_id, $textbotlang['users']['stateus']['notUsernameget'], $keyboard, 'html');
            step('home', $from_id);
            return;
        }
    }
    #-------------[ status ]----------------#
    $status = $DataUserOut['status'];
    $status_var = [
        'active' => $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired'],
        'on_hold' => $textbotlang['users']['stateus']['onhold']
    ][$status];
    #--------------[ expire ]---------------#
    $expirationDate = $DataUserOut['expire'] ? jdate('Y/m/d', $DataUserOut['expire']) : $textbotlang['users']['stateus']['Unlimited'];
    #-------------[ data_limit ]----------------#
    $LastTraffic = $DataUserOut['data_limit'] ? formatBytes($DataUserOut['data_limit']) : $textbotlang['users']['stateus']['Unlimited'];
    #---------------[ RemainingVolume ]--------------#
    $output = $DataUserOut['data_limit'] - $DataUserOut['used_traffic'];
    $RemainingVolume = $DataUserOut['data_limit'] ? formatBytes($output) : $textbotlang['users']['unlimited'];
    #---------------[ used_traffic ]--------------#
    $usedTrafficGb = $DataUserOut['used_traffic'] ? formatBytes($DataUserOut['used_traffic']) : $textbotlang['users']['stateus']['Notconsumed'];
    #--------------[ day ]---------------#
    $timeDiff = $DataUserOut['expire'] - time();
    $day = $DataUserOut['expire'] ? floor($timeDiff / 86400) + 1 . $textbotlang['users']['stateus']['day'] : $textbotlang['users']['stateus']['Unlimited'];
    #-----------------------------#


    $keyboardinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $DataUserOut['username'], 'callback_data' => "username"],
                ['text' => $textbotlang['users']['stateus']['username'], 'callback_data' => 'username'],
            ],
            [
                ['text' => $status_var, 'callback_data' => 'status_var'],
                ['text' => $textbotlang['users']['stateus']['stateus'], 'callback_data' => 'status_var'],
            ],
            [
                ['text' => $expirationDate, 'callback_data' => 'expirationDate'],
                ['text' => $textbotlang['users']['stateus']['expirationDate'], 'callback_data' => 'expirationDate'],
            ],
            [],
            [
                ['text' => $day, 'callback_data' => 'day'],
                ['text' => $textbotlang['users']['stateus']['daysleft'], 'callback_data' => 'day'],
            ],
            [
                ['text' => $LastTraffic, 'callback_data' => 'LastTraffic'],
                ['text' => $textbotlang['users']['stateus']['LastTraffic'], 'callback_data' => 'LastTraffic'],
            ],
            [
                ['text' => $usedTrafficGb, 'callback_data' => 'expirationDate'],
                ['text' => $textbotlang['users']['stateus']['usedTrafficGb'], 'callback_data' => 'expirationDate'],
            ],
            [
                ['text' => $RemainingVolume, 'callback_data' => 'RemainingVolume'],
                ['text' => $textbotlang['users']['stateus']['RemainingVolume'], 'callback_data' => 'RemainingVolume'],
            ]
        ]
    ]);
    sendmessage($from_id, $textbotlang['users']['stateus']['info'], $keyboardinfo, 'html');
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'html');
    step('home', $from_id);
}
if ($datain == 'next_page') {
    $numpage = select("invoice", "id_user", "id_user", $from_id, "count");
    $page = $user['pagenumber'];
    $items_per_page = 5;
    $sum = $user['pagenumber'] * $items_per_page;
    if ($sum > $numpage) {
        $next_page = 1;
    } else {
        $next_page = $page + 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn') ORDER BY username ASC LIMIT $start_index, $items_per_page");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "🌟️" . $row['username'] . "🌟️",
                'callback_data' => "product_" . $row['username']
            ],
        ];
    }
    $pagination_buttons = [
        [
            'text' => $textbotlang['users']['page']['next'],
            'callback_data' => 'next_page'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_page'
        ]
    ];
    $usernotlist = [
        [
            'text' => $textbotlang['Admin']['Status']['notusenameinbot'],
            'callback_data' => 'usernotlist'
        ]
    ];
    if ($setting['NotUser'] == "1") {
        $keyboardlists['inline_keyboard'][] = $usernotlist;
    }
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $text_callback, $keyboard_json);
} elseif ($datain == 'previous_page') {
    $page = $user['pagenumber'];
    $items_per_page = 5;
    if ($user['pagenumber'] <= 1) {
        $next_page = 1;
    } else {
        $next_page = $page - 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn') ORDER BY username ASC LIMIT $start_index, $items_per_page");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "🌟️" . $row['username'] . "🌟️",
                'callback_data' => "product_" . $row['username']
            ],
        ];
    }
    $pagination_buttons = [
        [
            'text' => $textbotlang['users']['page']['next'],
            'callback_data' => 'next_page'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_page'
        ]
    ];
    $usernotlist = [
        [
            'text' => $textbotlang['Admin']['Status']['notusenameinbot'],
            'callback_data' => 'usernotlist'
        ]
    ];
    if ($setting['NotUser'] == "1") {
        $keyboardlists['inline_keyboard'][] = $usernotlist;
    }
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $text_callback, $keyboard_json);
}
if (preg_match('/product_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $username);
    if (isset ($DataUserOut['msg']) && $DataUserOut['msg'] == "User not found") {
        sendmessage($from_id, $textbotlang['users']['stateus']['usernotfound'], $keyboard, 'html');
        update("invoice","Status","disabledn","id_invoice",$nameloc['id_invoice']);
        return;
    }
    if($DataUserOut['status'] == "Unsuccessful"){
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], $keyboard, 'html');
        return;
    }
    if($DataUserOut['online_at'] == "online"){
        $lastonline = $textbotlang['users']['online'];
    }elseif($DataUserOut['online_at'] == "offline"){
        $lastonline = $textbotlang['users']['offline'];
    }else{
        if(isset($DataUserOut['online_at']) && $DataUserOut['online_at'] !== null){
            $dateString = $DataUserOut['online_at'];
            $lastonline = jdate('Y/m/d h:i:s',strtotime($dateString));
        }else{
            $lastonline = $textbotlang['users']['stateus']['notconnected'];
        }
    }
    #-------------status----------------#
    $status = $DataUserOut['status'];
    $status_var = [
        'active' => $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired'],
        'on_hold' => $textbotlang['users']['stateus']['onhold']
    ][$status];
    #--------------[ expire ]---------------#
    $expirationDate = $DataUserOut['expire'] ? jdate('Y/m/d', $DataUserOut['expire']) : $textbotlang['users']['stateus']['Unlimited'];
    #-------------[ data_limit ]----------------#
    $LastTraffic = $DataUserOut['data_limit'] ? formatBytes($DataUserOut['data_limit']) : $textbotlang['users']['stateus']['Unlimited'];
    #---------------[ RemainingVolume ]--------------#
    $output = $DataUserOut['data_limit'] - $DataUserOut['used_traffic'];
    $RemainingVolume = $DataUserOut['data_limit'] ? formatBytes($output) : $textbotlang['users']['unlimited'];
    #---------------[ used_traffic ]--------------#
    $usedTrafficGb = $DataUserOut['used_traffic'] ? formatBytes($DataUserOut['used_traffic']) : $textbotlang['users']['stateus']['Notconsumed'];
    #--------------[ day ]---------------#
    $timeDiff = $DataUserOut['expire'] - time();
    $day = $DataUserOut['expire'] ? floor($timeDiff / 86400) + 1 . $textbotlang['users']['stateus']['day'] : $textbotlang['users']['stateus']['Unlimited'];
    #-----------------------------#
    if(!in_array($status,['active',"on_hold"])){
        $keyboardsetting = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['extend']['title'], 'callback_data' => 'extend_' . $username],
                ],
                [
                    ['text' => "🗑 حذف سرویس", 'callback_data' => 'removebyuser-' . $username],
                    ['text' => $textbotlang['users']['Extra_volume']['sellextra'], 'callback_data' => 'Extra_volume_' . $username],
                ],
                [
                    ['text' => $textbotlang['users']['stateus']['backlist'], 'callback_data' => 'backorder'],
                ]
            ]
        ]);
        $textinfo = "وضعیت سرویس : $status_var
نام کاربری سرویس : {$DataUserOut['username']}
لوکیشن :{$nameloc['Service_location']}
کد سرویس:{$nameloc['id_invoice']}
    
📥 حجم مصرفی : $usedTrafficGb
♾ حجم سرویس : $LastTraffic

📅 فعال تا تاریخ : $expirationDate ($day)
";

    }else{
        $keyboardsetting = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['stateus']['linksub'], 'callback_data' => 'subscriptionurl_' . $username],
                    ['text' => $textbotlang['users']['stateus']['config'], 'callback_data' => 'config_' . $username],
                ],
                [
                    ['text' => $textbotlang['users']['extend']['title'], 'callback_data' => 'extend_' . $username],
                    ['text' => $textbotlang['users']['changelink']['btntitle'], 'callback_data' => 'changelink_' . $username],
                ],
                [
                    ['text' => $textbotlang['users']['removeconfig']['btnremoveuser'], 'callback_data' => 'removeserviceuserco-' . $username],
                    ['text' => $textbotlang['users']['Extra_volume']['sellextra'], 'callback_data' => 'Extra_volume_' . $username],
                ],
                [
                    ['text' => $textbotlang['users']['stateus']['backlist'], 'callback_data' => 'backorder'],
                ]
            ]
        ]);
        $textinfo = "وضعیت سرویس : $status_var
نام کاربری سرویس : {$DataUserOut['username']}
لوکیشن :{$nameloc['Service_location']}
کد سرویس:{$nameloc['id_invoice']}
    
🟢 اخرین زمان اتصال شما : $lastonline
    
📥 حجم مصرفی : $usedTrafficGb
♾ حجم سرویس : $LastTraffic

📅 فعال تا تاریخ : $expirationDate ($day)
    
🚫 برای تغییر لینک و قطع دسترسی دیگران کافیست روی گزینه ' بروزرسانی اشتراک ' کلیک کنید.";
    }
    Editmessagetext($from_id, $message_id, $textinfo, $keyboardsetting);
}
if (preg_match('/subscriptionurl_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $username);
    $subscriptionurl = $DataUserOut['subscription_url'];
    $textsub = "<code>$subscriptionurl</code>";
    $randomString = bin2hex(random_bytes(2));
    $urlimage = "$from_id$randomString.png";
    $writer = new PngWriter();
    $qrCode = QrCode::create($subscriptionurl)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
        ->setSize(400)
        ->setMargin(0)
        ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
    $result = $writer->write($qrCode, null, null);
    $result->saveToFile($urlimage);
    telegram('sendphoto', [
        'chat_id' => $from_id,
        'photo' => new CURLFile($urlimage),
        'caption' => $textsub,
        'parse_mode' => "HTML",
    ]);
    unlink($urlimage);
} elseif (preg_match('/config_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $username);
    foreach ($DataUserOut['links'] as $configs) {
        $randomString = bin2hex(random_bytes(2));
        $urlimage = "$from_id$randomString.png";
        $writer = new PngWriter();
        $qrCode = QrCode::create($configs)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(400)
            ->setMargin(0)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
        $result = $writer->write($qrCode, null, null);
        $result->saveToFile($urlimage);
        telegram('sendphoto', [
            'chat_id' => $from_id,
            'photo' => new CURLFile($urlimage),
            'caption' => "<code>$configs</code>",
            'parse_mode' => "HTML",
        ]);
        unlink($urlimage);
    }
} elseif (preg_match('/extend_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $username);
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        return;
    }
    update("user", "Processing_value", $username, "id", $from_id);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE (Location = :Location OR location = '/all')");
    $stmt->bindValue(':Location', $nameloc['Service_location']);
    $stmt->execute();
    $productextend = ['inline_keyboard' => []];
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productextend['inline_keyboard'][] = [
            ['text' => $result['name_product'], 'callback_data' => "serviceextendselect_" . $result['code_product']]
        ];
    }
    $productextend['inline_keyboard'][] = [
        ['text' => $textbotlang['users']['backorder'], 'callback_data' => "product_" . $username]
    ];

    $json_list_product_lists = json_encode($productextend);
    Editmessagetext($from_id, $message_id, $textbotlang['users']['extend']['selectservice'], $json_list_product_lists);
} elseif (preg_match('/serviceextendselect_(\w+)/', $datain, $dataget)) {
    $codeproduct = $dataget[1];
    $nameloc = select("invoice", "*", "username", $user['Processing_value'], "select");
    $stmt = $pdo->prepare("SELECT * FROM product WHERE (Location = :Location OR location = '/all') AND code_product = :code_product LIMIT 1");
    $stmt->bindValue(':Location', $nameloc['Service_location']);
    $stmt->bindValue(':code_product', $codeproduct);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    update("invoice", "name_product", $product['name_product'], "username", $user['Processing_value']);
    update("invoice", "Service_time", $product['Service_time'], "username", $user['Processing_value']);
    update("invoice", "Volume", $product['Volume_constraint'], "username", $user['Processing_value']);
    update("invoice", "price_product", $product['price_product'], "username", $user['Processing_value']);
    update("user", "Processing_value_one", $codeproduct, "id", $from_id);
    $keyboardextend = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['extend']['confirm'], 'callback_data' => "confirmserivce-" . $codeproduct],
            ],
            [
                ['text' => $textbotlang['users']['backhome'], 'callback_data' => "backuser"]

            ]
        ]
    ]);
    $textextend = "🧾 فاکتور تمدید شما برای نام کاربری {$nameloc['username']} ایجاد شد.
            
🛍 نام محصول :  {$product['name_product']}
مبلغ تمدید :  {$product['price_product']}
مدت زمان تمدید : {$product['Service_time']} روز
حجم تمدید : {$product['Volume_constraint']} گیگ
            
            
✅ برای تایید و تمدید سرویس روی دکمه زیر کلیک کنید
            
❌ برای تمدید باید کیف پول خود را شارژ کنید.";
    Editmessagetext($from_id, $message_id, $textextend, $keyboardextend);
} elseif (preg_match('/confirmserivce-(.*)/', $datain, $dataget)) {
    $codeproduct = $dataget[1];
    deletemessage($from_id, $message_id);
    $nameloc = select("invoice", "*", "username", $user['Processing_value'], "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $stmt = $pdo->prepare("SELECT * FROM product WHERE (Location = :Location OR location = '/all') AND code_product = :code_product LIMIT 1");
    $stmt->bindValue(':Location', $nameloc['Service_location']);
    $stmt->bindValue(':code_product', $codeproduct);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user['Balance'] < $product['price_product']) {
        $Balance_prim = $product['price_product'] - $user['Balance'];
        update("user", "Processing_value", $Balance_prim, "id", $from_id);
        sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
        sendmessage($from_id, $textbotlang['users']['sell']['selectpayment'], $backuser, 'HTML');
        step('get_step_payment', $from_id);
        return;
    }
    $usernamepanel = $nameloc['username'];
    $Balance_Low_user = $user['Balance'] - $product['price_product'];
    update("user", "Balance", $Balance_Low_user, "id", $from_id);
    $ManagePanel->ResetUserDataUsage($nameloc['Service_location'], $user['Processing_value']);
    if ($marzban_list_get['type'] == "marzban") {
        if(intval($product['Service_time']) == 0){
            $newDate = 0;
        }else{
            $date = strtotime("+" . $product['Service_time'] . "day");
            $newDate = strtotime(date("Y-m-d H:i:s", $date));
        }
        $data_limit = intval($product['Volume_constraint']) * pow(1024, 3);
        $datam = array(
            "expire" => $newDate,
            "data_limit" => $data_limit
        );
        $ManagePanel->Modifyuser($user['Processing_value'], $nameloc['Service_location'], $datam);
    }elseif ($marzban_list_get['type'] == "marzneshin") {
        if(intval($product['Service_time']) == 0){
            $newDate = 0;
        }else{
            $date = strtotime("+" . $product['Service_time'] . "day");
            $newDate = strtotime(date("Y-m-d H:i:s", $date));
        }
        $data_limit = intval($product['Volume_constraint']) * pow(1024, 3);
        $datam = array(
            "expire_date" => $newDate,
            "data_limit" => $data_limit
        );
        $ManagePanel->Modifyuser($user['Processing_value'], $nameloc['Service_location'], $datam);
    } elseif ($marzban_list_get['type'] == "x-ui_single") {
        $date = strtotime("+" . $product['Service_time'] . "day");
        $newDate = strtotime(date("Y-m-d H:i:s", $date)) * 1000;
        $data_limit = intval($product['Volume_constraint']) * pow(1024, 3);
        $config = array(
            'id' => intval($marzban_list_get['inboundid']),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "totalGB" => $data_limit,
                            "expiryTime" => $newDate,
                            "enable" => true,
                        )
                    ),
                )
            ),
        );
        $ManagePanel->Modifyuser($user['Processing_value'], $nameloc['Service_location'], $config);
    }elseif ($marzban_list_get['type'] == "alireza") {
        $date = strtotime("+" . $product['Service_time'] . "day");
        $newDate = strtotime(date("Y-m-d H:i:s", $date)) * 1000;
        $data_limit = intval($product['Volume_constraint']) * pow(1024, 3);
        $config = array(
            'id' => intval($marzban_list_get['inboundid']),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "totalGB" => $data_limit,
                            "expiryTime" => $newDate,
                            "enable" => true,
                        )
                    ),
                )
            ),
        );
        $ManagePanel->Modifyuser($user['Processing_value'], $nameloc['Service_location'], $config);
    }
    elseif($marzban_list_get['type'] == "s_ui"){
        $date = strtotime("+" . $product['Service_time'] . "day");
        $newDate = strtotime(date("Y-m-d H:i:s", $date));
        $data_limit = intval($product['Volume_constraint']) * pow(1024, 3);
        $config = array(
            "volume" => $data_limit,
            "expiry" => $newDate
        );
        $ManagePanel->Modifyuser($user['Processing_value'], $nameloc['Service_location'], $config);
    }
    $keyboardextendfnished = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backlist'], 'callback_data' => "backorder"],
            ],
            [
                ['text' => $textbotlang['users']['stateus']['backservice'], 'callback_data' => "product_" . $usernamepanel],
            ]
        ]
    ]);
    $priceproductformat = number_format($product['price_product']);
    $balanceformatsell = number_format(select("user", "Balance", "id", $from_id, "select")['Balance']);
    sendmessage($from_id, $textbotlang['users']['extend']['thanks'], $keyboardextendfnished, 'HTML');
    $text_report = "⭕️ یک کاربر سرویس خود را تمدید کرد.
            
    اطلاعات کاربر : 
            
🪪 آیدی عددی : <code>$from_id</code>
🪪  نام کاربری : @$username
🛍 نام محصول :  {$product['name_product']}
💰 مبلغ تمدید $priceproductformat تومان
👤 نام کاربری مشتری در پنل  : $usernamepanel
موجودی کاربر : $balanceformatsell تومان
لوکیشن سرویس کاربر : {$nameloc['Service_location']}";
    if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
} elseif (preg_match('/changelink_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username, "select");
    $keyboardchange = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['changelink']['confirm'], 'callback_data' => "confirmchange_" . $username],
            ],[
                ['text' => $textbotlang['users']['stateus']['backservice'], 'callback_data' => "product_" . $username],
            ]
        ]
    ]);
    Editmessagetext($from_id,$message_id,$textbotlang['users']['changelink']['warnchange'], $keyboardchange);
} elseif (preg_match('/confirmchange_(\w+)/', $datain, $dataget)) {
    $usernameconfig = $dataget[1];
    $nameloc = select("invoice", "*", "username", $usernameconfig, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $ManagePanel->Revoke_sub($marzban_list_get['name_panel'], $usernameconfig);
    $keyboardchange = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backservice'], 'callback_data' => "product_" . $usernameconfig],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['users']['changelink']['confirmed'], $keyboardchange);

} elseif (preg_match('/Extra_volume_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    update("user", "Processing_value", $username, "id", $from_id);
    $textextra = " ⭕️ مقدار حجمی که میخواهید خریداری کنید را ارسال کنید.
    
⚠️ هر گیگ حجم اضافه  {$setting['Extra_volume']} است.";
    sendmessage($from_id, $textextra, $backuser, 'HTML');
    step('getvolumeextra', $from_id);
} elseif ($user['step'] == "getvolumeextra") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    if ($text < 1) {
        sendmessage($from_id, $textbotlang['users']['Extra_volume']['invalidprice'], $backuser, 'HTML');
        return;
    }
    $priceextra = $setting['Extra_volume'] * $text;
    $keyboardsetting = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['Extra_volume']['extracheck'], 'callback_data' => 'confirmaextra_' . $priceextra],
            ]
        ]
    ]);
    $priceextra = number_format($priceextra);
    $setting['Extra_volume'] = number_format($setting['Extra_volume']);
    $textextra = "📇 فاکتور خرید حجم اضافه برای شما ایجاد شد.
    
💰 قیمت هر گیگابایت حجم اضافه :  {$setting['Extra_volume']} تومان
📝 مبلغ  فاکتور شما :  $priceextra تومان
📥 حجم اضافه درخواستی : $text  گیگابایت

✅ جهت پرداخت و اضافه شدن حجم، روی دکمه زیر کلیک کنید.";
    sendmessage($from_id, $textextra, $keyboardsetting, 'HTML');
    step('home', $from_id);
} elseif (preg_match('/confirmaextra_(\w+)/', $datain, $dataget)) {
    $volume = $dataget[1];
    Editmessagetext($from_id, $message_id, $text_callback, json_encode(['inline_keyboard' => []]));
    $nameloc = select("invoice", "*", "username", $user['Processing_value'], "select");
    if ($user['Balance'] < $volume) {
        $Balance_prim = $volume - $user['Balance'];
        update("user", "Processing_value", $Balance_prim, "id", $from_id);
        sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
        step('get_step_payment', $from_id);
        return;
    }
    $Balance_Low_user = $user['Balance'] - $volume;
    update("user", "Balance", $Balance_Low_user, "id", $from_id);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $user['Processing_value']);
    $data_limit = $DataUserOut['data_limit'] + ($volume / $setting['Extra_volume'] * pow(1024, 3));
    if ($marzban_list_get['type'] == "marzban") {
        $datam = array(
            "data_limit" => $data_limit
        );
    }elseif($marzban_list_get['type'] == "marzneshin"){
        $datam = array(
            "data_limit" => $data_limit
        );
    } elseif ($marzban_list_get['type'] == "x-ui_single") {
        $datam = array(
            'id' => intval($marzban_list_get['inboundid']),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "totalGB" => $data_limit,
                        )
                    ),
                )
            ),
        );
    } elseif ($marzban_list_get['type'] == "alireza") {
        $datam = array(
            'id' => intval($marzban_list_get['inboundid']),
            'settings' => json_encode(
                array(
                    'clients' => array(
                        array(
                            "totalGB" => $data_limit,
                        )
                    ),
                )
            ),
        );
    }
    elseif ($marzban_list_get['type'] == "s_ui") {
        $datam = array(
            "volume" => $data_limit,
        );
    }
    $ManagePanel->Modifyuser($user['Processing_value'], $marzban_list_get['name_panel'], $datam);
    $keyboardextrafnished = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backservice'], 'callback_data' => "product_" . $user['Processing_value']],
            ]
        ]
    ]);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['extraadded'], $keyboardextrafnished, 'HTML');
    $volumes = $volume / $setting['Extra_volume'];
    $volume = number_format($volume);
    $text_report = "⭕️ یک کاربر حجم اضافه خریده است
    اطلاعات کاربر : 
🪪 آیدی عددی : $from_id
🛍 حجم خریداری شده  : $volumes
💰 مبلغ پرداختی : $volume تومان";
    if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
} elseif (preg_match('/removeserviceuserco-(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice","*","username",$username,"select");
    $marzban_list_get = select("marzban_panel","*","name_panel",$nameloc['Service_location'],"select");
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $username);
    if (isset ($DataUserOut['status']) && in_array($DataUserOut['status'], ["expired", "limited", "disabled"])) {
        sendmessage($from_id, $textbotlang['users']['stateus']['notusername'], null, 'html');
        return;
    }
    $requestcheck = select("cancel_service", "*", "username", $username, "count");
    if ($requestcheck != 0) {
        sendmessage($from_id, $textbotlang['users']['stateus']['errorexits'], null, 'html');
        return;
    }
    $confirmremove = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅  درخواست حذف سرویس را دارم", 'callback_data' => "confirmremoveservices-$username"],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['users']['stateus']['descriptions_removeservice'], $confirmremove);
}elseif (preg_match('/removebyuser-(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice","*","username",$username,"select");
    $marzban_list_get = select("marzban_panel","*","name_panel",$nameloc['Service_location'],"select");
    $ManagePanel->RemoveUser($nameloc['Service_location'],$nameloc['username']);
    update('invoice','status','removebyuser','id_invoice',$nameloc['id_invoice']);
    $tetremove = "ادمین عزیز یک کاربر سرویس خود را پس از پایان حجم یا زمان حدف کرده است
نام کاربری کانفیک : {$nameloc['username']}";
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage',[
            'chat_id' => $setting['Channel_Report'],
            'text' => $tetremove,
            'parse_mode' => "HTML"
        ]);
    }
    deletemessage($from_id, $message_id);
    sendmessage($from_id, "📌 سرویس با موفقیت حذف شد", null, 'html');
} elseif (preg_match('/confirmremoveservices-(\w+)/', $datain, $dataget)) {
    $checkcancelservice = mysqli_query($connect, "SELECT * FROM cancel_service WHERE id_user = '$from_id' AND status = 'waiting'");
    if (mysqli_num_rows($checkcancelservice) != 0) {
        sendmessage($from_id, $textbotlang['users']['stateus']['exitsrequsts'], null, 'HTML');
        return;
    }
    $usernamepanel = $dataget[1];
    $nameloc = select("invoice", "*", "username", $usernamepanel, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $stmt = $connect->prepare("INSERT IGNORE INTO cancel_service (id_user, username,description,status) VALUES (?, ?, ?, ?)");
    $descriptions = "0";
    $Status = "waiting";
    $stmt->bind_param("ssss", $from_id, $usernamepanel, $descriptions, $Status);
    $stmt->execute();
    $stmt->close();
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $usernamepanel);
    #-------------status----------------#
    $status = $DataUserOut['status'];
    $status_var = [
        'active' => $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired'],
        'on_hold' => $textbotlang['users']['stateus']['onhold']
    ][$status];
    #--------------[ expire ]---------------#
    $expirationDate = $DataUserOut['expire'] ? jdate('Y/m/d', $DataUserOut['expire']) : $textbotlang['users']['stateus']['Unlimited'];
    #-------------[ data_limit ]----------------#
    $LastTraffic = $DataUserOut['data_limit'] ? formatBytes($DataUserOut['data_limit']) : $textbotlang['users']['stateus']['Unlimited'];
    #---------------[ RemainingVolume ]--------------#
    $output = $DataUserOut['data_limit'] - $DataUserOut['used_traffic'];
    $RemainingVolume = $DataUserOut['data_limit'] ? formatBytes($output) : $textbotlang['users']['unlimited'];
    #---------------[ used_traffic ]--------------#
    $usedTrafficGb = $DataUserOut['used_traffic'] ? formatBytes($DataUserOut['used_traffic']) : $textbotlang['users']['stateus']['Notconsumed'];
    #--------------[ day ]---------------#
    $timeDiff = $DataUserOut['expire'] - time();
    $day = $DataUserOut['expire'] ? floor($timeDiff / 86400) . $textbotlang['users']['stateus']['day'] : $textbotlang['users']['stateus']['Unlimited'];
    #-----------------------------#
    $textinfoadmin = "سلام ادمین 👋
            
📌 یک درخواست حذف سرویس  توسط کاربر برای شما ارسال شده است. لطفا بررسی کرده و در صورت درست بودن و موافقت تایید کنید. 
⚠️ نکات تایید :
1 -  مبلغ قابل بازگشت به کاربر توسط شما تعیین خواهد شد.
            
            
📊 اطلاعات سرویس کاربر :
آیدی عددی کاربر : $from_id
نام کاربری کاربر : @$username
نام کاربری کانفیگ : {$nameloc['username']}
وضعیت سرویس : $status_var
لوکیشن : {$nameloc['Service_location']}
کد سرویس:{$nameloc['id_invoice']}
    
📥 حجم مصرفی : $usedTrafficGb
♾ حجم سرویس : $LastTraffic
🪫 حجم باقی مانده : $RemainingVolume
📅 فعال تا تاریخ : $expirationDate ($day)";
    $confirmremoveadmin = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['removeconfig']['btnremoveuser'] , 'callback_data' => "remoceserviceadmin-$usernamepanel"],
                ['text' => $textbotlang['users']['removeconfig']['rejectremove'], 'callback_data' => "rejectremoceserviceadmin-$usernamepanel"],
            ],
        ]
    ]);
    foreach ($admin_ids as $admin) {
        sendmessage($admin, $textinfoadmin, $confirmremoveadmin, 'html');
        step('home', $admin);
    }
    deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['users']['removeconfig']['accepetrequest'], $keyboard, 'html');

}
#-----------usertest------------#
if ($text == $datatextbot['text_usertest']) {
    $locationproduct = select("marzban_panel", "*", null, null, "count");
    if ($locationproduct == 0) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
        return;
    }
    if ($setting['get_number'] == "1" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        step('get_number', $from_id);
    }
    if ($user['number'] == "none" && $setting['get_number'] == "1")
        return;
    if ($user['limit_usertest'] <= 0) {
        sendmessage($from_id, $textbotlang['users']['usertest']['limitwarning'], $keyboard, 'html');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_usertest, 'html');
}
if ($user['step'] == "createusertest" || preg_match('/locationtests_(.*)/', $datain, $dataget)) {
    if ($user['limit_usertest'] <= 0) {
        sendmessage($from_id, $textbotlang['users']['usertest']['limitwarning'], $keyboard, 'html');
        return;
    }
    if ($user['step'] == "createusertest") {
        $name_panel = $user['Processing_value_one'];
        if (!preg_match('~(?!_)^[a-z][a-z\d_]{2,32}(?<!_)$~i', $text)) {
            sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser, 'HTML');
            return;
        }
    } else {
        deletemessage($from_id, $message_id);
        $id_panel = $dataget[1];
        $marzban_list_get = select("marzban_panel", "*", "id", $id_panel, "select");
        $name_panel = $marzban_list_get['name_panel'];
    }
    $randomString = bin2hex(random_bytes(2));
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $name_panel, "select");

    if ($marzban_list_get['MethodUsername'] == "نام کاربری دلخواه") {
        if ($user['step'] != "createusertest") {
            step('createusertest', $from_id);
            update("user", "Processing_value_one", $name_panel, "id", $from_id);
            sendmessage($from_id, $textbotlang['users']['selectusername'], $backuser, 'html');
            return;
        }
    }
    $username_ac = strtolower(generateUsername($from_id, $marzban_list_get['MethodUsername'], $user['username'], $randomString, $text));
    $DataUserOut = $ManagePanel->DataUser($marzban_list_get['name_panel'], $username_ac);
    if (isset ($DataUserOut['username']) || in_array($username_ac, $usernameinvoice)) {
        $random_number = random_int(1000000, 9999999);
        $username_ac = $username_ac . $random_number;
    }
    $datac = array(
        'expire' => strtotime(date("Y-m-d H:i:s", strtotime("+" . $setting['time_usertest'] . "hours"))),
        'data_limit' => $setting['val_usertest'] * 1048576,
    );
    $dataoutput = $ManagePanel->createUser($name_panel, $username_ac, $datac);
    if ($dataoutput['username'] == null) {
        $dataoutput['msg'] = json_encode($dataoutput['msg']);
        sendmessage($from_id, $textbotlang['users']['usertest']['errorcreat'], $keyboard, 'html');
        $texterros = "
⭕️ یک کاربر قصد دریافت اکانت تست داشت که ساخت کانفیگ با خطا مواجه شده و به کاربر کانفیگ داده نشد
✍️ دلیل خطا : 
{$dataoutput['msg']}
آیدی کابر : $from_id
نام کاربری کاربر : @$username";
        foreach ($admin_ids as $admin) {
            sendmessage($admin, $texterros, null, 'html');
        }
        step('home', $from_id);
        return;
    }
    $date = time();
    $randomString = bin2hex(random_bytes(2));
    $sql = "INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $Status = "active";
    $usertest = "usertest";
    $price = "0";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $randomString);
    $stmt->bindParam(3, $username_ac, PDO::PARAM_STR);
    $stmt->bindParam(4, $date);
    $stmt->bindParam(5, $name_panel, PDO::PARAM_STR);
    $stmt->bindParam(6, $usertest, PDO::PARAM_STR);
    $stmt->bindParam(7, $price);
    $stmt->bindParam(8, $setting['val_usertest']);
    $stmt->bindParam(9, $setting['time_usertest']);
    $stmt->bindParam(10, $Status);
    $stmt->execute();
    $text_config = "";
    $output_config_link = "";
    if ($marzban_list_get['sublink'] == "onsublink") {
        $output_config_link = $dataoutput['subscription_url'];
        $link_config = "            
        {$textbotlang['users']['stateus']['linksub']}
        $output_config_link";
    }
    if ($marzban_list_get['configManual'] == "onconfig") {
        foreach ($dataoutput['configs'] as $configs) {
            $config .= "\n\n" . $configs;
        }
        $text_config = $config;
    }
    $Shoppinginfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
            ]
        ]
    ]);
    $textcreatuser = "✅ سرویس با موفقیت ایجاد شد
    
👤 نام کاربری سرویس : <code>$username_ac</code>
🌿 نام سرویس: تست
‏🇺🇳 لوکیشن: {$marzban_list_get['name_panel']}
⏳ مدت زمان: {$setting['time_usertest']}  ساعت
🗜 حجم سرویس:  {$setting['val_usertest']} مگابایت
    
لینک اتصال:
<code>$output_config_link</code>
<code>$text_config</code>
    
📚 راهنمای اتصال به سرویس را از طریق کلیک کردن دکمه زیر مطالعه بفرمایید";
    if ($marzban_list_get['sublink'] == "onsublink") {
        $urlimage = "$from_id$randomString.png";
        $writer = new PngWriter();
        $qrCode = QrCode::create($output_config_link)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(400)
            ->setMargin(0)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
        $result = $writer->write($qrCode, null, null);
        $result->saveToFile($urlimage);
        telegram('sendphoto', [
            'chat_id' => $from_id,
            'photo' => new CURLFile($urlimage),
            'reply_markup' => $Shoppinginfo,
            'caption' => $textcreatuser,
            'parse_mode' => "HTML",
        ]);
        sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
        unlink($urlimage);
    } else {
        sendmessage($from_id, $textcreatuser, $usertestinfo, 'HTML');
        sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
    }
    step('home', $from_id);
    $limit_usertest = $user['limit_usertest'] - 1;
    update("user", "limit_usertest", $limit_usertest, "id", $from_id);
    step('home', $from_id);
    $usertestReport = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $user['number'], 'callback_data' => "iduser"],
                ['text' => $textbotlang['users']['usertest']['phonenumber'], 'callback_data' => "iduser"],
            ],
            [
                ['text' => $name_panel, 'callback_data' => "namepanel"],
                ['text' => $textbotlang['users']['usertest']['namepanel'], 'callback_data' => "namepanel"],
            ],
        ]
    ]);
    $text_report = " ⚜️ اکانت تست داده شد
            
⚙️ یک کاربر اکانت  با نام کانفیگ <code>$username_ac</code>  اکانت تست دریافت کرد
            
اطلاعات کاربر 👇👇
⚜️ نام کاربری کاربر: @{$user['username']}
آیدی عددی کاربر : <code>$from_id</code>";
    if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, $usertestReport, 'HTML');
    }
}
#-----------help------------#
if ($text == $datatextbot['text_help'] || $datain == "helpbtn" || $text == "/help") {
    if ($setting['help_Status'] == "0") {
        sendmessage($from_id, $textbotlang['users']['help']['disablehelp'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['selectoption'], $json_list_help, 'HTML');
    step('sendhelp', $from_id);
} elseif ($user['step'] == "sendhelp") {
    $helpdata = select("help", "*", "name_os", $text, "select");
    if (strlen($helpdata['Media_os']) != 0) {
        if ($helpdata['type_Media_os'] == "video") {
            sendvideo($from_id, $helpdata['Media_os'], $helpdata['Description_os']);
        } elseif ($helpdata['type_Media_os'] == "photo")
            sendphoto($from_id, $helpdata['Media_os'], $helpdata['Description_os']);
    } else {
        sendmessage($from_id, $helpdata['Description_os'], $json_list_help, 'HTML');
    }
}

#-----------support------------#
if ($text == $datatextbot['text_support'] || $text == "/support") {
    sendmessage($from_id, $textbotlang['users']['support']['btnsupport'], $supportoption, 'HTML');
} elseif ($datain == "support") {
    sendmessage($from_id, $textbotlang['users']['support']['sendmessageuser'], $backuser, 'HTML');
    step('gettextpm', $from_id);
} elseif ($user['step'] == 'gettextpm') {
    sendmessage($from_id, $textbotlang['users']['support']['sendmessageadmin'], $keyboard, 'HTML');
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['support']['answermessage'], 'callback_data' => 'Response_' . $from_id],
            ],
        ]
    ]);
    foreach ($admin_ids as $id_admin) {
        if ($text) {
            $textsendadmin = "
📥 یک پیام از کاربر دریافت شد برای پاسخ روی دکمه زیر کلیک کنید  و پیام خود را ارسال کنید.
        
آیدی عددی : $from_id
نام کاربری کاربر : @$username
📝 متن پیام : $text
            ";
            sendmessage($id_admin, $textsendadmin, $Response, 'HTML');
        }
        if ($photo) {
            $textsendadmin = "
📥 یک پیام از کاربر دریافت شد برای پاسخ روی دکمه زیر کلیک کنید  و پیام خود را ارسال کنید.
        
آیدی عددی : $from_id
نام کاربری کاربر : @$username
📝 متن پیام : $caption";
            telegram('sendphoto', [
                'chat_id' => $id_admin,
                'photo' => $photoid,
                'reply_markup' => $Response,
                'caption' => $textsendadmin,
                'parse_mode' => "HTML",
            ]);
        }
    }
    step('home', $from_id);
}
#-----------fq------------#
if ($datain == "fqQuestions") {
    sendmessage($from_id, $datatextbot['text_dec_fq'], null, 'HTML');
}
if ($text == $datatextbot['text_account']) {
    $dateacc = jdate('Y/m/d');
    $timeacc = jdate('H:i:s', time());
    $first_name = htmlspecialchars($first_name);
    $Balanceuser = number_format($user['Balance'], 0);
    $countorder = select("invoice", "id_user", 'id_user', $from_id, "count");
    $text_account = "
👨🏻‍💻 وضعیت حساب کاربری شما:
            
👤 نام: $first_name
🕴🏻 شناسه کاربری: <code>$from_id</code>
💰 موجودی: $Balanceuser تومان
🛍 تعداد سرویس های خریداری شده : $countorder
🤝 تعداد زیر مجموعه های شما : {$user['affiliatescount']} نفر
    
📆 $dateacc → ⏰ $timeacc
                ";
    sendmessage($from_id, $text_account, $keyboardPanel, 'HTML');
}
if ($text == $datatextbot['text_sell'] || $datain == "buy" || $text == "/buy") {
    $locationproduct = select("marzban_panel", "*", "status", "activepanel", "count");
    if ($locationproduct == 0) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
        return;
    }
    if ($setting['get_number'] == "1" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        step('get_number', $from_id);
    }
    if ($user['number'] == "none" && $setting['get_number'] == "1")
        return;
    #-----------------------#
    if ($locationproduct == 1) {
        $panel = select("marzban_panel", "*", "status", "activepanel", "select");
        update("user","Processing_value",$panel['name_panel'],"id",$from_id,"select");
        if($setting['statuscategory'] == "0"){
            $nullproduct = select("product", "*", null, null, "count");
            if ($nullproduct == 0) {
                sendmessage($from_id, $textbotlang['Admin']['Product']['nullpProduct'], null, 'HTML');
                return;
            }
            $textproduct = "🛍 برای خرید اشتراک سرویس مدنظر خود را انتخاب کنید
لوکیشن سرویس  :{$panel['name_panel']} ";
            sendmessage($from_id,$textproduct, KeyboardProduct($panel['name_panel'],"backuser",$panel['MethodUsername']), 'HTML');
        }else{
            $emptycategory = select("category", "*", null, null, "count");
            if ($emptycategory == 0) {
                sendmessage($from_id, "❌ دسته بندی برای نمایش یافت نشد.", null, 'HTML');
                return;
            }
            if($datain == "buy"){
                Editmessagetext($from_id, $message_id, "📌 دسته بندی مورد نظر خود را انتخاب نمایید.", KeyboardCategorybuy("backuser",$panel['name_panel']));
            }else{
                sendmessage($from_id, "📌 دسته بندی مورد نظر خود را انتخاب نمایید.", KeyboardCategorybuy("backuser",$panel['name_panel']), 'HTML');
            }
        }
    } else {
        if($datain == "buy"){
            Editmessagetext($from_id, $message_id, $textbotlang['users']['Service']['Location'], $list_marzban_panel_user);
        }else{
            sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_panel_user, 'HTML');
        }
    }
}elseif (preg_match('/^categorylist_(.*)/', $datain, $dataget)) {
    $categoryid = $dataget[1];
    $product = [];
    $nullproduct = select("product", "*", null, null, "count");
    if ($nullproduct == 0) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['nullpProduct'], null, 'HTML');
        return;
    }
    $location = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if($location == false){
        sendmessage($from_id, "❌ خطایی رخ داده است مراحل خرید را از اول انجام دهید.", null, 'HTML');
        return;
    }
    $textproduct = "🛍 برای خرید اشتراک سرویس مدنظر خود را انتخاب کنید
لوکیشن سرویس  :{$location['name_panel']} ";
    Editmessagetext($from_id, $message_id,$textproduct, KeyboardProduct($location['name_panel'],"buy",$location['MethodUsername'], $categoryid));
    update("user", "Processing_value", $location['name_panel'], "id", $from_id);
}elseif (preg_match('/^location_(.*)/', $datain, $dataget)) {
    $locationid = $dataget[1];
    $panellist = select("marzban_panel", "*", "id", $locationid, "select");
    $location = $panellist['name_panel'];
    update("user", "Processing_value", $location, "id", $from_id);
    if($setting['statuscategory'] == "0"){
        $nullproduct = select("product", "*", null, null, "count");
        if ($nullproduct == 0) {
            sendmessage($from_id, $textbotlang['Admin']['Product']['nullpProduct'], null, 'HTML');
            return;
        }
        $textproduct = "🛍 برای خرید اشتراک سرویس مدنظر خود را انتخاب کنید
لوکیشن سرویس  :{$panellist['name_panel']} ";
        Editmessagetext($from_id, $message_id,$textproduct, KeyboardProduct($panellist['name_panel'],"buy",$panellist['MethodUsername']));
    }else{
        $emptycategory = select("category", "*", null, null, "count");
        if ($emptycategory == 0) {
            sendmessage($from_id, "❌ دسته بندی برای نمایش یافت نشد.", null, 'HTML');
            return;
        }
        Editmessagetext($from_id, $message_id, "📌 دسته بندی مورد نظر خود را انتخاب نمایید.", KeyboardCategorybuy("buy",$panellist['name_panel']));
    }
} elseif (preg_match('/^prodcutservices_(.*)/', $datain, $dataget)) {
    $prodcut = $dataget[1];
    update("user", "Processing_value_one", $prodcut, "id", $from_id);
    sendmessage($from_id, $textbotlang['users']['selectusername'], $backuser, 'html');
    step('endstepuser', $from_id);
} elseif ($user['step'] == "endstepuser" || preg_match('/prodcutservice_(.*)/', $datain, $dataget)) {
    if($user['step'] != "endstepuser"){
        $prodcut = $dataget[1];
    }
    $panellist = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($panellist['MethodUsername'] == "نام کاربری دلخواه") {
        if (!preg_match('~(?!_)^[a-z][a-z\d_]{2,32}(?<!_)$~i', $text)) {
            sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser, 'HTML');
            return;
        }
        $loc = $user['Processing_value_one'];
    } else {
        deletemessage($from_id, $message_id);
        $loc = $prodcut;
    }
    if($loc == null){
        sendmessage($from_id, '❌ خطایی رخ داده است مراحل خرید را مجددا انجام دهید', $keyboard, 'html');
        step("home",$from_id);
        return;
    }
    update("user", "Processing_value_one", $loc, "id", $from_id);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code_product AND (location = :loc1 OR location = '/all') LIMIT 1");
    $stmt->bindValue(':code_product', $loc);
    $stmt->bindValue(':loc1', $user['Processing_value']);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $randomString = bin2hex(random_bytes(2));
    $panellist = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $username_ac = strtolower(generateUsername($from_id, $panellist['MethodUsername'], $username, $randomString, $text));
    $DataUserOut = $ManagePanel->DataUser($panellist['name_panel'], $username_ac);
    $random_number = random_int(1000000, 9999999);
    if (isset ($DataUserOut['username']) || in_array($username_ac, $usernameinvoice)) {
        $username_ac = $random_number . $username_ac;
    }
    update("user", "Processing_value_tow", $username_ac, "id", $from_id);
    if ($info_product['Volume_constraint'] == 0)
        $info_product['Volume_constraint'] = $textbotlang['users']['stateus']['Unlimited'];
    $info_product['price_product'] = number_format($info_product['price_product'], 0);
    $user['Balance'] = number_format($user['Balance']);
    $textin = "
             📇 پیش فاکتور شما:
👤 نام کاربری: <code>$username_ac</code>
🔐 نام سرویس: {$info_product['name_product']}
📆 مدت اعتبار: {$info_product['Service_time']} روز
💶 قیمت: {$info_product['price_product']}  تومان
👥 حجم اکانت: {$info_product['Volume_constraint']} گیگ
💵 موجودی کیف پول شما : {$user['Balance']}
    
💰 سفارش شما آماده پرداخت است.  ";
    sendmessage($from_id, $textin, $payment, 'HTML');
    step('payment', $from_id);
} elseif ($user['step'] == "payment" && $datain == "confirmandgetservice" || $datain == "confirmandgetserviceDiscount") {
    Editmessagetext($from_id, $message_id, $text_callback, json_encode(['inline_keyboard' => []]));
    $partsdic = explode("_", $user['Processing_value_four']);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code AND (location = :loc1 OR location = '/all') LIMIT 1");
    $stmt->bindValue(':code', $user['Processing_value_one']);
    $stmt->bindValue(':loc1', $user['Processing_value']);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if($marzban_list_get['linksubx'] == null and in_array($marzban_list_get['type'],["x-ui_single","alireza"])){
        foreach ($admin_ids as $admin) {
            sendmessage($admin, "❌ ادمین عزیز پنل زیر فعال نیست جهت فعالسازی پنل باید حتما لینک ساب را از پنل ثنایی فعال و داخل ربات تنظیم کنید سپس در صورتی که میخواهید کانفیگ دهید یا لینک ساب از مدیریت پنل می توانید تنظیم نمایید.
نام پنل : {$marzban_list_get['name_panel']}", null, 'HTML');
        }
        sendmessage($from_id, "❌ پنل درحال حاضر فعال نمی باشد.", $keyboard, 'HTML');
        return;
    }
    $username_ac = $user['Processing_value_tow'];
    $date = time();
    $randomString = bin2hex(random_bytes(2));
    if (empty ($info_product['price_product']) || empty ($info_product['price_product']))
        return;
    if ($datain == "confirmandgetserviceDiscount") {
        $priceproduct = $partsdic[2];
    } else {
        $priceproduct = $info_product['price_product'];
    }
    if ($priceproduct > $user['Balance']) {
        $Balance_prim = $priceproduct - $user['Balance'];
        update("user","Processing_value",$Balance_prim, "id",$from_id);
        sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
        step('get_step_payment', $from_id);
        $stmt = $connect->prepare("INSERT IGNORE INTO invoice(id_user, id_invoice, username,time_sell, Service_location, name_product, price_product, Volume, Service_time,Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)");
        $Status =  "unpaid";
        $stmt->bind_param("ssssssssss", $from_id, $randomString, $username_ac, $date, $marzban_list_get['name_panel'], $info_product['name_product'], $info_product['price_product'], $info_product['Volume_constraint'], $info_product['Service_time'], $Status);
        $stmt->execute();
        $stmt->close();
        update("user","Processing_value_one",$username_ac, "id",$from_id);
        update("user","Processing_value_tow","getconfigafterpay", "id",$from_id);
        return;
    }
    if (in_array($randomString, $id_invoice)) {
        $randomString = $random_number . $randomString;
    }
    $sql = "INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $Status = "active";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $randomString);
    $stmt->bindParam(3, $username_ac, PDO::PARAM_STR);
    $stmt->bindParam(4, $date);
    $stmt->bindParam(5, $user['Processing_value'], PDO::PARAM_STR);
    $stmt->bindParam(6, $info_product['name_product'], PDO::PARAM_STR);
    $stmt->bindParam(7, $info_product['price_product']);
    $stmt->bindParam(8, $info_product['Volume_constraint']);
    $stmt->bindParam(9, $info_product['Service_time']);
    $stmt->bindParam(10, $Status);
    $stmt->execute();
    if($info_product['Service_time'] == "0"){
        $data = "0";
    }else{
        $date = strtotime("+" . $info_product['Service_time'] . "days");
        $data = strtotime(date("Y-m-d H:i:s", $date));
    }
    $datac = array(
        'expire' => $data,
        'data_limit' => $info_product['Volume_constraint'] * pow(1024, 3),
    );
    $dataoutput = $ManagePanel->createUser($marzban_list_get['name_panel'], $username_ac, $datac);
    if ($dataoutput['username'] == null) {
        $dataoutput['msg'] = json_encode($dataoutput['msg']);
        sendmessage($from_id, $textbotlang['users']['sell']['ErrorConfig'], $keyboard, 'HTML');
        $texterros = "
⭕️ یک کاربر قصد دریافت اکانت داشت که ساخت کانفیگ با خطا مواجه شده و به کاربر کانفیگ داده نشد
✍️ دلیل خطا : 
{$dataoutput['msg']}
آیدی کابر : $from_id
نام کاربری کاربر : @$username";
        foreach ($admin_ids as $admin) {
            sendmessage($admin, $texterros, null, 'HTML');
        }
        step('home', $from_id);
        return;
    }
    if ($datain == "confirmandgetserviceDiscount") {
        $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount", $partsdic[0], "select");
        $value = intval($SellDiscountlimit['usedDiscount']) + 1;
        update("DiscountSell", "usedDiscount", $value, "codeDiscount", $partsdic[0]);
        $text_report = "⭕️ یک کاربر با نام کاربری @$username  و آیدی عددی $from_id از کد تخفیف {$partsdic[0]} استفاده کرد.";
        if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
        }
    }
    $affiliatescommission = select("affiliates", "*", null, null, "select");
    if ($affiliatescommission['status_commission'] == "oncommission" && ($user['affiliates'] !== null || $user['affiliates'] != "0")) {
        $affiliatescommission = select("affiliates", "*", null, null, "select");
        $result = ($priceproduct * $affiliatescommission['affiliatespercentage']) / 100;
        $user_Balance = select("user", "*", "id", $user['affiliates'], "select");
        if($user_Balance){
            $Balance_prim = $user_Balance['Balance'] + $result;
            update("user", "Balance", $Balance_prim, "id", $user['affiliates']);
            $result = number_format($result);
            $textadd = "🎁  پرداخت پورسانت 
    
مبلغ $result تومان به حساب شما از طرف  زیر مجموعه تان به کیف پول شما واریز گردید";
            sendmessage($user['affiliates'], $textadd, null, 'HTML');
        }
    }
    $link_config = "";
    $text_config = "";
    $config = "";
    $configqr = "";
    if ($marzban_list_get['sublink'] == "onsublink") {
        $output_config_link = $dataoutput['subscription_url'];
        $link_config = "<code>$output_config_link</code>";
    }
    if ($marzban_list_get['configManual'] == "onconfig") {
        if(isset($dataoutput['configs']) and count($dataoutput['configs']) !=0){
            foreach ($dataoutput['configs'] as $configs) {
                $config .= "\n" . $configs;
                $configqr .= $configs;
            }
        }else{
            $config .= "";
            $configqr .= "";
        }
        $text_config = "<code>$config</code>";
    }
    $Shoppinginfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
            ]
        ]
    ]);
    $textcreatuser = "✅ سرویس با موفقیت ایجاد شد
    
👤 نام کاربری سرویس : <code>$username_ac</code>
🌿 نام سرویس: {$info_product['name_product']}
‏🇺🇳 لوکیشن: {$marzban_list_get['name_panel']}
⏳ مدت زمان: {$info_product['Service_time']}  روز
🗜 حجم سرویس:  {$info_product['Volume_constraint']} گیگ
    
لینک اتصال:
$text_config
$link_config
    
📚 راهنمای اتصال به سرویس را از طریق کلیک کردن دکمه زیر مطالعه بفرمایید";
    if ($marzban_list_get['sublink'] == "onsublink") {
        $urlimage = "$from_id$randomString.png";
        $writer = new PngWriter();
        $qrCode = QrCode::create($output_config_link)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(400)
            ->setMargin(0)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
        $result = $writer->write($qrCode, null, null);
        $result->saveToFile($urlimage);
        telegram('sendphoto', [
            'chat_id' => $from_id,
            'photo' => new CURLFile($urlimage),
            'reply_markup' => $Shoppinginfo,
            'caption' => $textcreatuser,
            'parse_mode' => "HTML",
        ]);
        sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
        unlink($urlimage);
    }elseif ($marzban_list_get['config'] == "onconfig") {
        if (count($dataoutput['configs']) == 1) {
            $urlimage = "$from_id$randomString.png";
            $writer = new PngWriter();
            $qrCode = QrCode::create($configqr)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
                ->setSize(400)
                ->setMargin(0)
                ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
            $result = $writer->write($qrCode,null, null);
            $result->saveToFile($urlimage);
            telegram('sendphoto', [
                'chat_id' => $from_id,
                'photo' => new CURLFile($urlimage),
                'reply_markup' => $Shoppinginfo,
                'caption' => $textcreatuser,
                'parse_mode' => "HTML",
            ]);
            unlink($urlimage);
        } else {
            sendmessage($from_id, $textcreatuser, $Shoppinginfo, 'HTML');
        }
    } else {
        sendmessage($from_id, $textcreatuser, $Shoppinginfo, 'HTML');
        sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
    }
    $Balance_prim = $user['Balance'] - $priceproduct;
    update("user", "Balance", $Balance_prim, "id", $from_id);
    $user['Balance'] = number_format($user['Balance'], 0);
    $text_report = " 🛍 خرید جدید
            
    ⚙️ یک کاربر اکانت  با نام کانفیگ <code>$username_ac</code> خریداری کرد
    
    قیمت محصول : {$info_product['price_product']} تومان
    حجم محصول : {$info_product['Volume_constraint']} 
    آیدی عددی کاربر : <code>$from_id</code>
    شماره تلفن کاربر : {$user['number']}
    موقعیت سرویس کاربر :{$user['Processing_value']}
    موجودی کاربر : {$user['Balance']} تومان
    
        اطلاعات کاربر 👇👇
        ⚜️ نام کاربری کاربر: @$username";
    if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
    step('home', $from_id);
} elseif ($datain == "aptdc") {
    sendmessage($from_id, $textbotlang['users']['Discount']['getcodesell'], $backuser, 'HTML');
    step('getcodesellDiscount', $from_id);
    deletemessage($from_id, $message_id);
} elseif ($user['step'] == "getcodesellDiscount") {
    if (!in_array($text, $SellDiscount)) {
        sendmessage($from_id, $textbotlang['users']['Discount']['notcode'], $backuser, 'HTML');
        return;
    }
    $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount", $text, "select");
    if ($SellDiscountlimit == false) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['invalidcodedis'], null, 'HTML');
        return;
    }
    $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount", $text, "select");
    if ($SellDiscountlimit['limitDiscount'] == $SellDiscountlimit['usedDiscount']) {
        sendmessage($from_id, $textbotlang['users']['Discount']['erorrlimit'], null, 'HTML');
        return;
    }
    if ($SellDiscountlimit['usefirst'] == "1") {
        $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user");
        $stmt->bindParam(':id_user', $from_id);
        $stmt->execute();
        $countinvoice = $stmt->rowCount();
        if ($countinvoice != 0) {
            sendmessage($from_id, $textbotlang['users']['Discount']['firstdiscount'], null, 'HTML');
            return;
        }

    }
    sendmessage($from_id, $textbotlang['users']['Discount']['correctcode'], $keyboard, 'HTML');
    step('payment', $from_id);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code AND (location = :loc1 OR location = '/all') LIMIT 1");
    $stmt->bindValue(':code', $user['Processing_value_one']);
    $stmt->bindValue(':loc1', $user['Processing_value']);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = ($SellDiscountlimit['price'] / 100) * $info_product['price_product'];

    $info_product['price_product'] = $info_product['price_product'] - $result;
    $info_product['price_product'] = round($info_product['price_product']);
    if ($info_product['price_product'] < 0)
        $info_product['price_product'] = 0;
    $textin = "
             📇 پیش فاکتور شما:
👤 نام کاربری: <code>{$user['Processing_value_tow']}</code>
🔐 نام سرویس: {$info_product['name_product']}
📆 مدت اعتبار: {$info_product['Service_time']} روز
💶 قیمت: {$info_product['price_product']}  تومان
👥 حجم اکانت: {$info_product['Volume_constraint']} گیگ
💵 موجودی کیف پول شما : {$user['Balance']}
              
💰 سفارش شما آماده پرداخت است.  ";
    $paymentDiscount = json_encode([
        'inline_keyboard' => [
            [['text' => "💰 پرداخت و دریافت سرویس", 'callback_data' => "confirmandgetserviceDiscount"]],
            [['text' => $textbotlang['users']['backhome'], 'callback_data' => "backuser"]]
        ]
    ]);
    $parametrsendvalue = "dis_".$text . "_" . $info_product['price_product'];
    update("user", "Processing_value_four", $parametrsendvalue, "id", $from_id);
    sendmessage($from_id, $textin, $paymentDiscount, 'HTML');
}



#-------------------[ text_Add_Balance ]---------------------#
if ($text == $datatextbot['text_Add_Balance'] || $text == "/wallet") {
    if ($setting['get_number'] == "1" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        step('get_number', $from_id);
    }
    if ($user['number'] == "none" && $setting['get_number'] == "1")
        return;
    sendmessage($from_id, $textbotlang['users']['Balance']['priceinput'], $backuser, 'HTML');
    step('getprice', $from_id);
} elseif ($user['step'] == "getprice") {
    if (!is_numeric($text))
        return sendmessage($from_id, $textbotlang['users']['Balance']['errorprice'], null, 'HTML');
    if ($text > 10000000 or $text < 20000)
        return sendmessage($from_id, $textbotlang['users']['Balance']['errorpricelimit'], null, 'HTML');
    update("user", "Processing_value", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['users']['Balance']['selectPatment'], $step_payment, 'HTML');
    step('get_step_payment', $from_id);
} elseif ($user['step'] == "get_step_payment") {
    if ($datain == "cart_to_offline") {
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "CartDescription", "select")['ValuePay'];
        $Processing_value = number_format($user['Processing_value']);
        $textcart = "برای افزایش موجودی به صورت دستی، مبلغ $Processing_value  تومان  را به شماره‌ی حساب زیر واریز کنید 👇🏻
    
    ==================== 
    $PaySetting
    ====================
    
🌅 عکس رسید خود را در این مرحله ارسال نمایید. 
    
⚠️ حداکثر واریز مبلغ 10 میلیون تومان می باشد.
⚠️ امکان برداشت وجه از کیف پول  نیست.
⚠️ مسئولیت واریز اشتباهی با شماست.";
        sendmessage($from_id, $textcart, $backuser, 'HTML');
        step('cart_to_cart_user', $from_id);
    }
    if ($datain == "aqayepardakht") {
        if ($user['Processing_value'] < 5000) {
            sendmessage($from_id, $textbotlang['users']['Balance']['zarinpal'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $payment_Status = "Unpaid";
        $Payment_Method = "aqayepardakht";
        if($user['Processing_value_tow'] == "getconfigafterpay"){
            $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        }else{
            $invoice = "0|0";
        }
        $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method,invoice) VALUES (?, ?, ?, ?, ?, ?,?)");
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value'], PDO::PARAM_STR);
        $stmt->bindParam(5, $payment_Status);
        $stmt->bindParam(6, $Payment_Method);
        $stmt->bindParam(7, $invoice);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/aqayepardakht/aqayepardakht.php?price={$user['Processing_value']}&order_id=$randomString"],
                ]
            ]
        ]);
        $user['Processing_value'] = number_format($user['Processing_value'], 0);
        $textnowpayments = "
✅ فاکتور پرداخت ایجاد شد.
        
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : {$user['Processing_value']} تومان
    
    جهت پرداخت از دکمه زیر استفاده کنید👇🏻";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }

    if ($datain == "nowpayments") {
        $price_rate = tronratee();
        $USD = $price_rate['result']['USD'];
        $usdprice = round($user['Processing_value'] / $USD, 2);
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $payment_Status = "Unpaid";
        $Payment_Method = "Nowpayments";
        if($user['Processing_value_tow'] == "getconfigafterpay"){
            $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        }else{
            $invoice = "0|0";
        }
        $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method,invoice) VALUES (?, ?, ?, ?, ?, ?,?)");
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value'], PDO::PARAM_STR);
        $stmt->bindParam(5, $payment_Status);
        $stmt->bindParam(6, $Payment_Method);
        $stmt->bindParam(7, $invoice);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/nowpayments/nowpayments.php?price=$usdprice&order_description=Add_Balance&order_id=$randomString"],
                ]
            ]
        ]);
        $Processing_value = number_format($user['Processing_value'], 0);
        $USD = number_format($USD, 0);
        $textnowpayments = "
            ✅ فاکتور پرداخت ارزی NOWPayments ایجاد شد.
        
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : $Processing_value تومان
        
📊 قیمت دلار روز : $USD تومان
💵 نهایی:$usdprice دلار 
        
        
🌟 امکان پرداخت با ارز های مختلف وجود دارد
        
جهت پرداخت از دکمه زیر استفاده کنید👇🏻";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }
    if ($datain == "iranpay") {
        $price_rate = tronratee();
        $trx = $price_rate['result']['TRX'];
        $usd = $price_rate['result']['USD'];
        $trxprice = round($user['Processing_value'] / $trx, 2);
        $usdprice = round($user['Processing_value'] / $usd, 2);
        if ($trxprice <= 1) {
            sendmessage($from_id, $textbotlang['users']['Balance']['changeto'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d H:i:s');
        $randomString = bin2hex(random_bytes(5));
        $payment_Status = "Unpaid";
        $Payment_Method = "Currency Rial gateway";
        if($user['Processing_value_tow'] == "getconfigafterpay"){
            $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
        }else{
            $invoice = "0|0";
        }
        $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method,invoice) VALUES (?, ?, ?, ?, ?, ?,?)");
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value'], PDO::PARAM_STR);
        $stmt->bindParam(5, $payment_Status);
        $stmt->bindParam(6, $Payment_Method);
        $stmt->bindParam(7, $invoice);
        $stmt->execute();
        $order_description = "SwapinoBot_" . $randomString . "_" . $trxprice;
        $pay = nowPayments('payment', $usdprice, $randomString, $order_description);
        if (!isset ($pay->pay_address)) {
            $text_error = $pay->message;
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home', $from_id);
            foreach ($admin_ids as $admin) {
                $ErrorsLinkPayment = sprintf($textbotlang['users']['Balance']['payment_error_admin'], $text_error, $from_id, $username);
                sendmessage($admin, $ErrorsLinkPayment, $keyboard, 'HTML');
            }
            return;
        }
        $trxprice = str_replace('.', "_", strval($pay->pay_amount));
        $pay_address = $pay->pay_address;
        $payment_id = $pay->payment_id;
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://t.me/SwapinoBot?start=trx-$pay_address-$trxprice-Tron"]
                ],
                [
                    ['text' => $textbotlang['users']['Balance']['Confirmpaying'], 'callback_data' => "Confirmpay_user_{$payment_id}_{$randomString}"]
                ]
            ]
        ]);
        $pricetoman = number_format($user['Processing_value'], 0);
        $textnowpayments = $textbotlang['users']['Balance']['transaction_created'] . "\n\n" .
            $textbotlang['users']['Balance']['tracking_code'] . " <code>$randomString</code>\n" .
            $textbotlang['users']['Balance']['network'] . " TRX\n" .
            $textbotlang['users']['Balance']['wallet_address'] . " <code>$pay_address</code>\n" .
            $textbotlang['users']['Balance']['amount_trx'] . " <code>$trxprice</code>\n" .
            $textbotlang['users']['Balance']['amount_toman'] . " <code>$pricetoman</code>\n" .
            $textbotlang['users']['Balance']['trx_rate'] . " <code>$trx</code>\n";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }
    if ($datain == "perfectmoney") {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['perfectmoney']['getvcode'], $backuser, 'HTML');
        step('getvcodeuser', $from_id);
    }

}
if ($user['step'] == "getvcodeuser") {
    update("user", "Processing_value", $text, "id", $from_id);
    step('getvnumbervuser', $from_id);
    sendmessage($from_id, $textbotlang['users']['perfectmoney']['getvnumber'], $backuser, 'HTML');
} elseif ($user['step'] == "getvnumbervuser") {
    step('home', $from_id);
    $Voucher = ActiveVoucher($user['Processing_value'], $text);
    $lines = explode("\n", $Voucher);
    foreach ($lines as $line) {
        if (strpos($line, "Error:") !== false) {
            $errorMessage = trim(str_replace("Error:", "", $line));
            break;
        }
    }
    if ($errorMessage == "Invalid ev_number or ev_code") {
        sendmessage($from_id, $textbotlang['users']['perfectmoney']['invalidvcodeorev'], $keyboard, 'HTML');
        return;
    }
    if ($errorMessage == "Invalid ev_number") {
        sendmessage($from_id, $textbotlang['users']['perfectmoney']['invalid_ev_number'], $keyboard, 'HTML');
        return;
    }
    if ($errorMessage == "Invalid ev_code") {
        sendmessage($from_id, $textbotlang['users']['perfectmoney']['invalidvcode'], $keyboard, 'HTML');
        return;
    }
    if (isset ($errorMessage)) {
        sendmessage($from_id, $textbotlang['users']['perfectmoney']['errors'], null, 'HTML');
        foreach ($admin_ids as $id_admin) {
            $texterrors = "";
            sendmessage($id_admin, "❌ یک کاربر قصد افزایش موجودی با ووچر را داشته اما با خطا مواجه شده است 
    
دلیل خطا : $errorMessage", null, 'HTML');
        }
        return;
    }
    $Balance_id = select("user", "*", "id", $from_id, "select");
    $startTag = "<td>VOUCHER_AMOUNT</td><td>";
    $endTag = "</td>";
    $startPos = strpos($Voucher, $startTag) + strlen($startTag);
    $endPos = strpos($Voucher, $endTag, $startPos);
    $voucherAmount = substr($Voucher, $startPos, $endPos - $startPos);
    $USD = $voucherAmount * json_decode(file_get_contents('https://api.tetherland.com/currencies'), true)['data']['currencies']['USDT']['price'];
    $USD = number_format($USD, 0);
    update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
    $randomString = bin2hex(random_bytes(5));
    $dateacc = date('Y/m/d H:i:s');
    $payment_Status = "paid";
    $Payment_Method = "perfectmoney";
    if($user['Processing_value_tow'] == "getconfigafterpay"){
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
    }else{
        $invoice = "0|0";
    }
    $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method,invoice) VALUES (?, ?, ?, ?, ?, ?,?)");
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $randomString);
    $stmt->bindParam(3, $dateacc);
    $stmt->bindParam(4, $USD);
    $stmt->bindParam(5, $payment_Status);
    $stmt->bindParam(6, $Payment_Method);
    $stmt->bindParam(7, $invoice);
    $stmt->execute();
    DirectPayment($randomString);
    update("user","Processing_value","0", "id",$Balance_id['id']);
    update("user","Processing_value_one","0", "id",$Balance_id['id']);
    update("user","Processing_value_tow","0", "id",$Balance_id['id']);
}
if (preg_match('/Confirmpay_user_(\w+)_(\w+)/', $datain, $dataget)) {
    $id_payment = $dataget[1];
    $id_order = $dataget[2];
    $Payment_report = select("Payment_report", "*", "id_order", $id_order, "select");
    if ($Payment_report['payment_Status'] == "paid") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['Balance']['Confirmpayadmin'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
        return;
    }
    $StatusPayment = StatusPayment($id_payment);
    if ($StatusPayment['payment_status'] == "finished") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['Balance']['finished'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
        $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
        $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
        update("user", "Balance", $Balance_confrim, "id", $Payment_report['id_user']);
        update("Payment_report", "payment_Status", "paid", "id_order", $Payment_report['id_order']);
        sendmessage($from_id, $textbotlang['users']['Balance']['Confirmpay'], null, 'HTML');
        $Payment_report['price'] = number_format($Payment_report['price']);
        $text_report = "💵 پرداخت جدید
            
آیدی عددی کاربر : $from_id
مبلغ تراکنش : {$Payment_report['price']} 
روش پرداخت :  درگاه ارزی ریالی";
        if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
        }
    } elseif ($StatusPayment['payment_status'] == "expired") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['Balance']['expired'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
    } elseif ($StatusPayment['payment_status'] == "refunded") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['Balance']['refunded'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
    } elseif ($StatusPayment['payment_status'] == "waiting") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['Balance']['waiting'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
    } elseif ($StatusPayment['payment_status'] == "sending") {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['Balance']['sending'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
    } else {
        telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => $textbotlang['users']['Balance']['Failed'],
                'show_alert' => true,
                'cache_time' => 5,
            )
        );
    }
} elseif ($user['step'] == "cart_to_cart_user") {
    if (!$photo) {
        sendmessage($from_id, $textbotlang['users']['Balance']['Invalid-receipt'], null, 'HTML');
        return;
    }
    $dateacc = date('Y/m/d H:i:s');
    $randomString = bin2hex(random_bytes(5));
    $payment_Status = "Unpaid";
    $Payment_Method = "cart to cart";
    if($user['Processing_value_tow'] == "getconfigafterpay"){
        $invoice = "{$user['Processing_value_tow']}|{$user['Processing_value_one']}";
    }else{
        $invoice = "0|0";
    }
    $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method,invoice) VALUES (?, ?, ?, ?, ?, ?,?)");
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $randomString);
    $stmt->bindParam(3, $dateacc);
    $stmt->bindParam(4, $user['Processing_value'], PDO::PARAM_STR);
    $stmt->bindParam(5, $payment_Status);
    $stmt->bindParam(6, $Payment_Method);
    $stmt->bindParam(7, $invoice);
    $stmt->execute();
    if ($user['Processing_value_tow'] == "getconfigafterpay"){
        sendmessage($from_id, "🚀 رسید پرداخت شما ارسال شد پس از تایید توسط مدیریت سفارش شما ارسال خواهد شد", $keyboard, 'HTML');
    }else{
        sendmessage($from_id, $textbotlang['users']['Balance']['Send-receipt'], $keyboard, 'HTML');
    }
    $Confirm_pay = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['Balance']['Confirmpaying'], 'callback_data' => "Confirm_pay_{$randomString}"],
                ['text' => $textbotlang['users']['Balance']['reject_pay'], 'callback_data' => "reject_pay_{$randomString}"],
            ]
        ]
    ]);
    $Processing_value = number_format($user['Processing_value']);
    $textsendrasid = "
                ⭕️ یک پرداخت جدید انجام شده است .
            
👤 شناسه کاربر: $from_id
🛒 کد پیگیری پرداخت: $randomString
⚜️ نام کاربری: @$username
💸 مبلغ پرداختی: $Processing_value تومان
            
توضیحات: $caption
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
    foreach ($admin_ids as $id_admin) {
        telegram('sendphoto', [
            'chat_id' => $id_admin,
            'photo' => $photoid,
            'reply_markup' => $Confirm_pay,
            'caption' => $textsendrasid,
            'parse_mode' => "HTML",
        ]);
    }
    step('home', $from_id);
}

#----------------Discount------------------#
if ($datain == "Discount") {
    sendmessage($from_id, $textbotlang['users']['Discount']['getcode'], $backuser, 'HTML');
    step('get_code_user', $from_id);
} elseif ($user['step'] == "get_code_user") {
    if (!in_array($text, $code_Discount)) {
        sendmessage($from_id, $textbotlang['users']['Discount']['notcode'], null, 'HTML');
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM Giftcodeconsumed WHERE id_user = :id_user");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $Checkcode = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Checkcode[] = $row['code'];
    }
    if (in_array($text, $Checkcode)) {
        sendmessage($from_id, $textbotlang['users']['Discount']['onecode'], $keyboard, 'HTML');
        step('home', $from_id);
        return;
    }
    $stmt = $pdo->prepare("SELECT * FROM Discount WHERE code = :code LIMIT 1");
    $stmt->bindParam(':code', $text, PDO::PARAM_STR);
    $stmt->execute();
    $get_codesql = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance_user = $user['Balance'] + $get_codesql['price'];
    update("user", "Balance", $balance_user, "id", $from_id);
    $stmt = $pdo->prepare("SELECT * FROM Discount WHERE code = :code");
    $stmt->bindParam(':code', $text, PDO::PARAM_STR);
    $stmt->execute();
    $get_codesql = $stmt->fetch(PDO::FETCH_ASSOC);
    step('home', $from_id);
    number_format($get_codesql['price']);
    $text_balance_code = "کد هدیه با موفقیت ثبت شد و به موجودی شما مبلغ {$get_codesql['price']} تومان اضافه گردید. 🥳";
    sendmessage($from_id, $text_balance_code, $keyboard, 'HTML');
    $stmt = $pdo->prepare("INSERT INTO Giftcodeconsumed (id_user, code) VALUES (?, ?)");
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $text, PDO::PARAM_STR);
    $stmt->execute();
    $text_report = "کد هدیه $text استفاده شد
            
    اطلاعات کاربر : 
            
🪪 آیدی عددی : <code>$from_id</code>
🪪  نام کاربری : @$username
💰 مبلغ هدیه {$get_codesql['price']} تومان";
    if (isset($setting['Channel_Report']) &&strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
}
#----------------[  text_Tariff_list  ]------------------#
if ($text == $datatextbot['text_Tariff_list']) {
    sendmessage($from_id, $datatextbot['text_dec_Tariff_list'], null, 'HTML');
}
if ($datain == "closelist") {
    deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['users']['back'], $keyboard, 'HTML');
}
if ($text == "👥 زیر مجموعه گیری") {
    $affiliatesvalue = select("affiliates", "*", null, null, "select")['affiliatesstatus'];
    if ($affiliatesvalue == "offaffiliates") {
        sendmessage($from_id, $textbotlang['users']['affiliates']['offaffiliates'], $keyboard, 'HTML');
        return;
    }
    $affiliates = select("affiliates", "*", null, null, "select");
    $textaffiliates = "{$affiliates['description']}\n\n🔗 https://t.me/$usernamebot?start=$from_id";
    telegram('sendphoto', [
        'chat_id' => $from_id,
        'photo' => $affiliates['id_media'],
        'caption' => $textaffiliates,
        'parse_mode' => "HTML",
    ]);
    $affiliatescommission = select("affiliates", "*", null, null, "select");
    if ($affiliatescommission['status_commission'] == "oncommission") {
        $affiliatespercentage = $affiliatescommission['affiliatespercentage'] . " درصد";
    } else {
        $affiliatespercentage = "غیرفعال";
    }
    if ($affiliatescommission['Discount'] == "onDiscountaffiliates") {
        $price_Discount = $affiliatescommission['price_Discount'] . " تومان";
    } else {
        $price_Discount = "غیرفعال";
    }
    $textaffiliates = "🤔 زیرمجموعه گیری به چه صورت است ؟
    
👨🏻‍💻 ما برای شما محیطی فراهم کرده ایم  تا بتوانید بدون پرداخت حتی 1 ریال به ما، بتوانید موجودی کیف پول خودتان را در ربات افزایش دهید و از خدمات ربات استفاده نمایید.
    
👥 شما میتوانید با دعوت دوستان و آشنایان خود به ربات ما از طریق لینک اختصاصی شما! کسب درآمد کنید و حتی با هر خرید زیرمجموعه ها به شما پورسانت داده خواهد شد.

👤 شما می توانید با استفاده از بنر بالا برای خود زیرمجموعه جمع کنید
    
💵 مبلغ هدیه به ازای هر عضویت :  $price_Discount
💴 میزان پورسانت از خرید زیرمجموعه :  $affiliatespercentage";
    sendmessage($from_id, $textaffiliates, $keyboard, 'HTML');
}
require_once 'admin.php';
$connect->close();