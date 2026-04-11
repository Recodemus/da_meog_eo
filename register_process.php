<?php
require_once 'db_connect.php';

// 1. POST 요청인지 확인 (주소창 직접 접속 차단)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("잘못된 접근입니다.");
}

// 2. 값이 비어있으면 빈 문자열('')로 처리하여 에러 방지
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$nickname = $_POST['nickname'] ?? '';

// 3. 필수 값이 하나라도 없으면 중단
if (empty($username) || empty($password) || empty($nickname)) {
    die("모든 항목을 입력해주세요.");
}

// ... 이후 기존 비밀번호 해시 암호화 및 DB INSERT 코드 유지 ...

// 1. 비밀번호 해시 암호화 (복호화 불가능)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // 2. Prepared Statement로 SQL 인젝션 완벽 방어
    $sql = "INSERT INTO users (username, password, nickname) VALUES (:username, :password, :nickname)";
    $stmt = $pdo->prepare($sql);
    
    // 3. 데이터 바인딩 및 실행
    $stmt->execute([
        ':username' => $username,
        ':password' => $hashed_password,
        ':nickname' => $nickname
    ]);
    
    echo "회원가입이 완료되었습니다!";
} catch (PDOException $e) {
    echo "회원가입 에러: " . $e->getMessage();
}
?>