<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
  $product_id = $_POST['product_id'];
  $name = $_POST['name'];
  $price = $_POST['price'];

  $_SESSION['cart'][$product_id] = [
    'name' => $name,
    'price' => $price,
    'quantity' => ($_SESSION['cart'][$product_id]['quantity'] ?? 0) + 1
  ];
}

if (isset($_POST['clear_cart'])) {
  $_SESSION['cart'] = [];
}

$success = $error = '';
if (isset($_POST['checkout'])) {
  $customer_name = $_POST['customer_name'];
  $customer_email = $_POST['customer_email'];
  $customer_address = $_POST['customer_address'];
  $cart = $_SESSION['cart'];

  if (empty($cart)) {
    $error = "Your cart is empty!";
  } else {
    try {
      $pdo->beginTransaction();
      $total = 0;

      $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, total_amount, created_at) VALUES (?, ?, ?, ?, NOW())");

      foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
      }
      $stmt->execute([$customer_name, $customer_email, $customer_address, $total]);
      $order_id = $pdo->lastInsertId();

      $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
      foreach ($cart as $item) {
        $stmtItem->execute([$order_id, $item['name'], $item['price'], $item['quantity']]);
      }

      $pdo->commit();
      $_SESSION['cart'] = [];
      $success = "Payment successful! Your order #$order_id has been placed.";
    } catch (Exception $e) {
      $pdo->rollBack();
      $error = "Checkout failed: " . $e->getMessage();
    }
  }
}


$products = [];

$stmt = $pdo->query("SELECT id, name, price, image FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard | StreetSmart Market</title>
  <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../../assets/images/logo.png" alt="Logo" class="me-2" width="40">
      StreetSmart Market
    </a>
    <div class="d-flex">
      <a href="profile.php" class="btn btn-white text-primary me-2" style="background-color: white; border: 1px solid white;">Profile</a>
      <a href="../../controllers/logout_controller.php" class="btn btn-white text-primary" style="background-color: white; border: 1px solid white;">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="fw-bold text-primary mb-4">Browse Products</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="product-grid">
    <?php if (empty($products)): ?>
      <p class="text-center text-muted">No products available right now.</p>
    <?php else: ?>
      <?php foreach ($products as $p): ?>
      <div class="product-card">
        <img src="<?= htmlspecialchars($p['image']); ?>" alt="<?= htmlspecialchars($p['name']); ?>">
        <h4><?= htmlspecialchars($p['name']); ?></h4>
        <p class="price">Ksh <?= number_format($p['price']); ?></p>
        <form method="POST">
        <input type="hidden" name="product_id" value="<?= $p['id']; ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
        <input type="hidden" name="price" value="<?= htmlspecialchars($p['price']); ?>">
        <label for="quantity_<?= $p['id']; ?>" class="form-label mb-1">Quantity:</label>
        <input type="number" id="quantity_<?= $p['id']; ?>" name="quantity" value="1" min="1" class="form-control mb-2" style="width: 80px;" required>
        <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm mt-2">Add to Cart</button>
        </form>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="cart-container mt-5">
    <h3 class="fw-bold mb-3">Your Cart</h3>

    <?php if (empty($_SESSION['cart'])): ?>
      <p class="text-muted">Your cart is empty.</p>
    <?php else: ?>
      <form method="POST">
        <table class="cart-table table table-bordered">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $total = 0;
            foreach ($_SESSION['cart'] as $item): 
              $subtotal = $item['price'] * $item['quantity'];
              $total += $subtotal;
            ?>
            <tr>
              <td><?= htmlspecialchars($item['name']); ?></td>
              <td>Ksh <?= number_format($item['price']); ?></td>
              <td><?= htmlspecialchars($item['quantity']); ?></td>
              <td>Ksh <?= number_format($subtotal); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="text-end fw-bold mb-3">Total: Ksh <?= number_format($total); ?></div>

        <div class="cart-actions">
          <button type="submit" name="clear_cart" class="btn-clear">Clear Cart</button>
        </div>
      </form>
    <?php endif; ?>
  </div>

  <div class="checkout-form mt-5">
    <h3 class="fw-bold mb-3">Checkout</h3>
    <form method="POST">
      <input type="text" name="customer_name" placeholder="Full Name" required>
      <input type="email" name="customer_email" placeholder="Email" required>
      <input type="tel" name="customer_phone" placeholder="Phone Number" required>
      <input type="text" name="customer_address" placeholder="Delivery Address" required>
      <div class="mb-3">
        <label class="form-label fw-bold">Choose Payment Method:</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" required onclick="togglePaymentFields()">
          <label class="form-check-label" for="mpesa">M-Pesa</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" required onclick="togglePaymentFields()">
          <label class="form-check-label" for="card">Card</label>
        </div>
      </div>
      <div id="mpesa-fields" class="mb-3" style="display:none;">
        <label for="mpesa_phone" class="form-label">M-Pesa Phone Number</label>
        <input type="tel" class="form-control" id="mpesa_phone" name="mpesa_phone" placeholder="e.g. 07XXXXXXXX" pattern="^07\d{8}$">
        <button type="button" class="btn btn-primary mt-2" onclick="alert('Simulate M-Pesa payment prompt')">Pay with M-Pesa</button>
      </div>
      <div id="card-fields" class="mb-3" style="display:none;">
        <label for="card_number" class="form-label">Card Number</label>
        <input type="text" class="form-control mb-2" id="card_number" name="card_number" maxlength="19" placeholder="Card Number">
        <label for="card_expiry" class="form-label">Expiry Date</label>
        <input type="text" class="form-control mb-2" id="card_expiry" name="card_expiry" maxlength="5" placeholder="MM/YY">
        <label for="card_cvc" class="form-label">CVC</label>
        <input type="text" class="form-control" id="card_cvc" name="card_cvc" maxlength="4" placeholder="CVC">
      </div>
      <script>
        function togglePaymentFields() {
          var mpesa = document.getElementById('mpesa').checked;
          var card = document.getElementById('card').checked;
          document.getElementById('mpesa-fields').style.display = mpesa ? 'block' : 'none';
          document.getElementById('card-fields').style.display = card ? 'block' : 'none';
        }
      
        document.addEventListener('DOMContentLoaded', function() {
          togglePaymentFields();
        });
      </script>
      <button type="submit" name="checkout" class="btn btn-success">Complete Payment</button>
    </form>
  </div>
</div>

<footer>
  &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
