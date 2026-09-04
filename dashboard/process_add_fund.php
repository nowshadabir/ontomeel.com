<?php
// dashboard/process_add_fund.php
// Deprecated: Account Fund / Wallet system has been removed.
session_start();

$_SESSION['error_message'] = "তহবিল যোগ করার সুবিধাটি বন্ধ রয়েছে।";
header("Location: index.php");
exit();