<?php
// کرون جاب هر 5 دقیقه تنظیم شود
require_once 'config.php';
require_once 'apipanel.php';
require_once 'botapi.php';
#-------------[ Remove the test user if the user is inactive ]-------------#
$query = "SELECT * FROM TestAccount";
$result = mysqli_query($connect, $query);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
foreach($rows as $row) {
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$row['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $get_username_Check = getuser($row['username'], $Check_token['access_token'], $marzban_list_get['url_panel']);
    if(isset($get_username_Check['status'])){
    if ($get_username_Check['status'] != "active" && isset($get_username_Check['status'])) {
            sendmessage($row['id_user'], "⭕️ کاربر عزیز کانفیگ تست شما با نام کاربری {$row['username']}  حذف شد⭕️ ", null,'HTML');
        removeuser($Check_token['access_token'], $marzban_list_get['url_panel'], $row['username']);
    }
    }
}
#-------------[ Remove the test user if the user is inactive ]-------------#

