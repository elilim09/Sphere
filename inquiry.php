<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $username = base64_decode($_COOKIE['username']);
    $content = $_POST['content'];

    $sql = "INSERT INTO inquiries (type, title, content, username) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$type, $title, $content,$username])) {
        echo '<script>window.location.href="index.php";</script>';
    } else {
        echo '<script>alert("문의 게시를 실패했습니다. 다시 시도해주세요. 문제가 계속될 경우 관리자에게 문의하세요.");</script>';
    }
}
?>
