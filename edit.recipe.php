<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || empty($_GET['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_GET['id'];

try {
    // 기존 레시피 데이터 불러오기 (권한 체크 포함)
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE recipe_id = :recipe_id AND author_id = :user_id");
    $stmt->execute([':recipe_id' => $recipe_id, ':user_id' => $user_id]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        echo "<script>alert('수정 권한이 없거나 존재하지 않는 레시피입니다.'); history.back();</script>";
        exit;
    }

    // 전체 식재료 목록 및 이미 선택되었던 재료 불러오기
    $stmt_ing = $pdo->query("SELECT ingredient_id, name, icon_url FROM ingredients ORDER BY name ASC");
    $ingredients_list = $stmt_ing->fetchAll();

    $stmt_selected = $pdo->prepare("SELECT ingredient_id FROM recipe_ingredients WHERE recipe_id = ?");
    $stmt_selected->execute([$recipe_id]);
    $selected_ing_ids = $stmt_selected->fetchAll(PDO::FETCH_COLUMN); // ID만 배열로 추출

} catch (PDOException $e) {
    die("데이터 불러오기 실패: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>레시피 수정 - 다 먹어</title>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; }
        .form-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        textarea { resize: vertical; height: 150px; }
        .btn-submit { background-color: #4CAF50; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-back { display: inline-block; padding: 8px 15px; margin-bottom: 20px; text-decoration: none; color: #333; border: 1px solid #ddd; border-radius: 5px; background-color: #fff; }
    </style>
</head>
<body>
    
    <div class="form-container">
        <a href="mypage.php" class="btn-back">⬅️ 취소하고 돌아가기</a>
        <h2 style="text-align: center; margin-top: 0;">✏️ 레시피 수정</h2>
        
        <form action="edit_recipe_process.php" method="POST">
            <input type="hidden" name="recipe_id" value="<?= $recipe['recipe_id'] ?>">

            <div class="form-group">
                <label for="title">레시피 제목</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($recipe['title']) ?>">
            </div>
            
            <div class="form-group">
                <label for="cook_time">조리 시간 (분 단위)</label>
                <input type="number" id="cook_time" name="cook_time" value="<?= htmlspecialchars($recipe['cook_time']) ?>">
            </div>

            <div class="form-group">
                <label for="thumbnail_url">대표 이미지 URL</label>
                <input type="text" id="thumbnail_url" name="thumbnail_url" value="<?= htmlspecialchars($recipe['thumbnail_url']) ?>">
            </div>
            
            <div class="form-group">
                <label for="source_url">🔗 외부 레시피 링크 (만개의 레시피, 유튜브 등)</label>
                <input type="text" id="source_url" name="source_url" value="<?= htmlspecialchars($recipe['source_url']) ?>">
            </div>
            
            <div class="form-group">
                <label>필요한 재료 선택</label>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; background: #fafafa;">
                    <?php foreach($ingredients_list as $ing): ?>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: normal;">
                            <input type="checkbox" name="ingredients[]" value="<?= $ing['ingredient_id'] ?>" <?= in_array($ing['ingredient_id'], $selected_ing_ids) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($ing['icon_url'] ?? '') ?> <?= htmlspecialchars($ing['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="content">상세 조리법</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($recipe['content']) ?></textarea>
            </div>
            
            <button type="submit" class="btn-submit">수정 완료하기</button>
        </form>
    </div>
</body>
</html>