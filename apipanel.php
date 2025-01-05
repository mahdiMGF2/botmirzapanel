<?php
require_once 'functions.php';
#-----------------------------#
function token_panel($code_panel){
    $panel = select("marzban_panel","*","id",$code_panel,"select");
    if($panel['datelogin'] != null){
        $date = json_decode($panel['datelogin'],true);
        if(isset($date['time'])){
            $timecurrent = time();
            $start_date = time() - strtotime($date['time']);
            if($start_date <= 600){
                return $date;
            }
        }
    }
    $url_get_token = $panel['url_panel'].'/api/admin/token';
    $data_token = array(
        'username' => $panel['username_panel'],
        'password' => $panel['password_panel']
    );
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT_MS => 6000,
        CURLOPT_POSTFIELDS => http_build_query($data_token),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'accept: application/json'
        )
    );
    $curl_token = curl_init($url_get_token);
    curl_setopt_array($curl_token, $options);
    $token = curl_exec($curl_token);
    if (curl_error($curl_token)) {
        $token = [];
        $token['errror'] = curl_error($curl_token);
        return $token;
    }
    curl_close($curl_token);

    $body = json_decode( $token, true);
    if(isset($body['access_token'])){
        $time = date('Y/m/d H:i:s');
        $data = json_encode(array(
            'time' => $time,
            'access_token' => $body['access_token']
        ));
        update("marzban_panel","datelogin",$data,'name_panel',$panel['name_panel']);
    }
    return $body;
}

#-----------------------------#

function getuser($usernameac,$location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panel($marzban_list_get['id']);
    $url =  $marzban_list_get['url_panel'].'/api/user/' . $usernameac;
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
#-----------------------------#
function ResetUserDataUsage($usernameac,$location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panel($marzban_list_get['id']);
    $url =  $marzban_list_get['url_panel'].'/api/user/' . $usernameac.'/reset';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST , true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
#-----------------------------#
function adduser($username,$expire,$data_limit,$location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panel($marzban_list_get['id']);
    $url = $marzban_list_get['url_panel']."/api/user";
    $header_value = 'Bearer ';
    $data = array(
        "proxies" => json_decode($marzban_list_get['proxies']),
        "data_limit" => $data_limit,
        "username" => $username
    );
    if($marzban_list_get['inbounds'] != null and $marzban_list_get['inbounds'] != "null"){
        $data['inbounds'] = json_decode($marzban_list_get['inbounds'],true);
    }
    if($expire == "0"){
        $data['expire'] = 0;
    }else {
        if($marzban_list_get['onholdstatus'] == "ononhold"){
            $data["expire"] = 0;
            $data["status"] = "on_hold";
            $data["on_hold_expire_duration"] = $expire - time();
        }else{
            $data['expire'] = $expire;
        }
    }
    $payload = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $Check_token['access_token'],
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
//----------------------------------
function Get_System_Stats($location){
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panel($marzban_list_get['id']);
    $url =  $marzban_list_get['url_panel'].'/api/system';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $Check_token['access_token'],
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $Get_System_Stats = json_decode($output, true);
    return $Get_System_Stats;
}
//----------------------------------
function removeuser($location,$username)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panel($marzban_list_get['id']);
    $url =  $marzban_list_get['url_panel'].'/api/user/'.$username;
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
//----------------------------------
function Modifyuser($location,$username,array $data)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panel($marzban_list_get['id']);
    $url =  $marzban_list_get['url_panel'].'/api/user/'.$username;
    $payload = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Authorization: Bearer '.$Check_token['access_token'];
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($result, true);
    return $data_useer;
}

#-----------------------------------------------#
function revoke_sub($username,$location)
{
    global $connect;
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panel($marzban_list_get['id']);
    $usernameac = $username;
    $url =  $marzban_list_get['url_panel'].'/api/user/' . $usernameac.'/revoke_sub';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST , true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $Check_token['access_token']
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
