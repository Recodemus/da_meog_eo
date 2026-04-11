<?php
session_start();

// 세션 변수 모두 제거
$_SESSION = array();

// 세션 파기
session_destroy();

// 로그인 화면으로 이동
header("Location: auth.php");
exit;
?>