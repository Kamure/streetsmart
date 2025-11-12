<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../config/database.php'; // ensures $pdo is available

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    
    if (!$name || !$email || !$phone || !$password || !$role) {
        header("Location: ../views/register.php?msg=missing");
        exit;
    }

    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: ../views/register.php?msg=exists");
        exit;
    }

    
    $hashed = password_hash($password, PASSWORD_DEFAULT);


    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, phone, password, role, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $success = $stmt->execute([$name, $email, $phone, $hashed, $role]);

if ($success) {
        $subject = "Your StreetSmart OTP Code";
        $message = "Hello $name,\n\nYour OTP code is: $otp\n\nUse this to verify your account.";
        $headers = "From: no-reply@streetsmart.com";

        mail($email, $subject, $message, $headers);

        header("Location: ../views/login.php?msg=registered");
    } else {
        header("Location: ../views/register.php?msg=error");
    }
    exit;

}
?>