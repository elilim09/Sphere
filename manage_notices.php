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

// 공지사항 추가
if (isset($_POST['add_notice'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $sql = "INSERT INTO notices (title, content, username) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $content, $username]);
    header('Location: index.php'); // 새 공지사항 추가 후 페이지 새로고침
    exit();
}

// 공지사항 수정
if (isset($_POST['update_notice'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $sql = "UPDATE notices SET title = ?, content = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $content, $id]);
    header('Location: index.php'); // 수정 후 페이지 새로고침
    exit();
}

// 공지사항 삭제
if (isset($_POST['delete_notice'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM notices WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    header('Location: index.php'); // 삭제 후 페이지 새로고침
    exit();
}

// 공지사항 목록 조회
$sql = "SELECT * FROM notices ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공지사항 관리 - 관리자 페이지</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background-color: #f0f0f0;
            color: #333;
            padding: 1rem;
        }
        h1 {
            color: #00CED1;
        }
        .container {
            background-color: white;
            padding: 1rem;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            margin-bottom: 2rem;
        }
        form input, form textarea {
            display: block;
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
        }
        .notices {
            margin-bottom: 2rem;
        }
        .notice-item {
            border-bottom: 1px solid #ddd;
            padding: 0.5rem 0;
        }
        .notice-item h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }
        .notice-item p {
            margin: 0 0 0.5rem 0;
        }
        .notice-item button {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
        }
        .notice-item button:hover {
            background: #ff1a1a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>공지사항 관리</h1>

        <!-- 공지사항 추가 폼 -->
        <form method="POST">
            <h2>공지사항 추가</h2>
            <input type="text" name="title" placeholder="제목" required>
            <textarea name="content" placeholder="내용" required></textarea>
            <button type="submit" name="add_notice">추가</button>
        </form>

        <!-- 공지사항 목록 -->
        <div class="notices">
            <h2>공지사항 목록</h2>
            <?php foreach ($notices as $notice): ?>
                <div class="notice-item">
                    <h2><?= htmlspecialchars($notice['title']) ?></h2>
                    <p><?= htmlspecialchars($notice['content']) ?></p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $notice['id'] ?>">
                        <button type="submit" name="delete_notice">삭제</button>
                    </form>
                    <button onclick="showEditForm(<?= $notice['id'] ?>, '<?= htmlspecialchars($notice['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($notice['content'], ENT_QUOTES) ?>')">수정</button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 공지사항 수정 폼 (숨겨져 있음) -->
        <div id="editForm" style="display: none;">
            <h2>공지사항 수정</h2>
            <form method="POST">
                <input type="hidden" name="id" id="editId">
                <input type="text" name="title" id="editTitle" placeholder="제목" required>
                <textarea name="content" id="editContent" placeholder="내용" required></textarea>
                <button type="submit" name="update_notice">수정</button>
            </form>
            <button onclick="document.getElementById('editForm').style.display='none'">취소</button>
        </div>
    </div>

    <script>
        function showEditForm(id, title, content) {
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editContent').value = content;
            document.getElementById('editForm').style.display = 'block';
        }
    </script>
</body>
</html>
