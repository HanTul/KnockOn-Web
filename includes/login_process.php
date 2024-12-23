<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "아이디와 비밀번호를 모두 입력해주세요.";
        header("Location: /login");
        exit;
    } else {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $_SESSION['error'] = "데이터베이스 오류가 발생했습니다.";
            header("Location: /login");
            exit;
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: /");
            exit();
        } else {
            $_SESSION['error'] = "아이디 또는 비밀번호가 올바르지 않습니다.";
            header("Location: /login");
            exit;
        }
    }
} else {
    header("Location: /login");
    exit;
}
?>
