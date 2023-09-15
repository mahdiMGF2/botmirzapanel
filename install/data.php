<?php
$token = $_POST['tokenbot'];
$idadmin = $_POST['idadmin'];
$dbname = $_POST['dbname'];
$dbuser = $_POST['dbuser'];
$idbot = $_POST['idbot'];
$passworddb = $_POST['dbpassword'];
$domain = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['REQUEST_URI'],2);
$domain_hosts = $domain . $path;
$fileContent = file_get_contents('../config.php');
// تغییر مقدار $idbot
$patternidbot = '/\$usernamebot\s*=\s*".*?";/';
$newFileContent = preg_replace($patternidbot, '$usernamebot = "'.$idbot.'";', $fileContent);

// تغییر مقدار $domainhost
$patterndomain = '/\$domainhosts\s*=\s*".*?";/';
$newFileContent = preg_replace($patterndomain, '$domainhosts = "'.$domain_hosts.'";', $newFileContent);

// تغییر مقدار $dbname
$patterndbname = '/\$dbname\s*=\s*".*?";/';
$newFileContent = preg_replace($patterndbname, '$dbname = "'.$dbname.'";', $newFileContent);

// تغییر مقدار $dbuser
$patternuser = '/\$usernamedb\s*=\s*".*?";/';
$newFileContent = preg_replace($patternuser, '$usernamedb = "'.$dbuser.'";', $newFileContent);
// تغییر مقدار $dbdbpassword
$patternpass = '/\$passworddb\s*=\s*".*?";/';
$newFileContent = preg_replace($patternpass, '$passworddb = "'.$passworddb.'";', $newFileContent);

// تغییر مقدار $API_KEY
$patterntoken = '/\$APIKEY\s*=\s*".*?";/';
$newFileContent = preg_replace($patterntoken, '$APIKEY = "'.$token.'";', $newFileContent);

// تغییر مقدار $adminnumber
$patternidadmin = '/\$adminnumber\s*=\s*".*?";/';
$newFileContent = preg_replace($patternidadmin, '$adminnumber = "'.$idadmin.'";', $newFileContent);

file_put_contents('../config.php', $newFileContent);
try {
    $connect = new mysqli('localhost', $dbuser, $passworddb, $dbname);
    
    if ($connect->connect_errno) {
        $textdatabase = 'خطا در اتصال به پایگاه داده: ' . $connect->connect_error;
    } else {
        $textdatabase = "ارتباط به دیتابیس برقرارشد و جداول ساخته شدند";
        require_once 'table.php';
                $connect->close();
    }
} catch (Exception $e) {
    $textdatabase = 'خطا در اتصال به پایگاه داده: ' . $e->getMessage();
}
$delete = json_decode(file_get_contents("https://api.telegram.org/bot" . $token . "setWebhook?remove" ),true);
$response = json_decode(file_get_contents("https://api.telegram.org/bot" . $token . "/setWebhook?url=https://" .$domain_hosts."/index.php" ),true);
if($response['description'] == "Webhook was set"){
            $sendmessage =file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $idadmin . "&text=✅| ربات میرزا پنل با موفقیت نصب شد");
            $webhook = "ست وبهوک با موفقیت تنظیم شد";
        }
        elseif($response['description'] == "Webhook is already set"){
            $webhook = "ست وبهوک از قبل انجام شده بود";
        }
        else{
            $webhook = "ست وبهوک ربات با موفقتیت انجام نشد.";
        }
   unlink('data.php');
?>

<html>
<head>
    <title>نصب ربات</title>
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

        .installbox {
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

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      width:100%;
      text-align:right;
      direction:rtl;
    }
    
    .form-container {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
      text-align:center;
    }
    
    .form-container h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    </style>
</head>
<body>
    <?php if(!file_exists("../index.php") || !file_exists("table.php") ||!file_exists("../config.php")){ ?>
    <div class="installbox">
       <p>فایل های مورد نیاز ربات یافت نشد</p>
    </div>
    <?php
}
else{
?>
 <div class="container">
    <div class="form-container">
      <h2>اطلاعات با موفقیت ثبت گردید</h2>
      <h3><?php echo $textdatabase ?></h3>
      <h3><?php echo $webhook ?></h3>
    </div>
  </div>
    <?php
}
?>
</body>
</html>
