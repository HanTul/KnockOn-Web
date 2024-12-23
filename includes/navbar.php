<?php
require_once __DIR__ . "/init.php";

if (!isset($_SESSION['username']) && isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
}
?>
<nav class="navbar">
  <div class="navbar-container">
    <a href="/" class="navbar-logo">HanTul SNS</a>
    <div class="navbar-search">
      <form action="/search" method="GET" style="width:100%;">
        <input type="text" name="search" placeholder="검색어 입력">
      </form>
    </div>
    <div class="navbar-auth">
      <?php if (isset($_SESSION['username'])): ?>
        <span class="navbar-user"><?php echo htmlspecialchars($_SESSION['username']); ?>님</span>
        <a href="/logout" class="navbar-button">로그아웃</a>
      <?php else: ?>
        <a href="/login" class="navbar-button">로그인</a>
        <a href="/register" class="navbar-button signup">회원가입</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

