<?php
session_start();
require_once '../config/database.php';

// Make sure a seller_id is provided
if (!isset($_GET['seller_id'])) {
    echo "Seller not specified.";
    exit;
}

$seller_id = intval($_GET['seller_id']);

// Fetch seller info
$stmt = $pdo->prepare("SELECT name, avatar_path FROM users WHERE id = ?");
$stmt->execute([$seller_id]);
$seller = $stmt->fetch();

if (!$seller) {
    echo "Seller not found.";
    exit;
}

// Fetch reviews for this seller
$stmt = $pdo->prepare("SELECT r.rating, r.comment, u.name AS customer_name 
                       FROM reviews r
                       JOIN users u ON r.customer_id = u.id
                       WHERE r.seller_id = ?
                       ORDER BY r.created_at DESC");
$stmt->execute([$seller_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $total = 0;
    foreach ($reviews as $rev) {
        $total += $rev['rating'];
    }
    $avg_rating = round($total / count($reviews), 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($seller['name']) ?> Reviews | StreetSmart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body class="seller-profile-body">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <div class="container">
    <a class="navbar-brand" href="../views/profile.php">StreetSmart</a>
    <a href="../views/profile.php" class="btn btn-light btn-sm">Back to Profile</a>
  </div>
</nav>

<div class="container mt-5 pt-5">
    <div class="text-center mb-4">
        <img src="../uploads/<?= htmlspecialchars($seller['avatar_path'] ?? 'default-avatar.jpg') ?>" width="100" height="100" class="rounded-circle mb-2">
        <h3 class="fw-bold text-primary"><?= htmlspecialchars($seller['name']) ?></h3>
        <p class="text-muted mb-1">Average Rating: <span class="text-warning"><?= $avg_rating ?>/5</span></p>
        <p class="text-muted small"><?= count($reviews) ?> Review(s)</p>
    </div>

    <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $rev): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <p class="fw-semibold mb-1">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                    <small class="text-secondary">— <?= htmlspecialchars($rev['customer_name']) ?> (Rating: <?= $rev['rating'] ?>/5)</small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">No reviews yet for this seller.</div>
    <?php endif; ?>
</div>

<footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
