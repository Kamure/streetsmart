<?php
session_start();
require_once '../../config/database.php';

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    die("Order ID not specified.");
}

$stmtOrder = $pdo->prepare("
    SELECT o.id, o.total, o.payment_method, o.status, o.created_at, 
           u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    WHERE o.id = ?
");
$stmtOrder->execute([$order_id]);
$order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

$stmtItems = $pdo->prepare("
    SELECT oi.product_id, oi.quantity, oi.price, p.name AS product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
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

  <div class="main-content">
    <h3 class="text-center mb-4">Receipt for Order #<?= htmlspecialchars($order['id']); ?></h3>

    <div class="mb-4">
        <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']); ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']); ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars(ucfirst($order['payment_method'])); ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($order['status'])); ?></p>
        <p><strong>Order Date:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
    </div>

    <table class="table table-bordered mb-4">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price (Ksh)</th>
                <th>Quantity</th>
                <th>Subtotal (Ksh)</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php foreach ($items as $item): 
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']); ?></td>
                <td><?= number_format($item['price']); ?></td>
                <td><?= htmlspecialchars($item['quantity']); ?></td>
                <td><?= number_format($subtotal); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="text-end fw-bold mb-4">Total: Ksh <?= number_format($total); ?></div>

    <div class="text-center">
        <a href="customer.php" class="btn btn-secondary">Back to Dashboard</a>
        <a href="orders.php" class="btn btn-primary">View All Orders</a>
    </div>
  </div>
</div>

</body>
</html>
