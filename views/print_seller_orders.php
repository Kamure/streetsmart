<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    echo "Access denied.";
    exit;
}

$seller_id = $_GET['seller_id'] ?? $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT 
        o.id AS order_id,
        u.name AS customer_name,
        u.email AS customer_email,
        o.total AS total_amount,
        o.created_at
    FROM orders o
    JOIN shops s ON o.shop_id = s.id
    JOIN users u ON o.customer_id = u.id
    WHERE s.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$seller_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Seller Orders | StreetSmart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print { .no-print { display: none; } }
    body { padding: 2rem; font-family: 'Segoe UI', sans-serif; }
    .report-box {
      max-width: 900px;
      margin: auto;
      border: 1px solid #ddd;
      padding: 2rem;
      border-radius: 8px;
      background-color: #f9f9f9;
    }
  </style>
</head>
<body>
  <div class="report-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="text-primary">Sales Report</h4>
      <button onclick="window.print()" class="btn btn-outline-dark btn-sm no-print">Print / Save as PDF</button>
    </div>

    <?php if (empty($orders)): ?>
      <div class="alert alert-info">No orders found for this seller.</div>
    <?php else: ?>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Total (Ksh)</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= $o['order_id'] ?></td>
            <td><?= htmlspecialchars($o['customer_name']) ?></td>
            <td><?= htmlspecialchars($o['customer_email']) ?></td>
            <td><?= number_format($o['total_amount']) ?></td>
            <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>