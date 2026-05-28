<?php
session_start();
require_once 'db_connect.php';

// 1. 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "로그인이 필요합니다.";
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. 폼에서 전송된 데이터 받기
// $_POST['ingredient_icon'] 로 이모지 데이터를 받습니다.
$ingredient_id = $_POST['ingredient_id'] ?? '';
$storage_type = $_POST['storage_type'] ?? '';
$expiry_date = $_POST['expiry_date'] ?? '';
$icon = $_POST['ingredient_icon'] ?? '🍽️'; // 이모지가 안 넘어오면 기본값 설정

// 3. 필수 입력값 확인
if (empty($ingredient_id) || empty($storage_type) || empty($expiry_date)) {
    echo "필수 입력값이 누락되었습니다.";
    exit;
}

try {
    // 4. DB에 데이터 저장 (INSERT)
    // 🌟 쿼리문에 icon 컬럼과 :icon 바인딩을 추가했습니다!
    $sql = "INSERT INTO user_fridge (user_id, ingredient_id, storage_type, expiry_date, icon) 
            VALUES (:user_id, :ingredient_id, :storage_type, :expiry_date, :icon)";
            
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':user_id' => $user_id,
        ':ingredient_id' => $ingredient_id,
        ':storage_type' => $storage_type,
        ':expiry_date' => $expiry_date,
        ':icon' => $icon // 이모지 데이터 바인딩
    ]);

    if ($result) {
        echo "냉장고에 재료가 성공적으로 추가되었습니다! 🧊";
    } else {
        echo "재료 추가에 실패했습니다.";
    }

} catch (PDOException $e) {
    // 에러 발생 시 처리
    echo "데이터베이스 오류: " . $e->getMessage();
}
?>