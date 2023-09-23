<?php
date_default_timezone_set('Asia/Tehran');
require_once 'config.php';
require_once 'botapi.php';
require_once 'apipanel.php';
require_once 'jdf.php';
require_once 'keyboard.php';
require_once 'text.php';
require_once __DIR__ . '/vendor/phpqrcode/qrlib.php';
#-----------telegram_ip_ranges------------#
$telegram_ip_ranges = [
    ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
    ['lower' => '91.108.4.0',    'upper' => '91.108.7.255']
];
$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok = false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
    $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
    $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
    if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok = true;
}
if (!$ok) die("دسترسی غیرمجاز");
#-----------function------------#

function generateUUID() {
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); 

    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    return $uuid;
}
function tronratee(){
    return json_decode(file_get_contents('https://api.changeto.technology/api/rate'), true);
}
function nowPayments($payment, $price_amount, $order_id, $order_description){
    global $connect;
    $apinowpayments = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'apinowpayment'"))['ValuePay'];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/' . $payment,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT_MS => 4500,
        CURLOPT_ENCODING => '',
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array(
            'x-api-key:' . $apinowpayments,
            'Content-Type: application/json'
        ),
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
        'price_amount' => $price_amount,
        'price_currency' => 'usd',
        'pay_currency' => 'trx',
        'order_id' => $order_id,
        'order_description' => $order_description,
    ]));

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response);
}
function StatusPayment($paymentid){
    global $connect;
    $apinowpayments = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'apinowpayment'"))['ValuePay'];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/' . $paymentid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'x-api-key:' . $apinowpayments
        ),
    ));
    $response = curl_exec($curl);
    $response = json_decode($response, true);
    curl_close($curl);
    return $response;
}
#-------------Variable----------#
$version = file_get_contents('install/version');
$query = mysqli_query($connect, "SELECT * FROM user WHERE id = '$from_id' LIMIT 1");
if (mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);
} else {
    $user = array();
    $user = array(
        'step' => '',
        'Processing_value' => '',
        'User_Status' => '',
        'username' => '',
        'limit_usertest' =>'',
    );
}
$Processing_value =  $user['Processing_value'];
$setting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
$helpdata = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM help"));
$datatextbotget = mysqli_query($connect, "SELECT * FROM textbot");
$channels = array();
$id_invoice = array_column(mysqli_fetch_all(mysqli_query($connect, "SELECT (id_invoice) FROM invoice"), MYSQLI_ASSOC), 'id_invoice');
$channels = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM channels  LIMIT 1"));
$admin_ids = array_column(mysqli_fetch_all(mysqli_query($connect, "SELECT (id_admin) FROM admin"), MYSQLI_ASSOC), 'id_admin');
$usernameinvoice = array_column(mysqli_fetch_all(mysqli_query($connect, "SELECT (username) FROM invoice"), MYSQLI_ASSOC), 'username');
$code_Discount = array_column(mysqli_fetch_all(mysqli_query($connect, "SELECT (code) FROM Discount"), MYSQLI_ASSOC), 'code');
$users_ids = array_column(mysqli_fetch_all(mysqli_query($connect, "SELECT (id) FROM user"), MYSQLI_ASSOC), 'id');
$marzban_list = array_column(mysqli_fetch_all(mysqli_query($connect, "SELECT (name_panel) FROM marzban_panel"), MYSQLI_ASSOC), 'name_panel');
$name_product = array_column(mysqli_fetch_all(mysqli_query($connect, "SELECT (name_product) FROM product"), MYSQLI_ASSOC), 'name_product');
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
    'text_dec_info' => '',
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
if($from_id != 0)$connect->query("INSERT IGNORE INTO user (id , step,limit_usertest,User_Status,number,Balance,pagenumber,username,message_count,last_message_time) VALUES ('$from_id', 'none','{$setting['limit_usertest_all']}','Active','none','0','1','$username','0','0')");
if($user['username'] == "none" || $user['username'] == null){
    $stmt = $connect->prepare("UPDATE user SET username = ? WHERE id = ?");
    $stmt->bind_param("ss", $username, $from_id);
    $stmt->execute();
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
$timebot= time();
$TimeLastMessage=  $timebot - intval($user['last_message_time']);
if(floor($TimeLastMessage / 60) >= 1){
    $stmt = $connect->prepare("UPDATE user SET last_message_time = ? WHERE id = ?");
    $stmt->bind_param("ss", $timebot, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET message_count = ? WHERE id = ?");
    $addmessage = 1;
    $stmt->bind_param("is", $addmessage, $from_id);
    $stmt->execute();
}else{
if(!in_array($from_id,$admin_ids)){
$stmt = $connect->prepare("UPDATE user SET message_count = ? WHERE id = ?");
$addmessage = intval($user['message_count']) + 1;
$stmt->bind_param("ss", $addmessage, $from_id);
$stmt->execute();
if($user['message_count'] >= "35"){
    $stmt = $connect->prepare("UPDATE user SET User_Status = ? ,description_blocking = ? WHERE id = ?");
    $User_Status = "block";
    $stmt->bind_param("sss", $User_Status,$textbotlang['users']['spam']['spamed'], $from_id);
    $stmt->execute();
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
if (empty($channels['Channel_lock'])) {
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
    $stmt = $connect->prepare("UPDATE user SET roll_Status = ? WHERE id = ?");
    $confrim = true;
    $stmt->bind_param("ss", $confrim, $from_id);
    $stmt->execute();
}

#-----------Bot_Status------------#
if ($setting['Bot_Status'] == "❌ ربات خاموش است" && !in_array($from_id, $admin_ids)) {
    sendmessage($from_id, $datatextbot['text_bot_off'], null, 'html');
    return;
}
#-----------/start------------#
if ($text == "/start") {
    sendmessage($from_id, $datatextbot['text_start'], $keyboard, 'html');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
}
#-----------back------------#
if ($text == "🏠 بازگشت به منوی اصلی" || $datain == "backuser") {
    if($datain == "backuser")deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['users']['back'], $keyboard, 'html');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE user SET number = ? WHERE id = ?");
    $stmt->bind_param("ss", $user_phone, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "home";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}

#-----------Purchased services------------#
if ($text == $datatextbot['text_Purchased_services'] || $datain == "backorder") {
    $invoices = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$from_id'"));
    if (is_null($invoices) && $setting['NotUser'] == "offnotuser") {
        sendmessage($from_id, $textbotlang['users']['sell']['service_not_available'], null, 'html');
        return;
    }

    $stmt = $connect->prepare("UPDATE user SET pagenumber = ? WHERE id = ?");
    $pages = 1;
    $stmt->bind_param("ss", $pages, $from_id);
    $stmt->execute();
    $page = 1;
    $items_per_page = 5;
    $start_index = ($page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$from_id' ORDER BY username ASC LIMIT $start_index, $items_per_page");
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = mysqli_fetch_assoc($result)) {
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getusernameinfo';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "getusernameinfo") {
    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['users']['stateus']['Invalidusername'], $backuser, 'html');
        return;
    }

    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_panel_user, 'html');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getdata';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif (preg_match('/locationnotuser_(.*)/', $datain, $dataget)) {
    $location = $dataget[1];
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$location'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($Processing_value, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if ($data_useer['detail'] == "User not found") {
        sendmessage($from_id, $textbotlang['users']['stateus']['notUsernameget'], $keyboard, 'html');
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($datain == 'next_page') {
    $numpage =  mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id_user) FROM invoice WHERE id_user = '$from_id'"));
    $page = $user['pagenumber'];
    $items_per_page  = 5;
    $sum = $user['pagenumber'] * $items_per_page;
    if ($sum > $numpage['COUNT(id_user)']) {
        $next_page = 1;
    } else {
        $next_page = $page + 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$from_id'  LIMIT $start_index, $items_per_page");
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = mysqli_fetch_assoc($result)) {
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
    $stmt = $connect->prepare("UPDATE user SET pagenumber = ? WHERE id = ?");
    $stmt->bind_param("ss", $next_page, $from_id);
    $stmt->execute();
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
    $result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$from_id'  LIMIT $start_index, $items_per_page");
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    while ($row = mysqli_fetch_assoc($result)) {
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
    $stmt = $connect->prepare("UPDATE user SET pagenumber = ? WHERE id = ?");
    $stmt->bind_param("ss", $next_page, $from_id);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $text, $keyboard_json);
}
if (preg_match('/product_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($username, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if ($data_useer['detail'] == "User not found" || !isset($data_useer['status'])) {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], $keyboard, 'html');
        return;
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
                ['text' => $textbotlang['users']['qrcode']['manageservice']['getqrcodelink'], 'callback_data' => 'qrcodelink_'.$username],
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

📥 حجم مصرفی : $usedTrafficGb
♾ حجم سرویس : $LastTraffic

📅 فعال تا تاریخ : $expirationDate ($day)

🚫 برای تغییر لینک و قطع دسترسی دیگران کافیست روی گزینه ' بروزرسانی اشتراک ' کلیک کنید.";
    Editmessagetext($from_id, $message_id, $textinfo, $keyboardsetting);
}
if (preg_match('/subscriptionurl_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($username, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $subscriptionurl = $data_useer['subscription_url'];
    if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $subscriptionurl)) {
        $subscriptionurl = $marzban_list_get['url_panel'] . "/" . ltrim($subscriptionurl, "/");
    }
    $textsub = "
    {$textbotlang['users']['stateus']['linksub']}
    
    <code>$subscriptionurl</code>";
    sendmessage($from_id, $textsub, null, 'html');
}
elseif (preg_match('/config_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($username, $Check_token['access_token'], $marzban_list_get['url_panel']);
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
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $prodcut = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE name_product = '{$nameloc['name_product']}'"));
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'confirmextend';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif (preg_match('/confirmserivce_(\w+)/', $datain, $dataget) && $user['step'] == "confirmextend") {
    $usernamepanel = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$usernamepanel'"));
    $prodcut = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE name_product = '{$nameloc['name_product']}'"));
        if($user['Balance'] < $prodcut['price_product']){
    $Balance_prim = $prodcut['price_product'] - $user['Balance'];
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_prim, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_step_payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
        return;
        }
    $Balance_Low_user = $user['Balance'] - $prodcut['price_product'];
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_Low_user, $from_id);
    $stmt->execute();
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($usernamepanel, $Check_token['access_token'], $marzban_list_get['url_panel']);
    ResetUserDataUsage($usernamepanel, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $date = strtotime("+" . $prodcut['Service_time'] . "day");
    $newDate = strtotime(date("Y-m-d H:i:s", $date));
    $data_limit = $prodcut['Volume_constraint'] * pow(1024, 3);
        $datam = array(
        "expire" => $newDate,
        "data_limit" => $data_limit
        );
    $Modifyuser =Modifyuser($Check_token['access_token'],$marzban_list_get['url_panel'],$usernamepanel,$datam);
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif (preg_match('/changelink_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
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
    $username = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $Allowedusername = getuser($username_ac, $Check_token['access_token'], $marzban_list_get['url_panel']);
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
    Modifyuser($Check_token['access_token'], $marzban_list_get['url_panel'],$username,$datam);
    Editmessagetext($from_id, $message_id,  $textbotlang['users']['changelink']['confirmed'], null);

}
elseif (preg_match('/qrcodelink_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$username'"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($username, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $subscriptionurl = $data_useer['subscription_url'];
    if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $subscriptionurl)) {
        $subscriptionurl = $marzban_list_get['url_panel'] . "/" . ltrim($subscriptionurl, "/");
    }
    $randomString = bin2hex(random_bytes(3));
    $urlimage = "$from_id$randomString.png";
    QRcode::png($subscriptionurl,$urlimage, QR_ECLEVEL_L, 7);
    sendphoto($from_id, "https://$domainhosts/$urlimage", $textbotlang['users']['qrcode']['manageservice']['getqrcodelink']);
    unlink($urlimage);
}
elseif (preg_match('/Extra_volume_(\w+)/', $datain, $dataget)) {
    $username = $dataget[1];
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $username, $from_id);
    $stmt->execute();
    $textextra = " ⭕️ مقدار حجمی که میخواهید خریداری کنید را ارسال کنید.

⚠️ هر گیگ حجم اضافه  {$setting['Extra_volume']} است.";
    sendmessage($from_id, $textextra, $backuser, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getvolumeextra';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "getvolumeextra"){
        if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    if($text<1){
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
    $priceextra = number_format($priceextra);
    $setting['Extra_volume'] = number_format($setting['Extra_volume']);
    $textextra = "📇 فاکتور خرید حجم اضافه برای شما ایجاد شد.

💰 قیمت هر گیگابایت حجم اضافه :  {$setting['Extra_volume']} تومان
📝 مبلغ  فاکتور شما :  $priceextra تومان
📥 حجم اضافه درخواستی : $text  گیگابایت

✅ جهت پرداخت و اضافه شدن حجم، روی دکمه زیر کلیک کنید.";
    sendmessage($from_id,$textextra, $keyboardsetting, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif (preg_match('/confirmaextra_(\w+)/', $datain, $dataget)) {
    $volume = $dataget[1];
    sendmessage($from_id, $volume, null, 'HTML');
    $nameloc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '$Processing_value'"));
        if($user['Balance'] <$volume){
    $Balance_prim = $volume - $user['Balance'];
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_prim, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_step_payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
        return;
        }
    deletemessage($from_id, $message_id);
    $Balance_Low_user = $user['Balance'] - $volume;
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_Low_user, $from_id);
    $stmt->execute();
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$nameloc['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $data_useer = getuser($Processing_value, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $data_limit = $data_useer['data_limit'] + ($volume/$setting['Extra_volume'] *  pow(1024, 3));
    $datam = array(
        "data_limit" => $data_limit
        );
     Modifyuser($Check_token['access_token'],$marzban_list_get['url_panel'],$Processing_value,$datam);
            $keyboardextrafnished = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backservice'], 'callback_data' => "product_" . $Processing_value],
]
        ]
    ]);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['extraadded'], $keyboardextrafnished, 'HTML');
    $volumes  =  $volume/$setting['Extra_volume'];
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

#-----------usertest------------#
if ($text == $datatextbot['text_usertest']) {
    $locationproduct = mysqli_query($connect, "SELECT * FROM marzban_panel");
    if (mysqli_num_rows($locationproduct) == 0) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
    return;
}
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'get_number';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE user SET Processing_value_tow = ? WHERE id = ?");
    $stmt->bind_param("ss", $location, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['selectusername'], $backuser, 'html');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'createusertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "createusertest" || preg_match('/locationtests_(.*)/', $datain, $dataget)) {
        if ($user['limit_usertest'] <= 0) {
        sendmessage($from_id, $textbotlang['users']['usertest']['limitwarning'], $keyboard, 'html');
        return;
    }
    $location = $dataget[1];
        if($setting['MethodUsername'] == "نام کاربری دلخواه" && $user['step'] == "createusertest"){
            if (!preg_match('~^[a-z][a-z\d_]{2,32}$~i', $text)) {
        sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser,'HTML');
        return;
    }
    $name_panel = $user['Processing_value_tow'];
        }else{
    $name_panel =$location ;
        }
    $randomString = bin2hex(random_bytes(2));
    $username_ac = generateUsername($from_id, $setting['MethodUsername'], $user['username'], $randomString,$text);
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$name_panel'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $Allowedusername = getuser($username_ac, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if (isset($Allowedusername['username'])) {
        $random_number = rand(1000000, 9999999);
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
    $config_test = adduser($username_ac, $expire, $data_limit, $Check_token['access_token'], $marzban_list_get['url_panel'], $nameprotocol);
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
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        return;
    }
    $date = jdate('Y/m/d');
    $randomString = bin2hex(random_bytes(2));
    $stmt = $connect->prepare("INSERT IGNORE INTO TestAccount (id_user, id_invoice, username,Service_location,time_sell) VALUES (?, ?, ?, ?,?)");
    $stmt->bind_param("sssss", $from_id, $randomString, $username_ac, $name_panel, $date);
    $stmt->execute();
    $stmt->close();
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
    QRcode::png($output_config_link,$urlimage, QR_ECLEVEL_L, 7);
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $limit_usertest = $user['limit_usertest'] - 1;
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ? WHERE id = ?");
    $stmt->bind_param("ss", $limit_usertest, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendhelp';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "sendhelp") {
    $helpdata = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM help WHERE name_os = '$text'"));
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextpm';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-----------fq------------#
if ($datain == "fqQuestions") {
    sendmessage($from_id, $datatextbot['text_dec_fq'], null, 'HTML');
}
$dateacc = jdate('Y/m/d');
$current_time = time();
    $one_hour_later = strtotime('-1 hour', $current_time); 
    $timeacc = jdate('H:i:s', $one_hour_later); if ($text == $datatextbot['text_account']) {
    $first_name = htmlspecialchars($first_name);
    $Balanceuser = number_format($user['Balance'], 0);
    $countorder =  mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id_user) FROM invoice WHERE id_user = '$from_id'"));
    $text_account = "
👨🏻‍💻 وضعیت حساب کاربری شما:
        
👤 نام: $first_name
🕴🏻 شناسه کاربری: <code>$from_id</code>
💰 موجودی: $Balanceuser تومان
🛍 تعداد سرویس های خریداری شده : {$countorder['COUNT(id_user)']}

📆 $dateacc → ⏰ $timeacc
            ";
    sendmessage($from_id, $text_account, $keyboardPanel, 'HTML');
}
if ($text == $datatextbot['text_sell']) {
$locationproduct = mysqli_query($connect, "SELECT * FROM marzban_panel");
if (mysqli_num_rows($locationproduct) == 0) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
    return;
}
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'get_number';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
    }
    if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است") return;
    #-----------------------#
    sendmessage($from_id, $textbotlang['users']['Service']['Location'], $list_marzban_panel_user, 'HTML');
} 
elseif (preg_match('/^location_(.*)/', $datain, $dataget)) {
    $location = $dataget[1];
    $nullproduct = mysqli_query($connect, "SELECT * FROM product");
    if (mysqli_num_rows($nullproduct) == 0) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['nullpProduct'], null, 'HTML');
        return;
}
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $location, $from_id);
    $stmt->execute();
        $getdataproduct = mysqli_query($connect, "SELECT * FROM product WHERE Location = '$location' OR Location = '/all'");
   $product = ['inline_keyboard' => []];
