<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "로그인이 필요합니다.";
    exit;
}

$user_id = $_SESSION['user_id'];

// 폼 데이터 받기 (ingredient_icon 삭제됨!)
$ingredient_id = $_POST['ingredient_id'] ?? '';
$storage_type = $_POST['storage_type'] ?? '';
$expiry_date = $_POST['expiry_date'] ?? '';

if (empty($ingredient_id) || empty($storage_type) || empty($expiry_date)) {
    echo "필수 입력값이 누락되었습니다.";
    exit;
}

try {
    // DB 저장 (icon 컬럼에는 저장하지 않음)
    $sql = "INSERT INTO user_fridge (user_id, ingredient_id, storage_type, expiry_date) 
            VALUES (:user_id, :ingredient_id, :storage_type, :expiry_date)";
            
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':user_id' => $user_id,
        ':ingredient_id' => $ingredient_id,
        ':storage_type' => $storage_type,
        ':expiry_date' => $expiry_date
    ]);

    if ($result) {
        echo "냉장고에 재료가 성공적으로 추가되었습니다! 🧊";
    } else {
        echo "재료 추가에 실패했습니다.";
    }

} catch (PDOException $e) {
    echo "데이터베이스 오류: " . $e->getMessage();
}
?>