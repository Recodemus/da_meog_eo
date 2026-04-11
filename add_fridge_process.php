<?php
session_start();
require_once 'db_connect.php';

// 로그인 상태 확인
if (!isset($_SESSION['user_id'])) {
    die("로그인이 필요한 서비스입니다.");
}

// --- 추가할 방어 코드 ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("잘못된 접근입니다.");
}

$ingredient_id = $_POST['ingredient_id'] ?? ''; 
$expiry_date = $_POST['expiry_date'] ?? '';     
$storage_type = $_POST['storage_type'] ?? '';   

if (empty($ingredient_id) || empty($expiry_date) || empty($storage_type)) {
    die("재료 정보를 모두 입력해주세요.");
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "INSERT INTO user_fridge (user_id, ingredient_id, expiry_date, storage_type) 
            VALUES (:user_id, :ingredient_id, :expiry_date, :storage_type)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':ingredient_id' => $ingredient_id,
        ':expiry_date' => $expiry_date,
        ':storage_type' => $storage_type
    ]);
    
    echo "냉장고에 재료가 성공적으로 추가되었습니다!";
} catch (PDOException $e) {
    echo "재료 추가 에러: " . $e->getMessage();
}
?>