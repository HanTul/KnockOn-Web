<?php
$servername = "localhost";
$username = "사용자이름";
$password = "비밀번호!";
$dbname = "데이터베이스이름";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
