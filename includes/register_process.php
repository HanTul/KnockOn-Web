<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);

    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $_SESSION['error'] = "모든 필드를 입력해주세요.";
        header("Location: /register");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "비밀번호가 일치하지 않습니다.";
        header("Location: /register");
        exit;
    }

    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "이미 사용 중인 아이디 또는 이메일입니다.";
        header("Location: /register");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $insert_sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $username, $hashed_password, $email);

    if ($insert_stmt->execute()) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $insert_stmt->insert_id;
        $_SESSION['username'] = $username;
        header("Location: /");
        exit;
    } else {
        $_SESSION['error'] = "회원가입 중 오류가 발생했습니다.";
        header("Location: /register");
        exit;
    }
} else {
    header("Location: /register");
    exit;
}
?>
