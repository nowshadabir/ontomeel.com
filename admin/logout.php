<?php
require_once '../includes/db_connect.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_full_name']);
unset($_SESSION['admin_role']);
unset($_SESSION['admin_email_otp']);
unset($_SESSION['admin_recovery_request']);

// Only destroy session if member account is not currently logged in
if (!isset($_SESSION['user_id'])) {
    session_destroy();
}

header("Location: login/index.php");
exit();
?>