<?php
include 'db.php';
session_start();

// Cấu hình phân trang
$limit = 8; // Số sản phẩm mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Tổng số sản phẩm
$total = $conn->query("SELECT COUNT(*) FROM product")->fetchColumn();
$pages = ceil($total / $limit);

// Lấy sản phẩm theo trang
$stmt = $conn->prepare("SELECT * FROM product LIMIT :start, :limit");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card img {
            height: 250px;
            object-fit: cover;
        }
        .card:hover {
            transform: scale(1.05);
            transition: 0.3s;
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

<!-- Danh sách sản phẩm -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Danh sách sản phẩm</h2>
    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php foreach ($products as $product): ?>
        <div class="col">
            <div class="card h-100 shadow-sm">
                <?php
                $image_url = (strpos($product['image'], 'http') === 0) ? $product['image'] : $product['image'];
                ?>
                <img src="<?= htmlspecialchars($image_url) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="card-body">
                    <h5 class="card-title"> <?= htmlspecialchars($product['name']) ?> </h5>
                    <p class="card-text text-danger fw-bold"> <?= number_format($product['price'], 0, ',', '.') ?> VNĐ </p>
                    <a href="detail.php?id=<?= $product['id'] ?>" class="btn btn-dark w-100">Chi tiết</a>
                    <button class="btn btn-success add-to-cart mt-1 w-100" data-id="<?= $product['id'] ?>">Thêm vào giỏ hàng</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Phân trang -->
    <div class="mt-4 d-flex justify-content-center">
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 My Shop. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            let productId = this.getAttribute('data-id');
            fetch('cart.php?action=add&id=' + productId)
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
    });
</script>
</body>
</html>
