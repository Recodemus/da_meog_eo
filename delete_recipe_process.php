<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    die("error");
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_POST['recipe_id'] ?? '';

try {
    // 1. 본인이 작성한 레시피가 맞는지 권한 확인
    $check_sql = "SELECT recipe_id FROM recipes WHERE recipe_id = :recipe_id AND author_id = :user_id";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([':recipe_id' => $recipe_id, ':user_id' => $user_id]);
    
    if (!$stmt->fetch()) {
        die("error");
    }

    // 2. 삭제 시 관련 데이터(재료, 스크랩)도 함께 삭제 (트랜잭션)
    $pdo->beginTransaction();
    
    $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?")->execute([$recipe_id]);
    $pdo->prepare("DELETE FROM bookmarks WHERE recipe_id = ?")->execute([$recipe_id]);
    $pdo->prepare("DELETE FROM recipes WHERE recipe_id = ?")->execute([$recipe_id]);
    
    $pdo->commit();
    echo "success";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "error";
}
?>