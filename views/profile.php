<?php 
session_start();
require_once '../config/database.php';

// Only sellers can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user']['id'];
$name = $_SESSION['user']['name'];
$email = $_SESSION['user']['email'];

// Fetch profile info
$stmt = $pdo->prepare("SELECT bio, skills, avatar_path FROM users WHERE id = ?");
$stmt->execute([$seller_id]);
$profile = $stmt->fetch();

$bio = $profile['bio'] ?? '';
$skills = $profile['skills'] ?? '';
$avatar = $profile['avatar_path'] ?? 'default-avatar.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Profile | StreetSmart Market</title>
  <link rel="icon" type="image/png" href="../assets/images/favicon.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body class="seller-profile-body">
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center" href="seller.php">
        <img src="../assets/images/logo.png" alt="StreetSmart" class="navbar-logo" width="40" height="40" />
        <span class="fw-bold">StreetSmart Seller</span>
      </a>
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-3">
          <a href="profile.php" class="nav-link active">
            <img src="../uploads/<?= htmlspecialchars($avatar) ?>" alt="Profile" class="profile-img" width="40" height="40"/>
          </a>
        </li>
        <li class="nav-item">
          <a href="../controllers/logout_controller.php" class="btn btn-light btn-sm fw-semibold px-3">Logout</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container mt-5 pt-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card profile-card p-4 shadow-sm">
          <div class="text-center mb-4">
            <img src="../uploads/<?= htmlspecialchars($avatar) ?>" alt="Profile Picture" class="profile-avatar mb-3" width="120" height="120" />
            <h4 class="fw-bold text-primary"><?= htmlspecialchars($name) ?></h4>
            <p class="text-muted mb-1">Seller, StreetSmart Market</p>
            <p class="small text-secondary">"Turning creativity into commerce"</p>
          </div>

          <hr>

          <h5 class="fw-bold text-primary mb-3">Edit Profile</h5>
          <form action="../controllers/update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label fw-semibold">Full Name</label>
              <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Bio</label>
              <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($bio) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Skills</label>
              <input type="text" class="form-control" name="skills" value="<?= htmlspecialchars($skills) ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Profile Picture</label>
              <input type="file" class="form-control" name="avatar" accept="image/*">
            </div>

            <div class="text-end">
              <button class="btn btn-success px-4 py-2 rounded-3">Save Changes</button>
            </div>
          </form>

          <hr>

          <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="dashboard/seller.php" class="btn btn-primary">Manage Products</a>
            <a href="seller_reviews.php?seller_id=<?= $seller_id ?>" class="btn btn-outline-primary">View Ratings</a>
          </div>

        </div>
      </div>
    </div>
  </div>

  <footer class="text-center mt-5 mb-3 text-muted small">
    &copy; <?= date('Y'); ?> StreetSmart Market. All rights reserved.
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

