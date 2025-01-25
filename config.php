<?php
/*
channel => @mirzapanel
*/
//-----------------------------database-------------------------------
$dbname = "databasename"; //  نام دیتابیس
$usernamedb = "username"; // نام کاربری دیتابیس
$passworddb = "password"; // رمز عبور دیتابیس
$connect = mysqli_connect("localhost", $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) {
    die("The connection to the database failed:" . $connect->connect_error);
}
mysqli_set_charset($connect, "utf8mb4");
//-----------------------------info-------------------------------

$APIKEY = "**TOKEN**"; // توکن ربات خود را وارد کنید
$adminnumber = "5522424631";// آیدی عددی ادمین
$domainhosts = "domain.com/bot";// دامنه  هاست و مسیر سورس
$usernamebot = "marzbaninfobot"; //نام کاربری ربات  بدون @



$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$dsn = "mysql:host=localhost;dbname=$dbname;charset=utf8mb4";
try {
     $pdo = new PDO($dsn, $usernamedb, $passworddb, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
