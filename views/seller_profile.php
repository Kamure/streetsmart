<?php
require_once '../config/database.php';
require_once '../models/review.php';
require_once '../models/Shop.php';
session_start();

if (!isset($_GET['id'])) die("Seller not found.");

$seller_id = $_GET['id'];
$ratingModel = new Rating($pdo);
$shopModel = new Shop($pdo);

$seller = $shopModel->getByUserId($seller_id);
$ratings = $ratingModel->getSellerRatings($seller_id);
$avgRating = number_format($ratingModel->getAverageRating($seller_id), 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($seller['name']) ?> | StreetSmart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container py-5">
  <div class="text-center mb-4">
    <h2><?= htmlspecialchars($seller['name']) ?></h2>
    <p class="text-muted"><?= htmlspecialchars($seller['category']) ?> • <?= htmlspecialchars($seller['location']) ?></p>
    <h5>⭐ Average Rating: <strong><?= $avgRating ?></strong> / 5</h5>
  </div>

  <div class="card p-4 mb-4">
    <h4 class="mb-3">Rate This Seller</h4>
    <form action="../controllers/review.php" method="POST">
      <input type="hidden" name="seller_id" value="<?= $seller_id ?>">
      <div class="mb-3">
        <label class="form-label">Rating (1–5)</label>
        <select class="form-select" name="rating" required>
          <option value="">Select...</option>
          <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Comment (optional)</label>
        <textarea name="comment" class="form-control" rows="3"></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit Rating</button>
    </form>
  </div>

  <div class="card p-4">
    <h4 class="mb-3">Customer Reviews</h4>
    <?php if ($ratings): ?>
      <?php foreach ($ratings as $r): ?>
        <div class="border-bottom pb-3 mb-3">
          <strong><?= htmlspecialchars($r['customer_name']) ?></strong>
          <span class="text-warning"><?= str_repeat('⭐', $r['rating']) ?></span>
          <p class="mb-0"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
          <small class="text-muted"><?= $r['created_at'] ?></small>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No ratings yet.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
