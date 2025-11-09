<?php
class Receipt {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getReceiptByOrder($order_id) {
        $stmt = $this->pdo->prepare("SELECT o.id, o.total, o.payment_ref, o.payment_method, o.created_at, u.name AS customer_name FROM orders o JOIN users u ON o.customer_id = u.id WHERE o.id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt2 = $this->pdo->prepare("SELECT p.name, i.quantity, i.price, i.subtotal FROM order_items i JOIN products p ON i.product_id = p.id WHERE i.order_id = ?");
        $stmt2->execute([$order_id]);
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return ['order' => $order, 'items' => $items];
    }
}
?>