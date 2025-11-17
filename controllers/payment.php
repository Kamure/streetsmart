<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $method = $_POST['method']; // e.g. 'mpesa', 'card'
    $ref = 'TXN' . rand(100000, 999999);

    $stmt = $pdo->prepare("UPDATE orders SET payment_method = ?, payment_ref = ?, status = 'paid', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$method, $ref, $order_id]);

    // Insert payment record
    $order = $pdo->prepare("SELECT total FROM orders WHERE id = ?");
    $order->execute([$order_id]);
    $orderData = $order->fetch(PDO::FETCH_ASSOC);
    $amount = $orderData ? $orderData['total'] : 0;
    $stmtPay = $pdo->prepare("INSERT INTO payments (order_id, transaction_id, amount, method, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'success', NOW(), NOW())");
    $stmtPay->execute([$order_id, $ref, $amount, $method]);

    // Set a session flag to show payment success message once
    session_start();
    $_SESSION['show_payment_success'] = true;
    // Redirect to the receipt page after payment
    header('Location: ../views/dashboard/receipt.php?order_id=' . urlencode($order_id));
    exit;
}
?>