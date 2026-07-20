<?php
// db.php

// REPLACE THESE 4 VARIABLES WITH YOUR INFINITYFREE CREDENTIALS
$host = 'sql200.infinityfree.com'; // e.g., sql100.epizy.com or sql123.infinityfree.com
$db   = 'if0_42455524_car_workshop';   // e.g., epiz_12345678_carworkshop (or if0_..._carworkshop)
$user = 'if0_42455524'; // e.g., epiz_12345678 (or if0_...)
$pass = 'cD8vFBt2FS0'; // The password you revealed in your dashboard
$charset = 'utf8mb4';

// Do not change anything below this line
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throws exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // True prepared statements for security
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If this fails on the live server, it will print the exact reason here
    die("Database connection failed. Please check your credentials: " . $e->getMessage());
}
?>