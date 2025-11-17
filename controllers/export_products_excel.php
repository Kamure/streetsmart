<?php
session_start();
require_once '../config/database.php';
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') { header('Location: ../views/login.php'); exit; }

$seller_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.category, p.price, p.stock, p.updated_at
    FROM products p
    JOIN shops s ON p.shop_id = s.id
    WHERE s.user_id = ?
    ORDER BY p.updated_at DESC
");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Products');

$headers = ['ID','Name','Category','Price','Stock','Updated'];
foreach ($headers as $i => $h) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1);
    $sheet->setCellValue($colLetter.'1', $h);
}

$row = 2;
foreach ($products as $p) {
    $sheet->setCellValue('A'.$row, $p['id']);
    $sheet->setCellValue('B'.$row, $p['name']);
    $sheet->setCellValue('C'.$row, $p['category']);
    $sheet->setCellValue('D'.$row, $p['price']);
    $sheet->setCellValue('E'.$row, $p['stock']);
    $sheet->setCellValue('F'.$row, $p['updated_at']);
    $row++;
}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="products_report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;