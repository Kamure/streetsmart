<?php
require_once '../config/database.php';
require_once '../models/Receipt.php';

$receipt = new Receipt($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['order_id'])) {
    $data = $receipt->getReceiptByOrder($_GET['order_id']);
    echo json_encode($data);
}
?>