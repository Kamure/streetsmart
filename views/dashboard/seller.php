<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Redirect if not logged in or not a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
  header('Location: ../login.php');
  exit;
}

$user_id = $_SESSION['user']['id'];
$msg = $_GET['msg'] ?? null;

// Fetch seller’s products
$products = [];
$sql = "SELECT id, name, price, stock, category, image_path FROM products WHERE seller_id = ? ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seller Dashboard | StreetSmart Market</title>
  <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body class="dashboard-body">

  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../../assets/images/logo.png" alt="StreetSmart Market" class="logo me-2">
        <span class="fw-bold text-primary">StreetSmart Seller</span>
      </a>
      <div class="ms-auto d-flex align-items-center gap-3">
        <span class="fw-semibold text-muted">Hi, <?= htmlspecialchars($_SESSION['name'] ?? 'Seller'); ?></span>
        <a href="../profile.php" class="btn btn-outline-primary btn-sm">Profile</a>
        <a href="../../controllers/logout.php" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container mt-5 pt-4">
    <h3 class="fw-bold mb-4 text-center text-primary mt-5">Seller Dashboard</h3>

    <?php if ($msg === 'added'): ?>
      <div class="alert alert-success text-center">Product added successfully!</div>
    <?php elseif ($msg === 'deleted'): ?>
      <div class="alert alert-warning text-center">Product deleted.</div>
    <?php elseif ($msg === 'updated'): ?>
      <div class="alert alert-info text-center">Product updated.</div>
    <?php endif; ?>

    <div class="card shadow-sm p-4 mb-5 border-0">
      <h5 class="fw-bold mb-3 text-primary">Add New Product</h5>
      <form action="../../controllers/shop.php" method="POST" enctype="multipart/form-data" id="uploadForm">
        <input type="hidden" name="action" value="add_product">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Product Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Price (Ksh)</label>
            <input type="number" name="price" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Stock</label>
            <input type="number" name="stock" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Category</label>
            <select name="category" class="form-select" required>
              <option value="">Select Category</option>
              <option value="Food">Food</option>
              <option value="Clothing">Clothing</option>
              <option value="Electronics">Electronics</option>
              <option value="Accessories">Accessories</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Product Image</label>
            <input type="file" name="image" id="imageInput" class="form-control" accept="image/*" required>
          </div>
        </div>

        <div class="text-center mt-3">
          <img id="imagePreview" src="#" alt="Preview" class="d-none img-thumbnail mt-2" width="150">
        </div>

        <div class="text-end mt-4">
          <button type="submit" class="btn btn-primary px-4 fw-semibold">Upload Product</button>
        </div>
      </form>
    </div>

    <div class="card shadow-sm p-4 border-0">
      <h5 class="fw-bold mb-3 text-primary">My Products</h5>

      <?php if (!empty($products)): ?>
        <div class="table-responsive">
          <table class="table align-middle table-hover">
            <thead class="table-light">
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price (Ksh)</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product): ?>
                <tr>
                  <td><img src="<?= htmlspecialchars($product['image']); ?>" width="60" height="60" class="rounded"></td>
                  <td><?= htmlspecialchars($product['name']); ?></td>
                  <td><?= htmlspecialchars($product['category']); ?></td>
                  <td><?= number_format($product['price']); ?></td>
                  <td><?= $product['stock']; ?></td>
                  <td>
                    <a href="../edit_product.php?id=<?= $product['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="../../controllers/shop.php" method="POST" class="d-inline">
                      <input type="hidden" name="action" value="delete_product">
                      <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-info text-center">You haven't added any products yet.</div>
      <?php endif; ?>
    </div>
  </div>

  <footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('imageInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('imagePreview');
      if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
      } else {
        preview.classList.add('d-none');
      }
    });
  </script>
</body>
</html>
