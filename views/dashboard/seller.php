<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/review.php';

// Check login and role
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}
if ($_SESSION['user']['role'] !== 'seller') {
    die("Access denied - Seller account required.");
}

$seller_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'seller'");
$stmt->execute([$seller_id]);
$seller = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$seller) {
    die("Seller profile not found.");
}

$stmtShop = $pdo->prepare("SELECT * FROM shops WHERE user_id = ?");
$stmtShop->execute([$seller_id]);
$shop = $stmtShop->fetch(PDO::FETCH_ASSOC);

$products = [];
if ($shop) {
    $stmtProduct = $pdo->prepare("SELECT * FROM products WHERE shop_id = ?");
    $stmtProduct->execute([$shop['id']]);
    $products = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);
}
// Fetch services for this seller: if shop exists, use shop_id, else use seller_id
if ($shop) {
    $stmtServices = $pdo->prepare("SELECT * FROM services WHERE shop_id = ?");
    $stmtServices->execute([$shop['id']]);
    $services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);
} else {
    $services = [];
}

$reviewModel = new Review($pdo);
$avg_rating = $reviewModel->getAverageRating($seller_id);
$latest_reviews = $reviewModel->getSellerRatings($seller_id);
$total_reviews = is_array($latest_reviews) ? count($latest_reviews) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard | StreetSmart Market</title>
    <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<div class="dashboard-container">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../../assets/images/logo.png" alt="StreetSmart Market" class="logo me-2" width="40">
            <span class="fw-bold" style="color: white;">StreetSmart Seller</span>
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="fw-semibold text-white">Hi, <?= isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : 'Seller'; ?></span>
            <a href="../profile.php" class="btn btn-outline-dark btn-sm bg-white text-black border-black">Profile</a>
            <a href="../../controllers/logout_controller.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-primary mt-3 mb-0">Seller Dashboard</h3>
                <a href="analytics.php" class="btn btn-info fw-semibold" style="color:white;">
                        <i class="fa fa-chart-bar me-1"></i> Analytics & Reports
                </a>
        </div>

    <?php $msg = $_GET['msg'] ?? null; ?>
    <?php if ($msg === 'added'): ?>
        <div class="alert alert-success text-center">Product added successfully!</div>
    <?php elseif ($msg === 'deleted'): ?>
        <div class="alert alert-warning text-center">Product deleted.</div>
    <?php elseif ($msg === 'updated'): ?>
        <div class="alert alert-info text-center">Product updated.</div>
    <?php elseif ($msg === 'shop_created'): ?>
        <div class="alert alert-success text-center">Shop created successfully!</div>
  <?php endif; ?>

<!-- Optional shop creation -->
<?php if (!$shop): ?>
    <div id="shop-create-alert" class="alert alert-info text-center">You can add services and view your dashboard without a shop. Create a shop only if you want to add products.</div>
<?php endif; ?>

