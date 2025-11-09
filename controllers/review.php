<?php
require_once '../config/database.php';
require_once '../models/Review.php';

$reviewModel = new Review($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $customer_id = $_POST['customer_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $reviewModel->create($product_id, $customer_id, $rating, $comment);
    echo "Review submitted.";
}
?> 