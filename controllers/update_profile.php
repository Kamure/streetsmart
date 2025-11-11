<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$skills = trim($_POST['skills'] ?? '');
$avatar_path = null;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $newFileName = "avatar_{$user_id}." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
        $avatar_path = $newFileName;
    }
}

$sql = "UPDATE users SET name = ?, email = ?, bio = ?, skills = ?";
$params = [$name, $email, $bio, $skills];

if ($avatar_path) {
    $sql .= ", avatar_path = ?";
    $params[] = $avatar_path;
}

$sql .= " WHERE id = ?";
$params[] = $user_id;

$stmt = $pdo->prepare($sql);

if ($stmt->execute($params)) {
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    if ($avatar_path) $_SESSION['user']['avatar_path'] = $avatar_path;

    header("Location: ../views/profile.php?success=1");
    exit;
} else {
    echo "Failed to update profile. Please try again.";
}
