<?php
session_start();
$page_title = "Giỏ hàng";
include 'header.php';

// Kiểm tra và khởi tạo giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo '<div class="container" style="text-align: center; padding: 50px;">
            <i class="fas fa-shopping-cart" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
            <h2>Giỏ hàng trống</h2>
            <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
            <a href="san-pham.php" class="btn btn-primary">Tiếp tục mua sắm</a>
          </div>';
    include 'footer.php';
    exit;
}
?>

<div class="container">
    <h1>Giỏ hàng của bạn</h1>
    
    <div class="cart-container">
        <div class="cart-items">
            <?php
            $total_amount = 0;
            
            // SỬA QUAN TRỌNG: Duyệt giỏ hàng với product_id làm key
            foreach ($_SESSION['cart'] as $product_id => $item) {
                // Lấy thông tin sản phẩm từ database để đảm bảo dữ liệu mới nhất
                require 'ketnoi.php';
                $stmt = $conn->prepare("SELECT name, price, image_url, stock FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $subtotal = $product['price'] * $item['quantity'];
                    $total_amount += $subtotal;
                    
                    echo '
                    <div class="cart-item" data-product-id="' . $product_id . '">
                        <div class="item-image">
                            <img src="' . ($product['image_url'] ?: 'images/default-product.jpg') . '" 
                                 alt="' . htmlspecialchars($product['name']) . '">
                        </div>
                        <div class="item-details">
                            <h3>' . htmlspecialchars($product['name']) . '</h3>
                            <p class="item-price">' . number_format($product['price'], 0, ',', '.') . ' ₫</p>
                        </div>
                        <div class="item-quantity">
                            <button class="quantity-btn" onclick="updateQuantity(' . $product_id . ', -1)">-</button>
                            <input type="number" value="' . $item['quantity'] . '" min="1" max="' . $product['stock'] . '" 
                                   onchange="updateQuantity(' . $product_id . ', this.value)">
                            <button class="quantity-btn" onclick="updateQuantity(' . $product_id . ', 1)">+</button>
                        </div>
                        <div class="item-subtotal">
                            ' . number_format($subtotal, 0, ',', '.') . ' ₫
                        </div>
                        <div class="item-remove">
                            <button class="remove-btn" onclick="removeFromCart(' . $product_id . ')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>';
                }
                $stmt->close();
                $conn->close();
            }
            ?>
        </div>
        
        <div class="cart-summary">
            <h3>Tổng cộng</h3>
            <div class="summary-row">
                <span>Tạm tính:</span>
                <span><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</span>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <span>0 ₫</span>
            </div>
            <div class="summary-row total">
                <span>Thành tiền:</span>
                <span><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</span>
            </div>
            <a href="thanh-toan.php" class="btn-checkout">Tiến hành thanh toán</a>
            <a href="san-pham.php" class="btn-continue">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>

<script>
function updateQuantity(productId, change) {
    let newQuantity;
    
    if (typeof change === 'number') {
        // Nếu change là số (+1 hoặc -1)
        const currentInput = document.querySelector(`[data-product-id="${productId}"] .quantity-input`);
        newQuantity = parseInt(currentInput.value) + change;
    } else {
        // Nếu change là giá trị mới từ input
        newQuantity = parseInt(change);
    }
    
    if (newQuantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    fetch('cap-nhat-gio-hang.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function removeFromCart(productId) {
    if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        fetch('xoa-khoi-gio-hang.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}
</script>

<style>
.cart-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
    margin-top: 30px;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr 150px 120px 50px;
    gap: 15px;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-details h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.item-price {
    color: #e74c3c;
    font-weight: bold;
    margin: 0;
}

.item-quantity {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    border-radius: 4px;
}

.item-quantity input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    padding: 5px;
    border-radius: 4px;
}

.item-subtotal {
    font-weight: bold;
    color: #e74c3c;
    text-align: right;
}

.remove-btn {
    background: none;
    border: none;
    color: #e74c3c;
    cursor: pointer;
    font-size: 16px;
}

.cart-summary {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    height: fit-content;
}

.cart-summary h3 {
    margin-bottom: 20px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.summary-row.total {
    font-weight: bold;
    font-size: 18px;
    color: #e74c3c;
    border-bottom: none;
}

.btn-checkout {
    display: block;
    width: 100%;
    background: #28a745;
    color: white;
    text-align: center;
    padding: 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin-bottom: 10px;
    transition: background 0.3s;
}

.btn-checkout:hover {
    background: #218838;
}

.btn-continue {
    display: block;
    width: 100%;
    background: #6c757d;
    color: white;
    text-align: center;
    padding: 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: background 0.3s;
}

.btn-continue:hover {
    background: #5a6268;
}

@media (max-width: 768px) {
    .cart-container {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        grid-template-areas: 
            "image details"
            "quantity subtotal"
            "remove remove";
        gap: 10px;
    }
    
    .item-image { grid-area: image; }
    .item-details { grid-area: details; }
    .item-quantity { grid-area: quantity; }
    .item-subtotal { grid-area: subtotal; text-align: left; }
    .item-remove { grid-area: remove; text-align: right; }
}
</style>

<?php include 'footer.php'; ?>