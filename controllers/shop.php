<?php
require_once '../config/database.php';
require_once '../models/Shop.php';

$shopModel = new Shop($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $location = $_POST['location'];

    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); 
        }

        $filename = basename($_FILES['logo']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            $logo_path = 'uploads/' . $filename;

            
            $shopModel->create($user_id, $name, $category, $location, $logo_path);
            echo "Shop created successfully.";
        } else {
            echo "Failed to upload logo.";
        }
    } else {
        echo "Logo file missing or upload error.";
    }
}
?>