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
    // 2. 전체 식재료 목록 불러오기 (미리 정의된 재료 목록)
    $stmt_ing = $pdo->query("SELECT ingredient_id, name, icon_url FROM ingredients ORDER BY name ASC");
    $ingredients_list = $stmt_ing->fetchAll();

    // 3. 내 냉장고 데이터 불러오기 
    // ※ 주의: 기존 코드에서는 ingredients 테이블의 icon_url을 불러왔으나, 
    // 이제 사용자가 직접 선택한 아이콘(user_fridge의 icon)을 우선적으로 보여주려면 
    // 아래 쿼리에서 uf.icon 을 가져오도록 수정해야 합니다. (DB에 icon 컬럼이 추가되었다고 가정)
    $sql = "SELECT 
                uf.fridge_id, 
                uf.storage_type, 
                uf.expiry_date, 
                uf.icon, /* 사용자가 선택한 아이콘 */
                i.name, 
                DATEDIFF(uf.expiry_date, CURDATE()) AS d_day
            FROM user_fridge uf
            JOIN ingredients i ON uf.ingredient_id = i.ingredient_id
            WHERE uf.user_id = :user_id
            ORDER BY d_day ASC"; 
    
    $stmt_fridge = $pdo->prepare($sql);
    $stmt_fridge->execute([':user_id' => $user_id]);
    $my_fridge_items = $stmt_fridge->fetchAll();

} catch (PDOException $e) {
    die("데이터 불러오기 실패: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다 먹어 - 나의 냉장고</title>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .user-greeting { text-align: center; color: #666; margin-bottom: 20px; font-weight: bold; }
        
        .add-form-container { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .input-group { display: flex; gap: 10px; margin-bottom: 10px; }
        select, input, button { padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; flex: 1; }
        
        /* 아이콘 선택창은 조금 더 작게 설정 */
        #ingredient_icon { flex: 0.5; }
        
        .btn-submit { background-color: #4CAF50; color: white; border: none; cursor: pointer; font-weight: bold; width: 100%; flex: none; }
        .btn-submit:hover { background-color: #45a049; }

        .fridge-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px; }
        .ingredient-card { background: white; padding: 15px 10px; border-radius: 12px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative; }
        
        .status-good { border-top: 4px solid #4CAF50; } 
        .status-warn { border-top: 4px solid #FFC107; } 
        .status-danger { border-top: 4px solid #F44336; } 
        
        .icon { font-size: 30px; margin-bottom: 5px; }
        .name { font-weight: bold; font-size: 14px; margin-bottom: 5px;}
        .d-day { font-size: 12px; color: #666; }
        
        .btn-consume { background: #ff7675; color: white; border: none; border-radius: 4px; padding: 5px 10px; font-size: 12px; cursor: pointer; margin-top: 10px; }
        .empty-message { grid-column: 1 / -1; text-align: center; color: #999; padding: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>🧊 나의 냉장고</h2>
        <div class="user-greeting"><?= htmlspecialchars($_SESSION['nickname'] ?? '회원') ?>님의 냉장고 상황</div>
    </div>

    <div class="add-form-container">
        <form id="addIngredientForm">
            <div class="input-group">
                <select name="ingredient_id" id="ingredient_id" required>
                    <option value="">재료 선택</option>
                    <?php foreach($ingredients_list as $ing): ?>
                        <option value="<?= $ing['ingredient_id'] ?>">
                            <?= htmlspecialchars($ing['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select id="ingredient_icon" name="ingredient_icon">
                    <option value="🍽️">아이콘(🍽️)</option>
                    <option value="🥕">채소 (🥕)</option>
                    <option value="🥩">고기 (🥩)</option>
                    <option value="🐟">해산물 (🐟)</option>
                    <option value="🥚">계란/유제품 (🥚)</option>
                    <option value="🍎">과일 (🍎)</option>
                </select>
            </div>
            
            <div class="input-group">
                <select name="storage_type" id="storage_type" required>
                    <option value="냉장">냉장</option>
                    <option value="냉동">냉동</option>
                    <option value="실온">실온</option>
                </select>
                <input type="date" name="expiry_date" id="expiry_date" required placeholder="소비기한">
            </div>
            <button type="submit" class="btn-submit">냉장고에 넣기</button>
        </form>
    </div>

    <div class="fridge-grid" id="fridgeGrid">
        <?php if (empty($my_fridge_items)): ?>
            <div class="empty-message">냉장고가 텅 비어있습니다.<br>새로운 재료를 추가해 보세요!</div>
        <?php else: ?>
            <?php 
            foreach($my_fridge_items as $item): 
                $d_day = $item['d_day'];
                $status_class = 'status-good';
                
                if ($d_day < 0) {
                    $status_class = 'status-danger';
                    $d_day_text = "기한 지남 (" . abs($d_day) . "일)";
                } elseif ($d_day == 0) {
                    $status_class = 'status-danger';
                    $d_day_text = "D-Day (오늘)";
                } elseif ($d_day <= 3) {
                    $status_class = 'status-danger';
                    $d_day_text = "D-" . $d_day;
                } elseif ($d_day <= 7) {
                    $status_class = 'status-warn';
                    $d_day_text = "D-" . $d_day;
                } else {
                    $d_day_text = "D-" . $d_day;
                }
            ?>
            <div class="ingredient-card <?= $status_class ?>" id="item-<?= $item['fridge_id'] ?>">
                <div class="icon"><?= htmlspecialchars($item['icon'] ?? '🍽️') ?></div>
                <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="d-day"><?= $d_day_text ?> (<?= htmlspecialchars($item['storage_type']) ?>)</div>
                <button class="btn-consume" onclick="consumeIngredient(<?= $item['fridge_id'] ?>)">소비 완료</button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('addIngredientForm').addEventListener('submit', function(e) {
            e.preventDefault(); 
            const formData = new FormData(this);

            fetch('add_fridge_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                window.location.reload(); 
            })
            .catch(error => console.error('Error:', error));
        });

        function consumeIngredient(fridgeId) {
            if(!confirm('이 재료를 모두 소비하셨나요?')) return;

            const formData = new FormData();
            formData.append('fridge_id', fridgeId);

            fetch('delete_fridge_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const card = document.getElementById('item-' + fridgeId);
                if(card) {
                    card.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
    
<?php include 'nav.php'; ?>
    
</body>
</html>