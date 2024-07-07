<?php
include 'config.php';

$isLoggedIn = false;
$username = '';
$isAdmin = false;  // 관리자 여부를 확인하는 변수 추가

if (isset($_COOKIE['username'])) {
    $username = base64_decode($_COOKIE['username']); // 쿠키에서 암호화된 username을 복호화
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $isLoggedIn = true;
        if ($user['admin'] == 1) { // 관리자 권한 확인
            $isAdmin = true;
        }
    } else {
        $isLoggedIn = false;
    }
}

// 최근 3개의 공지사항을 가져옵니다.
try {
    $sql = "SELECT * FROM notices ORDER BY created_at DESC LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $notices = $stmt->fetchAll(); // fetchAll() 메소드로 데이터를 가져옵니다.
} catch (PDOException $e) {
    echo '<p>공지사항을 불러오는 데 실패했습니다: ' . htmlspecialchars($e->getMessage()) . '</p>';
    $notices = []; // 오류 발생 시 빈 배열로 초기화
}

// 닉네임 변경, 계정 삭제, 비밀번호 변경 요청 처리
if ($isLoggedIn) {
    if (isset($_POST['change_nickname'])) {
        $newNickname = trim($_POST['username']); // 사용자로부터 새로운 닉네임을 받습니다.
        if (!empty($newNickname)) {
            // 새 닉네임이 이미 존재하는지 확인합니다.
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$newNickname]);
            $nicknameExists = $stmt->fetchColumn();

            if ($nicknameExists > 0) {
                $message = '이미 존재하는 닉네임입니다. 다른 닉네임을 입력해주세요.';
            } else {
                // 새로운 닉네임으로 업데이트합니다.
                $sql = "UPDATE users SET username = ? WHERE username = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$newNickname, $username]);

                // 사용자 ID와 새로운 닉네임을 쿠키에 저장합니다.
                setcookie('username', base64_encode($newNickname), time() + (86400 * 30), '/'); // 30일 동안 유지되는 쿠키
                
                $message = '닉네임이 성공적으로 변경되었습니다.';
                
                // 성공적으로 변경된 경우 세션에서도 닉네임을 업데이트합니다.
                $_SESSION['username'] = $newNickname;
                $username = $newNickname; // 변수도 업데이트
            }
        } else {
            $message = '닉네임을 입력해주세요.';
        }
    }

    // 비밀번호 변경
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // 현재 비밀번호 확인
        $sql = "SELECT password FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $storedPassword = $stmt->fetchColumn();

        if (password_verify($currentPassword, $storedPassword)) {
            if ($newPassword === $confirmPassword) {
                $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET password = ? WHERE username = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$hashedNewPassword, $username]);
                $message = '비밀번호가 변경되었습니다.';
            } else {
                $message = '새 비밀번호와 비밀번호 확인이 일치하지 않습니다.';
            }
        } else {
            $message = '현재 비밀번호가 일치하지 않습니다.';
        }
    }

    // 계정 삭제
    if (isset($_POST['delete_account'])) {
        $passwordToDelete = $_POST['password_to_delete'];

        // 비밀번호 확인
        $sql = "SELECT password FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $storedPassword = $stmt->fetchColumn();

        if (password_verify($passwordToDelete, $storedPassword)) {
            $sql = "DELETE FROM users WHERE username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);

            // 쿠키 삭제 및 리디렉션
            setcookie('username', '', time() - 3600, '/');
            header('Location: index.php');
            exit();
        } else {
            $message = '비밀번호가 일치하지 않습니다.';
        }
    }
    if (isset($_POST['logout'])) {
        session_start();  // 세션을 시작합니다
        session_unset(); // 세션 변수를 모두 제거합니다
        session_destroy(); // 세션을 파괴합니다

        // 쿠키를 삭제합니다
        setcookie('user_id', '', time() - 3600, '/'); // 만료된 쿠키를 설정하여 삭제
        setcookie('username', '', time() - 3600, '/'); // 만료된 쿠키를 설정하여 삭제

        // 리다이렉트합니다
        header('Location: index.php');
        exit();
        }
        if (isset($_POST['logout'])) {
            session_start();
            session_unset();
            session_destroy();
    
            setcookie('user_id', '', time() - 3600, '/');
            setcookie('username', '', time() - 3600, '/');
    
            header('Location: index.php');
            exit();
        }
// 급식 정보 가져오기 (cURL 사용)
$currentDate = date('Ymd'); // 형식: YYYYMMDD
$apiUrl = "https://open.neis.go.kr/hub/mealServiceDietInfo?Type=json&pIndex=1&pSize=100&ATPT_OFCDC_SC_CODE=J10&SD_SCHUL_CODE=7551236&MLSV_YMD={$currentDate}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    $message = 'cURL 오류: ' . curl_error($ch);
}
curl_close($ch);

