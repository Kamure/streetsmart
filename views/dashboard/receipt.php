<?php
session_start();
$success = $_GET['status'] ?? 'failed';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Receipt | StreetSmart</title>
  <link rel="stylesheet" href="../../assets/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-container">
  <div class="sidebar">
    <h4>StreetSmart</h4>
    <a href="customer.php">Browse Products</a>
    <a href="orders.php">Order History</a>
    <a href="../../controllers/logout_controller.php" class="logout-btn">Logout</a>
  </div>

  <div class="main-content text-center">
    <h3>Payment <?= $success === 'success' ? 'Successful!' : 'Failed' ?></h3>

    <?php if ($success === 'success'): ?>
      <div class="alert alert-success mt-3">Thank you! Your payment was successful. A receipt has been sent to your email.</div>
      <a href="orders.php" class="btn btn-outline-primary mt-3">View Orders</a>
    <?php else: ?>
      <div class="alert alert-danger mt-3">Payment failed. Please try again or use a different method.</div>
      <a href="cart.php" class="btn btn-outline-danger mt-3">Back to Cart</a>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
