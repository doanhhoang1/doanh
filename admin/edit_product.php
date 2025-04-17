<?php
session_start();
include '../db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: products.php?error=ID sản phẩm không hợp lệ');
    exit;
}

$upload_dir = '../assets/uploads/products/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$stmt = $conn->prepare("SELECT * FROM product WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php?error=Không tìm thấy sản phẩm');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $current_image = $product['image'];  
    
    $image_path = $current_image;  
    $upload_error = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($file_ext, $allowed_ext)) {
            $upload_error = "Chỉ cho phép upload các file ảnh có định dạng: " . implode(', ', $allowed_ext);
        }
        
        else if ($file_size > 5242880) {
            $upload_error = "Kích thước file không được vượt quá 5MB";
        } 
        else {
            $new_file_name = uniqid() . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $image_path = 'assets/uploads/products/' . $new_file_name;
                
                if (strpos($current_image, 'assets/uploads/products/') === 0) {
                    $old_image_path = '../' . $current_image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            } else {
                $upload_error = "Đã xảy ra lỗi khi upload file. Vui lòng thử lại!";
            }
        }
    } else if ($_FILES['image']['error'] > 0 && $_FILES['image']['error'] != 4) {
        $upload_error = "Đã xảy ra lỗi khi upload file. Mã lỗi: " . $_FILES['image']['error'];
    }

    // Validate input
    if (empty($name) || $price <= 0 || empty($description)) {
        $error = "Vui lòng điền đầy đủ thông tin và giá phải lớn hơn 0!";
    } else if (!empty($upload_error)) {
        $error = $upload_error;
    } else {
        // Update product in database
        $stmt = $conn->prepare("UPDATE product SET name = :name, image = :image, price = :price, description = :description WHERE id = :id");
        $result = $stmt->execute([
            'name' => $name,
            'image' => $image_path,
            'price' => $price,
            'description' => $description,
            'id' => $id
        ]);

        if ($result) {
            header('Location: products.php?message=Cập nhật sản phẩm thành công!');
            exit;
        } else {
            $error = "Đã xảy ra lỗi khi cập nhật sản phẩm. Vui lòng thử lại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chỉnh sửa sản phẩm</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        #image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Quản trị viên</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Bảng điều khiển</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Quản lý sản phẩm</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../index.php" target="_blank">Xem trang chủ</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa sản phẩm</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Để giữ nguyên ảnh cũ, hãy bỏ qua trường này. Chọn ảnh mới để thay đổi.</div>
                            <div class="mt-2">
                                <p>Hình ảnh hiện tại:</p>
                                <img id="current-image" src="../<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-thumbnail" style="max-height: 150px;">
                                <img id="image-preview" class="img-thumbnail" src="#" alt="Xem trước ảnh" style="display: none;">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                                <span class="input-group-text">VNĐ</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                        
                        <div class="text-end">
                            <a href="products.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 Hệ thống quản lý sản phẩm</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Hiển thị xem trước ảnh khi chọn file
    document.getElementById('image').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        const currentImage = document.getElementById('current-image');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                currentImage.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            currentImage.style.display = 'block';
        }
    });
</script>
</body>
</html>