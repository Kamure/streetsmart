<?php
require_once '../config/database.php';
require_once '../models/Review.php';
session_start();

if (!isset($_SESSION['user']['id'])) {
    die("You must be logged in to rate sellers.");
}

$reviewModel = new Review($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seller_id = $_POST['seller_id'];
    $customer_id = $_SESSION['user']['id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'] ?? '';

    if ($reviewModel->addRating($seller_id, $customer_id, $rating, $comment)) {
        header("Location: ../views/dashboard/customer.php?success=1");
        exit;
    } else {
        echo "Error submitting rating.";
    }
}

