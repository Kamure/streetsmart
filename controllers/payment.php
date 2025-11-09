<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $method = $_POST['method']; // e.g. 'mpesa', 'card'
    $ref = 'TXN' . rand(100000, 999999);

    $stmt = $pdo->prepare("UPDATE orders SET payment_method = ?, payment_ref = ?, status = 'paid', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$method, $ref, $order_id]);

    echo json_encode(['message' => 'Payment successful', 'ref' => $ref]);
}
?>