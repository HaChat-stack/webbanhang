<?php
session_start();
if (!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header('Location: trang-chu.php');
    exit();
}

$order_id = $_SESSION['order_id'] ?? '';
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);

$page_title = "Cảm ơn - ShopOnline";
include 'header.php';
?>

<div class="container">
    <div style="text-align: center; padding: 60px 20px;">
        <i class="fas fa-check-circle" style="font-size: 80px; color: #4CAF50; margin-bottom: 30px;"></i>
        <h1 style="color: #4CAF50; margin-bottom: 20px;">Đặt hàng thành công!</h1>
        <p style="font-size: 18px; margin-bottom: 10px;">Cảm ơn bạn đã đặt hàng tại ShopOnline</p>
        <p style="font-size: 16px; color: #666; margin-bottom: 30px;">
            Mã đơn hàng của bạn: <strong>#<?php echo $order_id; ?></strong><br>
            Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận đơn hàng.
        </p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="trang-chu.php" class="btn" style="background: #4CAF50;">Tiếp tục mua sắm</a>
            <a href="tai-khoan.php" class="btn" style="background: #3498db;">Xem đơn hàng</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>