$data = json_decode($response, true);
$dishInfo = '';
if (isset($data['mealServiceDietInfo'][1]['row'][0]['DDISH_NM'])) {
    $dishInfo = $data['mealServiceDietInfo'][1]['row'][0]['DDISH_NM'];
}

}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sphere - 비공식 학교 웹</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* 기존 스타일은 그대로 유지됩니다 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background-color: #f0f0f0;
            color: #333;
        }
        
        header {
            background-color: #00CED1;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        #menuBtn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            position: absolute;
            right: 1rem;
            z-index: 1001;
        }
        
        nav {
            background-color: #008B8B;
            position: fixed;
            top: 0;
            right: -250px;
            width: 250px;
            height: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
        }
        
        nav.show {
            right: 0;
        }
        
        nav ul {
            list-style-type: none;
            padding-top: 60px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 1rem;
            transition: background-color 0.3s ease;
        }
        
        nav ul li a:hover {
            background-color: #006666;
        }
        
        main {
            padding: 1rem;
            transition: margin-right 0.3s ease;
        }
        
        main.shift {
            margin-right: 250px;
        }
        
        section {
            display: none;
            background-color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }
        
        section.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        h2 {
            color: #00CED1;
            margin-bottom: 1rem;
        }
        
        ul {
            list-style-type: none;
        }
        
        li {
            margin-bottom: 0.5rem;
        }
        
        form {
            display: flex;
            flex-direction: column;
        }
        
        input,
        select,
        textarea,
        button {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 3px;
            transition: border-color 0.3s ease;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            border-color: #00CED1;
            outline: none;
        }
        
        button {
            background-color: #FFA500;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: #FF8C00;
        }

        .terms {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .terms input {
            margin-right: 0.5rem;
        }
        
        .terms label {
            font-size: 0.9rem;
        }

        .error {
            color: red;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .meal-info {
            background-color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .meal-info h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .meal-info ul {
            list-style-type: none;
        }

        .meal-info ul li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }

    </style>
</head>
<body>
    <header>
        <a href="index.php" style="text-decoration: none; color: #FFFFFF;">
            <div class="logo">Sphere</div>
        </a>
        <button id="menuBtn">☰</button>
    </header>

    <nav id="menu">
        <ul>
            <li><a href="#home">홈</a></li>
            <?php if (!$isLoggedIn): ?>
                <li><a href="#login">로그인</a></li>
                <li><a href="#register">회원가입</a></li>
            <?php endif; ?>
            <li><a href="#notice">공지사항</a></li>
            <li><a href="#inquiry">문의</a></li>
            <li><a href="#meals" id="mealsBtn">급식 정보</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="#settings">사용자 설정</a></li> <!-- 사용자 설정 링크 추가 -->
            <?php endif; ?>
            <li><a href="#logout">로그아웃</a></li>
            <?php if ($isAdmin): ?>
                <li><a href="#admin">관리자 페이지</a></li> <!-- 관리자 페이지 링크 추가 -->
            <?php endif; ?>
        </ul>
    </nav>

    <main>
        <section id="home" class="active">
            <h2>최신 공지사항</h2>
            <ul>
                <?php if (!empty($notices)): ?>
                    <?php foreach ($notices as $notice): ?>
                        <li>
                            <a href="notices.php?id=<?= htmlspecialchars($notice['id']) ?>">
                                <?= htmlspecialchars($notice['title']) ?>
                            </a>
                            <span><?= htmlspecialchars(date('Y-m-d', strtotime($notice['created_at']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>공지사항이 없습니다.</li>
                <?php endif; ?>
            </ul>
        </section>

        <section id="login">
            <h2>로그인</h2>
            <form action="login.php" method="post">
                <input type="text" name="username" placeholder="아이디" required>
                <input type="password" name="password" placeholder="비밀번호" required>
                <button type="submit">로그인</button>
            </form>
            <p><a href="#register" class="link">회원가입</a> | <a href="#forgot-password" class="link">비밀번호 찾기</a></p>
        </section>

        <section id="register">
            <h2>회원가입</h2>
            <form id="registerForm" action="register.php" method="post">
                <input type="text" name="username" placeholder="아이디" required>
                <input type="text" name="name" placeholder="본명" required>
                <select name="grade" required>
                    <option value="">학년 선택</option>
                    <option value="1">1학년</option>
                    <option value="2">2학년</option>
                    <option value="3">3학년</option>
                </select>
                <input type="number" name="class" placeholder="반" min="1" max="9" required>
                <input type="number" name="number" placeholder="번호 (1~40)" min="1" max="40" required>
                <input type="password" id="password" name="password" placeholder="비밀번호" required>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="비밀번호 확인" required>
                <div id="passwordError" class="error"></div>
                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">이용약관에 동의합니다.</label>
                </div>
                <button type="submit">회원가입</button>
            </form>
            <p><a href="#login" class="link">로그인</a> | <a href="#forgot-password" class="link">비밀번호 찾기</a></p>
        </section>

        <section id="notice">
            <h2>공지사항</h2>
            <ul>
                <?php
                try {
                    $sql = "SELECT * FROM notices ORDER BY created_at DESC"; // 모든 공지사항을 가져오고 최신 공지사항이 먼저 보이도록 정렬
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $notices = $stmt->fetchAll(); // fetchAll() 메소드로 모든 데이터를 가져옵니다.
                } catch (PDOException $e) {
                    echo '<p>공지사항을 불러오는 데 실패했습니다: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    $notices = []; // 오류 발생 시 빈 배열로 초기화
                }
                ?>
                <?php if (!empty($notices)): ?>
                    <?php foreach ($notices as $notice): ?>
                        <li>
                            <a href="notices.php?id=<?= htmlspecialchars($notice['id']) ?>">
                                <?= htmlspecialchars($notice['title']) ?>
                            </a>
                            <span><?= htmlspecialchars(date('Y-m-d', strtotime($notice['created_at']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>공지사항이 없습니다.</li>
                <?php endif; ?>
            </ul>
        </section>

        <section id="inquiry">
            <h2>문의하기</h2>
            <form action="inquiry.php" method="post">
                <select name="type" required>
                    <option value="">문의 유형 선택</option>
                    <option value="학교폭력관련">학교폭력관련</option>
                    <option value="시설관련">시설관련</option>
                    <option value="계정관련">계정관련</option>
                    <option value="기타">기타</option>
                </select>
                <input type="text" name="title" placeholder="제목" required>
                <textarea name="content" placeholder="내용을 입력해주세요" style="height: 100px;" required></textarea>
                <button type="submit">제출</button>
            </form>
        </section>
        <section id="meals">
    <h2>급식 정보</h2>
    <div id="meal" class="meal-info"></div>
    <script>
        // PHP에서 받은 dishInfo 데이터를 JavaScript 변수로 저장합니다.
        const dishInfo = <?php echo json_encode($dishInfo); ?>;

        // dishInfo 데이터를 콘솔에 출력합니다.
        console.log(dishInfo);

        // dishInfo 데이터를 HTML로 표시합니다.
        const mealDiv = document.getElementById('meal');
        if (dishInfo) {
            // <br> 태그를 사용하여 줄바꿈을 적용합니다.
            mealDiv.innerHTML = dishInfo.replace(/\n/g, '<br>');
        } else {
            const p = document.createElement('p');
            p.textContent = '오늘의 급식 정보가 없습니다.';
            mealDiv.appendChild(p);
        }
    </script>
</section>



        <section id="admin">
            <h2>관리자 페이지</h2>
            <form action="admin.php" method="post">
                <ul>
                    <li><a href="manage_notices.php">공지사항 관리</a></li>
                    <li><a href="manage_users.php">사용자 관리</a></li>
                    <li><a href="manage_inquiry.php">문의사항 보기</a></li>
                </ul>
            </form>
        </section>

        <?php if ($isLoggedIn): ?>
        <section id="settings">
            <h2>사용자 설정</h2>
            <?php if (isset($message)): ?>
                <p class="error"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form action="" method="post">
                <h3>닉네임 변경</h3>
                <input type="text" name="username" placeholder="새 닉네임" required>
                <button type="submit" name="change_nickname">닉네임 변경</button>
            </form>
            <form action="" method="post">
                <h3>비밀번호 변경</h3>
                <input type="password" name="current_password" placeholder="현재 비밀번호" required>
                <input type="password" name="new_password" placeholder="새 비밀번호" required>
                <input type="password" name="confirm_password" placeholder="새 비밀번호 확인" required>
                <button type="submit" name="change_password">비밀번호 변경</button>
            </form>
            <form action="" method="post">
                <h3>계정 삭제</h3>
                <input type="password" name="password_to_delete" placeholder="비밀번호" required>
                <button type="submit" name="delete_account">계정 삭제</button>
            </form>
            
        </section>
        <section id="logout">
            <h2>로그아웃</h2>
            <form action="index.php" method="post">
                <button type="submit" name="logout">로그아웃</button>
            </form>
        </section>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('menuBtn');
            const menu = document.getElementById('menu');
            const main = document.querySelector('main');
            const menuItems = document.querySelectorAll('#menu a');
            const sections = document.querySelectorAll('main section');
            const links = document.querySelectorAll('.link');
            const termsCheckbox = document.getElementById('terms');

            menuBtn.addEventListener('click', function() {
                menu.classList.toggle('show');
                main.classList.toggle('shift');
                this.textContent = this.textContent === '☰' ? '✕' : '☰';
            });

            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    sections.forEach(section => {
                        section.classList.remove('active');
                    });
                    setTimeout(() => {
                        document.getElementById(targetId).classList.add('active');
                    }, 50);
                    menu.classList.remove('show');
                    main.classList.remove('shift');
                    menuBtn.textContent = '☰';
                });
            });

            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    sections.forEach(section => {
                        section.classList.remove('active');
                    });
                    document.getElementById(targetId).classList.add('active');
                });
            });

            const registerForm = document.getElementById('registerForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordError = document.getElementById('passwordError');

            registerForm.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault(); // 폼 제출 방지
                    passwordError.textContent = '비밀번호가 일치하지 않습니다.';
                    alert('비밀번호가 일치하지 않습니다.');
                } else {
                    passwordError.textContent = '';
                }
            });
        });
    </script>
</body>
</html>
