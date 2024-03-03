<?php
ini_set('error_log', 'error_log');

date_default_timezone_set('Asia/Tehran');
require_once 'config.php';
require_once 'botapi.php';
require_once 'apipanel.php';
require_once 'jdf.php';
require_once 'keyboard.php';
require_once 'text.php';
require_once 'functions.php';
require_once 'vendor/autoload.php';
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
#-----------telegram_ip_ranges------------#
$telegram_ip_ranges = [
    ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
    ['lower' => '91.108.4.0',    'upper' => '91.108.7.255']
];
$trust_ips = ['104.255.68.103'];
$ip = "";
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
    if (count($trust_ips)<1 || in_array($_SERVER['REMOTE_ADDR'], $trust_ips)) {
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = $ips[0];
    }
} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
    if (in_array($_SERVER['REMOTE_ADDR'], $trust_ips)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
    if (in_array($_SERVER['REMOTE_ADDR'], $trust_ips)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
} elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
    if (in_array($_SERVER['REMOTE_ADDR'], $trust_ips)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$ip_dec = (float)sprintf("%u", ip2long($ip));
$ok = false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
    $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
    $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
    if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok = true;
}
if (!$ok) die("دسترسی غیرمجاز");
#-------------Variable----------#
$setting = select("setting", "*");
$stmt = $pdo->prepare("INSERT IGNORE INTO user (id, step, limit_usertest, User_Status, number, Balance, pagenumber, username, message_count, last_message_time, affiliatescount, affiliates) VALUES (:from_id, 'none', :limit_usertest_all, 'Active', 'none', '0', '1', :username, '0', '0', '0', '0')");
$stmt->bindParam(':from_id', $from_id);
$stmt->bindParam(':limit_usertest_all', $setting['limit_usertest_all']);
$stmt->bindParam(':username', $username);
$stmt->execute();
$version = file_get_contents('install/version');
$user = select("user", "*", "id", $from_id,"select");
if ($user == false) {
    $user = array();
    $user = array(
        'step' => '',
        'Processing_value' => '',
        'User_Status' => '',
        'username' => '',
        'limit_usertest' =>'',
        'last_message_time' => '',
        'affiliates' => '',
    );
}
$channels = array();
$helpdata = select("help", "*");
$datatextbotget = select("textbot", "*",null ,null ,"fetchAll");
$id_invoice = select("invoice", "id_invoice",null,null,"FETCH_COLUMN");
$channels = select("channels", "*");
$admin_ids = select("admin", "id_admin",null,null,"FETCH_COLUMN");
$usernameinvoice = select("invoice", "username",null,null,"FETCH_COLUMN");
$code_Discount = select("Discount", "code",null,null,"FETCH_COLUMN");
$users_ids = select("user", "id",null,null,"FETCH_COLUMN");
$marzban_list = select("marzban_panel", "name_panel",null,null,"FETCH_COLUMN");
$name_product = select("product", "name_product",null,null,"FETCH_COLUMN");
$SellDiscount = select("DiscountSell", "codeDiscount",null,null,"FETCH_COLUMN");
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
    'text_account'  => '',
    'text_sell' => '',
    'text_Add_Balance' => '',
    'text_channel' => '',
    'text_Discount' => '',
    'text_Tariff_list' => '',
    'text_dec_Tariff_list' => '',
);
foreach ($datatxtbot as $item) {
    if (isset($datatextbot[$item['id_text']])) {
        $datatextbot[$item['id_text']] = $item['text'];
    }
}
#---------channel--------------#
$tch = '';
if (isset($channels['link']) && $from_id != 0) {
    $response = json_decode(file_get_contents('https://api.telegram.org/bot' . $APIKEY . "/getChatMember?chat_id=@{$channels['link']}&user_id=$from_id"));
    $tch = $response->result->status;
}
if($user['username'] == "none" || $user['username'] == null){
    update("user", "username", $username, "id",$from_id);
}
#-----------User_Status------------#
if ($user['User_Status'] == "block") {
    $textblock = "
               🚫 شما از طرف مدیریت بلاک شده اید.
                
            ✍️ دلیل مسدودی: {$user['description_blocking']}
                ";
    sendmessage($from_id, $textblock, null, 'html');
    return;
}
    if (strpos($text, "/start ") !== false) {
        if ($user['affiliates'] != 0) {
            sendmessage($from_id, "❌ شما زیرمجموعه کاربر {$user['affiliates']} هستید و نمی توانید زیر مجموعه کاربری دیگه ای باشید", null, 'html');
            return;
        }
        $affiliatesvalue = select("affiliates", "*", null, null,"select")['affiliatesstatus'];
        if ($affiliatesvalue == "offaffiliates") {
            sendmessage($from_id, $textbotlang['users']['affiliates']['offaffiliates'], $keyboard, 'HTML');
            return;
        }
        $affiliatesid = str_replace("/start ", "", $text);
        if(!ctype_digit($affiliatesid))return;
        if(!in_array($affiliatesid,$users_ids)){
        sendmessage($from_id, "❌امکان زیرمجموعه شدن با این شناسه کاربری وجود ندارد.", null, 'html');
        return;
    }
        if ($affiliatesid == $from_id) {
            sendmessage($from_id, $textbotlang['users']['affiliates']['invalidaffiliates'], null, 'html');
            return;
        }
        $marzbanDiscountaffiliates = select("affiliates", "*", null, null,"select");
        if ($marzbanDiscountaffiliates['Discount'] == "onDiscountaffiliates") {
            $marzbanDiscountaffiliates = select("affiliates", "*", null, null,"select");
            $Balance_user =  select("user", "*", "id", $affiliatesid,"select");
            $Balance_add_user = $Balance_user['Balance'] + $marzbanDiscountaffiliates['price_Discount'];
            update("user", "Balance", $Balance_add_user, "id",$affiliatesid);
            $addbalancediscount = number_format($marzbanDiscountaffiliates['price_Discount'], 0);
            sendmessage($affiliatesid, "🎁 مبلغ $addbalancediscount به موجودی شما از طرف زیر مجموعه با شناسه کاربری $from_id اضافه گردید.", null, 'html');
        }
        sendmessage($from_id, $datatextbot['text_start'], $keyboard, 'html');
            $useraffiliates =  select("user", "*", "id", $affiliatesid,"select");
        $addcountaffiliates = intval($useraffiliates['affiliatescount']) + 1;
        update("user", "affiliates", $affiliatesid, "id",$from_id);
        update("user", "affiliatescount", $addcountaffiliates, "id",$affiliatesid);
    }
$timebot= time();
$TimeLastMessage=  $timebot - intval($user['last_message_time']);
if(floor($TimeLastMessage / 60) >= 1){
    update("user", "last_message_time", $timebot, "id",$from_id);
    update("user", "message_count", "1", "id",$from_id);
}else{
if(!in_array($from_id,$admin_ids)){
$addmessage = intval($user['message_count']) + 1;
update("user", "message_count", $addmessage, "id",$from_id);
if($user['message_count'] >= "35"){
    $User_Status = "block";
    update("user", "User_Status", $User_Status, "id",$from_id);
    update("user", "description_blocking", $textbotlang['users']['spam']['spamed'], "id",$from_id);
    sendmessage($from_id, $textbotlang['users']['spam']['spamedmessage'], null, 'html');
    return;
}        

}
}#-----------Channel------------#
if($datain == "confirmchannel"){
    if(!in_array($tch, ['member', 'creator', 'administrator'])){
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['channel']['notconfirmed'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
    }else{
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
if ($setting['roll_Status'] == "✅ تایید قانون روشن است" && $user['roll_Status'] == 0 && $text != "✅ قوانین را می پذیرم" && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_roll'], $confrimrolls, 'html');
    return;
}
if ($text == "✅ قوانین را می پذیرم") {
    sendmessage($from_id, $textbotlang['users']['Rules'], $keyboard, 'html');
    $confrim = true;
    update("user", "roll_Status", $confrim, "id",$from_id);
}

#-----------Bot_Status------------#
if ($setting['Bot_Status'] == "❌ ربات خاموش است" && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_bot_off'], null, 'html');
    return;
}
#-----------/start------------#
if ($text == "/start") {
    $file_path = 'install/data.php';
    if (file_exists($file_path)) {
    unlink($file_path);
}
    sendmessage($from_id, $datatextbot['text_start'], $keyboard, 'html');
    step('home',$from_id);
    return;
}
#-----------back------------#
if ($text == "🏠 بازگشت به منوی اصلی" || $datain == "backuser") {
    if($datain == "backuser")deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['users']['back'], $keyboard, 'html');
    step('home',$from_id);
    return;
}
#-----------get_number------------#
if ($user['step'] == 'get_number') {
    if (empty($user_phone)) {
        sendmessage($from_id, $textbotlang['users']['number']['false'], $request_contact, 'html');
        return;
    }
    if ($contact_id != $from_id) {
        sendmessage($from_id, $textbotlang['users']['number']['Warning'], $request_contact, 'html');
        return;
    }
    if ($setting['iran_number'] == "✅ احرازشماره ایرانی روشن است" && !preg_match("/989[0-9]{9}$/", $user_phone)) {
        sendmessage($from_id, $textbotlang['users']['number']['erroriran'], $request_contact, 'html');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['number']['active'], $keyboard, 'html');
    update("user", "number", $user_phone, "id",$from_id);
    step('home',$from_id);
}

#-----------Purchased services------------#
if ($text == $datatextbot['text_Purchased_services'] || $datain == "backorder") {
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND status = 'active'");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $invoices = $stmt->rowCount();
    if ($invoices == 0 && $setting['NotUser'] == "offnotuser") {
        sendmessage($from_id, $textbotlang['users']['sell']['service_not_available'], null, 'html');
        return;
    }
     update("user", "pagenumber", "1", "id",$from_id);
    $page = 1;
    $items_per_page = 5;
    $start_index = ($page - 1) * $items_per_page;
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND status = 'active' ORDER BY username ASC LIMIT $start_index, $items_per_page");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "⭕️" . $row['username'] . "⭕️",
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
            'text' =>  $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_page'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    if ($datain == "backorder") {
        Editmessagetext($from_id, $message_id, $textbotlang['users']['sell']['service_sell'], $keyboard_json);
    } else {
        sendmessage($from_id, $textbotlang['users']['sell']['service_sell'], $keyboard_json, 'html');
    }
    if ($setting['NotUser'] == "onnotuser") {
        sendmessage($from_id, $textbotlang['users']['stateus']['notUsername'], $NotProductUser, 'html');
    }
}
if ($text == "⭕️ نام کاربری من در لیست نیست ⭕️") {
    sendmessage($from_id, $textbotlang['users']['stateus']['SendUsername'], $backuser, 'html');
    step('getusernameinfo',$from_id);
}
if ($user['step'] == "getusernameinfo") {
    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['users']['stateus']['Invalidusername'], $backuser, 'html');
        return;
    }
     update("user", "Processing_value", $text, "id",$from_id);
    sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_panel_user, 'html');
    step('getdata',$from_id);
} elseif (preg_match('/locationnotuser_(.*)/', $datain, $dataget)) {
    $location = $dataget[1];
    $marzban_list_get = select("marzban_panel", "name_panel", "name_panel", $location,"select");
    $data_useer = getuser($user['Processing_value'],$marzban_list_get['name_panel']);
    if ($data_useer['detail'] == "User not found") {
        sendmessage($from_id, $textbotlang['users']['stateus']['notUsernameget'], $keyboard, 'html');
        step('home',$from_id);
        return;
    }
    #-------------[ status ]----------------#
    $status = $data_useer['status'];
    $status_var = [
        'active' =>  $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired']
    ][$status];
    #--------------[ expire ]---------------#
    $expirationDate = $data_useer['expire'] ? jdate('Y/m/d', $data_useer['expire']) : $textbotlang['users']['stateus']['Unlimited'];
    #-------------[ data_limit ]----------------#
    $LastTraffic = $data_useer['data_limit'] ? formatBytes($data_useer['data_limit']) : $textbotlang['users']['stateus']['Unlimited'];
    #---------------[ RemainingVolume ]--------------#
    $output =  $data_useer['data_limit'] - $data_useer['used_traffic'];
    $RemainingVolume = $data_useer['data_limit'] ? formatBytes($output) : "نامحدود";
    #---------------[ used_traffic ]--------------#
    $usedTrafficGb = $data_useer['used_traffic'] ? formatBytes($data_useer['used_traffic']) : $textbotlang['users']['stateus']['Notconsumed'];
    #--------------[ day ]---------------#
    $timeDiff = $data_useer['expire'] - time();
    $day = $data_useer['expire'] ? floor($timeDiff / 86400) + 1 . $textbotlang['users']['stateus']['day'] : $textbotlang['users']['stateus']['Unlimited'];
    #-----------------------------#


    $keyboardinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $data_useer['username'], 'callback_data' => "username"],
                ['text' => $textbotlang['users']['stateus']['username'], 'callback_data' => 'username'],
            ], [
                ['text' => $status_var, 'callback_data' => 'status_var'],
                ['text' => $textbotlang['users']['stateus']['stateus'], 'callback_data' => 'status_var'],
            ], [
                ['text' => $expirationDate, 'callback_data' => 'expirationDate'],
                ['text' => $textbotlang['users']['stateus']['expirationDate'], 'callback_data' => 'expirationDate'],
            ], [], [
                ['text' => $day, 'callback_data' => 'روز'],
                ['text' => $textbotlang['users']['stateus']['daysleft'], 'callback_data' => 'day'],
            ], [
                ['text' => $LastTraffic, 'callback_data' => 'LastTraffic'],
                ['text' => $textbotlang['users']['stateus']['LastTraffic'], 'callback_data' => 'LastTraffic'],
            ], [
                ['text' => $usedTrafficGb, 'callback_data' => 'expirationDate'],
                ['text' => $textbotlang['users']['stateus']['usedTrafficGb'], 'callback_data' => 'expirationDate'],
            ], [
                ['text' => $RemainingVolume, 'callback_data' => 'RemainingVolume'],
                ['text' => $textbotlang['users']['stateus']['RemainingVolume'], 'callback_data' => 'RemainingVolume'],
            ]
        ]
    ]);
    sendmessage($from_id, $textbotlang['users']['stateus']['info'], $keyboardinfo, 'html');
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'html');
    step('home',$from_id);
}
if ($datain == 'next_page') {
    $numpage =  select("invoice", "id_user", "id_user", $from_id,"count");
    $page = $user['pagenumber'];
    $items_per_page  = 5;
    $sum = $user['pagenumber'] * $items_per_page;
    if ($sum > $numpage) {
        $next_page = 1;
    } else {
        $next_page = $page + 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND status = 'active' ORDER BY username ASC LIMIT $start_index, $items_per_page");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "⭕️" . $row['username'] . "⭕️",
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
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
     update("user", "pagenumber", $next_page, "id",$from_id);
    Editmessagetext($from_id, $message_id, $text, $keyboard_json);
} elseif ($datain == 'previous_page') {
    $page = $user['pagenumber'];
    $items_per_page  = 5;
    if ($user['pagenumber'] <= 1) {
        $next_page = 1;
    } else {
        $next_page = $page - 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user AND status = 'active' ORDER BY username ASC LIMIT $start_index, $items_per_page");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->execute();
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "⭕️" . $row['username'] . "⭕️",
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
            'text' =>  $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_page'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id",$from_id);
    Editmessagetext($from_id, $message_id, $text, $keyboard_json);
}
if (preg_match('/product_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username,"select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'],"select");
    $data_useer = getuser($username, $marzban_list_get['name_panel']);
    if ($data_useer['detail'] == "User not found" || !isset($data_useer['status'])) {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], $keyboard, 'html');
        return;
    }
    if(isset($data_useer['online_at']) && $data_useer['online_at'] !== null){
        $dateString = $data_useer['online_at'];
        $lastonline = jdate('Y/m/d h:i:s',strtotime($dateString));
    }else{
        $lastonline = "متصل نشده";
    }
    #-------------status----------------#
    $status = $data_useer['status'];
    $status_var = [
        'active' =>  $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired']
    ][$status];
    #--------------[ expire ]---------------#
        $expirationDate = $data_useer['expire'] ? jdate('Y/m/d', $data_useer['expire']) : $textbotlang['users']['stateus']['Unlimited'];
    #-------------[ data_limit ]----------------#
    $LastTraffic = $data_useer['data_limit'] ? formatBytes($data_useer['data_limit']) : $textbotlang['users']['stateus']['Unlimited'];
    #---------------[ RemainingVolume ]--------------#
    $output =  $data_useer['data_limit'] - $data_useer['used_traffic'];
    $RemainingVolume = $data_useer['data_limit'] ? formatBytes($output) : "نامحدود";
    #---------------[ used_traffic ]--------------#
    $usedTrafficGb = $data_useer['used_traffic'] ? formatBytes($data_useer['used_traffic']) : $textbotlang['users']['stateus']['Notconsumed'];
    #--------------[ day ]---------------#
    $timeDiff = $data_useer['expire'] - time();
    $day = $data_useer['expire'] ? floor($timeDiff / 86400) + 1 . $textbotlang['users']['stateus']['day'] : $textbotlang['users']['stateus']['Unlimited'];
    #-----------------------------#
    $keyboardsetting = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['linksub'], 'callback_data' => 'subscriptionurl_'.$username],
                ['text' => $textbotlang['users']['stateus']['config'], 'callback_data' => 'config_'.$username],
            ],[
                ['text' => $textbotlang['users']['extend']['title'], 'callback_data' => 'extend_'.$username],
                ['text' => $textbotlang['users']['changelink']['btntitle'], 'callback_data' => 'changelink_'.$username],
            ],
            [
                ['text' => $textbotlang['users']['removeconfig']['btnremoveuser'], 'callback_data' => 'removeserviceuserco-'.$username],
                ['text' => $textbotlang['users']['Extra_volume']['sellextra'], 'callback_data' => 'Extra_volume_'.$username],
                ],
            [
                ['text' => $textbotlang['users']['stateus']['backlist'], 'callback_data' => 'backorder'],
            ]
        ]
    ]);
    $textinfo = "وضعیت سرویس : $status_var
