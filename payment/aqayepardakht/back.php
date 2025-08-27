<?php
// --- bootstrap / includes ---
$rootPath  = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
$PHP_SELF  = filter_input(INPUT_SERVER, 'PHP_SELF');
$Pathfile  = dirname(dirname($PHP_SELF, 2));
$Pathfiles = $rootPath.$Pathfile;

require_once $Pathfiles.'/config.php';
require_once $Pathfiles.'/jdf.php';
require_once $Pathfiles.'/botapi.php';
require_once $Pathfiles.'/functions.php';
require_once $Pathfiles.'/panels.php';
require_once $Pathfiles.'/text.php';

// --- helper: safe getter from JSON or POST ---
function input_array(): array {
    // اگر Content-Type صراحتاً JSON است یا بدنه‌ی خام JSON معتبر باشد، از JSON بخوان
    $ctype = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    $raw   = file_get_contents('php://input');
    $asJson = false;
    if (stripos($ctype, 'application/json') !== false) {
        $asJson = true;
    } else {
        // حتی اگر هدر درست نبود ولی بدنه JSON معتبر بود، باز هم JSON در نظر بگیر
        $test = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($test)) {
            $asJson = true;
        }
    }
    if ($asJson) {
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    // فallback: فرم معمولی
    return $_POST ?? [];
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$in = input_array();

// نام فیلدها را از ورودی بگیر (JSON یا POST)
$invoice_id = h($in['invoice_id'] ?? $in['hashid'] ?? ''); // اگر جای دیگر "hashid" می‌فرستی این هم پوشش می‌دهد
$transid    = h($in['authority']    ?? '');

// اگر لازم داری روی مقدارها اعتبارسنجی داشته باشی:
if ($invoice_id === '' || $transid === '') {

}

// ادامه‌ی منطق قبلی
$PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht","select")['ValuePay'] ?? '';
$price      = select("Payment_report", "price", "id_order", $invoice_id,"select")['price'] ?? '';
$ManagePanel = new ManagePanel();

// --- verify transaction (ارسال درخواست JSON) ---
$payload = [
    'ApiKey'     => $PaySetting,
    'authority' => $transid,
];
$data = json_encode($payload, JSON_UNESCAPED_UNICODE);

$ch = curl_init('https://tetra98.ir/api/verify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data),
]);
$result = curl_exec($ch);
if ($result === false) {
    // اختیاری: لاگ خطای cURL
    $err = curl_error($ch);
}
curl_close($ch);

$result = json_decode($result, true); // آرایه برگردان

if (isset($result['status']) && (int)$result['status'] === 100) {
    $setting         = select("setting", "*");
    $payment_status  = $textbotlang['users']['moeny']['payment_success'];
    $dec_payment_status = $textbotlang['users']['moeny']['payment_success_dec'];
    $Payment_report  = select("Payment_report", "*", "id_order", $invoice_id,"select");
    $Balance_id      = select("user", "*", "id", $Payment_report['id_user'], "select");

    if (($Payment_report['payment_Status'] ?? '') !== "paid") {
        DirectPayment($Payment_report['id_order']);
        update("user","Processing_value","0", "id",$Balance_id['id']);
        update("user","Processing_value_one","0", "id",$Balance_id['id']);
        update("user","Processing_value_tow","0", "id",$Balance_id['id']);
        update("Payment_report","payment_Status","paid","id_order",$Payment_report['id_order']);
        if (!empty($setting['Channel_Report'])) {
            sendmessage($setting['Channel_Report'], sprintf($textbotlang['Admin']['Report']['aqayepardakht'],$Payment_report['id_user'],$price), null, 'HTML');
        }
    }
} else {
    // مپ کردن کدهای خطا
    $map = [
        '0' => "پرداخت انجام نشد",
        '2' => "تراکنش قبلا وریفای و پرداخت شده است",
    ];
    $code = isset($result->code) ? (string)$result->code : '0';
    $payment_status    = $map[$code] ?? "خطا در وریفای تراکنش";
    $dec_payment_status = "";
}

