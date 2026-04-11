<?php
session_start();
require_once 'db_connect.php';
$user_id = $_SESSION['user_id'] ?? 1; // 테스트용으로 임시 user_id 1 고정 가능

try {
    /* 핵심 SQL 설명:
     1. 레시피별로 필요한 '필수 재료'의 총 개수를 구합니다. (COUNT)
     2. 사용자의 냉장고(user_fridge)와 대조하여 일치하는 재료의 개수를 구합니다. (SUM)
     3. (일치하는 재료 수 / 필요 재료 총 개수) * 100 으로 일치율(match_rate)을 계산합니다.
    */
    $sql = "SELECT 
                r.recipe_id, 
                r.title,
                COUNT(ri.ingredient_id) AS total_needed,
                SUM(CASE WHEN uf.ingredient_id IS NOT NULL THEN 1 ELSE 0 END) AS matched_count,
                ROUND((SUM(CASE WHEN uf.ingredient_id IS NOT NULL THEN 1 ELSE 0 END) / COUNT(ri.ingredient_id)) * 100) AS match_rate
            FROM recipes r
            JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
            -- 회원의 냉장고 데이터만 매칭시키기 위해 LEFT JOIN 시 user_id 조건 추가
            LEFT JOIN user_fridge uf ON ri.ingredient_id = uf.ingredient_id AND uf.user_id = :user_id
            WHERE ri.is_essential = 1 -- 필수 재료만 계산에 포함
            GROUP BY r.recipe_id
            ORDER BY match_rate DESC, matched_count DESC"; // 일치율 높은 순 정렬
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $recommended_recipes = $stmt->fetchAll();

    // 결과 출력 테스트
    foreach ($recommended_recipes as $recipe) {
        echo "레시피: " . $recipe['title'] . "<br>";
        echo "일치율: " . $recipe['match_rate'] . "% (" . $recipe['matched_count'] . "/" . $recipe['total_needed'] . " 재료 보유)<br><hr>";
    }

} catch (PDOException $e) {
    echo "레시피 추천 에러: " . $e->getMessage();
}
?>