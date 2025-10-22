<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '20072007';
$db = 'shoponline';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>

