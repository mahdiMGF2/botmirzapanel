<?php
$Authority = htmlspecialchars($_GET['Authority'], ENT_QUOTES, 'UTF-8');
$StatusPayment = htmlspecialchars($_GET['Status'], ENT_QUOTES, 'UTF-8');
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$Pathfile = dirname(dirname($_SERVER['PHP_SELF'], 2));
$Pathfiles = $rootPath.$Pathfile;
$Pathfile = $Pathfiles.'/config.php';
$jdf = $Pathfiles.'/jdf.php';
$botapi = $Pathfiles.'/botapi.php';
require_once $Pathfile;
require_once $jdf;
require_once $botapi;
$PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'merchant_id'"));
$Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE dec_not_confirmed = '$Authority' LIMIT 1"));
    if($StatusPayment == "OK"){
        $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.zarinpal.com/pg/v4/payment/verify.json',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Accept: application/json'
  ),
));
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
  "merchant_id" => $PaySetting['ValuePay'],
  "amount"=> $Payment_report['price'],
  "authority" => $Authority,
        ]));
$response = curl_exec($curl);
curl_close($curl);
$response = json_decode($response,true);
 } 
       $payment_status = [
			"-9" => "خطا در ارسال داده",
			"-10" => "ای پی یا مرچنت كد پذیرنده صحیح نیست.",
			"-11" => "مرچنت کد فعال نیست،",
			"-12" => "تلاش بیش از دفعات مجاز در یک بازه زمانی کوتاه",
			"-15" => "درگاه پرداخت به حالت تعلیق در آمده است",
			"-16" => "سطح تایید پذیرنده پایین تر از سطح نقره ای است.",
			"-17" => "محدودیت پذیرنده در سطح آبی",
			"-30" => "پذیرنده اجازه دسترسی به سرویس تسویه اشتراکی شناور را ندارد.",
			"-31" => "حساب بانکی تسویه را به پنل اضافه کنید. مقادیر وارد شده برای تسهیم درست نیست. پذیرنده جهت استفاده از خدمات سرویس تسویه اشتراکی شناور، باید حساب بانکی معتبری به پنل کاربری خود اضافه نماید.",
			"-32" => "مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.",
			"-33" => "درصدهای وارد شده صحیح یست.",
			"-34" => "مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.",
			"-35" => "تعداد افراد دریافت کننده تسهیم بیش از حد مجاز است.",
			"-36" => "حداقل مبلغ جهت تسهیم باید ۱۰۰۰۰ ریال باشد",
			"-37" => "یک یا چند شماره شبای وارد شده برای تسهیم از سمت بانک غیر فعال است.",
			"-38" => "خطا٬عدم تعریف صحیح شبا٬لطفا دقایقی دیگر تلاش کنید.",
			"-39" => "	خطایی رخ داده است",
			"-40" => "",
			"-50" => "مبلغ پرداخت شده با مقدار مبلغ ارسالی در متد وریفای متفاوت است.",
			"-51" => "پرداخت ناموفق",
			"-52" => "	خطای غیر منتظره‌ای رخ داده است. ",
			"-53" => "پرداخت متعلق به این مرچنت کد نیست.",
			"-54" => "اتوریتی نامعتبر است.",
    ][$response['errors']['code']];
 if($response['data']['message'] == "Verified" || $response['data']['message'] == "Paid"){
    $price = $Payment_report['price'];
    $dec_payment_status = "از انجام تراکنش متشکریم!";
    if($Payment_report['payment_Status'] != "paid"){
    $Balance_id = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM user WHERE id = '{$Payment_report['id_user']}' LIMIT 1"));
    $stmt = $connect->prepare("UPDATE user SET Balance = ? WHERE id = ?");
    $Balance_confrim = intval($Balance_id['Balance']) + $price;
    $stmt->bind_param("ss", $Balance_confrim, $Payment_report['id_user']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE Payment_report SET payment_Status = ? WHERE id_order = ?");
    $Status_change = "paid";
    $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
    $stmt->execute();
    $stmt = $connect->prepare("UPDATE Payment_report SET dec_not_confirmed = ? WHERE id_order = ?");
    $Status_change = null;
    $stmt->bind_param("ss", $Status_change, $Payment_report['id_order']);
    $stmt->execute();
    sendmessage($Payment_report['id_user'],"💎 کاربر گرامی مبلغ $price تومان به کیف پول شما واریز گردید با تشکر از پرداخت شما.
    
    🛒 کد پیگیری شما: {$Payment_report['id_order']}",$keyboard,'HTML');
    deletemessage($from_id, $message_id);
    $setting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
$text_report = "💵 پرداخت جدید
        
آیدی عددی کاربر : $from_id
مبلغ تراکنش $price
روش پرداخت :  درگاه زرین پال";
    if (strlen($setting['Channel_Report']) > 0) {
        sendmessage($setting['Channel_Report'], $text_report, null, 'HTML');
    }
 }
 }
 else{
     $dec_payment_status = "";
 }
?>
<html>
<head>
    <title>فاکتور پرداخت</title>
    <style>
    @font-face {
    font-family: 'vazir';
    src: url('/Vazir.eot');
    src: local('☺'), url('../fonts/Vazir.woff') format('woff'), url('../fonts/Vazir.ttf') format('truetype');
}

        body {
            font-family:vazir;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .confirmation-box {
            background-color: #ffffff;
            border-radius: 8px;
            width:25%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #333333;
            margin-bottom: 20px;
        }

        p {
            color: #666666;
            margin-bottom: 10px;
        }
        .btn{
            display:block;
            margin : 10px 0;
            padding:10px 20px;
            background-color:#49b200;
            color:#fff;
            text-decoration :none;
            border-radius:10px;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h1><?php echo $payment_status ?></h1>
        <p>شماره تراکنش:<span><?php echo $Payment_report['id_order'] ?></span></p>
        <p>مبلغ پرداختی:  <span><?php echo $Payment_report['price'] ?></span>تومان</p>
        <p>تاریخ: <span>  <?php echo jdate('Y/m/d')  ?>  </span></p>
        <p><?php echo $dec_payment_status ?></p>
        <a class = "btn" href = "https://t.me/<?php echo $usernamebot ?>">بازگشت به ربات</a>
    </div>
</body>
</html>