نام کاربری سرویس : {$data_useer['username']}
لوکیشن :{$nameloc['Service_location']}
کد سرویس:{$nameloc['id_invoice']}

🟢 اخرین زمان اتصال شما : $lastonline

📥 حجم مصرفی : $usedTrafficGb
♾ حجم سرویس : $LastTraffic

📅 فعال تا تاریخ : $expirationDate ($day)

🚫 برای تغییر لینک و قطع دسترسی دیگران کافیست روی گزینه ' بروزرسانی اشتراک ' کلیک کنید.";
    Editmessagetext($from_id, $message_id, $textinfo, $keyboardsetting);
}
if (preg_match('/subscriptionurl_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username,"select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'],"select");
    $data_useer = getuser($username, $marzban_list_get['name_panel']);
    $subscriptionurl = $data_useer['subscription_url'];
    if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $subscriptionurl)) {
        $subscriptionurl = $marzban_list_get['url_panel'] . "/" . ltrim($subscriptionurl, "/");
    }
    $textsub = "
    {$textbotlang['users']['stateus']['linksub']}
    
    <code>$subscriptionurl</code>";
    $randomString = bin2hex(random_bytes(2));
    $urlimage = "$from_id$randomString.png";
    $writer = new PngWriter();
    $qrCode = QrCode::create($subscriptionurl)
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
    ->setSize(400)
    ->setMargin(0)
    ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
    $result = $writer->write($qrCode,null, null);
    $result->saveToFile($urlimage);
    sendphoto($from_id, "https://$domainhosts/$urlimage", $textsub);
    unlink($urlimage);
}
elseif (preg_match('/config_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username,"select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'],"select");
    $data_useer = getuser($username, $marzban_list_get['name_panel']);
    foreach ($data_useer['links'] as $configs) {
            $config .= "\n\n" . $configs;
        }
    $textsub = "
    {$textbotlang['users']['config']}
<code>$config</code>";
    sendmessage($from_id, $textsub, null, 'html');
}
elseif (preg_match('/extend_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username,"select");
    $prodcut = select("product", "*", "name_product", $nameloc['name_product'],"select");
    if(!isset($prodcut['price_product'])){
            sendmessage($from_id,$textbotlang['users']['extend']['error'], null, 'HTML');
            return;
    }
            $keyboardextend = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['extend']['confirm'], 'callback_data' => "confirmserivce_".$username],
            ]
        ]
    ]);
     $prodcut['price_product'] = number_format($prodcut['price_product'],0);
    $textextend = "🧾 فاکتور تمدید شما برای نام کاربری @$username ایجاد شد.

🛍 نام محصول :  {$nameloc['name_product']}
مبلغ تمدید :  {$prodcut['price_product']}
مدت زمان تمدید : {$prodcut['Service_time']} روز
حجم تمدید : {$prodcut['Volume_constraint']} گیگ

⚠️ پس از تمدید حجم شما ریست خواهد شدو اگر حجمی باقی مانده باشد حذف می شود

✅ برای تایید و تمدید سرویس روی دکمه زیر کلیک کنید

❌ برای تمدید باید کیف پول خود را شارژ کنید.";
    sendmessage($from_id,$textextend, $keyboardextend, 'HTML');
    step('confirmextend',$from_id);
}
elseif (preg_match('/confirmserivce_(\w+)/', $datain, $dataget) && $user['step'] == "confirmextend") {
    $usernamepanel = $dataget[1];
    $nameloc = select("invoice", "*", "username", $usernamepanel,"select");
    $prodcut = select("product", "*", "name_product", $nameloc['name_product'],"select");
        if($user['Balance'] < $prodcut['price_product']){
    $Balance_prim = $prodcut['price_product'] - $user['Balance'];
    update("user", "Processing_value", $Balance_prim, "id",$from_id);
    sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
    step('get_step_payment',$from_id);
        return;
        }
    $Balance_Low_user = $user['Balance'] - $prodcut['price_product'];
    update("user", "Balance", $Balance_Low_user, "id",$from_id);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'],"select");
    $data_useer = getuser($usernamepanel,$marzban_list_get['name_panel']);
    ResetUserDataUsage($usernamepanel, $marzban_list_get['name_panel']);
    $date = strtotime("+" . $prodcut['Service_time'] . "day");
    $newDate = strtotime(date("Y-m-d H:i:s", $date));
    $data_limit = $prodcut['Volume_constraint'] * pow(1024, 3);
        $datam = array(
        "expire" => $newDate,
        "data_limit" => $data_limit
        );
    $Modifyuser =Modifyuser($marzban_list_get['name_panel'],$usernamepanel,$datam);
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
    sendmessage($from_id,$textbotlang['users']['extend']['thanks'],$keyboardextendfnished, 'HTML');
    $prodcut['price_product'] = number_format($prodcut['price_product']);
    
     $text_report = "⭕️ یک کاربر سرویس خود را تمدید کرد.

اطلاعات کاربر : 

🪪 آیدی عددی : <code>$from_id</code>
🛍 نام محصول :  {$prodcut['name_product']}
💰 مبلغ تمدید :  {$prodcut['price_product']} تومان
👤 نام کاربری مشتری در پنل مرزبان : $usernamepanel
لوکیشن سرویس کاربر : {$nameloc['Service_location']}"; 
     if (strlen($setting['Channel_Report']) > 0) {    
         sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
         }
    step('home',$from_id);
}
elseif (preg_match('/changelink_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = select("invoice", "*", "username", $username,"select");
            $keyboardextend = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['changelink']['confirm'], 'callback_data' => "confirmchange_".$username],
            ]
        ]
    ]);
    sendmessage($from_id,$textbotlang['users']['changelink']['warnchange'], $keyboardextend, 'HTML');
}
elseif (preg_match('/confirmchange_(\w+)/', $datain, $dataget)) {
    $usernameconfig = $dataget[1];
    $nameloc = select("invoice", "*", "username", $usernameconfig,"select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'],"select");
    $Allowedusername = getuser($username,$marzban_list_get['name_panel']);
    $nameprotocol = array();
if(isset($marzban_list_get['vless']) && $marzban_list_get['vless'] == "onvless"){
    $nameprotocol['vless'] = array(
        "id" => generateUUID(),
        "status" => "active");
}
if(isset($marzban_list_get['vmess']) && $marzban_list_get['vmess'] == "onvmess"){
    $nameprotocol['vmess'] = array(
                "id" => generateUUID(),
        "status" => "active");
}
if(isset($marzban_list_get['trojan']) && $marzban_list_get['trojan'] == "ontrojan"){
    $nameprotocol['trojan'] = array(
        "id" => generateUUID(),
        "status" => "active");
}
if(isset($marzban_list_get['shadowsocks']) && $marzban_list_get['shadowsocks'] == "onshadowsocks"){
    $nameprotocol['shadowsocks'] = array(
        "id" => generateUUID(),
        "status" => "active");
}
$datam = array(
        "proxies" => $nameprotocol
        );
    Modifyuser($marzban_list_get['name_panel'],$usernameconfig,$datam);
    revoke_sub($usernameconfig,$nameloc['Service_location']);
    Editmessagetext($from_id, $message_id,  $textbotlang['users']['changelink']['confirmed'], null);

}
elseif (preg_match('/Extra_volume_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    update("user", "Processing_value", $username, "id",$from_id);
    $textextra = " ⭕️ مقدار حجمی که میخواهید خریداری کنید را ارسال کنید.

⚠️ هر گیگ حجم اضافه  {$setting['Extra_volume']} است.";
    sendmessage($from_id, $textextra, $backuser, 'HTML');
    step('getvolumeextra',$from_id);
}
elseif($user['step'] == "getvolumeextra"){
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    $text = number_format($text, 2);
    if($text<0.01){
        sendmessage($from_id, $textbotlang['users']['Extra_volume']['invalidprice'], $backuser, 'HTML');
        return;
    }
    $priceextra = $setting['Extra_volume']*$text;
        $keyboardsetting = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['Extra_volume']['extracheck'], 'callback_data' => 'confirmaextra_'.$priceextra],
            ]
        ]
    ]);
    $priceextra = number_format($priceextra, 2);
    $setting['Extra_volume'] = number_format($setting['Extra_volume']);
    $textextra = "📇 فاکتور خرید حجم اضافه برای شما ایجاد شد.

💰 قیمت هر گیگابایت حجم اضافه :  {$setting['Extra_volume']} تومان
📝 مبلغ  فاکتور شما :  $priceextra تومان
📥 حجم اضافه درخواستی : $text  گیگابایت

✅ جهت پرداخت و اضافه شدن حجم، روی دکمه زیر کلیک کنید.";
    sendmessage($from_id,$textextra, $keyboardsetting, 'HTML');
    step('home',$from_id);
}
elseif (preg_match('/confirmaextra_(\w+)/', $datain, $dataget)) {
    $volume = number_format($dataget[1], 2);
    $nameloc = select("invoice", "*", "username", $user['Processing_value'],"select");
        if($user['Balance'] <$volume){
    $Balance_prim = $volume - $user['Balance'];
    update("user", "Processing_value", $Balance_prim, "id",$from_id);
    sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
    step('get_step_payment',$from_id);
        return;
        }
    deletemessage($from_id, $message_id);
    $Balance_Low_user = $user['Balance'] - $volume;
    update("user", "Balance", $Balance_Low_user, "id",$from_id);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'],"select");
    $data_useer = getuser($user['Processing_value'], $marzban_list_get['name_panel']);
    $data_limit = $data_useer['data_limit'] + (number_format($volume / $setting['Extra_volume'], 2) * pow(1024, 3));
    $datam = array(
        "data_limit" => $data_limit
        );
     Modifyuser($marzban_list_get['name_panel'],$user['Processing_value'],$datam);
            $keyboardextrafnished = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backservice'], 'callback_data' => "product_" . $user['Processing_value']],
]
        ]
    ]);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['extraadded'], $keyboardextrafnished, 'HTML');
    $volumes = number_format($volume / $setting['Extra_volume'], 2);
    $volume = number_format($volume);
     $text_report = "⭕️ یک کاربر حجم اضافه خریده است

اطلاعات کاربر : 
🪪 آیدی عددی : $from_id
🛍 حجم خریداری شده  : $volumes
💰 مبلغ پرداختی : $volume تومان"; 
     if (strlen($setting['Channel_Report']) > 0) {    
         sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
         }
}
elseif (preg_match('/removeserviceuserco-(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $DataUserOut = getuser($username, $marzban_list_get['name_panel']);
    if(isset($DataUserOut['status']) && in_array($DataUserOut['status'], ["expired", "limited", "disabled"])){
        sendmessage($from_id, $textbotlang['users']['stateus']['notusername'], null, 'html');
        return;
    }
    $requestcheck = mysqli_query($connect, "SELECT * FROM cancel_service WHERE username = '$username' LIMIT 1");
    if (mysqli_num_rows($requestcheck) != 0) {
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
}
elseif (preg_match('/confirmremoveservices-(\w+)/', $datain, $dataget)) {
    $checkcancelservice = mysqli_query($connect, "SELECT * FROM cancel_service WHERE id_user = '$from_id' AND status = 'waiting'");
    if (mysqli_num_rows($checkcancelservice) != 0) {
        sendmessage($from_id,$textbotlang['users']['stateus']['exitsrequsts'], null, 'HTML');
        return;
    }
    $usernamepanel = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$usernamepanel'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $stmt = $connect->prepare("INSERT IGNORE INTO cancel_service (id_user, username,description,status) VALUES (?, ?, ?, ?)");
    $descriptions = "0";
    $Status = "waiting";
    $stmt->bind_param("ssss", $from_id, $usernamepanel, $descriptions, $Status);
    $stmt->execute();
    $stmt->close();
    $DataUserOut = getuser($usernamepanel, $marzban_list_get['name_panel']);
        #-------------status----------------#
    $status = $DataUserOut['status'];
    $status_var = [
        'active' =>  $textbotlang['users']['stateus']['active'],
        'limited' => $textbotlang['users']['stateus']['limited'],
        'disabled' => $textbotlang['users']['stateus']['disabled'],
        'expired' => $textbotlang['users']['stateus']['expired'],
        'on_hold' => $textbotlang['users']['stateus']['on_hold']
    ][$status];
    #--------------[ expire ]---------------#
    $expirationDate = $DataUserOut['expire'] ? jdate('Y/m/d', $DataUserOut['expire']) : $textbotlang['users']['stateus']['Unlimited'];
    #-------------[ data_limit ]----------------#
    $LastTraffic = $DataUserOut['data_limit'] ? formatBytes($DataUserOut['data_limit']) : $textbotlang['users']['stateus']['Unlimited'];
    #---------------[ RemainingVolume ]--------------#
    $output =  $DataUserOut['data_limit'] - $DataUserOut['used_traffic'];
    $RemainingVolume = $DataUserOut['data_limit'] ? formatBytes($output) : "نامحدود";
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
                ['text' => "❌حذف سرویس", 'callback_data' => "remoceserviceadmin-$usernamepanel"],
                ['text' => "❌عدم تایید حذف", 'callback_data' => "rejectremoceserviceadmin-$usernamepanel"],
            ],
        ]
    ]);
    foreach ($admin_ids as $admin) {
        sendmessage($admin, $textinfoadmin, $confirmremoveadmin, 'html');
        step('home', $admin);
    }
    deletemessage($from_id, $message_id);
    sendmessage($from_id, "✅ درخواست شما ارسال گردید پس از بررسی مدیریت نتیجه به شما اطلاع رسانی خواهد شد", $keyboard, 'html');

}
#-----------usertest------------#
if ($text == $datatextbot['text_usertest']) {
    $locationproduct = select("marzban_panel", "*", null, null,"count");
    if ($locationproduct == 0) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
    return;
}
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        step('get_number',$from_id);
    }
    if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است") return;
    if ($user['limit_usertest'] <= 0) {
        sendmessage($from_id, $textbotlang['users']['usertest']['limitwarning'], $keyboard, 'html');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_usertest, 'html');
    if($setting['MethodUsername'] == "نام کاربری دلخواه")return;
}
if (preg_match('/locationtest_(.*)/', $datain, $dataget)) {
    $location = $dataget[1];
    update("user", "Processing_value_tow", $location, "id",$from_id);
    sendmessage($from_id, $textbotlang['users']['selectusername'], $backuser, 'html');
    step('createusertest',$from_id);
}
if ($user['step'] == "createusertest" || preg_match('/locationtests_(.*)/', $datain, $dataget)) {
        if ($user['limit_usertest'] <= 0) {
        sendmessage($from_id, $textbotlang['users']['usertest']['limitwarning'], $keyboard, 'html');
        return;
    }
    $location = $dataget[1];
        if($setting['MethodUsername'] == "نام کاربری دلخواه" && $user['step'] == "createusertest"){
            if (!preg_match('~(?!_)^[a-z][a-z\d_]{2,32}(?<!_)$~i', $text)) {
        sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser,'HTML');
        return;
    }
    $name_panel = $user['Processing_value_tow'];
        }else{
    $name_panel =$location ;
        }
    $randomString = bin2hex(random_bytes(2));
    $username_ac = generateUsername($from_id, $setting['MethodUsername'], $user['username'], $randomString,$text);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $name_panel,"select");
    $Allowedusername = getuser($username_ac,$marzban_list_get['name_panel']);
    if (isset($Allowedusername['username'])) {
        $random_number = random_int(1000000, 9999999);
        $username_ac = $username_ac . $random_number;
    }
    $nameprotocol = array();
if(isset($marzban_list_get['vless']) && $marzban_list_get['vless'] == "onvless"){
    $nameprotocol['vless'] = array();
}

if(isset($marzban_list_get['vmess']) && $marzban_list_get['vmess'] == "onvmess"){
    $nameprotocol['vmess'] = array();
}

if(isset($marzban_list_get['trojan']) && $marzban_list_get['trojan'] == "ontrojan"){
    $nameprotocol['trojan'] = array();
}

if(isset($marzban_list_get['shadowsocks']) && $marzban_list_get['shadowsocks'] == "onshadowsocks"){
    $nameprotocol['shadowsocks'] = array();
}

if(isset($nameprotocol['vless']) && $setting['flow'] == "flowon"){
    $nameprotocol['vless']['flow'] = 'xtls-rprx-vision';
}
    $date = strtotime("+" . $setting['time_usertest'] . "hours");
    $timestamp = strtotime(date("Y-m-d H:i:s", $date));
    $expire = $timestamp;
    $data_limit = $setting['val_usertest'] * 1048576;
    $config_test = adduser($username_ac, $expire, $data_limit,$marzban_list_get['name_panel'], $nameprotocol);
    $data_test = json_decode($config_test, true);
    if (!isset($data_test['username'])) {
        $data_test['detail'] = json_encode($data_test['detail']);
        sendmessage($from_id, $textbotlang['users']['usertest']['errorcreat'], $keyboard, 'html');
        $texterros = "
    ⭕️ یک کاربر قصد دریافت اکانت تست داشت که ساخت کانفیگ با خطا مواجه شده و به کاربر کانفیگ داده نشد
    ✍️ دلیل خطا : 
    {$data_test['detail']}
    آیدی کابر : $from_id
    نام کاربری کاربر : @$username";
        foreach ($admin_ids as $admin) {
            sendmessage($admin, $texterros, null, 'html');
        }
        step('home',$from_id);
        return;
    }
    $date = jdate('Y/m/d');
    $randomString = bin2hex(random_bytes(2));
    $stmt = $pdo->prepare("INSERT IGNORE INTO TestAccount (id_user, id_invoice, username,Service_location,time_sell) VALUES (:id_user,:id_invoice,:username,:Service_location,:time_sell)");
    $stmt->bindParam(':id_user', $from_id);
    $stmt->bindParam(':id_invoice', $randomString);
    $stmt->bindParam(':username', $username_ac);
    $stmt->bindParam(':Service_location', $name_panel);
    $stmt->bindParam(':time_sell', $date);
    $stmt->execute();
    $text_config = "";
    $output_config_link = "";
    if ($setting['sublink'] == "✅ لینک اشتراک فعال است.") {
        $output_config_link = $data_test['subscription_url'];
        if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $output_config_link)) {
            $output_config_link = $marzban_list_get['url_panel'] . "/" . ltrim($output_config_link, "/");
        }
        $link_config = "            
    {$textbotlang['users']['stateus']['linksub']}
    $output_config_link";
    }
    if ($setting['configManual'] == "✅ ارسال کانفیگ بعد خرید فعال است.") {
        foreach ($data_test['links'] as $configs) {
            $config .= "\n\n" . $configs;
        }
        $text_config = "            
   {$textbotlang['users']['config']}
    $config";
    }
    $usertestinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $setting['time_usertest'] . " ساعت", 'callback_data' => "Service_time"],
                ['text' => $textbotlang['users']['time-Service'], 'callback_data' => "Service_time"],
            ],
            [
                ['text' => $setting['val_usertest'] . " مگابایت", 'callback_data' => "Volume_constraint"],
                ['text' => $textbotlang['users']['Volume-Service'], 'callback_data' => "Volume_constraint"],
            ],
            [
                ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
            ]
        ]
    ]);
    $textcreatuser = "🔑 اشتراک شما با موفقیت ساخته شد.
    
    👤 نام کاربری شما :<code>$username_ac</code>
    
    <code>$output_config_link</code>
    <code>$text_config</code>";
