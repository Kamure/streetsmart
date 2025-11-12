<?php
require_once '../config/database.php';
require '../vendor/autoload.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, name, password, email, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        

    // Store user info in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // ✅ Redirect based on role
        if ($user['role'] === 'seller') {
            header('Location: ../views/profile.php');
            exit;
        } elseif ($user['role'] === 'customer') {
            header('Location: ../views/dashboard/customer.php');
            exit;
        } else {
            // fallback if role missing
            header('Location: ../views/dashboard/index.php');
            exit;
        }
    }
?>