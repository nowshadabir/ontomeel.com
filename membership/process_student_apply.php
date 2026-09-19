<?php
// membership/process_student_apply.php
// Backend processor for "বইয়ের আনন্দ-পাঠ" student membership applications

session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: student-apply.php");
    exit();
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
if (!$user_id) {
    $_SESSION['student_apply_error'] = 'আবেদন করতে অনুগ্রহ করে প্রথমে লগইন করুন।';
    header("Location: ../login/index.php?redirect=" . urlencode("../membership/student-apply.php"));
    exit();
}

// Check member exists
$stmt = $pdo->prepare("SELECT id, full_name, email, phone, membership_plan FROM members WHERE id = ?");
$stmt->execute([$user_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    $_SESSION['student_apply_error'] = 'ব্যবহারকারীর তথ্য পাওয়া যায়নি।';
    header("Location: student-apply.php");
    exit();
}

// Check if user already has a pending application
$checkPending = $pdo->prepare("SELECT id FROM student_membership_requests WHERE member_id = ? AND status = 'Pending'");
$checkPending->execute([$user_id]);
if ($checkPending->fetch()) {
    $_SESSION['student_apply_error'] = 'আপনার একটি আবেদন ইতোমধ্যে পেন্ডিং রয়েছে। এডমিন পর্যালোচনার পর ফলাফল জানানো হবে।';
    header("Location: student-apply.php");
    exit();
}

$dob_str = trim($_POST['dob'] ?? '');
$institution_name = trim($_POST['institution_name'] ?? '');
$student_id_number = trim($_POST['student_id_number'] ?? '');

if (empty($dob_str) || empty($institution_name) || empty($student_id_number)) {
    $_SESSION['student_apply_error'] = 'অনুগ্রহ করে সকল আবশ্যকীয় তথ্য পূরণ করুন।';
    header("Location: student-apply.php");
    exit();
}

// Calculate and validate Age
try {
    $dob_date = new DateTime($dob_str);
    $now = new DateTime();
    $age_interval = $now->diff($dob_date);
    $age = $age_interval->y;

    if ($age < 12 || $age > 19) {
        $_SESSION['student_apply_error'] = "দুঃখিত, আপনার বয়স ({$age} বছর) এই মেম্বারশিপের জন্য প্রযোজ্য নয়। শুধুমাত্র ১২ থেকে ১৯ বছর বয়সীরা ‘বইয়ের আনন্দ-পাঠ’ প্ল্যানে আবেদন করতে পারবেন।";
        header("Location: student-apply.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['student_apply_error'] = 'জন্ম তারিখ সঠিক ফরম্যাটে প্রদান করুন।';
    header("Location: student-apply.php");
    exit();
}

// Handle File Upload
$uploaded_filename = null;
if (isset($_FILES['student_id_image']) && $_FILES['student_id_image']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['student_id_image']['tmp_name'];
    $file_name = $_FILES['student_id_image']['name'];
    $file_size = $_FILES['student_id_image']['size'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    if (!in_array($ext, $allowed_exts, true)) {
        $_SESSION['student_apply_error'] = 'শুধুমাত্র JPG, PNG, WEBP বা PDF ফাইল আপলোড করা যাবে।';
        header("Location: student-apply.php");
        exit();
    }

    if ($file_size > 5 * 1024 * 1024) { // 5MB limit
        $_SESSION['student_apply_error'] = 'ফাইলের সাইজ ৫ মেগাবাইটের কম হতে হবে।';
        header("Location: student-apply.php");
        exit();
    }

    $upload_dir = __DIR__ . '/../assets/uploads/student_ids/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $new_file_name = 'student_id_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        $uploaded_filename = 'assets/uploads/student_ids/' . $new_file_name;
    } else {
        $_SESSION['student_apply_error'] = 'ফাইল আপলোড করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।';
        header("Location: student-apply.php");
        exit();
    }
} else {
    $_SESSION['student_apply_error'] = 'স্টুডেন্ট আইডি কার্ড / পরিচয়পত্রের ছবি আপলোড করা বাধ্যতামূলক।';
    header("Location: student-apply.php");
    exit();
}

// Insert into student_membership_requests
try {
    $insertStmt = $pdo->prepare("
        INSERT INTO student_membership_requests 
        (member_id, dob, age, institution_name, student_id_number, student_id_image, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())
    ");
    $insertStmt->execute([
        $user_id,
        $dob_date->format('Y-m-d'),
        $age,
        $institution_name,
        $student_id_number,
        $uploaded_filename
    ]);

    $_SESSION['student_apply_success'] = 'আপনার ‘বইয়ের আনন্দ-পাঠ’ মেম্বারশিপ আবেদন সফলভাবে জমা হয়েছে। আমাদের এডমিন পর্যালোচনা করে দ্রুত অনুমোদন প্রদান করবেন।';
    header("Location: student-apply.php");
    exit();
} catch (Exception $e) {
    error_log("Student Apply Error: " . $e->getMessage());
    $_SESSION['student_apply_error'] = 'ডাটাবেজে আবেদন সংরক্ষণ করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
    header("Location: student-apply.php");
    exit();
}
