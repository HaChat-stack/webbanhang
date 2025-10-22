<?php
session_start();
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

$page_title = "Liên hệ";
include 'header.php';

// Xử lý form liên hệ
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Kiểm tra dữ liệu
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Email không hợp lệ';
    } else {
        // Lưu vào database (giả lập)
        $success_message = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
        
        // Ở đây bạn có thể thêm code để lưu vào database
        // hoặc gửi email thông báo
    }
}
?>

<section class="contact-section">
    <div class="section-header">
        <h1>Liên hệ với chúng tôi</h1>
        <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
    </div>

    <div class="contact-container">
        <!-- Thông tin liên hệ -->
        <div class="contact-info">
            <h2>Thông tin liên hệ</h2>
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="contact-details">
                    <h3>Địa chỉ</h3>
                    <p>Đỗ Minh Hà, Quận Hai Bà Trưng, Hà Nội</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="contact-details">
                    <h3>Điện thoại</h3>
                    <p>+84 28 1234 5678</p>
                    <p>+84 90 123 4567 (Hotline)</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-details">
                    <h3>Email</h3>
                    <p>support@shoponline.com</p>
                    <p>sales@shoponline.com</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="contact-details">
                    <h3>Giờ làm việc</h3>
                    <p>Thứ 2 - Thứ 6: 8:00 - 18:00</p>
                    <p>Thứ 7: 8:00 - 12:00</p>
                    <p>Chủ nhật: Nghỉ</p>
                </div>
            </div>

            <div class="social-links">
                <h3>Theo dõi chúng tôi</h3>
                <div class="social-icons">
                    <a href="#" class="social-link facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link youtube"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-link tiktok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>

        <!-- Form liên hệ -->
        <div class="contact-form">
            <h2>Gửi tin nhắn</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Họ tên <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="subject">Chủ đề <span class="required">*</span></label>
                        <select id="subject" name="subject" required>
                            <option value="">Chọn chủ đề</option>
                            <option value="support" <?php echo ($_POST['subject'] ?? '') === 'support' ? 'selected' : ''; ?>>Hỗ trợ kỹ thuật</option>
                            <option value="sales" <?php echo ($_POST['subject'] ?? '') === 'sales' ? 'selected' : ''; ?>>Tư vấn mua hàng</option>
                            <option value="warranty" <?php echo ($_POST['subject'] ?? '') === 'warranty' ? 'selected' : ''; ?>>Bảo hành</option>
                            <option value="complaint" <?php echo ($_POST['subject'] ?? '') === 'complaint' ? 'selected' : ''; ?>>Khiếu nại</option>
                            <option value="other" <?php echo ($_POST['subject'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Nội dung tin nhắn <span class="required">*</span></label>
                    <textarea id="message" name="message" rows="6" placeholder="Vui lòng mô tả chi tiết vấn đề của bạn..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Gửi tin nhắn
                </button>
            </form>
        </div>
    </div>

    <!-- Bản đồ -->
    <div class="map-section">
        <h2>Vị trí cửa hàng</h2>
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.395355662295!2d106.70541731533437!3d10.782837992319925!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f40a3b49e59%3A0xa1bd14e483a602db!2sLandmark%2081!5e0!3m2!1svi!2s!4v1602740384256!5m2!1svi!2s" 
                width="100%" 
                height="400" 
                style="border:0; border-radius: 10px;" 
                allowfullscreen="" 
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<style>
.contact-section {
    padding: 40px 0;
}

.contact-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-bottom: 50px;
}

.contact-info h2,
.contact-form h2 {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #4CAF50;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.contact-item:hover {
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.contact-icon {
    background: #4CAF50;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
}

.contact-icon i {
    font-size: 1.2rem;
}

.contact-details h3 {
    color: #333;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.contact-details p {
    color: #666;
    margin-bottom: 5px;
    line-height: 1.5;
}

.social-links {
    margin-top: 30px;
}

.social-links h3 {
    margin-bottom: 15px;
    color: #333;
}

.social-icons {
    display: flex;
    gap: 10px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.social-link.facebook { background: #3b5998; }
.social-link.twitter { background: #1da1f2; }
.social-link.instagram { background: #e4405f; }
.social-link.youtube { background: #cd201f; }
.social-link.tiktok { background: #000000; }

.contact-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.required {
    color: #e74c3c;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.btn-submit {
    background: #4CAF50;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-submit:hover {
    background: #45a049;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.map-section {
    margin-top: 50px;
}

.map-section h2 {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 25px;
    text-align: center;
}

.map-container {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .contact-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .contact-item {
        flex-direction: column;
        text-align: center;
    }
    
    .contact-icon {
        margin-right: 0;
        margin-bottom: 15px;
    }
}
</style>

<?php include 'footer.php'; ?>