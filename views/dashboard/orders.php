<?php
require_once '../../config/database.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
  header('Location: ../login.php');
  exit;
}

$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :id ORDER BY created_at DESC");
$stmt->execute(['id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders | StreetSmart</title>
  <link rel="stylesheet" href="../../assets/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-container">
  <div class="sidebar">
    <h4>StreetSmart</h4>
    <a href="customer.php">Browse Products</a>
    <a href="orders.php" class="active">Order History</a>
    <a href="../../controllers/logout_controller.php" class="logout-btn">Logout</a>
  </div>

  <div class="main-content">
    <h3>Order History</h3>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Total (Ksh)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders): ?>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td>#<?= htmlspecialchars($o['id']) ?></td>
                <td><?= htmlspecialchars($o['created_at']) ?></td>
                <td><?= number_format($o['total']) ?></td>
                <td><span class="badge bg-<?= $o['status'] === 'Completed' ? 'success' : 'warning' ?>"><?= htmlspecialchars($o['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
