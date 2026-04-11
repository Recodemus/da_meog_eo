<?php
session_start();
require_once 'db_connect.php';

// --- 추가할 방어 코드 ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("잘못된 접근입니다.");
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    die("아이디와 비밀번호를 모두 입력해주세요.");
}

try {
    // 1. 아이디로 사용자 조회
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // 2. 사용자가 존재하고, 입력한 비밀번호가 해시된 비밀번호와 일치하는지 확인
    if ($user && password_verify($password, $user['password'])) {
        // 로그인 성공: 세션에 사용자 정보 저장
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['nickname'] = $user['nickname'];
        echo "로그인 성공! 환영합니다, " . $user['nickname'] . "님.";
    } else {
        echo "아이디 또는 비밀번호가 올바르지 않습니다.";
    }
} catch (PDOException $e) {
    echo "로그인 에러: " . $e->getMessage();
}
?>