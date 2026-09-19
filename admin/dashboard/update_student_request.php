<?php
// admin/dashboard/update_student_request.php
// Admin handler for Approving and Rejecting Student Membership requests

session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'অননুমোদিত অ্যাক্সেস। অনুগ্রহ করে লগইন করুন।']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'অবৈধ রিকোয়েস্ট মেথড।']);
    exit();
}

$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : null;
$action = trim($_POST['action'] ?? '');
$reason = trim($_POST['reason'] ?? '');

if (!$request_id || !in_array($action, ['approve', 'reject'], true)) {
    echo json_encode(['success' => false, 'message' => 'প্রয়োজনীয় তথ্য পাওয়া যায়নি।']);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT sr.*, m.full_name, m.email, m.phone, m.membership_id, m.membership_plan, m.plan_expire_date, m.student_plan_expire_date 
                          FROM student_membership_requests sr 
                          JOIN members m ON sr.member_id = m.id 
                          WHERE sr.id = ? FOR UPDATE");
    $stmt->execute([$request_id]);
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'আবেদনটি খুঁজে পাওয়া যায়নি।']);
        exit();
    }

    if ($action === 'approve') {
        // Update request record
        $updReq = $pdo->prepare("UPDATE student_membership_requests SET status = 'Approved', rejection_reason = NULL, updated_at = NOW() WHERE id = ?");
        $updReq->execute([$request_id]);

        // Calculate student plan expire date: +1 year (extend if currently active)
        $new_student_expire = "NOW() + INTERVAL 1 YEAR";
        if (!empty($req['student_plan_expire_date']) && strtotime($req['student_plan_expire_date']) > time()) {
            $new_student_expire = "student_plan_expire_date + INTERVAL 1 YEAR";
        }

        // Update student_plan_expire_date without overwriting existing paid plan
        $pdo->prepare("UPDATE members SET student_plan_expire_date = {$new_student_expire} WHERE id = ?")
            ->execute([$req['member_id']]);

        // If member doesn't have an active paid plan, set membership_plan indicator to Student
        if (empty($req['membership_plan']) || in_array($req['membership_plan'], ['None', 'Student'], true)) {
            $pdo->prepare("UPDATE members SET membership_plan = 'Student' WHERE id = ?")
                ->execute([$req['member_id']]);
        }

        // Record in transactions table (0 amount free grant)
        $insTrx = $pdo->prepare("INSERT INTO transactions (member_id, amount, type, description, reference_id, created_at) VALUES (?, 0.00, 'Purchase', 'Student Membership (বইয়ের আনন্দ-পাঠ) Approved', ?, NOW())");
        $insTrx->execute([$req['member_id'], 'STUDENT-REQ-' . $request_id]);

        $pdo->commit();

        // Fetch updated student expiration date for email
        $dateStmt = $pdo->prepare("SELECT student_plan_expire_date FROM members WHERE id = ?");
        $dateStmt->execute([$req['member_id']]);
        $new_date = $dateStmt->fetchColumn();

        // Send activation email
        if (!empty($req['email'])) {
            try {
                require_once __DIR__ . '/../../includes/notification_helper.php';
                $notif_data = [
                    'name' => $req['full_name'],
                    'membership_id' => $req['membership_id'],
                    'institution_name' => $req['institution_name'],
                    'expire_date' => date('d M, Y', strtotime($new_date))
                ];
                send_notification_instantly($req['email'], 'student_membership_approved', $notif_data);
            } catch (Exception $e) {
                error_log("Student Approval Email Error: " . $e->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "শিক্ষার্থী '{$req['full_name']}'-এর ‘বইয়ের আনন্দ-পাঠ’ মেম্বারশিপ সফলভাবে অনুমোদিত হয়েছে।"
        ]);
        exit();

    } elseif ($action === 'reject') {
        $updReq = $pdo->prepare("UPDATE student_membership_requests SET status = 'Rejected', rejection_reason = ?, updated_at = NOW() WHERE id = ?");
        $updReq->execute([$reason ?: 'তথ্য সঠিক নয় বা বয়সসীমা বহির্ভূত।', $request_id]);

        $pdo->commit();

        // Send rejection email
        if (!empty($req['email'])) {
            try {
                require_once __DIR__ . '/../../includes/notification_helper.php';
                $notif_data = [
                    'name' => $req['full_name'],
                    'reason' => $reason ?: 'স্টুডেন্ট আইডি বা তথ্য যাচাইয়ে অসঙ্গতি থাকায় আবেদনটি বাতিল করা হয়েছে।'
                ];
                send_notification_instantly($req['email'], 'student_membership_rejected', $notif_data);
            } catch (Exception $e) {
                error_log("Student Rejection Email Error: " . $e->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "শিক্ষার্থী '{$req['full_name']}'-এর আবেদন বাতিল করা হয়েছে।"
        ]);
        exit();
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Update Student Request Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'সার্ভার ত্রুটি: ' . $e->getMessage()]);
    exit();
}
