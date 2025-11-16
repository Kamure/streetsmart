<?php
require_once '../../config/database.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
  header('Location: ../login.php');
  exit;
}

$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
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
           <?php foreach ($orders as $order): ?>
  <tr>
    <td><?= htmlspecialchars($order['id']); ?></td>
    <td><?= htmlspecialchars($order['status']); ?></td>
    <td><?= date('M d, Y', strtotime($order['created_at'])); ?></td>
    <td>
      
      <a href="../receipt.php?order_id=<?= $order['id']; ?>" class="btn btn-sm btn-outline-primary">View Receipt</a>
    </td>
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
