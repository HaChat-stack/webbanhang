<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if ($product_id === null || !isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        exit;
    }
    
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Số lượng phải lớn hơn 0']);
        exit;
    }
    
    // Kiểm tra số lượng tồn kho
    require 'ketnoi.php';
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Số lượng sản phẩm trong kho không đủ']);
        exit;
    }
    
    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật giỏ hàng']);
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
}
?>