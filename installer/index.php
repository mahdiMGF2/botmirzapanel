<?php

$uPOST = sanitizeInput($_POST);
$rootDirectory = dirname(__DIR__).'/';

$configDirectory = $rootDirectory.'config.php';
$tablesDirectory = $rootDirectory.'table.php';
if(!file_exists($configDirectory) || !file_exists($tablesDirectory)) {
    $ERROR[] = "فایل های پروژه ناقص هستند.";
    $ERROR[] = "فایل های پروژه را مجددا دانلود و بارگذاری کنید (<a href='https://github.com/mahdiMGF2/botmirzapanel'>‎🌐 Github</a>)";
}
if(phpversion() < 8.2){
    $ERROR[] = "نسخه PHP شما باید حداقل 8.2 باشد.";
    $ERROR[] = "نسخه فعلی: ".phpversion();
    $ERROR[] = "لطفا نسخه PHP خود را به 8.2 یا بالاتر ارتقا دهید.";
}

if(!empty($_SERVER['SCRIPT_URI'])) {
    $URI = str_replace($_SERVER['REQUEST_SCHEME'].'://','',$_SERVER['SCRIPT_URI']);
    if(basename($URI) == 'index.php') {
        $URI = (dirname($URI));
    }
    $webAddress = (dirname($URI)).'/';
}
else {
    $webAddress = $_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['SCRIPT_NAME']));
}

if(isset($uPOST['submit']) && $uPOST['submit']) {

    $ERROR = [];
    $SUCCESS[] = "✅ ربات با موفقیت نصب شد !";
    $rawConfigData = file_get_contents($configDirectory);

    $tgAdminId = $uPOST['admin_id'];
    $tgBotToken = $uPOST['tg_bot_token'];
    $dbInfo['host'] = 'localhost';
    $dbInfo['name'] = $uPOST['database_name'];
    $dbInfo['username'] = $uPOST['database_username'];
    $dbInfo['password'] = $uPOST['database_password'];
    $document['address'] = dirname($uPOST['bot_address_webhook']);

    if($_SERVER['REQUEST_SCHEME'] != 'https') {
        $ERROR[] = 'برای فعال سازی ربات تلگرام نیازمند فعال بودن SSL (https) هستید';
        $ERROR[] = '<i>اگر از فعال بودن SSL مطمئن هستید آدرس صفحه را چک کنید، حتما با https صفحه را باز کنید.</i>';
        $ERROR[] = '<a href="https://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['SCRIPT_NAME'].'">https://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['SCRIPT_NAME'].'</a>';
    }

    $isValidToken = isValidTelegramToken($tgBotToken);
    if(!$isValidToken) {
        $ERROR[] = "توکن ربات صحیح نمی باشد.";
    }

    if (!isValidTelegramId($tgAdminId)) {
        $ERROR[] = "آیدی عددی ادمین نامعتبر است.";
    }

    if($isValidToken) {
        $tgBot['details'] = getContents("https://api.telegram.org/bot".$tgBotToken."/getMe");
        if($tgBot['details']['ok'] == false) {
            $ERROR[] = "توکن ربات را بررسی کنید. <i>عدم توانایی دریافت جزئیات ربات.</i>";
        }
        else {
            $tgBot['recognitionion'] = getContents("https://api.telegram.org/bot".$tgBotToken."/getChat?chat_id=".$tgAdminId);
            if($tgBot['recognitionion']['ok'] == false) {
                $ERROR[] = "<b>عدم شناسایی مدیر ربات:</b>";
                $ERROR[] = "ابتدا ربات را فعال/استارت کنید با اکانت که میخواهید مدیر اصلی ربات باشد.";
                $ERROR[] = "<a href='https://t.me/'".$tgBot['details']['result']['username'].">@".$tgBot['details']['result']['username']."</a>";
            }
        }
    }


    try {
        $dsn = "mysql:host=" . $dbInfo['host'] . ";dbname=" . $dbInfo['name'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $dbInfo['username'], $dbInfo['password']);
        $SUCCESS[] = "✅ اتصال به دیتابیس موفقیت آمیز بود!";
    }
    catch (\PDOException $e) {
        $ERROR[] = "❌ عدم اتصال به دیتابیس: ";
        $ERROR[] = "اطلاعات ورودی را بررسی کنید.";
        $ERROR[] = "<code>".$e->getMessage()."</code>";
    }

    if(empty($ERROR)) {
        $replacements = [
            '{DOMAIN.COM/PATH/BOT}' => $document['address'],
            '{BOT_USERNAME}' => $tgBot['details']['result']['username'],
            '{BOT_TOKEN}' => $tgBotToken,
            '{ADMIN_#ID}' => $tgAdminId,
            '{DATABASE_USERNAME}' => $dbInfo['username'],
            '{DATABASE_PASSOWRD}' => $dbInfo['password'],
            '{DATABASE_NAME}' => $dbInfo['name']
        ];

        $newConfigData = str_replace(array_keys($replacements),array_values($replacements),$rawConfigData,$count);
        if(file_put_contents($configDirectory,$newConfigData) === false || $count == 0) {
            $ERROR[] = '✏️❌ خطا در زمان بازنویسی اطلاعات فایل اصلی ربات';
            $ERROR[] = "فایل های پروژه را مجددا دانلود و بارگذاری کنید (<a href='https://github.com/mahdiMGF2/botmirzapanel'>‎🌐 Github</a>)";
        }
        else {
            getContents("https://api.telegram.org/bot".$tgBotToken."/setwebhook?url=https://".$document['address'].'/index.php');
            getContents("https://".$document['address']."/table.php");
            $botFirstMessage = "\n[🤖] شما به عنوان ادمین معرفی شدید.";
            getContents('https://api.telegram.org/bot'.$tgBotToken.'/sendMessage?chat_id='.$tgAdminId.'&text='.urlencode(' '.$SUCCESS[0].$botFirstMessage).'&reply_markup={"inline_keyboard":[[{"text":"⚙️ شروع ربات، رفتن به تنظیمات بخش ادمین","callback_data":"PANEL"}]]}');
        }

    }
}