if ($setting['sublink'] == "✅ لینک اشتراک فعال است.") {
    $urlimage = "$from_id$randomString.png";
    $writer = new PngWriter();
    $qrCode = QrCode::create($output_config_link)
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
    ->setSize(400)
    ->setMargin(0)
    ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
    $result = $writer->write($qrCode,null, null);
    $result->saveToFile($urlimage);
        telegram('sendphoto', [
            'chat_id' => $from_id,
            'photo' => "https://$domainhosts/$urlimage",
            'reply_markup' => $usertestinfo,
            'caption' => $textcreatuser,
            'parse_mode' => "HTML",
        ]);
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
unlink($urlimage);
}else{
    sendmessage($from_id, $textcreatuser, $usertestinfo, 'HTML');
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
}
    step('home',$from_id);
    $limit_usertest = $user['limit_usertest'] - 1;
    update("user", "limit_usertest", $limit_usertest, "id",$from_id);
    step('home',$from_id);
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
    if (strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, $usertestReport, 'HTML');
    }
}
#-----------help------------#
if ($text == $datatextbot['text_help'] || $datain == "helpbtn") {
    if ($setting['help_Status'] == "❌ آموزش غیرفعال است") {
        sendmessage($from_id, $textbotlang['users']['help']['disablehelp'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['selectoption'], $json_list_help, 'HTML');
    step('sendhelp',$from_id);
} elseif ($user['step'] == "sendhelp") {
   $helpdata = select("help", "*", "name_os", $text,"select");
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
if ($text == $datatextbot['text_support']) {
    sendmessage($from_id, $textbotlang['users']['support']['btnsupport'], $supportoption, 'HTML');
}elseif($datain == "support"){
    sendmessage($from_id, $textbotlang['users']['support']['sendmessageuser'], $backuser, 'HTML');
    step('gettextpm',$from_id);
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
        if($text){
             $textsendadmin = "
        📥 یک پیام از کاربر دریافت شد برای پاسخ روی دکمه زیر کلیک کنید  و پیام خود را ارسال کنید.
    
    آیدی عددی : $from_id
    نام کاربری کاربر : @$username
     📝 متن پیام : $text
        ";
            sendmessage($id_admin, $textsendadmin, $Response, 'HTML');
        }
        if($photo){
             $textsendadmin = "
        📥 یک پیام از کاربر دریافت شد برای پاسخ روی دکمه زیر کلیک کنید  و پیام خود را ارسال کنید.
    
    آیدی عددی : $from_id
    نام کاربری کاربر : @$username
     📝 متن پیام : $caption
        ";
                    telegram('sendphoto', [
            'chat_id' => $id_admin,
            'photo' => $photoid,
            'reply_markup' => $Response,
            'caption' => $textsendadmin,
            'parse_mode' => "HTML",
        ]);
        }
    }
    step('home',$from_id);
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
    $countorder =  select("invoice", "id_user", 'id_user', $from_id,"count");
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
if ($text == $datatextbot['text_sell']) {
$locationproduct = select("marzban_panel", "*", null, null,"count");
    if ($locationproduct == 0) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
    return;
}
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        step('get_number',$from_id);
    }
    if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است") return;
    #-----------------------#
        if ($locationproduct == 1) {
        $nullproduct = select("product", "*", null, null,"count");
        if ($nullproduct == 0) {
            sendmessage($from_id, $textbotlang['Admin']['Product']['nullpProduct'], null, 'HTML');
            return;
        }
    $product = [];
    $location = select("marzban_panel", "*", null, null,"select")['name_panel'];
    $stmt = $pdo->prepare("SELECT * FROM product WHERE Location = :location OR Location = '/all'");
    $stmt->bindParam(':location', $location);
    $stmt->execute();
   $product = ['inline_keyboard' => []];
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if($setting['MethodUsername'] == "نام کاربری دلخواه"){
    $product['inline_keyboard'][] = [
        ['text' => $result['name_product'], 'callback_data' => "prodcutservices_".$result['code_product']]
    ];
    }
    else{
          $product['inline_keyboard'][] = [
        ['text' => $result['name_product'], 'callback_data' => "prodcutservice_{$result['code_product']}"]
    ];  
    }
}
$product['inline_keyboard'][] = [
    ['text' => "🏠 بازگشت به منوی اصلی", 'callback_data' => "backuser"]
];

    $json_list_product_list = json_encode($product);
    $textproduct = "🛍 برای خرید اشتراک سرویس مدنظر خود را انتخاب کنید
    لوکیشن سرویس  :$location ";
    sendmessage($from_id,$textproduct, $json_list_product_list, 'HTML');
    update("user", "Processing_value", $location, "id",$from_id);
        }else{
                sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_panel_user, 'HTML');
        }
} 
elseif (preg_match('/^location_(.*)/', $datain, $dataget)) {
    $location = $dataget[1];
    $nullproduct = select("product", "*", null, null,"count");
    if ($nullproduct == 0) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['nullpProduct'], null, 'HTML');
        return;
}
    update("user", "Processing_value", $location, "id",$from_id);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE Location = :location OR Location = '/all'");
    $stmt->bindParam(':location', $location);
    $stmt->execute();
   $product = ['inline_keyboard' => []];
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if($setting['MethodUsername'] == "نام کاربری دلخواه"){
    $product['inline_keyboard'][] = [
        ['text' => $result['name_product'], 'callback_data' => "prodcutservices_".$result['code_product']]
    ];
    }
    else{
          $product['inline_keyboard'][] = [
        ['text' => $result['name_product'], 'callback_data' => "prodcutservice_{$result['code_product']}"]
    ];  
    }
}
$product['inline_keyboard'][] = [
    ['text' => "🏠 بازگشت به منوی اصلی", 'callback_data' => "backuser"]
];

    $json_list_product_list = json_encode($product);
    Editmessagetext($from_id, $message_id, $textbotlang['users']['sell']['Service-select'], $json_list_product_list);
} 
elseif (preg_match('/^prodcutservices_(.*)/', $datain, $dataget)){
    $prodcut = $dataget[1];
    update("user", "Processing_value_one", $prodcut, "id",$from_id);
    sendmessage($from_id, $textbotlang['users']['selectusername'], $backuser, 'html');
    step('endstepuser',$from_id);
}
elseif ($user['step'] == "endstepuser" ||preg_match('/prodcutservice_(.*)/', $datain, $dataget)) {
        $prodcut = $dataget[1];
        if($setting['MethodUsername'] == "نام کاربری دلخواه"){
            if (!preg_match('~(?!_)^[a-z][a-z\d_]{2,32}(?<!_)$~i', $text)) {
        sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser,'HTML');
        return;
            }
        $loc = $user['Processing_value_one'];
        }else{
    deletemessage($from_id, $message_id);
    $loc = $prodcut;
    }
    update("user", "Processing_value_one",$loc,"id",$from_id);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code_product AND (location = :loc1 OR location = '/all') LIMIT 1");
    $stmt->bindValue(':code_product', $loc);
    $stmt->bindValue(':loc1', $user['Processing_value']);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $randomString = bin2hex(random_bytes(2));
    $username_ac = generateUsername($from_id, $setting['MethodUsername'], $username, $randomString,$text);
    update("user", "Processing_value_tow",$username_ac,"id",$from_id);
    if($info_product['Volume_constraint'] == 0 )$info_product['Volume_constraint'] = $textbotlang['users']['stateus']['Unlimited'];
    $info_product['price_product'] = number_format($info_product['price_product'], 2);
    $user['Balance'] = number_format($user['Balance'], 2);
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
    step('payment',$from_id);
} 
elseif ($user['step'] == "payment" && $datain == "confirmandgetservice" || $datain == "confirmandgetserviceDiscount"){
    $partsdic = explode("_", $user['Processing_value_four']);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code AND (location = :loc1 OR location = '/all') LIMIT 1");
    $stmt->bindValue(':code', $user['Processing_value_one']);
    $stmt->bindValue(':loc1', $user['Processing_value']);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($info_product['price_product']) || empty($info_product['price_product'])) return;
    if ($datain == "confirmandgetserviceDiscount") {
        $priceproduct =  $partsdic[1];
    } else {
        $priceproduct =  $info_product['price_product'];
    }
    if ($priceproduct > $user['Balance']) {
    $Balance_prim = $priceproduct - $user['Balance'];
    update("user", "Processing_value",$Balance_prim,"id",$from_id);
    sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
    step('get_step_payment',$from_id);
        return;
    }
    $username_ac = $user['Processing_value_tow'];
    $date = jdate('Y/m/d');
    $randomString = bin2hex(random_bytes(2));
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $get_username_Check = getuser($username_ac, $marzban_list_get['name_panel']);
    $random_number = random_int(1000000, 9999999);
    if (isset($get_username_Check['username']) || in_array($username_ac, $usernameinvoice)) {
        $username_ac = $random_number . $username_ac;
    }
        if(in_array($randomString,$id_invoice)){
        $randomString = $random_number.$randomString;
    }
    $sql = "INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $Status = "active";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $randomString);
    $stmt->bindParam(3, $username_ac);
    $stmt->bindParam(4, $date);
    $stmt->bindParam(5, $user['Processing_value']);
    $stmt->bindParam(6, $info_product['name_product']);
    $stmt->bindParam(7, $info_product['price_product']);
    $stmt->bindParam(8, $info_product['Volume_constraint']);
    $stmt->bindParam(9, $info_product['Service_time']);
    $stmt->bindParam(10, $Status);
    $stmt->execute();
    $date = strtotime("+" . $info_product['Service_time'] . "days");
    $timestamp = strtotime(date("Y-m-d H:i:s", $date));
    $data_limit = $info_product['Volume_constraint'] * pow(1024, 3);
    $nameprotocol = array();
if(isset($marzban_list_get['vless']) && $marzban_list_get['vless'] == "onvless"){
    $nameprotocol['vless'] = array();
}

if(isset($marzban_list_get['vmess']) && $marzban_list_get['vmess'] == "onvmess"){
    $nameprotocol['vmess'] = array();
}

if(isset($marzban_list_get['trojan']) && $marzban_list_get['trojan'] == "ontrojan"){
    $nameprotocol['trojan'] = array();
}

if(isset($marzban_list_get['shadowsocks']) && $marzban_list_get['shadowsocks'] == "onshadowsocks"){
    $nameprotocol['shadowsocks'] = array();
}

if(isset($nameprotocol['vless']) && $setting['flow'] == "flowon"){
    $nameprotocol['vless']['flow'] = 'xtls-rprx-vision';
}
    $configuser = adduser($username_ac, $timestamp, $data_limit,$marzban_list_get['name_panel'], $nameprotocol);
    $data = json_decode($configuser, true);
    if (!isset($data['username'])) {
        $data['detail'] = json_encode($data);
        sendmessage($from_id, $textbotlang['users']['sell']['ErrorConfig'], $keyboard, 'HTML');
        $texterros = "
    ⭕️ یک کاربر قصد دریافت اکانت داشت که ساخت کانفیگ با خطا مواجه شده و به کاربر کانفیگ داده نشد
    ✍️ دلیل خطا : 
    {$data['detail']}
    آیدی کابر : $from_id
    نام کاربری کارب : @$username";
        foreach ($admin_ids as $admin) {
            sendmessage($admin, $texterros, null, 'HTML');
        }
        step('home',$from_id);
        return;
    }
    if ($datain == "confirmandgetserviceDiscount") {
        $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount", $partsdic[0],"select");
        $value = intval($SellDiscountlimit['usedDiscount']) + 1;
        update("DiscountSell", "usedDiscount",$value,"codeDiscount",$partsdic[0]);
        $text_report = "⭕️ یک کاربر با نام کاربری @$username  و آیدی عددی $from_id از کد تخفیف {$partsdic[0]} استفاده کرد.";
        if (strlen($setting['Channel_Report']) > 0) {
            sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
        }
    }
    $affiliatescommission = select("affiliates", "*", null, null,"select");
    if ($affiliatescommission['status_commission'] == "oncommission" &&($user['affiliates'] !== null || $user['affiliates'] != "0")) {
        $affiliatescommission = select("affiliates", "*", null, null,"select");
        $result = ($priceproduct * $affiliatescommission['affiliatespercentage']) / 100;
        $user_Balance = select("user", "*", "id", $user['affiliates'],"select");
        $Balance_prim = $user_Balance['Balance'] + $result;
        update("user", "Balance",$Balance_prim,"id",$user['affiliates']);
        $result = number_format($result, 2);
        $textadd = "🎁  پرداخت پورسانت 

مبلغ $result تومان به حساب شما از طرف  زیر مجموعه تان به کیف پول شما واریز گردید";
        sendmessage($user['affiliates'], $textadd, null, 'HTML');
    }
    $link_config = "";
    $text_config = "";
    $config = "";
    $configqr = "";
    if ($setting['sublink'] == "✅ لینک اشتراک فعال است.") {
        $output_config_link = $data['subscription_url'];
        if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $output_config_link)) {
            $output_config_link = $marzban_list_get['url_panel'] . "/" . ltrim($output_config_link, "/");
        }
        $link_config = "<code>$output_config_link</code>";
    }
    if ($setting['configManual'] == "✅ ارسال کانفیگ بعد خرید فعال است.") {
        foreach ($data['links'] as $configs) {
            $config .= "\n\n" . $configs;
            $configqr .= $configs;
        }
        $text_config = "            
    {$textbotlang['users']['config']}
<code>$config</code>";
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

🧑‍🦯 شما میتوانید شیوه اتصال را  با فشردن دکمه زیر و انتخاب سیستم عامل خود را دریافت کنید";
if ($setting['sublink'] == "✅ لینک اشتراک فعال است.") {
$urlimage = "$from_id$randomString.png";
    $writer = new PngWriter();
    $qrCode = QrCode::create($output_config_link)
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
    ->setSize(400)
    ->setMargin(0)
    ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
    $result = $writer->write($qrCode,null, null);
    $result->saveToFile($urlimage);
        telegram('sendphoto', [
            'chat_id' => $from_id,
            'photo' => "https://$domainhosts/$urlimage",
            'reply_markup' => $Shoppinginfo,
            'caption' => $textcreatuser,
            'parse_mode' => "HTML",
        ]);
            sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
            unlink($urlimage);
}else{
    sendmessage($from_id, $textcreatuser, $Shoppinginfo, 'HTML');
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard, 'HTML');
}
    $Balance_prim = $user['Balance'] - $priceproduct;
    update("user", "Balance",$Balance_prim,"id",$from_id);
    $user['Balance'] = number_format($user['Balance'], 2);
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
    if (strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
    step('home',$from_id);
}
elseif ($datain == "aptdc") {
    sendmessage($from_id, $textbotlang['users']['Discount']['getcodesell'], $backuser, 'HTML');
    step('getcodesellDiscount',$from_id);
    deletemessage($from_id, $message_id);
}
elseif ($user['step'] == "getcodesellDiscount") {
    if (!in_array($text, $SellDiscount)) {
        sendmessage($from_id, $textbotlang['users']['Discount']['notcode'], $backuser, 'HTML');
        return;
    }
    $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount",$text,"select");
    if ($SellDiscountlimit == false) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['invalidcodedis'], null, 'HTML');
        return;
    }
    $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount",$text,"select");
    if ($SellDiscountlimit['limitDiscount'] == $SellDiscountlimit['usedDiscount']) {
        sendmessage($from_id, $textbotlang['users']['Discount']['erorrlimit'], null, 'HTML');
        return;
    }
    if($SellDiscountlimit['usefirst'] == "1"){
            $stmt = $pdo->prepare("SELECT * FROM invoice WHERE id_user = :id_user");
            $stmt->bindParam(':id_user', $from_id);
            $stmt->execute();
            $countinvoice = $stmt->rowCount();
            if($countinvoice != 0){
                        sendmessage($from_id, $textbotlang['users']['Discount']['firstdiscount'], null, 'HTML');
                        return;
            }

    }
    sendmessage($from_id, $textbotlang['users']['Discount']['correctcode'], $keyboard, 'HTML');
    step('payment',$from_id);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code AND (location = :loc1 OR location = '/all') LIMIT 1");
    $stmt->bindValue(':code', $user['Processing_value_one']);
    $stmt->bindValue(':loc1', $user['Processing_value']);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = ($SellDiscountlimit['price'] / 100) * number_format($info_product['price_product'], 2);

    $info_product['price_product'] = number_format($info_product['price_product'], 2) - $result;
    $info_product['price_product'] = round($info_product['price_product']);
    if ($info_product['price_product'] < 0) $info_product['price_product'] = 0;
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
            [['text' => "🏠 بازگشت به منوی اصلی",  'callback_data' => "backuser"]]
        ]
    ]);
    $parametrsendvalue = $text."_".$info_product['price_product'];
    update("user", "Processing_value_four",$parametrsendvalue,"id",$from_id);
    sendmessage($from_id, $textin, $paymentDiscount, 'HTML');
}



