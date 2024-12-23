<?php
require_once __DIR__ . "/init.php";
include __DIR__ . "/db_connect.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "관리자 권한이 없습니다.";
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "유효하지 않은 요청입니다.";
        header("Location: /admin");
        exit();
    }

    if ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);

        if ($user_id === $_SESSION['user_id']) {
            $_SESSION['error'] = "자기 자신을 삭제할 수 없습니다.";
            header("Location: /admin");
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {

            $log_file = '/var/www/html/logs/admin_actions.log';
            $admin_id = $_SESSION['user_id'];
            $log_entry = date('Y-m-d H:i:s') . " - User ID {$user_id} deleted by Admin ID {$admin_id}\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);

            $_SESSION['success'] = "사용자가 성공적으로 삭제되었습니다.";
        } else {
            $_SESSION['error'] = "사용자 삭제에 실패했습니다.";
        }
        $stmt->close();
        header("Location: /admin");
        exit();
    }

    if ($_POST['action'] === 'change_role' && isset($_POST['user_id']) && isset($_POST['new_role'])) {
        $user_id = intval($_POST['user_id']);
        $new_role = $_POST['new_role'] === 'admin' ? 'admin' : 'user';
        if ($user_id === $_SESSION['user_id']) {
            $_SESSION['error'] = "자기 자신의 역할을 변경할 수 없습니다.";
            header("Location: /admin");
            exit();
        }

        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        if ($stmt->execute()) {
            $log_file = '/var/www/html/logs/admin_actions.log';
            $admin_id = $_SESSION['user_id'];
            $log_entry = date('Y-m-d H:i:s') . " - User ID {$user_id} role changed to {$new_role} by Admin ID {$admin_id}\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);

            $_SESSION['success'] = "사용자 역할이 성공적으로 변경되었습니다.";
        } else {
            $_SESSION['error'] = "사용자 역할 변경에 실패했습니다.";
        }
        $stmt->close();
        header("Location: /admin");
        exit();
    }

    if ($_POST['action'] === 'add_user' && isset($_POST['new_username']) && isset($_POST['new_password'])) {
        $new_username = trim($_POST['new_username']);
        $new_password = $_POST['new_password'];
        $new_role = $_POST['new_role'] === 'admin' ? 'admin' : 'user';

        if (empty($new_username) || empty($new_password)) {
            $_SESSION['error'] = "사용자 이름과 비밀번호를 모두 입력해주세요.";
            header("Location: /admin");
            exit();
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = "이미 존재하는 사용자 이름입니다.";
            $stmt->close();
            header("Location: /admin");
            exit();
        }
        $stmt->close();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $new_username, $hashed_password, $new_role);
        if ($stmt->execute()) {
            $log_file = '/var/www/html/logs/admin_actions.log';
            $admin_id = $_SESSION['user_id'];
            $log_entry = date('Y-m-d H:i:s') . " - New user '{$new_username}' added as '{$new_role}' by Admin ID {$admin_id}\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);

            $_SESSION['success'] = "사용자가 성공적으로 추가되었습니다.";
        } else {
            $_SESSION['error'] = "사용자 추가에 실패했습니다.";
        }
        $stmt->close();
        header("Location: /admin");
        exit();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">

    <script>
        <?php
        if (isset($_SESSION['success'])) {
            echo "Swal.fire({
                icon: 'success',
                title: '성공',
                text: '" . addslashes($_SESSION['success']) . "'
            });\n";
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo "Swal.fire({
                icon: 'error',
                title: '오류',
                text: '" . addslashes($_SESSION['error']) . "'
            });\n";
            unset($_SESSION['error']);
        }
        ?>
    </script>
</head>
<body>
    <?php include "navbar.php"; ?>
    <div id="admin_area">
        <h1>관리자 페이지</h1>
        <p>여기서만 볼 수 있는 관리자 기능.</p>
        <br><hr><br>
        
        <form method="GET" action="/admin">
            <input type="text" name="search" placeholder="사용자 이름 검색" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">검색</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>사용자 이름</th>
                    <th>역할</th>
                    <th>가입일</th>
                    <th>액션</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $search_query = "";
                if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                    $search_query = trim($_GET['search']);
                    $stmt = $conn->prepare("SELECT id, username, role, created_at FROM users WHERE username LIKE ? LIMIT 100");
                    $like_search = "%" . $search_query . "%";
                    $stmt->bind_param("s", $like_search);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $sql = "SELECT id, username, role, created_at FROM users LIMIT 100";
                    $result = $conn->query($sql);
                }

                if ($result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td class="action-buttons">
                        <form action="/admin" method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="hidden" name="action" value="change_role">
                            <select name="new_role" onchange="this.form.submit()">
                                <option value="user" <?php if($row['role'] === 'user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if($row['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                            </select>
                        </form>
                        <form action="/admin" method="POST" style="display:inline;" onsubmit="return confirm('정말로 이 사용자를 삭제하시겠습니까?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="delete-btn">삭제</button>
                        </form>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                    echo "<tr><td colspan='5'>등록된 사용자가 없습니다.</td></tr>";
                endif;
                ?>
            </tbody>
        </table>
        
        
        
        <div class="add-user-form">
            <h2>새 사용자 추가</h2>
            <form action="/admin" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="add_user">
                <label for="new_username">사용자 이름:</label>
                <input type="text" id="new_username" name="new_username" required>
                
                <label for="new_password">비밀번호:</label>
                <input type="password" id="new_password" name="new_password" required>
                
                <label for="new_role">역할:</label>
                <select id="new_role" name="new_role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                
                <button type="submit">추가</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
