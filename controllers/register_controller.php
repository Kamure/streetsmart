<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../config/database.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $user_id = $pdo->lastInsertId();
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $_SESSION['pending_user_id'] = $user_id;
        $_SESSION['otp'] = $otp;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'beyonce.kamure@strathmore.edu';
            $mail->Password = 'xmwmyznownxbhrof'; // Gmail App Password
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('beyonce.kamure@strathmore.edu', 'StreetSmart');
            $mail->addAddress($email, $name);
            $mail->Subject = 'Your StreetSmart OTP Code';
            $mail->Body = "Hello $name,\n\nYour OTP code is: $otp\n\nUse this to verify your account.";

            $mail->send();
            header("Location: ../views/verify.php");
            exit;
        } catch (Exception $e) {
            echo "Registration successful, but OTP failed to send: {$mail->ErrorInfo}";
        }
    } else {
        header("Location: ../views/register.php?msg=error");
        exit;
    }
}
?>