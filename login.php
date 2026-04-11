<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>다 먹어 - 로그인/회원가입</title>
    <style>
        /* 기본 모바일 친화적 UI 설정 (냉장고 페이지와 통일) */
        body { font-family: 'Pretendard', sans-serif; background-color: #f7f9fa; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .auth-container { background: white; padding: 30px 20px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 360px; text-align: center; }
        .logo { font-size: 32px; font-weight: bold; color: #4CAF50; margin-bottom: 20px; }
        
        /* 탭 전환 버튼 */
        .tab-group { display: flex; margin-bottom: 20px; border-bottom: 2px solid #eee; }
        .tab { flex: 1; padding: 10px; cursor: pointer; color: #999; font-weight: bold; transition: 0.3s; }
        .tab.active { color: #4CAF50; border-bottom: 2px solid #4CAF50; }

        /* 입력 폼 디자인 */
        .input-group { margin-bottom: 15px; text-align: left; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #4CAF50; }
        
        .btn-submit { background-color: #4CAF50; color: white; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; margin-top: 10px; }
        .btn-submit:hover { background-color: #45a049; }

        /* 폼 숨김 처리용 클래스 */
        .hidden { display: none; }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="logo">🍽️ 다 먹어</div>
        
        <div class="tab-group">
            <div class="tab active" id="tabLogin" onclick="switchTab('login')">로그인</div>
            <div class="tab" id="tabRegister" onclick="switchTab('register')">회원가입</div>
        </div>

        <form id="loginForm">
            <div class="input-group">
                <input type="text" name="username" placeholder="아이디" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="비밀번호" required>
            </div>
            <button type="submit" class="btn-submit">로그인</button>
        </form>

        <form id="registerForm" class="hidden">
            <div class="input-group">
                <input type="text" name="username" placeholder="아이디 (영문/숫자)" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="비밀번호" required>
            </div>
            <div class="input-group">
                <input type="text" name="nickname" placeholder="닉네임 (예: 요리왕)" required>
            </div>
            <button type="submit" class="btn-submit">가입하기</button>
        </form>
    </div>

    <script>
        // 탭 전환 기능
        function switchTab(type) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const tabLogin = document.getElementById('tabLogin');
            const tabRegister = document.getElementById('tabRegister');

            if (type === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                tabLogin.classList.add('active');
                tabRegister.classList.remove('active');
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                tabLogin.classList.remove('active');
                tabRegister.classList.add('active');
            }
        }

        // --- 회원가입 처리 ---
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault(); // 새로고침 방지
            const formData = new FormData(this);

            fetch('register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // PHP에서 보낸 "회원가입이 완료되었습니다!" 메시지 출력
                if (data.includes("완료")) {
                    this.reset(); // 폼 비우기
                    switchTab('login'); // 성공 시 자동으로 로그인 탭으로 이동
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // --- 로그인 처리 ---
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // 새로고침 방지
            const formData = new FormData(this);

            fetch('login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // PHP에서 보낸 메시지 출력
                if (data.includes("성공")) {
                    // 로그인 성공 시 나의 냉장고 페이지로 이동
                    window.location.href = 'my_fridge.php'; 
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>