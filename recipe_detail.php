<?php
session_start();
require_once 'db_connect.php';

// URL에서 id 값 가져오기 (예: ?id=1 이면 1을 가져옴)
$recipe_id = $_GET['id'] ?? null;

// id가 없으면 튕겨내기
if (!$recipe_id) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

try {
    // DB에서 해당 id의 레시피 정보 불러오기
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE recipe_id = :id");
    $stmt->execute([':id' => $recipe_id]);
    $recipe = $stmt->fetch();

    // DB에 해당 레시피가 없으면 튕겨내기
    if (!$recipe) {
        echo "<script>alert('존재하지 않는 레시피입니다.'); history.back();</script>";
        exit;
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
    <title><?= htmlspecialchars($recipe['title']) ?> - 다 먹어</title>
    <style>
        /* 버튼 스타일을 여기에 넣으면 더 깔끔합니다 */
        .btn-external {
            display: inline-block; 
            padding: 10px 20px; 
            background: #ff6b6b; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn-external:hover {
            background: #fa5252;
        }
        .external-link-box {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div style="padding: 20px;">
        <h2>🍽️ <?= htmlspecialchars($recipe['title']) ?></h2>
        <p>⏱️ 조리 시간: <?= htmlspecialchars($recipe['cook_time'] ?? '미상') ?></p>
        
        <?php if (!empty($recipe['thumbnail_url'])): ?>
            <img src="<?= htmlspecialchars($recipe['thumbnail_url']) ?>" alt="레시피 이미지" style="max-width: 100%; border-radius: 8px;">
        <?php endif; ?>

        <hr>

        <div class="recipe-content-area">
            <?php if (!empty($recipe['source_url'])): ?>
                <div class="external-link-box">
                    <p>이 레시피는 외부 사이트에서 제공하는 상세 레시피입니다.</p>
                    <a href="<?= htmlspecialchars($recipe['source_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-external">
                        자세한 레시피 보러가기 🍳
                    </a>
                </div>
            <?php else: ?>
                <div class="internal-recipe-steps">
                    <p><?= nl2br(htmlspecialchars($recipe['content'] ?? '상세 조리법이 준비 중입니다.')) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <br><br>
        
        <button onclick="goBack()" style="padding: 8px 16px; cursor: pointer; background: #fff; border: 1px solid #ccc; border-radius: 4px;">
            ⬅️ 뒤로 가기
        </button>
                
        <script>
        function goBack() {
            if (document.referrer) {
                window.location.href = document.referrer;
            } else {
                window.location.href = 'index.php';
            }
        }
        </script>
    </div>
</body>
</html>