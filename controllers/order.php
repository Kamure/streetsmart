<?php
require_once '../config/database.php';
require_once '../models/Order.php';

$orderModel = new Order($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $shop_id = $_POST['shop_id'];
    $payment_ref = $_POST['payment_ref'];
    $payment_method = $_POST['payment_method'];
    $items = json_decode($_POST['items'], true); // array of {product_id, quantity, price}

    $total = array_reduce($items, fn($sum, $item) => $sum + ($item['quantity'] * $item['price']), 0);
    $order_id = $orderModel->create($customer_id, $shop_id, $total, $payment_ref, $payment_method);

    foreach ($items as $item) {
        $orderModel->addItem($order_id, $item['product_id'], $item['quantity'], $item['price']);
    }

    echo "Order placed successfully.";
}
?>