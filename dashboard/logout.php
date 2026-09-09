<?php
require_once '../includes/db_connect.php';

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['membership_id']);
unset($_SESSION['membership_plan']);
unset($_SESSION['member_email_otp']);
unset($_SESSION['recovery_request']);

// Only destroy session if admin is not currently logged in
if (!isset($_SESSION['admin_id'])) {
    session_destroy();
}

header("Location: ../login/");
exit();
?>