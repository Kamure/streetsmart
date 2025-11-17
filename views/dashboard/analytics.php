 <?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
  header('Location: ../login.php');
  exit;
}

$seller_id = $_SESSION['user']['id'];

// Sales by day (last 7 days)
$stmt = $pdo->prepare("SELECT DATE(created_at) as day, SUM(total) as sales FROM orders WHERE shop_id = (SELECT id FROM shops WHERE user_id = ?) GROUP BY day ORDER BY day DESC LIMIT 7");
$stmt->execute([$seller_id]);
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sales_labels = array_reverse(array_column($sales_data, 'day'));
$sales_values = array_reverse(array_map('floatval', array_column($sales_data, 'sales')));

// Order status distribution
$stmt2 = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders WHERE shop_id = (SELECT id FROM shops WHERE user_id = ?) GROUP BY status");
$stmt2->execute([$seller_id]);
$status_data = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$status_labels = array_column($status_data, 'status');
$status_values = array_map('intval', array_column($status_data, 'count'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics Dashboard | StreetSmart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../../assets/js/analytics_charts.js"></script>
</head>
<body>
<div class="container mt-5">
  <h2 class="fw-bold text-primary mb-4">Analytics Dashboard</h2>
  <div class="row g-4">
    <div class="col-md-7">
      <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Sales (Last 7 Days)</h5>
        <canvas id="salesChart" height="120"></canvas>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Order Status</h5>
        <canvas id="ordersPie" height="120"></canvas>
      </div>
    </div>
  </div>
</div>
<script>
// Sales chart
const salesLabels = <?php echo json_encode($sales_labels); ?>;
const salesData = <?php echo json_encode($sales_values); ?>;
const salesCtx = document.getElementById('salesChart').getContext('2d');
renderSalesChart(salesCtx, salesLabels, salesData);
// Orders pie chart
const statusLabels = <?php echo json_encode($status_labels); ?>;
const statusData = <?php echo json_encode($status_values); ?>;
const ordersPieCtx = document.getElementById('ordersPie').getContext('2d');
renderOrdersPie(ordersPieCtx, statusLabels, statusData);
</script>
</body>
</html>
