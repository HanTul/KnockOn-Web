<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";


?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div id="login_area">
    <h2>로그인</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo '<script>alert(' . json_encode($_SESSION['error']) . ');</script>';
        unset($_SESSION['error']);
    }
    ?>
    <form action="/includes/login_process.php" method="POST">
        <div class="input-group">
            <label for="username">아이디</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="input-group">
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">로그인</button>
    </form>
    <p class="redirect">아직 회원이 아니신가요? <a href="/register">회원가입</a></p>
</div>

</body>
</html>
