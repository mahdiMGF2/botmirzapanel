    <?php
// کرون جاب هر 1 روز تنظیم شود
require_once 'config.php';
require_once 'apipanel.php';
require_once 'botapi.php';
#-------------[  Notification to the user ]-------------#
$list_service = mysqli_query($connect, "SELECT * FROM invoice");
while ($row = mysqli_fetch_assoc($list_service)) {
    $marzban_list_get = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM marzban_panel WHERE name_panel = '{$row['Service_location']}'"));
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $get_username_Check = getuser($row['username'], $Check_token['access_token'], $marzban_list_get['url_panel']);
    if(isset($get_username_Check['status'])){
    $timeservice = $get_username_Check['expire'] - time();
    $day = floor($timeservice / 86400) + 1 ;
    $output =  $get_username_Check['data_limit'] - $get_username_Check['used_traffic'];
        $RemainingVolume =formatBytes($output);
    if($output <= 1073741824 && $output>0 &&  isset($get_username_Check['data_limit']) && $get_username_Check['status'] == "active"){
        $text = "
⭕️ کاربر گرامی از حجم  سرویس تان  $RemainingVolume مانده است

نام کاربری : <code>{$row['username']}</code>
نام سرویس : {$row['Service_location']}
";
sendmessage($row['id_user'], $text, null,'HTML');
    }
    if($timeservice <= "167000" && $timeservice >0){
        $text = "
⭕️ کاربر گرامی به پایان زمان سرویس تان $day روز مانده  است در صورت تمدید نکردن تا 72 ساعت معلق می ماند و پس از آن سرویس تان حذف خواهد شد.

نام کاربری : <code>{$row['username']}</code>
نام سرویس : {$row['Service_location']}
";
sendmessage($row['id_user'], $text, null,'HTML');
    }
if($day == "-3"){
    removeuser($Check_token['access_token'], $marzban_list_get['url_panel'], $row['username']);
    $stmt = $connect->prepare("DELETE FROM invoice WHERE username = ? ");
    $stmt->bind_param("s", $row['username']);
    $stmt->execute();
    }
    }
}
#-------------[  Notification to the user ]-------------#

