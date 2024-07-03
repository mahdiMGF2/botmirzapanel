<?php
require_once 'vendor/autoload.php';
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
function ActiveVoucher($ev_number, $ev_code){
    global $connect;
    $Payer_Account = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_Payer_Account',"select")['ValuePay'];
    $AccountID = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_AccountID',"select")['ValuePay'];
    $PassPhrase = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_PassPhrase',"select")['ValuePay'];
    $opts = array(
        'socket' => array(
            'bindto' => 'ip',
        )
    );

    $context = stream_context_create($opts);

    $voucher = file_get_contents("https://perfectmoney.com/acct/ev_activate.asp?AccountID=" . $AccountID . "&PassPhrase=" . $PassPhrase . "&Payee_Account=" . $Payer_Account . "&ev_number=" . $ev_number . "&ev_code=" . $ev_code);
    return $voucher;
}
function update($table, $field, $newValue, $whereField = null, $whereValue = null) {
    global $pdo,$user;

    if ($whereField !== null) {
        $stmt = $pdo->prepare("SELECT $field FROM $table WHERE $whereField = ? FOR UPDATE");
        $stmt->execute([$whereValue]);
        $currentValue = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE $table SET $field = ? WHERE $whereField = ?");
        $stmt->execute([$newValue, $whereValue]);
    } else {
        $stmt = $pdo->prepare("UPDATE $table SET $field = ?");
        $stmt->execute([$newValue]);
    }
}
function step($step, $from_id){
    global $pdo;
    $stmt = $pdo->prepare('UPDATE user SET step = ? WHERE id = ?');
    $stmt->execute([$step, $from_id]);


}
function select($table, $field, $whereField = null, $whereValue = null, $type = "select") {
    global $pdo;

    $query = "SELECT $field FROM $table";

    if ($whereField !== null) {
        $query .= " WHERE $whereField = :whereValue";
    }

    try {
        $stmt = $pdo->prepare($query);

        if ($whereField !== null) {
            $stmt->bindParam(':whereValue', $whereValue , PDO::PARAM_STR);
        }

        $stmt->execute();

        if ($type == "count") {
            return $stmt->rowCount();
        } elseif ($type == "FETCH_COLUMN") {
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }elseif ($type == "fetchAll") {
            return $stmt->fetchAll();
        } else {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

function generateUUID() {
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); 

    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    return $uuid;
}
function tronratee(){
    $tronrate = [];
    $tronrate['results'] = [];
    $requests = json_decode(file_get_contents('https://eswap.ir/fa/rates'), true);
    $tronrate['result']['USD'] = $requests['fiats'][0]['price'];
    $tronrate['result']['TRX'] = $requests['coins'][0]['price']*$requests['fiats'][0]['price'];
    return $tronrate;
}
function nowPayments($payment, $price_amount, $order_id, $order_description){
    $apinowpayments = select("PaySetting", "ValuePay", "NamePay", 'apinowpayment',"select")['ValuePay'];
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
    $apinowpayments = select("PaySetting", "ValuePay", "NamePay", 'apinowpayment',"select")['ValuePay'];
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
function formatBytes($bytes, $precision = 2): string
{
    $base = log($bytes, 1024);
    $power = $bytes > 0 ? floor($base) : 0;
    $suffixes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
    return round(pow(1024, $base - $power), $precision) . ' ' . $suffixes[$power];
}
#---------------------[ ]--------------------------#
function generateUsername($from_id,$Metode,$username,$randomString,$text)
{
    global $connect;
    $setting = select("setting", "*");
    global $connect;
    if($Metode == "آیدی عددی + حروف و عدد رندوم"){
        return $from_id."_".$randomString;
    }
    elseif($Metode == "نام کاربری + حروف و عدد رندوم"){
        return $username."_".$randomString;
    }
    elseif($Metode == "نام کاربری + عدد به ترتیب"){
        $statistics = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id_user)  FROM invoice WHERE id_user = '$from_id'"));
        $countInvoice = intval($statistics['COUNT(id_user)']) + 1 ;
        return $username."_".$countInvoice;
    }
    elseif($Metode == "نام کاربری دلخواه")return $text;
    elseif($Metode == "متن دلخواه + عدد رندوم")return $setting['namecustome']."_".$randomString;
}

function outputlunk($text){
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $text);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
$response = curl_exec($ch);
if($response === false) {
    $error = curl_error($ch);
    return "";
} else {
    return $response;
}

curl_close($ch);
}
function DirectPayment($order_id){
    global $pdo,$ManagePanel,$textbotlang,$keyboard,$from_id,$message_id,$callback_query_id;
    $setting = select("setting", "*");
    $admin_ids = select("admin", "id_admin",null,null,"FETCH_COLUMN");
    $Payment_report = select("Payment_report", "*", "id_order", $order_id,"select");
    $format_price_cart = number_format($Payment_report['price']);
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'],"select");
    $steppay = explode("|", $Payment_report['invoice']);
    if ($steppay[0] == "getconfigafterpay") {
        $stmt = $pdo->prepare("SELECT * FROM invoice WHERE username = '{$steppay[1]}' AND Status = 'unpaid' LIMIT 1");
        $stmt->execute();
        $get_invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT * FROM product WHERE name_product = '{$get_invoice['name_product']}' AND (Location = '{$get_invoice['Service_location']}'  or Location = '/all')");
        $stmt->execute();
        $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
        $username_ac = $get_invoice['username'];
        $randomString = bin2hex(random_bytes(2));
        $marzban_list_get = select("marzban_panel", "*", "name_panel", $get_invoice['Service_location'],"select");
        $date = strtotime("+" . $get_invoice['Service_time'] . "days");
        if(intval($get_invoice['Service_time']) == 0){
            $timestamp = 0;
            }else{
            $timestamp = strtotime(date("Y-m-d H:i:s", $date));
            }        
        $datac = array(
    'expire' => $timestamp,
    'data_limit' => $get_invoice['Volume'] * pow(1024, 3),
    );
        $dataoutput = $ManagePanel->createUser($marzban_list_get['name_panel'],$username_ac,$datac);

        if ($dataoutput['username'] == null) {
        $dataoutput['msg'] = json_encode($dataoutput['msg']);
        sendmessage($Balance_id['id'], $textbotlang['users']['sell']['ErrorConfig'], $keyboard, 'HTML');
        $texterros = "
⭕️ یک کاربر قصد دریافت اکانت داشت که ساخت کانفیگ با خطا مواجه شده و به کاربر کانفیگ داده نشد
✍️ دلیل خطا : 
{$dataoutput['msg']}
آیدی کابر : {$Balance_id['id']}
نام کاربری کاربر : @{$Balance_id['username']}
نام پنل : {$marzban_list_get['name_panel']}";
        foreach ($admin_ids as $admin) {
            sendmessage($admin, $texterros, null, 'HTML');
        step('home', $admin);
        }
        return;
    }
        $output_config_link = "";
        $config = "";
        $Shoppinginfo = [
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
                ]
            ]
        ];
        if ($marzban_list_get['sublink'] == "onsublink") {
            $output_config_link = $dataoutput['subscription_url'];
        }
        if ($marzban_list_get['configManual'] == "onconfig") {
            foreach ($dataoutput['configs'] as $configs) {
                $config .= "\n\n" . $configs;
                $configqr .= $configs;
            }
        }
        $Shoppinginfo = json_encode($Shoppinginfo);
        $textcreatuser = "✅ سرویس با موفقیت ایجاد شد
    
👤 نام کاربری سرویس : <code>{$dataoutput['username']}</code>
🌿 نام سرویس: {$get_invoice['name_product']}
‏🇺🇳 لوکیشن: {$marzban_list_get['name_panel']}
⏳ مدت زمان: {$get_invoice['Service_time']}  روز
🗜 حجم سرویس:  {$get_invoice['Volume']} گیگ
    
لینک اتصال:
<code>{$config}{$output_config_link}</code>
    
📚 راهنمای اتصال به سرویس را از طریق کلیک کردن دکمه زیر مطالعه بفرمایید";
        if ($marzban_list_get['configManual'] == "onconfig") {
            if (count($dataoutput['configs']) == 1) {
        $urlimage = "{$get_invoice['id_user']}$randomString.png";
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
                    'chat_id' => $get_invoice['id_user'],
                    'photo' => new CURLFile($urlimage),
                    'reply_markup' => $Shoppinginfo,
                    'caption' => $textcreatuser,
                    'parse_mode' => "HTML",
                ]);
                unlink($urlimage);
            } else {
                sendmessage($get_invoice['id_user'], $textcreatuser, $Shoppinginfo, 'HTML');
            }
        }
        elseif ($marzban_list_get['sublink'] == "onsublink") {
            $urlimage = "{$get_invoice['id_user']}$randomString.png";
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
                'chat_id' => $get_invoice['id_user'],
                'photo' => new CURLFile($urlimage),
                'reply_markup' => $Shoppinginfo,
                'caption' => $textcreatuser,
                'parse_mode' => "HTML",
            ]);
            unlink($urlimage);
        }
        $partsdic = explode("_", $Balance_id['Processing_value_four']);
        if ($partsdic[0] == "dis") {
            $SellDiscountlimit = select("DiscountSell", "*", "codeDiscount", $partsdic[1],"select");
            $value = intval($SellDiscountlimit['usedDiscount']) + 1;
            update("DiscountSell","usedDiscount",$value, "codeDiscount",$partsdic[1]);
            $stmt = $pdo->prepare("INSERT INTO Giftcodeconsumed (id_user,code) VALUES (:id_user,:code)");
            $stmt->bindParam(':id_user', $Balance_id['id']);
            $stmt->bindParam(':code', $partsdic[1]);
            $stmt->execute();
            $text_report = "⭕️ یک کاربر با نام کاربری @{$Balance_id['username']}  و آیدی عددی {$Balance_id['id']} از کد تخفیف {$partsdic[1]} استفاده کرد.";
            if (strlen($setting['Channel_Report']) > 0) {
                telegram('sendmessage',[
        'chat_id' => $setting['Channel_Report'],
        'text' => $text_report,
        ]);
            }
        }
        $affiliatescommission = select("affiliates", "*", null, null,"select");
        if ($affiliatescommission['status_commission'] == "oncommission" &&($Balance_id['affiliates'] !== null || $Balance_id['affiliates'] != 0)) {
            $result = ($get_invoice['price_product'] * $affiliatescommission['affiliatespercentage']) / 100;
            $user_Balance = select("user", "*", "id", $Balance_id['affiliates'],"select");
            if(isset($user_Balance)){
            $Balance_prim = $user_Balance['Balance'] + $result;
            update("user","Balance",$Balance_prim, "id",$Balance_id['affiliates']);
            $result = number_format($result);
            $textadd = "🎁  پرداخت پورسانت 
        
        مبلغ $result تومان به حساب شما از طرف  زیر مجموعه تان به کیف پول شما واریز گردید";
            sendmessage($Balance_id['affiliates'], $textadd, null, 'HTML');
            }
        }
        $Balance_prims = $Balance_id['Balance'] - $get_invoice['price_product'];
        if($Balance_prims <= 0) $Balance_prims = 0;
        update("user","Balance",$Balance_prims, "id",$Balance_id['id']);
        $Balance_id['Balance'] = select("user", "Balance", "id", $get_invoice['id_user'],"select")['Balance'];
        $balanceformatsell = number_format($Balance_id['Balance'], 0);
        $text_report = " 🛍 خرید جدید بعد پرداخت موفق
                
