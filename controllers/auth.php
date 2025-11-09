<?php
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/OtpCode.php';

function generateOTP($length = 6) {
    return str_pad(rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
}



$userModel = new User($pdo);
$otpModel = new OtpCode($pdo);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $success = $userModel->create($name, $email, $phone, $password, $role);

    if ($success) {
         $user = $userModel->findByEmail($email);
        $user_id = $user['id'];


        $otp_code = $otpModel->generate($user_id);

         mail($email, "Your verification code is: $otp");



        echo "User registered successfully.";
    } else {
        echo "Registration failed.";
    }
}
?>