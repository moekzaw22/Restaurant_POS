<?php
// $host = "127.0.0.1";
// $db = "restaurant_pos";
// $user = "root";
// $pass = "";

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch(PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }
$connection = mysqli_connect('localhost', 'root', 'moekhant21202', 'restaurant_pos');
date_default_timezone_set('Asia/Yangon');
?>
