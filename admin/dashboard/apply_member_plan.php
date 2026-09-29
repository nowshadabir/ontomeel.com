<?php
// admin/dashboard/apply_member_plan.php
// Admin handler to manually assign, update, extend or revoke membership plans for members

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/notification_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'অননুমোদিত অ্যাক্সেস। অনুগ্রহ করে লগইন করুন।']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'শুধুমাত্র POST রিকোয়েস্ট গ্রহণযোগ্য।']);
    exit();
}

$member_id = (int)($_POST['member_id'] ?? 0);
$plan = trim($_POST['plan'] ?? 'None');
$duration = trim($_POST['duration'] ?? '1_year');
$custom_date = trim($_POST['custom_date'] ?? '');
$notify_email = !empty($_POST['notify_email']);

if ($member_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ মেম্বার আইডি।']);
    exit();
}

$valid_plans = ['None', 'General', 'BookLover', 'Collector', 'Student'];
if (!in_array($plan, $valid_plans, true)) {
    echo json_encode(['success' => false, 'message' => 'অবৈধ মেম্বারশিপ প্ল্যান নির্বাচিত হয়েছে।']);
    exit();
}

$plan_names = [
    'General' => 'সাধারণ পাঠক (General)',
    'BookLover' => 'নিয়মিত পাঠক (BookLover)',
    'Collector' => 'সাহিত্য অনুরাগী (Collector)',
    'Student' => 'স্টুডেন্ট মেম্বার (বইয়ের আনন্দ-পাঠ)',
    'None' => 'ফ্রি অ্যাকাউন্ট (কোনো প্ল্যান নেই)'
];

try {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'মেম্বার খুঁজে পাওয়া যায়নি।']);
        exit();
    }

    $new_expire_date = null;

    if ($plan !== 'None') {
        if ($duration === 'custom' && !empty($custom_date)) {
            $parsed_time = strtotime($custom_date);
            if ($parsed_time === false || $parsed_time < time() - 86400) {
                echo json_encode(['success' => false, 'message' => 'অনুগ্রহ করে সঠিক ভবিষ্যৎ মেয়াদোত্তীর্ণের তারিখ নির্বাচন করুন।']);
                exit();
            }
            $new_expire_date = date('Y-m-d 23:59:59', $parsed_time);
        } else {
            $interval_map = [
                '1_month' => '+1 month',
                '3_months' => '+3 months',
                '6_months' => '+6 months',
                '1_year' => '+1 year',
                '2_years' => '+2 years'
            ];
            $int_val = $interval_map[$duration] ?? '+1 year';

            // If same active plan already, extend from current expiration; otherwise from now
            $base_time = time();
            if ($plan === 'Student') {
                if (!empty($member['student_plan_expire_date']) && strtotime($member['student_plan_expire_date']) > time()) {
                    $base_time = strtotime($member['student_plan_expire_date']);
                }
            } else {
                if ($member['membership_plan'] === $plan && !empty($member['plan_expire_date']) && strtotime($member['plan_expire_date']) > time()) {
                    $base_time = strtotime($member['plan_expire_date']);
                }
            }
            $new_expire_date = date('Y-m-d 23:59:59', strtotime($int_val, $base_time));
        }
    }

    $pdo->beginTransaction();

    if ($plan === 'None') {
        $upd = $pdo->prepare("UPDATE members SET membership_plan = 'None', plan_expire_date = NULL, student_plan_expire_date = NULL WHERE id = ?");
        $upd->execute([$member_id]);
    } elseif ($plan === 'Student') {
        $upd = $pdo->prepare("UPDATE members SET membership_plan = 'Student', student_plan_expire_date = ? WHERE id = ?");
        $upd->execute([$new_expire_date, $member_id]);
    } else {
        $upd = $pdo->prepare("UPDATE members SET membership_plan = ?, plan_expire_date = ? WHERE id = ?");
        $upd->execute([$plan, $new_expire_date, $member_id]);
    }

    // Record administrative transaction/activity log
    $admin_name = $_SESSION['admin_full_name'] ?? $_SESSION['admin_username'] ?? 'Admin';
    $desc = "Admin ({$admin_name}) assigned membership: " . ($plan_names[$plan] ?? $plan);
    if ($new_expire_date) {
        $desc .= " (Exp: " . date('d M Y', strtotime($new_expire_date)) . ")";
    }

    $insTrx = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id, created_at) VALUES (?, 0.00, 'Purchase', ?, ?, NOW())");
    $insTrx->execute([$member_id, $desc, 'ADMIN-MANUAL-' . time()]);

    $pdo->commit();

    // Send email notification if requested and email is present
    if ($notify_email && !empty($member['email']) && $plan !== 'None') {
        try {
            $notif_data = [
                'name' => $member['full_name'],
                'membership_id' => $member['membership_id'] ?: 'OM-' . str_pad($member_id, 4, '0', STR_PAD_LEFT),
                'plan_name' => $plan_names[$plan] ?? $plan,
                'expire_date' => $new_expire_date ? date('d M, Y', strtotime($new_expire_date)) : 'অনির্দিষ্ট',
                'amount' => '0.00',
                'payment_method' => 'ADMIN DIRECT'
            ];
            if ($plan === 'Student') {
                send_notification_instantly($member['email'], 'student_membership_approved', $notif_data);
            } else {
                send_notification_instantly($member['email'], 'membership_activated', $notif_data);
            }
        } catch (Exception $e) {
            error_log("Admin Apply Membership Email Notification Error: " . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'মেম্বারশিপ প্ল্যান সফলভাবে আপডেট করা হয়েছে।',
        'member_id' => $member_id,
        'plan' => $plan,
        'plan_name' => $plan_names[$plan] ?? $plan,
        'expire_date' => $new_expire_date ? date('d M, Y', strtotime($new_expire_date)) : null
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Apply Member Plan Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
