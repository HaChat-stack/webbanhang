<?php
session_start();
require 'ketnoi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: san-pham.php');
    exit();
}

$product_id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: san-pham.php');
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ShopOnline</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 40px 0;
        }
        
        .product-image {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .product-info h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .product-price {
            color: #e74c3c;
            font-size: 32px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .product-stock {
            color: #27ae60;
            font-size: 16px;
            margin: 15px 0;
        }
        
        .product-description {
            line-height: 1.6;
            color: #666;
            margin: 20px 0;
        }
        
        .quantity-selector {
            margin: 30px 0;
        }
        
        .quantity-selector label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-controls button {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 18px;
        }
        
        .quantity-controls button:hover {
            background: #f5f5f5;
        }
        
        .quantity-controls input {
            width: 60px;
            height: 40px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 16px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 30px 0;
        }
        
        .add-to-cart-btn, .buy-now-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
        }
        
        .add-to-cart-btn {
            background: #4CAF50;
            color: white;
        }
        
        .add-to-cart-btn:hover {
            background: #45a049;
        }
        
        .buy-now-btn {
            background: #e74c3c;
            color: white;
        }
        
        .buy-now-btn:hover {
            background: #c0392b;
        }
        
        .add-to-cart-btn:disabled, .buy-now-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .notification.success {
            background: #4CAF50;
        }
        
        .notification.error {
            background: #e74c3c;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="product-detail">
            <div class="product-image-container">
                <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : 'https://via.placeholder.com/500'; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image">
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</div>
                <div class="product-stock">
                    <i class="fas fa-check-circle"></i> 
                    Còn lại: <?php echo $product['stock']; ?> sản phẩm
                </div>
                
                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Không có mô tả')); ?>
                </div>
                
                <div class="quantity-selector">
                    <label for="quantity">Số lượng:</label>
                    <div class="quantity-controls">
                        <button type="button" onclick="decreaseQuantity()">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button type="button" onclick="increaseQuantity()">+</button>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="add-to-cart-btn" onclick="addToCart()">
                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                    </button>
                    <button class="buy-now-btn" onclick="buyNow()">
                        <i class="fas fa-bolt"></i> Mua ngay
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        let currentQuantity = 1;
        const maxStock = <?php echo $product['stock']; ?>;
        const productId = <?php echo $product['id']; ?>;

        function decreaseQuantity() {
            if (currentQuantity > 1) {
                currentQuantity--;
                document.getElementById('quantity').value = currentQuantity;
            }
        }

        function increaseQuantity() {
            if (currentQuantity < maxStock) {
                currentQuantity++;
                document.getElementById('quantity').value = currentQuantity;
            } else {
                showNotification('Số lượng vượt quá tồn kho', 'error');
            }
        }

        function addToCart() {
            const quantity = document.getElementById('quantity').value;
            const button = document.querySelector('.add-to-cart-btn');
            const originalText = button.innerHTML;
            
            // Hiển thị loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
            button.disabled = true;
            
            fetch('them-vao-gio-hang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi kết nối');
                }
                return response.json();
            })
            .then(data => {
                // Khôi phục button
                button.innerHTML = originalText;
                button.disabled = false;
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateCartCount(data.cart_count);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
            });
        }

        function buyNow() {
            const quantity = document.getElementById('quantity').value;
            const button = document.querySelector('.buy-now-btn');
            const originalText = button.innerHTML;
            
            // Hiển thị loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            button.disabled = true;
            
            // Thêm vào giỏ hàng rồi chuyển đến trang thanh toán
            fetch('them-vao-gio-hang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'thanh-toan.php';
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra', 'error');
            });
        }

        // Hàm hiển thị thông báo
        function showNotification(message, type = 'success') {
            // Xóa thông báo cũ nếu có
            const oldNotification = document.querySelector('.notification');
            if (oldNotification) {
                oldNotification.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.classList.add('show'), 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

        // Hàm cập nhật số lượng giỏ hàng
        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = count;
            });
        }

        // Validate input quantity
        document.getElementById('quantity').addEventListener('change', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                this.value = 1;
                currentQuantity = 1;
            } else if (value > maxStock) {
                this.value = maxStock;
                currentQuantity = maxStock;
                showNotification('Số lượng tối đa là ' + maxStock, 'info');
            } else {
                currentQuantity = value;
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>