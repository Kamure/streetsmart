<?php
session_start();
require_once '../config/database.php';
require_once '../models/Receipt.php';
require '../vendor/autoload.php';


if (!isset($_SESSION['user'])) { header('Location: ../views/login.php'); exit; }

$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id) die('Order ID required.');

$receiptModel = new Receipt($pdo);
$data = $receiptModel->getReceiptByOrder($order_id);
if (!$data || !$data['order']) {
  header('Location: ../views/error.php?msg=receipt_not_found');
  exit;
}

$order = $data['order'];
$items = $data['items'];

$pdf = new TCPDF();
$pdf->SetCreator('StreetSmart');
$pdf->SetAuthor('StreetSmart');
$pdf->SetTitle('Receipt - Order #' . $order['id']);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$pdf->Cell(0, 10, 'StreetSmart Market - Receipt', 0, 1, 'C');
$pdf->Ln(2);
$pdf->Cell(0, 8, 'Order #: ' . $order['id'], 0, 1);
$pdf->Cell(0, 8, 'Customer: ' . $order['customer_name'], 0, 1);
$pdf->Cell(0, 8, 'Payment: ' . $order['payment_method'] . ' (' . $order['payment_ref'] . ')', 0, 1);
$pdf->Cell(0, 8, 'Date: ' . $order['created_at'], 0, 1);
$pdf->Ln(4);

foreach ($items as $it) {
  $line = sprintf(
    '%s x%s @ %s = %s',
    $it['name'],
    $it['quantity'],
    number_format((float)($it['price'] ?? 0), 2),
    number_format((float)($it['subtotal'] ?? $it['price'] * $it['quantity']), 2)
  );
  $pdf->Cell(0, 8, $line, 0, 1);
}

$pdf->Ln(4);
$pdf->Cell(0, 10, 'Total: Ksh ' . number_format($order['total'], 2), 0, 1, 'R');

$pdf->Output('receipt_order_' . $order_id . '.pdf', 'D');