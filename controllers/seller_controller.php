<?php
require_once '../config/database.php';
require_once '../utils/notify.php';
session_start();
$seller_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT o.id, o.total, o.status, o.created_at, c.name AS customer_name FROM orders o JOIN users c ON o.customer_id = c.id WHERE o.shop_id = ?");
    $stmt->execute([$seller_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];


    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ? AND shop_id = ?");
    $stmt->execute([$status, $order_id, $seller_id]);

    
    $stmt2 = $pdo->prepare("SELECT u.email FROM orders o JOIN users u ON o.customer_id = u.id WHERE o.id = ?");
    $stmt2->execute([$order_id]);
    $customer = $stmt2->fetch(PDO::FETCH_ASSOC);

    
    if ($customer) {
        $email = $customer['email'];
        $message = "Hello! Your order #$order_id status has been updated to '$status'.";
        sendNotification($email, $message);
    }

    echo "Order updated and customer notified.";
}

?>