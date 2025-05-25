<?php


function login_mikrotik($url,$username,$password){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url.'/rest/system/resource',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $username . ":" . $password,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 1,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if(!isset($response) || $httpcode != 200)return array("error" => 404);
$response = json_decode($response,true);
curl_close($curl);
return $response;

}

function addUser_mikrotik($name_panel,$username,$password,$group){
    $panel = select("marzban_panel","*","name_panel",$name_panel,"select");
    $curl = curl_init();
    $data = array(
        'name' => $username,
        'password' => $password
    );
    curl_setopt_array($curl, array(
      CURLOPT_URL => $panel['url_panel'].'/rest/user-manager/user/add',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $panel['username_panel'] . ":" . $panel['password_panel'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
      CURLOPT_POSTFIELDS => json_encode($data,true)
));

$response = curl_exec($curl);
if(!isset($response))return json_encode(array("error" => 404));
set_profile_mikrotik($name_panel,$username,$group);
$response = json_decode($response,true);
curl_close($curl);
return $response;
}
function set_profile_mikrotik($name_panel,$username,$prof_name){
    $panel = select("marzban_panel","*","name_panel",$name_panel,"select");
    $curl = curl_init();
    $data = array(
        'user' => $username,
        'profile' => $prof_name
    );
    curl_setopt_array($curl, array(
      CURLOPT_URL => $panel['url_panel'].'/rest/user-manager/user-profile/add',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $panel['username_panel'] . ":" . $panel['password_panel'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
      CURLOPT_POSTFIELDS => json_encode($data,true)
));

$response = curl_exec($curl);
if(!isset($response))return json_encode(array("error" => 404));
$response = json_decode($response,true);
curl_close($curl);
return $response;
}
function GetUsermikrotik($name_panel,$username){
    $panel = select("marzban_panel","*","name_panel",$name_panel,"select");
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $panel['url_panel'].'/rest/user-manager/user?name='.$username,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $panel['username_panel'] . ":" . $panel['password_panel'],
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    )
));

$response = curl_exec($curl);
if(!isset($response))return json_encode(array("error" => 404));
$response = json_decode($response,true);
curl_close($curl);
return $response;
}
function GetUsermikrotik_volume($name_panel,$id){
    $panel = select("marzban_panel","*","name_panel",$name_panel,"select");
    $curl = curl_init();
    $data = array(
        'once' => true,
        '.id' => $id
        );
    curl_setopt_array($curl, array(
      CURLOPT_URL => $panel['url_panel'].'/rest/user-manager/user/monitor',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $panel['username_panel'] . ":" . $panel['password_panel'],
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST', 
      CURLOPT_POSTFIELDS => json_encode($data,true),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    )
));

$response = curl_exec($curl);
if(!isset($response))return json_encode(array("error" => 404));
$response = json_decode($response,true)[0];
curl_close($curl);
return $response;
}
function deleteUser_mikrotik($name_panel,$username){
    $panel = select("marzban_panel","*","name_panel",$name_panel,"select");
    $curl = curl_init();
    $data = array(
        '.id' => $username
        );
    curl_setopt_array($curl, array(
      CURLOPT_URL => $panel['url_panel'].'/rest/user-manager/user/remove',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $panel['username_panel'] . ":" . $panel['password_panel'],
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST', 
      CURLOPT_POSTFIELDS => json_encode($data,true),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    )
));

$response = curl_exec($curl);
if(!isset($response))return json_encode(array("error" => 404));
$response = json_decode($response,true)[0];
curl_close($curl);
return $response;
}
