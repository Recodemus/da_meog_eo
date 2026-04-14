<?php
// 닷홈 환경에 맞게 수정된 db_connect.php
$host = 'localhost'; // 닷홈도 보통 localhost를 씁니다.
$db   = 'recode040109'; // 닷홈은 DB 이름이 내 아이디와 똑같습니다!
$user = 'recode040109'; // 닷홈은 DB 사용자도 내 아이디와 똑같습니다!
$pass = 'minsuk040109!';

try {
    // 한글 깨짐 방지를 위해 charset=utf8 추가
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("데이터베이스 연결 실패: " . $e->getMessage());
}
?>