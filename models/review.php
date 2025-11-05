<?php
class Review {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($product_id, $customer_id, $rating, $comment) {
        $stmt = $this->pdo->prepare("INSERT INTO reviews (product_id, customer_id, rating, comment, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        return $stmt->execute([$product_id, $customer_id, $rating, $comment]);
    }
}
?>