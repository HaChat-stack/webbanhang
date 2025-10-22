<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ShopOnline' : 'ShopOnline'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* CSS cho danh mục con */
        .sub-categories {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            border-radius: 0 8px 8px 8px;
            z-index: 1001;
        }

        .dropdown-menu li {
            position: relative;
        }

        .dropdown-menu li:hover .sub-categories {
            display: block;
        }

        .sub-categories a {
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
            font-size: 14px;
        }

        .sub-categories a:hover {
            background: #4CAF50;
            color: white;
        }

        .sub-categories a:last-child {
            border-bottom: none;
        }

        .has-submenu > a::after {
            content: "▸";
            float: right;
            margin-left: 10px;
            color: #666;
        }

        .dropdown-menu li:hover > a::after {
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <i class="fas fa-shopping-bag"></i>
                    <a href="trang-chu.php" style="color: inherit; text-decoration: none;">ShopOnline</a>
                </div>
                <form class="search-bar" action="san-pham.php" method="GET">
                    <input type="text" name="q" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <div class="user-actions">
                    <?php if(isset($_SESSION['username']) && isset($_SESSION['da_dang_nhap']) && $_SESSION['da_dang_nhap'] === true): ?>
                        <span>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="tai-khoan.php"><i class="fas fa-user"></i> Tài khoản</a>
                        <a href="dang-xuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    <?php else: ?>
                        <a href="dangnhap.php"><i class="fas fa-user"></i> Đăng nhập</a>
                        <a href="dangky.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    <?php endif; ?>
                    <a href="gio-hang.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng 
                        <?php 
                        $cart_count = 0;
                        if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            $cart_count = count($_SESSION['cart']);
                        }
                        if($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
        <nav>
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="trang-chu.php">Trang chủ</a></li>
                    <li>
                        <a href="san-pham.php">Sản phẩm <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php
                            require 'ketnoi.php';
                            
                            // Lấy danh mục cha (parent_id = NULL hoặc 0)
                            $sql = "SELECT * FROM category WHERE (parent_id IS NULL OR parent_id = 0) AND status = 'active' ORDER BY sort_order ASC";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $category_id = $row['id'];
                                    $category_name = $row['name'];
                                    $category_slug = $row['slug'];
                                    
                                    echo '<li class="has-submenu">';
                                    echo '<a href="san-pham.php?danh_muc=' . urlencode($category_slug) . '">' . htmlspecialchars($category_name) . '</a>';
                                    
                                    // Lấy danh mục con dựa trên parent_id
                                    $sub_sql = "SELECT * FROM category WHERE parent_id = ? AND status = 'active' ORDER BY sort_order ASC";
                                    $stmt = $conn->prepare($sub_sql);
                                    $stmt->bind_param("i", $category_id);
                                    $stmt->execute();
                                    $sub_result = $stmt->get_result();
                                    
                                    if ($sub_result->num_rows > 0) {
                                        echo '<ul class="sub-categories">';
                                        while($sub_row = $sub_result->fetch_assoc()) {
                                            echo '<li><a href="san-pham.php?danh_muc=' . urlencode($category_slug) . '&danh_muc_con=' . urlencode($sub_row['slug']) . '">' . htmlspecialchars($sub_row['name']) . '</a></li>';
                                        }
                                        echo '</ul>';
                                    }
                                    $stmt->close();
                                    
                                    echo '</li>';
                                }
                            } else {
                                echo '<li><a href="san-pham.php">Tất cả sản phẩm</a></li>';
                            }
                            $conn->close();
                            ?>
                        </ul>
                    </li>
                    <li><a href="khuyen-mai.php">Khuyến mãi</a></li>
                    <li><a href="tin-tuc.php">Tin tức</a></li>
                    <li><a href="lien-he.php">Liên hệ</a></li>
                    <li><a href="themsanpham.php">Thêm sản phẩm</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">