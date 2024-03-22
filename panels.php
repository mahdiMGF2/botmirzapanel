<?php
ini_set('error_log', 'error_log');
require_once 'config.php';
require_once 'apipanel.php';
require_once 'x-ui_single.php';
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
            $domain = explode(":", $Get_Data_Panel['linksubx']);
            $Output['subscription_url'] = $domain[0].":".$domain[1].":2096/sub/{$subId}?name=$subId";
            $Output['configs'] = [outputlunk($Output['subscription_url'])];
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
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
           $UsernameData = get_Client($username,$Get_Data_Panel['name_panel']);
           $UsernameData2 = get_clinets($username,$Get_Data_Panel['name_panel']);
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
            $domain = explode(":", $Get_Data_Panel['linksubx']);
            $subId = $UsernameData2['subId'];
            $status_user = get_onlinecli($Get_Data_Panel['name_panel'],$username);
            $Output = array(
                'status' => $UsernameData['enable'],
                'username' => $UsernameData['email'],
                'data_limit' => $UsernameData['total'],
                'expire' => $UsernameData['expiryTime']/1000,
                'online_at' => $status_user,
                'used_traffic' => $UsernameData['up']+$UsernameData['down'],
                'links' => [outputlunk($domain[0].":".$domain[1].":2096/sub/{$UsernameData2['subId']}?name={$UsernameData2['subId']}")],
                'subscription_url' => $domain[0].":".$domain[1].":2096/sub/{$UsernameData2['subId']}?name={$UsernameData2['subId']}",
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
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
            $clients = get_clinets($username,$name_panel);
            $subId = bin2hex(random_bytes(8));
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
            $domain = explode(":", $Get_Data_Panel['linksubx']);
            $Output = array(
                'status' => 'successful',
                'configs' => outputlunk($domain[0].":".$domain[1].":2096/sub/{$subId}?name=$subId"),
                'subscription_url' => $domain[0].":".$domain[1].":2096/sub/{$subId}?name=$subId",
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
        }
        elseif($Get_Data_Panel['type'] == "x-ui_single"){
            ResetUserDataUsagex_uisin($username, $name_panel);
        }
    }
    function Modifyuser($username,$name_panel,$config = array()){
        $Output = array();
        global $connect;
        $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel,"select");
        if($Get_Data_Panel['type'] == "marzban"){
          Modifyuser($name_panel, $username, $config);
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

    }



}