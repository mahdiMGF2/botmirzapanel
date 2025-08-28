<?php
/*
channel => @mirzapanel
*/
//-----------------------------database-------------------------------
$dbname = '{DATABASE_NAME}'; //  Name Database
$usernamedb = '{DATABASE_USERNAME}'; // Username Database
$passworddb = '{DATABASE_PASSOWRD}'; // Password Database
$connect = mysqli_connect("localhost", $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) {
    die("The connection to the database failed:" . $connect->connect_error);
}
mysqli_set_charset($connect, "utf8mb4");

//-----------------------------info-------------------------------

$APIKEY = "{BOT_TOKEN}"; // Token Bot of Botfather
$adminnumber = "{ADMIN_#ID}";// Id Number Admin
$domainhosts = "{DOMAIN.COM/PATH/BOT}";// Domain Host and Path of Bot without trailing /
$usernamebot = "{BOT_USERNAME}"; // Username Bot without @

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$dsn = "mysql:host=localhost;dbname=$dbname;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $usernamedb, $passworddb, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}
