<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header('Location: ../views/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

$action = $_POST['action'] ?? '';

if ($action === 'add_product') {
    $stmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $shop = $stmt->fetch();
    
    if (!$shop) {
        die('<div class="alert alert-danger text-center">You must create a shop first!</div>');
    }

    $shop_id = $shop['id'];

    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');

    
    if (!$name || !$price || !$stock || !$category) {
        die('<div class="alert alert-danger text-center">Please fill in all required fields.</div>');
    }

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = $filename;
        } else {
            die('<div class="alert alert-danger text-center">Failed to upload product image.</div>');
        }
    } else {
        die('<div class="alert alert-danger text-center">Product image is required.</div>');
    }

    $stmt = $pdo->prepare("INSERT INTO products (shop_id, name, description, price, stock, category, image_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$shop_id, $name, $description, $price, $stock, $category, $image_path]);

    header('Location: ../views/dashboard/seller.php?msg=added');
    exit;
}


if ($action === 'update_product') {
    $product_id = $_POST['product_id'] ?? null;
    if (!$product_id) die("Product ID is missing.");

    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) die("Product not found.");

    $image_path = $product['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '.' . $ext;
        $upload_dir = __DIR__ . '/../assets/images/';
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = $filename;
        }
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category=?, image_path=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$name, $description, $price, $stock, $category, $image_path, $product_id]);

    header('Location: ../views/dashboard/seller.php?msg=updated');
    exit;
}

if ($action === 'delete_product') {
    $product_id = $_POST['product_id'] ?? null;
    if ($product_id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
        $stmt->execute([$product_id]);
    }

    header('Location: ../views/dashboard/seller.php?msg=deleted');
    exit;
}
