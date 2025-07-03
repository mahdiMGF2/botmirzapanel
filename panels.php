<?php
ini_set('error_log', 'error_log');
require_once 'config.php';
require_once 'apipanel.php';
require_once 'x-ui_single.php';
require_once 'marzneshin.php';
require_once 'alireza_single.php';
require_once 's_ui.php';
require_once 'wgdashboard.php';
require_once 'mikrotik.php';
class ManagePanel{
    public $name_panel;
    public $connect;
    function createUser($name_panel,$usernameC, array $Data_Config){
        $Output = [];
        global $connect;
        // input time expire timestep use $Data_Config
        // input data_limit byte use $Data_Config
        // input username use $Data_Config
        // input from_id use $Data_Config
        // input type config use $Data_Config

        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel,"select");
        $expire = $Data_Config['expire'];
        $data_limit = $Data_Config['data_limit'];
        if($Get_Data_Panel['type'] == "marzban"){
            //create user
            $ConnectToPanel= adduser($usernameC,$expire,$data_limit,$Get_Data_Panel['name_panel']);
            $data_Output = json_decode($ConnectToPanel, true);
            if(isset($data_Output['detail']) && $data_Output['detail']){
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['detail'];
            }else{
                if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $data_Output['subscription_url'])) {
                    $data_Output['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($data_Output['subscription_url'], "/");
                }
                $Output['status'] = 'successful';
                $Output['username'] = $data_Output['username'];
                $Output['subscription_url'] = $data_Output['subscription_url'];
                $Output['configs'] = $data_Output['links'];
            }
        }
        elseif($Get_Data_Panel['type'] == "marzneshin"){
            //create user
            $ConnectToPanel= adduserm($Get_Data_Panel['name_panel'],$data_limit,$usernameC,$expire);
            $data_Output = json_decode($ConnectToPanel, true);
            if(isset($data_Output['detail']) && $data_Output['detail']){
                $Output['status'] = 'Unsuccessful';
                if($data_Output['detail']){
                    $Output['msg'] = $data_Output['detail'];
                }else{
                    $Output['msg'] = '';
                }
            }else{
                if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $data_Output['subscription_url'])) {
                    $data_Output['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($data_Output['subscription_url'], "/");
                }
                $links_user = outputlink($data_Output['subscription_url']);
                if(isBase64($string)){
                    $links_user = base64_decode($links_user);
                }
                $links_user = explode("\n",trim($links_user));
                $timestamp = strtotime($data_Output['expire']);
                $data_Output['expire'] = $timestamp;
                $Output['status'] = 'successful';
                $Output['username'] = $data_Output['username'];
                $Output['subscription_url'] = $data_Output['subscription_url'];
                $Output['configs'] = $links_user;
            }
        }
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
            $subId = bin2hex(random_bytes(8));
            $Expireac = $expire*1000;
            $data_Output = addClient($Get_Data_Panel['name_panel'],$usernameC,$Expireac,$data_limit,generateUUID(),"",$subId);
            if(!$data_Output['success']){
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'];
            }else{
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = "{$Get_Data_Panel['linksubx']}/{$subId}#$usernameC";
                $Output['configs'] = [outputlink($Output['subscription_url'])];
            }
        }
        elseif($Get_Data_Panel['type'] == "alireza"){
            $subId = bin2hex(random_bytes(8));
            $Expireac = $expire*1000;
            $data_Output = addClientalireza_singel($Get_Data_Panel['name_panel'],$usernameC,$Expireac,$data_limit,generateUUID(),"",$subId);
            if(!$data_Output['success']){
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'];
            }else{
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = "{$Get_Data_Panel['linksubx']}/{$subId}?name=$usernameC";
                $Output['configs'] = [outputlink($Output['subscription_url'])];
            }
        }
        elseif($Get_Data_Panel['type'] == "s_ui"){
            $data_Output = addClientS_ui($Get_Data_Panel['name_panel'], $usernameC, $expire,$data_limit,json_decode($Get_Data_Panel['proxies']));
            if(!$data_Output['success']){
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'];
            }else{
                $setting_app = get_settig($Get_Data_Panel['name_panel']);
                $url = explode(":",$Get_Data_Panel['url_panel']);
                $url_sub = $url[0] .":". $url[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $usernameC;
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = $url_sub;
                $Output['configs'] = [outputlink($url_sub)];
            }

        }
        elseif($Get_Data_Panel['type'] == "wgdashboard"){
            $data_limit = round($data_limit / (1024*1024*1024),2);
            $data_Output = addpear($Get_Data_Panel['name_panel'],$usernameC);
            if($data_limit != 0){
                setjob($Get_Data_Panel['name_panel'],"total_data",$data_limit,$data_Output['public_key']);
            }
            if($expire != 0){
                setjob($Get_Data_Panel['name_panel'],"date",date('Y-m-d H:i:s',$expire),$data_Output['public_key']);
            }
            update("invoice","user_info",json_encode($data_Output),"username",$usernameC);
            if(!$data_Output['status']){
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'];
            }else{
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = strval(downloadconfig($Get_Data_Panel['name_panel'],$data_Output['public_key'])['file']);
                $Output['configs'] = [];
            }
        }
        elseif($Get_Data_Panel['type'] == "mikrotik"){
            $password = bin2hex(random_bytes(6));
            $name_group = $Get_Data_Panel['inboundid'];
            $data_Output = addUser_mikrotik($Get_Data_Panel['name_panel'],$usernameC,$password,$name_group);
            if(isset($data_Output['error'])){
               $Output['status'] = 'Unsuccessful';
               $Output['msg'] = $data_Output['msg']; 
            }else{
            $Output['status'] = 'successful';
            $Output['username'] = $usernameC;
            $Output['subscription_url'] = $password;
            $Output['configs'] = [];
            }

        }
        else{
            $Output['status'] = 'Unsuccessful';
            $Output['msg'] = 'Panel Not Found';
        }
        return $Output;
    }
    function DataUser($name_panel,$username){
        $Output = array();
        global $connect;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel,"select");
        if($Get_Data_Panel == false){
            $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => ""
                );
        }
        if($Get_Data_Panel['type'] == "marzban"){
            $UsernameData = getuser($username,$Get_Data_Panel['name_panel']);
            if(isset($UsernameData['detail']) && $UsernameData['detail']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['detail']
                );
            }elseif(!isset($UsernameData['username'])){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['detail']
                );
            }else{
                if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $UsernameData['subscription_url'])) {
                    $UsernameData['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($UsernameData['subscription_url'], "/");
                }

                $Output = array(
                    'status' => $UsernameData['status'],
                    'username' => $UsernameData['username'],
                    'data_limit' => $UsernameData['data_limit'],
                    'expire' => $UsernameData['expire'],
                    'online_at' => $UsernameData['online_at'],
                    'used_traffic' => $UsernameData['used_traffic'],
                    'links' => $UsernameData['links'],
                    'subscription_url' => $UsernameData['subscription_url'],
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "marzneshin"){
            $UsernameData = getuserm($username,$Get_Data_Panel['name_panel']);
            if(isset($UsernameData['detail']) && $UsernameData['detail']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['detail']
                );
            }elseif(!isset($UsernameData['username'])){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => ""
                );
            }else{
                if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $UsernameData['subscription_url'])){
                    $UsernameData['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($UsernameData['subscription_url'], "/");
                }
                $UsernameData['status']  = "active";
                if(!$UsernameData['enabled']){
                    $UsernameData['status'] = "disabled";
                }elseif($UsernameData['expire_strategy'] == "start_on_first_use"){
                    $UsernameData['status'] = "on_hold";
                }elseif($UsernameData['expired']){
                    $UsernameData['status'] = "expired";
                }elseif($UsernameData['data_limit'] - $UsernameData['used_traffic'] <= 0){
                    $UsernameData['status'] = "limtied";
                }
                $links_user = outputlink($UsernameData['subscription_url']);
                if(isBase64($string)){
                    $links_user = base64_decode($links_user);
                }
                $links_user = explode("\n",trim($links_user));
                if(isset($UsernameData['expire_date'])){
                    $expiretime = strtotime(($UsernameData['expire_date']));
                }else{
                    $expiretime = 0;
                }
                $Output = array(
                    'status' => $UsernameData['status'],
                    'username' => $UsernameData['username'],
                    'data_limit' => $UsernameData['data_limit'],
                    'expire' => $expiretime,
                    'online_at' => $UsernameData['online_at'],
                    'used_traffic' => $UsernameData['used_traffic'],
                    'links' => $links_user,
                    'subscription_url' => $UsernameData['subscription_url'],
                    'sub_updated_at' => $UsernameData['sub_updated_at'],
                    'sub_last_user_agent'=> $UsernameData['sub_last_user_agent'],
                    'uuid' => null
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
            $UsernameData = get_Client($username,$Get_Data_Panel['name_panel']);
            $UsernameData2 = get_clinets($username,$Get_Data_Panel['name_panel']);
            $expire = $UsernameData['expiryTime']/1000;
            if(!$UsernameData['id']){
                if(empty($UsernameData['msg']))$UsernameData['msg'] = "";
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                );
            }else{
                if($UsernameData['enable']){
                    $UsernameData['enable'] = "active";
                }else{
                    $UsernameData['enable'] = "disabled";
                }
                if(intval($UsernameData['expiryTime']) != 0){
                    if($expire - time() <=0 )$UsernameData['enable'] = "expired";
                }
                $subId = $UsernameData2['subId'];
                $status_user = get_onlinecli($Get_Data_Panel['name_panel'],$username);
                $linksub = "{$Get_Data_Panel['linksubx']}/{$subId}#$username";
                $Output = array(
                    'status' => $UsernameData['enable'],
                    'username' => $UsernameData['email'],
                    'data_limit' => $UsernameData['total'],
                    'expire' => $UsernameData['expiryTime']/1000,
                    'online_at' => $status_user,
                    'used_traffic' => $UsernameData['up']+$UsernameData['down'],
                    'links' => [outputlink($linksub)],
                    'subscription_url' => $linksub,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "alireza"){
            $UsernameData = get_Clientalireza($username,$Get_Data_Panel['name_panel']);
            $UsernameData2 = get_clinetsalireza($username,$Get_Data_Panel['name_panel']);
            if(!$UsernameData['id']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                );
            }else{
                if($UsernameData['enable']){
                    $UsernameData['enable'] = "active";
                }else{
                    $UsernameData['enable'] = "disabled";
                }
                $subId = $UsernameData2['subId'];
                $status_user = get_onlinecli($Get_Data_Panel['name_panel'],$username);
                $linksub = "{$Get_Data_Panel['linksubx']}/{$subId}?name=$username";
                $Output = array(
                    'status' => $UsernameData['enable'],
                    'username' => $UsernameData['email'],
                    'data_limit' => $UsernameData['total'],
                    'expire' => $UsernameData['expiryTime']/1000,
                    'online_at' => $status_user,
                    'used_traffic' => $UsernameData['up']+$UsernameData['down'],
                    'links' => [outputlink($linksub)],
                    'subscription_url' => $linksub,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "s_ui"){
            $UsernameData = GetClientsS_UI($username,$Get_Data_Panel['name_panel']);
            $onlinestatus = get_onlineclients_ui($Get_Data_Panel['name_panel'],$username);
            if(!isset($UsernameData['id'])){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                );}
            else{
                $links = [];
                if(is_array($UsernameData['links'])){
                    foreach ($UsernameData['links'] as $config){
                        $links[] = $config['uri'];
                    }
                }
                $setting_app = get_settig($Get_Data_Panel['name_panel']);
                $url = explode(":",$Get_Data_Panel['url_panel']);
                $url_sub = $url[0] .":". $url[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $username;
                $data_limit = $UsernameData['volume'];
                $useage = $UsernameData['up'] + $UsernameData['down'];
                $RemainingVolume = $data_limit - $useage;
                $expire = $UsernameData['expiry'];
                if($UsernameData['enable']){
                    $UsernameData['enable'] = "active";
                }elseif($data_limit != 0 and $RemainingVolume < 0){
                    $UsernameData['enable'] = "limited";
                }elseif($expire - time() < 0 and $expire != 0 ){
                    $UsernameData['enable'] = "expired";
                }else{
                    $UsernameData['enable'] = "disabled";
                }
                $Output = array(
                    'status' => $UsernameData['enable'],
                    'username' => $UsernameData['name'],
                    'data_limit' => $data_limit,
                    'expire' => $expire,
                    'online_at' => $onlinestatus,
                    'used_traffic' => $useage,
                    'links' => $links,
                    'subscription_url' => $url_sub,
                    'sub_updated_at' => null,
                    'sub_last_user_agent'=> null,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "wgdashboard"){
            $UsernameData = get_userwg($username,$Get_Data_Panel['name_panel']);
            $invoiceinfo = select("invoice","*","username",$username,"select");
            $infoconfig = json_decode($invoiceinfo['user_info'],true);
            if(!isset($UsernameData['id'])){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => isset($UsernameData['msg']) ? $UsernameData['msg'] : ''
                );
            }else{
                $jobtime = [];
                $jobvolume = [];
                foreach ($UsernameData['jobs'] as $job){
                    if($job['Field'] == "total_data"){
                        $jobvolume = $job;
                    }elseif($job['Field'] == "date"){
                        $jobtime = $job;
                    }
                }
                if(intval($invoiceinfo['Service_time']) == 0){
                    $expire = 0;
                }else{
                    if(isset($jobtime['Value'])){
                        $expire = strtotime($jobtime['Value']);
                    }else{
                        $expire = 0;
                    }
                }
                $status = "active";
                if ($expire != 0 and $expire - time() < 0 ){
                    $status = "expired";
                }
                $data_useage = ($UsernameData['total_data'] * pow(1024,3)) + ($UsernameData['cumu_data'] * pow(1024,3));
                if(($jobvolume['Value'] * pow(1024,3)) < $data_useage){
                    $status = "limited";
                }
                $Output = array(
                    'status' => $status,
                    'username' => $UsernameData['name'],
                    'data_limit' => $jobvolume['Value'] * pow(1024,3),
                    'expire' => $expire,
                    'online_at' => null,
                    'used_traffic' => $data_useage,
                    'links' => [],
                    'subscription_url' => strval(downloadconfig($Get_Data_Panel['name_panel'],$infoconfig['public_key'])['file']),
                    'sub_updated_at' => null,
                    'sub_last_user_agent'=> null,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "mikrotik"){
           $UsernameData = GetUsermikrotik($Get_Data_Panel['name_panel'],$username)[0];
            if(isset($UsernameData['error'])){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                    );}
            else{
                   $invocie = select("invoice","*","username",$username,"select");
                   $traffic_get = GetUsermikrotik_volume($Get_Data_Panel['name_panel'],$UsernameData['.id']);
                   $used_traffic = $traffic_get['total-upload'] + $traffic_get['total-download'];
                   $data_limit = $invocie['Volume']*pow(1024,3);
                   $expire = $invocie['time_sell'] +  ($invocie['Service_time']*86400);
                   $UsernameData['enable'] = "active";
                   $Output = array(
                        'status' => $UsernameData['enable'],
                        'username' => $invocie['username'],
                        'data_limit' => $data_limit,
                        'expire' => $expire,
                        'online_at' => null,
                        'used_traffic' => $used_traffic,
                        'links' => [],
                        'subscription_url' => $UsernameData['password'],
);
            }
        }
        else{
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => 'Panel Not Found'
            );
        }
        return $Output;
    }
    function Revoke_sub($name_panel,$username){
        $Output = array();
        global $connect;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel,"select");
        if($Get_Data_Panel['type'] == "marzban"){
            $revoke_sub = revoke_sub($username,$name_panel);
            if(isset($revoke_sub['detail']) && $revoke_sub['detail']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $revoke_sub['detail']
                );
            }else{
                $config = new ManagePanel();
                $Data_User  = $config->DataUser($name_panel,$username);
                if (!preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?((\/[^\s\/]+)+)?$/', $Data_User['subscription_url'])) {
                    $Data_User['subscription_url'] = $Get_Data_Panel['url_panel'] . "/" . ltrim($Data_User['subscription_url'], "/");
                }
                $Output = array(
                    'status' => 'successful',
                    'configs' => $Data_User['links'],
                    'subscription_url' => $Data_User['subscription_url']
                );
            }
        }
        else if($Get_Data_Panel['type'] == "marzneshin"){
            $revoke_sub = revoke_subm($username,$name_panel);
            if(isset($revoke_sub['detail']) && $revoke_sub['detail']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $revoke_sub['detail']
                );
            }else{
                $config = new ManagePanel();
                $Data_User  = $config->DataUser($name_panel,$username);
                $Data_User['links'] = [base64_decode(outputlink($Data_User['subscription_url']))];
                $Output = array(
                    'status' => 'successful',
                    'configs' => $Data_User['links'],
                    'subscription_url' => $Data_User['subscription_url']
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
            $clients = get_clinets($username,$name_panel);
            $subId = bin2hex(random_bytes(8));
            $linksub = "{$Get_Data_Panel['linksubx']}/{$subId}#$username";
            $config = array(
                'id' => intval($Get_Data_Panel['inboundid']),
                'settings' => json_encode(array(
                        'clients' => array(
                            array(
                                "id" => generateUUID(),
                                "flow" => $clients['flow'],
                                "email" => $clients['email'],
                                "totalGB" => $clients['totalGB'],
                                "expiryTime" => $clients['expiryTime'],
                                "enable" => true,
                                "subId" => $subId,
                            )),
                    )
                )
            );
            $updateinbound = updateClient($Get_Data_Panel['name_panel'],$username,$config);
            if(!$clients){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => 'Unsuccessful'
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'configs' => outputlink($linksub),
                    'subscription_url' => $linksub,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "alireza"){
            $clients = get_clinetsalireza($username,$name_panel);
            $subId = bin2hex(random_bytes(8));
            $linksub = "{$Get_Data_Panel['linksubx']}/{$subId}/?name=$username";
            $config = array(
                'id' => intval($Get_Data_Panel['inboundid']),
                'settings' => json_encode(array(
                        'clients' => array(
                            array(
                                "id" => generateUUID(),
                                "flow" => $clients['flow'],
                                "email" => $clients['email'],
                                "totalGB" => $clients['totalGB'],
                                "expiryTime" => $clients['expiryTime'],
                                "enable" => true,
                                "subId" => $subId,
                            )),
                    )
                )
            );
            $updateinbound = updateClientalireza($Get_Data_Panel['name_panel'],$username,$config);
            if(!$clients){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => 'Unsuccessful'
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'configs' => outputlink($linksub),
                    'subscription_url' => $linksub,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "s_ui"){
            $clients = GetClientsS_UI($username,$name_panel);
            $password = bin2hex(random_bytes(16));
            $usernameac  = $username;
            $configpanel = array(
                "object" => 'clients',
                'action' => "edit",
                "data" => json_encode(array(
                    "id" => $clients['id'],
                    "enable" => $clients['enable'],
                    "name" => $usernameac,
                    "config" => array (
                        "mixed" => array(
                            "username" => $usernameac
                        ,"password" =>generateAuthStr()
                        ),"socks" =>array(
                            "username" =>$usernameac,
                            "password"=>generateAuthStr()
                        ),"http"=> array(
                            "username"=>$usernameac,
                            "password"=>generateAuthStr()
                        ),"shadowsocks"=>array(
                            "name"=> $usernameac,
                            "password"=>$password
                        ),"shadowsocks16"=>array(
                            "name"=>$usernameac,
                            "password"=>$password
                        ),"shadowtls"=>array(
                            "name"=>$usernameac,
                            "password"=>$password
                        ),"vmess"=>array(
                            "name"=>$usernameac,
                            "uuid"=>generateUUID(),
                            "alterId"=>0
                        ),"vless"=>array(
                            "name"=>$usernameac,
                            "uuid"=>generateUUID(),
                            "flow"=>""
                        ),"trojan"=>array(
                            "name"=>$usernameac,
                            "password"=>generateAuthStr()
                        ),"naive"=>array(
                            "username"=>$usernameac,
                            "password"=>generateAuthStr()
                        ),"hysteria"=>array(
                            "name"=>$usernameac,
                            "auth_str"=>generateAuthStr()
                        ),"tuic"=>array(
                            "name"=>$usernameac,
                            "uuid"=>generateUUID(),
                            "password"=>generateAuthStr()
                        ),"hysteria2"=>array(
                            "name"=>$usernameac,
                            "password"=>generateAuthStr()
                        )),
                    "inbounds" => $clients['inbounds'],
                    "links" => [],
                    "volume" => $clients['volume'],
                    "expiry" => $clients['expiry'],
                    "desc" => $clients['desc']
                )),
            );
            $result = updateClientS_ui($Get_Data_Panel['name_panel'],$configpanel);
            if(!$result['success']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => 'Unsuccessful'
                );
            }else{
                $setting_app = get_settig($Get_Data_Panel['name_panel']);
                $url = explode(":",$Get_Data_Panel['url_panel']);
                $url_sub = $url[0] .":". $url[1] . ":" . $setting_app['subPort'] . $setting_app['subPath'] . $username;
                $Output = array(
                    'status' => 'successful',
                    'configs' => [outputlink($url_sub)],
                    'subscription_url' => $url_sub,
                );
            }
        }

        else{
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => 'Panel Not Found'
            );
        }
        return $Output;
    }
    function RemoveUser($name_panel,$username){
        $Output = array();
        global $connect;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel,"select");
        if($Get_Data_Panel['type'] == "marzban"){
            $UsernameData = removeuser($Get_Data_Panel['name_panel'],$username);
            if(isset($UsernameData['detail']) && $UsernameData['detail']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['detail']
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'username' => $username,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "marzneshin"){
            $UsernameData = removeuserm($Get_Data_Panel['name_panel'],$username);
            if(isset($UsernameData['detail']) && $UsernameData['detail']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['detail']
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'username' => $username,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
            $UsernameData = removeClient($Get_Data_Panel['name_panel'],$username);
            if(!$UsernameData['success']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'username' => $username,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "s_ui"){
            $UsernameData = removeClientS_ui($Get_Data_Panel['name_panel'],$username);
            if(!$UsernameData['success']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'username' => $username,
                );
            }
        }
        elseif($Get_Data_Panel['type'] == "wgdashboard"){
            $UsernameData = remove_userwg($Get_Data_Panel['name_panel'],$username);
            if(!$UsernameData['status']){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'username' => $username,
                );
            }
        }
         elseif($Get_Data_Panel['type'] == "mikrotik"){
           $UsernameData = GetUsermikrotik($Get_Data_Panel['name_panel'],$username)[0];
            if(isset($UsernameData['error'])){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => $UsernameData['msg']
                    );
                
            }
            else{
                deleteUser_mikrotik($Get_Data_Panel['name_panel'],$UsernameData['.id']);
                $Output = array(
                'status' => 'successful',
                'username' => $username,
                );
            }
        }
        else{
            $Output = array(
                'status' => 'Unsuccessful',
                'msg' => 'Panel Not Found'
            );
        }
        return $Output;
    }
    function ResetUserDataUsage($name_panel,$username){
        $Output = array();
        global $connect;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel,"select");
        if($Get_Data_Panel['type'] == "marzban"){
            ResetUserDataUsage($username, $name_panel);
        }elseif($Get_Data_Panel['type'] == "marzneshin"){
            ResetUserDataUsagem($username, $name_panel);
        }
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
            ResetUserDataUsagex_uisin($username, $name_panel);
        }
        elseif($Get_Data_Panel['type'] == "alireza"){
            ResetUserDataUsagealirezasin($username, $name_panel);
        }elseif($Get_Data_Panel['type'] == "s_ui"){
            ResetUserDataUsages_ui($username, $name_panel);
        }
        elseif($Get_Data_Panel['type'] == "wgdashboard"){
            allowAccessPeers($name_panel,$username);
            $datauser = get_userwg($username, $name_panel);
            ResetUserDataUsagewg($datauser['id'], $name_panel);
        }
    }
    function Modifyuser($username,$name_panel,$config = array()){
        $Output = array();
        global $connect;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel,"select");
        if($Get_Data_Panel['type'] == "marzban"){
            Modifyuser($name_panel, $username, $config);
        }elseif($Get_Data_Panel['type'] == "marzneshin"){
            $UsernameData = getuserm($username,$Get_Data_Panel['name_panel']);
            if(!isset($config['expire_date'])){
                $config['expire_date'] = $UsernameData['expire_date'];
            }
            $config['expire_strategy'] = $UsernameData['expire_strategy'];
            $config['username'] = $username;
            Modifyuserm($name_panel, $username, $config);
        }elseif($Get_Data_Panel['type'] == "x-ui_single"){
            $clients = get_clinets($username, $name_panel);
            $configs = array(
                'id' => intval($Get_Data_Panel['inboundid']),
                'settings' => json_encode(array(
                        'clients' => array(
                            array(
                                "id" => $clients['id'],
                                "flow" => $clients['flow'],
                                "email" => $clients['email'],
                                "totalGB" => $clients['totalGB'],
                                "expiryTime" => $clients['expiryTime'],
                                "enable" => true,
                                "subId" => $clients['subId'],
                            )),
                        'decryption' => 'none',
                        'fallbacks' => array(),
                    )
                ),
            );
            $configs['settings'] = json_encode(array_replace_recursive(json_decode($configs['settings'], true),json_decode($config['settings'], true)));
            $updateinbound = updateClient($Get_Data_Panel['name_panel'], $username,$configs);
        }
        elseif($Get_Data_Panel['type'] == "alireza"){
            $clients = get_clinetsalireza($username, $name_panel);
            $configs = array(
                'id' => intval($Get_Data_Panel['inboundid']),
                'settings' => json_encode(array(
                        'clients' => array(
                            array(
                                "id" => $clients['id'],
                                "flow" => $clients['flow'],
                                "email" => $clients['email'],
                                "totalGB" => $clients['totalGB'],
                                "expiryTime" => $clients['expiryTime'],
                                "enable" => true,
                                "subId" => $clients['subId'],
                            )),
                        'decryption' => 'none',
                        'fallbacks' => array(),
                    )
                ),
            );
            $configs['settings'] = json_encode(array_replace_recursive(json_decode($configs['settings'], true),json_decode($config['settings'], true)));
            $updateinbound = updateClientalireza($Get_Data_Panel['name_panel'], $username,$configs);
        }
        elseif($Get_Data_Panel['type'] == "s_ui"){
            $clients = GetClientsS_UI($username,$name_panel);
            if(!$clients)return [];
            $usernameac  = $username;
            $configs = array(
                "object" => 'clients',
                'action' => "edit",
                "data" => array(
                    "id" => $clients['id'],
                    "enable" => $clients['enable'],
                    "name" => $usernameac,
                    "config" => $clients['config'],
                    "inbounds" => $clients['inbounds'],
                    "links" => $clients['links'],
                    "volume" => $clients['volume'],
                    "expiry" => $clients['expiry'],
                    "desc" => $clients['desc']
                ),
            );
            $configs['data'] = array_merge($configs['data'], $config);
            $configs['data'] = json_encode($configs['data'],true);
            return updateClientS_ui($Get_Data_Panel['name_panel'],$configs);
        }
        elseif($Get_Data_Panel['type'] == "WGDashboard"){
            $data_user = get_userwg($username, $name_panel);
            $configs = array(
                "DNS" =>  $data_user['DNS'],
                "allowed_ip" => $data_user['allowed_ip'],
                "endpoint_allowed_ip" => "0.0.0.0/0",
                "jobs" => $data_user['jobs'],
                "id" =>  $data_user['id'],
                "keepalive" => $data_user['keepalive'],
                "mtu" =>  $data_user['mtu'],
                "name" =>$data_user['name'],
                "preshared_key" => $data_user['preshared_key'],
                "private_key" => $data_user['private_key']
            );
            $configs = array_merge($configs, $config);
            return updatepear($Get_Data_Panel['name_panel'],$configs);
        }

    }
}