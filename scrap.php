<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 내가 스크랩한 레시피만 가져오는 쿼리
    $sql = "SELECT r.recipe_id, r.title, r.is_external, r.source_url, r.cook_time
            FROM recipes r
            JOIN bookmarks b ON r.recipe_id = b.recipe_id
            WHERE b.user_id = :user_id
            ORDER BY b.created_at DESC"; // 최근 스크랩한 순서
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $scraped_recipes = $stmt->fetchAll();

} catch (PDOException $e) {
    die("데이터 로드 실패: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다 먹어 - 스크랩한 레시피</title>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .recipe-list { display: flex; flex-direction: column; gap: 15px; max-width: 600px; margin: 0 auto; }
        .recipe-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none; color: inherit; display: block; }
        .badge { background-color: #eee; padding: 2px 6px; border-radius: 4px; font-size: 11px; color: #666; }
        .back-btn { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #4CAF50; font-weight: bold; }
    </style>
</head>
<body>

    <a href="mypage.php" class="back-btn">❮ 마이페이지로 돌아가기</a>

    <div class="header">
        <h2>⭐ 스크랩한 레시피</h2>
    </div>

    <div class="recipe-list">
        <?php if (empty($scraped_recipes)): ?>
            <div style="text-align:center; color:#999; padding: 30px;">아직 스크랩한 레시피가 없습니다.</div>
        <?php else: ?>
            <?php foreach ($scraped_recipes as $recipe): 
                $link = $recipe['is_external'] ? $recipe['source_url'] : "recipe_detail.php?id=" . $recipe['recipe_id'];
            ?>
            <a href="<?= $link ?>" class="recipe-card" <?= $recipe['is_external'] ? 'target="_blank"' : '' ?>>
                <h3 style="margin: 0 0 10px 0;">
                    <?= htmlspecialchars($recipe['title']) ?>
                    <?php if($recipe['is_external']): ?> <span class="badge">외부 링크</span> <?php endif; ?>
                </h3>
                <div style="font-size: 13px; color: #888;">
                    ⏱️ 조리 시간: <?= $recipe['cook_time'] ? $recipe['cook_time'].'분' : '미상' ?>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'nav.php'; ?>
</body>
</html>