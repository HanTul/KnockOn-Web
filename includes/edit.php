<?php
session_start();
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
    echo "<script>alert('해당 글을 수정할 권한이 없습니다.');history.back();</script>";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if (empty($title) || empty($content)) {
        echo "<script>alert('제목과 내용을 모두 입력해주세요.');history.back();</script>";
        exit();
    }
    $u = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE post_id = ?");
    $u->bind_param("ssi", $title, $content, $post_id);
    if ($u->execute()) {
        header("Location: /post_detail/$post_id");
        exit();
    } else {
        echo "<script>alert('글 수정 중 오류가 발생했습니다.');history.back();</script>";
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
    <title>글 수정</title>
    <link rel="stylesheet" href="/assets/css/write_edit.css"/>
    <link rel="stylesheet" href="/assets/css/navbar.css"/>
</head>
<body>
<?php include "navbar.php"; ?>
<div id="board_area">
    <h1>글 수정</h1>
    <form action="/edit/<?php echo $post_id; ?>" method="POST">
        <table class="write-table">
            <tr>
                <th>제목</th>
                <td><input type="text" name="title" size="100" value="<?php echo htmlspecialchars($post['title']); ?>" required></td>
            </tr>
            <tr>
                <th>내용</th>
                <td><textarea name="content" rows="15" cols="100" required><?php echo htmlspecialchars($post['content']); ?></textarea></td>
            </tr>
        </table>
        <div id="write_buttons">
            <button type="submit">수정 완료</button>
            <a href="/post_detail/<?php echo $post_id; ?>"><button type="button">취소</button></a>
        </div>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
