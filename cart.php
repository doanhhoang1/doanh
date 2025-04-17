<?php
session_start();
include 'db.php';

// Kiểm tra đăng nhập khi thực hiện các hành động với giỏ hàng
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';
$quantity = intval($_GET['quantity'] ?? 1);

// Kiểm tra đăng nhập cho các hành động thêm/xóa/cập nhật giỏ hàng
if ($action && !isset($_SESSION['user_id'])) {
    echo "Vui lòng đăng nhập để thực hiện chức năng này!";
    http_response_code(401); // Unauthorized
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($action == 'add' && $id) {
    $_SESSION['cart'][$id] = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id] + $quantity : max(1, $quantity);
    echo "Thêm vào giỏ hàng thành công!";
    exit;
} elseif ($action == 'remove' && $id) {
    unset($_SESSION['cart'][$id]);
    echo "Đã xóa sản phẩm khỏi giỏ hàng!";
    exit;
} elseif ($action == 'update' && $id) {
    if ($quantity > 0) {
        $_SESSION['cart'][$id] = $quantity;
        echo "Cập nhật số lượng thành công!";
    } else {
        unset($_SESSION['cart'][$id]);
        echo "Sản phẩm đã được xóa khỏi giỏ hàng!";
    }
    exit;
}

$cartItems = [];
$total = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    $stmt = $conn->prepare("SELECT * FROM product WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        $product['quantity'] = $qty;
        $product['subtotal'] = $product['price'] * $qty;
        $total += $product['subtotal'];
        $cartItems[] = $product;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Trang Chủ</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="cart.php">Giỏ Hàng</a>
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
    <h2 class="text-center">Giỏ Hàng</h2>
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        <div class="alert alert-warning text-center">
            Vui lòng <a href="login.php" class="alert-link">đăng nhập</a> để sử dụng giỏ hàng.
        </div>
    <?php elseif(empty($cartItems)): ?>
        <div class="alert alert-info text-center">
            Giỏ hàng của bạn đang trống. <a href="index.php" class="alert-link">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tổng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                <?php
                // Kiểm tra xem đường dẫn ảnh là URL đầy đủ hoặc đường dẫn tương đối
                $image_url = (strpos($item['image'], 'http') === 0) ? $item['image'] : $item['image'];
                ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($image_url) ?>" width="50" height="50" class="img-thumbnail"></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= number_format($item['price'], 0, ',', '.') ?> VNĐ</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary update-cart" data-id="<?= $item['id'] ?>" data-action="decrease">-</button>
                        <input type="text" value="<?= $item['quantity'] ?>" class="text-center" style="width: 40px;" disabled>
                        <button class="btn btn-sm btn-outline-secondary update-cart" data-id="<?= $item['id'] ?>" data-action="increase">+</button>
                    </td>
                    <td><?= number_format($item['subtotal'], 0, ',', '.') ?> VNĐ</td>
                    <td><button class="btn btn-danger remove-item" data-id="<?= $item['id'] ?>">Xóa</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Tiếp tục mua sắm</a>
            <h4 class="mb-0">Tổng cộng: <?= number_format($total, 0, ',', '.') ?> VNĐ</h4>
        </div>
    <?php endif; ?>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2025 My Shop. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.update-cart').forEach(button => {
        button.addEventListener('click', function() {
            let productId = this.getAttribute('data-id');
            let action = this.getAttribute('data-action');
            let currentQuantity = parseInt(this.parentElement.querySelector('input').value);
            let newQuantity = action === 'increase' ? currentQuantity + 1 : Math.max(1, currentQuantity - 1);
            fetch('cart.php?action=update&id=' + productId + '&quantity=' + newQuantity)
                .then(response => {
                    if (response.status === 401) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Vui lòng đăng nhập để thực hiện chức năng này!',
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
                        text: data,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
                })
                .catch(error => {
                    if (error !== 'Unauthorized') {
                        console.error('Error:', error);
                    }
                });
        });
    });

    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            let productId = this.getAttribute('data-id');
            fetch('cart.php?action=remove&id=' + productId)
                .then(response => {
                    if (response.status === 401) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Vui lòng đăng nhập để thực hiện chức năng này!',
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
                        title: 'Đã xóa!',
                        text: data,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
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