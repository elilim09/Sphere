<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $grade = $_POST['grade'];
    $class = $_POST['class'];
    $number = $_POST['number'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // 아이디 중복 확인
    $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]); // 암호화된 아이디로 중복 확인
    $userCount = $stmt->fetchColumn();

    if ($userCount > 0) {
        echo '<script>alert("이미 사용 중인 아이디입니다. 다른 아이디를 선택해 주세요."); window.location.href="index.php#register";</script>';
        exit();
    }

    $sql = "INSERT INTO users (username, name, grade, class, number, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$username, $name, $grade, $class, $number, $password])) {
        $userId = $pdo->lastInsertId(); // 새로운 사용자 ID를 가져옵니다

        // 로그인 상태를 쿠키에 저장합니다
        setcookie('user_id', $userId, time() + (86400 * 30), '/'); // 30일 동안 유지되는 쿠키
        setcookie('username', base64_encode($username), time() + (86400 * 30), '/'); // 30일 동안 유지되는 쿠키
        
        // 세션에 사용자 ID와 username을 저장
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;

        echo '<script>window.location.href="index.php";</script>';
        exit();
    } else {
        echo '<script>alert("회원가입에 실패했습니다. 다시 시도해주세요. 문제가 계속될 경우 관리자에게 문의하세요."); window.location.href="index.php#register";</script>';
    }
}
?>