#-------------------[ text_Add_Balance ]---------------------#
if ($text == $datatextbot['text_Add_Balance']) {
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        step('get_number',$from_id);
    }
    if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است") return;
    sendmessage($from_id, $textbotlang['users']['Balance']['priceinput'], $backuser, 'HTML');
    step('getprice',$from_id);
} elseif ($user['step'] == "getprice") {
    if(!is_numeric($text)) return sendmessage($from_id, $textbotlang['users']['Balance']['errorprice'], null, 'HTML');
    if ($text > 10000000 or $text < 20000) return sendmessage($from_id, $textbotlang['users']['Balance']['errorpricelimit'],  null, 'HTML');
    update("user", "Processing_value",$text,"id",$from_id);
    sendmessage($from_id, $textbotlang['users']['Balance']['selectPatment'], $step_payment, 'HTML');
    step('get_step_payment',$from_id);
} elseif ($user['step'] == "get_step_payment") {
    if ($datain == "cart_to_offline") {
$PaySetting = select("PaySetting", "ValuePay", "NamePay", "CartDescription","select")['ValuePay'];
$Processing_value = number_format($user['Processing_value'], 2);
$textcart = "برای افزایش موجودی به صورت دستی، مبلغ $Processing_value  تومان  را به شماره‌ی حساب زیر واریز کنید 👇🏻

==================== 
$PaySetting
====================

🌅 عکس رسید خود را در این مرحله ارسال نمایید. 

⚠️ حداکثر واریز مبلغ 10 میلیون تومان می باشد.
⚠️ امکان برداشت وجه از کیف پول  نیست.
⚠️ مسئولیت واریز اشتباهی با شماست.";
        sendmessage($from_id,$textcart, $backuser, 'HTML');
        step('cart_to_cart_user',$from_id);
    }
    if ($datain == "zarinpal") {
        if ($user['Processing_value'] < 5000) {
            sendmessage($from_id, $textbotlang['users']['Balance']['zarinpal'], null, 'HTML'); 
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $sql = "INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)";
        $payment_Status = "Unpaid";
        $Payment_Method = "zarinpal";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value']);
$stmt->bindParam(5, $payment_Status);
$stmt->bindParam(6, $Payment_Method);

$stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/zarinpal/zarinpal.php?price={$user['Processing_value']}&order_id=$randomString"],
                ]
            ]
        ]);
        $user['Processing_value'] = number_format($user['Processing_value'], 2);
        $textnowpayments = "
        ✅ فاکتور پرداخت ایجاد شد.
    
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : {$user['Processing_value']} تومان

جهت پرداخت از دکمه زیر استفاده کنید👇🏻";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }
    if ($datain == "aqayepardakht") {
        if ($user['Processing_value'] < 5000) {
            sendmessage($from_id, $textbotlang['users']['Balance']['zarinpal'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $payment_Status = "Unpaid";
        $Payment_Method = "aqayepardakht";
        $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value']);
        $stmt->bindParam(5, $payment_Status);
        $stmt->bindParam(6, $Payment_Method);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/aqayepardakht/aqayepardakht.php?price={$user['Processing_value']}&order_id=$randomString"],
                ]
            ]
        ]);
        $user['Processing_value'] = number_format($user['Processing_value'], 2);
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
        if ($usdprice < 1) {
            sendmessage($from_id, $textbotlang['users']['Balance']['nowpayments'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $payment_Status = "Unpaid";
        $Payment_Method = "Nowpayments";
        $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value']);
        $stmt->bindParam(5, $payment_Status);
        $stmt->bindParam(6, $Payment_Method);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/nowpayments/nowpayments.php?price=$usdprice&order_description=Add_Balance&order_id=$randomString"],
                ]
            ]
        ]);
        $Processing_value = number_format($user['Processing_value'], 2);
        $USD = number_format($USD, 2);
        $textnowpayments = "
        ✅ فاکتور پرداخت ارزی NOWPayments ایجاد شد.
    
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : $Processing_value تومان
    
📊 قیمت دلار روز : $USD تومان
💵 نهایی:$usdprice دلار 
    
    
🌟 امکان پرداخت با ارز های مختلف وجود دارد
    
جهت پرداخت از دکمه زیر استفاده کنید👇🏻
    ";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }
    
    if ($datain == "plisio") {
        $price_rate = tronratee();
        $USD = $price_rate['result']['USD'];
        $usdprice = round($user['Processing_value'] / $USD, 2);
        if ($usdprice < 1) {
            sendmessage($from_id, $textbotlang['users']['Balance']['plisio'] . ", 当前金额: " . $usdprice . ".", null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $payment_Status = "Unpaid";
        $Payment_Method = "Nowpayments";
        $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value']);
        $stmt->bindParam(5, $payment_Status);
        $stmt->bindParam(6, $Payment_Method);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/plisio/plisio.php?price=$usdprice&order_description=Add_Balance&order_id=$randomString"],
                ]
            ]
        ]);
        $Processing_value = number_format($user['Processing_value'], 2);
        $USD = number_format($USD, 2);
        $textplisio = "
✅ Plisio 支付发票已创建.
    
🔢 发票号码 : $randomString
💰 发票金额 : $Processing_value USD
    
📊 当日汇率 : $USD
💵 最终存入 : $usdprice USD 

🌟 可以用不同的货币支付，充值产生的手续费由您承担
    
使用下面的按钮付款👇🏻
    ";
        sendmessage($from_id, $textplisio, $paymentkeyboard, 'HTML');
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
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $payment_Status = "Unpaid";
        $Payment_Method = "Currency Rial gateway";
        $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $from_id);
        $stmt->bindParam(2, $randomString);
        $stmt->bindParam(3, $dateacc);
        $stmt->bindParam(4, $user['Processing_value']);
        $stmt->bindParam(5, $payment_Status);
        $stmt->bindParam(6, $Payment_Method);
        $stmt->execute();
        $order_description = "weswap_" . $randomString . "_" . $trxprice;
        $pay = nowPayments('payment', $usdprice, $randomString, $order_description);
        if (!isset($pay->pay_address)) {
            $text_error = $pay->message;
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            step('home',$from_id);
            foreach ($admin_ids as $admin) {
                $ErrorsLinkPayment = "
                ⭕️ یک کاربر قصد پرداخت داشت که ساخت لینک پرداخت  با خطا مواجه شده و به کاربر لینک داده نشد
    ✍️ دلیل خطا : $text_error
    
    آیدی کابر : $from_id
    نام کاربری کاربر : @$username";
                sendmessage($admin, $ErrorsLinkPayment, $keyboard, 'HTML');
            }
            return;
        }
        $pay_address = $pay->pay_address;
        $payment_id = $pay->payment_id;
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://changeto.technology/quick/?amount=$trxprice&currency=TRX&address=$pay_address"]
                ],
                [
                    ['text' => $textbotlang['users']['Balance']['Confirmpaying'], 'callback_data' => "Confirmpay_user_{$payment_id}_{$randomString}"]
                ]
            ]
        ]);
        $pricetoman = number_format($user['Processing_value'], 0);
        $textnowpayments = "✅ تراکنش شما ایجاد شد

🛒 کد پیگیری:  <code>$randomString</code> 
🌐 شبکه: TRX
💳 آدرس ولت: <code>$pay_address</code>
💲 مبلغ تراکنش به ترون : <code>$trxprice</code>
💲 مبلغ تراکنش به تومان  : <code>$pricetoman</code>
💲 نرخ ترون   : <code>$trx</code>



📌 مبلغ $pricetoman  تومان بعد از تایید پرداخت توسط شبکه بلاکچین به کیف پول شما اضافه میشود

💢 لطفا به این نکات قبل از پرداخت توجه کنید 👇

🔸 در صورت اشتباه وارد کردن آدرس کیف پول، تراکنش تایید نمیشود و بازگشت وجه امکان پذیر نیست
🔹 مبلغ ارسالی نباید کمتر و یا بیشتر از مبلغ اعلام شده باشد.
🔸 کارمزد تراکنش باید از سمت کاربر پرداخت شود و باید دقیقا مبلغی که اعلام شده ارسال شود.
🔹 در صورت واریز بیش از مقدار گفته شده، امکان اضافه کردن تفاوت وجه وجود ندارد.
🔸 هر کیف پول فقط برای یک تراکنش قابل استفاده است و درصورت ارسال مجدد ارز امکان برگشت وجه نیست.
🔹 هر تراکنش بین 10 دقیقه الی  15 دقیقه  معتبر است .

