<?php
require_once '../config/database.php';
session_start();
$customer_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, status, updated_at FROM orders WHERE customer_id = ?");
$stmt->execute([$customer_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>