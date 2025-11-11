<?php
class Review {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addRating($seller_id, $customer_id, $rating, $comment) {
        $stmt = $this->pdo->prepare("INSERT INTO seller_ratings (seller_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$seller_id, $customer_id, $rating, $comment]);
    }

    public function getSellerRatings($seller_id) {
        $stmt = $this->pdo->prepare("SELECT r.*, u.name AS customer_name
                                     FROM seller_ratings r
                                     JOIN users u ON r.customer_id = u.id
                                     WHERE r.seller_id = ?
                                     ORDER BY r.created_at DESC");
        $stmt->execute([$seller_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRating($seller_id) {
        $stmt = $this->pdo->prepare("SELECT AVG(rating) AS avg_rating FROM seller_ratings WHERE seller_id = ?");
        $stmt->execute([$seller_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['avg_rating'] ?? 0;
    }
}

?>