?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚙️ نصب خودکار ربات میرزا</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>⚙️ نصب خودکار ربات میرزا</h1>
        
        <?php if (!empty($ERROR)): ?>
            <div class="alert alert-danger">
                <?php echo implode("<br>",$ERROR); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($SUCCESS) && empty($ERROR)): ?>
            <div class="alert alert-success">
                <?php echo implode("<br>",$SUCCESS); ?>
            </div>
            <a class="submit-success" href="https://t.me/<?php echo $tgBot['details']['result']['username']; ?>">🤖 رفتن به ربات <?php echo "‎@".$tgBot['details']['result']['username']; ?> »</a>
        <?php endif; ?>
            
            <form id="installer-form" <?php if(isset($botFirstMessage)) { echo 'style="display:none;"'; } ?> method="post">
                <div class="form-group">
                    <label for="admin_id">آیدی عددی ادمین:</label>
                    <input type="text" id="admin_id" name="admin_id" 
                           placeholder="ADMIN TELEGRAM #Id" value="<?php echo $uPOST['admin_id'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="tg_bot_token">توکن ربات تلگرام :</label>
                    <input type="text" id="tg_bot_token" name="tg_bot_token" 
                           placeholder="BOT TOKEN" value="<?php echo $uPOST['tg_bot_token'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="database_username">نام کاربری دیتابیس :</label>
                    <input type="text" id="database_username" name="database_username" 
                           placeholder="DATABASE USERNAME" value="<?php echo $uPOST['database_username'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="database_password">رمز عبور  دیتابیس :</label>
                    <input type="text" id="database_password" name="database_password" 
                           placeholder="DATABASE PASSOWRD" value="<?php echo $uPOST['database_password'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="database_name">نام دیتابیس :</label>
                    <input type="text" id="database_name" name="database_name" 
                           placeholder="DATABASE NAME" value="<?php echo $uPOST['database_name'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <details>
                        <summary for="secret_key"><i>آدرس سورس ربات</i></summary>
                        <label for="bot_address_webhook ">آدرس صفحه سورس ربات</label>
                        <input type="text" id="bot_address_webhook" name="bot_address_webhook" placeholder="Web URL for Set Webhook" value="<?php echo $webAddress.'/index.php'; ?>" required>
                    </details>
                </div>
                <div class="form-group">
                    <label for="remove_directory"><b style="color:#f30;">هشدار:</b> حذف خودکار اسکریپت نصب&zwnj;کننده پس از نصب موفقیت&zwnj;آمیز</label>
                    <label for="remove_directory" style="font-size: 14px;font-weight: normal;text-indent: 20px;">برای امنیت بیشتر، بعد از اتمام نصب ربات پوشه Installer حذف خواهد شد. </label>
                </div>
                
                <button type="submit" name="submit" value="submit">نصب ربات</button>
            </form>
        <footer>
            <p>Mirzabot Installer , Made by ♥️ | <a href="https://github.com/mahdiMGF2/botmirzapanel">Github</a> | <a href="https://t.me/mirzapanel">Telegram</a> | &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
</body>
</html>

<?php 

function getContents($url) {
    return json_decode(file_get_contents($url),true);
}
function isValidTelegramToken($token) {
    return preg_match('/^\d{6,12}:[A-Za-z0-9_-]{35}$/', $token);
}
function isValidTelegramId($id) {
    return preg_match('/^\d{6,12}$/', $id);
}
function sanitizeInput(&$INPUT, array $options = []) {

    $defaultOptions = [
        'allow_html' => false,
        'allowed_tags' => '',
        'remove_spaces' => false,
        'connection' => null,
        'max_length' => 0,
        'encoding' => 'UTF-8'
    ];
    
    $options = array_merge($defaultOptions, $options);
    
    if (is_array($INPUT)) {
        return array_map(function($item) use ($options) {
            return sanitizeInput($item, $options);
        }, $INPUT);
    }
    
    if ($INPUT === null || $INPUT === false) {
        return '';
    }
    
    $INPUT = (string)$INPUT;
    
    $INPUT = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $INPUT);
    
    if ($options['max_length'] > 0) {
        $INPUT = mb_substr($INPUT, 0, $options['max_length'], $options['encoding']);
    }
    
    if (!$options['allow_html']) {
        $INPUT = strip_tags($INPUT);
    } elseif (!empty($options['allowed_tags'])) {
        $INPUT = strip_tags($INPUT, $options['allowed_tags']);
    }
    
    if ($options['remove_spaces']) {
        $INPUT = preg_replace('/\s+/', ' ', trim($INPUT));
    }
    
    $INPUT = htmlspecialchars($INPUT, ENT_QUOTES | ENT_HTML5, $options['encoding']);
    
    if ($options['connection'] instanceof mysqli) {
        $INPUT = $options['connection']->real_escape_string($INPUT);
    }
    
    return $INPUT;
}