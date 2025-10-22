<?php
session_start();
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

$page_title = "Trang chủ";
include 'header.php';

require 'ketnoi.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Khám phá thế giới công nghệ</h1>
        <p>Những sản phẩm công nghệ mới nhất với giá tốt nhất</p>
        <a href="san-pham.php" class="btn">Mua ngay</a>
    </div>
</section>

<!-- Sản phẩm nổi bật -->
<section class="featured-products">
    <h2 class="section-title">Sản phẩm nổi bật</h2>
    <div class="product-grid">
        <?php
        // Sửa query để JOIN với bảng category và lấy category_name
        $sql = "SELECT p.id, p.name, p.description, p.price, p.image_url, p.stock, c.name as category_name 
                FROM products p 
                JOIN category c ON p.category_id = c.id 
                WHERE p.featured = 1 
                LIMIT 8";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $image_url = !empty($row["image_url"]) ? htmlspecialchars($row["image_url"]) : 'images/default-product.jpg';
                
                // Kiểm tra stock
                $stock = isset($row["stock"]) ? $row["stock"] : 100;
                $is_out_of_stock = $stock <= 0;
                $stock_text = $is_out_of_stock ? '<span style="color: #e74c3c">● Hết hàng</span>' : '<span style="color: #4CAF50">● Còn ' . $stock . ' sản phẩm</span>';
                
                echo '
                <div class="product-card">
                    <a href="chi-tiet-san-pham.php?id=' . $row["id"] . '">
                        <img src="' . $image_url . '" 
                             alt="' . htmlspecialchars($row["name"]) . '" 
                             class="product-image"
                             onerror="this.src=\'images/default-product.jpg\'">
                    </a>
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="chi-tiet-san-pham.php?id=' . $row["id"] . '">' . htmlspecialchars($row["name"]) . '</a>
                        </h3>
                        <p class="product-category">' . htmlspecialchars($row["category_name"]) . '</p>
                        <p class="product-description">' . htmlspecialchars(substr($row["description"], 0, 100)) . '...</p>
                        <div class="product-price">' . number_format($row["price"], 0, ',', '.') . ' ₫</div>
                        <div class="product-stock">' . $stock_text . '</div>
                        <div class="product-actions">
                            <button class="add-to-cart" onclick="addToCart(' . $row["id"] . ')" ' . ($is_out_of_stock ? 'disabled' : '') . '>
                                <i class="fas fa-shopping-cart"></i> ' . ($is_out_of_stock ? 'Hết hàng' : 'Thêm giỏ') . '
                            </button>
                            <button class="wishlist" onclick="toggleWishlist(' . $row["id"] . ')">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo "<div class='no-products' style='grid-column: 1 / -1; text-align: center; padding: 40px;'>
                    <i class='fas fa-box-open' style='font-size: 60px; color: #ddd; margin-bottom: 20px;'></i>
                    <h3>Không tìm thấy sản phẩm nổi bật</h3>
                    <p>Hãy thêm sản phẩm nổi bật trong trang quản lý</p>
                  </div>";
        }
        ?>
    </div>
</section>

<!-- Sản phẩm mới -->
<section class="new-products">
    <h2 class="section-title">Sản phẩm mới</h2>
    <div class="product-grid">
        <?php
        // Sửa query để JOIN với bảng category và lấy category_name
        $sql = "SELECT p.id, p.name, p.description, p.price, p.image_url, p.stock, c.name as category_name 
                FROM products p 
                JOIN category c ON p.category_id = c.id 
                ORDER BY p.created_at DESC 
                LIMIT 8";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $image_url = !empty($row["image_url"]) ? htmlspecialchars($row["image_url"]) : 'images/default-product.jpg';
                
                // Kiểm tra stock
                $stock = isset($row["stock"]) ? $row["stock"] : 100;
                $is_out_of_stock = $stock <= 0;
                $stock_text = $is_out_of_stock ? '<span style="color: #e74c3c">● Hết hàng</span>' : '<span style="color: #4CAF50">● Còn ' . $stock . ' sản phẩm</span>';
                
                echo '
                <div class="product-card">
                    <a href="chi-tiet-san-pham.php?id=' . $row["id"] . '">
                        <img src="' . $image_url . '" 
                             alt="' . htmlspecialchars($row["name"]) . '" 
                             class="product-image"
                             onerror="this.src=\'images/default-product.jpg\'">
                    </a>
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="chi-tiet-san-pham.php?id=' . $row["id"] . '">' . htmlspecialchars($row["name"]) . '</a>
                        </h3>
                        <p class="product-category">' . htmlspecialchars($row["category_name"]) . '</p>
                        <p class="product-description">' . htmlspecialchars(substr($row["description"], 0, 100)) . '...</p>
                        <div class="product-price">' . number_format($row["price"], 0, ',', '.') . ' ₫</div>
                        <div class="product-stock">' . $stock_text . '</div>
                        <div class="product-actions">
                            <button class="add-to-cart" onclick="addToCart(' . $row["id"] . ')" ' . ($is_out_of_stock ? 'disabled' : '') . '>
                                <i class="fas fa-shopping-cart"></i> ' . ($is_out_of_stock ? 'Hết hàng' : 'Thêm giỏ') . '
                            </button>
                            <button class="wishlist" onclick="toggleWishlist(' . $row["id"] . ')">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo "<div class='no-products' style='grid-column: 1 / -1; text-align: center; padding: 40px;'>
                    <i class='fas fa-search' style='font-size: 60px; color: #ddd; margin-bottom: 20px;'></i>
                    <h3>Không tìm thấy sản phẩm</h3>
                    <p>Hãy thêm sản phẩm mới trong trang quản lý</p>
                    <a href='themsanpham.php' class='btn' style='margin-top: 15px;'>Thêm sản phẩm</a>
                  </div>";
        }
        $conn->close();
        ?>
    </div>
</section>

<script>
function addToCart(productId) {
    fetch('them-vao-gio-hang.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã thêm sản phẩm vào giỏ hàng!', 'success');
            if (document.querySelector('.cart-count')) {
                document.querySelector('.cart-count').textContent = data.cart_count;
            }
        } else {
            showNotification('Có lỗi xảy ra: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
    });
}

function toggleWishlist(productId) {
    const heart = event.target;
    if (heart.classList.contains('far')) {
        heart.classList.remove('far');
        heart.classList.add('fas');
        heart.style.color = '#e74c3c';
        showNotification('Đã thêm vào yêu thích', 'success');
    } else {
        heart.classList.remove('fas');
        heart.classList.add('far');
        heart.style.color = '';
        showNotification('Đã xóa khỏi yêu thích', 'info');
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#e74c3c' : '#3498db'};
        color: white;
        border-radius: 5px;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Thêm CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'footer.php'; ?>