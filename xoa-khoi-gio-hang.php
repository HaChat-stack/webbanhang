<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    
    if ($product_id === null || !isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        exit;
    }
    
    // Xóa sản phẩm khỏi giỏ hàng
    unset($_SESSION['cart'][$product_id]);
    
    echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
}
?>