<?php
// db_connect.php 전체 코드 예시

$host = 'localhost';
$db   = 'recode040109'; // 본인의 DB 이름
$user = 'recode040109'; // 본인의 DB 아이디
$pass = 'minsuk040109!'; // 🌟 본인의 비밀번호로 변경하세요!

// 🌟 핵심: charset=utf8mb4 
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4"; 

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 🌟 이 코드를 꼭 추가해 주세요! (연결 직후 강제로 utf8mb4 모드 켜기)
    $pdo->exec("SET NAMES utf8mb4"); 
    
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>