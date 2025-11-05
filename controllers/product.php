<?php
require_once '../config/db.php';
require_once '../models/Product.php';

$productModel = new Product($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_id = $_POST['shop_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_path = 'uploads/' . $filename;
            $productModel->create($shop_id, $name, $description, $price, $stock, $image_path, $category);
            echo "Product created successfully.";
        } else {
            echo "Image upload failed.";
        }
    } else {
        echo "Image missing or upload error.";
    }
}
?>