✅ در صورت مشکل میتوانید با پشتیبانی در ارتباط باشید";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }
        if ($datain == "perfectmoney") {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, $textbotlang['users']['perfectmoney']['getvcode'], $backuser, 'HTML');
        step('getvcodeuser',$from_id);
    }

}
if ($user['step'] == "getvcodeuser") {
    update("user", "Processing_value",$text,"id",$from_id);
    step('getvnumbervuser',$from_id);
    sendmessage($from_id, $textbotlang['users']['perfectmoney']['getvnumber'], $backuser, 'HTML');
} elseif ($user['step'] == "getvnumbervuser") {
    step('home',$from_id);
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
    if (isset($errorMessage)) {
        sendmessage($from_id, $textbotlang['users']['perfectmoney']['errors'], null, 'HTML');
        foreach ($admin_ids as $id_admin) {
            $texterrors = "";
            sendmessage($id_admin, "❌ یک کاربر قصد افزایش موجودی با ووچر را داشته اما با خطا مواجه شده است 

دلیل خطا : $errorMessage", null, 'HTML');
        }
        return;
    }
        $Balance_id = select("user", "*", "id", $from_id,"select");
        $startTag = "<td>VOUCHER_AMOUNT</td><td>";
        $endTag = "</td>";
        $startPos = strpos($Voucher, $startTag) + strlen($startTag);
        $endPos = strpos($Voucher, $endTag, $startPos);
        $voucherAmount = substr($Voucher, $startPos, $endPos - $startPos);
        $USD = $voucherAmount * json_decode(file_get_contents('https://api.tetherland.com/currencies'), true)['data']['currencies']['USDT']['price'];
        $Balance_confrim = intval($user['Balance']) + intval($USD);
        update("user", "Balance",$Balance_confrim,"id",$from_id);
        $USD = number_format($USD, 2);
        sendmessage($from_id, "💎 با تشکر از پرداخت شما 

پرداخت شما با  موفقیت تایید گردید و مبلغ $USD به موجودی شما اضافه گردید.
⚙️ کد پیگیری پرداخت شما :$randomString  ", $keyboard, 'HTML');
    $dateacc = date('Y/m/d h:i:s');
    $randomString = bin2hex(random_bytes(5));
    $payment_Status = "paid";
    $Payment_Method = "perfectmoney";
    $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $randomString);
    $stmt->bindParam(3, $dateacc);
    $stmt->bindParam(4, $USD);
    $stmt->bindParam(5, $payment_Status);
    $stmt->bindParam(6, $Payment_Method);
    $stmt->execute();
}
if (preg_match('/Confirmpay_user_(\w+)_(\w+)/', $datain, $dataget)) {
    $id_payment = $dataget[1];
    $id_order = $dataget[2];
    $Payment_report = select("Payment_report", "*", "id_order", $id_order,"select");
    if ($Payment_report['payment_Status'] == "paid") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['Confirmpayadmin'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $StatusPayment = StatusPayment($Payment_report['Payment_Method'], $id_payment)
    if ($StatusPayment['payment_status'] == "finished") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['finished'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        $Balance_id = select("user", "*", "id", $Payment_report['id_user'],"select");
        $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
        update("user", "Balance",$Balance_confrim,"id",$Payment_report['id_user']);
        update("Payment_report", "payment_Status","paid","id_order",$Payment_report['id_order']);
        sendmessage($from_id, $textbotlang['users']['Balance']['Confirmpay'], null, 'HTML');
        $Payment_report['price'] = number_format($Payment_report['price'], 2);
    $text_report = "💵 پرداخت جدید
        
آیدی عددی کاربر : $from_id
مبلغ تراکنش : {$Payment_report['price']} 
روش پرداخت :  درگاه ارزی ریالی";
    if (strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
    } elseif ($StatusPayment['payment_status'] == "expired") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['expired'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
    } elseif ($StatusPayment['payment_status'] == "refunded") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['refunded'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
    } elseif ($StatusPayment['payment_status'] == "waiting") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['waiting'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
    }elseif ($StatusPayment['payment_status'] == "sending") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['sending'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
    }  else {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['Failed'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
    }
}elseif ($user['step'] == "cart_to_cart_user") {
    if (!$photo) {
        sendmessage($from_id, $textbotlang['users']['Balance']['Invalid-receipt'], null, 'HTML');
        return;
    }
    $dateacc = date('Y/m/d h:i:s');
    $randomString = bin2hex(random_bytes(5));
    $payment_Status = "Unpaid";
    $Payment_Method = "cart to cart";
    $stmt = $pdo->prepare("INSERT INTO Payment_report (id_user, id_order, time, price, payment_Status, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $randomString);
    $stmt->bindParam(3, $dateacc);
    $stmt->bindParam(4, $user['Processing_value']);
    $stmt->bindParam(5, $payment_Status);
    $stmt->bindParam(6, $Payment_Method);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['Balance']['Send-receipt'], $keyboard, 'HTML');
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
    step('home',$from_id);
}

#----------------Discount------------------#
if ($datain == "Discount") {
    sendmessage($from_id, $textbotlang['users']['Discount']['getcode'], $backuser, 'HTML');
    step('get_code_user',$from_id);
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
        step('home',$from_id);
        return;
    }
    $stmt = $pdo->prepare("SELECT * FROM Discount WHERE code = :code LIMIT 1");
    $stmt->bindParam(':code', $text);
    $stmt->execute();
    $get_codesql = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance_user = $user['Balance'] + number_format($get_codesql['price'], 2);
    update("user", "Balance",$balance_user,"id",$from_id);
    $stmt = $pdo->prepare("SELECT * FROM Discount WHERE code = :code");
    $stmt->bindParam(':code', $text);
    $stmt->execute();
    $get_codesql = $stmt->fetch(PDO::FETCH_ASSOC);
    step('home',$from_id);
    number_format($get_codesql['price'], 2);
    $text_balance_code = "کد هدیه با موفقیت ثبت شد و به موجودی شما مبلغ {$get_codesql['price']} تومان اضافه گردید. 🥳";
    sendmessage($from_id, $text_balance_code, $keyboard, 'HTML');
    $stmt = $pdo->prepare("INSERT INTO Giftcodeconsumed (id_user, code) VALUES (?, ?)");
    $stmt->bindParam(1, $from_id);
    $stmt->bindParam(2, $text);

$stmt->execute();
}
#----------------[  text_Tariff_list  ]------------------#
if ($text == $datatextbot['text_Tariff_list']) {
    sendmessage($from_id, $datatextbot['text_dec_Tariff_list'], null, 'HTML');
}
if($datain == "colselist"){
    deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['users']['back'], $keyboard, 'HTML');
}
if ($text == "👥 زیر مجموعه گیری") {
    $affiliatesvalue = select("affiliates", "*", null, null,"select")['affiliatesstatus'];
    if ($affiliatesvalue == "offaffiliates") {
        sendmessage($from_id, $textbotlang['users']['affiliates']['offaffiliates'], $keyboard, 'HTML');
        return;
    }
    $affiliates = select("affiliates", "*", null, null,"select");
    $textaffiliates = "{$affiliates['description']}\n\n🔗 https://t.me/$usernamebot?start=$from_id";
    telegram('sendphoto', [
        'chat_id' => $from_id,
        'photo' => $affiliates['id_media'],
        'caption' => $textaffiliates,
        'parse_mode' => "HTML",
    ]);
    $affiliatescommission = select("affiliates", "*", null, null,"select");
    if ($affiliatescommission['status_commission'] == "oncommission"){
        $affiliatespercentage = $affiliatescommission['affiliatespercentage']." درصد";
    }else{
        $affiliatespercentage = "غیرفعال";
    }
    if ($affiliatescommission['Discount'] == "onDiscountaffiliates"){
        $price_Discount = $affiliatescommission['price_Discount']." تومان";
    }else{
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

#----------------[  admin section  ]------------------#
$textadmin = ["panel", "/panel", "پنل مدیریت", "ادمین"];
if (!in_array($from_id, $admin_ids)) {
    if (in_array($text, $textadmin)) {
        sendmessage($from_id, $textbotlang['users']['Invalid-comment'], null, 'HTML');
        foreach ($admin_ids as $admin) {
            $textadmin = "
            مدیر عزیز یک کاربر قصد ورود به پنل ادمین را داشت 
    نام کاربری : @$username
    آیدی عددی : $from_id
    نام کاربر  :$first_name
            ";
            sendmessage($admin, $textadmin, null, 'HTML');
        }
    }
    return;
}
if (in_array($text, $textadmin)) {
    $text_admin = "
    سلام مدیر عزیز به پنل ادمین خوش امدی گلم😍
⭕️ نسخه فعلی ربات شما : $version
❓راهنمایی : 
1 - برای اضافه کردن پنل دکمه پنل مرزبان  را زده و دکمه اضافه کردن پنل را بزنید.
2- از دکمه مالی میتوانید وضعیت درگاه و مرچنت ها را تنظیم کنید
3-  درگاه ارزی ریالی باید فقط api nowpayments را تنظیم کنید و تمام تنظیمات کیف پول و... داخل سایت nowpayments است";
    sendmessage($from_id, $text_admin, $keyboardadmin, 'HTML');
}
if ($text == "🏠 بازگشت به منوی مدیریت") {
    sendmessage($from_id, $textbotlang['Admin']['Back-Admin'], $keyboardadmin, 'HTML');
    step('home',$from_id);
    return;
}
if ($text == "🔑 روشن / خاموش کردن قفل کانال") {
if ($channels['Channel_lock'] == "off") {
        sendmessage($from_id, $textbotlang['Admin']['channel']['join-channel-on'], $channelkeyboard, 'HTML');
        update("channels", "Channel_lock","on");
} else {
        sendmessage($from_id, $textbotlang['Admin']['channel']['join-channel-off'], $channelkeyboard, 'HTML');
        update("channels", "Channel_lock","off");
    }
}
elseif ($text == "📣 تنظیم کانال جوین اجباری") {
    sendmessage($from_id, $textbotlang['Admin']['channel']['changechannel'] . $channels['link'], $backadmin, 'HTML');
    step('addchannel',$from_id);
}
elseif ($user['step'] == "addchannel") {
    sendmessage($from_id, $textbotlang['Admin']['channel']['setchannel'], $channelkeyboard, 'HTML');
    step('home',$from_id);
    $channels_ch = select("channels", "link", null, null,"count");
    if ($channels_ch == 0) {
        $Channel_lock = 'off';
        $stmt = $pdo->prepare( "INSERT INTO channels (link, Channel_lock) VALUES (?, ?)");
        $stmt->bindParam(1, $text);
        $stmt->bindParam(2, $Channel_lock);

$stmt->execute();
    } else {
        update("channels", "link",$text);
    }
}
if ($text == "👨‍💻 اضافه کردن ادمین") {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
    step('addadmin',$from_id);
}
if ($user['step'] == "addadmin") {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['addadminset'], $keyboardadmin, 'HTML');
    step('home',$from_id);
    $stmt = $pdo->prepare("INSERT INTO admin (id_admin) VALUES (?)");
    $stmt->bindParam(1, $text);
    $stmt->execute();
}
if ($text == "❌ حذف ادمین"  ) {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
    step('deleteadmin',$from_id);
}
elseif ($user['step'] == "deleteadmin") {
    if (!is_numeric($text) || !in_array($text, $admin_ids)) return;
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['removedadmin'], $keyboardadmin, 'HTML');
    $stmt = $pdo->prepare("DELETE FROM admin WHERE id_admin = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    step('home',$from_id);
}
if ($text == "➕ محدودیت ساخت اکانت تست برای کاربر"  ) {
    sendmessage($from_id, $textbotlang['Admin']['manageusertest']['getidlimit'], $backadmin, 'HTML');
    step('add_limit_usertest_foruser',$from_id);
}
elseif ($user['step'] == "add_limit_usertest_foruser") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['getid'], $backadmin, 'HTML');
     update("user", "Processing_value",$text,"id",$from_id);
    step('get_number_limit',$from_id);
}
elseif ($user['step'] == "get_number_limit") {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimit'], $keyboard_usertest, 'HTML');
    $id_user_set = $text;
    step('home',$from_id);
    update("user", "limit_usertest", $text, "id",$user['Processing_value']);
}
if ($text == "➕ محدودیت ساخت اکانت تست برای همه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['limitall'], $backadmin, 'HTML');
    step('limit_usertest_allusers',$from_id);
}
elseif ($user['step'] == "limit_usertest_allusers") {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimitall'], $keyboard_usertest, 'HTML');
    step('home',$from_id);
    update("setting", "limit_usertest_all",$text);
    update("user", "limit_usertest",$text);
}
if ($text == "📯 تنظیمات کانال"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $channelkeyboard, 'HTML');
}
#-------------------------#
if ($text == "📊 آمار ربات" ) {
    $date = jdate('Y/m/d');
    $timeacc = jdate('H:i:s', time()); 
    $dayListSell =  select("invoice", "*", 'time_sell', $date,"count");
    $count_usertest =  select("TestAccount", "*", null, null,"count");
    $stmt = $pdo->prepare("SELECT SUM(Balance) FROM user");
    $stmt->execute();
    $Balanceall = $stmt->fetch(PDO::FETCH_ASSOC);
    $statistics =  select("user", "id", null, null,"count");
    $invoice =  select("invoice", "*", null, null,"count");
    $ping = sys_getloadavg();
    $ping = floatval($ping[0]);
    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $statistics, 'callback_data' => 'countusers'],
                ['text' => $textbotlang['Admin']['sumuser'], 'callback_data' => 'countusers'],
            ],
            [
                ['text' => $count_usertest, 'callback_data' => 'count_usertest_var'],
                ['text' => $textbotlang['Admin']['sumusertest'], 'callback_data' => 'count_usertest_var'],
            ],
            [
                ['text' => phpversion(), 'callback_data' => 'phpversion'],
                ['text' => $textbotlang['Admin']['phpversion'], 'callback_data' => 'phpversion'],
            ],
            [
                ['text' => number_format($ping, 2), 'callback_data' => 'ping'],
                ['text' => $textbotlang['Admin']['pingbot'], 'callback_data' => 'ping'],
            ],
            [
                ['text' => $invoice, 'callback_data' => 'sellservices'],
                ['text' => $textbotlang['Admin']['sellservices'], 'callback_data' => 'sellservices'],
            ],
            [
                ['text' => $dayListSell, 'callback_data' => 'dayListSell'],
                ['text' => $textbotlang['Admin']['dayListSell'], 'callback_data' => 'dayListSell'],
            ],
                        [
                ['text' => $Balanceall['SUM(Balance)'], 'callback_data' => 'Balanceall'],
                ['text' => $textbotlang['Admin']['Balanceall'], 'callback_data' => 'Balanceall'],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['btn'] . "
📆 $date → ⏰ $timeacc", $keyboardstatistics, 'HTML');
}
if ($text == "🖥 پنل مرزبان"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardmarzban, 'HTML');
}
if ($text == "🔌 وضعیت پنل"  ) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['selectpanel'], $json_list_marzban_panel, 'HTML');
    step('get_panel',$from_id);
}
elseif ($user['step'] == "get_panel") {
    $marzban_list_get = select("marzban_panel", "*", "name_panel",$text,"select");
    ini_set('max_execution_time', 1);
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    if (isset($Check_token['access_token'])) {
        $System_Stats = Get_System_Stats($marzban_list_get['url_panel'], $marzban_list_get['name_panel']);
        $active_users = $System_Stats['users_active'];
        $total_user = $System_Stats['total_user'];
        $mem_total = formatBytes($System_Stats['mem_total']);
        $mem_used = formatBytes($System_Stats['mem_used']);
        $bandwidth =formatBytes($System_Stats['outgoing_bandwidth']+$System_Stats['incoming_bandwidth']);
        $Condition_marzban = "";
        $text_marzban = "
                اطلاعات پنل شما👇:
                     
🖥 وضعیت اتصال پنل مرزبان: ✅ پنل متصل است
👥  تعداد کل کاربران: $total_user
👤 تعداد کاربران فعال: $active_users
📡 نسخه پنل مرزبان :  {$System_Stats['version']}
💻 مصرف کل  رم پنل مرزبان  : $mem_total
💻 مصرف رم پنل مرزبان  : $mem_used
🌐 ترافیک کل مصرف شده  ( آپلود / دانلود) : $bandwidth
";
    } elseif ($Check_token['detail'] == "Incorrect username or password") {
        $text_marzban = "❌ نام کاربری یا رمز عبور پنل اشتباه است";
    } else {
        $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel']."\n\rدلیل خطا : 
            {$Check_token['errror']}";
    }

    sendmessage($from_id, $text_marzban, $keyboardmarzban, 'HTML');
    step('home',$from_id);
}
if ($text == "📜 مشاهده لیست ادمین ها"  ) {
    $List_admin = null;
    $admin_ids = array_filter($admin_ids);
    foreach ($admin_ids as $admin) {
        $List_admin .= "$admin\n";
    }
    $list_admin_text = "👨‍🔧 آیدی عددی ادمین ها: 
            
        $List_admin";
    sendmessage($from_id, $list_admin_text, $admin_section_panel, 'HTML');
}
if ($text == "🖥 اضافه کردن پنل  مرزبان"  ) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addpanelname'], $backadmin, 'HTML');
    step('add_name_panel',$from_id);
} elseif ($user['step'] == "add_name_panel") {
    if(in_array($text,$marzban_list)){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['Repeatpanel'], $backadmin, 'HTML');
    return;
    }
    $vless = "onvless";
    $vmess = "offvmess";
    $trojan = "offtrojan";
    $shadowsocks = "offshadowsocks";
    $stmt = $pdo->prepare("INSERT INTO marzban_panel (name_panel, vless, vmess, trojan, shadowsocks) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$text, $vless, $vmess, $trojan, $shadowsocks]);
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addpanelurl'], $backadmin, 'HTML');
    step('add_link_panel',$from_id);
     update("user", "Processing_value",$text,"id",$from_id);
} elseif ($user['step'] == "add_link_panel") {
    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['usernameset'], $backadmin, 'HTML');
    step('add_username_panel',$from_id);
    update("marzban_panel", "url_panel", $text, "name_panel",$user['Processing_value']);
} elseif ($user['step'] == "add_username_panel") {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getpassword'], $backadmin, 'HTML');
    step('add_password_panel',$from_id);
    update("marzban_panel", "username_panel", $text, "name_panel",$user['Processing_value']);
} elseif ($user['step'] == "add_password_panel") {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addedpanel'], $backadmin, 'HTML');
    sendmessage($from_id, "🥳", $keyboardmarzban, 'HTML');
    step('home',$from_id);
    update("marzban_panel", "password_panel", $text, "name_panel",$user['Processing_value']);
}
if ($text == "📨 ارسال پیام" ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $sendmessageuser, 'HTML');
} elseif ($text == "✉️ ارسال همگانی") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetText'], $backadmin, 'HTML');
    step('gettextforsendall',$from_id);
} elseif ($user['step'] == "gettextforsendall") {
sendmessage($from_id, "درحال ارسال پیام",$keyboardaadmin, 'HTML');
step('home',$from_id);
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
    sendmessage($line, $text, null, 'HTML');
    usleep(1000000);
    }
    sendmessage($from_id, "✅ پیام به تمامی کاربران ارسال شد",$keyboardaadmin, 'HTML');
    fclose($file);
}
unlink($filename);
} elseif ($text == "📤 فوروارد همگانی" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ForwardGetext'], $backadmin, 'HTML');
    step('gettextforwardMessage',$from_id);
} elseif ($user['step'] == "gettextforwardMessage") {
sendmessage($from_id, "درحال ارسال پیام",$keyboardaadmin, 'HTML');
step('home',$from_id);
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
    sendmessage($from_id, "✅ پیام به تمامی کاربران ارسال شد",$keyboardaadmin, 'HTML');
    fclose($file);
}
unlink($filename);
}
//_________________________________________________
if ($text  == "📝 تنظیم متن ربات"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $textbot, 'HTML');
} elseif ($text == "تنظیم متن شروع"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_start'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextstart',$from_id);
} elseif ($user['step'] == "changetextstart") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_start");
    step('home',$from_id);
} elseif ($text == "دکمه سرویس خریداری شده"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Purchased_services'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextinfo',$from_id);
} elseif ($user['step'] == "changetextinfo") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_Purchased_services");
    step('home',$from_id);
} elseif ($text == "دکمه اکانت تست"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_usertest'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('changetextusertest',$from_id);
} elseif ($user['step'] == "changetextusertest") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_usertest");
    step('home',$from_id);
}elseif ($text == "متن دکمه 📚 آموزش" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_help'], $backadmin, 'HTML');
    step('text_help',$from_id);
} elseif ($user['step'] == "text_help") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_help");
    step('home',$from_id);
} elseif ($text == "متن دکمه ☎️ پشتیبانی"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_support'], $backadmin, 'HTML');
    step('text_support',$from_id);
} elseif ($user['step'] == "text_support") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_support");
    step('home',$from_id);
} elseif ($text == "دکمه سوالات متداول"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_fq'], $backadmin,'HTML');
    step('text_fq',$from_id);
} elseif ($user['step'] == "text_fq") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_fq");
    step('home',$from_id);
} elseif ($text == "📝 تنظیم متن توضیحات سوالات متداول"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_dec_fq'], $backadmin, 'HTML');
    step('text_dec_fq',$from_id);
} elseif ($user['step'] == "text_dec_fq") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_dec_fq");
    step('home',$from_id);
} elseif ($text == "📝 تنظیم متن توضیحات عضویت اجباری"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_channel'], $backadmin, 'HTML');
    step('text_channel',$from_id);
} elseif ($user['step'] == "text_channel") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_channel");
    step('home',$from_id);
} elseif ($text == "متن دکمه حساب کاربری"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_account'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    step('text_account',$from_id);
} elseif ($user['step'] == "text_account") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_account");
    step('home',$from_id);
} elseif ($text == "دکمه افزایش موجودی"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Add_Balance'], $backadmin, 'HTML');
    step('text_Add_Balance',$from_id);
} elseif ($user['step'] == "text_Add_Balance") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_Add_Balance");
    step('home',$from_id);
} elseif ($text == "متن دکمه خرید اشتراک"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_sell'],$backadmin, 'HTML');
    step('text_sell',$from_id);
} elseif ($user['step'] == "text_sell") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_sell");
    step('home',$from_id);
}elseif ($text == "متن دکمه لیست تعرفه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Tariff_list'], $backadmin, 'HTML');
    step('text_Tariff_list',$from_id);
} elseif ($user['step'] == "text_Tariff_list") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_Tariff_list");
    step('home',$from_id);
} elseif ($text == "متن توضیحات لیست تعرفه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_dec_Tariff_list'], $backadmin, 'HTML');
    step('text_dec_Tariff_list',$from_id);
} elseif ($user['step'] == "text_dec_Tariff_list") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_dec_Tariff_list");
    step('home',$from_id);
}
//_________________________________________________
if ($text == "✍️ ارسال پیام برای یک کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetText'], $backadmin, 'HTML');
    step('sendmessagetext',$from_id);
}
elseif ($user['step'] == "sendmessagetext") {
     update("user", "Processing_value",$text,"id",$from_id);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIDMessage'], $backadmin, 'HTML');
    step('sendmessagetid',$from_id);
}
elseif ($user['step'] == "sendmessagetid") {
if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $textsendadmin = "
                👤 یک پیام از طرف ادمین ارسال شده است  
متن پیام:
            {$user['Processing_value']}";
    sendmessage($text,  $textsendadmin, null, 'HTML');
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['MessageSent'], $keyboardadmin, 'HTML');
    step('home',$from_id);
}
//_________________________________________________
if ($text == "📚 بخش آموزش"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardhelpadmin, 'HTML');} 
elseif ($text == "📚 اضافه کردن آموزش"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddNameHelp'], $backadmin, 'HTML');
    step('add_name_help',$from_id);
}
elseif ($user['step'] == "add_name_help") {
    $stmt = $pdo->prepare("INSERT IGNORE INTO help (name_os) VALUES (?)");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddDecHelp'], $backadmin, 'HTML');
    step('add_dec',$from_id);
    update("user", "Processing_value",$text,"id",$from_id);
}
elseif ($user['step'] == "add_dec") {
    if ($photo) {
        update("help", "Media_os", $photoid, "name_os",$user['Processing_value']);
        update("help", "Description_os", $caption, "name_os",$user['Processing_value']);
        update("help", "type_Media_os", "photo", "name_os",$user['Processing_value']);
    } elseif ($text) {
        update("help", "Description_os", $text, "name_os",$user['Processing_value']);
    } elseif ($video) {
        update("help", "Media_os", $videoid, "name_os",$user['Processing_value']);
        update("help", "Description_os", $caption, "name_os",$user['Processing_value']);
        update("help", "type_Media_os", "video", "name_os",$user['Processing_value']);
    }
    sendmessage($from_id, $textbotlang['Admin']['Help']['SaveHelp'], $keyboardadmin, 'HTML');
    step('home',$from_id);
}
elseif ($text == "❌ حذف آموزش") {
    sendmessage($from_id, $textbotlang['Admin']['Help']['SelectName'], $json_list_help, 'HTML');
    step('remove_help',$from_id);
}
elseif ($user['step'] == "remove_help") {
    $stmt = $pdo->prepare("DELETE FROM help WHERE name_os = ?");
    $stmt->execute([$text]);
    sendmessage($from_id, $textbotlang['Admin']['Help']['RemoveHelp'], $keyboardhelpadmin, 'HTML');
    step('home',$from_id);
}
//_________________________________________________
if (preg_match('/Response_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    update("user", "Processing_value",$iduser,"id",$from_id);
    step('getmessageAsAdmin',$from_id);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetTextResponse'], $backadmin, 'HTML');}
elseif ($user['step'] == "getmessageAsAdmin") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SendMessageuser'], null, 'HTML');
    if($text){
        $textSendAdminToUser = "
                📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
            
    متن پیام : 
    $text";
    sendmessage($user['Processing_value'], $textSendAdminToUser, null, 'HTML');
    }
    if($photo){
        $textSendAdminToUser = "
                📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
            
    متن پیام : 
    $caption";
        telegram('sendphoto', [
            'chat_id' => $user['Processing_value'],
            'photo' => $photoid,
            'reply_markup' => $Response,
            'caption' => $textSendAdminToUser,
            'parse_mode' => "HTML",
        ]);
    }
    step('home',$from_id);
}
//_________________________________________________
$Bot_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['Bot_Status'], 'callback_data' => $setting['Bot_Status']],
        ],
    ]
]);
if ($text == "📡 وضعیت ربات"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status, 'HTML');
}
elseif ($datain == "✅  ربات روشن است"  ) {
    update("setting", "Bot_Status","❌ ربات خاموش است");
    Editmessagetext($from_id, $message_id,  $textbotlang['Admin']['Status']['BotStatusOff'], null);}
elseif ($datain == "❌ ربات خاموش است"  ) {
    update("setting", "Bot_Status","✅  ربات روشن است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['BotStatuson'], null);
}

//_________________________________________________
$flow_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['flow'], 'callback_data' => $setting['flow']],
        ],
    ]
]);
if ($text == "🍀 قابلیت flow"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Status']['flow'], $flow_Status, 'HTML');
}
if ($datain == "flowon"  ) {
    update("setting", "flow","offflow");
    Editmessagetext($from_id, $message_id,  $textbotlang['Admin']['Status']['flowStatusOff'], null);}
elseif ($datain == "offflow"  ) {
    update("setting", "flow","flowon");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['flowStatuson'], null);
}
#-----------------[ not user change status ]-----------------#
$not_user = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['NotUser'], 'callback_data' => $setting['NotUser']],
        ],
    ]
]);
if ($text == "👤 دکمه نام کاربری"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Status']['UsernameTitle'], $not_user, 'HTML');
}
if ($datain == "onnotuser"  ) {
    update("setting", "NotUser","offnotuser");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['UsernameStatusOff'], null);}
