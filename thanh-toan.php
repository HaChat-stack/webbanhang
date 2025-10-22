<?php
session_start();

// Kiểm tra đăng nhập - chỉ kiểm tra một lần
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

require 'ketnoi.php';

// Lấy thông tin người dùng từ bảng nguoidung

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: gio-hang.php');
    exit();
}

// Tính tổng tiền đơn hàng
$total_amount = 0;
$cart_items = [];

foreach ($_SESSION['cart'] as $product_id => $item) {
    $stmt = $conn->prepare("SELECT name, price, image_url FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();
    $stmt->close();
    
    if ($product) {
        $item_total = $product['price'] * $item['quantity'];
        $total_amount += $item_total;
        
        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'image_url' => $product['image_url'],
            'quantity' => $item['quantity'],
            'total' => $item_total
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - ShopOnline</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 30px 0;
        }
        
        .checkout-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .payment-method.selected {
            border-color: #4CAF50;
            background: #f8fff8;
        }
        
        .payment-method input {
            margin-right: 10px;
        }
        
        .payment-icon {
            font-size: 24px;
            margin-right: 10px;
            color: #666;
        }
        
        .order-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-total {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
            margin: 20px 0;
        }
        
        .confirm-btn {
            width: 100%;
            padding: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .confirm-btn:hover {
            background: #45a049;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .order-item-quantity {
            color: #666;
            font-size: 14px;
        }

        .order-item-price {
            font-weight: bold;
            color: #e74c3c;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-cart i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1 style="margin: 30px 0;">Thanh Toán</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Giỏ hàng của bạn đang trống</h3>
                <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục thanh toán</p>
                <a href="san-pham.php" class="btn" style="margin-top: 20px;">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <form id="checkoutForm" action="xu-ly-thanh-toan.php" method="POST">
                <div class="checkout-container">
                    <div class="checkout-left">
                        <div class="checkout-section">
                            <h2 class="section-title">Thông tin giao hàng</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Họ và tên *</label>
                                    <input type="text" class="form-control" name="fullname" 
                                           value="<?php echo htmlspecialchars($user['hoten'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại *</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?php echo htmlspecialchars($user['sodienthoai'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Địa chỉ *</label>
                                <input type="text" class="form-control" name="address" 
                                       value="<?php echo htmlspecialchars($user['diachi'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tỉnh/Thành phố *</label>
                                    <select class="form-control" name="city" required>
                                        <option value="">Chọn tỉnh/thành phố</option>
                                        <option value="hcm">TP. Hồ Chí Minh</option>
                                        <option value="hn">Hà Nội</option>
                                        <option value="dn">Đà Nẵng</option>
                                        <option value="hp">Hải Phòng</option>
                                        <option value="ct">Cần Thơ</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Quận/Huyện *</label>
                                    <select class="form-control" name="district" required>
                                        <option value="">Chọn quận/huyện</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Ghi chú đơn hàng</label>
                                <textarea class="form-control" name="notes" rows="3" 
                                          placeholder="Ghi chú về đơn hàng..."></textarea>
                            </div>
                        </div>
                        
                        <div class="checkout-section">
                            <h2 class="section-title">Phương thức thanh toán</h2>
                            
                            <div class="payment-methods">
                                <label class="payment-method selected">
                                    <input type="radio" name="payment_method" value="cod" required checked>
                                    <i class="fas fa-money-bill-wave payment-icon"></i>
                                    <div>
                                        <strong>Thanh toán khi nhận hàng (COD)</strong>
                                        <p>Thanh toán bằng tiền mặt khi nhận hàng</p>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="banking">
                                    <i class="fas fa-university payment-icon"></i>
                                    <div>
                                        <strong>Chuyển khoản ngân hàng</strong>
                                        <p>Chuyển khoản qua Internet Banking</p>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="momo">
                                    <i class="fas fa-mobile-alt payment-icon"></i>
                                    <div>
                                        <strong>Ví điện tử MoMo</strong>
                                        <p>Thanh toán qua ứng dụng MoMo</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-right">
                        <div class="checkout-section">
                            <h2 class="section-title">Đơn hàng của bạn</h2>
                            
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-summary-item">
                                    <div class="order-item-details">
                                        <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="order-item-quantity">Số lượng: <?php echo $item['quantity']; ?></div>
                                    </div>
                                    <div class="order-item-price"><?php echo number_format($item['total'], 0, ',', '.'); ?> ₫</div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="order-summary-item">
                                <div>Tạm tính</div>
                                <div><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</div>
                            </div>
                            
                            <div class="order-summary-item">
                                <div>Phí vận chuyển</div>
                                <div>0 ₫</div>
                            </div>
                            
                            <div class="order-summary-item">
                                <div>Giảm giá</div>
                                <div>-0 ₫</div>
                            </div>
                            
                            <div class="order-total">
                                <div>Tổng cộng</div>
                                <div><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</div>
                            </div>
                            
                            <button type="submit" class="confirm-btn">
                                <i class="fas fa-shopping-bag"></i> Đặt hàng
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function selectPayment(element) {
            document.querySelectorAll('.payment-method').forEach(method => {
                method.classList.remove('selected');
            });
            element.classList.add('selected');
            const radio = element.querySelector('input[type="radio"]');
            radio.checked = true;
        }
        
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                selectPayment(this);
            });
        });
        
        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                if (!paymentMethod) {
                    alert('Vui lòng chọn phương thức thanh toán!');
                    return;
                }
                
                if (confirm('Xác nhận đặt hàng?')) {
                    this.submit();
                }
            });
        }

        const citySelect = document.querySelector('select[name="city"]');
        const districtSelect = document.querySelector('select[name="district"]');
        
        const districts = {
            'hcm': ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10', 'Quận 11', 'Quận 12', 'Quận Bình Thạnh', 'Quận Gò Vấp', 'Quận Phú Nhuận', 'Quận Tân Bình', 'Quận Tân Phú'],
            'hn': ['Quận Ba Đình', 'Quận Hoàn Kiếm', 'Quận Hai Bà Trưng', 'Quận Đống Đa', 'Quận Tây Hồ', 'Quận Cầu Giấy', 'Quận Thanh Xuân', 'Quận Hoàng Mai', 'Quận Long Biên'],
            'dn': ['Quận Hải Châu', 'Quận Thanh Khê', 'Quận Sơn Trà', 'Quận Ngũ Hành Sơn', 'Quận Liên Chiểu', 'Quận Cẩm Lệ'],
            'hp': ['Quận Hồng Bàng', 'Quận Ngô Quyền', 'Quận Lê Chân', 'Quận Hải An', 'Quận Kiến An', 'Quận Đồ Sơn'],
            'ct': ['Quận Ninh Kiều', 'Quận Bình Thủy', 'Quận Cái Răng', 'Quận Ô Môn', 'Quận Thốt Nốt']
        };
        
        if (citySelect && districtSelect) {
            citySelect.addEventListener('change', function() {
                const selectedCity = this.value;
                districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
                
                if (selectedCity && districts[selectedCity]) {
                    districts[selectedCity].forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.toLowerCase().replace(/\s+/g, '_');
                        option.textContent = district;
                        districtSelect.appendChild(option);
                    });
                }
            });
        }
    </script>
</body>
</html>