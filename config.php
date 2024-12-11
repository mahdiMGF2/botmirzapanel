<?php
/*
channel => @mirzapanel
*/

//----------------------------- Configuration -------------------------------
define('DB_NAME', 'databasename'); // نام دیتابیس
define('DB_USER', 'username');    // نام کاربری دیتابیس
define('DB_PASS', 'password');    // رمز عبور دیتابیس
define('DB_HOST', 'localhost');   // هاست دیتابیس

define('BOT_API_KEY', 'TOKEN');          // توکن ربات
define('ADMIN_ID', '5522424631');        // آیدی عددی ادمین
define('BOT_DOMAIN', 'domain.com/bot'); // دامنه و مسیر سورس
define('BOT_USERNAME', 'marzbaninfobot'); // نام کاربری ربات (بدون @)

//----------------------------- MySQLi Connection ---------------------------
$connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($connect->connect_error) {
    die("Database connection failed: " . $connect->connect_error);
}

$connect->set_charset("utf8mb4");

//----------------------------- PDO Connection -------------------------------
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("PDO connection failed: " . $e->getMessage());
}
