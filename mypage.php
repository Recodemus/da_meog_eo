<?php
session_start();
require_once 'db_connect.php';

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 내 정보 불러오기
    $sql = "SELECT username, nickname, created_at FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("사용자 정보를 찾을 수 없습니다.");
    }
} catch (PDOException $e) {
    die("DB 에러: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다 먹어 - 마이페이지</title>
    <style>
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        
        /* 프로필 카드 */
        .profile-card { background: white; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .profile-avatar { font-size: 50px; margin-bottom: 10px; }
        .profile-name { font-size: 20px; font-weight: bold; margin: 0 0 5px 0; color: #333; }
        .profile-id { color: #888; font-size: 14px; margin: 0; }
        .join-date { font-size: 12px; color: #aaa; margin-top: 15px; }

        /* 설정 메뉴들 */
        .menu-section { background: white; border-radius: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px; }
        .menu-item { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; border-bottom: 1px solid #eee; text-decoration: none; color: #333; font-weight: 500; cursor: pointer; }
        .menu-item:last-child { border-bottom: none; }
        .menu-item:active { background-color: #f9f9f9; }
        .menu-item .icon { margin-right: 15px; font-size: 18px; }
        
        /* 로그아웃 버튼 특화 */
        .logout-text { color: #F44336; }
        
        /* 닉네임 변경 폼 (기본 숨김) */
        #editProfileForm { padding: 20px; background: #fdfdfd; border-top: 1px solid #eee; display: none; }
        .input-group { display: flex; gap: 10px; }
        input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .btn-save { background-color: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>👤 마이페이지</h2>
    </div>

    <div class="profile-card">
        <div class="profile-avatar">🧑‍🍳</div>
        <h3 class="profile-name"><?= htmlspecialchars($user['nickname']) ?>님</h3>
        <p class="profile-id">@<?= htmlspecialchars($user['username']) ?></p>
        <div class="join-date">가입일: <?= date('Y년 m월 d일', strtotime($user['created_at'])) ?></div>
    </div>

    <div class="menu-section">
        <div class="menu-item" onclick="toggleEditForm()">
            <div><span class="icon">✏️</span> 닉네임 변경</div>
            <div>❯</div>
        </div>
        
        <form id="editProfileForm">
            <div class="input-group">
                <input type="text" name="new_nickname" id="new_nickname" value="<?= htmlspecialchars($user['nickname']) ?>" required>
                <button type="submit" class="btn-save">저장</button>
            </div>
        </form>

        <a href="#" class="menu-item" onclick="alert('준비 중인 기능입니다!')">
            <div><span class="icon">📖</span> 나만의 레시피 관리</div>
            <div>❯</div>
        </a>
        
        <a href="#" class="menu-item" onclick="alert('준비 중인 기능입니다!')">
            <div><span class="icon">⭐</span> 스크랩한 레시피</div>
            <div>❯</div>
        </a>
    </div>

    <div class="menu-section">
        <a href="logout.php" class="menu-item">
            <div class="logout-text"><span class="icon">👋</span> 로그아웃</div>
        </a>
    </div>

    <?php include 'nav.php'; ?>

    <script>
        // 닉네임 변경 폼 열기/닫기
        function toggleEditForm() {
            const form = document.getElementById('editProfileForm');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }

        // 닉네임 변경 비동기 처리
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newNickname = document.getElementById('new_nickname').value;
            
            // 실제 서비스에서는 update_profile_process.php 를 만들어 DB의 내용을 업데이트 해야 합니다.
            // 여기서는 UI의 흐름만 보여주기 위해 임시 알림 처리 후 새로고침 합니다.
            alert("닉네임이 '" + newNickname + "'(으)로 변경되었습니다! (현재는 UI 시뮬레이션입니다)");
            toggleEditForm();
            // window.location.reload(); // 실제 서버 연동 후 주석 해제
        });
    </script>

</body>
</html>