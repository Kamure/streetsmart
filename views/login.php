<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | StreetSmart Market</title>

  <link rel="icon" type="image/png" href="../assets/images/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">

</head>
<body class="login-body">

  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card login-card shadow-lg p-4 rounded-4">
      <div class="text-center mb-4">
        <img src="../assets/images/logo.png" alt="StreetSmart Market" class="logo mb-3">
        <h3 class="fw-bold text-primary">Welcome Back</h3>
        <p class="text-muted">Login to continue shopping or selling</p>
      </div>

      <form action="../controllers/login_controller.php" method="POST"> 
        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Email</label>
          <input type="email" class="form-control" id="email" name="email">
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <input type="password" class="form-control" id="password" name="password">
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold">Login</button>

        <div class="text-center mt-3">
          <p class="small">Don't have an account? 
            <a href="register.php" class="text-decoration-none text-primary fw-semibold">Register</a>
          </p>
        </div>
      </form>
    </div>
  </div>

  <footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
