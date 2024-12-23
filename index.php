<?php
include __DIR__ . "/includes/db_connect.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $sql = "SELECT posts.*, users.username
            FROM posts
            LEFT JOIN users ON posts.author_id = users.id
            WHERE posts.title LIKE ? OR posts.content LIKE ?
            ORDER BY posts.post_id DESC";
    $stmt = $conn->prepare($sql);
    $like = '%' . $search . '%';
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT posts.*, users.username
            FROM posts
            LEFT JOIN users ON posts.author_id = users.id
            ORDER BY posts.post_id DESC";
    $result = $conn->query($sql);
}
if (!$result) {
    echo "쿼리 오류: " . $conn->error;
    exit();
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>HanTul SNS</title>
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
</head>
<body>
<?php include "includes/navbar.php"; ?>


<div id="board_area">
    <h1>자유게시판</h1>
    <h4>자유롭게 글을 쓸 수 있는 게시판입니다.</h4>
    <table class="list-table">
        <thead>
            <tr>
                <th width="70" class="center">번호</th>
                <th width="500" class="center">제목</th>
                <th width="120" class="center">작성자</th>
                <th width="250" class="center">작성일</th>
                <th width="100" class="center">조회수</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            $i = $result->num_rows;
            while ($post = $result->fetch_assoc()) {
                $title = $post['title'];
                if (mb_strlen($title, 'utf-8') > 30) {
                    $title = mb_substr($title, 0, 30, 'utf-8') . '...';
                }
                echo "<tr>";
                echo "<td class='center'>{$i}</td>";
                echo "<td><a href='/post_detail/" . $post['post_id'] . "'>" . htmlspecialchars($title) . "</a></td>";
                echo "<td class='center'>" . htmlspecialchars($post['username']) . "</td>";
                echo "<td class='center'>" . htmlspecialchars($post['created_at']) . "</td>";
                echo "<td class='center'>" . htmlspecialchars($post['hit']) . "</td>";
                echo "</tr>";
                $i--;
            }
        } else {
            echo "<tr><td colspan='5' class='center'>게시물이 없습니다.</td></tr>";
        }
        ?>
        </tbody>
    </table>
    <div id="write_btn">
        <a href="/write"><button>글쓰기</button></a>
    </div>
</div>
</body>
</html>
<?php
if (isset($stmt)) $stmt->close();
$conn->close();
?>
