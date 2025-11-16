<?php
session_start();
require_once '../config/database.php';

$order_id = $_GET['order_id'] ?? null;
$customer_id = $_SESSION['user']['id'] ?? null;

if (!$order_id || !$customer_id) {
    die("Access denied.");
}

// Fetch order details
$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.created_at, o.payment_method, o.payment_ref,
           o.customer_name, o.customer_email, o.customer_phone, o.customer_address,
           s.name AS shop_name
    FROM orders o
    JOIN shops s ON o.shop_id = s.id
    WHERE o.id = ? AND o.customer_id = ?
");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

// Fetch order items
$stmtItems = $pdo->prepare("
    SELECT p.name, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Receipt | StreetSmart Market</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print { .no-print { display: none; } }
    body { padding: 2rem; font-family: 'Segoe UI', sans-serif; }
    .receipt-box {
      max-width: 800px;
      margin: auto;
      border: 1px solid #ddd;
      padding: 2rem;
      border-radius: 8px;
      background-color: #f9f9f9;
    }
  </style>
</head>
<body>
  <div class="receipt-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="text-primary">Customer Receipt</h4>
      <button onclick="window.print()" class="btn btn-outline-dark btn-sm no-print">Print / Save as PDF</button>
    </div>

    <p><strong>Order #<?= $order['id'] ?></strong></p>
    <p>Shop: <?= htmlspecialchars($order['shop_name']) ?></p>
    <p>Customer: <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_email']) ?>)</p>
    <p>Phone: <?= htmlspecialchars($order['customer_phone']) ?></p>
    <p>Delivery Address: <?= htmlspecialchars($order['customer_address']) ?></p>
    <p>Date: <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
    <p>Payment Method: <?= htmlspecialchars($order['payment_method']) ?></p>
    <p>Payment Reference: <?= htmlspecialchars($order['payment_ref']) ?></p>

    <table class="table table-bordered mt-4">
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Unit Price</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>Ksh <?= number_format($item['price']) ?></td>
          <td>Ksh <?= number_format($item['quantity'] * $item['price']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h5 class="text-end mt-3">Total: Ksh <?= number_format($order['total']) ?></h5>
  </div>
</body>
</html>