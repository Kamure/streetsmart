<?php
session_start();
require_once '../config/database.php';

$seller_id = $_GET['id'] ?? null;

if (!$seller_id) {
    die("Seller ID not specified.");
}

$stmtSeller = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'seller'");
$stmtSeller->execute([$seller_id]);
$seller = $stmtSeller->fetch(PDO::FETCH_ASSOC);

if (!$seller) {
    die("Seller not found.");
}

$stmtReviews = $pdo->prepare("
    SELECT r.rating, r.comment, u.name AS customer_name, r.created_at
    FROM reviews r
    JOIN users u ON r.customer_id = u.id
    WHERE r.seller_id = ?
    ORDER BY r.created_at DESC
");
$stmtReviews->execute([$seller_id]);
$reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

$stmtAvg = $pdo->prepare("SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE seller_id = ?");
$stmtAvg->execute([$seller_id]);
$stats = $stmtAvg->fetch(PDO::FETCH_ASSOC);
$avg_rating = $stats['avg_rating'] ?? 0;
$total_reviews = $stats['total_reviews'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Reviews | StreetSmart Market</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="seller-reviews-body">

<div class="container mt-5">
    <h2 class="fw-bold text-primary mb-3"><?= htmlspecialchars($seller['name']); ?>'s Reviews</h2>
    <div class="mb-4">
        <span class="fs-4 text-warning">⭐ <?= $avg_rating ?: '0.0'; ?></span>
        <span class="text-muted small">Average Rating (<?= $total_reviews; ?> Reviews)</span>
    </div>

    <?php if ($reviews): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <strong><?= htmlspecialchars($review['customer_name']); ?></strong>
                    <span class="text-warning ms-2"><?= str_repeat('⭐', (int)$review['rating']); ?></span>
                    <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($review['comment'])); ?></p>
                    <small class="text-muted">Reviewed on <?= date('M d, Y', strtotime($review['created_at'])); ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No reviews yet for this seller.</div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="customer.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
