<?php
require_once 'config.php';
ini_set('error_log', 'error_log');


function loginwg($url,$username,$password){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url.'/api/authenticate',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT_MS => 4000,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(array(
            "username" => $username,
            "password" =>  $password,
            "totp" => "123456"
        )),
        CURLOPT_COOKIEJAR => 'cookiewg.txt',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        )
    ));
    $response = curl_exec($curl);
    if (curl_error($curl)) {
        $token = [];
        $token['errror'] = curl_error($curl);
        return $token;
    }
    curl_close($curl);
    return json_decode($response,true);
}
function get_userwg($username,$namepanel){
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(isset($loginpanel['errror']))return;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/getWireguardConfigurationInfo?configurationName='.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json'
        ),
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
    ));
    $response = json_decode(curl_exec($curl),true);
    if(!isset($response))return;
    $outputpear = array_merge($response['data']['configurationPeers'],$response['data']['configurationRestrictedPeers']);
    $output = [];
    foreach ($outputpear as $userinfo){
        if($userinfo['name'] == $username){
            $output = $userinfo;
            break;
        }
    }
    curl_close($curl);
    unlink('cookiewg.txt');
    return $output;
}
function ipslast($namepanel){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(isset($loginpanel['errror']))return;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/getAvailableIPs/'.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json'
        ),
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
    ));
    $response = json_decode(curl_exec($curl),true)['data'];
    curl_close($curl);
    return $response[0];
}
function downloadconfig($namepanel,$publickey){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(isset($loginpanel['errror']))return;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel']."/api/downloadPeer/{$marzban_list_get['inboundid']}?id=".urlencode($publickey),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json'
        ),
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
    ));
    $response = json_decode(curl_exec($curl),true)['data'];
    curl_close($curl);
    unlink('cookiewg.txt');
    return $response;
}
function addpear($namepanel, $usernameac){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $pubandprivate = publickey();
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if($loginpanel['status'] == false)return;
    $config = array(
        'name' => $usernameac,
        'allowed_ips' => [ipslast($namepanel)],
        'private_key' => $pubandprivate['private_key'],
        'public_key' => $pubandprivate['public_key'],
        'preshared_key' => $pubandprivate['preshared_key'],
    );

    $configpanel = json_encode($config,true);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/addPeers/'.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $configpanel,
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
        ),
    ));
    $response =json_decode(curl_exec($curl),true);
    $config['status'] = true;
    if($response['status'] == false)return $response;

    curl_close($curl);
    unlink('cookiewg.txt');
    return $config;
}
function setjob($namepanel,$type,$value,$publickey){
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(isset($loginpanel['errror']))return;
    $curl = curl_init();
    $data = json_encode(array(
        "Job" => array(
            "JobID" =>  generateUUID(),
            "Configuration" => $marzban_list_get['inboundid'],
            "Peer" => $publickey,
            "Field" => $type,
            "Operator" => "lgt",
            "Value" => strval($value),
            "CreationDate" => "",
            "ExpireDate" => null,
            "Action" => "restrict"
        )));
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/savePeerScheduleJob/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
    unlink('cookiewg.txt');
    return $response;

}
function updatepear($namepanel,array $config){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $configpanel = json_encode($config,true);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/updatePeerSettings/'.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $configpanel,
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    return $response;
    curl_close($curl);
    unlink('cookiewg.txt');
    return json_decode($response, true);
}
function deletejob($namepanel,array $config){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $configpanel = json_encode($config);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/deletePeerScheduleJob/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $configpanel,
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    unlink('cookiewg.txt');
    return json_decode($response, true);
}
function ResetUserDataUsagewg($publickey, $namepanel){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $config = array(
        "id" => $publickey,
        "type" => "total"
    );
    $configpanel = json_encode($config,true);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/resetPeerData/'.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $configpanel,
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    unlink('cookiewg.txt');
    return json_decode($response, true);
}


function remove_userwg($location,$username){
    allowAccessPeers($location,$username);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $data_user = json_decode(select("invoice","user_info","username",$username,"select")['user_info'],true)['public_key'];
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/deletePeers/'.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
        CURLOPT_POSTFIELDS => json_encode(array(
            "peers" => array(
                $data_user
            )
        )),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
    ));
    $response = json_decode(curl_exec($curl),true);
    curl_close($curl);
    unlink('cookiewg.txt');
    return $response;
}
function allowAccessPeers($location,$username){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $data_user = json_decode(select("invoice","user_info","username",$username,"select")['user_info'],true)['public_key'];
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/allowAccessPeers/'.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
        CURLOPT_POSTFIELDS => json_encode(array(
            "peers" => array(
                $data_user
            )
        )),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
    ));
    $response = json_decode(curl_exec($curl),true);
    curl_close($curl);
    unlink('cookiewg.txt');
    return $response;
}
function restrictPeers($location,$username){

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location,"select");
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $data_user = json_decode(select("invoice","user_info","username",$username,"select")['user_info'],true)['public_key'];
    $loginpanel = loginwg($marzban_list_get['url_panel'],$marzban_list_get['username_panel'],$marzban_list_get['password_panel']);
    if(!$loginpanel['status'])return;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'].'/api/restrictPeers/'.$marzban_list_get['inboundid'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_COOKIEFILE => 'cookiewg.txt',
        CURLOPT_POSTFIELDS => json_encode(array(
            "peers" => array(
                $data_user
            )
        )),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
    ));
    $response = json_decode(curl_exec($curl),true);
    curl_close($curl);
    unlink('cookiewg.txt');
    return $response;
}