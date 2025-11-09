<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
  header('Location: ../login.php');
  exit;
}

$cart = $_SESSION['cart'] ?? [];
$total = array_sum(array_column($cart, 'price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Cart | StreetSmart</title>
  <link rel="stylesheet" href="../../assets/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-container">
  <div class="sidebar">
    <h4>StreetSmart</h4>
    <a href="customer.php">Browse Products</a>
    <a href="cart.php" class="active">My Cart</a>
    <a href="orders.php">Order History</a>
    <a href="../../controllers/logout.php" class="logout-btn">Logout</a>
  </div>

  <div class="main-content">
    <h3>My Cart</h3>

    <?php if (!empty($cart)): ?>
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price (Ksh)</th>
            <th>Remove</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart as $index => $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td><?= number_format($item['price']) ?></td>
              <td>
                <a href="../../controllers/cart_controller.php?remove=<?= $index ?>" class="btn btn-sm btn-outline-danger">Remove</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h5 class="text-end me-4">Total: <span class="text-primary">Ksh <?= number_format($total) ?></span></h5>

      <div class="checkout-form mt-4">
        <h5>Checkout</h5>
        <form action="../../controllers/order_controller.php" method="POST">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="fullname" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Payment Method</label>
              <select name="payment_method" class="form-select" required>
                <option value="mpesa">M-Pesa</option>
                <option value="card">Card</option>
              </select>
            </div>
          </div>
          <button type="submit" name="checkout" class="btn btn-primary">Complete Payment</button>
        </form>
      </div>

    <?php else: ?>
      <div class="alert alert-info text-center">Your cart is empty.</div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
