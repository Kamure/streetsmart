<?php
class Order {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($customer_id, $shop_id, $total, $payment_ref, $payment_method) {
        $stmt = $this->pdo->prepare("INSERT INTO orders (customer_id, shop_id, total, status, payment_ref, payment_method, created_at, updated_at) VALUES (?, ?, ?, 'pending', ?, ?, NOW(), NOW())");
        $stmt->execute([$customer_id, $shop_id, $total, $payment_ref, $payment_method]);
        return $this->pdo->lastInsertId();
    }

    public function addItem($order_id, $product_id, $quantity, $price) {
        $subtotal = $quantity * $price;
        $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        return $stmt->execute([$order_id, $product_id, $quantity, $price, $subtotal]);
    }
}
?>