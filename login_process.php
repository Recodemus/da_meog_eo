<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("잘못된 접근");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) die("모든 항목을 입력하세요.");

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['nickname'] = $user['nickname'];
        echo "로그인 성공";
    } else {
        echo "정보 불일치";
    }
} catch (PDOException $e) { echo $e->getMessage(); }
?>