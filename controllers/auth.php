<?php
require_once '../config/db.php';
require_once '../models/User.php';

$userModel = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $success = $userModel->create($name, $email, $phone, $password, $role);

    if ($success) {
        echo "User registered successfully.";
    } else {
        echo "Registration failed.";
    }
}
?>