<?php
if ($shop) {
    $shop_id = $shop['id'];
    $stmt = $pdo->prepare("SELECT SUM(total) AS total_sales, COUNT(*) AS order_count FROM orders WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    $sales = $stmt->fetch();

    // Check for products
    $stmtProductCount = $pdo->prepare("SELECT COUNT(*) FROM products WHERE shop_id = ?");
    $stmtProductCount->execute([$shop_id]);
    $product_count = $stmtProductCount->fetchColumn();

    // Check for orders
    $stmtOrderCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE shop_id = ?");
    $stmtOrderCount->execute([$shop_id]);
    $order_count = $stmtOrderCount->fetchColumn();

    $reviewModel = new Review($pdo);
    $avg_rating = $reviewModel->getAverageRating($seller_id);
    $latest_reviews = $reviewModel->getSellerRatings($seller_id);
    $total_reviews = is_array($latest_reviews) ? count($latest_reviews) : 0;

    if ($product_count == 0) {
        echo '<div class="alert alert-warning text-center">You have not added any products to your shop. Add products to start receiving orders.</div>';
    } elseif ($order_count == 0) {
        echo '<div class="alert alert-info text-center">You have products, but no orders yet. Share your shop or wait for customers to place orders.</div>';
    }
} else {
    $sales = ['total_sales' => 0, 'order_count' => 0];
    $avg_rating = 0;
    $latest_reviews = [];
    $total_reviews = 0;
}
?>

<div class="mb-3 text-center">
    <label class="me-3"><input type="radio" name="item_type" value="product" checked> Product</label>
    <label><input type="radio" name="item_type" value="service"> Service</label>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm p-4 border-0 h-100">
            <h5 class="fw-bold mb-3 text-primary">Sales Summary</h5>
            <p><strong>Total Sales:</strong> KES <?= number_format($sales['total_sales'] ?? 0, 2) ?></p>
            <p><strong>Orders:</strong> <?= $sales['order_count'] ?? 0 ?></p>
            <a href="analytics.php" class="btn btn-outline-dark">View Sales Summary</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm p-4 border-0 h-100">
            <h5 class="fw-bold mb-3 text-primary">My Ratings</h5>
            <div class="mb-2">
                <span class="fs-4 text-warning">&#9733; <?= $avg_rating ? number_format($avg_rating,1) : '0.0'; ?></span>
                <span class="text-muted small">Average (<?= $total_reviews; ?> Reviews)</span>
            </div>
            <?php if ($latest_reviews): ?>
                <div style="max-height:120px;overflow-y:auto;">
                <?php foreach (array_slice($latest_reviews,0,3) as $review): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <span class="text-warning"><?= str_repeat('&#9733;', (int)$review['rating']); ?></span>
                        <span class="small text-muted ms-2">by <?= htmlspecialchars($review['customer_name']); ?></span><br>
                        <span class="small">"<?= htmlspecialchars($review['comment']); ?>"</span>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="text-muted">No reviews yet.</span>
            <?php endif; ?>
            <a href="../seller_reviews.php?id=<?= $_SESSION['user']['id'] ?>" class="btn btn-outline-primary mt-2">View All Reviews</a>
        </div>
    </div>
</div>


<div class="row mb-4">
  <div class="col-md-6 mx-auto">
    <div id="service-fields" class="card shadow-sm p-4 mb-4 border-0" style="display:none;">
      <h5 class="fw-bold mb-3 text-primary">Add New Service</h5>
      <form action="../../controllers/service.php" method="POST">
        <input type="hidden" name="action" value="add_service">
        <div class="mb-3">
          <label class="form-label fw-semibold">Service Name</label>
          <input type="text" name="service_name" class="form-control" placeholder="Enter service name">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="service_description" class="form-control" rows="3" placeholder="Describe your service"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Price (Ksh)</label>
          <input type="number" name="service_price" class="form-control" placeholder="e.g. 500">
        </div>
        <div class="text-end">
          <button type="submit" class="btn btn-primary px-4 fw-semibold">Add Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleItemType() {
    const type = document.querySelector('input[name="item_type"]:checked').value;
    document.getElementById('service-fields').style.display = type === 'service' ? 'block' : 'none';
    document.getElementById('product-form').style.display = type === 'product' ? 'block' : 'none';
    document.getElementById('product-list').style.display = type === 'product' ? 'block' : 'none';
}
document.querySelectorAll('input[name="item_type"]').forEach(el => {
    el.addEventListener('change', toggleItemType);
});
document.addEventListener('DOMContentLoaded', toggleItemType);
</script>


        <div id="product-form" class="card shadow-sm p-4 mb-5 border-0">
            <h5 class="fw-bold mb-3 text-primary">Add New Product</h5>
            <form action="../../controllers/product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Shop Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($shop['name'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Shop Location</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($shop['location'] ?? '') ?>" readonly>
                    </div>
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

        <div id="product-list" class="card shadow-sm p-4 border-0">
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

<div id="service-list-section" class="mb-4">
  <h5 class="fw-bold mb-3 text-primary">My Services</h5>
  <?php if (!empty($services)): ?>
    <div class="row g-3">
      <?php $serviceModals = [];
      foreach ($services as $service): ?>
        <div class="col-md-4">
          <div class="card h-100 border-primary">
            <div class="card-body">
              <h6 class="card-title fw-bold text-primary"><?= htmlspecialchars($service['name']); ?></h6>
              <p class="card-text mb-2 small"><?= htmlspecialchars($service['description']); ?></p>
              <p class="mb-2"><span class="fw-semibold">Price:</span> Ksh <?= number_format($service['price']); ?></p>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editServiceModal<?= $service['id']; ?>">Edit</button>
                <form action="../../controllers/service.php" method="POST" class="d-inline">
                  <input type="hidden" name="action" value="delete_service">
                  <input type="hidden" name="service_id" value="<?= $service['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this service?')">Delete</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php ob_start(); ?>
        <div class="modal fade" id="editServiceModal<?= $service['id']; ?>" tabindex="-1" aria-labelledby="editServiceModalLabel<?= $service['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="../../controllers/service.php" method="POST">
                <input type="hidden" name="action" value="edit_service">
                <input type="hidden" name="service_id" value="<?= $service['id']; ?>">
                <div class="modal-header">
                  <h5 class="modal-title" id="editServiceModalLabel<?= $service['id']; ?>">Edit Service</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Service Name</label>
                    <input type="text" name="service_name" class="form-control" value="<?= htmlspecialchars($service['name']); ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="service_description" class="form-control" rows="3" required><?= htmlspecialchars($service['description']); ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Price (Ksh)</label>
                    <input type="number" name="service_price" class="form-control" value="<?= htmlspecialchars($service['price']); ?>" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php $serviceModals[] = ob_get_clean(); ?>
      <?php endforeach; ?>
    </div>
    <?php foreach ($serviceModals as $modalHtml) echo $modalHtml; ?>
  <?php else: ?>
    <div class="alert alert-info text-center">You haven't added any services yet.</div>
  <?php endif; ?>
</div>
</div>

<footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
