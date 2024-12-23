<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";


?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div id="register_area">
    <h2>가입</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo '<script>alert(' . json_encode($_SESSION['error']) . ');</script>';
        unset($_SESSION['error']);
    }
    ?>
    <form action="/includes/register_process.php" method="POST">
        <div class="input-group">
            <label for="username">아이디</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="input-group">
            <label for="email">이메일</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="input-group">
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="input-group">
            <label for="confirm_password">비밀번호 확인</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit">가입하기</button>
    </form>
    <p class="redirect">이미 회원이라면? <a href="/login">로그인</a></p>
</div>
</body>
</html>