elseif ($datain == "offnotuser"  ) {
    update("setting", "NotUser","onnotuser");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['UsernameStatuson'], null);
}
//_________________________________________________
if ($text == "🔒 مسدود کردن کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockUserId'], $backadmin, 'HTML');
    step('getidblock',$from_id);
} elseif ($user['step'] == "getidblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $userblock = select("user", "*", "id",$text,"select");
    if ($userblock['User_Status'] == "block") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockedUser'], $backadmin, 'HTML');
        return;
    }
    update("user", "Processing_value",$text,"id",$from_id);
    update("user", "User_Status", "block", "id",$text);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockUser'], $backadmin, 'HTML');
    step('adddecriptionblock',$from_id);
} elseif ($user['step'] == "adddecriptionblock") {
    update("user", "description_blocking", $text, "id",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['DescriptionBlock'], $keyboardadmin, 'HTML');
    step('home',$from_id);
} elseif ($text == "🔓 رفع مسدودی کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    step('getidunblock',$from_id);
} elseif ($user['step'] == "getidunblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $userunblock = select("user", "*", "id",$text,"select");
    if ($userunblock['User_Status'] == "Active") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserNotBlock'], $backadmin, 'HTML');
        return;
    }
    update("user", "User_Status", "Active", "id",$text);
    update("user", "description_blocking", "", "id",$text);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserUnblocked'], $keyboardadmin, 'HTML');
    step('home',$from_id);
}
//_________________________________________________
if ($text == "♨️ بخش قوانین"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $rollkey, 'HTML');}
elseif ($text == "⚖️ متن قانون"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_roll'], $backadmin, 'HTML');
    step('text_roll',$from_id);
}
elseif ($user['step'] == "text_roll") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text","text_roll");
   step('home',$from_id);
}
$roll_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['roll_Status'], 'callback_data' => $setting['roll_Status']],
        ],
    ]
]);
if ($text == "💡 روشن / خاموش کردن تایید قوانین"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Status']['rollTitle'], $roll_Status, 'HTML');
}
if ($datain == "✅ تایید قانون روشن است"  ) {
    update("setting", "roll_Status","❌ تایید قوانین خاموش است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['rollStatusOff'], null);}
elseif ($datain == "❌ تایید قوانین خاموش است"  ) {
    update("setting", "roll_Status","✅ تایید قانون روشن است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['rollStatuson'], null);
}
//_________________________________________________
if ($text == "👤 خدمات کاربر" ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $User_Services, 'HTML');
}
#-------------------------#

elseif ($text == "📊 وضعیت تایید شماره کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    step('get_status',$from_id);
} elseif ($user['step'] == "get_status") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $user_phone_status = select("user", "*", "id",$text,"select");
    if ($user_phone_status['number'] == "none") {
        sendmessage($from_id, $textbotlang['Admin']['phone']['notactive'], $User_Services, 'HTML');
    } else {
        sendmessage($from_id, $textbotlang['Admin']['phone']['active'], $User_Services, 'HTML');
    }
    step('home',$from_id);
}
#-------------------------#

$get_number = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['get_number'], 'callback_data' => $setting['get_number']],
        ],
    ]
]);
if ($text == "☎️ وضعیت احراز هویت شماره تماس" ) {
    sendmessage($from_id, $textbotlang['Admin']['Status']['phoneTitle'], $get_number, 'HTML');
}
if ($datain == "✅ تایید شماره موبایل روشن است" ) {
    update("setting", "get_number","❌ احرازهویت شماره تماس غیرفعال است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['phoneStatusOff'], null);}
elseif ($datain == "❌ احرازهویت شماره تماس غیرفعال است" ) {
    update("setting", "get_number","✅ تایید شماره موبایل روشن است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['phoneStatuson'], null);
}
#-------------------------#
if ($text == "👀 مشاهده شماره تلفن کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    step('get_number_admin',$from_id);
}
elseif ($user['step'] == "get_number_admin") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $user_phone_number = select("user", "*", "id",$text,"select");
    step('home',$from_id);
    if ($user_phone_number['number'] == "none") {
        sendmessage($from_id, $textbotlang['Admin']['phone']['NotSend'], $User_Services, 'HTML');
        return;
    }
    $text_number = "
            ☎️ شماره تلفن کاربر :{$user_phone_number['number']}
             ";
    sendmessage($from_id, $text_number, $User_Services, 'HTML');
}
#-------------------------#
if ($text == "👈 تایید دستی شماره" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    step('confrim_number',$from_id);
}
elseif ($user['step'] == "confrim_number") {
    update("user", "number", "confrim number by admin", "id",$text);
    step('home',$text);
    sendmessage($from_id, $textbotlang['Admin']['phone']['active'], $User_Services, 'HTML');
}
if ($text == "📣 تنظیم کانال گزارش"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Channel']['ReportChannel'] . $setting['Channel_Report'], $backadmin, 'HTML');
    step('addchannelid',$from_id);
}
elseif ($user['step'] == "addchannelid") {
    sendmessage($from_id, $textbotlang['Admin']['Channel']['SetChannelReport'], $keyboardadmin, 'HTML');
    update("setting", "Channel_Report",$text);
    step('home',$from_id);
    sendmessage($setting['Channel_Report'], $textbotlang['Admin']['Channel']['TestChannel'], null, 'HTML');
}
#-------------------------#
if ($text == "🏬 بخش فروشگاه"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $shopkeyboard, 'HTML');
} 
elseif ($text == "🛍 اضافه کردن محصول"  ) {
       $locationproduct = select("marzban_panel", "*", null, null,"count");
    if ($locationproduct == 0) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpaneladmin'], null, 'HTML');
    return;
}
    sendmessage($from_id, $textbotlang['Admin']['Product']['AddProductStepOne'], $backadmin, 'HTML');
    step('get_limit',$from_id);
}
elseif ($user['step'] == "get_limit") {
    $randomString = bin2hex(random_bytes(2));
    $stmt = $pdo->prepare("INSERT IGNORE INTO product (name_product, code_product) VALUES (?, ?)");
    $stmt->bindParam(1, $text);
    $stmt->bindParam(2, $randomString);

$stmt->execute();
    update("user", "Processing_value",$randomString,"id",$from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['Service_location'], $json_list_marzban_panel, 'HTML');
    step('get_location',$from_id);
}elseif ($user['step'] == "get_location") {
    update("product", "Location",$text, "code_product",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetLimit'], $backadmin, 'HTML');
    step('get_time',$from_id);
}elseif ($user['step'] == "get_time") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    update("product", "Volume_constraint",$text, "code_product",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GettIime'], $backadmin, 'HTML');
    step('get_price',$from_id);
}elseif ($user['step'] == "get_price") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    update("product", "Service_time",$text, "code_product",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetPrice'], $backadmin, 'HTML');
    step('endstep',$from_id);
} elseif ($user['step'] == "endstep") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    update("product", "price_product",$text, "code_product",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Product']['SaveProduct'], $shopkeyboard, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "👨‍🔧 بخش ادمین"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $admin_section_panel, 'HTML');
}
#-------------------------#
if ($text == "⚙️ تنظیمات"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $setting_panel, 'HTML');
}
#-------------------------#
if ($text == "📱 احراز هویت شماره" ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $valid_Number, 'HTML');
}
#-------------------------#
if ($text == "🔑 تنظیمات اکانت تست"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard_usertest, 'HTML');
}
#-------------------------#
if (preg_match('/Confirm_pay_(\w+)/', $datain, $dataget) ) {
    $order_id = $dataget[1];
    $Payment_report = select("Payment_report", "*", "id_order", $order_id,"select");
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'],"select");
    if ($Payment_report['payment_Status'] == "paid" || $Payment_report['payment_Status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
    update("user", "Balance",$Balance_confrim,"id",$Payment_report['id_user']);
    update("Payment_report", "payment_Status","paid","id_order",$Payment_report['id_order']);
    $Payment_report['price'] = number_format($Payment_report['price'], 2);
    $textconfrom = "
            💵 پرداخت با موفقیت تایید گردید.
              به موجودی کاربر مبلغ {$Payment_report['price']} اضافه گردید.
            ";
    sendmessage($from_id, $textconfrom, null, 'HTML');
    sendmessage($Payment_report['id_user'], "💎 کاربر گرامی مبلغ {$Payment_report['price']} تومان به کیف پول شما واریز گردید با تشکر از پرداخت شما.
        
        🛒 کد پیگیری شما: {$Payment_report['id_order']}", null, 'HTML');
             $text_report = "📣 یک ادمین رسید پرداخت کارت به کارت را تایید کرد.

اطلاعات :
👤آیدی عددی  ادمین تایید کننده : $from_id
💰 مبلغ پرداخت : {$Payment_report['price']}
"; 
     if (strlen($setting['Channel_Report']) > 0) {    
         sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
         }
}
#-------------------------#
if (preg_match('/reject_pay_(\w+)/', $datain, $datagetr)) {
    $id_order = $datagetr[1];
    $Payment_report = select("Payment_report", "*", "id_order", $id_order,"select");
    update("user", "Processing_value",$Payment_report['id_user'],"id",$from_id);
    update("user", "Processing_value_one",$id_order,"id",$from_id);
    if ($Payment_report['payment_Status'] == "reject" || $Payment_report['payment_Status']  == "paid") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    update("Payment_report", "payment_Status","reject","id_order",$id_order);
    sendmessage($from_id, $textbotlang['Admin']['Payment']['Reasonrejecting'], $backadmin, 'HTML');
    step('reject-dec',$from_id);
    Editmessagetext($from_id, $message_id, $text, null);}
elseif ($user['step'] == "reject-dec") {
    update("Payment_report", "dec_not_confirmed",$text,"id_order",$user['Processing_value_one']);
    $text_reject = "❌ کاربر گرامی پرداخت شما به دلیل زیر رد گردید.
        ✍️ $text
        🛒 کد پیگیری پرداخت: {$user['Processing_value_one']}
        ";
    sendmessage($from_id, $textbotlang['Admin']['Payment']['Rejected'], $keyboardadmin, 'HTML');
    sendmessage($user['Processing_value'], $text_reject, null, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "❌ حذف محصول"  ) {
    sendmessage($from_id,$textbotlang['Admin']['Product']['Rmove_location'], $json_list_marzban_panel, 'HTML');
    step('selectloc',$from_id);
}
elseif ($user['step'] == "selectloc") {
    update("user", "Processing_value",$text,"id",$from_id);
    step('remove-product',$from_id);
    sendmessage($from_id,$textbotlang['Admin']['Product']['selectRemoveProduct'], $json_list_product_list_admin, 'HTML');}
elseif ($user['step'] == "remove-product") {
    if (!in_array($text, $name_product)) {
        sendmessage($from_id, $textbotlang['users']['sell']['error-product'], null, 'HTML');
        return;
    }
    $ydf = '/all';
    $stmt = $pdo->prepare("DELETE FROM product WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $stmt->execute([$text, $user['Processing_value'], $ydf]);
    sendmessage($from_id, $textbotlang['Admin']['Product']['RemoveedProduct'], $shopkeyboard, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "✏️ ویرایش محصول"  ) {
    sendmessage($from_id,$textbotlang['Admin']['Product']['Rmove_location'], $json_list_marzban_panel, 'HTML');
    step('selectlocedite',$from_id);
}
elseif ($user['step'] == "selectlocedite") {
    update("user", "Processing_value_one",$text,"id",$from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectEditProduct'], $json_list_product_list_admin, 'HTML');
    step('change_filde',$from_id);
}
elseif ($user['step'] == "change_filde") {
    if (!in_array($text, $name_product)) {
        sendmessage($from_id, $textbotlang['users']['sell']['error-product'], null,'HTML');
        return;
    }
    update("user", "Processing_value",$text,"id",$from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectfieldProduct'], $change_product, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "قیمت"  ) {
    sendmessage($from_id, "قیمت جدید را ارسال کنید", $backadmin, 'HTML');
    step('change_price',$from_id);
}
elseif ($user['step'] == "change_price") {
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    $location = '/all';
    $stmtFirst = $pdo->prepare("UPDATE product SET price_product = ? WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $stmtFirst->execute([$text, $user['Processing_value'], $user['Processing_value_one'], $location]);
    $stmtSecond = $pdo->prepare("UPDATE invoice SET price_product = ? WHERE name_product = ? AND Service_location = ?");
    $stmtSecond->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    sendmessage($from_id, "✅ قیمت محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "نام محصول"  ) {
    sendmessage($from_id, "نام جدید را ارسال کنید", $backadmin, 'HTML');
    step('change_name',$from_id);
}
elseif ($user['step'] == "change_name") {
    $value = "/all";
    $stmtFirst = $pdo->prepare("UPDATE product SET name_product = ? WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $stmtFirst->execute([$text, $user['Processing_value'], $user['Processing_value_one'], $value]);
    $sqlSecond = "UPDATE invoice SET name_product = ? WHERE name_product = ? AND Service_location = ?";
    $stmtSecond = $pdo->prepare($sqlSecond);
    $stmtSecond->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    sendmessage($from_id, "✅نام محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "حجم"  ) {
    sendmessage($from_id, "حجم جدید را ارسال کنید", $backadmin, 'HTML');
    step('change_val',$from_id);
} 
elseif ($user['step'] == "change_val") {
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    $sqlInvoice = "UPDATE invoice SET Volume = ? WHERE name_product = ? AND Service_location = ?";
    $stmtInvoice = $pdo->prepare($sqlInvoice);
    $stmtInvoice->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    $sqlProduct = "UPDATE product SET Volume_constraint = ? WHERE name_product = ? AND Location = ?";
    $stmtProduct = $pdo->prepare($sqlProduct);
    $stmtProduct->execute([$text, $user['Processing_value'], $user['Processing_value_one']]);
    sendmessage($from_id, $textbotlang['Admin']['Product']['volumeUpdated'], $shopkeyboard, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "زمان"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['NewTime'], $backadmin, 'HTML');
    step('change_time',$from_id);
}
elseif ($user['step'] == "change_time") {
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
    step('home',$from_id);
}
#-------------------------#
if ($text == "⏳ زمان سرویس تست"  ) {
    sendmessage($from_id, "🕰 مدت زمان سرویس تست را ارسال کنید.
        زمان فعلی: {$setting['time_usertest']} ساعت
        ⚠️ زمان بر حسب ساعت است.", $backadmin, 'HTML');
    step('updatetime',$from_id);
}
elseif ($user['step'] == "updatetime") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    update("setting", "time_usertest",$text);
    sendmessage($from_id, $textbotlang['Admin']['Usertest']['TimeUpdated'], $keyboard_usertest, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "💾 حجم اکانت تست"  ) {
    sendmessage($from_id, "حجم سرویس تست را ارسال کنید.
            حجم فعلی: {$setting['val_usertest']} مگابایت
        ⚠️ حجم بر حسب مگابایت است.", $backadmin,'HTML');
    step('val_usertest',$from_id);
}
elseif ($user['step'] == "val_usertest") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    update("setting", "val_usertest",$text);
    sendmessage($from_id, $textbotlang['Admin']['Usertest']['VolumeUpdated'], $keyboard_usertest, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "⬆️️️ افزایش موجودی کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalance'], $backadmin, 'HTML');
    step('add_Balance',$from_id);
}
elseif ($user['step'] == "add_Balance") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['PriceBalance'], $backadmin, 'HTML');
    update("user", "Processing_value",$text,"id",$from_id);
    step('get_price_add',$from_id);
}
elseif ($user['step'] == "get_price_add") {
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalanceUser'], $User_Services, 'HTML');
    $Balance_user = select("user", "*", "id", $user['Processing_value'],"select");
    $Balance_add_user = $Balance_user['Balance'] + $text;
    update("user", "Balance",$Balance_add_user,"id",$user['Processing_value']);
    $text = number_format($text, 2);
    $textadd = "💎 کاربر عزیز مبلغ $text تومان به موجودی کیف پول تان اضافه گردید.";
    sendmessage($user['Processing_value'], $textadd, null, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "⬇️ کم کردن موجودی" ) {
    sendmessage($from_id, $textbotlang['Admin']['Balance']['NegativeBalance'], $backadmin, 'HTML');
    step('Negative_Balance',$from_id);
}
elseif ($user['step'] == "Negative_Balance") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['PriceBalancek'], $backadmin, 'HTML');
    update("user", "Processing_value",$text,"id",$from_id);
    step('get_price_Negative',$from_id);
}
elseif ($user['step'] == "get_price_Negative") {
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['NegativeBalanceUser'], $User_Services, 'HTML');
    $Balance_user = select("user", "*", "id", $user['Processing_value'],"select");
    $Balance_Low_user = $Balance_user['Balance'] - $text;
    update("user", "Balance",$Balance_Low_user,"id",$user['Processing_value']);
    $text = number_format($text);
    $textkam = "❌ کاربر عزیز مبلغ $text تومان از  موجودی کیف پول تان کسر گردید.";
    sendmessage($user['Processing_value'], $textkam, null, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "👁‍🗨 مشاهده اطلاعات کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    step('show_info',$from_id);
}
elseif ($user['step'] == "show_info") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $user = select("user", "*", "id",$text,"select");
    $roll_Status = [
        '1' => $textbotlang['Admin']['ManageUser']['Acceptedphone'],
        '0' => $textbotlang['Admin']['ManageUser']['Failedphone'],
    ][$user['roll_Status']];
    $userinfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $text, 'callback_data' => "id_user"],
                ['text' => $textbotlang['Admin']['ManageUser']['Userid'], 'callback_data' => "id_user"],
            ],
            [
                ['text' => $user['limit_usertest'], 'callback_data' => "limit_usertest"],
                ['text' => $textbotlang['Admin']['ManageUser']['LimitUsertest'], 'callback_data' => "limit_usertest"],
            ],
            [
                ['text' => $roll_Status, 'callback_data' => "roll_Status"],
                ['text' => $textbotlang['Admin']['ManageUser']['rollUser'], 'callback_data' => "roll_Status"],
            ],
            [
                ['text' => $user['number'], 'callback_data' => "number"],
                ['text' => $textbotlang['Admin']['ManageUser']['PhoneUser'], 'callback_data' => "number"],
            ],
            [
                ['text' => $user['Balance'], 'callback_data' => "Balance"],
                ['text' => $textbotlang['Admin']['ManageUser']['BalanceUser'], 'callback_data' => "Balance"],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ViewInfo'], $userinfo, 'HTML');
    sendmessage($from_id, $textbotlang['users']['selectoption'], $User_Services, 'HTML');
    step('home',$from_id);
}
#-------------------------#
$help_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['help_Status'], 'callback_data' => $setting['help_Status']],
        ],
    ]
]);
if ($text == "💡 وضعیت بخش آموزش"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Status']['HelpTitle'], $help_Status, 'HTML');
}
if ($datain == "✅ آموزش فعال است"  ) {
    update("setting", "help_Status","❌ آموزش غیرفعال است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['HelpStatusOff'], null);}
elseif ($datain == "❌ آموزش غیرفعال است"  ) {
    update("setting", "help_Status","✅ آموزش فعال است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['HelpStatuson'], null);
}
#-------------------------#
if ($text == "🎁 ساخت کد هدیه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['GetCode'], $backadmin, 'HTML');
    step('get_code',$from_id);
}
elseif ($user['step'] == "get_code") {
    if (!preg_match('/^[A-Za-z]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['ErrorCode'], null, 'HTML');
        return;
    }
$stmt = $pdo->prepare("INSERT INTO Discount (code) VALUES (?)");
$stmt->bindParam(1, $text);
$stmt->execute();
    
    sendmessage($from_id, $textbotlang['Admin']['Discount']['PriceCode'], null, 'HTML');
    step('get_price_code',$from_id);
    update("user", "Processing_value",$text,"id",$from_id);
}
elseif ($user['step'] == "get_price_code") {
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    update("Discount", "price",$text,"code",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['SaveCode'], $keyboardadmin, 'HTML');
    step('home',$from_id);
}
#-------------------------#
$getNumberIran = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['iran_number'], 'callback_data' => $setting['iran_number']],
        ],
    ]
]);
if ($text == "تایید شماره ایرانی 🇮🇷" ) {
    sendmessage($from_id, $textbotlang['Admin']['Status']['PhoneIranTitle'], $getNumberIran, 'HTML');
}
if ($datain == "✅ احرازشماره ایرانی روشن است" ) {
    update("setting", "iran_number","❌ بررسی شماره ایرانی غیرفعال است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['PhoneIranStatusOff'], null);}
elseif ($datain == "❌ بررسی شماره ایرانی غیرفعال است" ) {
    update("setting", "iran_number","✅ احرازشماره ایرانی روشن است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['PhoneIranStatuson'], null);
}
#-------------------------#
$sublinkkeyboard = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['sublink'], 'callback_data' => $setting['sublink']],
        ],
    ]
]);
if ($text == "🔗 ارسال لینک سابسکرایبشن") {
        if ($setting['configManual'] == "✅ ارسال کانفیگ بعد خرید فعال است.") {
                sendmessage($from_id, "ابتدا  ارسال کانفیگ را خاموش کنید", null, 'HTML');
                return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Status']['subTitle'], $sublinkkeyboard, 'HTML');
}
if ($datain == "✅ لینک اشتراک فعال است."  ) {
    update("setting", "sublink","❌ ارسال لینک سابسکرایب غیرفعال است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['subStatusOff'], null);}
elseif ($datain == "❌ ارسال لینک سابسکرایب غیرفعال است"  ) {
    update("setting", "sublink","✅ لینک اشتراک فعال است.");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['subStatuson'], null);
}
#-------------------------#
$configkeyboard = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $setting['configManual'], 'callback_data' => $setting['configManual']],
        ],
    ]
]);
if ($text == "⚙️ارسال کانفیگ"  ) {
    if ($setting['sublink'] == "✅ لینک اشتراک فعال است.") {
                sendmessage($from_id, "ابتدا لینک اشتراک را خاموش کنید", null, 'HTML');
                return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Status']['configTitle'], $configkeyboard, 'HTML');
}
if ($datain == "✅ ارسال کانفیگ بعد خرید فعال است."  ) {
    update("setting", "configManual","❌ ارسال کانفیگ دستی خاموش است");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['configStatusOff'], null);}
