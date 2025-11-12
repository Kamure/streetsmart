<?php
class Review {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addRating($seller_id, $customer_id, $rating, $comment) {
        $stmt = $this->pdo->prepare("
            INSERT INTO reviews (seller_id, customer_id, rating, comment, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        return $stmt->execute([$seller_id, $customer_id, $rating, $comment]);
    }

    public function getSellerRatings($seller_id) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.name AS customer_name
            FROM reviews r
            JOIN users u ON u.id = r.customer_id
            WHERE r.seller_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$seller_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRating($seller_id) {
        $stmt = $this->pdo->prepare("
            SELECT AVG(rating) AS avg_rating
            FROM reviews
            WHERE seller_id = ?
        ");
        $stmt->execute([$seller_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['avg_rating'] ?? 0;
    }
}
?>
