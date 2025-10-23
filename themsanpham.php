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
    
    // Xử lý upload ảnh - PHIÊN BẢN ĐÃ SỬA
    $image_url = "";
    $upload_errors = [];

    if (!$is_writable) {
        $upload_errors[] = "Thư mục upload không có quyền ghi. Vui lòng kiểm tra quyền thư mục.";
    } else if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['product_image'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            // Kiểm tra file thực tế có tồn tại không
            if (!file_exists($file_tmp)) {
                $upload_errors[] = "File tạm thời không tồn tại. Có thể do kích thước file vượt quá giới hạn PHP.";
            } else if (in_array($file_ext, $allowed_ext)) {
                if ($file_size <= 5 * 1024 * 1024) {
                    $new_file_name = uniqid('product_', true) . '.' . $file_ext;
                    $file_destination = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $image_url = $file_destination;
                    } else {
                        $upload_errors[] = "Không thể upload ảnh. Lỗi hệ thống.";
                        // Kiểm tra lỗi cụ thể
                        $upload_error = error_get_last();
                        if ($upload_error) {
                            $upload_errors[] = "Chi tiết lỗi: " . $upload_error['message'];
                        }
                    }
                } else {
                    $upload_errors[] = "File ảnh quá lớn (tối đa 5MB). File của bạn: " . round($file_size/1024/1024, 2) . "MB";
                }
            } else {
                $upload_errors[] = "Định dạng file không được hỗ trợ. Chỉ chấp nhận: " . implode(', ', $allowed_ext);
            }
        } else {
            // Xử lý các lỗi upload khác
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $upload_errors[] = "File ảnh quá lớn (tối đa 5MB). Kiểm tra cấu hình PHP.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $upload_errors[] = "File chỉ được upload một phần";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $upload_errors[] = "Thiếu thư mục tạm";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $upload_errors[] = "Không thể ghi file";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $upload_errors[] = "Upload bị dừng bởi extension PHP";
                    break;
                default:
                    $upload_errors[] = "Lỗi upload file không xác định (Code: " . $file['error'] . ")";
            }
        }
    } else {
        $upload_errors[] = "Vui lòng chọn ảnh sản phẩm";
    }
    
    // Thêm lỗi upload vào danh sách lỗi chung
    if (!empty($upload_errors)) {
        $errors = array_merge($errors, $upload_errors);
    }
    
    // Thêm vào database nếu không có lỗi
    if (empty($errors) && !empty($image_url)) {
        $sql = "INSERT INTO products (name, description, price, category_id, image_url, featured, stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
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
            $error_message = "Lỗi chuẩn bị statement: " . $conn->error;
        }
    } else {
        $all_errors = array_merge($errors, $upload_errors);
        $error_message = implode("<br>", $all_errors);
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
            <i class="fas fa-exclamation-circle"></i> 
            <strong>Lỗi:</strong><br>
            <?php echo $error_message; ?>
            
            <?php if (isset($_FILES['product_image'])): ?>
            <div style="margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 5px; font-size: 12px;">
                <strong>Thông tin Debug:</strong><br>
                File Name: <?php echo $_FILES['product_image']['name'] ?? 'None'; ?><br>
                File Size: <?php echo isset($_FILES['product_image']['size']) ? number_format($_FILES['product_image']['size']) . ' bytes' : '0'; ?><br>
                File Error: <?php echo $_FILES['product_image']['error'] ?? 'Unknown'; ?><br>
                Temp File: <?php echo $_FILES['product_image']['tmp_name'] ?? 'None'; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Thông tin thư mục upload -->
    <div class="alert <?php echo $is_writable ? 'alert-success' : 'alert-error'; ?>">
        <i class="fas fa-<?php echo $is_writable ? 'check' : 'exclamation'; ?>-circle"></i> 
        <strong>Thông tin Upload:</strong><br>
        Thư mục: <code><?php echo realpath($upload_dir) ?: $upload_dir; ?></code><br>
        Có thể ghi: <?php echo $is_writable ? 'Có ✓' : 'Không ✗'; ?>
        <?php if (!$is_writable): ?>
            <br><small>Vui lòng kiểm tra quyền thư mục: <code>chmod 755 product_images/</code></small>
        <?php endif; ?>
    </div>
    
    <div class="form-container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" id="productForm">
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
                
                <!-- Input file thật (ẩn đi) -->
                <input type="file" id="product_image" name="product_image" accept="image/*" required 
                       style="display: none;" onchange="handleFileSelect(this)">
                
                <!-- Nút thay thế - có thể click được -->
                <div class="file-input-wrapper">
                    <button type="button" class="file-select-btn" onclick="openFilePicker()">
                        <i class="fas fa-folder-open"></i> Chọn tệp ảnh
                    </button>
                    <span class="file-name" id="fileName">Chưa chọn tệp nào</span>
                </div>
                
                <small class="file-info">Định dạng: JPG, PNG, GIF, WEBP (Tối đa 5MB)</small>
                
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
                <button type="reset" class="btn btn-secondary" onclick="resetFileInput()">Làm mới</button>
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

/* CSS cho file input replacement */
.file-input-wrapper {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-bottom: 10px;
}

.file-select-btn {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-size: 14px;
}

.file-select-btn:hover {
    background: linear-gradient(135deg, #45a049, #4CAF50);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.file-select-btn:active {
    transform: translateY(0);
}

.file-name {
    color: #666;
    font-style: italic;
    flex: 1;
    font-size: 14px;
}

.file-info {
    display: block;
    margin-top: 5px;
    color: #888;
    font-size: 12px;
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
    object-fit: contain;
}

.image-preview-text {
    color: #999;
    font-size: 0.9rem;
}

.image-preview-text i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
    color: #ccc;
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
    
    .file-input-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
function openFilePicker() {
    document.getElementById('product_image').click();
}

function handleFileSelect(input) {
    const fileName = document.getElementById('fileName');
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    
    console.log('File input changed:', input.files);
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        console.log('File details:', {
            name: file.name,
            size: file.size,
            type: file.type
        });
        
        // Hiển thị tên file
        fileName.textContent = file.name;
        fileName.style.color = '#333';
        fileName.style.fontStyle = 'normal';
        
        // Kiểm tra kích thước file
        if (file.size > 5 * 1024 * 1024) {
            alert('File quá lớn! Tối đa 5MB. File của bạn: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
            resetFileInput();
            return;
        }
        
        // Kiểm tra định dạng
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
            alert('Định dạng file không được hỗ trợ! Chỉ chấp nhận: JPG, PNG, GIF, WEBP.');
            resetFileInput();
            return;
        }
        
        // Hiển thị preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.add('has-image');
            console.log('Preview loaded successfully');
        }
        reader.onerror = function(e) {
            console.error('Error reading file:', e);
            alert('Lỗi đọc file! Vui lòng thử file khác.');
            resetFileInput();
        }
        reader.readAsDataURL(file);
    } else {
        console.log('No file selected');
        resetFileInput();
    }
}

function resetFileInput() {
    const fileInput = document.getElementById('product_image');
    const fileName = document.getElementById('fileName');
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    
    fileInput.value = '';
    fileName.textContent = 'Chưa chọn tệp nào';
    fileName.style.color = '#666';
    fileName.style.fontStyle = 'italic';
    previewImg.src = '';
    preview.classList.remove('has-image');
}

// Giữ hàm cũ để tương thích
function previewImage(input) {
    handleFileSelect(input);
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - file input system ready');
});
</script>

<?php 
// Không cần đóng kết nối, vì PHP tự động đóng
include 'footer.php'; 
?>