<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Dashboard | StreetSmart Market</title>

  <link rel="icon" type="image/png" href="../../assets/images/favicon.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../../assets/css/style.css" />
</head>

<body class="seller-dashboard">

  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../../assets/images/logo.png" alt="StreetSmart" class="navbar-logo" width="40" height="40" />
        <span class="fw-bold">StreetSmart Seller</span>
      </a>

      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-3">
          <a href="../profile.php" class="nav-link">
            <img src="../../assets/images/default-avatar.jpg" alt="Profile" class="profile-img" width="40" height="40"/>
            <button class="btn btn-light">View Profile</button>
          </a>
        </li>
        <li class="nav-item">
          <a href="../../controllers/logout.php" class="btn btn-light btn-sm fw-semibold px-3">Logout</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container mt-5 pt-5">
    <div class="dashboard-header text-center">
      <h3 class="text-primary">Welcome back, Seller</h3>
      <p class="text-muted">Manage your products and grow your shop with StreetSmart Market</p>
    </div>

    <div class="card add-product-card p-4 mb-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary m-0">Add New Product</h5>
        <button class="btn btn-primary add-btn px-4" type="button" data-bs-toggle="collapse" data-bs-target="#addProductForm">+ Add Product</button>
      </div>

      <div class="collapse" id="addProductForm">
        <form action="../../controllers/add_product.php" method="POST" enctype="multipart/form-data">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Product Name</label>
              <input type="text" name="name" class="form-control" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Price (Ksh)</label>
              <input type="number" name="price" class="form-control" min="0" required />
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" rows="2" class="form-control" placeholder="Brief description..." required></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Stock Quantity</label>
              <input type="number" name="stock" class="form-control" min="1" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Upload Image</label>
              <input type="file" name="image" class="form-control" accept="image/*" required />
            </div>
          </div>

          <div class="text-end">
            <button class="btn btn-success px-4 py-2 rounded-3">Save Product</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card product-list-card p-4">
      <h5 class="fw-bold text-primary mb-4">My Products</h5>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card product-card p-3">
            <img src="../../assets/images/sample-product.jpeg" alt="Product" class="product-img mb-3" />
            <h6 class="fw-bold mb-1">Handmade Necklace</h6>
            <p class="text-muted mb-1">Ksh 1500</p>
            <p class="small text-secondary">Stock: 15</p>
            <div class="d-flex justify-content-between">
              <button class="btn btn-outline-secondary btn-sm">Edit</button>
              <button class="btn btn-outline-danger btn-sm">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
