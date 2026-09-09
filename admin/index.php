<?php
require_once '../includes/db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard/index.php");
} else {
    header("Location: login/index.php");
}
exit();
?>