elseif ($datain == "❌ ارسال کانفیگ دستی خاموش است"  ) {
    update("setting", "configManual","✅ ارسال کانفیگ بعد خرید فعال است.");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['configStatuson'], null);
}
#----------------[  view order user  ]------------------#
if ($text == "🛍 مشاهده سفارشات کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ViewOrder'], $backadmin, 'HTML');
    step('GetIdAndOrdedrs',$from_id);
}
elseif ($user['step'] == "GetIdAndOrdedrs") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $OrderUsers = select("invoice", "*","id_user" ,$text ,"fetchAll");
    foreach ($OrderUsers as $OrderUser) {
        if (isset($OrderUser['time_sell'])) {
            $datatime = $OrderUser['time_sell'];
        } else {
            $datatime = $textbotlang['Admin']['ManageUser']['dataorder'];
        }
        $text_order = "
            🛒 شماره سفارش  :  <code>{$OrderUser['id_invoice']}</code>
    🙍‍♂️ شناسه کاربر : <code>{$OrderUser['id_user']}</code>
    👤 نام کاربری اشتراک :  <code>{$OrderUser['username']}</code> 
    📍 لوکیشن سرویس :  {$OrderUser['Service_location']}
    🛍 نام محصول :  {$OrderUser['name_product']}
    💰 قیمت پرداختی سرویس : {$OrderUser['price_product']} تومان
    ⚜️ حجم سرویس خریداری شده : {$OrderUser['Volume']}
    ⏳ زمان سرویس خریداری شده : {$OrderUser['Service_time']} روزه
    📆 تاریخ خرید : $datatime
            ";
        sendmessage($from_id, $text_order, null, 'HTML');
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SendOrder'], $User_Services, 'HTML');
    step('home',$from_id);
}
#----------------[  remove Discount   ]------------------#
if ($text == "❌ حذف کد هدیه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemoveCode'], $json_list_Discount_list_admin, 'HTML');
    step('remove-Discount',$from_id);
}
elseif ($user['step'] == "remove-Discount") {
    if (!in_array($text, $code_Discount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['NotCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM Discount WHERE code = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemovedCode'], $shopkeyboard, 'HTML');
}
#----------------[  REMOVE protocol   ]------------------#
if ($text == "🗑 حذف پروتکل"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Protocol']['RemoveProtocol'], $keyboardprotocollist, 'HTML');
    step('removeprotocol',$from_id);
}
elseif ($user['step'] == "removeprotocol") {
    if (!in_array($text, $protocoldata)) {
        sendmessage($from_id, $textbotlang['Admin']['Protocol']['invalidProtocol'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Protocol']['RemovedProtocol'], $keyboardmarzban, 'HTML');
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $text);
    $stmt->execute();
    step('home',$from_id);
}
if ($text == "❌ حذف سرویس کاربر"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemoveService'], $backadmin, 'HTML');
    step('removeservice',$from_id);
}
elseif ($user['step'] == "removeservice") {
    $info_product = select("invoice", "*", "username", $text,"select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel",$info_product['Service_location'],"select");
    $get_username_Check = getuser($text,$marzban_list_get['name_panel']);
    if(isset($get_username_Check['status'])){
        removeuser($marzban_list_get['name_panel'], $text);
    }
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE username = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemovedService'], $keyboardadmin, 'HTML');
    step('home',$from_id);
}
if ($text == "💡 روش ساخت نام کاربری"  ) {
    $text_username = "⭕️ روش ساخت نام کاربری برای اکانت ها را از دکمه زیر انتخاب نمایید.

⚠️ در صورتی که کاربری نام کاربری نداشته باشه کلمه NOT_USERNAME جای نام کاربری اعمال خواهد شد.

⚠️ در صورتی که نام کاربری وجود داشته باشه یک عدد رندوم به نام کاربری اضافه خواهد شد

روش فعلی : {$setting['MethodUsername']}";
    sendmessage($from_id, $text_username, $MethodUsername, 'HTML');
    step('updatemethodusername',$from_id);
}
elseif ($user['step'] == "updatemethodusername") {
    update("setting", "MethodUsername",$text);
    sendmessage($from_id, $textbotlang['Admin']['AlgortimeUsername']['SaveData'], $keyboardmarzban, 'HTML');
        if ($text == "متن دلخواه + عدد رندوم") {
    step('getnamecustom',$from_id);
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['customnamesend'], $backuser, 'HTML');
    return;
    }
    step('home',$from_id);
}
elseif ($user['step'] == "getnamecustom") {
    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['invalidname'], $backadmin, 'html');
        return;
    }
    update("setting", "namecustome",$text);
    step('home',$from_id);
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['savedname'], $optionMarzban, 'HTML');
}
#----------------[  MANAGE PAYMENT   ]------------------#

if($text == "💵 مالی"  ){
        sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardpaymentManage, 'HTML');
}
if($text == "💳 تنظبمات درگاه آفلاین"  ){
            sendmessage($from_id, $textbotlang['users']['selectoption'], $CartManage, 'HTML');
}
if($text == "💳 تنظیم شماره کارت"  ){
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "CartDescription","select");
    $textcart = "💳 شماره کارت خود را ارسال کنید

⭕️ همراه با شماره کارت می توانید نام صاحب کارت هم ارسال نمایید.

💳 شماره کارت فعلی شما : {$PaySetting['ValuePay']}";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    step('changecard',$from_id);
}
elseif($user['step'] == "changecard"){
    sendmessage($from_id,$textbotlang['Admin']['SettingPayment']['Savacard'] , $CartManage,'HTML');
    update("PaySetting", "ValuePay",$text,"NamePay","CartDescription");
   step('home',$from_id);
}
if ($text == "🔌 وضعیت درگاه آفلاین"  ) {
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "Cartstatus","select")['ValuePay'];
    $card_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $PaySetting, 'callback_data' => $PaySetting],
        ],
    ]
]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['cardTitle'], $card_Status, 'HTML');
}
if ($datain == "oncard"  ){
    update("PaySetting", "ValuePay","offcard","NamePay","Cartstatus");
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['cardStatusOff'], null);}
elseif ($datain == "offcard"  ) {
    update("PaySetting", "ValuePay","oncard","NamePay","Cartstatus");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['cardStatuson'], null);
}
if($text == "💵 تنظیمات nowpayment"  ){
            sendmessage($from_id, $textbotlang['users']['selectoption'], $NowPaymentsManage, 'HTML');
}
if($text == "🧩 api nowpayment"  ){
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "apinowpayment","select")['ValuePay'];
    $textcart = "⚙️ api سایت nowpayments.io را ارسال نمایید

api nowpayment :$PaySetting";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    step('apinowpayment',$from_id);
}
elseif($user['step'] == "apinowpayment"){
    sendmessage($from_id,$textbotlang['Admin']['SettingnowPayment']['Savaapi'] , $NowPaymentsManage,'HTML');
    update("PaySetting", "ValuePay",$text,"NamePay","apinowpayment");
    step('home',$from_id);
}
if ($text == "🔌 وضعیت درگاه nowpayments"  ) {
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "nowpaymentstatus","select")['ValuePay'];
    $now_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $PaySetting, 'callback_data' => $PaySetting],
        ],
    ]
]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['nowpaymentsTitle'], $now_Status, 'HTML');
}
if ($datain == "onnowpayment"  ){
    update("PaySetting", "ValuePay","offnowpayment","NamePay","nowpaymentstatus");
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['nowpaymentsStatusOff'], null);}
elseif ($datain == "offnowpayment"  ) {
    update("PaySetting", "ValuePay","onnowpayment","NamePay","nowpaymentstatus");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['nowpaymentsStatuson'], null);
}

if ($text == "🟡 Plisio 设置") {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $PlisioManage, 'HTML');
}
if ($text == "🧩 Plisio API") {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "apiplisio", "select")['ValuePay'];
    $textcart = "⚙️ Plisios.io API 密钥

Plisio API 密钥:$PaySetting";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    step('apiplisio', $from_id);
} elseif ($user['step'] == "apiplisio") {
    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $PlisioManage, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "apiplisio");
    step('home', $from_id);
}
if ($text == "🔌 plisio 状态") {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "plisiostatus", "select")['ValuePay'];
    $now_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $PaySetting, 'callback_data' => $PaySetting],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['plisioTitle'], $now_Status, 'HTML');
}
if ($datain == "onplisio") {
    update("PaySetting", "ValuePay", "offplisio", "NamePay", "plisiostatus");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['plisioStatusOff'], null);
} elseif ($datain == "offplisio") {
    update("PaySetting", "ValuePay", "onplisio", "NamePay", "plisiostatus");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['plisioStatuson'], null);
}

