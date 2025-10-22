<?php
require 'ketnoi.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($password)) {
        header("Location: dangnhap.php?loi=" . urlencode("Vui lòng điền đầy đủ thông tin."));
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    if (!$stmt) {
        header("Location: dangnhap.php?loi=" . urlencode("Lỗi hệ thống. Vui lòng thử lại."));
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $username_db, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username_db;
            $_SESSION["da_dang_nhap"] = true;

            header("Location: trang-chu.php");
            exit;
        } else {
            header("Location: dangnhap.php?loi=" . urlencode("Mật khẩu không đúng."));
            exit;
        }
    } else {
        header("Location: dangnhap.php?loi=" . urlencode("Tên đăng nhập không tồn tại."));
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: dangnhap.php");
    exit;
}
?>