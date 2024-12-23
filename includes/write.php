<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";


if (!isset($_SESSION['username'])) {
    header("Location: /login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $author_id = $_SESSION['user_id'];
    $file_path = null;

    if (empty($title) || empty($content)) {
        $error = "제목과 내용을 모두 입력해주세요.";
    } else {
        if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['upload_file']['tmp_name'];
            $original_name = basename($_FILES['upload_file']['name']);
            $ext = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_name = uniqid("file_") . "." . $ext;
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $target_path = $upload_dir . $new_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                $file_path = $new_name;
            } else {
                $error = "파일 업로드에 실패했습니다.";
            }
        }

        if (!isset($error)) {
            $stmt = $conn->prepare("
                INSERT INTO posts (title, content, author_id, created_at, hit, file_path) 
                VALUES (?, ?, ?, NOW(), 0, ?)
            ");
            $stmt->bind_param("ssis", $title, $content, $author_id, $file_path);
            if ($stmt->execute()) {
                header("Location: /");
                exit();
            } else {
                $error = "글 작성 중 문제가 발생했습니다.";
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8"/>
    <title>글쓰기</title>
    <link rel="stylesheet" href="/assets/css/navbar.css"/>
    <link rel="stylesheet" href="/assets/css/write_edit.css"/>
</head>
<body>
<?php include "navbar.php"; ?>
<div id="board_area">
    <h1>글쓰기</h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="/includes/write.php" method="POST" enctype="multipart/form-data">
        <table class="write-table">
            <tr>
                <th>제목</th>
                <td><input type="text" name="title" required></td>
            </tr>
            <tr>
                <th>내용</th>
                <td><textarea name="content" rows="10" required></textarea></td>
            </tr>
            <tr>
                <th>파일 첨부</th>
                <td><input type="file" name="upload_file"></td>
            </tr>
        </table>
        <div id="write_buttons">
            <button type="submit">작성 완료</button>
            <a href="/"><button type="button">취소</button></a>
        </div>
    </form>
</div>
</body>
</html>
<?php
$conn->close();
?>
