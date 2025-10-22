<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'ketnoi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Kiểm tra trường rỗng
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: dangky.php?loi=" . urlencode("Vui lòng điền đầy đủ thông tin.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }

    // Kiểm tra định dạng email
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        header("Location: dangky.php?loi=" . urlencode("Chỉ chấp nhận email định dạng @gmail.com.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }

    // Kiểm tra tên đăng nhập đã tồn tại
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if ($stmt === false) {
        header("Location: dangky.php?loi=" . urlencode("Lỗi kết nối database.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header("Location: dangky.php?loi=" . urlencode("Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }
    $stmt->close();

    // Kiểm tra mật khẩu khớp
    if ($password !== $confirm_password) {
        header("Location: dangky.php?loi=" . urlencode("Mật khẩu nhập lại không khớp.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }

    // Kiểm tra độ dài mật khẩu
    if (strlen($password) < 6 || strlen($password) > 24) {
        header("Location: dangky.php?loi=" . urlencode("Mật khẩu phải từ 6 đến 24 ký tự.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }

    // Kiểm tra email đã tồn tại
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($stmt === false) {
        header("Location: dangky.php?loi=" . urlencode("Lỗi kết nối database.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: dangky.php?loi=" . urlencode("Email đã được đăng ký.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }
    $stmt->close();

    // Mã hóa mật khẩu và thêm người dùng
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    
    if ($stmt === false) {
        header("Location: dangky.php?loi=" . urlencode("Lỗi chuẩn bị câu lệnh.") . "&username=" . urlencode($username) . "&email=" . urlencode($email));
        exit();
    }
    
    $stmt->bind_param("sss", $username, $email, $hashed);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Đăng ký thành công! Bạn có thể đăng nhập.";
        header("Location: dangnhap.php");
    } else {
        header("Location: dangky.php?loi=" . urlencode("Lỗi khi đăng ký: " . $stmt->error) . "&username=" . urlencode($username) . "&email=" . urlencode($email));
    }
    $stmt->close();
    $conn->close();
} else {
    // Nếu không phải phương thức POST, chuyển hướng về trang đăng ký
    header("Location: dangky.php");
    exit();
}
?>