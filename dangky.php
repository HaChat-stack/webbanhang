<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Đăng Ký</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="register-box">
            <h1>ĐĂNG KÝ TÀI KHOẢN</h1>
            
            <form method="POST" action="xulydangki.php">
                <div class="nhap-lieu">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" 
                           value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>" required>
                </div>
                
                <div class="nhap-lieu">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
                
                <div class="nhap-lieu">
                    <label for="confirm_password">Nhập lại mật khẩu:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                </div>
                
                <div class="nhap-lieu">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Nhập email" 
                           value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required>
                </div>
                
                <button type="submit" class="nut-dang-ky">Đăng Ký</button>
            </form>
            
            <div class="chuyen-trang">
                Đã có tài khoản? <a href="dangnhap.php">Đăng nhập ngay</a>
            </div>

            <?php if(isset($_GET['loi'])): ?>
                <div class="thong-bao-loi">
                    <strong>Lỗi:</strong> <?= htmlspecialchars($_GET['loi']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>