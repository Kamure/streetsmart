<?php
class Cart {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addItem($customer_id, $product_id, $quantity) {
        $stmt = $this->pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity), updated_at = NOW()");
        return $stmt->execute([$customer_id, $product_id, $quantity]);
    }

    public function removeItem($customer_id, $product_id) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?");
        return $stmt->execute([$customer_id, $product_id]);
    }

    public function getCart($customer_id) {
        $stmt = $this->pdo->prepare("SELECT c.product_id, p.name, p.price, c.quantity, (p.price * c.quantity) AS subtotal FROM cart c JOIN products p ON c.product_id = p.id WHERE c.customer_id = ?");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearCart($customer_id) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE customer_id = ?");
        return $stmt->execute([$customer_id]);
    }
}
?>