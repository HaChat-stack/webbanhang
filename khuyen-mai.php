<?php
session_start();
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

$page_title = "Khuyến mãi";
include 'header.php';
?>

<section class="promotions-section">
    <div class="section-header">
        <h1>Khuyến mãi hấp dẫn</h1>
        <p>Ưu đãi đặc biệt dành riêng cho bạn</p>
    </div>

    <!-- Banner khuyến mãi lớn -->
    <div class="promo-banner">
        <div class="promo-content">
            <span class="promo-badge">GIẢM GIÁ</span>
            <h2>SIÊU SALE CUỐI NĂM</h2>
            <p class="promo-description">Giảm đến 50% cho tất cả sản phẩm công nghệ</p>
            <div class="promo-timer">
                <div class="timer-item">
                    <span id="days">00</span>
                    <small>Ngày</small>
                </div>
                <div class="timer-item">
                    <span id="hours">00</span>
                    <small>Giờ</small>
                </div>
                <div class="timer-item">
                    <span id="minutes">00</span>
                    <small>Phút</small>
                </div>
                <div class="timer-item">
                    <span id="seconds">00</span>
                    <small>Giây</small>
                </div>
            </div>
            <a href="san-pham.php?khuyen_mai=1" class="btn btn-promo">Mua ngay <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>

    <!-- Danh sách khuyến mãi -->
    <div class="promotions-grid">
        <?php for($i = 1; $i <= 4; $i++): ?>
        <div class="promotion-card">
            <div class="promo-header">
                <span class="discount-badge">-<?php echo 20 + ($i * 5); ?>%</span>
                <span class="promo-time">Còn <?php echo 3 + $i; ?> ngày</span>
            </div>
            <div class="promo-content">
                <h3>Ưu đãi đặc biệt tháng <?php echo date('m'); ?></h3>
                <p class="promo-desc">Giảm <?php echo 20 + ($i * 5); ?>% cho sản phẩm được chọn</p>
                <ul class="promo-features">
                    <li><i class="fas fa-check"></i> Áp dụng cho đơn hàng từ 2 triệu</li>
                    <li><i class="fas fa-check"></i> Miễn phí vận chuyển</li>
                    <li><i class="fas fa-check"></i> Tặng kèm phụ kiện</li>
                </ul>
                <div class="promo-actions">
                    <a href="san-pham.php?khuyen_mai=<?php echo $i; ?>" class="btn-promo-small">Xem ngay</a>
                    <span class="promo-code">Mã: SALE<?php echo 20 + ($i * 5); ?></span>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Sản phẩm đang khuyến mãi -->
    <div class="promo-products">
        <h2 class="section-title">Sản phẩm đang giảm giá</h2>
        <div class="product-grid">
            <?php
            require 'ketnoi.php';
            $sql = "SELECT p.id, p.name, p.price, p.discount_price 
                    FROM products p 
                    WHERE p.discount_price IS NOT NULL AND p.discount_price < p.price 
                    LIMIT 4";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $discount_percent = round(($row["price"] - $row["discount_price"]) / $row["price"] * 100);
                    echo '
                    <div class="product-card promo-product">
                        <div class="discount-flag">-' . $discount_percent . '%</div>
                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="chi-tiet-san-pham.php?id=' . $row["id"] . '">' . htmlspecialchars($row["name"]) . '</a>
                            </h3>
                            <div class="price-container">
                                <span class="original-price">' . number_format($row["price"], 0, ',', '.') . ' ₫</span>
                                <span class="discount-price">' . number_format($row["discount_price"], 0, ',', '.') . ' ₫</span>
                            </div>
                            <button class="add-to-cart" onclick="addToCart(' . $row["id"] . ')">
                                <i class="fas fa-shopping-cart"></i> Thêm giỏ
                            </button>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="no-promo">Hiện không có sản phẩm khuyến mãi</p>';
            }
            $conn->close();
            ?>
        </div>
    </div>
</section>

<style>
.promotions-section {
    padding: 40px 0;
}

.promo-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 40px;
    margin-bottom: 50px;
    color: white;
    position: relative;
    overflow: hidden;
}

.promo-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: rgba(255,255,255,0.1);
    transform: rotate(45deg);
}

.promo-content {
    position: relative;
    z-index: 2;
}

.promo-badge {
    background: #ff4757;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 15px;
    display: inline-block;
}

.promo-content h2 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.promo-description {
    font-size: 1.2rem;
    margin-bottom: 25px;
    opacity: 0.9;
}

.promo-timer {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.timer-item {
    background: rgba(255,255,255,0.2);
    padding: 10px 15px;
    border-radius: 8px;
    text-align: center;
    backdrop-filter: blur(10px);
}

.timer-item span {
    font-size: 1.5rem;
    font-weight: 700;
    display: block;
}

.timer-item small {
    font-size: 0.8rem;
    opacity: 0.8;
}

.btn-promo {
    background: white;
    color: #667eea;
    padding: 12px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-promo:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.promotions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
}

.promotion-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border-top: 4px solid #4CAF50;
}

.promotion-card:hover {
    transform: translateY(-5px);
}

.promo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.discount-badge {
    background: #ff4757;
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: 600;
}

.promo-time {
    color: #666;
    font-size: 0.8rem;
}

.promo-content h3 {
    margin-bottom: 10px;
    color: #333;
}

.promo-desc {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}

.promo-features {
    list-style: none;
    margin-bottom: 20px;
}

.promo-features li {
    margin-bottom: 8px;
    color: #555;
    font-size: 0.9rem;
}

.promo-features i {
    color: #4CAF50;
    margin-right: 8px;
}

.promo-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-promo-small {
    background: #4CAF50;
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.btn-promo-small:hover {
    background: #45a049;
    transform: translateY(-2px);
}

.promo-code {
    background: #f8f9fa;
    color: #666;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-family: monospace;
}

.promo-products {
    margin-top: 50px;
}

.promo-product {
    position: relative;
}

.discount-flag {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ff4757;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
}

.price-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}

.original-price {
    color: #999;
    text-decoration: line-through;
    font-size: 0.9rem;
}

.discount-price {
    color: #ff4757;
    font-weight: 600;
    font-size: 1.1rem;
}

.no-promo {
    text-align: center;
    color: #666;
    font-size: 1.1rem;
    grid-column: 1 / -1;
    padding: 40px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.15);
}
</style>

<script>
// Đếm ngược thời gian khuyến mãi
function updateCountdown() {
    const targetDate = new Date('2024-12-31T23:59:59').getTime();
    const now = new Date().getTime();
    const difference = targetDate - now;

    const days = Math.floor(difference / (1000 * 60 * 60 * 24));
    const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((difference % (1000 * 60)) / 1000);

    document.getElementById('days').textContent = days.toString().padStart(2, '0');
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
}

setInterval(updateCountdown, 1000);
updateCountdown();
</script>

<?php include 'footer.php'; ?>