<!-- delete.php -->
<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";


if (!isset($_SESSION['username'])) {
    header("Location: /login");
    exit();
}

if (!isset($_GET['post_id'])) {
    echo "<script>alert('잘못된 접근입니다.');location.href='/';</script>";
    exit();
}

$post_id = intval($_GET['post_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "<script>alert('존재하지 않는 글입니다.');history.back();</script>";
    exit();
}

if ($post['author_id'] != $user_id) {
    echo "<script>alert('글 삭제 권한이 없습니다.');history.back();</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
    $d->bind_param("i", $post_id);
    if ($d->execute()) {
        header("Location: /");
        exit();
    } else {
        echo "<script>alert('글 삭제 중 오류가 발생했습니다.');history.back();</script>";
        exit();
    }
}

$stmt->close();
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>글 삭제</title>
    <link rel="stylesheet" href="/assets/css/navbar.css"/>
    <link rel="stylesheet" href="/assets/css/write_edit.css"/>
</head>
<body>
<?php include "navbar.php"; ?>
<div id="board_area" class="delete-area">
    <h1>글 삭제</h1>
    <p class="delete-text">정말 이 글을 삭제하시겠습니까?</p>
    <form action="/delete/<?php echo $post_id; ?>" method="POST" class="delete-form">
        <button type="submit" class="btn-delete">삭제</button>
        <a href="/post_detail/<?php echo $post_id; ?>" class="btn-cancel">취소</a>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
