<?php
require_once __DIR__ . '/../includes/db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: /admin/overview");
} else {
    header("Location: /admin/login/");
}
exit();
