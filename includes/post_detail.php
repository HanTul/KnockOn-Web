<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";



if (!isset($_GET['post_id'])) {
    header("Location: /");
    exit();
}
$post_id = intval($_GET['post_id']);
$update_hit = $conn->prepare("UPDATE posts SET hit = hit + 1 WHERE post_id = ?");
$update_hit->bind_param("i", $post_id);
$update_hit->execute();
$update_hit->close();

$stmt = $conn->prepare("
    SELECT posts.*, users.username 
    FROM posts
    LEFT JOIN users ON posts.author_id = users.id
    WHERE posts.post_id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
if (!$post) {
    echo "존재하지 않는 글입니다.";
    exit();
}
$stmt->close();
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8"/>
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="/assets/css/navbar.css"/>
    <link rel="stylesheet" href="/assets/css/post_detail.css"/>
</head>
<body>
<?php include "navbar.php"; ?>
<div id="post_area">
    <div class="post-header">
        <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
        <div class="post-meta">
            <span class="meta-item">작성자: <?php echo htmlspecialchars($post['username']); ?></span>
            <span class="meta-item">작성일: <?php echo htmlspecialchars($post['created_at']); ?></span>
            <span class="meta-item">조회수: <?php echo htmlspecialchars($post['hit']); ?></span>
        </div>
    </div>
    <div class="post-body">
        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
        <?php if (!empty($post['file_path'])): ?>
            <div class="post-file">
                <?php
                $upload_url = "/uploads/" . urlencode($post['file_path']);
                $ext = pathinfo($post['file_path'], PATHINFO_EXTENSION);
                $allowed_ext = ['png','jpg','jpeg','gif','webp'];
                if (in_array(strtolower($ext), $allowed_ext)): 
                ?>
                    <img src="<?php echo $upload_url; ?>" alt="첨부이미지" style="max-width:100%; height:auto;">
                <?php else: ?>
                    <a href="<?php echo $upload_url; ?>" download>첨부파일 다운로드</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="post-footer">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['author_id']): ?>
            <a href="/edit/<?php echo $post_id; ?>"><button class="action-button">수정</button></a>
            <a href="/delete/<?php echo $post_id; ?>"><button class="action-button">삭제</button></a>
        <?php endif; ?>
        <a href="/"><button class="action-button secondary">목록으로</button></a>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
