<?php
include 'config.php';

// 관리자인지 확인합니다.
$isLoggedIn = false;
$isAdmin = false;
if (isset($_COOKIE['username'])) {
    $username = base64_decode($_COOKIE['username']);
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $isLoggedIn = true;
        if ($user['admin'] == 1) {
            $isAdmin = true;
        }
    }
}

if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

// 문의사항 목록을 가져옵니다.
try {
    $sql = "SELECT * FROM inquiries ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $inquiries = $stmt->fetchAll(); // fetchAll() 메소드로 데이터를 가져옵니다.
} catch (PDOException $e) {
    echo '<p>문의사항을 불러오는 데 실패했습니다: ' . htmlspecialchars($e->getMessage()) . '</p>';
    $inquiries = []; // 오류 발생 시 빈 배열로 초기화
}

if (isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $id = $_POST['inquiry_id'];
    try {
        $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo '<p>상태가 업데이트되었습니다.</p>';
        header('Refresh:0'); // 페이지 새로고침
    } catch (PDOException $e) {
        echo '<p>상태 업데이트에 실패했습니다: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}

if (isset($_POST['delete_inquiry'])) {
    $id = $_POST['inquiry_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
        $stmt->execute([$id]);
        echo '<p>문의사항이 삭제되었습니다.</p>';
        header('Refresh:0'); // 페이지 새로고침
    } catch (PDOException $e) {
        echo '<p>문의사항 삭제에 실패했습니다: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>문의사항 관리</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background-color: #f0f0f0;
            color: #333;
            padding: 2rem;
        }

        h1 {
            color: #00CED1;
            margin-bottom: 1rem;
        }

        .inquiry-item {
            background-color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            cursor: pointer;
            border: 1px solid #ddd;
        }

        .inquiry-item.active {
            border-color: #00CED1;
        }

        .details {
            display: none;
            padding: 1rem;
            background-color: #f9f9f9;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .status-form {
            margin-top: 1rem;
        }

        select, button {
            margin-bottom: 1rem;
        }

        .error {
            color: red;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <h1>문의사항 관리</h1>
    <?php if (!empty($inquiries)): ?>
        <?php foreach ($inquiries as $inquiry): ?>
            <div class="inquiry-item" data-id="<?= htmlspecialchars($inquiry['id']) ?>">
                <h2><?= htmlspecialchars($inquiry['title']) ?></h2>
                <p>작성일: <?= htmlspecialchars(date('Y-m-d', strtotime($inquiry['created_at']))) ?></p>
                <p>상태: <?= htmlspecialchars(ucfirst($inquiry['status'])) ?></p>
                <div class="details">
                    <p><strong>문의 내용:</strong> <?= nl2br(htmlspecialchars($inquiry['content'])) ?></p>
                    <p><strong>유형:</strong> <?= htmlspecialchars(ucfirst($inquiry['type'])) ?></p>
                    <p><strong>작성자:</strong> <?= htmlspecialchars($inquiry['username']) ?></p>
                    <form class="status-form" method="post">
                        <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars($inquiry['id']) ?>">
                        <label for="status">상태 변경:</label>
                        <select name="status" id="status" required>
                            <option value="pending" <?= $inquiry['status'] == 'pending' ? 'selected' : '' ?>>대기 중</option>
                            <option value="in_progress" <?= $inquiry['status'] == 'in_progress' ? 'selected' : '' ?>>진행 중</option>
                            <option value="resolved" <?= $inquiry['status'] == 'resolved' ? 'selected' : '' ?>>완료</option>
                        </select>
                        <button type="submit" name="update_status">상태 업데이트</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars($inquiry['id']) ?>">
                        <button type="submit" name="delete_inquiry" onclick="return confirm('정말로 이 문의사항을 삭제하시겠습니까?');">문의사항 삭제</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>문의사항이 없습니다.</p>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.inquiry-item').on('click', function() {
                const details = $(this).find('.details');
                details.slideToggle(); // 상세 정보 표시/숨기기
                $(this).toggleClass('active'); // active 클래스 토글
            });

            // 폼 제출 시 상태 업데이트
            $('form.status-form').on('submit', function(e) {
                const status = $(this).find('select[name="status"]').val();
                const inquiryId = $(this).find('input[name="inquiry_id"]').val();
                $.post('', { update_status: true, status: status, inquiry_id: inquiryId })
                    .done(function() {
                        alert('상태가 업데이트되었습니다.');
                        location.reload();
                    })
                    .fail(function() {
                        alert('상태 업데이트에 실패했습니다.');
                    });
                e.preventDefault(); // 폼의 기본 제출 동작 방지
            });
        });
    </script>
</body>
</html>
