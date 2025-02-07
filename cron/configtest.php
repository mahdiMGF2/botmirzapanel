<?php
date_default_timezone_set('Asia/Tehran');
require_once '../config.php';
require_once '../botapi.php';
require_once '../panels.php';
require_once '../functions.php';
$ManagePanel = new ManagePanel();
$stmt = $pdo->prepare("SELECT * FROM invoice WHERE status = 'active' AND name_product = 'usertest' LIMIT 10");
$stmt->execute();
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $resultt  = trim($result['username']);
    $marzban_list_get = select("marzban_panel","*","name_panel",$result['Service_location'],"select");
    $get_username_Check = $ManagePanel->DataUser($result['Service_location'],$result['username']);
    if (!in_array($get_username_Check['status'],['active','on_hold','Unsuccessful','disabled'])) {
        $ManagePanel->RemoveUser($result['Service_location'],$resultt);
        update("invoice","status","disabled","username",$resultt);
        $Response = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "🛍 خرید سرویس", 'callback_data' => 'buy'],
                ],
            ]
        ]);
        $textexpire = "با سلام خدمت شما کاربر گرامی 
سرویس تست شما با نام کاربری $resultt به پایان رسیده است
امیدواریم تجربه‌ی خوبی از آسودگی و سرعت سرویستون داشته باشین. در صورتی که از سرویس‌ تست خودتون راضی بودین، میتونید سرویس اختصاصی خودتون رو تهیه کنید و از داشتن اینترنت آزاد با نهایت کیفیت لذت ببرید😉🔥
🛍 برای تهیه سرویس با کیفیت می توانید از دکمه زیر استفاده نمایید";
        sendmessage($result['id_user'], $textexpire, $Response, 'HTML');
    }
}