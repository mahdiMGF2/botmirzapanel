<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');
require_once '../config.php';
require_once '../botapi.php';
require_once '../functions.php';
if(!is_file('info'))return;
if(!is_file('users.json'))return;


$userid = json_decode(file_get_contents('users.json'));
$info = json_decode(file_get_contents('info'),true);
$count = 0;
if(count($userid) == 0){
    if(isset($info['id_admin'])){
    sendmessage($info['id_admin'], "📌 پیام برای تمامی کاربران ارسال گردید.", null, 'HTML');
    unlink('info');
    }
    return;
    
}
foreach ($userid as $iduser){
        if($count == 20)break;
            sendmessage($iduser->id, $info['text'], null, 'HTML');
        unset($userid[0]);
        $userid = array_values($userid);
        $count +=1;
}
file_put_contents('users.json',json_encode($userid,true));