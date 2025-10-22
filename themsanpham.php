<?php
session_start();
if (!isset($_SESSION['da_dang_nhap']) || $_SESSION['da_dang_nhap'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

require 'ketnoi.php';

// Khởi tạo biến
$name = $description = $price = $category_id = $stock = "";
$featured = 0;
$success_message = "";
$error_message = "";

// Sử dụng thư mục mới - product_images
$upload_dir = "product_images/";

// Đảm bảo thư mục tồn tại và có quyền ghi
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$is_writable = is_dir($upload_dir) && is_writable($upload_dir);

// Lấy danh sách danh mục từ CSDL
$categories = [];
$category_result = $conn->query("SELECT id, name, parent_id FROM category WHERE status = 'active' ORDER BY sort_order ASC");
if ($category_result->num_rows > 0) {
    while($cat = $category_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// Xử lý form khi submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category_id = trim($_POST['category_id']);
    $stock = trim($_POST['stock']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Kiểm tra dữ liệu
    $errors = [];
    
    if (empty($name)) $errors[] = "Tên sản phẩm là bắt buộc";
    if (empty($description)) $errors[] = "Mô tả sản phẩm là bắt buộc";
    if (empty($price) || !is_numeric($price) || $price < 0) $errors[] = "Giá sản phẩm phải là số và lớn hơn 0";
    if (empty($category_id)) $errors[] = "Danh mục sản phẩm là bắt buộc";
    if (empty($stock) || !is_numeric($stock) || $stock < 0) $errors[] = "Số lượng tồn kho phải là số và lớn hơn 0";
    
    // Xử lý upload ảnh
    $image_url = "";
    
    if (!$is_writable) {
        $errors[] = "Thư mục upload không có quyền ghi. Vui lòng kiểm tra quyền thư mục.";
    } else if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size <= 5 * 1024 * 1024) {
                $new_file_name = uniqid('product_', true) . '.' . $file_ext;
                $file_destination = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $file_destination)) {
                    $image_url = $file_destination;
                } else {
                    $errors[] = "Không thể upload ảnh. Vui lòng thử lại.";
                }
            } else {
                $errors[] = "File ảnh quá lớn (tối đa 5MB)";
            }
        } else {
            $errors[] = "Định dạng file không được hỗ trợ";
        }
    } else {
        if (isset($_FILES['product_image'])) {
            switch ($_FILES['product_image']['error']) {
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = "Vui lòng chọn ảnh sản phẩm";
                    break;
                default:
                    $errors[] = "Lỗi upload file (Code: " . $_FILES['product_image']['error'] . ")";
            }
        } else {
            $errors[] = "Không có file được chọn";
        }
    }
    
    // Thêm vào database nếu không có lỗi
    if (empty($errors) && !empty($image_url)) {
        $sql = "INSERT INTO products (name, description, price, category_id, image_url, featured, stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdissi", $name, $description, $price, $category_id, $image_url, $featured, $stock);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            $success_message = "Sản phẩm đã được thêm thành công! Mã sản phẩm: #" . $product_id;
            // Reset form
            $name = $description = $price = $category_id = $stock = "";
            $featured = 0;
        } else {
            $error_message = "Lỗi database: " . $conn->error;
            // Xóa file ảnh nếu insert thất bại
            if (file_exists($image_url)) {
                unlink($image_url);
            }
        }
        $stmt->close();
    } else {
        $error_message = implode("<br>", $errors);
    }
}

