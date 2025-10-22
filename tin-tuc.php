<?php
session_start();
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

$page_title = "Tin tức";
include 'header.php';
?>

<section class="news-section">
    <div class="section-header">
        <h1>Tin tức công nghệ</h1>
        <p>Cập nhật những tin tức mới nhất về công nghệ và sản phẩm</p>
    </div>

    <div class="news-container">
        <!-- Tin tức nổi bật -->
        <div class="featured-news">
            <h2 class="section-title">Tin nổi bật</h2>
            <div class="featured-news-grid">
                <article class="featured-news-card">
                    <div class="news-content">
                        <span class="news-date"><i class="far fa-calendar"></i> 15/12/2024</span>
                        <h3><a href="chi-tiet-tin-tuc.php">Top 10 xu hướng công nghệ sẽ thống trị năm 2024</a></h3>
                        <p class="news-excerpt">Khám phá những công nghệ đột phá sẽ định hình tương lai của chúng ta trong năm tới, từ AI đến điện toán lượng tử. Các chuyên gia dự đoán sự phát triển vượt bậc trong lĩnh vực trí tuệ nhân tạo và học máy.</p>
                        <a href="chi-tiet-tin-tuc.php" class="read-more">Đọc tiếp <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>

                <article class="featured-news-card">
                    <div class="news-content">
                        <span class="news-date"><i class="far fa-calendar"></i> 10/12/2024</span>
                        <h3><a href="chi-tiet-tin-tuc.php">Đánh giá chi tiết iPhone 15 Pro Max: Có đáng để nâng cấp?</a></h3>
                        <p class="news-excerpt">Trải nghiệm thực tế với flagship mới nhất của Apple, những cải tiến đáng giá và điểm hạn chế cần lưu ý. Camera được nâng cấp, hiệu năng vượt trội và thiết kế sang trọng.</p>
                        <a href="chi-tiet-tin-tuc.php" class="read-more">Đọc tiếp <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
        </div>

        <!-- Danh sách tin tức -->
        <div class="news-list">
            <h2 class="section-title">Tin tức mới nhất</h2>
            <div class="news-grid">
                <?php for($i = 1; $i <= 6; $i++): ?>
                <article class="news-card">
                    <div class="news-content">
                        <span class="news-date"><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime('-'.$i.' days')); ?></span>
                        <h3><a href="chi-tiet-tin-tuc.php">Công nghệ AI thay đổi cách chúng ta làm việc và giải trí</a></h3>
                        <p class="news-excerpt">Sự phát triển của trí tuệ nhân tạo đang tạo ra những thay đổi sâu sắc trong mọi lĩnh vực của cuộc sống, từ y tế đến giáo dục và giải trí.</p>
                        <div class="news-meta">
                            <span class="news-category">Công nghệ</span>
                            <span class="news-views"><i class="far fa-eye"></i> 1.2K</span>
                        </div>
                    </div>
                </article>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Phân trang -->
        <div class="pagination">
            <a href="#" class="page-active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#"><i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
</section>

<style>
.news-section {
    padding: 40px 0;
}

.section-header {
    text-align: center;
    margin-bottom: 40px;
}

.section-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 10px;
}

.section-header p {
    color: #666;
    font-size: 1.1rem;
}

.featured-news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.featured-news-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border-left: 4px solid #4CAF50;
}

.featured-news-card:hover {
    transform: translateY(-5px);
}

.news-date {
    color: #888;
    font-size: 0.9rem;
    margin-bottom: 10px;
    display: block;
}

.news-date i {
    margin-right: 5px;
}

.news-content h3 {
    margin-bottom: 15px;
    line-height: 1.4;
}

.news-content h3 a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}

.news-content h3 a:hover {
    color: #4CAF50;
}

.news-excerpt {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
}

.read-more {
    color: #4CAF50;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

.read-more i {
    margin-left: 5px;
    transition: transform 0.3s;
}

.read-more:hover i {
    transform: translateX(3px);
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.news-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-top: 3px solid #4CAF50;
}

.news-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.15);
}

.news-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.news-category {
    background: #4CAF50;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
}

.news-views {
    color: #888;
    font-size: 0.8rem;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 40px;
}

.pagination a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: all 0.3s;
}

.pagination .page-active,
.pagination a:hover {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

.section-title {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #4CAF50;
    display: inline-block;
}
</style>

<?php include 'footer.php'; ?>