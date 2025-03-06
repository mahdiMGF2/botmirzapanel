<?php
ini_set('error_log', 'error_log');
require_once 'config.php';
require_once 'apipanel.php';
require_once 'x-ui_single.php';
require_once 'marzneshin.php';
require_once 'alireza_single.php';
require_once 's_ui.php';
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
                $data_Output['links'] = [base64_decode(outputlunk($data_Output['subscription_url']))];
                $timestamp = strtotime($data_Output['expire']);
                $data_Output['expire'] = $timestamp;
                $Output['status'] = 'successful';
                $Output['username'] = $data_Output['username'];
                $Output['subscription_url'] = $data_Output['subscription_url'];
                $Output['configs'] = $data_Output['links'];
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
                $Output['subscription_url'] = "{$Get_Data_Panel['linksubx']}/{$subId}/?name=$usernameC";
                $Output['configs'] = [outputlunk($Output['subscription_url'])];
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
                $Output['subscription_url'] = "{$Get_Data_Panel['linksubx']}/{$subId}/?name=$usernameC";
                $Output['configs'] = [outputlunk($Output['subscription_url'])];
            }
        }
        elseif($Get_Data_Panel['type'] == "s_ui"){
            $data_Output = addClientS_ui($Get_Data_Panel['name_panel'], $usernameC, $expire,$data_limit,json_decode($Get_Data_Panel['proxies']));
            if(!$data_Output['success']){
                $Output['status'] = 'Unsuccessful';
                $Output['msg'] = $data_Output['msg'];
            }else{
                $Output['status'] = 'successful';
                $Output['username'] = $usernameC;
                $Output['subscription_url'] = $Get_Data_Panel['linksubx']."/$usernameC";
                $Output['configs'] = [outputlunk($Get_Data_Panel['linksubx']."/$usernameC")];
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
                $UsernameData['links'] = [base64_decode(outputlunk($UsernameData['subscription_url']))];
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
                    'links' => $UsernameData['links'],
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
                $linksub = "{$Get_Data_Panel['linksubx']}/{$subId}/?name=$username";
                $Output = array(
                    'status' => $UsernameData['enable'],
                    'username' => $UsernameData['email'],
                    'data_limit' => $UsernameData['total'],
                    'expire' => $UsernameData['expiryTime']/1000,
                    'online_at' => $status_user,
                    'used_traffic' => $UsernameData['up']+$UsernameData['down'],
                    'links' => [outputlunk($linksub)],
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
                $linksub = "{$Get_Data_Panel['linksubx']}/{$subId}/?name=$username";
                $Output = array(
                    'status' => $UsernameData['enable'],
                    'username' => $UsernameData['email'],
                    'data_limit' => $UsernameData['total'],
                    'expire' => $UsernameData['expiryTime']/1000,
                    'online_at' => $status_user,
                    'used_traffic' => $UsernameData['up']+$UsernameData['down'],
                    'links' => [outputlunk($linksub)],
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
                    'subscription_url' => $Get_Data_Panel['linksubx']."/{$UsernameData['name']}",
                    'sub_updated_at' => null,
                    'sub_last_user_agent'=> null,
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
                $Data_User['links'] = [base64_decode(outputlunk($Data_User['subscription_url']))];
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
            $updateinbound = updateClient($Get_Data_Panel['name_panel'],$username,$config);
            if(!$clients){
                $Output = array(
                    'status' => 'Unsuccessful',
                    'msg' => 'Unsuccessful'
                );
            }else{
                $Output = array(
                    'status' => 'successful',
                    'configs' => outputlunk($linksub),
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
                    'configs' => outputlunk($linksub),
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
                $Output = array(
                    'status' => 'successful',
                    'configs' => [outputlunk($Get_Data_Panel['linksubx']."/{$usernameac}")],
                    'subscription_url' => $Get_Data_Panel['linksubx']."/{$usernameac}",
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

    }



}