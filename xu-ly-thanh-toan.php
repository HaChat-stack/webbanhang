<?php
session_start();

// Kiểm tra đăng nhập - chỉ kiểm tra một lần
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

require 'ketnoi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy thông tin từ form
    $user_id = $_SESSION['id'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $district = trim($_POST['district']);
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = trim($_POST['payment_method']);
    
    // Kiểm tra giỏ hàng
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['error'] = 'Giỏ hàng trống';
        header('Location: gio-hang.php');
        exit();
    }
    
    // Tính tổng tiền
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            // Kiểm tra tồn kho
            if ($product['stock'] < $item['quantity']) {
                $_SESSION['error'] = 'Sản phẩm ' . $product_id . ' không đủ số lượng tồn kho';
                header('Location: thanh-toan.php');
                exit();
            }
            $total_amount += $product['price'] * $item['quantity'];
        }
    }
    
    try {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        // Thêm đơn hàng vào database
        $stmt = $conn->prepare("INSERT INTO orders (user_id, fullname, phone, email, address, city, district, notes, payment_method, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssi", $user_id, $fullname, $phone, $email, $address, $city, $district, $notes, $payment_method, $total_amount);
        
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi tạo đơn hàng: ' . $stmt->error);
        }
        
        $order_id = $stmt->insert_id;
        $stmt->close();
        
        // Thêm chi tiết đơn hàng và cập nhật tồn kho
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($product) {
                // Thêm chi tiết đơn hàng
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisii", $order_id, $product_id, $product['name'], $product['price'], $item['quantity']);
                
                if (!$stmt->execute()) {
                    throw new Exception('Lỗi khi thêm chi tiết đơn hàng: ' . $stmt->error);
                }
                $stmt->close();
                
                // Cập nhật tồn kho
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $product_id);
                if (!$stmt->execute()) {
                    throw new Exception('Lỗi khi cập nhật tồn kho: ' . $stmt->error);
                }
                $stmt->close();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Xóa giỏ hàng
        unset($_SESSION['cart']);
        
        // Chuyển hướng đến trang cảm ơn
        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $order_id;
        header('Location: cam-on.php');
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        $_SESSION['error'] = 'Có lỗi xảy ra khi đặt hàng: ' . $e->getMessage();
        header('Location: thanh-toan.php');
        exit();
    }
} else {
    header('Location: thanh-toan.php');
    exit();
}
?>