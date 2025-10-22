<?php
session_start();
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

$page_title = "Sản phẩm";
include 'header.php';

require 'ketnoi.php';

// Xử lý filter
$category_slug = $_GET['danh_muc'] ?? '';
$sub_category_slug = $_GET['danh_muc_con'] ?? '';
$search = $_GET['q'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Xây dựng query với JOIN bảng category
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        JOIN category c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];
$types = "";

// Filter theo danh mục
if (!empty($category_slug)) {
    if (!empty($sub_category_slug)) {
        // Filter theo danh mục con
        $sql .= " AND c.slug = ?";
        $params[] = $sub_category_slug;
        $types .= "s";
    } else {
        // Filter theo danh mục cha (bao gồm cả danh mục con)
        $sql .= " AND (c.slug = ? OR c.parent_id = (SELECT id FROM category WHERE slug = ?))";
        $params[] = $category_slug;
        $params[] = $category_slug;
        $types .= "ss";
    }
}

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (!empty($min_price)) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "i";
}

if (!empty($max_price)) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "i";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Lấy danh sách danh mục cho filter
$categories_sql = "SELECT * FROM category WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order ASC";
$categories_result = $conn->query($categories_sql);
?>

<div class="products-page">
    <div class="page-header">
        <h1>Sản phẩm</h1>
        <p class="product-count">Tìm thấy <?php echo $result->num_rows; ?> sản phẩm</p>
    </div>

    <div class="products-container">
        <aside class="filters-sidebar">
            <h3>Bộ lọc</h3>
            
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Danh mục</label>
                    <select name="danh_muc" onchange="this.form.submit()">
                        <option value="">Tất cả danh mục</option>
                        <?php
                        if ($categories_result->num_rows > 0) {
                            while($cat = $categories_result->fetch_assoc()) {
                                $selected = ($category_slug == $cat['slug']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($cat['slug']) . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <?php if (!empty($category_slug)): ?>
                    <?php
                    // Lấy danh mục con nếu có danh mục cha được chọn
                    $sub_cats_sql = "SELECT * FROM category WHERE parent_id = (SELECT id FROM category WHERE slug = ?) AND status = 'active' ORDER BY sort_order ASC";
                    $sub_stmt = $conn->prepare($sub_cats_sql);
                    $sub_stmt->bind_param("s", $category_slug);
                    $sub_stmt->execute();
                    $sub_cats_result = $sub_stmt->get_result();
                    
                    if ($sub_cats_result->num_rows > 0): ?>
                        <div class="filter-group">
                            <label>Danh mục con</label>
                            <select name="danh_muc_con" onchange="this.form.submit()">
                                <option value="">Tất cả</option>
                                <?php
                                while($sub_cat = $sub_cats_result->fetch_assoc()) {
                                    $selected = ($sub_category_slug == $sub_cat['slug']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($sub_cat['slug']) . '" ' . $selected . '>' . htmlspecialchars($sub_cat['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    <?php 
                    endif;
                    $sub_stmt->close();
                    ?>
                <?php endif; ?>

                <div class="filter-group">
                    <label>Khoảng giá</label>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="Giá thấp nhất" value="<?php echo htmlspecialchars($min_price); ?>">
                        <input type="number" name="max_price" placeholder="Giá cao nhất" value="<?php echo htmlspecialchars($max_price); ?>">
                    </div>
                </div>

                <button type="submit" class="btn-filter">Áp dụng bộ lọc</button>
                <a href="san-pham.php" class="btn-reset">Xóa bộ lọc</a>
            </form>
        </aside>

        <div class="products-grid">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Xử lý URL hình ảnh
                    $image_url = !empty($row["image_url"]) ? htmlspecialchars($row["image_url"]) : 'images/default-product.jpg';
                    
                    echo '
                    <div class="product-card">
                        <div class="product-image-container">
                            <a href="chi-tiet-san-pham.php?id=' . $row["id"] . '">
                                <img src="' . $image_url . '" 
                                     alt="' . htmlspecialchars($row["name"]) . '" 
                                     class="product-image"
                                     onerror="this.src=\'images/default-product.jpg\'">
                            </a>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="chi-tiet-san-pham.php?id=' . $row["id"] . '">' . htmlspecialchars($row["name"]) . '</a>
                            </h3>
                            <p class="product-category">' . htmlspecialchars($row["category_name"]) . '</p>
                            <p class="product-description">' . htmlspecialchars(substr($row["description"], 0, 100)) . '...</p>
                            <div class="product-price">' . number_format($row["price"], 0, ',', '.') . ' ₫</div>
                            <div class="product-stock">
                                ' . (($row["stock"] > 0) ? '<span style="color: #4CAF50">●</span> Còn ' . $row["stock"] . ' sản phẩm' : '<span style="color: #e74c3c">●</span> Hết hàng') . '
                            </div>
                            <div class="product-actions">
                                <button class="add-to-cart" onclick="addToCart(' . $row["id"] . ')" ' . ($row["stock"] == 0 ? 'disabled' : '') . '>
                                    <i class="fas fa-shopping-cart"></i> ' . ($row["stock"] == 0 ? 'Hết hàng' : 'Thêm giỏ') . '
                                </button>
                                <button class="wishlist" onclick="toggleWishlist(' . $row["id"] . ')">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="no-products">
                    <i class="fas fa-search" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                    <h3>Không tìm thấy sản phẩm nào</h3>
                    <p>Hãy thử điều chỉnh bộ lọc hoặc tìm kiếm với từ khóa khác</p>
                    <a href="san-pham.php" class="btn" style="margin-top: 20px;">Xem tất cả sản phẩm</a>
                </div>';
            }
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>
</div>

<style>
.products-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
    text-align: center;
}

.page-header h1 {
    color: #333;
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.product-count {
    color: #666;
    font-size: 1.1rem;
}

.products-container {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    margin-top: 30px;
}

/* Filters Sidebar */
.filters-sidebar {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    height: fit-content;
    position: sticky;
    top: 20px;
}

.filters-sidebar h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 1.3rem;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.filter-group {
    margin-bottom: 20px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.filter-group select,
.filter-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.price-inputs {
    display: flex;
    gap: 10px;
}

.price-inputs input {
    flex: 1;
}

.btn-filter {
    background: #007bff;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    margin-bottom: 10px;
    transition: background 0.3s;
}

.btn-filter:hover {
    background: #0056b3;
}

.btn-reset {
    display: block;
    text-align: center;
    color: #666;
    text-decoration: none;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: all 0.3s;
}

.btn-reset:hover {
    background: #e9ecef;
    color: #333;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #eee;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
    transition: transform 0.3s;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-info {
    padding: 20px;
}

.product-name {
    margin-bottom: 8px;
}

.product-name a {
    color: #333;
    text-decoration: none;
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.4;
}

.product-name a:hover {
    color: #007bff;
}

.product-category {
    color: #007bff;
    font-size: 0.9rem;
    margin-bottom: 8px;
    font-weight: 500;
}

.product-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 15px;
    height: 40px;
    overflow: hidden;
}

.product-price {
    font-size: 1.3rem;
    font-weight: bold;
    color: #e74c3c;
    margin-bottom: 8px;
}

.product-stock {
    font-size: 0.85rem;
    margin-bottom: 15px;
}

.product-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.add-to-cart {
    flex: 1;
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.add-to-cart:hover:not(:disabled) {
    background: #218838;
}

.add-to-cart:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.wishlist {
    background: #f8f9fa;
    border: 1px solid #ddd;
    padding: 10px 12px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.wishlist:hover {
    background: #e9ecef;
    border-color: #ccc;
}

/* No Products State */
.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-products h3 {
    margin-bottom: 10px;
    color: #333;
}

/* Responsive Design */
@media (max-width: 768px) {
    .products-container {
        grid-template-columns: 1fr;
    }
    
    .filters-sidebar {
        position: static;
        margin-bottom: 20px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .products-page {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .add-to-cart {
        width: 100%;
    }
}
</style>

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