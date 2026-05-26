<?php
session_start();
require_once 'db_connect.php';

// 1. 사용자 입력값 받기 (GET 방식)
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지 (기본값 1)

// 2. 페이지네이션 설정
$limit = 10; // 한 페이지에 보여줄 레시피 개수
$offset = ($page - 1) * $limit; // DB에서 건너뛸 개수 (1페이지면 0개, 2페이지면 10개 건너뜀)

try {
    // 3. 동적 쿼리 작성 (검색어와 카테고리에 따라 WHERE 절이 달라짐)
    $whereClause = "WHERE 1=1"; // 기본 조건 (항상 참)
    $params = []; // PDO 파라미터 배열

    if (!empty($search)) {
        $whereClause .= " AND title LIKE :search";
        $params[':search'] = "%{$search}%"; // 앞뒤로 %를 붙여 포함하는 단어 검색
    }
    
    if (!empty($category)) {
        // 참고: DB의 recipes 테이블에 'category' 컬럼이 있어야 합니다!
        $whereClause .= " AND category = :category";
        $params[':category'] = $category;
    }

    // 4. 전체 데이터 개수 구하기 (페이지 네비게이션을 그리기 위해 필요)
    $countSql = "SELECT COUNT(*) FROM recipes $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total_records = $countStmt->fetchColumn();
    $total_pages = ceil($total_records / $limit); // 총 페이지 수 계산 (올림)

    // 5. 실제 데이터 가져오기 (LIMIT과 OFFSET 적용)
    // 기존의 복잡한 쿼리(일치율 계산 등)에 위에서 만든 $whereClause와 LIMIT을 붙입니다.
    $sql = "SELECT * FROM recipes 
            $whereClause 
            ORDER BY recipe_id DESC 
            LIMIT $limit OFFSET $offset"; 
            // (주의: 기존 일치율 쿼리를 쓰신다면 구조에 맞게 WHERE와 LIMIT을 끼워넣어야 합니다)

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll();

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

    <div class="search-box" style="margin-bottom: 20px; text-align: center;">
        <form method="GET" action="recipe.php">
            <select name="category" style="padding: 5px;">
                <option value="">전체보기</option>
                <option value="한식" <?= ($_GET['category'] ?? '') == '한식' ? 'selected' : '' ?>>한식</option>
                <option value="양식" <?= ($_GET['category'] ?? '') == '양식' ? 'selected' : '' ?>>양식</option>
                <option value="일식" <?= ($_GET['category'] ?? '') == '일식' ? 'selected' : '' ?>>일식</option>
            </select>

            <input type="text" name="search" placeholder="레시피 이름 검색..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="padding: 5px;">

            <button type="submit" style="padding: 5px 10px;">검색</button>
        </form>
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

            <div class="pagination" style="text-align: center; margin-top: 30px;">
                <?php
                // 검색 조건이 페이지 이동 시에도 유지되도록 쿼리스트링 생성
                $queryString = "";
                if(!empty($search)) $queryString .= "&search=".urlencode($search);
                if(!empty($category)) $queryString .= "&category=".urlencode($category);
                ?>
            
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $queryString ?>" style="padding: 5px;">[이전]</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <strong style="padding: 5px; color: green;"><?= $i ?></strong>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= $queryString ?>" style="padding: 5px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                    
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $queryString ?>" style="padding: 5px;">[다음]</a>
                <?php endif; ?>
            </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php include 'nav.php'; ?>
    
</body>
</html>