<?php
include 'config.php';

// 관리자 권한 확인
if (!isset($_COOKIE['username'])) {
    header('Location: index.php'); // 로그인 페이지로 리디렉션
    exit();
}

$username = base64_decode($_COOKIE['username']);
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || $user['admin'] != 1) {
    header('Location: index.php'); // 일반 사용자일 경우 홈으로 리디렉션
    exit();
}
