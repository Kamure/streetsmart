<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StreetSmart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://kit.fontawesome.com/a2e0c56c55.js" crossorigin="anonymous"></script>
</head>
<body>

  <nav class="navbar navbar-expand-lg shadow-sm fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="../assets/images/logo.png" alt="StreetSmart Logo" width="55" height="55">
        StreetSmart Market
      </a>
      <div>
        <a href="login.php" class="btn btn-white bg-white text-primary border me-2">Login</a>
        <a href="register.php" class="btn btn-white bg-white text-primary border me-2">Sign Up</a>
      </div>
    </div>
  </nav>

  <div class="container main hero d-flex flex-column justify-content-center align-items-center text-center" style="margin-top: 120px;">
    <h2 class="home-heading mb-4">Buy & Sell Smarter in Your Street</h2>
  </div>

  <section class="features py-5">
    <div class="container">
      <div class="d-flex justify-content-center gap-4 flex-wrap">
        <div class="feature-box bg-white shadow rounded-4 p-4 text-center flex-fill" style="min-width:260px; max-width:340px;">
          <i class="fas fa-store fa-2x mb-3 text-primary"></i>
          <h5 class="fw-semibold mb-2">Local Shops</h5>
          <p class="mb-0">Discover and support small businesses near you with real-time listings.</p>
        </div>
        <div class="feature-box bg-white shadow rounded-4 p-4 text-center flex-fill" style="min-width:260px; max-width:340px;">
          <i class="fas fa-users fa-2x mb-3 text-primary"></i>
          <h5 class="fw-semibold mb-2">Trusted Community</h5>
          <p class="mb-0">Engage safely with verified sellers and customers in your neighbourhood.</p>
        </div>
        <div class="feature-box bg-white shadow rounded-4 p-4 text-center flex-fill" style="min-width:260px; max-width:340px;">
          <i class="fas fa-chart-line fa-2x mb-3 text-primary"></i>
          <h5 class="fw-semibold mb-2">Smart Insights</h5>
          <p class="mb-0">View analytics and shop performance directly from your dashboard.</p>
        </div>
      </div>
    </div>
  </section>

  <footer class="text-center">
    <small>© 2025 StreetSmart Market. All Rights Reserved.</small>
  </footer>

</body>
</html>
