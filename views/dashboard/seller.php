<?php
session_start();

$seller_id = $_SESSION['user']['id'];

require_once '../../config/database.php';
require_once '../../models/review.php';


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
if ($shop){
  $stmtProduct = $pdo->prepare("SELECT * FROM products WHERE shop_id = ?");
  $stmtProduct->execute([$shop['id']]);
  $products = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);
}

$stmtServices = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmtServices->execute([$seller_id]);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);
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
            <span class="fw-semibold text-white">Hi, <?= htmlspecialchars($_SESSION['user']['name']); ?></span>
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

<?php if (!$shop): ?>
    <div class="card shadow-sm p-4 mb-5 border-0">
        <h5 class="fw-bold mb-3 text-primary">Create Your Shop</h5>
        <form action="../../controllers/shop.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_shop">
            <div class="mb-3">
                <label class="form-label fw-semibold">Shop Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" placeholder="Clothing, Accessories, Electronics...">Category</label>
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

<?php else: 
    $shop_id = $shop['id'];
    $stmt = $pdo->prepare("SELECT SUM(total) AS total_sales, COUNT(*) AS order_count FROM orders WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    $sales = $stmt->fetch();

    
    $reviewModel = new Review($pdo);
    $avg_rating = $reviewModel->getAverageRating($seller_id);
    $latest_reviews = $reviewModel->getSellerRatings($seller_id);
    $total_reviews = is_array($latest_reviews) ? count($latest_reviews) : 0;
?>

<div class="mb-3 text-center">
        <label class="me-3"><input type="radio" name="item_type" value="product" checked> Product</label>
        <label><input type="radio" name="item_type" value="service"> Service</label>
</div>

<div class="d-flex justify-content-center">
    <div id="service-fields" class="card shadow-sm p-4 mb-4 border-0" style="display:none; max-width: 600px; width:100%;">
        <h5 class="fw-bold mb-3 text-primary">Add New Service</h5>
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
        <div class="card shadow-sm p-4 border-0 h-100">
    <h5 class="fw-bold mb-3 text-primary">Sales Summary</h5>
    <p><strong>Total Sales:</strong> KES <?= number_format($sales['total_sales'] ?? 0, 2) ?></p>
    <p><strong>Orders:</strong> <?= $sales['order_count'] ?? 0 ?></p>

    
    <div class="mt-3">
        <a href="../print_seller_orders.php?seller_id=<?= $seller_id; ?>" class="btn btn-outline-dark">
    Print Sales (Browser)
</a>
        <a href="/streetsmart/controllers/export_orders_pdf.php" 
           class="btn btn-outline-danger">Export Orders PDF</a>
        <a href="/streetsmart/controllers/export_products_excel.php" 
           class="btn btn-outline-success">Export Products Excel</a>
    </div>
</div>

    </div>
</div>--

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


<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm p-4 border-0 h-100">
            <h5 class="fw-bold mb-3 text-primary">Sales Summary</h5>
            <p><strong>Total Sales:</strong> KES <?= number_format($sales['total_sales'] ?? 0, 2) ?></p>
            <p><strong>Orders:</strong> <?= $sales['order_count'] ?? 0 ?></p>
            <a href="../print_seller_orders.php?seller_id=<?= $user_id ?>" target="_blank" class="btn btn-outline-dark mt-2">Download Sales Report</a>
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
    <?php endif; ?>
</div>

<footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
