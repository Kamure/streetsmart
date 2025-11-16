<?php
session_start();
require_once '../config/database.php';
require '../vendor/autoload.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') { header('Location: ../views/login.php'); exit; }

$seller_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.status, o.created_at, u.name AS customer_name
    FROM orders o
    JOIN shops s ON o.shop_id = s.id
    JOIN users u ON o.customer_id = u.id
    WHERE s.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$seller_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new TCPDF();
$pdf->SetTitle('StreetSmart - Orders Report');
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Orders Report', 0, 1, 'C');
$pdf->Ln(4);

foreach ($orders as $o) {
    $line = sprintf('Order #%s | Customer: %s | Total: Ksh %s | Status: %s | Date: %s',
        $o['id'], $o['customer_name'], number_format((float)($o['total'] ?? 0), 2), ucfirst($o['status']), $o['created_at']);
    $pdf->Cell(0, 8, $line, 0, 1);
}

$pdf->Output('orders_report.pdf', 'D');