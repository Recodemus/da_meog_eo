<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); window.location.href='auth.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. 파라미터 이름 중복 방지 및 MAX() 적용
    $sql = "SELECT 
                r.recipe_id, r.title, r.is_external, r.source_url, r.cook_time,
                COUNT(ri.ingredient_id) AS total_needed,
                SUM(CASE WHEN uf.ingredient_id IS NOT NULL THEN 1 ELSE 0 END) AS matched_count,
                ROUND((SUM(CASE WHEN uf.ingredient_id IS NOT NULL THEN 1 ELSE 0 END) / COUNT(ri.ingredient_id)) * 100) AS match_rate,
                GROUP_CONCAT(CASE WHEN uf.ingredient_id IS NULL THEN i.name ELSE NULL END SEPARATOR ', ') AS missing_ingredients,
                MAX(CASE WHEN b.bookmark_id IS NOT NULL THEN 1 ELSE 0 END) AS is_scraped
            FROM recipes r
            JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id AND ri.is_essential = 1
            JOIN ingredients i ON ri.ingredient_id = i.ingredient_id
            LEFT JOIN user_fridge uf ON ri.ingredient_id = uf.ingredient_id AND uf.user_id = :u_id1
            LEFT JOIN bookmarks b ON r.recipe_id = b.recipe_id AND b.user_id = :u_id2
            GROUP BY r.recipe_id
            ORDER BY match_rate DESC, matched_count DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':u_id1' => $user_id, ':u_id2' => $user_id]); // 각각 매핑
    $recommended_recipes = $stmt->fetchAll();

} catch (PDOException $e) {
    die("에러 발생: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다 먹어 - 맞춤 레시피 추천</title>
    <style>
        /* 기본 UI 설정 */
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .sub-title { text-align: center; color: #666; margin-bottom: 30px; }
        
        /* 레시피 카드 목록 스타일 */
        .recipe-list { display: flex; flex-direction: column; gap: 15px; max-width: 600px; margin: 0 auto; }
        .recipe-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 10px; cursor: pointer; transition: transform 0.2s; border-left: 5px solid transparent; }
        .recipe-card:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        
        /* 일치율에 따른 테두리 색상 */
        .match-perfect { border-left-color: #4CAF50; } /* 100% 일치 */
        .match-good { border-left-color: #FFC107; }    /* 50% 이상 일치 */
        .match-low { border-left-color: #F44336; }     /* 50% 미만 일치 */

        /* 카드 내부 텍스트 디자인 */
        .recipe-title { font-size: 18px; font-weight: bold; color: #333; margin: 0; }
        .recipe-meta { font-size: 13px; color: #888; display: flex; gap: 10px; }
        .match-rate { font-weight: bold; }
        .rate-100 { color: #4CAF50; }
        
        /* 부족한 재료 표시 영역 */
        .missing-info { font-size: 13px; background-color: #fff4f4; padding: 10px; border-radius: 8px; color: #d32f2f; margin-top: 5px; }
        .ready-info { font-size: 13px; background-color: #e8f5e9; padding: 10px; border-radius: 8px; color: #2e7d32; margin-top: 5px; font-weight: bold; }
        
        /* 외부 링크 뱃지 */
        .badge { background-color: #eee; padding: 2px 6px; border-radius: 4px; font-size: 11px; color: #666; }
    </style>
</head>
<body>

    <div class="header">
        <h2>🧑‍🍳 맞춤 레시피 추천</h2>
        <div class="sub-title">내 냉장고 재료를 바탕으로 가장 만들기 쉬운 요리를 찾아드려요!</div>
    </div>

    <div class="recipe-list">
        <?php if (empty($recommended_recipes)): ?>
            <div style="text-align:center; color:#999;">등록된 레시피가 없습니다.</div>
        <?php else: ?>
            <?php foreach ($recommended_recipes as $recipe): 
                
                // 일치율에 따른 클래스 부여
                $rate = $recipe['match_rate'];
                $card_class = '';
                if ($rate == 100) $card_class = 'match-perfect';
                elseif ($rate >= 50) $card_class = 'match-good';
                else $card_class = 'match-low';

                // 클릭 시 이동할 URL 설정 (외부 링크 vs 자체 제공)
                $link = $recipe['is_external'] ? $recipe['source_url'] : "recipe_detail.php?id=" . $recipe['recipe_id'];
            ?>
            
            <div class="recipe-card <?= $card_class ?>" onclick="window.open('<?= $link ?>', '_blank')">
<div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="recipe-title">
                        <?= htmlspecialchars($recipe['title']) ?>
                        <?php if($recipe['is_external']): ?> <span class="badge">외부 링크</span> <?php endif; ?>
                    </h3>
                    
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <span class="match-rate <?= $rate == 100 ? 'rate-100' : '' ?>">일치율 <?= $rate ?>%</span>
                        <span class="scrap-btn" id="scrap-<?= $recipe['recipe_id'] ?>" 
                              onclick="toggleScrap(event, <?= $recipe['recipe_id'] ?>)" 
                              style="font-size: 20px; cursor: pointer;">
                            <?= $recipe['is_scraped'] ? '⭐' : '☆' ?>
                        </span>
                    </div>
                </div>

<script>
    function toggleScrap(event, recipeId) {
        event.stopPropagation(); // 중요! 카드를 클릭했을 때 새 창으로 넘어가는 것을 막아줍니다.

        const formData = new FormData();
        formData.append('recipe_id', recipeId);

        fetch('scrap_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(status => {
            const starSpan = document.getElementById('scrap-' + recipeId);
            if (status === 'added') {
                starSpan.innerText = '⭐'; // 색칠된 별
            } else if (status === 'removed') {
                starSpan.innerText = '☆'; // 빈 별
            } else {
                alert('오류가 발생했습니다.');
            }
        });
    }
</script>
                
                <div class="recipe-meta">
                    <span>⏱️ 조리 시간: <?= $recipe['cook_time'] ? $recipe['cook_time'].'분' : '미상' ?></span>
                    <span>🛒 필요 재료: <?= $recipe['matched_count'] ?> / <?= $recipe['total_needed'] ?> 개 보유</span>
                </div>

                <?php if ($rate == 100 || empty($recipe['missing_ingredients'])): ?>
                    <div class="ready-info">✨ 모든 재료가 준비되었어요! 바로 조리 가능합니다.</div>
                <?php else: ?>
                    <div class="missing-info">🚨 부족한 재료: <?= htmlspecialchars($recipe['missing_ingredients']) ?></div>
                <?php endif; ?>
            </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php include 'nav.php'; ?>
    
</body>
</html>