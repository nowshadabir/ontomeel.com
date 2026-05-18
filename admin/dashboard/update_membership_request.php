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

    if ($action === 'confirm') {
        $pdo->prepare("UPDATE membership_requests SET status = 'Confirmed' WHERE id = ?")
            ->execute([$request_id]);

        // Smart Expiration Logic: Preserve remaining time if subscription is still active
        $stmt = $pdo->prepare("SELECT membership_plan, plan_expire_date FROM members WHERE id = ?");
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
    } elseif ($action === 'cancel') {
        $pdo->prepare("UPDATE membership_requests SET status = 'Cancelled' WHERE id = ?")
            ->execute([$request_id]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
