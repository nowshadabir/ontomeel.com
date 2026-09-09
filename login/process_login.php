<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/security_helper.php';

function normalize_login_id($input) {
    $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    $en_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($bn_digits, $en_digits, trim($input));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check: 10 attempts per 5 minutes
    if (!check_rate_limit('login', 10, 300)) {
        log_security_event('rate_limit_exceeded', ['identifier' => 'login']);
        $redirect = trim($_POST['redirect'] ?? ($_GET['redirect'] ?? ''));
        $error_redirect = "index.php?error=rate_limit" . (!empty($redirect) ? '&redirect=' . urlencode($redirect) : '');
        header("Location: " . $error_redirect);
        exit();
    }

    $raw_login_id = $_POST['login_id'] ?? '';
    $login_id = normalize_login_id($raw_login_id);
    $password = $_POST['password'] ?? '';
    $redirect = trim($_POST['redirect'] ?? ($_GET['redirect'] ?? ''));

    if (empty($login_id) || empty($password)) {
        $error_redirect = "index.php?error=empty" . (!empty($redirect) ? '&redirect=' . urlencode($redirect) : '');
        header("Location: " . $error_redirect);
        exit();
    }

    // Prepare normalized phone format (strip spaces, dashes, country code prefix)
    $phone_cleaned = preg_replace('/[^0-9]/', '', $login_id);
    if (strlen($phone_cleaned) === 13 && strpos($phone_cleaned, '880') === 0) {
        $phone_cleaned = substr($phone_cleaned, 2);
    }

    try {
        // Find member by Email, Phone (raw or cleaned), or Membership ID
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? OR phone = ? OR phone = ? OR membership_id = ?");
        $stmt->execute([$login_id, $login_id, $phone_cleaned, $login_id]);
        $user = $stmt->fetch();

        if ($user) {
            $password_valid = false;

            // Verify password using bcrypt
            if (password_verify($password, $user['password'])) {
                $password_valid = true;

                // Upgrade hash if needed
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $user['id']]);
                }
            }

            if ($password_valid) {
                // Clear rate limiting counter on success
                clear_rate_limit('login');

                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['membership_id'] = $user['membership_id'];
                $_SESSION['last_activity'] = time();
                $_SESSION['created_at'] = time();

                // Synchronize membership expiration state
                $user_plan = $user['membership_plan'] ?? 'None';
                if ($user_plan !== 'None' && !empty($user['plan_expire_date'])) {
                    if (strtotime($user['plan_expire_date']) < time()) {
                        $user_plan = 'None';
                        $pdo->prepare("UPDATE members SET membership_plan = 'None' WHERE id = ?")->execute([$user['id']]);
                    }
                }
                $_SESSION['membership_plan'] = $user_plan;

                // Handle safe redirect
                $target_url = '../dashboard/';
                if (!empty($redirect)) {
                    // Safe internal path validation
                    if (!preg_match('/^https?:\/\/|^\/\//i', $redirect)) {
                        $target_url = $redirect;
                    }
                }

                header("Location: " . $target_url);
                exit();
            }
        }

        // Login failed
        $error_redirect = "index.php?error=invalid" . (!empty($redirect) ? '&redirect=' . urlencode($redirect) : '');
        header("Location: " . $error_redirect);
        exit();

    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $error_redirect = "index.php?error=db" . (!empty($redirect) ? '&redirect=' . urlencode($redirect) : '');
        header("Location: " . $error_redirect);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>