<?php
session_start();
session_destroy();
echo "Logged out.";
session_start();
session_unset();
session_destroy();
session_start();
$_SESSION['logout_message'] = 'Logged out successfully';
header('Location: ../views/index.php');
exit();
?>