<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
        exit;
    }
    
    // Kiểm tra sản phẩm có tồn tại không
    require 'ketnoi.php';
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }
    
    $product = $result->fetch_assoc();
    $stmt->close();
    
    // Kiểm tra số lượng tồn kho
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Số lượng sản phẩm trong kho không đủ']);
        exit;
    }
    
    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // SỬA QUAN TRỌNG: Sử dụng product_id làm key trong session cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }
    
    // Tính tổng số lượng sản phẩm trong giỏ hàng
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
}
?>