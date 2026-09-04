<?php
// membership/process_wallet.php
// Handles 1-click membership activation via Account Balance / Wallet

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php?redirect=" . urlencode("../membership/"));
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$plans = [
    'General' => ['name' => 'সাধারণ পাঠক', 'price' => 500],
    'BookLover' => ['name' => 'নিয়মিত পাঠক', 'price' => 1000],
    'Collector' => ['name' => 'সাহিত্য অনুরাগী', 'price' => 1500]
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf_token)) {
    die("Security verification failed (CSRF token invalid). Please try again.");
}

$plan_key = trim($_POST['plan'] ?? 'General');
if (!isset($plans[$plan_key])) {
    die("Invalid plan selected.");
}

$selected_plan = $plans[$plan_key];
$price = (float)$selected_plan['price'];

$trx_id = 'WAL-MEM-' . $user_id . '-' . time();

try {
    $pdo->beginTransaction();

    // Lock and check member row
    $stmt = $pdo->prepare("SELECT id, full_name, email, membership_id, membership_plan, acc_balance, plan_expire_date FROM members WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        $pdo->rollBack();
        die("User account not found.");
    }

    $current_balance = (float)$member['acc_balance'];
    if ($current_balance < $price) {
        $pdo->rollBack();
        header("Location: request.php?plan=" . urlencode($plan_key) . "&error=insufficient_balance");
        exit();
    }

    // 1. Deduct from balance
    $deductStmt = $pdo->prepare("UPDATE members SET acc_balance = acc_balance - ? WHERE id = ?");
    $deductStmt->execute([$price, $user_id]);

    // 2. Smart Expiration Extension
    $new_expire_sql = "NOW() + INTERVAL 30 DAY";
    if (!empty($member['plan_expire_date']) && strtotime($member['plan_expire_date']) > time()) {
        $new_expire_sql = "plan_expire_date + INTERVAL 30 DAY";
    }

    // 3. Update Plan & Expiry
    $updMember = $pdo->prepare("UPDATE members SET membership_plan = ?, plan_expire_date = {$new_expire_sql} WHERE id = ?");
    $updMember->execute([$plan_key, $user_id]);

    // 4. Record confirmed request
    $insReq = $pdo->prepare("INSERT INTO membership_requests (member_id, plan, payment_method, trx_id, amount, status, created_at) VALUES (?, ?, 'Wallet', ?, ?, 'Confirmed', NOW())");
    $insReq->execute([$user_id, $plan_key, $trx_id, $price]);
    $req_id = (int)$pdo->lastInsertId();

    // 5. Record Transaction
    $insTrx = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id, created_at) VALUES (?, ?, 'Purchase', ?, ?, NOW())");
    $insTrx->execute([
        $user_id,
        $price,
        "Membership Subscription (Wallet): " . $selected_plan['name'],
        $trx_id
    ]);

    $pdo->commit();

    // Update active session plan
    $_SESSION['membership_plan'] = $plan_key;

    // Fetch updated expiration date for notification
    $dateStmt = $pdo->prepare("SELECT plan_expire_date FROM members WHERE id = ?");
    $dateStmt->execute([$user_id]);
    $updDate = $dateStmt->fetchColumn();

    // Send notification email
    if (!empty($member['email'])) {
        try {
            $notif_data = [
                'name' => $member['full_name'],
                'membership_id' => $member['membership_id'],
                'plan_name' => $selected_plan['name'],
                'expire_date' => date('d M, Y', strtotime($updDate)),
                'amount' => $price,
                'payment_method' => 'Wallet Balance'
            ];
            send_notification_instantly($member['email'], 'membership_activated', $notif_data);
        } catch (Exception $e) {
            error_log("Wallet Membership Email Error: " . $e->getMessage());
        }
    }

    // Redirect to success receipt
    header("Location: sslcommerz_return.php?status=success&plan=" . urlencode($plan_key) . "&tran_id=" . urlencode($trx_id) . "&req_id=" . urlencode($req_id), true, 303);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Wallet Membership Error: " . $e->getMessage());
    die("An unexpected error occurred. Please try again.");
}
