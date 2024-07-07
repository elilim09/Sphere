<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 입력된 아이디를 암호화합니다
    $encryptedUsername = base64_encode($username); // 아이디 암호화 (base64 인코딩)

    // 암호화된 아이디로 사용자 조회
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 비밀번호 검증
    if ($user && password_verify($password, $user['password'])) {
        // 비밀번호가 일치하면 세션에 사용자 ID와 username을 저장
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;

        // 쿠키에 사용자 ID와 username을 저장
        setcookie('user_id', $user['id'], time() + (86400 * 30), '/'); // 30일 동안 유지되는 쿠키
        setcookie('username', $encryptedUsername, time() + (86400 * 30), '/'); // 30일 동안 유지되는 쿠키
        
        echo '<script>window.location.href="index.php";</script>';
        exit();
    } else {
        echo '<script>alert("아이디 또는 비밀번호가 올바르지 않습니다."); window.location.href="index.php#login";</script>';
    }
}
?>
