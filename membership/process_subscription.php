<?php
// membership/process_subscription.php
// Deprecated insecure endpoint - permanently redirected to official payment page

require_once __DIR__ . '/../includes/db_connect.php';

$plan = trim($_POST['plan'] ?? ($_GET['plan'] ?? 'General'));
header("Location: request.php?plan=" . urlencode($plan));
exit();