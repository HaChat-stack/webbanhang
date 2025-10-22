<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Kiểm tra kết nối database
try {
    require 'ketnoi.php';
    
    if (!$conn) {
        throw new Exception('Không thể kết nối database');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi kết nối database: ' . $e->getMessage()
    ]);
    exit;
}

header('Content-Type: application/json');

// Xác định hành động cần thực hiện
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update':
        handleUpdateCart();
        break;
        
    case 'remove':
        handleRemoveFromCart();
        break;
        
    case 'get_count':
        handleGetCartCount();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
        break;
}

function handleUpdateCart() {
    global $conn;
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }
    
    if (!isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        return;
    }
    
    try {
        // Kiểm tra tồn kho
        $stmt = $conn->prepare("SELECT stock, name FROM products WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn');
        }
        
        $stmt->bind_param("i", $product_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thực thi truy vấn');
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            $stmt->close();
            return;
        }
        
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($quantity <= $product['stock']) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            
            // Tính tổng số lượng giỏ hàng
            $cart_count = getCartCount();
            
            echo json_encode([
                'success' => true, 
                'cart_count' => $cart_count,
                'message' => 'Đã cập nhật số lượng ' . $product['name']
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Số lượng vượt quá tồn kho. Chỉ còn ' . $product['stock'] . ' sản phẩm'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ]);
    }
}

function handleRemoveFromCart() {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
        return;
    }
    
    if (!isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        return;
    }
    
    $product_name = $_SESSION['cart'][$product_id]['name'];
    unset($_SESSION['cart'][$product_id]);
    
    // Tính tổng số lượng giỏ hàng mới
    $cart_count = getCartCount();
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cart_count,
        'message' => 'Đã xóa "' . $product_name . '" khỏi giỏ hàng'
    ]);
}

function handleGetCartCount() {
    $cart_count = getCartCount();
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cart_count
    ]);
}

function getCartCount() {
    $cart_count = 0;
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
    }
    return $cart_count;
}

// Đóng kết nối
if (isset($conn)) {
    $conn->close();
}
?>