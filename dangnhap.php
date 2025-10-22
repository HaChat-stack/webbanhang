<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Đăng Nhập</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>ĐĂNG NHẬP HỆ THỐNG</h1>
            
            <form action="xu-ly-dang-nhap.php" method="POST">
                <div class="nhap-lieu">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                </div>
                
                <div class="nhap-lieu">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
                
                <button type="submit" class="nut-dang-nhap">Đăng Nhập</button>
            </form>
            
            <div class="chuyen-trang">
                Chưa có tài khoản? <a href="dangky.php">Đăng ký ngay</a>
            </div>
            
            <?php if(isset($_GET['loi'])): ?>
                <div class="thong-bao-loi">
                    <?php echo htmlspecialchars($_GET['loi']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>