⚙️ یک کاربر اکانت  با نام کانفیگ {$get_invoice['username']} خریداری کرد
        
        
قیمت محصول : {$get_invoice['price_product']} تومان
حجم محصول : {$get_invoice['Volume']} 
آیدی عددی کاربر : <code>{$get_invoice['id_user']}</code>
شماره تلفن کاربر : {$Balance_id['number']}
موقعیت سرویس کاربر :{$get_invoice['Service_location']}
موجودی کاربر : $balanceformatsell  تومان
کد پیگیری: $randomString
        
            اطلاعات کاربر 👇👇
            ⚜️ نام کاربری کاربر: @{$Balance_id['username']}";
        if (strlen($setting['Channel_Report']) > 0) {
            telegram('sendmessage',[
        'chat_id' => $setting['Channel_Report'],
        'text' => $text_report,
        'parse_mode' => "HTML"
        ]);
        }
        update("invoice","status","active","username",$get_invoice['username']);
        if($Payment_report['Payment_Method'] == "cart to cart"){
        update("invoice","Status","active","id_invoice",$get_invoice['id_invoice']);
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "سفارش تایید شد",
            'show_alert' => true,
            'cache_time' => 5,
        )
        );
        }
    }else {
        $Balance_confrim = intval($Balance_id['Balance']) + intval($Payment_report['price']);
        update("user","Balance",$Balance_confrim, "id",$Payment_report['id_user']);
        update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
        $Payment_report['price'] = number_format($Payment_report['price'], 0);
        $format_price_cart = $Payment_report['price'];
        if($Payment_report['Payment_Method'] == "cart to cart"){
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "سفارش تایید شد",
            'show_alert' => true,
            'cache_time' => 5,
        )
        );
        }
        sendmessage($Payment_report['id_user'], "💎 کاربر گرامی مبلغ {$Payment_report['price']} تومان به کیف پول شما واریز گردید با تشکراز پرداخت شما.
                
🛒 کد پیگیری شما: {$Payment_report['id_order']}", null, 'HTML');
}
}
function savedata($type,$namefiled,$valuefiled){
    global $from_id;
    if($type == "clear"){
        $datauser = [];
        $datauser[$namefiled] = $valuefiled;
        $data = json_encode($datauser);
        update("user","Processing_value",$data,"id",$from_id);
    }elseif($type == "save"){
        $userdata = select("user","*","id",$from_id,"select");
        $dataperevieos = json_decode($userdata['Processing_value'],true);
        $dataperevieos[$namefiled] = $valuefiled;
        update("user","Processing_value",json_encode($dataperevieos),"id",$from_id);
    }
}