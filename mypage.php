<?php
session_start();
require_once 'db_connect.php';

// 1. 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다.'); window.location.href='auth.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 2. 사용자가 스크랩한 레시피 불러오기
    $sql_scrap = "SELECT r.recipe_id, r.title, r.thumbnail_url, r.cook_time 
                  FROM bookmarks b 
                  JOIN recipes r ON b.recipe_id = r.recipe_id 
                  WHERE b.user_id = :user_id";
    $stmt_scrap = $pdo->prepare($sql_scrap);
    $stmt_scrap->execute([':user_id' => $user_id]);
    $scrapped_recipes = $stmt_scrap->fetchAll();

    // 3. 내가 직접 작성한 레시피 불러오기
    $sql_my = "SELECT recipe_id, title, thumbnail_url, cook_time 
               FROM recipes 
               WHERE author_id = :user_id 
               ORDER BY created_at DESC";
    $stmt_my = $pdo->prepare($sql_my);
    $stmt_my->execute([':user_id' => $user_id]);
    $my_recipes = $stmt_my->fetchAll();

} catch (PDOException $e) {
    die("데이터 불러오기 실패: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다 먹어 - 마이페이지</title>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; padding-bottom: 80px; }
        .header { text-align: center; margin-bottom: 20px; }
        
        /* 탭 메뉴 스타일 */
        .tab-container { display: flex; justify-content: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; }
        .tab-button { background: none; border: none; padding: 10px 20px; font-size: 16px; font-weight: bold; color: #999; cursor: pointer; }
        .tab-button.active { color: #4CAF50; border-bottom: 3px solid #4CAF50; }
        
        /* 레시피 카드 그리드 */
        .recipe-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .recipe-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-decoration: none; color: inherit; display: block; position: relative; }
        .recipe-image { width: 100%; height: 120px; object-fit: cover; background-color: #eee; }
        .recipe-info { padding: 10px; }
        .recipe-title { font-weight: bold; font-size: 14px; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .recipe-time { font-size: 12px; color: #666; }
        
        /* 스크랩 취소 버튼 */
        .btn-unscrap { position: absolute; top: 8px; right: 8px; background: rgba(255, 255, 255, 0.9); border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; color: #ff6b6b; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        .empty-message { text-align: center; color: #999; padding: 40px 20px; width: 100%; grid-column: 1 / -1; }
        
        /* 내가 쓴 레시피 영역 (초기에는 숨김) */
        #myRecipes { display: none; }
    </style>
</head>
<body>

    <div class="header">
        <h2>👤 마이페이지</h2>
    </div>

    <!-- 탭 메뉴 -->
    <div class="tab-container">
        <button class="tab-button active" onclick="switchTab('scraps')">⭐ 스크랩한 레시피</button>
        <button class="tab-button" onclick="switchTab('myRecipes')">🧑‍🍳 나의 레시피</button>
    </div>

    <!-- 1. 스크랩 레시피 영역 -->
    <div id="scraps" class="recipe-grid">
        <?php if (empty($scrapped_recipes)): ?>
            <div class="empty-message">아직 스크랩한 레시피가 없습니다.<br>마음에 드는 레시피를 저장해 보세요!</div>
        <?php else: ?>
            <?php foreach($scrapped_recipes as $recipe): ?>
                <div class="recipe-card" id="recipe-<?= $recipe['recipe_id'] ?>">
                    <a href="recipe_detail.php?id=<?= $recipe['recipe_id'] ?>">
                        <img src="<?= htmlspecialchars($recipe['thumbnail_url'] ?? 'https://via.placeholder.com/150?text=No+Image') ?>" alt="레시피 이미지" class="recipe-image">
                        <div class="recipe-info">
                            <div class="recipe-title"><?= htmlspecialchars($recipe['title']) ?></div>
                            <!-- DB에서 숫자로 바꿨으므로 화면에 출력할 때 '분'을 붙여줍니다 -->
                            <div class="recipe-time">⏱️ <?= htmlspecialchars($recipe['cook_time'] ?? '미상') ?>분</div>
                        </div>
                    </a>
                    <button class="btn-unscrap" onclick="removeScrap(<?= $recipe['recipe_id'] ?>)" title="스크랩 취소">❌</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 🌟 2. 나의 레시피 영역 (버튼 및 목록 추가 완료) -->
    <div id="myRecipes" class="recipe-grid">
        
        <div style="grid-column: 1 / -1; text-align: right; margin-bottom: 10px;">
            <a href="create_recipe.php" style="display: inline-block; padding: 10px 20px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                + 새 레시피 작성하기 🧑‍🍳
            </a>
        </div>

        <?php if (empty($my_recipes)): ?>
            <div class="empty-message">아직 등록한 나만의 레시피가 없습니다.<br>나만의 특별한 요리법을 기록해 보세요!</div>
        <?php else: ?>
            <?php foreach($my_recipes as $recipe): ?>
                <div class="recipe-card" id="my-recipe-<?= $recipe['recipe_id'] ?>">
                    <a href="recipe_detail.php?id=<?= $recipe['recipe_id'] ?>">
                        <img src="<?= htmlspecialchars($recipe['thumbnail_url'] ?? 'https://via.placeholder.com/150?text=No+Image') ?>" alt="레시피 이미지" class="recipe-image">
                        <div class="recipe-info">
                            <div class="recipe-title"><?= htmlspecialchars($recipe['title']) ?></div>
                            <div class="recipe-time">⏱️ <?= htmlspecialchars($recipe['cook_time'] ?? '미상') ?>분</div>
                        </div>
                    </a>
                    <div style="position: absolute; top: 8px; right: 8px; display: flex; gap: 5px;">
                        <button onclick="window.location.href='edit_recipe.php?id=<?= $recipe['recipe_id'] ?>'" style="background: rgba(255,255,255,0.9); border: none; border-radius: 5px; cursor: pointer; padding: 5px;" title="수정">✏️</button>
                        <button onclick="deleteRecipe(<?= $recipe['recipe_id'] ?>)" style="background: rgba(255,255,255,0.9); border: none; border-radius: 5px; cursor: pointer; padding: 5px;" title="삭제">🗑️</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // 기존 탭 전환 및 스크랩 취소 기능 유지
        function switchTab(tabId) {
            document.getElementById('scraps').style.display = tabId === 'scraps' ? 'grid' : 'none';
            document.getElementById('myRecipes').style.display = tabId === 'myRecipes' ? 'grid' : 'none';
            
            const buttons = document.querySelectorAll('.tab-button');
            buttons[0].classList.toggle('active', tabId === 'scraps');
            buttons[1].classList.toggle('active', tabId === 'myRecipes');
        }

        function removeScrap(recipeId) {
            if(!confirm('이 레시피를 스크랩에서 삭제하시겠습니까?')) return;
            const formData = new FormData();
            formData.append('recipe_id', recipeId);
            fetch('scrap_process.php', { method: 'POST', body: formData })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'removed') {
                    const card = document.getElementById('recipe-' + recipeId);
                    if (card) card.style.display = 'none';
                }
            });
        }

        // 🌟 새로 추가된 레시피 삭제 기능
        function deleteRecipe(recipeId) {
            if(!confirm('정말로 이 레시피를 삭제하시겠습니까?\n삭제 후에는 복구할 수 없습니다.')) return;

            const formData = new FormData();
            formData.append('recipe_id', recipeId);

            fetch('delete_recipe_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    const card = document.getElementById('my-recipe-' + recipeId);
                    if (card) card.style.display = 'none';
                } else {
                    alert('삭제 처리 중 오류가 발생했습니다.');
                }
            });
        }
    </script>
    
<?php include 'nav.php'; ?>
    
</body>
</html>