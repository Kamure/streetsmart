<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$msg = $_GET['msg'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM shops WHERE user_id = ?");
$stmt->execute([$user_id]);
$shop = $stmt->fetch();
$shop_id = $shop['id'] ?? null;

$products = [];
if ($shop_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE shop_id = ? ORDER BY id DESC");
    $stmt->execute([$shop_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <img src="../../assets/images/logo.png" alt="StreetSmart Market" class="logo me-2" width="40">
            <span class="fw-bold" style="color: white;">StreetSmart Seller</span>
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="fw-semibold text-white">Hi, <?= htmlspecialchars($_SESSION['user']['name']); ?></span>
            <a href="../profile.php" class="btn btn-outline-dark btn-sm bg-white text-black border-black">Profile</a>
            <a href="../../controllers/logout_controller.php" class="btn btn-danger btn-sm">Logout</a>
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
    <?php elseif ($msg === 'shop_created'): ?>
        <div class="alert alert-success text-center">Shop created successfully!</div>
    <?php endif; ?>

    <?php if (!$shop_id): ?>
        <div class="card shadow-sm p-4 mb-5 border-0">
            <h5 class="fw-bold mb-3 text-primary">Create Your Shop</h5>
            <form action="../../controllers/shop.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_shop">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Shop Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category</label>
                    <input type="text" name="category" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Shop Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*" required>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4 fw-semibold">Create Shop</button>
                </div>
            </form>
        </div>
    <?php else: ?>
<?php
$stmt = $pdo->prepare("SELECT SUM(total) AS total_sales, COUNT(*) AS order_count FROM orders WHERE shop_id = ?");
$stmt->execute([$shop_id]);

$sales = $stmt->fetch();
?>

<div class="card shadow-sm p-4 mb-4 border-0">
  <h5 class="fw-bold mb-3 text-primary">Sales Summary</h5>
  <div class="row">
    <div class="col-md-6">
      <p><strong>Total Sales:</strong> KES <?= number_format($sales['total_sales'] ?? 0, 2) ?></p>
      <p><strong>Orders:</strong> <?= $sales['order_count'] ?? 0 ?></p>
    </div>
    <div class="col-md-6 text-end">
      <a href="../views/print_seller_orders.php?seller_id=<?= $user_id ?>" target="_blank" class="btn btn-outline-dark">Download Sales Report</a>
    </div>
  </div>
</div>
        <div class="card shadow-sm p-4 mb-5 border-0">
            <h5 class="fw-bold mb-3 text-primary">Add New Product</h5>
            <form action="../../controllers/product.php" method="POST" enctype="multipart/form-data">
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
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
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
                                <td><img src="../../uploads/<?= htmlspecialchars($product['image_path']); ?>" width="60" height="60" class="rounded"></td>
                                <td><?= htmlspecialchars($product['name']); ?></td>
                                <td><?= htmlspecialchars($product['category']); ?></td>
                                <td><?= number_format($product['price']); ?></td>
                                <td><?= $product['stock']; ?></td>
                                <td>
                                    <a href="../edit_product.php?id=<?= $product['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="../../controllers/product.php" method="POST" class="d-inline">
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
    <?php endif; ?>
</div>

<footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
