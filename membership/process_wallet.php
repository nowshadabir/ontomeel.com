<?php
// membership/process_wallet.php
// Deprecated: Wallet payment system has been removed.
session_start();

$plan = $_POST['plan'] ?? 'General';
$_SESSION['error_message'] = "ওয়ালেট পেমেন্ট সিস্টেমটি বন্ধ করা হয়েছে। অনুগ্রহ করে অনলাইন পেমেন্ট ব্যবহার করুন।";
header("Location: request.php?plan=" . urlencode($plan));
exit();
