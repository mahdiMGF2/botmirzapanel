<?php
$rootPath = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING);
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING);
$Pathfile = dirname(dirname($PHP_SELF, 2));
$Pathfiles = $rootPath . $Pathfile;
$Pathfile = $Pathfiles . '/config.php';
$functions = $Pathfiles . '/functions.php';
require_once $functions;
require_once $Pathfile;
$apiplisio = select("PaySetting", "ValuePay", "NamePay", "apiplisio", "select")['ValuePay'];
$amount = htmlspecialchars($_GET['price'], ENT_QUOTES, 'UTF-8');
$invoice_id = htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8');
$currency = htmlspecialchars($_GET['pay_currency'], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_GET['pay_email'], ENT_QUOTES, 'UTF-8');
if (!isset($currency) || $currency == "") {
    $currency = "USDT_BSC";
}
$order_description = htmlspecialchars($_GET['order_description'], ENT_QUOTES, 'UTF-8');
$curl = curl_init();
$totalurl = "https://plisio.net/api/v1/invoices/new?source_currency=USD"
    . "&source_amount=" . $amount
    . "&order_number=" . $invoice_id
    . "&currency=" . $currency
    . "&email=" . $email
    . "&order_name=" . $order_description
    . "&callback_url=" . "https://" . $domainhosts . '/payment/plisio/back.php?order_number=' . $invoice_id
    . "&api_key=" . $apiplisio;

curl_setopt_array($curl, array(
    CURLOPT_URL => $totalurl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'x-api-key:' . $apiplisio,
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
curl_close($curl);
$res = json_decode($response);
if (isset($res->status) && $res->status == "error") {
    echo 'An error has occurred: Erorr Code : ' . $res->data->code . "</br>Error description : " . $res->data->message;
    return;
}
header('Location: ' . $res->data->invoice_url);
