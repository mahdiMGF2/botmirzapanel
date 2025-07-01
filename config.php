<?php
/*
channel => @mirzapanel
*/
//-----------------------------database-------------------------------
$dbname = '{DATABASE_NAME}'; //  نام دیتابیس
$usernamedb = '{DATABASE_USERNAME}'; // نام کاربری دیتابیس
$passworddb = '{DATABASE_PASSOWRD}'; // رمز عبور دیتابیس
$connect = mysqli_connect("localhost", $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) {
    die("The connection to the database failed:" . $connect->connect_error);
}
mysqli_set_charset($connect, "utf8mb4");

//-----------------------------info-------------------------------

$APIKEY = "{BOT_TOKEN}"; // توکن ربات خود را وارد کنید
$adminnumber = "{ADMIN_#ID}";// آیدی عددی ادمین
$domainhosts = "{DOMAIN.COM/PATH/BOT}";// دامنه  هاست و مسیر سورس بدون / اخر
$usernamebot = "{BOT_USERNAME}"; //نام کاربری ربات  بدون @

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
