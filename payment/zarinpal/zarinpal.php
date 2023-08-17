<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$Pathfile = dirname(dirname($_SERVER['PHP_SELF'], 2));
$Pathfile = $rootPath.$Pathfile;
$Pathfile = $Pathfile.'/config.php'; 
$price = $_GET['price'];
$order_id = $_GET['order_id'];
require_once $Pathfile;
$PaySetting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT (ValuePay) FROM PaySetting WHERE NamePay = 'merchant_id'"));
$Payment_report = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_order = '$order_id'"));
if($Payment_report['price'] != $price){
 echo "مبلغ نامعتبر"; 
 return;
}
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.zarinpal.com/pg/v4/payment/request.json',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Accept: application/json'
  ),
));
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
  "merchant_id" => $PaySetting['ValuePay'],
  "currency" => "IRT",
  "amount"=> $price,
  "callback_url" =>"https://$domainhosts/payment/zarinpal/back.php",
  "description" => "payment",
  "metadata" => array(
      "order_id" => $order_id
      )
        ]));
$response = curl_exec($curl);
curl_close($curl);
$response = json_decode($response,true);
if($response['data']['code'] == "100"){
    header('Location: https://www.zarinpal.com/pg/StartPay/'.$response['data']['authority']);
    $stmt = $connect->prepare("UPDATE Payment_report SET dec_not_confirmed = ? WHERE id_order = ?");
    $confrim = $response['data']['authority'];
    $stmt->bind_param("ss", $confrim, $order_id);
    $stmt->execute();
}
else{
        $status_payment = [
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
    echo $status_payment;
}
