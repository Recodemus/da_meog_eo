<?php
session_start();
require_once 'db_connect.php';

// 로그인 안 된 사용자는 로그인 화면으로 이동
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nickname = htmlspecialchars($_SESSION['nickname']);

try {
    // 1. 소비기한 임박(3일 이내) 재료 개수 파악
    $sql_warn = "SELECT COUNT(*) as warn_count 
                 FROM user_fridge 
                 WHERE user_id = :user_id AND DATEDIFF(expiry_date, CURDATE()) <= 3";
    $stmt_warn = $pdo->prepare($sql_warn);
    $stmt_warn->execute([':user_id' => $user_id]);
    $warn_result = $stmt_warn->fetch();
    $warn_count = $warn_result['warn_count'];

    // 2. 홈 화면에 띄워줄 추천 레시피 (임의로 2개만 추출 - 빠른 추천 용도)
    $sql_recipe = "SELECT recipe_id, title, cook_time, is_external, source_url 
                   FROM recipes 
                   ORDER BY RAND() LIMIT 2"; // 실제로는 최근 본 레시피나 개인화 추천 로직이 들어가면 좋습니다.
    $stmt_recipe = $pdo->query($sql_recipe);
    $quick_recipes = $stmt_recipe->fetchAll();

} catch (PDOException $e) {
    die("홈 데이터 로드 실패: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다 먹어 - 홈</title>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; }
        
        /* 상단 환영 메시지 */
        .header-greeting { font-size: 24px; font-weight: bold; margin-bottom: 20px; color: #333; }
        .header-greeting span { color: #4CAF50; }

        /* 경고 팝업 카드 (소비기한 임박) */
        .alert-card { background: linear-gradient(135deg, #ff7675, #d63031); color: white; padding: 20px; border-radius: 16px; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(214, 48, 49, 0.3); display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: transform 0.2s; }
        .alert-card:active { transform: scale(0.98); }
        .alert-card .icon { font-size: 40px; margin-right: 15px; }
        .alert-card .text h3 { margin: 0 0 5px 0; font-size: 18px; }
        .alert-card .text p { margin: 0; font-size: 13px; opacity: 0.9; }

        /* 대시보드 (빠른 메뉴) */
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
        .dash-btn { background: white; padding: 20px; border-radius: 16px; text-align: center; text-decoration: none; color: #333; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: background 0.2s; }
        .dash-btn:active { background: #f0f0f0; }
        .dash-btn .icon { font-size: 30px; margin-bottom: 10px; }
        .dash-btn .title { font-weight: bold; font-size: 15px; }

        /* 오늘의 빠른 추천 */
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
        .quick-recipe-list { display: flex; flex-direction: column; gap: 12px; }
        .quick-recipe-card { background: white; padding: 15px; border-radius: 12px; display: flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-decoration: none; color: inherit; }
        .quick-recipe-card .icon { font-size: 24px; background: #e8f5e9; padding: 10px; border-radius: 10px; margin-right: 15px; }
        .quick-recipe-card .info h4 { margin: 0 0 5px 0; font-size: 15px; }
        .quick-recipe-card .info p { margin: 0; font-size: 12px; color: #888; }
    </style>
</head>
<body>

    <div class="header-greeting">
        안녕하세요, <span><?= $nickname ?></span>님!<br>오늘 뭐 먹을까요?
    </div>

    <?php if ($warn_count > 0): ?>
        <div class="alert-card" onclick="window.location.href='my_fridge.php'">
            <div style="display: flex; align-items: center;">
                <div class="icon">🚨</div>
                <div class="text">
                    <h3>구출이 시급해요!</h3>
                    <p>소비기한이 3일 이하로 남은 재료가 <b><?= $warn_count ?>개</b> 있습니다.</p>
                </div>
            </div>
            <div style="font-size: 20px;">👉</div>
        </div>
    <?php else: ?>
        <div class="alert-card" style="background: linear-gradient(135deg, #4CAF50, #2E7D32); box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);">
            <div style="display: flex; align-items: center;">
                <div class="icon">✨</div>
                <div class="text">
                    <h3>냉장고 평화 유지 중</h3>
                    <p>버려질 위기에 처한 재료가 없습니다!</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <a href="my_fridge.php" class="dash-btn">
            <div class="icon">🛒</div>
            <div class="title">재료 등록하기</div>
        </a>
        <a href="recipe.php" class="dash-btn">
            <div class="icon">🍳</div>
            <div class="title">냉장고 파먹기</div>
        </a>
    </div>

    <div class="section-title">오늘의 빠른 추천</div>
    <div class="quick-recipe-list">
        <?php foreach ($quick_recipes as $r): 
            $link = $r['is_external'] ? $r['source_url'] : "recipe_detail.php?id=" . $r['recipe_id'];
        ?>
            <a href="<?= $link ?>" class="quick-recipe-card" <?= $r['is_external'] ? 'target="_blank"' : '' ?>>
                <div class="icon">🥘</div>
                <div class="info">
                    <h4><?= htmlspecialchars($r['title']) ?></h4>
                    <p>⏱️ 약 <?= $r['cook_time'] ?>분 소요</p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php include 'nav.php'; ?>

</body>
</html>