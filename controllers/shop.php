<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header('Location: ../views/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_shop') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $location = trim($_POST['location'] ?? '');

        if (!$name || !$category || !$location) {
            die("All fields are required.");
        }

        $logo_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'shop_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $destination = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                $logo_path = $filename;
            } else {
                die("Failed to upload logo.");
            }
        } else {
            die("Shop logo is required.");
        }

        $stmt = $pdo->prepare("INSERT INTO shops (user_id, name, category, location, logo_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$user_id, $name, $category, $location, $logo_path]);

        header('Location: ../views/dashboard/seller.php?msg=shop_created');
        exit;
    }
}
