<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | StreetSmart Market</title>

  <link rel="icon" type="image/png" href="../assets/images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="register-body">

  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card register-card shadow-lg p-4 rounded-4">
      <div class="text-center mb-4">
        <img src="../assets/images/logo.png" alt="StreetSmart Market" class="logo mb-3">
        <h3 class="fw-bold text-primary">Create Your Account</h3>
        <p class="text-muted">Join StreetSmart Market today</p>
      </div>

<<<<<<< HEAD
<<<<<<< HEAD
      <form action="../controllers/register.php" method="POST">
=======
      <form action="../controllers/register_controller.php" method="POST"> 
>>>>>>> 174be5e7663f25d51d704660b45baee4a07b1cfd
=======
      <form action="../controllers/register_controller.php" method="POST"> 
>>>>>>> 174be5e7663f25d51d704660b45baee4a07b1cfd
        <div class="mb-3">
          <label for="name" class="form-label fw-semibold">Full Name</label>
          <input type="text" class="form-control" id="name" name="name">
        </div>

        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Email</label>
          <input type="email" class="form-control" id="email" name="email">
        </div>

        <div class="mb-3">
         <label for="phone" class="form-label fw-semibold">Phone Number</label>
         <input type="text" class="form-control" id="phone" name="phone" maxlength="10" placeholder="Enter your 10 digit phone number" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="mb-3">
          <label for="role" class="form-label fw-semibold">Register As</label>
          <select name="role" id="role" class="form-select">
            <option value="customer">Customer</option>
            <option value="seller">Seller</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold">Sign Up</button>

        <div class="text-center mt-3">
          <p class="small">Already have an account? 
            <a href="login.php" class="text-decoration-none text-primary fw-semibold">Login</a>
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
