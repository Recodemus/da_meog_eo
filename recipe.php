<?php
session_start();
require_once 'db_connect.php';

// 1. 사용자 입력값 받기
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 

// 2. 페이지네이션 설정
$limit = 10; 
$offset = ($page - 1) * $limit; 

try {
    // 3. 동적 쿼리 작성
    $whereClause = "WHERE 1=1"; 
    $params = []; 

    if (!empty($search)) {
        $whereClause .= " AND title LIKE :search";
        $params[':search'] = "%{$search}%"; 
    }
    
    if (!empty($category)) {
        $whereClause .= " AND category = :category";
        $params[':category'] = $category;
    }

    // 4. 전체 데이터 개수 구하기
    $countSql = "SELECT COUNT(*) FROM recipes $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total_records = $countStmt->fetchColumn();
    $total_pages = ceil($total_records / $limit); 

    // 5. 실제 데이터 가져오기
    $sql = "SELECT * FROM recipes 
            $whereClause 
            ORDER BY recipe_id DESC 
            LIMIT $limit OFFSET $offset"; 

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $raw_recipes = $stmt->fetchAll();

    // 🌟 수정: 화면 오류 방지를 위해 필요한 데이터를 채워서 $recommended_recipes에 담아줍니다.
    $recommended_recipes = [];
    foreach ($raw_recipes as $r) {
        $r['match_rate'] = 0; // 아직 추천 쿼리가 없으므로 임시값 설정
        $r['is_external'] = !empty($r['source_url']) ? 1 : 0;
        $r['is_scraped'] = 0; // 임시값
        $r['matched_count'] = 0; // 임시값
        $r['total_needed'] = 0; // 임시값
        $r['missing_ingredients'] = '추천 알고리즘 연결 필요'; // 임시값
        $recommended_recipes[] = $r;
    }

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
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; padding-bottom: 80px; }
        .header { text-align: center; margin-bottom: 20px; }
        .sub-title { text-align: center; color: #666; margin-bottom: 30px; }
        
        .recipe-list { display: flex; flex-direction: column; gap: 15px; max-width: 600px; margin: 0 auto; }
        .recipe-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 10px; cursor: pointer; transition: transform 0.2s; border-left: 5px solid transparent; }
        .recipe-card:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        
        .match-perfect { border-left-color: #4CAF50; } 
        .match-good { border-left-color: #FFC107; }    
        .match-low { border-left-color: #F44336; }     

        .recipe-title { font-size: 18px; font-weight: bold; color: #333; margin: 0; }
        .recipe-meta { font-size: 13px; color: #888; display: flex; gap: 10px; }
        .match-rate { font-weight: bold; }
        .rate-100 { color: #4CAF50; }
        
        .missing-info { font-size: 13px; background-color: #fff4f4; padding: 10px; border-radius: 8px; color: #d32f2f; margin-top: 5px; }
        .ready-info { font-size: 13px; background-color: #e8f5e9; padding: 10px; border-radius: 8px; color: #2e7d32; margin-top: 5px; font-weight: bold; }
        
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
                <option value="한식" <?= ($category == '한식') ? 'selected' : '' ?>>한식</option>
                <option value="양식" <?= ($category == '양식') ? 'selected' : '' ?>>양식</option>
                <option value="일식" <?= ($category == '일식') ? 'selected' : '' ?>>일식</option>
            </select>

            <input type="text" name="search" placeholder="레시피 이름 검색..." 
                   value="<?= htmlspecialchars($search) ?>" style="padding: 5px;">

            <button type="submit" style="padding: 5px 10px;">검색</button>
        </form>
    </div>

    <div class="recipe-list">
        <?php if (empty($recommended_recipes)): ?>
            <div style="text-align:center; color:#999;">검색된 레시피가 없습니다.</div>
        <?php else: ?>
            <?php foreach ($recommended_recipes as $recipe): 
                
                $rate = $recipe['match_rate'];
                $card_class = '';
                if ($rate == 100) $card_class = 'match-perfect';
                elseif ($rate >= 50) $card_class = 'match-good';
                else $card_class = 'match-low';

                $link = $recipe['is_external'] ? $recipe['source_url'] : "recipe_detail.php?id=" . $recipe['recipe_id'];
            ?>
            
            <div class="recipe-card <?= $card_class ?>" onclick="window.location.href='<?= htmlspecialchars($link) ?>'">
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

                <div class="recipe-meta">
                    <span>⏱️ 조리 시간: <?= $recipe['cook_time'] ? htmlspecialchars($recipe['cook_time']).'분' : '미상' ?></span>
                    <span>🛒 필요 재료: <?= $recipe['matched_count'] ?> / <?= $recipe['total_needed'] ?> 개 보유</span>
                </div>

                <?php if ($rate == 100 || empty($recipe['missing_ingredients'])): ?>
                    <div class="ready-info">✨ 모든 재료가 준비되었어요! 바로 조리 가능합니다.</div>
                <?php else: ?>
                    <div class="missing-info">🚨 부족한 재료: <?= htmlspecialchars($recipe['missing_ingredients']) ?></div>
                <?php endif; ?>
            </div>

            <?php endforeach; ?> <?php endif; ?>

        <div class="pagination" style="text-align: center; margin-top: 30px;">
            <?php
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
    </div>

    <script>
        function toggleScrap(event, recipeId) {
            event.stopPropagation(); 

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
                    starSpan.innerText = '⭐'; 
                } else if (status === 'removed') {
                    starSpan.innerText = '☆'; 
                } else {
                    alert('오류가 발생했습니다.');
                }
            });
        }
    </script>

<?php include 'nav.php'; ?>
    
</body>
</html>