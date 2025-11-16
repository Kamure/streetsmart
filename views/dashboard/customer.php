
<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!empty($_SESSION['flash_success'])) {
  echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
  unset($_SESSION['flash_success']);
}
if (!empty($_SESSION['flash_error'])) {
  echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
  unset($_SESSION['flash_error']);
}
require_once '../../config/database.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $stmtShopId = $pdo->prepare("SELECT shop_id FROM products WHERE id = ?");
    $stmtShopId->execute([$product_id]);
    $shop_id = $stmtShopId->fetchColumn();

    if (isset($_SESSION['cart'][$product_id])) {
      $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
      $_SESSION['cart'][$product_id] = [
        'id' => $product_id,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'shop_id' => $shop_id
      ];
    }
}

if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
}

$success = $error = '';

if (isset($_POST['checkout'])) {
    if (!isset($_SESSION['user'])) {
        $error = "You must be logged in to checkout.";
    } else {
        $customer_id = $_SESSION['user']['id'];
        $cart = $_SESSION['cart'];

        if (empty($cart)) {
            $error = "Your cart is empty!";
        } else {
            try {
                $pdo->beginTransaction();

                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['price'] * $item['quantity'];
                }

                $payment_method = $_POST['payment_method'] ?? 'mpesa';

          $cart_by_shop = [];
          foreach ($cart as $item) {
            $cart_by_shop[$item['shop_id']][] = $item;
          }

          $first_order_id = null;
          foreach ($cart_by_shop as $shop_id => $items) {
            $shop_total = 0;
            foreach ($items as $item) {
              $shop_total += $item['price'] * $item['quantity'];
            }
            $stmt = $pdo->prepare("INSERT INTO orders (customer_id, shop_id, total, payment_method, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())");
            $stmt->execute([$customer_id, $shop_id, $shop_total, $payment_method]);
            $order_id = $pdo->lastInsertId();
            if ($first_order_id === null) $first_order_id = $order_id;

            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
              $stmtItem->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            }
          }

          $pdo->commit();
          $_SESSION['cart'] = [];
          header("Location: receipt.php?order_id=" . $first_order_id);
          exit;

                $stmt = $pdo->prepare("
                    INSERT INTO orders (customer_id, shop_id, total, payment_method, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())
                ");
                $stmt->execute([$customer_id, $shop_id, $total, $payment_method]);
                $order_id = $pdo->lastInsertId();

                $stmtItem = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");

        foreach ($cart as $item) {
          $stmtItem->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
          
          $stmtStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
          $stmtStock->execute([$item['quantity'], $item['id'], $item['quantity']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];

        header("Location: receipt.php?order_id=" . $order_id);
        exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Checkout failed: " . $e->getMessage();
            }
        }
    }
}



$stmt = $pdo->query("SELECT p.*, s.name AS shop_name, s.location AS shop_location FROM products p JOIN shops s ON p.shop_id = s.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$customer_id = $_SESSION['user']['id'] ?? null;
if ($customer_id && !isset($_SESSION['user']['role'])) {
  $_SESSION['user']['role'] = 'customer';
}
$sellers = [];
if ($customer_id) {
    $stmt = $pdo->prepare("
      SELECT DISTINCT s.user_id AS id, s.name 
      FROM shops s 
      JOIN products p ON s.id = p.shop_id 
      JOIN order_items oi ON oi.product_id = p.id 
      JOIN orders o ON o.id = oi.order_id 
      WHERE o.customer_id = ?
    ");
    $stmt->execute([$customer_id]);
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once '../../models/review.php';
$reviewModel = new Review($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Dashboard | StreetSmart Market</title>
<link rel="icon" type="image/png" href="../../assets/images/favicon.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../../assets/images/logo.png" alt="Logo" class="me-2" width="40">
      StreetSmart Market
    </a>
    <div class="d-flex">
      <a href="profile.php" class="btn btn-white text-primary me-2" style="background-color: white; border: 1px solid white;">Profile</a>
  <a href="../../controllers/logout_controller.php" class="btn btn-white text-primary" style="background-color: white; border: 1px solid white;">Logout</a>
    </div>
  </div>
</nav>


<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary mb-0">Browse Available Products</h2>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="product-grid">
    <?php if (empty($products)): ?>
      <p class="text-center text-muted">No products available right now.</p>
    <?php else: ?>
      <?php foreach ($products as $p): ?>
      <div class="product-card">
        <img src="/streetsmart/uploads/<?= htmlspecialchars($p['image_path'] ?? 'default-product.jpg'); ?>" alt="<?= htmlspecialchars($p['name'] ?? 'Product'); ?>" width="120">
        <h4><?= htmlspecialchars($p['name']); ?></h4>
        <p class="mb-1"><span class="badge bg-secondary">Shop: <?= htmlspecialchars($p['shop_name']); ?></span></p>
        <p class="mb-1"><span class="badge bg-info text-dark">Location: <?= htmlspecialchars($p['shop_location']); ?></span></p>
        <p class="price">Ksh <?= number_format($p['price']); ?></p>
        <form method="POST">
          <input type="hidden" name="product_id" value="<?= $p['id']; ?>">
          <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
          <input type="hidden" name="price" value="<?= htmlspecialchars($p['price']); ?>">
          <label for="quantity_<?= $p['id']; ?>" class="form-label mb-1">Quantity:</label>
          <input type="number" id="quantity_<?= $p['id']; ?>" name="quantity" value="1" min="1" class="form-control mb-2" style="width: 80px;" required>
          <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm mt-2">Add to Cart</button>
        </form>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="cart-container mt-5">
  <h3 class="fw-bold mb-3">Your Cart</h3>

  <?php if (empty($_SESSION['cart'])): ?>
    <p class="text-muted">Your cart is empty.</p>
  <?php else: ?>
    <form method="POST">
      <table class="cart-table table table-bordered">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $total = 0;
          foreach ($_SESSION['cart'] as $item): 
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
          ?>
          <tr>
            <td><?= htmlspecialchars($item['name']); ?></td>
            <td>Ksh <?= number_format($item['price']); ?></td>
            <td><?= htmlspecialchars($item['quantity']); ?></td>
            <td>Ksh <?= number_format($subtotal); ?></td>
          </tr>
          <?php endforeach; ?>
          
        </tbody>
        </table>

      <div class="text-end fw-bold mb-3">Total: Ksh <?= number_format($total); ?></div>

      <div class="cart-actions mb-4">
        <form method="POST" style="display:inline;">
          <button type="submit" name="clear_cart" class="btn btn-warning">Clear Cart</button>
        </form>
      </div>

      <form method="POST">
        <h3 class="fw-bold mb-3">Checkout</h3>
        <div class="mb-3">
          <label for="customer_name" class="form-label">Full Name</label>
          <input type="text" name="customer_name" id="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="customer_email" class="form-label">Email</label>
          <input type="email" name="customer_email" id="customer_email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="customer_phone" class="form-label">Phone Number</label>
          <input type="tel" name="customer_phone" id="customer_phone" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="customer_address" class="form-label">Delivery Address</label>
          <input type="text" name="customer_address" id="customer_address" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Choose Payment Method:</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" required onclick="togglePaymentFields()">
            <label class="form-check-label" for="mpesa">M-Pesa</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" required onclick="togglePaymentFields()">
            <label class="form-check-label" for="card">Card</label>
          </div>
        </div>

        <div id="mpesa-fields" class="mb-3" style="display:none;">
          <label for="mpesa_phone" class="form-label">M-Pesa Phone Number</label>
          <input type="tel" class="form-control" id="mpesa_phone" name="mpesa_phone" length="10" pattern="^07\d{8}$">
          <button type="button" class="btn btn-primary mt-2" onclick="alert('Simulate M-Pesa payment prompt')">Pay with M-Pesa</button>
        </div>

        <div id="card-fields" class="mb-3" style="display:none;">
          <label for="card_number" class="form-label">Card Number</label>
          <input type="text" class="form-control mb-2" id="card_number" name="card_number" maxlength="19" placeholder="Card Number">
          <label for="card_expiry" class="form-label">Expiry Date</label>
          <input type="text" class="form-control mb-2" id="card_expiry" name="card_expiry" maxlength="5" placeholder="MM/YY">
          <label for="card_cvc" class="form-label">CVC</label>
          <input type="text" class="form-control" id="card_cvc" name="card_cvc" maxlength="4" placeholder="CVC">
        </div>

        <button type="submit" name="checkout" class="btn btn-success">Complete Payment</button>
        
    <div class="mt-3">
        <a href="../controllers/export_receipt_pdf.php?order_id=<?= htmlspecialchars($order['id']) ?>" 
           class="btn btn-outline-primary">Download Receipt (PDF)</a>
    </div>
</div>
      </form>
  <?php endif; ?>
</div>

<script>
function togglePaymentFields() {
  document.getElementById('mpesa-fields').style.display = document.getElementById('mpesa').checked ? 'block' : 'none';
  document.getElementById('card-fields').style.display = document.getElementById('card').checked ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', togglePaymentFields);
</script>

  <div class="rate-seller-form mt-5">
    <h3 class="fw-bold mb-3">Rate a Seller (Optional)</h3>
    <?php if ($customer_id && $sellers): ?>
      <form action="../../controllers/review.php" method="POST">
        <div class="mb-3">
          <label for="seller_id" class="form-label">Select Seller</label>
          <select name="seller_id" id="seller_id" class="form-select" required>
            <option value="">Choose seller...</option>
            <?php foreach ($sellers as $seller): ?>
              <option value="<?= $seller['id'] ?>"><?= htmlspecialchars($seller['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="rating" class="form-label">Rating (1–5)</label>
          <select name="rating" id="rating" class="form-select" required>
            <option value="">Select...</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="comment" class="form-label">Comment (optional)</label>
          <textarea name="comment" id="comment" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Rating</button>
      </form>
    <?php elseif (!$customer_id): ?>
      <p class="text-muted">Log in to rate sellers.</p>
    <?php else: ?>
      <p class="text-muted">You have not purchased from any sellers yet.</p>
    <?php endif; ?>
  </div>

  <div class="contact-seller-form mt-5">
    <h3 class="fw-bold mb-3">Contact a Seller (Call)</h3>
    <?php if ($customer_id && $sellers): ?>
      <form method="GET">
        <div class="mb-3">
          <label for="call_seller_id" class="form-label">Select Seller</label>
          <select name="seller_id" id="call_seller_id" class="form-select" required onchange="this.form.submit()">
            <option value="">Choose seller...</option>
            <?php foreach ($sellers as $seller): ?>
              <option value="<?= $seller['id'] ?>" <?= (isset($_GET['seller_id']) && $_GET['seller_id'] == $seller['id']) ? 'selected' : '' ?>><?= htmlspecialchars($seller['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
      <?php
      if (isset($_GET['seller_id']) && $_GET['seller_id']) {
        $sid = intval($_GET['seller_id']);
        $stmt = $pdo->prepare("SELECT phone, name FROM users WHERE id = ? AND role = 'seller'");
        $stmt->execute([$sid]);
        $seller = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($seller && !empty($seller['phone'])) {
          echo '<div class="alert alert-info">Call ' . htmlspecialchars($seller['name']) . ' at <a href="tel:' . htmlspecialchars($seller['phone']) . '">' . htmlspecialchars($seller['phone']) . '</a></div>';
        } elseif ($seller) {
          echo '<div class="alert alert-warning">This seller has not provided a phone number.</div>';
        }
      }
      ?>
    <?php elseif (!$customer_id): ?>
      <p class="text-muted">Log in to contact sellers.</p>
    <?php else: ?>
      <p class="text-muted">You have not purchased from any sellers yet.</p>
    <?php endif; ?>
  </div>
</div>

<footer>
  &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
