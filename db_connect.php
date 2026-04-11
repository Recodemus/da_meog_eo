<?php
// 데이터베이스 연결 정보 설정
$host = 'localhost';       // 데이터베이스 호스트 (보통 localhost 또는 127.0.0.1)
$db   = 'da_meog_eo';      // 데이터베이스 이름
$user = 'root';            // MySQL 사용자 아이디 (로컬 환경 기본값: root)
$pass = '1234';                // MySQL 비밀번호 (APM, XAMPP 등 로컬 환경은 보통 비어있거나 'root' 입니다)
$charset = 'utf8mb4';      // 한글 및 이모지(아이콘) 깨짐 방지

// DSN (Data Source Name) 설정
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO 옵션 설정 (보안 및 편의성)
$options = [
    // 에러 발생 시 예외(Exception)를 던지도록 설정하여 에러 추적을 쉽게 함
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // 데이터를 가져올 때 연관 배열(컬럼명이 키값) 형태로 가져오도록 기본값 설정
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Prepared Statement를 데이터베이스 차원에서 지원하도록 설정 (SQL 인젝션 방어)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 데이터베이스 연결 시도
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // 연결 실패 시 에러 메시지 출력 후 스크립트 종료
    die("데이터베이스 연결 실패: " . $e->getMessage());
}
?>