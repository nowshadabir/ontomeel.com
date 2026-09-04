<?php
session_start();
include '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$request_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM membership_requests WHERE id = ? FOR UPDATE");
    $stmt->execute([$request_id]);
    $req = $stmt->fetch();

    if (!$req) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit();
    }

    $plan_names = [
        'General' => 'সাধারণ পাঠক',
        'BookLover' => 'নিয়মিত পাঠক',
        'Collector' => 'সাহিত্য অনুরাগী'
    ];

    if ($action === 'confirm') {
        $pdo->prepare("UPDATE membership_requests SET status = 'Confirmed' WHERE id = ?")
            ->execute([$request_id]);

        // Smart Expiration Logic: Preserve remaining time if subscription is still active
        $stmt = $pdo->prepare("SELECT full_name, email, membership_id, membership_plan, plan_expire_date FROM members WHERE id = ?");
        $stmt->execute([$req['member_id']]);
        $member = $stmt->fetch();

        $new_expire = "NOW() + INTERVAL 30 DAY";
        if ($member && !empty($member['plan_expire_date'])) {
            if (strtotime($member['plan_expire_date']) > time()) {
                $new_expire = "plan_expire_date + INTERVAL 30 DAY";
            }
        }

        $pdo->prepare("UPDATE members SET membership_plan = ?, plan_expire_date = {$new_expire} WHERE id = ?")
            ->execute([$req['plan'], $req['member_id']]);

        // Record in transactions table
        $insTrx = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id, created_at) VALUES (?, ?, 'Purchase', ?, ?, NOW())");
        $insTrx->execute([
            $req['member_id'],
            $req['amount'],
            "Membership Subscription (" . strtoupper($req['payment_method']) . "): " . ($plan_names[$req['plan']] ?? $req['plan']),
            $req['trx_id']
        ]);

        $pdo->commit();

        // Send activation email notification
        if ($member && !empty($member['email'])) {
            try {
                require_once __DIR__ . '/../../includes/notification_helper.php';
                $dateStmt = $pdo->prepare("SELECT plan_expire_date FROM members WHERE id = ?");
                $dateStmt->execute([$req['member_id']]);
                $updDate = $dateStmt->fetchColumn();

                $notif_data = [
                    'name' => $member['full_name'],
                    'membership_id' => $member['membership_id'],
                    'plan_name' => $plan_names[$req['plan']] ?? $req['plan'],
                    'expire_date' => date('d M, Y', strtotime($updDate)),
                    'amount' => $req['amount'],
                    'payment_method' => strtoupper($req['payment_method'])
                ];
                send_notification_instantly($member['email'], 'membership_activated', $notif_data);
            } catch (Exception $e) {
                error_log("Admin Membership Confirmation Email Error: " . $e->getMessage());
            }
        }
    } elseif ($action === 'cancel') {
        $pdo->prepare("UPDATE membership_requests SET status = 'Cancelled' WHERE id = ?")
            ->execute([$request_id]);

        $pdo->commit();

        // Send cancellation email notification
        $stmt = $pdo->prepare("SELECT full_name, email FROM members WHERE id = ?");
        $stmt->execute([$req['member_id']]);
        $member = $stmt->fetch();

        if ($member && !empty($member['email'])) {
            try {
                require_once __DIR__ . '/../../includes/notification_helper.php';
                $notif_data = [
                    'name' => $member['full_name'],
                    'plan_name' => $plan_names[$req['plan']] ?? $req['plan']
                ];
                send_notification_instantly($member['email'], 'membership_cancelled', $notif_data);
            } catch (Exception $e) {
                error_log("Admin Membership Cancel Email Error: " . $e->getMessage());
            }
        }
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