$page_title = "Thêm Sản Phẩm";
include 'header.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="trang-chu.php">Trang chủ</a> &gt;
        <a href="san-pham.php">Sản phẩm</a> &gt;
        <span>Thêm sản phẩm</span>
    </div>

    <h1 class="section-title">Thêm Sản Phẩm Mới</h1>
    
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Thông tin thư mục upload -->
    <div class="alert <?php echo $is_writable ? 'alert-success' : 'alert-error'; ?>">
        <i class="fas fa-<?php echo $is_writable ? 'check' : 'exclamation'; ?>-circle"></i> 
        <strong>Thông tin Upload:</strong><br>
        Thư mục: <code><?php echo $upload_dir; ?></code><br>
        Có thể ghi: <?php echo $is_writable ? 'Có ✓' : 'Không ✗'; ?>
        <?php if (!$is_writable): ?>
            <br><small>Vui lòng chạy lệnh: <code>sudo chown daemon:daemon product_images/</code></small>
        <?php endif; ?>
    </div>
    
    <div class="form-container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-col">
                    <div class="nhap-lieu">
                        <label for="name" class="required">Tên sản phẩm</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="nhap-lieu">
                        <label for="price" class="required">Giá (VNĐ)</label>
                        <input type="number" id="price" name="price" min="0" step="1000" value="<?php echo htmlspecialchars($price); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="nhap-lieu">
                        <label for="category_id" class="required">Danh mục</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            <?php
                            // Hiển thị danh mục theo cấu trúc phân cấp
                            $main_categories = array_filter($categories, function($cat) {
                                return $cat['parent_id'] === null;
                            });
                            
                            foreach ($main_categories as $main_cat):
                                $sub_categories = array_filter($categories, function($cat) use ($main_cat) {
                                    return $cat['parent_id'] == $main_cat['id'];
                                });
                            ?>
                                <optgroup label="<?php echo htmlspecialchars($main_cat['name']); ?>">
                                    <option value="<?php echo $main_cat['id']; ?>" <?php echo ($category_id == $main_cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($main_cat['name']); ?> (Tất cả)
                                    </option>
                                    <?php foreach ($sub_categories as $sub_cat): ?>
                                        <option value="<?php echo $sub_cat['id']; ?>" <?php echo ($category_id == $sub_cat['id']) ? 'selected' : ''; ?>>
                                            &nbsp;&nbsp;└─ <?php echo htmlspecialchars($sub_cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="nhap-lieu">
                        <label for="stock" class="required">Số lượng tồn kho</label>
                        <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($stock ?: '100'); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="nhap-lieu">
                <label for="description" class="required">Mô tả sản phẩm</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            
            <div class="nhap-lieu">
                <label for="product_image" class="required">Ảnh sản phẩm</label>
                <input type="file" id="product_image" name="product_image" accept="image/*" required 
                       onchange="previewImage(this)" <?php echo !$is_writable ? 'disabled' : ''; ?>>
                <div class="image-preview" id="imagePreview">
                    <img src="" alt="Preview">
                    <div class="image-preview-text">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Preview sẽ hiển thị ở đây</span>
                    </div>
                </div>
            </div>
            
            <div class="nhap-lieu">
                <div class="checkbox-group">
                    <input type="checkbox" id="featured" name="featured" value="1" <?php echo ($featured == 1) ? 'checked' : ''; ?>>
                    <label for="featured">Sản phẩm nổi bật</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Làm mới</button>
                <button type="submit" class="btn btn-success" <?php echo !$is_writable ? 'disabled' : ''; ?>>Thêm sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    padding: 40px;
    margin: 30px 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 0;
}

.form-col {
    display: flex;
    flex-direction: column;
}

.nhap-lieu {
    margin-bottom: 25px;
}

.nhap-lieu label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #333;
    font-size: 1rem;
}

.nhap-lieu input,
.nhap-lieu select,
.nhap-lieu textarea {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s;
}

.nhap-lieu input:focus,
.nhap-lieu select:focus,
.nhap-lieu textarea:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.nhap-lieu textarea {
    min-height: 150px;
    resize: vertical;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.checkbox-group input {
    width: auto;
    transform: scale(1.2);
}

.checkbox-group label {
    margin-bottom: 0;
    font-weight: 500;
    color: #333;
}

.required::after {
    content: " *";
    color: #e74c3c;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 15px 30px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-success {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #45a049, #4CAF50);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.image-preview {
    margin-top: 15px;
    max-width: 300px;
    border: 2px dashed #ddd;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    text-align: center;
    background: #fafafa;
}

.image-preview img {
    max-width: 100%;
    max-height: 250px;
    border-radius: 5px;
    display: none;
}

.image-preview-text {
    color: #999;
    font-size: 0.9rem;
}

.image-preview-text i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
}

.image-preview.has-image img {
    display: block;
}

.image-preview.has-image .image-preview-text {
    display: none;
}

.alert {
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 500;
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

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

optgroup {
    font-weight: bold;
    font-size: 14px;
}

optgroup option {
    font-weight: normal;
    font-size: 13px;
    padding-left: 10px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .form-container {
        padding: 25px;
        margin: 20px 0;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .image-preview {
        max-width: 100%;
    }
}
</style>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.add('has-image');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewImg.src = '';
        preview.classList.remove('has-image');
    }
}
</script>

<?php 
// Không cần đóng kết nối, vì PHP tự động đóng
include 'footer.php'; 
?>