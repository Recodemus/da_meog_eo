<?php
session_start();
require_once 'db_connect.php'; // DB 연결 필수!

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); window.location.href='auth.php';</script>";
    exit;
}

// 전체 식재료 목록 불러오기 (체크박스 용도)
$stmt_ing = $pdo->query("SELECT ingredient_id, name, icon_url FROM ingredients ORDER BY name ASC");
$ingredients_list = $stmt_ing->fetchAll();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>새 레시피 작성 - 다 먹어</title>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; }
        .form-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit; }
        textarea { resize: vertical; height: 150px; }
        .btn-submit { background-color: #4CAF50; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background-color: #45a049; }
        
        /* 뒤로가기 버튼 스타일 */
        .btn-back { display: inline-block; padding: 8px 15px; margin-bottom: 20px; text-decoration: none; color: #333; border: 1px solid #ddd; border-radius: 5px; background-color: #fff; }
        .btn-back:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    
    <div class="form-container">
        <a href="mypage.php" class="btn-back">⬅️ 마이페이지로</a>

        <h2 style="text-align: center; margin-top: 0;">🍳 나만의 레시피 기록</h2>
        
        <form action="create_recipe_process.php" method="POST">
            
            <div class="form-group">
                <label for="title">레시피 제목</label>
                <input type="text" id="title" name="title" required placeholder="예: 초간단 계란 볶음밥">
            </div>
            
            <div class="form-group">
                <label for="cook_time">조리 시간 (분 단위)</label>
                <input type="number" id="cook_time" name="cook_time" placeholder="예: 15 (숫자만 입력)">
            </div>

            <div class="form-group">
                <label for="thumbnail_url">대표 이미지 URL (선택)</label>
                <input type="text" id="thumbnail_url" name="thumbnail_url" placeholder="이미지 링크 주소를 입력하세요">
            </div>
            
            <div class="form-group">
                <label>필요한 재료 선택 (다중 선택 가능)</label>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; background: #fafafa;">
                    <?php foreach($ingredients_list as $ing): ?>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: normal;">
                            <input type="checkbox" name="ingredients[]" value="<?= $ing['ingredient_id'] ?>">
                            <?= htmlspecialchars($ing['icon_url'] ?? '') ?> <?= htmlspecialchars($ing['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="content">상세 조리법</label>
                <textarea id="content" name="content" required placeholder="조리 순서를 자세히 적어주세요."></textarea>
            </div>

            <div class="form-group">
                <label for="source_url">🔗 외부 레시피 링크 (만개의 레시피, 유튜브 등)</label>
                <input type="text" id="source_url" name="source_url" placeholder="링크가 있다면 여기에 주소를 붙여넣으세요 (선택 사항)">
                <div style="font-size: 12px; color: #888; margin-top: 5px;">* 링크를 입력하면 '외부 링크' 뱃지가 붙고 클릭 시 해당 사이트로 이동합니다.</div>
            </div>
            
            <button type="submit" class="btn-submit">레시피 등록하기</button>
        </form>
    </div>
    
</body>
</html>