// خروجی HTML (مثل قبل)
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>پرداخت موفق بود | هدایت به ربات</title>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=vazirmatn:400,600,800" rel="stylesheet" />
  <style>
    :root{
      --bg1:#10b981; --bg2:#0ea5e9; --ink:#0f172a; --muted:#64748b;
      --card:#ffffffee; --glass:rgba(255,255,255,.08); --bd:rgba(15,23,42,.08);
      --btn:#10b981; --btn-ink:#0b1320;
    }
    *{box-sizing:border-box; font-family:'Vazirmatn', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif}
    html,body{height:100%}
    body{
      margin:0;
      color:var(--ink);
      background:
        radial-gradient(1000px 520px at 100% -10%, rgba(16,185,129,.25), transparent 60%),
        radial-gradient(900px 500px at -10% 110%, rgba(14,165,233,.25), transparent 60%),
        linear-gradient(135deg, #f8fafc, #eef2ff 60%);
      display:grid;
      place-items:center;
      padding:24px;
    }
    .card{
      width:min(680px, 92vw);
      background:var(--card);
      border:1px solid var(--bd);
      box-shadow: 0 10px 30px rgba(2,6,23,.10);
      border-radius:24px;
      padding:28px;
      text-align:center;
      backdrop-filter:saturate(160%) blur(6px);
    }
    .check{
      width:88px; height:88px; margin:8px auto 18px;
      display:grid; place-items:center; border-radius:50%;
      background: radial-gradient(circle at 30% 30%, #22c55e, #16a34a);
      box-shadow: 0 10px 22px rgba(16,185,129,.35), inset 0 0 0 6px #ffffffaa;
    }
    .check svg{width:46px; height:46px; color:#fff}
    h1{margin:0 0 8px; font-size:26px; font-weight:800; letter-spacing:-.2px}
    p.lead{margin:0 0 18px; font-size:16px; color:var(--muted)}
    .cta{
      display:inline-flex; align-items:center; justify-content:center; gap:10px;
      min-width:220px; padding:14px 18px; border-radius:14px;
      background:linear-gradient(180deg, #34d399, #10b981);
      color:#ffffff; font-weight:700; text-decoration:none; font-size:15px;
      box-shadow:0 10px 18px rgba(16,185,129,.25);
      transition:transform .1s ease, box-shadow .1s ease, filter .2s;
      border:0;
    }
    .cta:hover{transform:translateY(-1px); box-shadow:0 14px 22px rgba(16,185,129,.28)}
    .cta:active{transform:translateY(0)}
    .row{
      display:flex; flex-wrap:wrap; align-items:center; justify-content:center; gap:14px; margin-top:18px;
    }
    .count{
      font-weight:700; font-variant-numeric:tabular-nums;
      display:inline-block; min-width:1.6ch; text-align:center;
    }
    .hint{color:var(--muted); font-size:14px; margin-top:8px}
    .divider{
      width:100%; height:1px; background:linear-gradient(90deg, transparent, var(--bd), transparent);
      margin:18px 0;
    }
    .link{
      color:#0ea5e9; text-decoration:none; font-weight:600;
    }
  </style>
  <!-- فallback برای کاربران بدون جاوااسکریپت -->
  <meta http-equiv="refresh" content="5;url=<?= htmlspecialchars($tg_url, ENT_QUOTES) ?>">
</head>
<body>
  <main class="card" role="main" aria-live="polite">
    <div class="check" aria-hidden="true">
      <!-- تیک سبز -->
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 6L9 17l-5-5"/>
      </svg>
    </div>

    <h1>پرداخت با موفقیت انجام شد</h1>
    <p class="lead">برای بازگشت به ربات روی دکمه زیر کلیک کنید.</p>

    <div class="row">
      <a class="cta" href="https://t.me/<?php echo $usernamebot ?>">
        <!-- آیکن تلگرام -->
        <svg width="18" height="18" viewBox="0 0 240 240" fill="none" aria-hidden="true">
          <circle cx="120" cy="120" r="120" fill="#22c3ff"/>
          <path d="M53 118l120-47c6-2 12 3 10 9l-20 96c-1 7-9 9-14 5l-39-29-21 20c-4 4-11 2-12-4l1-30 82-52-98 42c-6 2-11-6-9-10z" fill="#fff"/>
        </svg>
        <span>بازگشت به ربات</span>
      </a>
    </div>

    <div class="hint" id="auto-redirect">
      هدایت خودکار به ربات در <span class="count" id="sec">3</span> ثانیه…
    </div>

    <div class="divider"></div>

   
  </main>

<script>
  (function(){
    var s = 3;
    var el = document.getElementById('sec');
    var url = "https://t.me/<?php echo $usernamebot ?>";
    var timer = setInterval(function(){
      s = s - 1;
      if (s <= 0) {
        clearInterval(timer);
        window.location.href = url;
      }
      if (el) el.textContent = Math.max(s,0);
    }, 1000);
  })();
</script>

</body>
</html>
