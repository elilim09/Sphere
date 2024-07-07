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

// 비밀번호 초기화
if (isset($_POST['reset_password'])) {
    $userId = intval($_POST['user_id']);
    $newPassword = '1234';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hashedPassword, $userId]);
    $message = '비밀번호가 초기화되었습니다. (새 비밀번호: 1234)';
}

// 사용자 권한 변경
if (isset($_POST['change_role'])) {
    $userId = intval($_POST['user_id']);
    $newRole = $_POST['role'] === 'admin' ? 1 : 0;
    $sql = "UPDATE users SET admin = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newRole, $userId]);
    $message = '사용자 권한이 변경되었습니다.';
}

// 사용자 삭제
if (isset($_POST['delete_user'])) {
    $userId = intval($_POST['user_id']);
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $message = '사용자가 삭제되었습니다.';
}

// 사용자 리스트 가져오기
try {
    $sql = "SELECT * FROM users ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<p>사용자 목록을 불러오는 데 실패했습니다: ' . htmlspecialchars($e->getMessage()) . '</p>';
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>사용자 관리 - 관리자 페이지</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            padding: 0.5rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        form {
            margin: 0.5rem 0;
        }
        button {
            background-color: #00CED1;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #009C9A;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>사용자 관리</h1>
        <?php if (isset($message)): ?>
            <script>
                alert('<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>');
            </script>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>학번</th>
                    <th>관리자</th>
                    <th>비밀번호 초기화</th>
                    <th>권한 변경</th>
                    <th>삭제</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['grade']) ?>0<?= htmlspecialchars($user['class']) ?><?= htmlspecialchars($user['number']) ?> <?= htmlspecialchars($user['name']) ?></td>
                        <td><?= $user['admin'] ? '관리자' : '일반 사용자' ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <button type="submit" name="reset_password">비밀번호 초기화</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <select name="role">
                                    <option value="admin" <?= $user['admin'] ? 'selected' : '' ?>>관리자</option>
                                    <option value="user" <?= !$user['admin'] ? 'selected' : '' ?>>일반 사용자</option>
                                </select>
                                <button type="submit" name="change_role">권한 변경</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('정말로 이 사용자를 삭제하시겠습니까?');">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <button type="submit" name="delete_user">삭제</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
