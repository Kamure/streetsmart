<?php
function sendNotification($email, $message) {
    mail($email, "Order Update", $message);
}
?>