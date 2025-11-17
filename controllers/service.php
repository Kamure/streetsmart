<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seller_id = $_SESSION['user']['id'];
    $action = $_POST['action'] ?? '';

    // Add Service
    if ($action === '' || $action === 'add_service') {
        $service_name = trim($_POST['service_name'] ?? '');
        $service_description = trim($_POST['service_description'] ?? '');
        $service_price = trim($_POST['service_price'] ?? '');
        if ($service_name === '' || $service_description === '' || $service_price === '' || !is_numeric($service_price)) {
            header('Location: ../views/dashboard/seller.php?msg=service_error');
            exit;
        }
        $stmt = $pdo->prepare('SELECT id FROM shops WHERE user_id = ?');
        $stmt->execute([$seller_id]);
        $shop = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$shop) {
            header('Location: ../views/dashboard/seller.php?msg=no_shop');
            exit;
        }
        $shop_id = $shop['id'];
        $stmt = $pdo->prepare('INSERT INTO services (shop_id, name, description, price, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$shop_id, $service_name, $service_description, $service_price]);
        header('Location: ../views/dashboard/seller.php?msg=service_added');
        exit;
    }

    // Delete Service
    if ($action === 'delete_service') {
        $service_id = $_POST['service_id'] ?? null;
        if ($service_id) {
            $stmt = $pdo->prepare('DELETE FROM services WHERE id = ? AND shop_id IN (SELECT id FROM shops WHERE user_id = ?)');
            $stmt->execute([$service_id, $seller_id]);
        }
        header('Location: ../views/dashboard/seller.php?msg=service_deleted');
        exit;
    }

    // Edit Service
    if ($action === 'edit_service') {
        $service_id = $_POST['service_id'] ?? null;
        $service_name = trim($_POST['service_name'] ?? '');
        $service_description = trim($_POST['service_description'] ?? '');
        $service_price = trim($_POST['service_price'] ?? '');
        if ($service_id && $service_name !== '' && $service_description !== '' && is_numeric($service_price)) {
            $stmt = $pdo->prepare('UPDATE services SET name = ?, description = ?, price = ? WHERE id = ? AND shop_id IN (SELECT id FROM shops WHERE user_id = ?)');
            $stmt->execute([$service_name, $service_description, $service_price, $service_id, $seller_id]);
            header('Location: ../views/dashboard/seller.php?msg=service_updated');
            exit;
        } else {
            header('Location: ../views/dashboard/seller.php?msg=service_error');
            exit;
        }
    }
}

header('Location: ../views/dashboard/seller.php');
exit;
