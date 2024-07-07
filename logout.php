<?php
session_start();  // 세션을 시작합니다
session_unset(); // 세션 변수를 모두 제거합니다
session_destroy(); // 세션을 파괴합니다

// 쿠키를 삭제합니다
setcookie('user_id', '', time() - 3600, '/'); // 만료된 쿠키를 설정하여 삭제
setcookie('username', '', time() - 3600, '/'); // 만료된 쿠키를 설정하여 삭제

// 리다이렉트합니다
header('Location: index.php');
exit();
?>
