<?php
include 'db.php';
session_start();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Sử dụng Prepared Statements để tránh SQL injection
$stmt = $conn->prepare("SELECT * FROM product WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit;
}

// Kiểm tra xem đường dẫn ảnh là URL đầy đủ hoặc đường dẫn tương đối
$image_url = (strpos($product['image'], 'http') === 0) ? $product['image'] : $product['image'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .product-img {
            max-height: 400px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Trang Chủ</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">Giỏ Hàng</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if($_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="admin/dashboard.php">Quản trị</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($image_url) ?>" class="img-fluid product-img rounded" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p class="text-danger fw-bold">Giá: <?= number_format($product['price'], 0, ',', '.') ?> VNĐ</p>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <div class="mb-3">
                <label for="quantity" class="form-label">Số lượng:</label>
                <input type="number" style="width: 100px;" id="quantity" class="form-control" value="1" min="1">
            </div>
            <button class="btn btn-success add-to-cart" data-id="<?= $product['id'] ?>">Thêm vào giỏ hàng</button>
            <a href="index.php" class="btn btn-secondary">Quay lại danh sách</a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 My Shop. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelector('.add-to-cart').addEventListener('click', function() {
        let productId = this.getAttribute('data-id');
        let quantity = document.getElementById('quantity').value;
        if (quantity < 1) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Số lượng phải lớn hơn 0.',
            });
            return;
        }
        fetch('cart.php?action=add&id=' + productId + '&quantity=' + quantity)
            .then(response => {
                if (response.status === 401) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!',
                        confirmButtonText: 'Đăng nhập ngay',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                    return Promise.reject('Unauthorized');
                }
                return response.text();
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Sản phẩm đã được thêm vào giỏ hàng.',
                    showConfirmButton: false,
                    timer: 2000
                });
            })
            .catch(error => {
                if (error !== 'Unauthorized') {
                    console.error('Error:', error);
                }
            });
    });
</script>
</body>
</html>