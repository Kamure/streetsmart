<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header('Location: login.php');
    exit;
}

$product_id = $_GET['id'] ?? null;
if (!$product_id) die("Product ID missing.");

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("Product not found.");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Product</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h2>Edit Product</h2>
  <form method="POST" action="../controllers/product.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="update_product">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

    <div class="mb-3">
      <label>Name</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Price</label>
      <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
    </div>

    <div class="mb-3">
      <label>Stock</label>
      <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
    </div>

    <div class="mb-3">
      <label>Category</label>
      <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($product['category']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="mb-3">
      <label>Current Image</label><br>
      <?php if ($product['image_path']): ?>
        <img src="../uploads/<?= htmlspecialchars($product['image_path']) ?>" width="120">
      <?php else: ?>
        <p>No image uploaded.</p>
      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label>Upload New Image (optional)</label>
      <input type="file" name="image" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Update Product</button>
    <a href="dashboard/seller.php" class="btn btn-secondary">Cancel</a>
  </form>
</body>
</html>