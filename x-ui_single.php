<?php
require_once 'config.php';

function panel_login_cookie($code_panel)
{
    $panel = select("marzban_panel", "*", "id", $code_panel, "select");
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $panel['url_panel'] . '/login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT_MS => 4000,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => "username={$panel['username_panel']}&password={$panel['password_panel']}",
        CURLOPT_COOKIEJAR => 'cookie.txt',
    ));
    $response = curl_exec($curl);
    if (curl_error($curl)) {
        $token = [];
        $token['errror'] = curl_error($curl);
        return $token;
    }
    curl_close($curl);
    return $response;
}

function login($code_panel, $verify = true)
{
    $panel = select("marzban_panel", "*", "id", $code_panel, "select");
    if ($panel['datelogin'] != null && $verify) {
        $date = json_decode($panel['datelogin'], true);
        if (isset($date['time'])) {
            $timecurrent = time();
            $start_date = time() - strtotime($date['time']);
            if ($start_date <= 3000) {
                file_put_contents('cookie.txt', $date['access_token']);
                return;
            }
        }
    }
    $response = panel_login_cookie($panel['id']);
    $time = date('Y/m/d H:i:s');
    $data = json_encode(array(
        'time' => $time,
        'access_token' => file_get_contents('cookie.txt')
    ));
    update("marzban_panel", "datelogin", $data, 'id', $panel['id']);
    if (!is_string($response))
        return array('success' => false);
    return json_decode($response, true);
}


function get_Client($username, $namepanel)
{
    global $connect;
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel, "select");
    login($marzban_list_get['id']);
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'] . '/panel/api/inbounds/getClientTraffics/' . $username,
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
        CURLOPT_COOKIEFILE => 'cookie.txt',
    ));
    $output = [];
    $response = json_decode(curl_exec($curl), true)['obj'];
    curl_close($curl);
    return $response;
    unlink('cookie.txt');
}
function get_clinets($username, $namepanel)
{
    global $connect;
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel, "select");
    $login = login($marzban_list_get['id']);
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'] . '/panel/api/inbounds/list',
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
        CURLOPT_COOKIEFILE => 'cookie.txt',
    ));
    $output = [];
    $response = json_decode(curl_exec($curl), true)['obj'];
    if (!isset($response))
        return [];
    foreach ($response as $client) {
        $client = json_decode($client['settings'], true)['clients'];
        foreach ($client as $clinets) {
            if ($clinets['email'] == $username) {
                $output = $clinets;
                break;
            }
        }

    }
    curl_close($curl);
    unlink('cookie.txt');
    return $output;
}
function addClient($namepanel, $usernameac, $Expire, $Total, $Uuid, $Flow, $subid)
{
    global $connect;
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel, "select");
    $Allowedusername = get_Client($usernameac, $namepanel);
    if (isset($Allowedusername['email'])) {
        $random_number = rand(1000000, 9999999);
        $username_ac = $usernameac . $random_number;
    }
    login($marzban_list_get['id']);
    $config = array(
        "id" => intval($marzban_list_get['inboundid']),
        'settings' => json_encode(array(
            'clients' => array(
                array(
                    "id" => $Uuid,
                    "flow" => $Flow,
                    "email" => $usernameac,
                    "totalGB" => $Total,
                    "expiryTime" => $Expire,
                    "enable" => true,
                    "tgId" => "",
                    "subId" => $subid,
                    "reset" => 0
                )
            ),
            'decryption' => 'none',
            'fallbacks' => array(),
        ))
    );

    $configpanel = json_encode($config, true);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'] . '/panel/api/inbounds/addClient',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $configpanel,
        CURLOPT_COOKIEFILE => 'cookie.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
        ),
    ));
    $response = curl_exec($curl);

    curl_close($curl);
    unlink('cookie.txt');
    return json_decode($response, true);
}
function updateClient($namepanel, $username, array $config)
{
    global $connect;
    $UsernameData = get_clinets($username, $namepanel);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel, "select");
    login($marzban_list_get['id']);
    $configpanel = json_encode($config, true);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'] . '/panel/api/inbounds/updateClient/' . $UsernameData['id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $configpanel,
        CURLOPT_COOKIEFILE => 'cookie.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    unlink('cookie.txt');
    return json_decode($response, true);
}
function ResetUserDataUsagex_uisin($usernamepanel, $namepanel)
{
    global $connect;
    $data_user = get_clinets($usernamepanel, $namepanel);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $namepanel, "select");
    login($marzban_list_get['id']);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'] . "/panel/api/inbounds/{$marzban_list_get['inboundid']}/resetClientTraffic/" . $data_user['email'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_COOKIEFILE => 'cookie.txt',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
        ),

    ));

    $response = curl_exec($curl);
    curl_close($curl);
    unlink('cookie.txt');
}
function removeClient($location, $username)
{
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $location, "select");
    login($marzban_list_get['id']);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'] . "/panel/api/inbounds/{$marzban_list_get['inboundid']}/delClientByEmail/" . $username,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_COOKIEFILE => 'cookie.txt',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
        ),
    ));

    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    unlink('cookie.txt');
    return $response;
}
function get_onlinecli($name_panel, $username)
{
    global $connect;
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    login($marzban_list_get['id']);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $marzban_list_get['url_panel'] . '/panel/api/inbounds/onlines',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json'
        ),
        CURLOPT_COOKIEFILE => 'cookie.txt',
    ));
    $response = json_decode(curl_exec($curl), true)['obj'];
    if ($response == null)
        return "offline";
    if (in_array($username, $response))
        return "online";
    return "offline";
    curl_close($curl);
    unlink('cookie.txt');

}