-- Tạo database
CREATE DATABASE IF NOT EXISTS shoponline 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE shoponline;

-- Bảng người dùng (đổi tên từ users thành nguoidung để phù hợp với code PHP)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng danh mục
CREATE TABLE category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    slug VARCHAR(255) UNIQUE,
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES category(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng sản phẩm
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price INT NOT NULL,
    category_id INT NOT NULL,
    image_url VARCHAR(500),
    featured TINYINT(1) DEFAULT 0,
    stock INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES category(id)
);

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    notes TEXT,
    payment_method VARCHAR(20) NOT NULL,
    total_amount INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Chèn dữ liệu mẫu cho danh mục
INSERT INTO category (name, description, slug, sort_order) VALUES
('Điện thoại', 'Các loại điện thoại thông minh', 'dien-thoai', 1),
('Máy tính bảng', 'Máy tính bảng các hãng', 'may-tinh-bang', 2),
('Laptop', 'Máy tính xách tay', 'laptop', 3),
('Phụ kiện', 'Phụ kiện điện tử', 'phu-kien', 4);

-- Chèn danh mục con
INSERT INTO category (name, description, slug, parent_id, sort_order) VALUES
('iPhone', 'Điện thoại iPhone', 'iphone', 1, 1),
('Samsung', 'Điện thoại Samsung', 'samsung', 1, 2),
('Xiaomi', 'Điện thoại Xiaomi', 'xiaomi', 1, 3),
('Oppo', 'Điện thoại Oppo', 'oppo', 1, 4),
('iPad', 'Máy tính bảng iPad', 'ipad', 2, 1),
('Samsung Tablet', 'Máy tính bảng Samsung', 'samsung-tablet', 2, 2),
('MacBook', 'Laptop Apple', 'macbook', 3, 1),
('Dell', 'Laptop Dell', 'dell', 3, 2),
('HP', 'Laptop HP', 'hp', 3, 3),
('Tai nghe', 'Tai nghe các loại', 'tai-nghe', 4, 1),
('Sạc dự phòng', 'Pin sạc dự phòng', 'sac-du-phong', 4, 2),
('Ốp lưng', 'Ốp lưng điện thoại', 'op-lung', 4, 3);

-- Chèn dữ liệu mẫu cho sản phẩm
INSERT INTO products (name, description, price, category_id, image_url, featured, stock) VALUES
('iPhone 15 Pro Max', 'iPhone 15 Pro Max 256GB, chip A17 Pro, camera 48MP', 28990000, 5, 'product_images/iphone15.jpg', 1, 50),
('Samsung Galaxy S24 Ultra', 'Samsung Galaxy S24 Ultra 512GB, bút S-Pen, camera 200MP', 24990000, 6, 'product_images/samsung_s24.jpg', 1, 45),
('Xiaomi Redmi Note 13', 'Xiaomi Redmi Note 13 128GB, camera 108MP, pin 5000mAh', 5990000, 7, 'product_images/xiaomi_note13.jpg', 0, 100),
('MacBook Pro 16 inch', 'MacBook Pro 16 inch M3 Pro, 18GB RAM, 512GB SSD', 55990000, 11, 'product_images/macbook_pro.jpg', 1, 20),
('Dell XPS 13', 'Dell XPS 13, Intel Core i7, 16GB RAM, 512GB SSD', 32990000, 12, 'product_images/dell_xps.jpg', 0, 30),
('AirPods Pro 2', 'Tai nghe AirPods Pro 2, chống ồn chủ động', 6990000, 13, 'product_images/airpods_pro.jpg', 1, 80),
('Sạc dự phòng 10000mAh', 'Sạc dự phòng Xiaomi 10000mAh, hỗ trợ sạc nhanh', 590000, 14, 'product_images/sac_du_phong.jpg', 0, 150);

-- Tạo indexes để tối ưu hiệu suất
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_products_created_at ON products(created_at);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);
CREATE INDEX idx_category_parent_id ON category(parent_id);
CREATE INDEX idx_category_status ON category(status);

-- Tạo user mẫu (mật khẩu: 123456 - đã được mã hóa)
INSERT INTO users (username, email, password, fullname, phone, address) VALUES 
('admin', 'admin@shoponline.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', '0123456789', 'Hà Nội'),
('user1', 'user1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Người dùng 1', '0987654321', 'TP HCM');