<?php
require_once __DIR__ . "/init.php";
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

if (isset($_COOKIE['user_id'])) {
    setcookie("user_id", "", time() - 3600, "/");
}

if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/");
}

session_destroy();
header("Location: /login");
exit;
?>
