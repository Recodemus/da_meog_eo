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
    </head>
<body>
    <div style="padding: 20px;">
        <h2>🍽️ <?= htmlspecialchars($recipe['title']) ?></h2>
        <p>⏱️ 조리 시간: <?= htmlspecialchars($recipe['cook_time']) ?></p>
        
        <hr>

        <?php if ($recipe['is_external'] == 1 && !empty($recipe['source_url'])): ?>
            <p>이 레시피는 외부 영상/블로그를 참고하세요!</p>
            <a href="<?= htmlspecialchars($recipe['source_url']) ?>" target="_blank" style="display:inline-block; padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:5px;">
                원본 레시피 보러가기
            </a>
        <?php else: ?>
            <p>상세 조리법이 준비 중입니다.</p>
        <?php endif; ?>

        <br><br>
        <button onclick="goBack()" style="padding: 5px 10px; cursor: pointer; background: #fff; border: 1px solid #ccc; border-radius: 4px;">
            ⬅️ 뒤로 가기
        </button>
                
        <script>
        function goBack() {
            // document.referrer는 '나를 이 페이지로 보낸 이전 페이지의 주소'를 기억합니다.
            if (document.referrer) {
                // 이전 페이지 주소가 있으면 무조건 거기로 돌아갑니다.
                window.location.href = document.referrer;
            } else {
                // 새 창에서 열렸거나 주소를 직접 치고 들어와서 이전 기록이 없다면 홈으로 보냅니다.
                window.location.href = 'index.php';
            }
        }
        </script>
    </div>
</body>
</html>