if ($text == "💎 درگاه ارزی ریالی"  ) {
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "digistatus","select")['ValuePay'];
    $digi_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $PaySetting, 'callback_data' => $PaySetting],
        ],
    ]
]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['digiTitle'], $digi_Status, 'HTML');
}
if ($datain == "offdigi"  ){
    update("PaySetting", "ValuePay","ondigi","NamePay","digistatus");
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['digiStatuson'], null);}
elseif ($datain == "ondigi"  ) {
    update("PaySetting", "ValuePay","offdigi","NamePay","digistatus");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['digiStatusOff'], null);
}
if($text == "🟡  درگاه زرین پال"  ){
    sendmessage($from_id, $textbotlang['users']['selectoption'], $zarinpal, 'HTML');
}
if($text == "تنظیم مرچنت"  ){
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id","select");
    $textzarinpal = "💳 مرچنت کد خود را از زرین پال دریافت و در این قسمت وارد کنید

مرچنت کد فعلی شما : {$PaySetting['ValuePay']}";
    sendmessage($from_id, $textzarinpal, $backadmin, 'HTML');
    step('merchant_id',$from_id);
}
elseif($user['step'] == "merchant_id"){
    sendmessage($from_id,$textbotlang['Admin']['SettingnowPayment']['Savaapi'] , $zarinpal,'HTML');
    update("PaySetting", "ValuePay",$text,"NamePay","merchant_id");
    step('home',$from_id);
}
if ($text == "وضعیت درگاه زرین پال"  ) {
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "statuszarinpal","select")['ValuePay'];
    $zarinpal_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $PaySetting, 'callback_data' => $PaySetting],
        ],
    ]
]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['zarinpalTitle'], $zarinpal_Status, 'HTML');
}
if ($datain == "offzarinpal"  ){
    update("PaySetting", "ValuePay","onzarinpal","NamePay","statuszarinpal");
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['zarinpalStatuson'], null);}
elseif ($datain == "onzarinpal"  ) {
     update("PaySetting", "ValuePay","offzarinpal","NamePay","statuszarinpal");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['zarrinpalStatusOff'], null);
}
if($text == "🔵 درگاه آقای پرداخت"  ){
    sendmessage($from_id, $textbotlang['users']['selectoption'], $aqayepardakht, 'HTML');
}
if($text == "تنظیم مرچنت آقای پرداخت"  ){
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht","select");
    $textaqayepardakht = "💳 مرچنت کد خود را ازآقای پرداخت دریافت و در این قسمت وارد کنید

مرچنت کد فعلی شما : {$PaySetting['ValuePay']}";
    sendmessage($from_id, $textaqayepardakht, $backadmin, 'HTML');
    step('merchant_id_aqayepardakht',$from_id);
}
elseif($user['step'] == "merchant_id_aqayepardakht"){
    sendmessage($from_id,$textbotlang['Admin']['SettingnowPayment']['Savaapi'] , $aqayepardakht,'HTML');
    update("PaySetting", "ValuePay",$text,"NamePay","merchant_id_aqayepardakht");
    step('home',$from_id);
}
if ($text == "وضعیت درگاه آقای پرداخت"  ) {
        $PaySetting = select("PaySetting", "ValuePay", "NamePay", "statusaqayepardakht","select")['ValuePay'];
    $aqayepardakht_Status = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $PaySetting, 'callback_data' => $PaySetting],
        ],
    ]
]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['aqayepardakhtTitle'], $aqayepardakht_Status, 'HTML');
}
if ($datain == "offaqayepardakht"  ){
    update("PaySetting", "ValuePay","onaqayepardakht","NamePay","statusaqayepardakht");
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['aqayepardakhtStatuson'], null);}
elseif ($datain == "onaqayepardakht"  ) {
    update("PaySetting", "ValuePay","offaqayepardakht","NamePay","statusaqayepardakht");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['aqayepardakhtStatusOff'], null);
}
if($text == "✏️ ویرایش پنل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getloc'], $json_list_marzban_panel, 'HTML');
    step('GetLocationEdit',$from_id);
}
elseif($user['step'] == "GetLocationEdit"){
    update("user", "Processing_value",$text,"id",$from_id);
    sendmessage($from_id, $textbotlang['users']['selectoption'], $optionMarzban, 'HTML');
    step('home',$from_id);
}
elseif($text == "✍️ نام پنل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['GetNameNew'], $backadmin, 'HTML');
    step('GetNameNew',$from_id);
}
elseif($user['step'] == "GetNameNew"){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedNmaePanel'], $optionMarzban, 'HTML');
    update("marzban_panel", "name_panel",$text,"name_panel",$user['Processing_value']);
    update("invoice", "Service_location",$text,"Service_location",$user['Processing_value']);
    update("product", "Location",$text,"Location",$user['Processing_value']);
    update("TestAccount", "Service_location",$text,"Service_location",$user['Processing_value']);
    update("user", "Processing_value",$text,"id",$from_id);
    step('home',$from_id);
}
elseif($text == "🔗 ویرایش آدرس پنل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['geturlnew'], $backadmin, 'HTML');
    step('GeturlNew',$from_id);
}
elseif($user['step'] == "GeturlNew"){
        if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedurlPanel'], $optionMarzban, 'HTML');
    update("marzban_panel", "url_panel",$text,"name_panel",$user['Processing_value']);
    step('home',$from_id);
}
elseif($text == "👤 ویرایش نام کاربری"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getusernamenew'], $backadmin, 'HTML');
    step('GetusernameNew',$from_id);
}
elseif($user['step'] == "GetusernameNew"){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedusernamePanel'], $optionMarzban, 'HTML');
    update("marzban_panel", "username_panel",$text,"name_panel",$user['Processing_value']);
    step('home',$from_id);
}
elseif($text == "🔐 ویرایش رمز عبور"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getpasswordnew'], $backadmin, 'HTML');
    step('GetpaawordNew',$from_id);
}
elseif($user['step'] == "GetpaawordNew"){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedpasswordPanel'], $optionMarzban, 'HTML');
    update("marzban_panel", "password_panel",$text,"name_panel",$user['Processing_value']);
    step('home',$from_id);
}
elseif($text == "⚙️ تنظیمات پروتکل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['settingprotocol'], $keyboardprotocol, 'HTML');
}
elseif($text == "vless" ){
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $vless = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['vless'], 'callback_data' => $marzbanprotocol['vless']],
        ],
    ]
]);

        sendmessage($from_id,$textbotlang['Admin']['managepanel']['staatusprotocol'], $vless, 'HTML');
}
elseif($datain == "onvless"){
    update("marzban_panel", "vless","offvless","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $vless = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['vless'], 'callback_data' => $marzbanprotocol['vless']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['onprotocol'], $vless);
}
elseif($datain == "offvless"){
    update("marzban_panel", "vless","onvless","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $vless = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['vless'], 'callback_data' => $marzbanprotocol['vless']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['offprotocol'], $vless);
}
elseif($text == "vmess" ){
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $vmess = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['vmess'], 'callback_data' => $marzbanprotocol['vmess']],
        ],
    ]
]);

        sendmessage($from_id,$textbotlang['Admin']['managepanel']['staatusprotocol'], $vmess, 'HTML');
}
elseif($datain == "onvmess"){
    update("marzban_panel", "vmess","offvmess","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $vmess = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['vmess'], 'callback_data' => $marzbanprotocol['vmess']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['onprotocol'], $vmess);
}
elseif($datain == "offvmess"){
    update("marzban_panel", "vmess","onvmess","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $vmess = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['vmess'], 'callback_data' => $marzbanprotocol['vmess']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['offprotocol'], $vmess);
}
elseif($text == "trojan" ){
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $trojan = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['trojan'], 'callback_data' => $marzbanprotocol['trojan']],
        ],
    ]
]);

        sendmessage($from_id,$textbotlang['Admin']['managepanel']['staatusprotocol'], $trojan, 'HTML');
}
elseif($datain == "ontrojan"){
    update("marzban_panel", "trojan","offtrojan","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $trojan = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['trojan'], 'callback_data' => $marzbanprotocol['trojan']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['onprotocol'], $trojan);
}
elseif($datain == "offtrojan"){
    update("marzban_panel", "trojan","ontrojan","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $trojan = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['trojan'], 'callback_data' => $marzbanprotocol['trojan']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['offprotocol'], $trojan);
}
elseif($text == "shadowsocks" ){
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $shadowsocks = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['shadowsocks'], 'callback_data' => $marzbanprotocol['shadowsocks']],
        ],
    ]
]);

        sendmessage($from_id,$textbotlang['Admin']['managepanel']['staatusprotocol'], $shadowsocks, 'HTML');
}
elseif($datain == "onshadowsocks"){
    update("marzban_panel", "shadowsocks","offshadowsocks","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $shadowsocks = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['shadowsocks'], 'callback_data' => $marzbanprotocol['shadowsocks']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['onprotocol'], $shadowsocks);
}
elseif($datain == "offshadowsocks"){
    update("marzban_panel", "shadowsocks","onshadowsocks","name_panel",$user['Processing_value']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'],"select");
    $shadowsocks = json_encode([
    'inline_keyboard' => [
        [
            ['text' => $marzbanprotocol['shadowsocks'], 'callback_data' => $marzbanprotocol['shadowsocks']],
        ],
    ]
]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['managepanel']['offprotocol'], $shadowsocks);
}
elseif($user['step'] == "GetpaawordNew"){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedpasswordPanel'], $optionMarzban, 'HTML');
    update("marzban_panel", "password_panel",$text,"name_panel",$user['Processing_value']);
    step('home',$from_id);
}
if($text == "❌ حذف پنل"  ) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['RemovedPanel'], $keyboardmarzban, 'HTML');
    $stmt = $pdo->prepare("DELETE FROM marzban_panel WHERE name_panel = ?");
    $stmt->bindParam(1, $user['Processing_value']);
    $stmt->execute();
}
if($text == "➕ تنظیم قیمت حجم اضافه"  ){
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['SetPrice'].$setting['Extra_volume'], $backadmin, 'HTML');
    step('GetPriceExtra',$from_id);
}
elseif($user['step'] == "GetPriceExtra"){
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    update("setting", "Extra_volume",$text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['ChangedPrice'], $shopkeyboard, 'HTML');
    step('home',$from_id);
}
#-------------------------#
if ($text == "👥 شارژ همگانی") {
    sendmessage($from_id, $textbotlang['Admin']['Balance']['addallbalance'], $backadmin, 'HTML');
    step('add_Balance_all',$from_id);
} elseif ($user['step'] == "add_Balance_all") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalanceUsers'], $User_Services, 'HTML');
    $Balance_user = select("user", "*",null ,null ,"fetchAll");
    foreach ($Balance_user as $balance) {
    $Balance_add_user = $balance['Balance'] + $text;
    update("user", "Balance",$Balance_add_user,"id",$balance['id']);
    }
    step('home',$from_id);
}
if ($text == "🔴 درگاه پرفکت مانی") {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $perfectmoneykeyboard, 'HTML');
} elseif ($text == "تنظیم شماره اکانت") {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "perfectmoney_AccountID","select")['ValuePay'];
    sendmessage($from_id, "⭕️ شماره اکانت پرفکت مانی خود را ارسال کنید
مثال : 93293828
شماره اکانت فعلی : $PaySetting", $backadmin, 'HTML');
    step('setnumberaccount',$from_id);
} elseif ($user['step'] == "setnumberaccount") {
    sendmessage($from_id, $textbotlang['Admin']['perfectmoney']['setnumberacount'], $perfectmoneykeyboard, 'HTML');
     update("PaySetting", "ValuePay",$text,"NamePay","perfectmoney_AccountID");
    step('home',$from_id);
}
if ($text == "تنظیم شماره کیف پول") {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "perfectmoney_Payer_Account","select")['ValuePay'];
    sendmessage($from_id, "⭕️ شماره کیف پولی که میخواهید ووچر پرفکت مانی به آن واریز شود را ارسال کنید 
مثال : u234082394
شماره کیف پول فعلی : $PaySetting", $backadmin, 'HTML');
    step('perfectmoney_Payer_Account',$from_id);
} elseif ($user['step'] == "perfectmoney_Payer_Account") {
    sendmessage($from_id, $textbotlang['Admin']['perfectmoney']['setnumberacount'], $perfectmoneykeyboard, 'HTML');
    update("PaySetting", "ValuePay",$text,"NamePay","perfectmoney_Payer_Account");
    step('home',$from_id);
}
if ($text == "تنظیم رمز اکانت") {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "perfectmoney_PassPhrase","select")['ValuePay'];
    sendmessage($from_id, "⭕️ رمز اکانت پرفکت مانی خود را ارسال کنید
رمز عبور فعلی : $PaySetting", $backadmin, 'HTML');
    step('perfectmoney_PassPhrase',$from_id);
} elseif ($user['step'] == "perfectmoney_PassPhrase") {
    sendmessage($from_id, $textbotlang['Admin']['perfectmoney']['setnumberacount'], $perfectmoneykeyboard, 'HTML');
    update("PaySetting", "ValuePay",$text,"NamePay","perfectmoney_PassPhrase");
    step('home',$from_id);
}
if ($text == "وضعیت پرفکت مانی") {
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "status_perfectmoney","select")['ValuePay'];
    $status_perfectmoney = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $PaySetting, 'callback_data' => $PaySetting],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['perfectmoneyTitle'], $status_perfectmoney, 'HTML');
}
if ($datain == "offperfectmoney") {
    update("PaySetting", "ValuePay","onperfectmoney","NamePay","status_perfectmoney");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['perfectmoneyStatuson'], null);
} elseif ($datain == "onperfectmoney") {
    update("PaySetting", "ValuePay","offperfectmoney","NamePay","status_perfectmoney");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['perfectmoneyStatusOff'], null);
}
if ($text == "🎁 ساخت کد تخفیف") {
    sendmessage($from_id, $textbotlang['Admin']['Discountsell']['GetCode'], $backadmin, 'HTML');
    step('get_codesell',$from_id);
}
elseif ($user['step'] == "get_codesell") {
    if (!preg_match('/^[A-Za-z]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['ErrorCode'], null, 'HTML');
        return;
    }
   $values = "0";
   $stmt = $pdo->prepare("INSERT INTO DiscountSell (codeDiscount, usedDiscount, price, limitDiscount) VALUES (?, ?, ?, ?)");
   $stmt->bindParam(1, $text);
   $stmt->bindParam(2, $values);
   $stmt->bindParam(3, $values);
   $stmt->bindParam(4, $values);
   $stmt->execute();

    sendmessage($from_id, $textbotlang['Admin']['Discount']['PriceCodesell'], null, 'HTML');
    step('get_price_codesell',$from_id);
    update("user", "Processing_value",$text,"id",$from_id);
}
elseif ($user['step'] == "get_price_codesell") {
    if (!preg_match('/^[\d\.]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    update("DiscountSell", "price",$text,"codeDiscount",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discountsell']['getlimit'], $backadmin, 'HTML');
    step('getlimitcode',$from_id);
}
elseif ($user['step'] == "getlimitcode") {
    update("DiscountSell", "limitDiscount",$text,"codeDiscount",$user['Processing_value']);
    sendmessage($from_id, "📌 کد تخفیف برای خرید اول باشد یا همه خرید ها
0 : همه خرید ها
1 : خرید اول ", $backadmin, 'HTML');
    step('getusefirst',$from_id);
}elseif ($user['step'] == "getusefirst") {
    update("DiscountSell", "usefirst",$text,"codeDiscount",$user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['SaveCode'], $keyboardadmin, 'HTML');
    step('home',$from_id);
}
if ($text == "❌ حذف کد تخفیف") {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemoveCode'], $json_list_Discount_list_admin_sell, 'HTML');
    step('remove-Discountsell',$from_id);
}
elseif ($user['step'] == "remove-Discountsell") {
    if (!in_array($text, $SellDiscount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['NotCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM DiscountSell WHERE codeDiscount = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemovedCode'], $shopkeyboard, 'HTML');
}
if ($text == "👥 تنظیمات زیر مجموعه گیری") {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $affiliates, 'HTML');
}
elseif ($text == "🎁 وضعیت زیرمجموعه گیری") {
    $affiliatesvalue = select("affiliates", "*", null, null,"select")['affiliatesstatus'];
    $keyboardaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $affiliatesvalue, 'callback_data' => $affiliatesvalue],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['affiliates'], $keyboardaffiliates, 'HTML');
}
elseif ($datain == "onaffiliates") {
    update("affiliates", "affiliatesstatus", "offaffiliates");
    $affiliatesvalue = select("affiliates", "*", null, null,"select")['affiliatesstatus'];
    $keyboardaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $affiliatesvalue, 'callback_data' => $affiliatesvalue],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['affiliatesStatusOff'], $keyboardaffiliates);
}
elseif ($datain == "offaffiliates") {
    update("affiliates", "affiliatesstatus", "onaffiliates");
    $affiliatesvalue = select("affiliates", "*", null, null,"select")['affiliatesstatus'];
    $keyboardaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $affiliatesvalue, 'callback_data' => $affiliatesvalue],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['affiliatesStatuson'], $keyboardaffiliates);
}
if ($text == "🧮 تنظیم درصد زیرمجموعه") {
    sendmessage($from_id, $textbotlang['users']['affiliates']['setpercentage'], $backadmin, 'HTML');
    step('setpercentage',$from_id);
}
elseif ($user['step'] == "setpercentage") {
    sendmessage($from_id, $textbotlang['users']['affiliates']['changedpercentage'], $affiliates, 'HTML');
    update("affiliates", "affiliatespercentage", $text);
    step('home',$from_id);
}
elseif ($text == "🏞 تنظیم بنر زیرمجموعه گیری") {
    sendmessage($from_id, $textbotlang['users']['affiliates']['banner'], $backadmin, 'HTML');
    step('setbanner',$from_id);
}
elseif ($user['step'] == "setbanner") {
    if (!$photo) {
        sendmessage($from_id, $textbotlang['users']['affiliates']['invalidbanner'], $backadmin, 'HTML');
        return;
    }
    update("affiliates", "description", $caption);
    update("affiliates", "id_media", $photoid);
    sendmessage($from_id, $textbotlang['users']['affiliates']['insertbanner'], $affiliates, 'HTML');
   step('home',$from_id);
}
elseif ($text == "🎁 پورسانت بعد از خرید") {
    $marzbancommission = select("affiliates", "*", null, null,"select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['commission'], $keyboardcommission, 'HTML');
}
elseif ($datain == "oncommission") {
    update("affiliates", "status_commission", "offcommission");
    $marzbancommission = select("affiliates", "*", null, null,"select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['commissionStatusOff'], $keyboardcommission);
}
elseif ($datain == "offcommission") {
    update("affiliates", "status_commission", "oncommission");
    $marzbancommission = select("affiliates", "*", null, null,"select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['commissionStatuson'], $keyboardcommission);
}
elseif ($text == "🎁 دریافت هدیه") {
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null,"select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['Discountaffiliates'], $keyboardDiscountaffiliates, 'HTML');
}
elseif ($datain == "onDiscountaffiliates") {
    update("affiliates", "Discount", "offDiscountaffiliates");
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null,"select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['DiscountaffiliatesStatusOff'], $keyboardDiscountaffiliates);
}
elseif ($datain == "offDiscountaffiliates") {
    update("affiliates", "Discount", "onDiscountaffiliates");
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null,"select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['DiscountaffiliatesStatuson'], $keyboardDiscountaffiliates);
}
if ($text == "🌟 مبلغ هدیه استارت") {
    sendmessage($from_id, $textbotlang['users']['affiliates']['priceDiscount'], $backadmin, 'HTML');
    step('getdiscont',$from_id);
}
elseif ($user['step'] == "getdiscont") {
    sendmessage($from_id, $textbotlang['users']['affiliates']['changedpriceDiscount'], $affiliates, 'HTML');
    update("affiliates", "price_Discount", $text);
    step('home',$from_id);
}


elseif (preg_match('/rejectremoceserviceadmin-(\w+)/', $datain, $dataget)) {
    $usernamepanel = $dataget[1];
    $requestcheck = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM cancel_service WHERE username = '$usernamepanel' LIMIT 1"));
    if ($requestcheck['status'] == "accept" || $requestcheck['status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    step("descriptionsrequsts",$from_id);
    update("user","Processing_value",$usernamepanel, "id",$from_id);
    sendmessage($from_id, "📌 درخواست رد کردن حذف با موفقیت ثبت شد دلیل عدم تایید را ارسال کنید", $backuser, 'HTML');

}
elseif($user['step'] == "descriptionsrequsts"){
    sendmessage($from_id, "✅ با موفقیت ثبت گردید", $keyboardadmin, 'HTML');
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '{$user['Processing_value']}'"));
    update("cancel_service","status","reject", "username",$user['Processing_value']);
    update("cancel_service","description",$text, "username",$user['Processing_value']);
    step("home",$from_id);
    sendmessage($nameloc['id_user'], "❌ کاربری گرامی درخواست حذف شما با نام کاربری  {$user['Processing_value']} موافقت نگردید.
        
        دلیل عدم تایید : $text", null, 'HTML');

}
elseif (preg_match('/remoceserviceadmin-(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $requestcheck = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM cancel_service WHERE username = '$username' LIMIT 1"));
    if ($requestcheck['status'] == "accept" || $requestcheck['status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    step("getpricerequests",$from_id);
    update("user","Processing_value",$username, "id",$from_id);
    sendmessage($from_id, "💰 مقدار مبلغی که میخواهید به موجودی کاربر اضافه شود را ارسال کنید.", $backuser, 'HTML');

}
elseif($user['step'] == "getpricerequests"){
    if (!ctype_digit($text)) {
        sendmessage($from_id,"⭕️ ورودی نا معتبر", null, 'HTML');
    }
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '{$user['Processing_value']}'"));
    if($nameloc['price_product'] < $text){
        sendmessage($from_id, "❌ مبلغ بازگشتی بزرگ تر از مبلغ محصول است!", $backuser, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ با موفقیت ثبت گردید", $keyboardadmin, 'HTML');
    step("home",$from_id);
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $get_username_Check = getuser($user['Processing_value'],$marzban_list_get['name_panel']);
    if(isset($get_username_Check['status'])){
        removeuser($marzban_list_get['name_panel'], $user['Processing_value']);
    }
    update("cancel_service","status","accept", "username",$user['Processing_value']);
    update("invoice","status","removedbyadmin", "username",$user['Processing_value']);
    step("home",$from_id);
    sendmessage($nameloc['id_user'],"✅ کاربری گرامی درخواست حذف شما با نام کاربری  {$user['Processing_value']} موافقت گردید.", null, 'HTML');
    $pricecancel = number_format(intval($text), 2);
    if(intval($text) != 0){
        $Balance_id_cancel = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$nameloc['id_user']}' LIMIT 1"));
        $Balance_id_cancel_fee = intval($Balance_id_cancel['Balance']) + intval($text);
        update("user","Balance",$Balance_id_cancel_fee, "id",$nameloc['id_user']);
        sendmessage($nameloc['id_user'],"💰کاربر گرامی مبلغ $pricecancel تومان به موجودی شما اضافه گردید.", null, 'HTML');
    }
    $text_report = "⭕️ یک ادمین سرویس کاربر که درخواست حذف داشت را تایید کرد
        
        اطلاعات کاربر تایید کننده  : 
        
        🪪 آیدی عددی : <code>$from_id</code>
        💰 مبلغ بازگشتی : $pricecancel تومان
        👤 نام کاربری : $username
        آیدی عددی درخواست کننده کنسل کردن : {$nameloc['id_user']}";
    if (strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
}

$connect->close();
