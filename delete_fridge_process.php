<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("로그인이 필요합니다.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['fridge_id'])) {
    die("잘못된 요청입니다.");
}

$user_id = $_SESSION['user_id'];
$fridge_id = $_POST['fridge_id'];

try {
    // 본인의 냉장고 재료가 맞는지 확인하며 삭제 (보안)
    $sql = "DELETE FROM user_fridge WHERE fridge_id = :fridge_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fridge_id' => $fridge_id,
        ':user_id' => $user_id
    ]);
    
    echo "소비 완료 (삭제 성공)";
} catch (PDOException $e) {
    echo "삭제 에러: " . $e->getMessage();
}
?>