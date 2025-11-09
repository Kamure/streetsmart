<?php
require_once '../config/database.php';
require_once '../models/Cart.php';

$cart = new Cart($pdo);
session_start();
$customer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;

    if ($action === 'add') {
        $cart->addItem($customer_id, $product_id, $quantity);
        echo "Item added.";
    } elseif ($action === 'remove') {
        $cart->removeItem($customer_id, $product_id);
        echo "Item removed.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($cart->getCart($customer_id));
}
?>