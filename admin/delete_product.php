<?php
session_start();
include '../db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: products.php?error=ID sản phẩm không hợp lệ');
    exit;
}

try {
    $checkStmt = $conn->prepare("SELECT * FROM product WHERE id = :id");
    $checkStmt->execute(['id' => $id]);
    $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: products.php?error=Không tìm thấy sản phẩm cần xóa');
        exit;
    }
    
    if (strpos($product['image'], 'assets/uploads/products/') === 0) {
        $image_path = '../' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM product WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    header('Location: products.php?message=Xóa sản phẩm thành công');
    exit;
    
} catch (PDOException $e) {
    header('Location: products.php?error=Lỗi khi xóa sản phẩm: ' . $e->getMessage());
    exit;
}
?>