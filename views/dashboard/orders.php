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

<body style="background: linear-gradient(135deg, #f7f8fa, #eef1f5); min-height: 100vh;">
<div class="d-flex" style="min-height: 100vh;">
  <div class="bg-white shadow-sm d-flex flex-column p-4" style="width: 230px; min-height: 100vh;">
    <h4 class="fw-bold text-primary mb-4">StreetSmart</h4>
    <a href="customer.php" class="mb-2 btn btn-outline-primary w-100 text-start">Browse Products</a>
    <a href="orders.php" class="mb-2 btn btn-primary w-100 text-start active">Order History</a>
    <a href="../../controllers/logout_controller.php" class="btn btn-outline-danger w-100 mt-auto">Logout</a>
  </div>

  <div class="flex-grow-1 p-4">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
          <div class="card shadow border-0 mb-4">
            <div class="card-body">
              <h3 class="fw-bold text-primary mb-4">Order History</h3>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Order #</th>
                      <th>Date</th>
                      <th>Total (Ksh)</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($orders): ?>
                      <?php foreach ($orders as $order): ?>
                        <tr>
                          <td class="fw-semibold">#<?= htmlspecialchars($order['id']); ?></td>
                          <td><?= date('M d, Y', strtotime($order['created_at'])); ?></td>
                          <td><?= number_format($order['total']); ?></td>
                          <td>
                            <span class="badge bg-<?= $order['status'] === 'paid' ? 'success' : ($order['status'] === 'pending' ? 'warning text-dark' : 'secondary') ?>">
                              <?= htmlspecialchars(ucfirst($order['status'])); ?>
                            </span>
                          </td>
                          <td>
                            <a href="receipt.php?order_id=<?= $order['id']; ?>" class="btn btn-sm btn-outline-primary">View Receipt</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr><td colspan="5" class="text-center text-muted">No orders found.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
