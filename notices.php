<?php
include 'config.php'; // 데이터베이스 연결 파일

// 공지사항 ID를 가져옵니다.
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 공지사항 상세 정보를 가져옵니다.
$sql = "SELECT * FROM notices WHERE id = ?"; // 테이블 이름을 `notice`에서 `notices`로 수정
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$notice = $stmt->fetch();

if (!$notice) {
    echo '<p>존재하지 않는 공지사항입니다.</p>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>공지사항 상세</title>
    <style>/* styles.css */

body {
    font-family: 'Noto Sans KR', sans-serif;
    background-color: #f0f0f0;
    color: #333;
    padding: 1rem;
}

#notice-detail {
    background-color: white;
    padding: 1rem;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

a {
    color: #00CED1;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
    <section id="notice-detail">
        <h2><?= htmlspecialchars($notice['title']) ?></h2>
        <p>작성일: <?= htmlspecialchars(date('Y-m-d', strtotime($notice['created_at']))) ?></p>
        <p>작성자: <?= htmlspecialchars($notice['username']) ?></p> <!-- 공지사항 작성자 이름을 추가했습니다. -->
        <div>
            <?= nl2br(htmlspecialchars($notice['content'])) ?>
        </div>
        <a href="index.php#notice">목록으로 돌아가기</a>
    </section>
</body>
</html>
