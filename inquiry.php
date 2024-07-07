<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "INSERT INTO inquiries (type, title, content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$type, $title, $content])) {
        echo "Inquiry submitted successfully";
    } else {
        echo "Failed to submit inquiry";
    }
}
?>

<form method="post">
    <select name="type" required>
        <option value="">문의 유형 선택</option>
        <option value="academic">학사 관련</option>
        <option value="facility">시설 관련</option>
        <option value="other">기타</option>
    </select>
    <input type="text" name="title" placeholder="제목" required>
    <textarea name="content" placeholder="내용을 입력해주세요" required></textarea>
    <button type="submit">제출</button>
</form>
