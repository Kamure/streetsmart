<?php
session_start();
require_once '../../config/database.php';

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    die("Order ID not specified.");
}


$stmtOrder = $pdo->prepare("
    SELECT o.id, o.total, o.payment_method, o.status, o.created_at, 
           u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
           s.name AS shop_name, s.location AS shop_location, selleru.name AS seller_name, selleru.email AS seller_email, selleru.phone AS seller_phone
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    JOIN shops s ON o.shop_id = s.id
    JOIN users selleru ON s.user_id = selleru.id
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
  </div>


        <div class="main-content" style="background: #f4f8fb; min-height: 100vh; padding-top: 40px;">
            <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="card shadow-lg p-4" style="max-width: 600px; width: 100%; border-radius: 18px; background: #fff;">
                    <div class="text-center mb-4">
                        <span style="font-size: 2.5rem; color: #4bb543;">
                            <?php if ($order['status'] === 'paid'): ?>
                                <i class="fa fa-check-circle"></i>
                            <?php else: ?>
                                <i class="fa fa-file-invoice"></i>
                            <?php endif; ?>
                        </span>
                        <h2 class="fw-bold mb-1" style="color:#407bba; letter-spacing:1px;">Payment Receipt</h2>
                        <div class="mb-2 text-muted" style="font-size:1.1rem;">Thank you for your order!</div>
                    </div>
                    <div class="mb-4 px-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-1"><strong>Order #:</strong> <?= htmlspecialchars($order['id']); ?></div>
                                <div class="mb-1"><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></div>
                                <div class="mb-1"><strong>Customer Email:</strong> <?= htmlspecialchars($order['customer_email']); ?></div>
                                <div class="mb-1"><strong>Customer Phone:</strong> <?= htmlspecialchars($order['customer_phone']); ?></div>
                                <div class="mb-1"><strong>Shop:</strong> <?= htmlspecialchars($order['shop_name']); ?> (<?= htmlspecialchars($order['shop_location']); ?>)</div>
                            </div>
                            <div class="col-md-6">
                                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'seller'): ?>
                                    <div class="mb-1"><strong>Seller:</strong> <?= htmlspecialchars($order['seller_name']); ?></div>
                                    <div class="mb-1"><strong>Seller Email:</strong> <?= htmlspecialchars($order['seller_email']); ?></div>
                                    <div class="mb-1"><strong>Seller Phone:</strong> <?= htmlspecialchars($order['seller_phone']); ?></div>
                                <?php endif; ?>
                                <div class="mb-1"><strong>Payment Method:</strong> <?= htmlspecialchars(ucfirst($order['payment_method'])); ?></div>
                                <div class="mb-1"><strong>Status:</strong> <span class="badge bg-<?= $order['status']==='paid'?'success':'warning' ?>" style="font-size:1em;"><?= htmlspecialchars(ucfirst($order['status'])); ?></span></div>
                                <div class="mb-1"><strong>Order Date:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0" style="background:#f8f9fa; border-radius:10px; overflow:hidden;">
                            <thead class="table-light">
                                <tr style="background:#e9ecef;">
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
                    </div>
                    <div class="text-end fw-bold mb-4" style="font-size:1.3rem; color:#407bba;">Total: Ksh <?= number_format($total); ?></div>
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                        <a href="customer.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                        <a href="orders.php" class="btn btn-primary">View All Orders</a>
                    </div>
                    <div class="text-center mt-4">
                        <?php if ($order['status'] !== 'paid'): ?>
                            <form id="completePaymentForm" action="../../controllers/payment.php" method="POST" class="d-inline-block mt-3">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']); ?>">
                                <input type="hidden" name="method" value="<?= htmlspecialchars($order['payment_method']); ?>">
                                <button type="submit" class="btn btn-success px-4">Complete Payment</button>
                            </form>
<?php else: ?>
    <div class="alert alert-success mt-3 mb-0" style="font-size:1.1em;">Payment completed. Thank you!</div>
<?php endif; ?>
<div class="alert alert-success mt-3 mb-0" style="font-size:1.1em;">Payment completed. Thank you!</div>

<!-- ✅ Place PDF download link here -->
<div class="mt-3 text-center">
  <a href="../../controllers/export_receipt_pdf.php?order_id=<?= htmlspecialchars($order['id']); ?>" class="btn btn-outline-primary">Download Receipt (PDF)</a>
</div>
</div> <!-- end of card -->
                    </div>
                </div>
            </div>
        </div>

</body>
</html>
