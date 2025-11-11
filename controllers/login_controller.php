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

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'beyonce.kamure@strathmore.edu'; 
            $mail->Password = 'xmwm yzno wnxb hrof
';   
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('beyonce.kamure@strathmore.edu', 'StreetSmart');
            $mail->addAddress($email, $user['name']);
            $mail->Subject = 'Your Login OTP';
            $mail->Body = "Hello {$user['name']},\n\nYour OTP is: $otp\n\nUse this to complete your login.";

            $mail->send();
            echo "Login successful. OTP sent.";
        } catch (Exception $e) {
            echo "Login successful, but OTP failed to send: {$mail->ErrorInfo}";
        }
    } else {
        echo "Invalid credentials.";
    }
}
?>