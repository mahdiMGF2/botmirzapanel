<?php
#-----------------------------#
function token_panelm($url_panel,$username_panel,$password_panel){
    $panel = select("marzban_panel","*","url_panel",$url_panel,"select");
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
    $url_get_token = $url_panel.'/api/admins/token';
    $data_token = array(
        'username' => $username_panel,
        'password' => $password_panel
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

function getuserm($username,$location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $usernameac = $username;
    $url =  $marzban_list_get['url_panel'].'/api/users/' . $usernameac;
    if(!isset($Check_token['access_token']))return;
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
function ResetUserDataUsagem($username,$location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $usernameac = $username;
    $url =  $marzban_list_get['url_panel'].'/api/users/' . $usernameac.'/reset';
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
function revoke_subm($username,$location)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $usernameac = $username;
    $url =  $marzban_list_get['url_panel'].'/api/users/' . $usernameac.'/revoke_sub';
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
function adduserm($location,$data_limit,$username_ac,$timestamp)
{
    global $pdo;
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url = $marzban_list_get['url_panel']."/api/users";
    $header_value = 'Bearer ';
    $data = array(
        'service_ids' => json_decode($marzban_list_get['proxies'],true),
        "data_limit" => $data_limit,
        "username" => $username_ac,
    );
    if($marzban_list_get['onholdstatus'] == "offonhold"){
        if ($timestamp == 0) {
            $data["expire_date"] = null;
            $data["expire_strategy"] = "never";
        } else {
            $date = date('c',$timestamp);
            $data["expire_date"] = $date;
            $data["expire_strategy"] = "fixed_date";
        }
    }else{
        if($timestamp == 0 ){
            $data["expire_date"] = null;
            $data["expire_strategy"] = "never";
        }else{
            $data["expire_date"] = null;
            $data["expire_strategy"] = "start_on_first_use";
            $data["usage_duration"] = $timestamp - time();
        }
    }
    $payload = json_encode($data);
    file_put_contents('ss',$payload);
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
function Get_System_Statsm($location){
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url =  $marzban_list_get['url_panel'].'/api/system/stats/users';
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
    $Get_System_Stats = json_decode($output, true);
    return $Get_System_Stats;
}
//----------------------------------
function removeuserm($location,$username)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url =  $marzban_list_get['url_panel'].'/api/users/'.$username;
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
function Modifyuserm($location,$username,array $data)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $Check_token = token_panelm($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $url =  $marzban_list_get['url_panel'].'/api/users/'.$username;
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