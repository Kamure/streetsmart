<?php
session_start();
require_once '../config/database.php';

$user_id = $_SESSION['pending_user_id'] ?? null;
$entered_otp = trim($_POST['otp'] ?? '');

if (!$user_id || !$entered_otp) {
    die("Missing verification data.");
}

$stored_otp = $_SESSION['otp'] ?? null;

if ($entered_otp === $stored_otp) {
    // Mark user as verified
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$user_id]);

    // Clear session OTP data
    unset($_SESSION['otp'], $_SESSION['pending_user_id']);

    // Redirect to login with success message
    header("Location: ../views/login.php?msg=verified");
    exit;
} else {
    echo "Invalid or expired OTP. Please go back and try again.";
}