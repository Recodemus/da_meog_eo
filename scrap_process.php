<?php
session_start();
require_once 'db_connect.php';

// 비정상적인 접근 차단
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || empty($_POST['recipe_id'])) {
    die("error");
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_POST['recipe_id'];

try {
    // 1. 이미 스크랩한 레시피인지 확인
    $check_sql = "SELECT bookmark_id FROM bookmarks WHERE user_id = :user_id AND recipe_id = :recipe_id";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
    $is_bookmarked = $stmt->fetch();

    if ($is_bookmarked) {
        // 2. 이미 있다면 -> 스크랩 취소 (삭제)
        $del_sql = "DELETE FROM bookmarks WHERE user_id = :user_id AND recipe_id = :recipe_id";
        $pdo->prepare($del_sql)->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
        echo "removed";
    } else {
        // 3. 없다면 -> 스크랩 등록 (추가)
        $ins_sql = "INSERT INTO bookmarks (user_id, recipe_id) VALUES (:user_id, :recipe_id)";
        $pdo->prepare($ins_sql)->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
        echo "added";
    }
} catch (PDOException $e) {
    echo "error";
}
?>