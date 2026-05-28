<?php
session_start();
require_once 'db_connect.php';

// 1. 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); window.location.href='auth.php';</script>";
    exit;
}

// 2. 폼에서 보낸 데이터 받기
$user_id = $_SESSION['user_id'];
$title = $_POST['title'] ?? '';
$cook_time = $_POST['cook_time'] ?? 0; // 숫자로 받기
$thumbnail_url = $_POST['thumbnail_url'] ?? '';
$content = $_POST['content'] ?? '';
$selected_ingredients = $_POST['ingredients'] ?? []; 

// 🌟 추가된 외부 링크 데이터 받기
$source_url = $_POST['source_url'] ?? '';
// source_url에 값이 있으면 외부 링크(1), 없으면 직접 작성(0)으로 자동 판별
$is_external = !empty($source_url) ? 1 : 0; 

if (empty($title) || empty($content)) {
    echo "<script>alert('제목과 조리법은 필수 입력 사항입니다.'); history.back();</script>";
    exit;
}

try {
    $pdo->beginTransaction();

    // 3. DB에 레시피 저장 (is_external, source_url 추가)
    $sql = "INSERT INTO recipes (title, cook_time, thumbnail_url, content, author_id, is_external, source_url) 
            VALUES (:title, :cook_time, :thumbnail_url, :content, :author_id, :is_external, :source_url)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':cook_time' => $cook_time,
        ':thumbnail_url' => $thumbnail_url,
        ':content' => $content,
        ':author_id' => $user_id,
        ':is_external' => $is_external,
        ':source_url' => $source_url
    ]);

    $new_recipe_id = $pdo->lastInsertId();

    // 4. 재료 저장
    if (!empty($selected_ingredients)) {
        $ing_sql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (:recipe_id, :ingredient_id)";
        $ing_stmt = $pdo->prepare($ing_sql);

        foreach ($selected_ingredients as $ing_id) {
            $ing_stmt->execute([
                ':recipe_id' => $new_recipe_id,
                ':ingredient_id' => $ing_id
            ]);
        }
    }

    $pdo->commit();

    echo "<script>alert('레시피가 성공적으로 등록되었습니다!'); window.location.href='recipe.php';</script>";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<script>alert('데이터베이스 오류: " . addslashes($e->getMessage()) . "'); history.back();</script>";
}
?>