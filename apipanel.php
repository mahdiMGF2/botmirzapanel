<?php
include('config.php');
#-----------------------------#
function token_panel($url_panel,$username_panel,$password_panel){
    $url_get_token = $url_panel.'/api/admin/token';
    $data_token = array(
        'username' => $username_panel,
        'password' => $password_panel
    );
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT_MS => 3000,
        CURLOPT_POSTFIELDS => http_build_query($data_token),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'accept: application/json'
        )
    );
    $curl_token = curl_init($url_get_token);
    curl_setopt_array($curl_token, $options);
    $token = curl_exec($curl_token);
    curl_close($curl_token);

    $body = json_decode( $token, true);
    return $body;
}
#-----------------------------#

function getuser($username,$token,$url_panel)
{
    $usernameac = $username;
    $url =  $url_panel.'/api/user/' . $usernameac;
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $token
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
#-----------------------------#
function ResetUserDataUsage($username,$token,$url_panel)
{
    $usernameac = $username;
    $url =  $url_panel.'/api/user/' . $usernameac.'/reset';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST , true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $token
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
#-----------------------------#
function adduser($username,$expire,$data_limit,$token,$url_panel,array $protocol)
{
    $url = $url_panel."/api/user";
    $header_value = 'Bearer ';
    $data = array(
        "proxies" => $protocol,
        "expire" => $expire,
        "data_limit" => $data_limit,
        "username" => $username
    );

    $payload = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $token,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
//----------------------------------
function Get_System_Stats($url_panel,$token){
    $url =  $url_panel.'/api/system';
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $token
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $Get_System_Stats = json_decode($output, true);
    return $Get_System_Stats;
}
//----------------------------------
function removeuser($token,$url_panel,$username)
{
    $url =  $url_panel.'/api/user/'.$username;
    $header_value = 'Bearer ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Authorization: ' . $header_value .  $token
    ));

    $output = curl_exec($ch);
    curl_close($ch);
    $data_useer = json_decode($output, true);
    return $data_useer;
}
//----------------------------------
function Modifyuser($token,$url_panel,$username,array $data)
{
$url =  $url_panel.'/api/user/'.$username;
    $payload = json_encode($data);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$headers = array();
$headers[] = 'Accept: application/json';
$headers[] = 'Authorization: Bearer '.$token;
$headers[] = 'Content-Type: application/json';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
curl_close($ch);
     $data_useer = json_decode($result, true);
    return $data_useer;
}
#-----------------------------------------------#
function formatBytes($bytes, $precision = 2): string
{
    $base = log($bytes, 1024);
    $power = $bytes > 0 ? floor($base) : 0;
    $suffixes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
    return round(pow(1024, $base - $power), $precision) . ' ' . $suffixes[$power];
}
#---------------------[ ]--------------------------#
function generateUsername($from_id,$Metode,$username,$randomString,$text)
{
    global $connect;
    $setting = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
    global $connect;
    if($Metode == "آیدی عددی + حروف و عدد رندوم"){
        return $from_id."_".$randomString;
    }
    elseif($Metode == "نام کاربری + حروف و عدد رندوم"){
        return $username."_".$randomString;
    }
    elseif($Metode == "نام کاربری + عدد به ترتیب"){
        $statistics = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(id_user)  FROM invoice WHERE id_user = '$from_id'"));
        $countInvoice = intval($statistics['COUNT(id_user)']) + 1 ;
        return $username."_".$countInvoice;
    }
    elseif($Metode == "نام کاربری دلخواه")return $text;
}
