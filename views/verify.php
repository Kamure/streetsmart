<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Account | StreetSmart Market</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="verify-body">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card p-4 shadow rounded-4" style="max-width: 400px; width: 100%;">
    <h4 class="text-center mb-3 text-primary fw-bold">Verify Your Account</h4>
    <p class="text-muted text-center">Enter the 6-digit OTP sent to your email</p>

    <form method="POST" action="../controllers/verify_controller.php">
      <div class="mb-3">
        <label for="otp" class="form-label">OTP Code</label>
        <input type="text" class="form-control" id="otp" name="otp" maxlength="6" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Verify</button>
    </form>
  </div>
</div>

</body>
</html>