while ($result = mysqli_fetch_assoc($getdataproduct)) {
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
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $prodcut, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['selectusername'], $backuser, 'html');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'endstepuser';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "endstepuser" ||preg_match('/prodcutservice_(.*)/', $datain, $dataget)) {
        $prodcut = $dataget[1];
        if($setting['MethodUsername'] == "نام کاربری دلخواه"){
            if (!preg_match('~^[a-z][a-z\d_]{2,32}$~i', $text)) {
        sendmessage($from_id, $textbotlang['users']['invalidusername'], $backuser,'HTML');
        return;
            }
        $loc = $user['Processing_value_one'];
        }else{
    deletemessage($from_id, $message_id);
    $loc = $prodcut;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $loc, $from_id);
    $stmt->execute();
    $info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE code_product = '$loc' AND (Location = '$Processing_value'or Location = '/all') LIMIT 1"));
    $randomString = bin2hex(random_bytes(2));
    $username_ac = generateUsername($from_id, $setting['MethodUsername'], $username, $randomString,$text);
    $stmt = $connect->prepare("UPDATE user SET Processing_value_tow = ? WHERE id = ?");
    $stmt->bind_param("ss", $username_ac, $from_id);
    $stmt->execute();
    if($info_product['Volume_constraint'] == 0 )$info_product['Volume_constraint'] = $textbotlang['users']['stateus']['Unlimited'];
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} 
elseif ($user['step'] == "payment" && $datain == "confirmandgetservice"){
    $info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE code_product = '{$user['Processing_value_one']}' AND (Location = '$Processing_value'  or Location = '/all') LIMIT 1"));
    if (empty($info_product['price_product']) || empty($info_product['price_product'])) return;
        $priceproduct =  $info_product['price_product'];
    if ($priceproduct > $user['Balance']) {
    $Balance_prim = $info_product['price_product'] - $user['Balance'];
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_prim, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['sell']['None-credit'], $step_payment, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_step_payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
        return;
    }
    $username_ac = $user['Processing_value_tow'];
    $date = jdate('Y/m/d');
    $randomString = bin2hex(random_bytes(2));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $get_username_Check = getuser($username_ac, $Check_token['access_token'], $marzban_list_get['url_panel']);
    $random_number = rand(1000000, 9999999);
    if (isset($get_username_Check['username']) || in_array($username_ac, $usernameinvoice)) {
        $username_ac = $random_number . $username_ac;
    }
        if(in_array($randomString,$id_invoice)){
        $randomString = $random_number.$randomString;
    }
    $stmt = $connect->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username,time_sell, Service_location, name_product, price_product, Volume, Service_time,Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)");
    $Status =  "active";
    $stmt->bind_param("ssssssssss", $from_id, $randomString, $username_ac, $date, $Processing_value, $info_product['name_product'], $info_product['price_product'], $info_product['Volume_constraint'], $info_product['Service_time'],$Status);
    $stmt->execute();
    $stmt->close();
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
    $configuser = adduser($username_ac, $timestamp, $data_limit, $Check_token['access_token'], $marzban_list_get['url_panel'], $nameprotocol);
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
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'home';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        return;
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
        $link_config = "            
   {$textbotlang['users']['stateus']['getlinksub']}
        <code>$output_config_link</code>";
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
                ['text' => $info_product['Service_time'] . " روز", 'callback_data' => "Service_time"],
                ['text' => $textbotlang['users']['time-Service'], 'callback_data' => "Service_time"],
            ],
            [
                ['text' => $info_product['Volume_constraint'] . " گیگابایت", 'callback_data' => "Volume_constraint"],
                ['text' => $textbotlang['users']['Volume-Service'], 'callback_data' => "Volume_constraint"],
            ]
        ]
    ]);
    $textcreatuser = "
