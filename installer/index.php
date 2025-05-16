<?php
$admin_id =  htmlspecialchars($_POST['userid'], ENT_QUOTES, 'UTF-8');
$token = htmlspecialchars($_POST['Token'], ENT_QUOTES, 'UTF-8');
$usernamedb = htmlspecialchars($_POST['usernamedb'], ENT_QUOTES, 'UTF-8');
$passworddb = htmlspecialchars($_POST['passworddb'], ENT_QUOTES, 'UTF-8');
$hostdb = "localhost";
$namedb = htmlspecialchars($_POST['namedb'], ENT_QUOTES, 'UTF-8');
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$parsedUrl = parse_url($referer);
$domain = dirname(dirname($parsedUrl['host'] . (isset($parsedUrl['path']) ? $parsedUrl['path'] : '')));
$valid_form = true;

if(strlen($token) != 0){
if (!is_numeric($admin_id)) {
    $error = "آیدی عددی ادمین نامعتبر است";
    $valid_form = false;
}
$get_webhook_info = file_get_contents("https://api.telegram.org/bot".$token."/getme");
if($get_webhook_info == false){
    $error = "توکن صحیح نمی باشد";
    $valid_form = false;
}
$get_webhook_info = json_decode($get_webhook_info,true);
if(!$get_webhook_info['ok']){
    $error = "خطا در دریافت اطلاعات";
    $valid_form = false;
}
try {
    $dsn = "mysql:host=$hostdb;dbname=$namedb;charset=utf8mb4";
    $pdo = new PDO($dsn, $_POST['usernamedb'], $_POST['passworddb']);
    $success =  "✅ اتصال موفق بود!";
} catch (\PDOException $e) {
    $error =  "❌ اتصال برقرار نشد: " . $e->getMessage();
    $valid_form = false;
}
if($valid_form){
    $success =  "✅ ربات با موفقیت نصب شد !";
    file_get_contents("https://api.telegram.org/bot".$token."/sendmessage?chat_id=$admin_id&text=✅ ربات با موفقیت نصب گردید.");
    $config_file = file_get_contents('../config.php');
    $set_data = str_replace('"domain.com/bot"',"\"$domain\"",$config_file);
    $set_data = str_replace('"marzbaninfobot"',"\"{$get_webhook_info['result']['username']}\"",$set_data);
    $set_data = str_replace('**TOKEN**',$token,$set_data);
    $set_data = str_replace('"5522424631"',"\"$admin_id\"",$set_data);
    $set_data = str_replace('"username"',"\"$usernamedb\"",$set_data);
    $set_data = str_replace('"password"',"\"$passworddb\"",$set_data);
    $set_data = str_replace('"databasename"',"\"$namedb\"",$set_data);
    file_put_contents('../config.php',$set_data);
    file_get_contents("https://api.telegram.org/bot".$token."/setwebhook?url=https://$domain/index.php");
    file_get_contents("https://$domain/table.php");
    unlink('index.php');
    unlink('style.css');
}
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب ربات میرزا</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>نصب ربات میرزا</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($warning)): ?>
            <div class="alert warning-message">
                <?php echo $warning; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($critical_warning)): ?>
            <div class="alert warning-message critical-warning">
                <?php echo $critical_warning; ?>
            </div>
        <?php endif; ?>
            <form id="installer-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="userid">آیدی عددی ادمین:</label>
                    <input type="text" id="userid" name="userid" 
                           placeholder="id admin" value = "<?php echo $admin_id; ?>" required>
                </div>
                <div class="form-group">
                    <label for="Token">توکن ربات تلگرام :</label>
                    <input type="text" id="Token" name="Token" 
                           placeholder="Token bot" value = "<?php echo $token; ?>" required>
                </div>
                <div class="form-group">
                    <label for="usernamedb">نام کاربری دیتابیس :</label>
                    <input type="text" id="usernamedb" name="usernamedb" 
                           placeholder="username database" value = "<?php echo $usernamedb; ?>" required>
                </div>
                <div class="form-group">
                    <label for="passworddb">رمز عبور  دیتابیس :</label>
                    <input type="text" id="passworddb" name="passworddb" 
                           placeholder="password database" value = "<?php echo $passworddb; ?>" required>
                </div>
                <div class="form-group">
                    <label for="passworddb">نام دیتابیس :</label>
                    <input type="text" id="namedb" name="namedb" 
                           placeholder="name database" value = "<?php echo $namedb; ?>" required>
                </div>
                
                <button type="submit" id="submit-btn">نصب ربات</button>
            </form>
        <footer>
            <p>&copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
</body>
</html>