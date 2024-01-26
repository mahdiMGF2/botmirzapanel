<?php
function ActiveVoucher($ev_number, $ev_code){
    global $connect;
    $Payer_Account = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_Payer_Account',"select")['ValuePay'];
    $AccountID = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_AccountID',"select")['ValuePay'];
    $PassPhrase = select("PaySetting", "ValuePay", "NamePay", 'perfectmoney_PassPhrase',"select")['ValuePay'];
    $opts = array(
        'socket' => array(
            'bindto' => 'ip',
        )
    );

    $context = stream_context_create($opts);

    $voucher = file_get_contents("https://perfectmoney.com/acct/ev_activate.asp?AccountID=" . $AccountID . "&PassPhrase=" . $PassPhrase . "&Payee_Account=" . $Payer_Account . "&ev_number=" . $ev_number . "&ev_code=" . $ev_code);
    return $voucher;
}
function update($table, $field, $newValue, $whereField = null, $whereValue = null) {
    global $pdo,$user;

    if ($whereField !== null) {
        $stmt = $pdo->prepare("SELECT $field FROM $table WHERE $whereField = ? FOR UPDATE");
        $stmt->execute([$whereValue]);
        $currentValue = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE $table SET $field = ? WHERE $whereField = ?");
        $stmt->execute([$newValue, $whereValue]);
    } else {
        $stmt = $pdo->prepare("UPDATE $table SET $field = ?");
        $stmt->execute([$newValue]);
    }
}
function step($step, $from_id){
    global $pdo;
    $stmt = $pdo->prepare('UPDATE user SET step = ? WHERE id = ?');
    $stmt->execute([$step, $from_id]);


}
function select($table, $field, $whereField = null, $whereValue = null, $type = "select") {
    global $pdo;

    $query = "SELECT $field FROM $table";

    if ($whereField !== null) {
        $query .= " WHERE $whereField = :whereValue";
    }

    try {
        $stmt = $pdo->prepare($query);

        if ($whereField !== null) {
            $stmt->bindParam(':whereValue', $whereValue);
        }

        $stmt->execute();

        if ($type == "count") {
            return $stmt->rowCount();
        } elseif ($type == "FETCH_COLUMN") {
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }elseif ($type == "fetchAll") {
            return $stmt->fetchAll();
        } else {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

function generateUUID() {
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); 

    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    return $uuid;
}
function tronratee(){
    $tronrate = [];
    $tronrate['results'] = [];
    $requests = json_decode(file_get_contents('https://api.bitpin.ir/v1/mkt/currencies/'), true)['results'];
    foreach($requests as  $request){
        if($request['id'] == 15){
            $tronrate['result']['TRX'] = $request['price_info']['price'];
        }
        if($request['id'] == 4){
            $tronrate['result']['USD'] = $request['price_info']['price'];
        }
    }
    return $tronrate;
}
function nowPayments($payment, $price_amount, $order_id, $order_description){
    $apinowpayments = select("PaySetting", "ValuePay", "NamePay", 'apinowpayment',"select")['ValuePay'];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/' . $payment,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT_MS => 4500,
        CURLOPT_ENCODING => '',
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array(
            'x-api-key:' . $apinowpayments,
            'Content-Type: application/json'
        ),
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
        'price_amount' => $price_amount,
        'price_currency' => 'usd',
        'pay_currency' => 'trx',
        'order_id' => $order_id,
        'order_description' => $order_description,
    ]));

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response);
}
function StatusPayment($paymentid){
    $apinowpayments = select("PaySetting", "ValuePay", "NamePay", 'apinowpayment',"select")['ValuePay'];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/' . $paymentid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'x-api-key:' . $apinowpayments
        ),
    ));
    $response = curl_exec($curl);
    $response = json_decode($response, true);
    curl_close($curl);
    return $response;
}