👤 نام کاربری شما : <code>$username_ac</code>
🔑 اشتراک شما با موفقیت ساخته شد.
    
$text_config
$link_config";
if ($setting['sublink'] == "✅ لینک اشتراک فعال است.") {
$urlimage = "$from_id$randomString.png";
    QRcode::png($output_config_link,$urlimage, QR_ECLEVEL_L, 7);
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
        $priceproduct =  $info_product['price_product'];
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_prim = $user['Balance'] - $priceproduct;
    $stmt->bind_param("ss", $Balance_prim, $from_id);
    $stmt->execute();
    $user['Balance'] = number_format($user['Balance'],0);
    $text_report = " 🛍 خرید جدید
        
⚙️ یک کاربر اکانت  با نام کانفیگ <code>$username_ac</code> خریداری کرد

قیمت محصول : {$info_product['price_product']} تومان
حجم محصول : {$info_product['Volume_constraint']} 
آیدی عددی کاربر : <code>$from_id</code>
شماره تلفن کاربر : {$user['number']}
موقعیت سرویس کاربر :$Processing_value
موجودی کاربر : {$user['Balance']} تومان

    اطلاعات کاربر 👇👇
    ⚜️ نام کاربری کاربر: @$username";
    if (strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}



#-------------------[ text_Add_Balance ]---------------------#
if ($text == $datatextbot['text_Add_Balance']) {
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است" && $user['step'] != "get_number" && $user['number'] == "none") {
        sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'get_number';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
    }
    if ($user['number'] == "none" && $setting['get_number'] == "✅ تایید شماره موبایل روشن است") return;
    sendmessage($from_id, $textbotlang['users']['Balance']['priceinput'], $backuser, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getprice';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "getprice") {
    if(!is_numeric($text)) return sendmessage($from_id, $textbotlang['users']['Balance']['errorprice'], null, 'HTML');
    if ($text > 10000000 or $text < 20000) return sendmessage($from_id, $textbotlang['users']['Balance']['errorpricelimit'],  null, 'HTML');
      
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['Balance']['selectPatment'], $step_payment, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_step_payment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "get_step_payment") {
    if ($datain == "cart_to_offline") {
$PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'CartDescription'"))['ValuePay'];
$Processing_value = number_format($Processing_value);
$textcart = "برای افزایش موجودی به صورت دستی، مبلغ $Processing_value  تومان  را به شماره‌ی حساب زیر واریز کنید 👇🏻

==================== 
$PaySetting
====================

🌅 عکس رسید خود را در این مرحله ارسال نمایید. 

⚠️ حداکثر واریز مبلغ 10 میلیون تومان می باشد.
⚠️ امکان برداشت وجه از کیف پول  نیست.
⚠️ مسئولیت واریز اشتباهی با شماست.";
        sendmessage($from_id,$textcart, $backuser, 'HTML');
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'cart_to_cart_user';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
    }
    if ($datain == "zarinpal") {
        if ($Processing_value < 5000) {
            sendmessage($from_id, $textbotlang['users']['Balance']['zarinpal'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method) VALUES (?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "zarinpal";
        $stmt->bind_param("ssssss", $from_id, $randomString, $dateacc, $Processing_value, $payment_Status,$Payment_Method);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/zarinpal/zarinpal.php?price=$Processing_value&order_id=$randomString"],
                ]
            ]
        ]);
        $Processing_value = number_format($Processing_value, 0);
        $textnowpayments = "
        ✅ فاکتور پرداخت ایجاد شد.
    
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : $Processing_value تومان

جهت پرداخت از دکمه زیر استفاده کنید👇🏻";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }
    if ($datain == "aqayepardakht") {
        if ($Processing_value < 5000) {
            sendmessage($from_id, $textbotlang['users']['Balance']['zarinpal'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method) VALUES (?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "aqayepardakht";
        $stmt->bind_param("ssssss", $from_id, $randomString, $dateacc, $Processing_value, $payment_Status,$Payment_Method);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/aqayepardakht/aqayepardakht.php?price=$Processing_value&order_id=$randomString"],
                ]
            ]
        ]);
        $Processing_value = number_format($Processing_value, 0);
        $textnowpayments = "
        ✅ فاکتور پرداخت ایجاد شد.
    
🔢 شماره فاکتور : $randomString
💰 مبلغ فاکتور : $Processing_value تومان

جهت پرداخت از دکمه زیر استفاده کنید👇🏻";
        sendmessage($from_id, $textnowpayments, $paymentkeyboard, 'HTML');
    }

    if ($datain == "nowpayments") {
        $price_rate = tronratee();
        $USD = $price_rate['result']['USD'];
        $usdprice = round($Processing_value / $USD, 2);
        if ($usdprice < 1) {
            sendmessage($from_id, $textbotlang['users']['Balance']['nowpayments'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method) VALUES (?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "Nowpayments";
        $stmt->bind_param("ssssss", $from_id, $randomString, $dateacc, $Processing_value, $payment_Status,$Payment_Method);
        $stmt->execute();
        $paymentkeyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['payments'], 'url' => "https://" . "$domainhosts" . "/payment/nowpayments/nowpayments.php?price=$usdprice&order_description=Add_Balance&order_id=$randomString"],
                ]
            ]
        ]);
        $Processing_value = number_format($Processing_value, 0);
        $USD = number_format($USD, 0);
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
    if ($datain == "iranpay") {
        $price_rate = tronratee();
        $trx = $price_rate['result']['TRX'];
        $usd = $price_rate['result']['USD'];
        $trxprice = round($Processing_value / $trx, 2);
        $usdprice = round($Processing_value / $usd, 2);
        if ($trxprice <= 1) {
            sendmessage($from_id, $textbotlang['users']['Balance']['changeto'], null, 'HTML');
            return;
        }
        sendmessage($from_id, $textbotlang['users']['Balance']['linkpayments'], $keyboard, 'HTML');
        $dateacc = date('Y/m/d h:i:s');
        $randomString = bin2hex(random_bytes(5));
        $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method) VALUES (?,?,?,?,?,?)");
        $payment_Status = "Unpaid";
        $Payment_Method = "Currency Rial gateway";
        $stmt->bind_param("ssssss", $from_id, $randomString, $dateacc, $Processing_value, $payment_Status,$Payment_Method);
        $stmt->execute();
        $order_description = "weswap_" . $randomString . "_" . $trxprice;
        $pay = nowPayments('payment', $usdprice, $randomString, $order_description);
        if (!isset($pay->pay_address)) {
            $text_error = $pay->message;
            sendmessage($from_id, $textbotlang['users']['Balance']['errorLinkPayment'], $keyboard, 'HTML');
            $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
            $step = 'home';
            $stmt->bind_param("ss", $step, $from_id);
            $stmt->execute();
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
        $pricetoman = number_format($Processing_value, 0);
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
}
if (preg_match('/Confirmpay_user_(\w+)_(\w+)/', $datain, $dataget)) {
    $id_payment = $dataget[1];
    $id_order = $dataget[2];
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$id_order' LIMIT 1"));
    if ($Payment_report['payment_Status'] == "paid") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['Confirmpayadmin'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $StatusPayment = StatusPayment($id_payment);
    if ($StatusPayment['payment_status'] == "finished") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['users']['Balance']['finished'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        $Balance_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$Payment_report['id_user']}' LIMIT 1"));
        $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
        $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
        $stmt->bind_param("ss", $Balance_confrim, $Payment_report['id_user']);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
        $Status_change = "paid";
        $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
        $stmt->execute();
        sendmessage($from_id, $textbotlang['users']['Balance']['Confirmpay'], null, 'HTML');
        $Payment_report['price'] = number_format($Payment_report['price']);
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
    $stmt = $connect->prepare("INSERT INTO Payment_report (id_user,id_order,time,price,payment_Status,Payment_Method) VALUES (?,?,?,?,?,?)");
    $payment_Status = "Unpaid";
    $Payment_Method = "cart to cart";
    $stmt->bind_param("ssssss", $from_id, $randomString, $dateacc, $Processing_value, $payment_Status,$Payment_Method);
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
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_user = '$from_id' LIMIT 1"));
    $Processing_value = number_format($Processing_value);
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}

#----------------Discount------------------#
if ($datain == "Discount") {
    sendmessage($from_id, $textbotlang['users']['Discount']['getcode'], $backuser, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_code_user';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "get_code_user") {
    if (!in_array($text, $code_Discount)) {
        sendmessage($from_id, $textbotlang['users']['Discount']['notcode'], null, 'HTML');
        return;
    }
    $Checkcodesql = mysqli_query($connect, "SELECT * FROM Giftcodeconsumed WHERE id_user = '$from_id'");
    $Checkcode = [];
    while ($row = mysqli_fetch_assoc($Checkcodesql)) {
        $Checkcode[] = $row['code'];
    }
    if (in_array($text, $Checkcode)) {
        sendmessage($from_id, $textbotlang['users']['Discount']['onecode'], $keyboard, 'HTML');
        $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
        $step = 'اhome';
        $stmt->bind_param("ss", $step, $from_id);
        $stmt->execute();
        return;
    }
    $get_codesql = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Discount WHERE code = '$text' LIMIT 1"));
    $balance_user = $user['Balance'] + $get_codesql['price'];
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $balance_user, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'اhome';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    number_format($get_codesql['price']);
    $text_balance_code = "کد هدیه با موفقیت ثبت شد و به موجودی شما مبلغ {$get_codesql['price']} تومان اضافه گردید. 🥳";
    sendmessage($from_id, $text_balance_code, $keyboard, 'HTML');
    $stmt = $connect->prepare("INSERT INTO Giftcodeconsumed (id_user,code) VALUES (?,?)");
    $stmt->bind_param("ss", $from_id, $text);
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    return;
}
if ($text == "🔑 روشن / خاموش کردن قفل کانال") {
if ($channels['Channel_lock'] == "off") {
        sendmessage($from_id, $textbotlang['Admin']['channel']['join-channel-on'], $channelkeyboard, 'HTML');
        $stmt = $connect->prepare("UPDATE channels SET Channel_lock = ?");
        $Channel_lock = 'on';
        $stmt->bind_param("s", $Channel_lock);
        $stmt->execute();
} else {
        sendmessage($from_id, $textbotlang['Admin']['channel']['join-channel-off'], $channelkeyboard, 'HTML');
        $stmt = $connect->prepare("UPDATE channels SET Channel_lock = ?");
        $Channel_lock = 'off';
        $stmt->bind_param("s", $Channel_lock);
        $stmt->execute();
    }
}
elseif ($text == "📣 تنظیم کانال جوین اجباری") {
    sendmessage($from_id, $textbotlang['Admin']['channel']['changechannel'] . $channels['link'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addchannel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "addchannel") {
    sendmessage($from_id, $textbotlang['Admin']['channel']['setchannel'], $channelkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("SELECT COUNT(link) FROM channels");
    $stmt->execute();
    $result = $stmt->get_result();
    $channels_ch = $result->fetch_array(MYSQLI_NUM);
    if ($channels_ch[0] == 0) {
        $stmt = $connect->prepare("INSERT INTO channels (link,Channel_lock) VALUES (?,?)");
        $Channel_lock = 'off';
        $stmt->bind_param("ss", $text, $Channel_lock);
        $stmt->execute();
    } else {
        $stmt = $connect->prepare("UPDATE channels SET link = ?");
        $stmt->bind_param("s", $text);
        $stmt->execute();
    }
}
if ($text == "👨‍💻 اضافه کردن ادمین") {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addadmin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($user['step'] == "addadmin") {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['addadminset'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("INSERT INTO admin (id_admin) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
}
if ($text == "❌ حذف ادمین"  ) {
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['getid'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'deleteadmin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "deleteadmin") {
    if (!is_numeric($text) || !in_array($text, $admin_ids)) return;
    sendmessage($from_id, $textbotlang['Admin']['manageadmin']['removedadmin'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("DELETE FROM admin WHERE id_admin = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "➕ محدودیت ساخت اکانت تست برای کاربر"  ) {
    sendmessage($from_id, $textbotlang['Admin']['manageusertest']['getidlimit'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_limit_usertest_foruser';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "add_limit_usertest_foruser") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['getid'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_number_limit';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_number_limit") {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimit'], $keyboard_usertest, 'HTML');
    $id_user_set = $text;
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
}
if ($text == "➕ محدودیت ساخت اکانت تست برای همه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['limitall'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'limit_usertest_allusers';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "limit_usertest_allusers") {
    sendmessage($from_id, $textbotlang['Admin']['getlimitusertest']['setlimitall'], $keyboard_usertest, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET limit_usertest = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE setting SET limit_usertest_all = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
}
if ($text == "📯 تنظیمات کانال"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $channelkeyboard, 'HTML');
}
#-------------------------#
if ($text == "📊 آمار ربات" ) {
    $date = jdate('Y/m/d');
    $current_time = time();
    $one_hour_later = strtotime('-1 hour', $current_time); 
    $timeacc = jdate('H:i:s', $one_hour_later); 
    $dayListSell =  mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) FROM invoice WHERE time_sell = '$date'"));
    $count_usertest =  mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) FROM TestAccount"));
    $Balanceall =  mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(Balance) FROM user"));
    $statistics = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id)  FROM user"));
    $invoice = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*)  FROM invoice"));
    $ping = sys_getloadavg();
    $ping = floatval($ping[0]);
    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $statistics['COUNT(id)'], 'callback_data' => 'countusers'],
                ['text' => $textbotlang['Admin']['sumuser'], 'callback_data' => 'countusers'],
            ],
            [
                ['text' => $count_usertest['COUNT(*)'], 'callback_data' => 'count_usertest_var'],
                ['text' => $textbotlang['Admin']['sumusertest'], 'callback_data' => 'count_usertest_var'],
            ],
            [
                ['text' => phpversion(), 'callback_data' => 'phpversion'],
                ['text' => $textbotlang['Admin']['phpversion'], 'callback_data' => 'phpversion'],
            ],
            [
                ['text' => round($ping,2), 'callback_data' => 'ping'],
                ['text' => $textbotlang['Admin']['pingbot'], 'callback_data' => 'ping'],
            ],
            [
                ['text' => $invoice['COUNT(*)'], 'callback_data' => 'sellservices'],
                ['text' => $textbotlang['Admin']['sellservices'], 'callback_data' => 'sellservices'],
            ],
            [
                ['text' => $dayListSell['COUNT(*)'], 'callback_data' => 'dayListSell'],
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "get_panel") {
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$text' LIMIT 1"));
    ini_set('max_execution_time', 1);
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    if (isset($Check_token['access_token'])) {
        $System_Stats = Get_System_Stats($marzban_list_get['url_panel'], $Check_token['access_token']);
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
        $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'];
    }

    sendmessage($from_id, $text_marzban, $keyboardmarzban, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_name_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "add_name_panel") {
    if(in_array($text,$marzban_list)){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['Repeatpanel'], $backadmin, 'HTML');
    return;
    }
    $stmt = $connect->prepare("INSERT INTO marzban_panel (name_panel,vless,vmess,trojan,shadowsocks) VALUES (?,?,?,?,?)");
    $vless= "onvless";
    $vmess = "offvmess";
    $trojan = "offtrojan";
    $shadowsocks = "offshadowsocks";
    $stmt->bind_param("sssss", $text,$vless,$vmess,$trojan,$shadowsocks);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addpanelurl'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_link_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET  Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "add_link_panel") {
    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['usernameset'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_username_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE marzban_panel SET  url_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
} elseif ($user['step'] == "add_username_panel") {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getpassword'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_password_panel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE marzban_panel SET  username_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
} elseif ($user['step'] == "add_password_panel") {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['addedpanel'], $backadmin, 'HTML');
    sendmessage($from_id, "🥳", $keyboardmarzban, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE marzban_panel SET  password_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
}
if ($text == "📨 ارسال پیام به کاربر" ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $sendmessageuser, 'HTML');
} elseif ($text == "✉️ ارسال همگانی") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetText'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextforsendall';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "gettextforsendall") {
sendmessage($from_id, "درحال ارسال پیام",$keyboardaadmin, 'HTML');
$stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
$step = 'home';
$stmt->bind_param("ss", $step, $from_id);
$stmt->execute();
$filename = 'user.txt';
$query = "SELECT id FROM user";
$result = $connect->query($query);
if ($result) {
    $ids = array();
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    $idsText = implode("\n", $ids);
    file_put_contents($filename, $idsText);
}
$file = fopen($filename, 'r');
if ($file) {
    while (($line = fgets($file)) !== false) {
    sendmessage($line, $text, null, 'HTML');
    usleep(2000000);
    }
    sendmessage($from_id, "✅ پیام به تمامی کاربران ارسال شد",$keyboardaadmin, 'HTML');
    fclose($file);
}
unlink($filename);
} elseif ($text == "📤 فوروارد همگانی" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ForwardGetext'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'gettextforwardMessage';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "gettextforwardMessage") {
sendmessage($from_id, "درحال ارسال پیام",$keyboardaadmin, 'HTML');
$stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
$step = 'home';
$stmt->bind_param("ss", $step, $from_id);
$stmt->execute();
$filename = 'user.txt';
$query = "SELECT id FROM user WHERE agent != 'f'";
$result = $connect->query($query);
if ($result) {
    $ids = array();
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    $idsText = implode("\n", $ids);
    file_put_contents($filename, $idsText);
}
$file = fopen($filename, 'r');
if ($file) {
    while (($line = fgets($file)) !== false) {
    forwardMessage($chat_id,$message_id,$from_id);
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextstart';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextstart") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_start'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه سرویس خریداری شده"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Purchased_services'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextinfo';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextinfo") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_Purchased_services'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه اکانت تست"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_usertest'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextusertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextusertest") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_usertest'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "📝 تنظیم متن توضیحات اطلاعات سرویس"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_dec_info'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changetextinfodec';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "changetextinfodec") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_info'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه 📚 آموزش" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_help'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_help") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_help'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه ☎️ پشتیبانی"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_support'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_support';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_support") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_support'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه سوالات متداول"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_fq'], $backadmin,'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_fq';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_fq") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_fq'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "📝 تنظیم متن توضیحات سوالات متداول"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_dec_fq'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_dec_fq';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_dec_fq") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_fq'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "📝 تنظیم متن توضیحات عضویت اجباری"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_channel'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_channel';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_channel") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_channel'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه حساب کاربری"  ) {
    $textstart = $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_account'];
    sendmessage($from_id, $textstart, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_account';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_account") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_account'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "دکمه افزایش موجودی"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Add_Balance'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_Add_Balance';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_Add_Balance") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_Add_Balance'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه خرید اشتراک"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_sell'],$backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_sell';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_sell") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_sell'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه سرویس های خریداری شده"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Purchased_services'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_Purchased_services';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_Purchased_services") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_Purchased_services'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن دکمه لیست تعرفه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_Tariff_list'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_Tariff_list';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_Tariff_list") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_Tariff_list'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "متن توضیحات لیست تعرفه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_dec_Tariff_list'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_dec_Tariff_list';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "text_dec_Tariff_list") {
    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_dec_Tariff_list'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($text == "✍️ ارسال پیام برای یک کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetText'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendmessagetext';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "sendmessagetext") {
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIDMessage'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'sendmessagetid';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "sendmessagetid") {
if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $textsendadmin = "
                👤 یک پیام از طرف ادمین ارسال شده است  
متن پیام:
            $Processing_value";
    sendmessage($text,  $textsendadmin, null, 'HTML');
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['MessageSent'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($text == "📚 بخش آموزش"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardhelpadmin, 'HTML');} 
elseif ($text == "📚 اضافه کردن آموزش"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddNameHelp'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_name_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "add_name_help") {
    $stmt = $connect->prepare("INSERT IGNORE INTO help (name_os) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddDecHelp'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_dec';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET  Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "add_dec") {
    if ($photo) {
        $stmt = $connect->prepare("UPDATE help SET  Media_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $photoid, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $caption, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  type_Media_os	 = ? WHERE name_os = ?");
        $type = "photo";
        $stmt->bind_param("ss", $type, $Processing_value);
        $stmt->execute();
    } elseif ($text) {
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $text, $Processing_value);
        $stmt->execute();
    } elseif ($video) {
        $stmt = $connect->prepare("UPDATE help SET  Media_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $videoid, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  Description_os	 = ? WHERE name_os = ?");
        $stmt->bind_param("ss", $caption, $Processing_value);
        $stmt->execute();
        $stmt = $connect->prepare("UPDATE help SET  type_Media_os	 = ? WHERE name_os = ?");
        $type = "video";
        $stmt->bind_param("ss", $type, $Processing_value);
        $stmt->execute();
    }
    sendmessage($from_id, $textbotlang['Admin']['Help']['SaveHelp'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($text == "❌ حذف آموزش"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Help']['SelectName'], $json_list_help, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'remove_help';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "remove_help") {
    $stmt = $connect->prepare("DELETE FROM help WHERE name_os = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Help']['RemoveHelp'], $keyboardhelpadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if (preg_match('/Response_(\w+)/', $datain, $dataget)) {
    $iduser = $dataget[1];
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $iduser, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getmessageAsAdmin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetTextResponse'], $backadmin, 'HTML');}
elseif ($user['step'] == "getmessageAsAdmin") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SendMessageuser'], null, 'HTML');
    if($text){
        $textSendAdminToUser = "
                📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
            
    متن پیام : 
    $text";
    sendmessage($Processing_value, $textSendAdminToUser, null, 'HTML');
    }
    if($photo){
        $textSendAdminToUser = "
                📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
            
    متن پیام : 
    $caption";
        telegram('sendphoto', [
            'chat_id' => $Processing_value,
            'photo' => $photoid,
            'reply_markup' => $Response,
            'caption' => $textSendAdminToUser,
            'parse_mode' => "HTML",
        ]);
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET Bot_Status = ?");
    $Status = '❌ ربات خاموش است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id,  $textbotlang['Admin']['Status']['BotStatusOff'], null);}
elseif ($datain == "❌ ربات خاموش است"  ) {
    $stmt = $connect->prepare("UPDATE setting SET Bot_Status = ?");
    $Status = "✅  ربات روشن است";;
    $stmt->bind_param("s", $Status);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET flow = ?");
    $Status = 'offflow';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id,  $textbotlang['Admin']['Status']['flowStatusOff'], null);}
elseif ($datain == "offflow"  ) {
    $stmt = $connect->prepare("UPDATE setting SET flow = ?");
    $Status = "flowon";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET NotUser = ?");
    $Status = 'offnotuser';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['UsernameStatusOff'], null);}
elseif ($datain == "offnotuser"  ) {
    $stmt = $connect->prepare("UPDATE setting SET NotUser = ?");
    $Status = "onnotuser";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['UsernameStatuson'], null);
}
//_________________________________________________
if ($text == "🔒 مسدود کردن کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockUserId'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getidblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "getidblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $query = sprintf("SELECT * FROM user WHERE id = '%d' LIMIT 1", $text);
    $result = mysqli_query($connect, $query);
    $userblock = mysqli_fetch_assoc($result);
    if ($userblock['User_Status'] == "block") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockedUser'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET User_Status = ? WHERE id = ?");
    $User_Status = "block";
    $stmt->bind_param("ss", $User_Status, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockUser'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'adddecriptionblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "adddecriptionblock") {
    $stmt = $connect->prepare("UPDATE user SET description_blocking = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['DescriptionBlock'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($text == "🔓 رفع مسدودی کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'getidunblock';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "getidunblock") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $query = sprintf("SELECT * FROM user WHERE id = '%d' LIMIT 1", $text);
    $result = mysqli_query($connect, $query);
    $userunblock = mysqli_fetch_assoc($result);
    if ($userunblock['User_Status'] == "Active") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserNotBlock'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET User_Status = ? WHERE id = ?");
    $User_Status = "Active";
    $stmt->bind_param("ss", $User_Status, $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET description_blocking = ? WHERE id = ?");
    $spcae = "";
    $stmt->bind_param("ss", $spcae, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserUnblocked'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
//_________________________________________________
if ($text == "♨️ بخش قوانین"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $rollkey, 'HTML');}
elseif ($text == "⚖️ متن قانون"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_roll'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'text_roll';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "text_roll") {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    $stmt = $connect->prepare("UPDATE textbot SET text = ? WHERE id_text = 'text_roll'");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET roll_Status = ?");
    $Status = '❌ تایید قوانین خاموش است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['rollStatusOff'], null);}
elseif ($datain == "❌ تایید قوانین خاموش است"  ) {
    $stmt = $connect->prepare("UPDATE setting SET roll_Status = ?");
    $Status = '✅ تایید قانون روشن است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['rollStatuson'], null);
}
//_________________________________________________
if ($text == "👤 خدمات کاربر" ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $User_Services, 'HTML');
}
#-------------------------#

elseif ($text == "📊 وضعیت تایید شماره کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['manageusertest']['getidlimit'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_status';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "get_status") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $user_phone_status = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    if ($user_phone_status['number'] == "none") {
        sendmessage($from_id, $textbotlang['Admin']['phone']['notactive'], $User_Services, 'HTML');
    } else {
        sendmessage($from_id, $textbotlang['Admin']['phone']['active'], $User_Services, 'HTML');
    }
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET get_number = ?");
    $Status = '❌ احرازهویت شماره تماس غیرفعال است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['phoneStatusOff'], null);}
elseif ($datain == "❌ احرازهویت شماره تماس غیرفعال است" ) {
    $stmt = $connect->prepare("UPDATE setting SET get_number = ?");
    $Status = '✅ تایید شماره موبایل روشن است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['phoneStatuson'], null);
}
#-------------------------#
if ($text == "👀 مشاهده شماره تلفن کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_number_admin';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "get_number_admin") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $user_phone_number = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'confrim_number';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "confrim_number") {
    $stmt = $connect->prepare("UPDATE user SET number  = ? WHERE id = ?");
    $confrimnum = 'confrim number by admin';
    $stmt->bind_param("ss", $confrimnum, $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step  = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['phone']['active'], $User_Services, 'HTML');
}
if ($text == "📣 تنظیم کانال گزارش"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Channel']['ReportChannel'] . $setting['Channel_Report'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'addchannelid';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "addchannelid") {
    sendmessage($from_id, $textbotlang['Admin']['Channel']['SetChannelReport'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE setting SET Channel_Report = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    sendmessage($setting['Channel_Report'], $textbotlang['Admin']['Channel']['TestChannel'], null, 'HTML');
}
#-------------------------#
if ($text == "🏬 بخش فروشگاه"  ) {
    sendmessage($from_id, $textbotlang['users']['selectoption'], $shopkeyboard, 'HTML');
} 
elseif ($text == "🛍 اضافه کردن محصول"  ) {
        $locationproduct = mysqli_query($connect, "SELECT * FROM marzban_panel");
    if (mysqli_num_rows($locationproduct) == 0) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpaneladmin'], null, 'HTML');
    return;
}
    sendmessage($from_id, $textbotlang['Admin']['Product']['AddProductStepOne'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_limit';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "get_limit") {
    $randomString = bin2hex(random_bytes(2));
    $stmt = $connect->prepare("INSERT IGNORE INTO product (name_product,code_product) VALUES (?,?)");
    $stmt->bind_param("ss", $text,$randomString);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $randomString, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['Service_location'], $json_list_marzban_panel, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_location';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}elseif ($user['step'] == "get_location") {
    $stmt = $connect->prepare("UPDATE product SET Location = ? WHERE code_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
        sendmessage($from_id, $textbotlang['Admin']['Product']['GetLimit'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_time';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}elseif ($user['step'] == "get_time") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Volume_constraint = ? WHERE code_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['GettIime'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}elseif ($user['step'] == "get_price") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET Service_time = ? WHERE code_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetPrice'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'endstep';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
} elseif ($user['step'] == "endstep") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET price_product = ? WHERE code_product = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['SaveProduct'], $shopkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$order_id' LIMIT 1"));
    $Balance_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$Payment_report['id_user']}' LIMIT 1"));
    if ($Payment_report['payment_Status'] == "paid" || $Payment_report['payment_Status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
    $stmt->bind_param("ss", $Balance_confrim, $Payment_report['id_user']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "paid";
    $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
    $stmt->execute();
    $Payment_report['price'] = number_format($Payment_report['price'],0);
    $textconfrom = "
            💵 پرداخت با موفقیت تایید گردید.
              به موجودی کاربر مبلغ {$Payment_report['price']} اضافه گردید.
            ";
    sendmessage($from_id, $textconfrom, null, 'HTML');
    number_format($Payment_report['price']);
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
    $Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$id_order' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $Payment_report['id_user'], $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $id_order, $from_id);
    $stmt->execute();
    if ($Payment_report['payment_Status'] == "reject" || $Payment_report['payment_Status']  == "paid") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "reject";
    $stmt->bind_param("ss", $Status_change, $id_order);
    $stmt->execute();

    sendmessage($from_id, $textbotlang['Admin']['Payment']['Reasonrejecting'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "reject-dec";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $text, null);}
elseif ($user['step'] == "reject-dec") {
    $stmt = $connect->prepare("UPDATE Payment_report SET dec_not_confirmed = ? WHERE id_order = ?");
    $stmt->bind_param("ss", $text, $user['Processing_value_one']);
    $stmt->execute();
    $text_reject = "❌ کاربر گرامی پرداخت شما به دلیل زیر رد گردید.
        ✍️ $text
        🛒 کد پیگیری پرداخت: {$user['Processing_value_one']}
        ";
    sendmessage($from_id, $textbotlang['Admin']['Payment']['Rejected'], $keyboardadmin, 'HTML');
    sendmessage($Processing_value, $text_reject, null, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "home";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "❌ حذف محصول"  ) {
    sendmessage($from_id,$textbotlang['Admin']['Product']['Rmove_location'], $json_list_marzban_panel, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'selectloc';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "selectloc") {
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "remove-product";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    sendmessage($from_id,$textbotlang['Admin']['Product']['selectRemoveProduct'], $json_list_product_list_admin, 'HTML');}
elseif ($user['step'] == "remove-product") {
    if (!in_array($text, $name_product)) {
        sendmessage($from_id, $textbotlang['users']['sell']['error-product'], null, 'HTML');
        return;
    }
    $stmt = $connect->prepare("DELETE FROM product WHERE name_product = ? AND (Location= ? or Location= ?)");
    $ydf = '/all';
    $stmt->bind_param("sss", $text,$user['Processing_value'],$ydf);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['RemoveedProduct'], $shopkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "home";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "✏️ ویرایش محصول"  ) {
    sendmessage($from_id,$textbotlang['Admin']['Product']['Rmove_location'], $json_list_marzban_panel, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'selectlocedite';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif ($user['step'] == "selectlocedite") {
    $stmt = $connect->prepare("UPDATE user SET Processing_value_one = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = "editproduct";
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectEditProduct'], $json_list_product_list_admin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_filde';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "change_filde") {
    if (!in_array($text, $name_product)) {
        sendmessage($from_id, $textbotlang['users']['sell']['error-product'], null,'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectfieldProduct'], $change_product, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "قیمت"  ) {
    sendmessage($from_id, "قیمت جدید را ارسال کنید", $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_price';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "change_price") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE product SET price_product = ? WHERE name_product = ? AND (Location = ? OR Location = ?)");
    $location = '/all';
    $stmt->bind_param("ssss", $text, $Processing_value,$user['Processing_value_one'],$location);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE invoice SET price_product = ? WHERE name_product = ? AND Service_location = ? ");
    $stmt->bind_param("sss", $text, $Processing_value,$user['Processing_value_one']);
    $stmt->execute();
    sendmessage($from_id, "✅ قیمت محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "نام محصول"  ) {
    sendmessage($from_id, "نام جدید را ارسال کنید", $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_name';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "change_name") {
    $stmt = $connect->prepare("UPDATE product SET name_product = ? WHERE name_product = ? AND (Location = ?  || Location = ?)");
    $value = "/all";
    $stmt->bind_param("ssss", $text, $Processing_value,$user['Processing_value_one'],$value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE invoice SET name_product = ? WHERE name_product = ? AND Service_location = ? ");
    $stmt->bind_param("sss", $text, $Processing_value,$user['Processing_value_one']);
    $stmt->execute();
    sendmessage($from_id, "✅نام محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "حجم"  ) {
    sendmessage($from_id, "حجم جدید را ارسال کنید", $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_val';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();} 
elseif ($user['step'] == "change_val") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE invoice SET Volume = ? WHERE name_product = ? AND Service_location = ? ");
    $stmt->bind_param("sss", $text, $Processing_value,$user['Processing_value_one']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE product SET Volume_constraint = ? WHERE name_product = ?  AND Location = ? ");
    $stmt->bind_param("sss", $text, $Processing_value,$user['Processing_value_one']);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['volumeUpdated'], $shopkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "زمان"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Product']['NewTime'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'change_time';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "change_time") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE invoice SET Service_time = ? WHERE name_product = ? AND Service_location = ? ");
    $stmt->bind_param("sss", $text, $Processing_value,$user['Processing_value_one']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE product SET Service_time = ? WHERE name_product = ? AND Location = ? ");
    $stmt->bind_param("sss", $text, $Processing_value,$user['Processing_value_one']);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['TimeUpdated'], $shopkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "⏳ زمان سرویس تست"  ) {
    sendmessage($from_id, "🕰 مدت زمان سرویس تست را ارسال کنید.
        زمان فعلی: {$setting['time_usertest']} ساعت
        ⚠️ زمان بر حسب ساعت است.", $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'updatetime';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "updatetime") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE setting SET time_usertest = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Usertest']['TimeUpdated'], $keyboard_usertest, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "💾 حجم اکانت تست"  ) {
    sendmessage($from_id, "حجم سرویس تست را ارسال کنید.
        زمان فعلی: {$setting['val_usertest']} مگابایت
        ⚠️ حجم بر حسب مگابایت است.", $backadmin,'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'val_usertest';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "val_usertest") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE setting SET val_usertest = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Usertest']['VolumeUpdated'], $keyboard_usertest, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "⬆️️️ افزایش موجودی کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalance'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'add_Balance';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "add_Balance") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['PriceBalance'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price_add';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "get_price_add") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalanceUser'], $User_Services, 'HTML');
    $Balance_user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$Processing_value' LIMIT 1"));
    $Balance_add_user = $Balance_user['Balance'] + $text;
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_add_user, $Processing_value);
    $stmt->execute();
    $text = number_format($text);
    $textadd = "💎 کاربر عزیز مبلغ $text تومان به موجودی کیف پول تان اضافه گردید.";
    sendmessage($Processing_value, $textadd, null, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "⬇️ کم کردن موجودی" ) {
    sendmessage($from_id, $textbotlang['Admin']['Balance']['NegativeBalance'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'Negative_Balance';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "Negative_Balance") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['PriceBalancek'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price_Negative';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "get_price_Negative") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['NegativeBalanceUser'], $User_Services, 'HTML');
    $Balance_user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$Processing_value' LIMIT 1"));
    $Balance_Low_user = $Balance_user['Balance'] - $text;
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $stmt->bind_param("ss", $Balance_Low_user, $Processing_value);
    $stmt->execute();
    $text = number_format($text);
    $textkam = "❌ کاربر عزیز مبلغ $text تومان از  موجودی کیف پول تان کسر گردید.";
    sendmessage($Processing_value, $textkam, null, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#-------------------------#
if ($text == "👁‍🗨 مشاهده اطلاعات کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'show_info';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "show_info") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '$text' LIMIT 1"));
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET help_Status = ?");
    $Status = '❌ آموزش غیرفعال است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['HelpStatusOff'], null);}
elseif ($datain == "❌ آموزش غیرفعال است"  ) {
    $stmt = $connect->prepare("UPDATE setting SET help_Status = ?");
    $Status = '✅ آموزش فعال است';
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['HelpStatuson'], null);
}
#-------------------------#
if ($text == "🎁 ساخت کد هدیه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['GetCode'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_code';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "get_code") {
    if (!preg_match('/^[A-Za-z]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['ErrorCode'], null, 'HTML');
        return;
    }
    $stmt = $connect->prepare("INSERT INTO Discount (code) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['PriceCode'], null, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'get_price_code';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "get_price_code") {
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE Discount SET price = ? WHERE code = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['SaveCode'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET iran_number = ?");
    $Status = "❌ بررسی شماره ایرانی غیرفعال است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['PhoneIranStatusOff'], null);}
elseif ($datain == "❌ بررسی شماره ایرانی غیرفعال است" ) {
    $stmt = $connect->prepare("UPDATE setting SET iran_number = ?");
    $Status = "✅ احرازشماره ایرانی روشن است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET sublink = ?");
    $Status = "❌ ارسال لینک سابسکرایب غیرفعال است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['subStatusOff'], null);}
elseif ($datain == "❌ ارسال لینک سابسکرایب غیرفعال است"  ) {
    $stmt = $connect->prepare("UPDATE setting SET sublink = ?");
    $Status = "✅ لینک اشتراک فعال است.";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
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
    $stmt = $connect->prepare("UPDATE setting SET configManual = ?");
    $Status = "❌ ارسال کانفیگ دستی خاموش است";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['configStatusOff'], null);}
elseif ($datain == "❌ ارسال کانفیگ دستی خاموش است"  ) {
    $stmt = $connect->prepare("UPDATE setting SET configManual = ?");
    $Status = "✅ ارسال کانفیگ بعد خرید فعال است.";
    $stmt->bind_param("s", $Status);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['configStatuson'], null);
}
#----------------[  view order user  ]------------------#
if ($text == "🛍 مشاهده سفارشات کاربر" ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ViewOrder'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'GetIdAndOrdedrs';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "GetIdAndOrdedrs") {
    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    $OrderUsers = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$text'");
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
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
#----------------[  remove Discount   ]------------------#
if ($text == "❌ حذف کد هدیه"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemoveCode'], $json_list_Discount_list_admin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'remove-Discount';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "remove-Discount") {
    if (!in_array($text, $code_Discount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['NotCode'], null, 'HTML');
        return;
    }
    $stmt = $connect->prepare("DELETE FROM Discount WHERE code = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemovedCode'], $shopkeyboard, 'HTML');
}
#----------------[  REMOVE protocol   ]------------------#
if ($text == "🗑 حذف پروتکل"  ) {
    sendmessage($from_id, $textbotlang['Admin']['Protocol']['RemoveProtocol'], $keyboardprotocollist, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'removeprotocol';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "removeprotocol") {
    if (!in_array($text, $protocoldata)) {
        sendmessage($from_id, $textbotlang['Admin']['Protocol']['invalidProtocol'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Protocol']['RemovedProtocol'], $keyboardmarzban, 'HTML');
    $stmt = $connect->prepare("DELETE FROM protocol WHERE NameProtocol = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "❌ حذف سرویس کاربر"  ) {
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemoveService'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'removeservice';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "removeservice") {
    $info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM invoice WHERE username = '{$text}' LIMIT 1"));
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$info_product['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $get_username_Check = getuser($text, $Check_token['access_token'], $marzban_list_get['url_panel']);
    if(isset($get_username_Check['status'])){
        removeuser($Check_token['access_token'], $marzban_list_get['url_panel'], $text);
    }
    $stmt = $connect->prepare("DELETE FROM invoice WHERE username = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemovedService'], $keyboardadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "💡 روش ساخت نام کاربری"  ) {
    $text_username = "⭕️ روش ساخت نام کاربری برای اکانت ها را از دکمه زیر انتخاب نمایید.

⚠️ در صورتی که کاربری نام کاربری نداشته باشه کلمه NOT_USERNAME جای نام کاربری اعمال خواهد شد.

⚠️ در صورتی که نام کاربری وجود داشته باشه یک عدد رندوم به نام کاربری اضافه خواهد شد

روش فعلی : {$setting['MethodUsername']}";
    sendmessage($from_id, $text_username, $MethodUsername, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'updatemethodusername';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();}
elseif ($user['step'] == "updatemethodusername") {
    $stmt = $connect->prepare("UPDATE setting SET MethodUsername = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['AlgortimeUsername']['SaveData'], $keyboardmarzban, 'HTML');
}
#----------------[  MANAGE PAYMENT   ]------------------#

if($text == "💵 مالی"  ){
        sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardpaymentManage, 'HTML');
}
if($text == "💳 تنظبمات درگاه آفلاین"  ){
            sendmessage($from_id, $textbotlang['users']['selectoption'], $CartManage, 'HTML');
}
if($text == "💳 تنظیم شماره کارت"  ){
    $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'CartDescription'"));
    $textcart = "💳 شماره کارت خود را ارسال کنید

⭕️ همراه با شماره کارت می توانید نام صاحب کارت هم ارسال نمایید.

💳 شماره کارت فعلی شما : {$PaySetting['ValuePay']}";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'changecard';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "changecard"){
    sendmessage($from_id,$textbotlang['Admin']['SettingPayment']['Savacard'] , $CartManage,'HTML');
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $namepay = "CartDescription";
    $stmt->bind_param("ss", $text, $namepay);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "🔌 وضعیت درگاه آفلاین"  ) {
        $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'Cartstatus'"))['ValuePay'];
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
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'offcard';
    $where = 'Cartstatus';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['cardStatusOff'], null);}
elseif ($datain == "offcard"  ) {
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'oncard';
    $where = 'Cartstatus';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['cardStatuson'], null);
}
if($text == "💵 تنظیمات nowpayment"  ){
            sendmessage($from_id, $textbotlang['users']['selectoption'], $NowPaymentsManage, 'HTML');
}
if($text == "🧩 api nowpayment"  ){
    $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'apinowpayment'"))['ValuePay'];
    $textcart = "⚙️ api سایت nowpayments.io را ارسال نمایید

api nowpayment :$PaySetting";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'apinowpayment';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "apinowpayment"){
    sendmessage($from_id,$textbotlang['Admin']['SettingnowPayment']['Savaapi'] , $NowPaymentsManage,'HTML');
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $namepay = "apinowpayment";
    $stmt->bind_param("ss", $text, $namepay);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "🔌 وضعیت درگاه nowpayments"  ) {
        $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'nowpaymentstatus'"))['ValuePay'];
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
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'offnowpayment';
    $where = 'nowpaymentstatus';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['nowpaymentsStatusOff'], null);}
elseif ($datain == "offnowpayment"  ) {
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'onnowpayment';
    $where = 'nowpaymentstatus';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['nowpaymentsStatuson'], null);
}
if ($text == "💎 درگاه ارزی ریالی"  ) {
        $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'digistatus'"))['ValuePay'];
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
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'ondigi';
    $where = 'digistatus';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['digiStatuson'], null);}
elseif ($datain == "ondigi"  ) {
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'offdigi';
    $where = 'digistatus';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['digiStatusOff'], null);
}
if($text == "🟡  درگاه زرین پال"  ){
    sendmessage($from_id, $textbotlang['users']['selectoption'], $zarinpal, 'HTML');
}
if($text == "تنظیم مرچنت"  ){
    $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'merchant_id'"));
    $textzarinpal = "💳 مرچنت کد خود را از زرین پال دریافت و در این قسمت وارد کنید

مرچنت کد فعلی شما : {$PaySetting['ValuePay']}";
    sendmessage($from_id, $textzarinpal, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'merchant_id';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "merchant_id"){
    sendmessage($from_id,$textbotlang['Admin']['SettingnowPayment']['Savaapi'] , $zarinpal,'HTML');
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $namepay = "merchant_id";
    $stmt->bind_param("ss", $text, $namepay);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "وضعیت درگاه زرین پال"  ) {
        $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'statuszarinpal'"))['ValuePay'];
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
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'onzarinpal';
    $where = 'statuszarinpal';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['zarinpalStatuson'], null);}
elseif ($datain == "onzarinpal"  ) {
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'offzarinpal';
    $where = 'statuszarinpal';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['zarrinpalStatusOff'], null);
}
if($text == "🔵 درگاه آقای پرداخت"  ){
    sendmessage($from_id, $textbotlang['users']['selectoption'], $aqayepardakht, 'HTML');
}
if($text == "تنظیم مرچنت آقای پرداخت"  ){
    $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'merchant_id_aqayepardakht'"));
    $textaqayepardakht = "💳 مرچنت کد خود را ازآقای پرداخت دریافت و در این قسمت وارد کنید

مرچنت کد فعلی شما : {$PaySetting['ValuePay']}";
    sendmessage($from_id, $textaqayepardakht, $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'merchant_id_aqayepardakht';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "merchant_id_aqayepardakht"){
    sendmessage($from_id,$textbotlang['Admin']['SettingnowPayment']['Savaapi'] , $aqayepardakht,'HTML');
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $namepay = "merchant_id_aqayepardakht";
    $stmt->bind_param("ss", $text, $namepay);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if ($text == "وضعیت درگاه آقای پرداخت"  ) {
        $PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'statusaqayepardakht'"))['ValuePay'];
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
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'onaqayepardakht';
    $where = 'statusaqayepardakht';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id,$textbotlang['Admin']['Status']['aqayepardakhtStatuson'], null);}
elseif ($datain == "onaqayepardakht"  ) {
    $stmt = $connect->prepare("UPDATE PaySetting SET ValuePay = ? WHERE NamePay = ?");
    $Status = 'offaqayepardakht';
    $where = 'statusaqayepardakht';
    $stmt->bind_param("ss", $Status,$where);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['aqayepardakhtStatusOff'], null);
}
if($text == "✏️ ویرایش پنل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getloc'], $json_list_marzban_panel, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'GetLocationEdit';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "GetLocationEdit"){
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['selectoption'], $optionMarzban, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($text == "✍️ نام پنل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['GetNameNew'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'GetNameNew';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "GetNameNew"){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedNmaePanel'], $optionMarzban, 'HTML');
    $stmt = $connect->prepare("UPDATE marzban_panel SET name_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE invoice SET Service_location = ? WHERE Service_location = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE product SET Location = ? WHERE Location = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE TestAccount SET Service_location = ? WHERE Service_location = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET Processing_value = ? WHERE id = ?");
    $stmt->bind_param("ss", $text, $from_id);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($text == "🔗 ویرایش آدرس پنل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['geturlnew'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'GeturlNew';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "GeturlNew"){
        if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedurlPanel'], $optionMarzban, 'HTML');
    $stmt = $connect->prepare("UPDATE marzban_panel SET url_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($text == "👤 ویرایش نام کاربری"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getusernamenew'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'GetusernameNew';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "GetusernameNew"){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedusernamePanel'], $optionMarzban, 'HTML');
    $stmt = $connect->prepare("UPDATE marzban_panel SET username_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($text == "🔐 ویرایش رمز عبور"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getpasswordnew'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'GetpaawordNew';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "GetpaawordNew"){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['ChangedpasswordPanel'], $optionMarzban, 'HTML');
    $stmt = $connect->prepare("UPDATE marzban_panel SET password_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($text == "⚙️ تنظیمات پروتکل"  ){
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['settingprotocol'], $keyboardprotocol, 'HTML');
}
elseif($text == "vless" ){
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET vless = ? WHERE name_panel = ?");
    $values = 'offvless';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET vless = ? WHERE name_panel = ?");
    $values = 'onvless';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET vmess = ? WHERE name_panel = ?");
    $values = 'offvmess';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET vmess = ? WHERE name_panel = ?");
    $values = 'onvmess';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET trojan = ? WHERE name_panel = ?");
    $values = 'offtrojan';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET trojan = ? WHERE name_panel = ?");
    $values = 'ontrojan';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET shadowsocks = ? WHERE name_panel = ?");
    $values = 'offshadowsocks';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET shadowsocks = ? WHERE name_panel = ?");
    $values = 'onshadowsocks';
    $stmt->bind_param("ss", $values, $Processing_value);
    $stmt->execute();
    $marzbanprotocol = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '$Processing_value'"));
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
    $stmt = $connect->prepare("UPDATE marzban_panel SET password_panel = ? WHERE name_panel = ?");
    $stmt->bind_param("ss", $text, $Processing_value);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if($text == "❌ حذف پنل"  ) {
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['RemovedPanel'], $keyboardmarzban, 'HTML');
    $stmt = $connect->prepare("DELETE FROM marzban_panel WHERE name_panel = ?");
    $stmt->bind_param("s", $Processing_value);
    $stmt->execute();
}
if($text == "➕ تنظیم قیمت حجم اضافه"  ){
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['SetPrice'].$setting['Extra_volume'], $backadmin, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'GetPriceExtra';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
elseif($user['step'] == "GetPriceExtra"){
        if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    $stmt = $connect->prepare("UPDATE setting SET Extra_volume = ?");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['ChangedPrice'], $shopkeyboard, 'HTML');
    $stmt = $connect->prepare("UPDATE user SET step = ? WHERE id = ?");
    $step = 'home';
    $stmt->bind_param("ss", $step, $from_id);
    $stmt->execute();
}
if($text == "🤖 افزودن نماینده" ){
        sendmessage($from_id, "این قابلیت در نسخه پولی فعال است", null, 'HTML');
}
if($text == "🤖 حذف نماینده" ){
        sendmessage($from_id, "این قابلیت در نسخه پولی فعال است", null, 'HTML');
}
if ($text == "👤 کاربران ربات" || $datain == "backuser" ) {
       sendmessage($from_id, "این قابلیت در نسخه پولی فعال است", null, 'HTML');
}
if ($text == "🎁 ساخت کد تخفیف"  ) {
    sendmessage($from_id, "این قابلیت در نسخه پولی فعال است", null, 'HTML');
}
if($text == "🔴 درگاه پرفکت مانی"  ){
    sendmessage($from_id, "این قابلیت در نسخه پولی فعال است", null, 'HTML');
}
