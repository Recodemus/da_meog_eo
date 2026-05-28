<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); window.location.href='auth.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_POST['recipe_id'] ?? '';
$title = $_POST['title'] ?? '';
$cook_time = $_POST['cook_time'] ?? 0;
$thumbnail_url = $_POST['thumbnail_url'] ?? '';
$content = $_POST['content'] ?? '';
$source_url = $_POST['source_url'] ?? '';
$selected_ingredients = $_POST['ingredients'] ?? []; 
$is_external = !empty($source_url) ? 1 : 0; 

if (empty($recipe_id) || empty($title) || empty($content)) {
    echo "<script>alert('필수 입력 사항이 누락되었습니다.'); history.back();</script>";
    exit;
}

try {
    // 본인 확인 
    $check_stmt = $pdo->prepare("SELECT recipe_id FROM recipes WHERE recipe_id = ? AND author_id = ?");
    $check_stmt->execute([$recipe_id, $user_id]);
    if (!$check_stmt->fetch()) {
        echo "<script>alert('수정 권한이 없습니다.'); history.back();</script>";
        exit;
    }

    $pdo->beginTransaction();

    // 1. 레시피 기본 정보 업데이트
    $update_sql = "UPDATE recipes SET title = :title, cook_time = :cook_time, thumbnail_url = :thumbnail_url, content = :content, source_url = :source_url, is_external = :is_external WHERE recipe_id = :recipe_id";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([
        ':title' => $title,
        ':cook_time' => $cook_time,
        ':thumbnail_url' => $thumbnail_url,
        ':content' => $content,
        ':source_url' => $source_url,
        ':is_external' => $is_external,
        ':recipe_id' => $recipe_id
    ]);

    // 2. 기존 재료 연결 삭제 후 새로운 재료 목록으로 덮어쓰기
    $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?")->execute([$recipe_id]);
    
    if (!empty($selected_ingredients)) {
        $ing_stmt = $pdo->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (:recipe_id, :ingredient_id)");
        foreach ($selected_ingredients as $ing_id) {
            $ing_stmt->execute([':recipe_id' => $recipe_id, ':ingredient_id' => $ing_id]);
        }
    }

    $pdo->commit();
    echo "<script>alert('레시피가 성공적으로 수정되었습니다!'); window.location.href='mypage.php';</script>";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<script>alert('데이터베이스 오류가 발생했습니다.'); history.back();</script>";
}
?>