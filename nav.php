<?php
// 현재 접속 중인 파일의 이름만 쏙 뽑아옵니다 (예: my_fridge.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* 하단 네비게이션이 화면을 가리지 않도록 body 하단에 여백을 줍니다 */
    body { padding-bottom: 80px !important; }

    /* 하단 탭 바 고정 스타일 */
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #ffffff;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-around;
        padding: 12px 0 15px 0; /* 터치하기 편하게 위아래 패딩 넉넉히 */
        z-index: 9999;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    .nav-item {
        text-decoration: none;
        color: #b0b0b0;
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 11px;
        font-weight: bold;
        transition: all 0.2s ease;
        -webkit-tap-highlight-color: transparent; /* 모바일 터치 시 깜빡임 방지 */
    }

    .nav-item .icon {
        font-size: 24px;
        margin-bottom: 4px;
        filter: grayscale(100%); /* 기본 상태는 흑백으로 */
        opacity: 0.5;
        transition: transform 0.2s;
    }

    /* 💡 현재 보고 있는 페이지(활성화) 탭 스타일 */
    .nav-item.active {
        color: #4CAF50; /* 다 먹어 메인 컬러 (초록색) */
    }

    .nav-item.active .icon {
        filter: grayscale(0%); /* 본래 색상 살리기 */
        opacity: 1;
        transform: translateY(-2px); /* 살짝 위로 올라오는 효과 */
    }
</style>

<nav class="bottom-nav">
    <a href="index.php" class="nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
        <div class="icon">🏠</div>
        <div>홈</div>
    </a>

    <a href="my_fridge.php" class="nav-item <?= $current_page == 'my_fridge.php' ? 'active' : '' ?>">
        <div class="icon">🧊</div>
        <div>냉장고</div>
    </a>

    <a href="recipe.php" class="nav-item <?= $current_page == 'recipe.php' ? 'active' : '' ?>">
        <div class="icon">🧑‍🍳</div>
        <div>레시피</div>
    </a>

    <a href="mypage.php" class="nav-item <?= $current_page == 'mypage.php' ? 'active' : '' ?>">
        <div class="icon">👤</div>
        <div>MY</div>
    </a>
</nav>