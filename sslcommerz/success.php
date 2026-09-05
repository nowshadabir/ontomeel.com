<?php
// sslcommerz/success.php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/config.php';

// --- Step 1: Handle POST request from SSLCommerz (PRG: Post-Redirect-Get Pattern) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $val_id    = trim($_POST['val_id'] ?? '');
    $tran_id   = trim($_POST['tran_id'] ?? '');
    $amount    = (float)($_POST['amount'] ?? 0);
    $order_raw = trim($_POST['value_a'] ?? '');
    $inv_raw   = trim($_POST['value_b'] ?? '');
    $card_type = trim($_POST['card_type'] ?? '');

    $sslConfig = getSSLCommerzConfig($pdo);

    $is_valid = false;
    $orderData = null;
    $order_id = 0;
    $is_risk = false;

    if (!empty($val_id) && !empty($sslConfig['store_id']) && !empty($sslConfig['store_passwd'])) {
        $validation_url = $sslConfig['val_url'] . "?val_id=" . urlencode($val_id) . "&store_id=" . urlencode($sslConfig['store_id']) . "&store_passwd=" . urlencode($sslConfig['store_passwd']) . "&v=1&format=json";

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $validation_url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $result = json_decode($response, true);

        if ($code == 200 && $result && isset($result['status']) && ($result['status'] === 'VALID' || $result['status'] === 'VALIDATED')) {
            $verified_amount = (float)($result['amount'] ?? $amount);
            $verified_currency = strtoupper($result['currency'] ?? 'BDT');

            if (empty($tran_id) && !empty($result['tran_id'])) {
                $tran_id = $result['tran_id'];
            }
            if (empty($order_raw) && !empty($result['value_a'])) {
                $order_raw = trim($result['value_a']);
            }
            if (empty($inv_raw) && !empty($result['value_b'])) {
                $inv_raw = trim($result['value_b']);
            }
            if (empty($card_type) && !empty($result['card_type'])) {
                $card_type = $result['card_type'];
            }

            if (isset($result['risk_level']) && (string)$result['risk_level'] === '1') {
                $is_risk = true;
            }

            // Order lookup: by ID
            if (!empty($order_raw) && is_numeric($order_raw)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt->execute([(int)$order_raw]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Order lookup: by invoice number
            if (!$orderData && !empty($inv_raw)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ?");
                $stmt->execute([$inv_raw]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Order lookup: by value_a as invoice
            if (!$orderData && !empty($order_raw)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ?");
                $stmt->execute([$order_raw]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Order lookup: by transaction ID
            if (!$orderData && !empty($tran_id)) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE trx_id = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$tran_id]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Amount validation
            if ($orderData && $verified_currency === 'BDT') {
                $expected_amount = (float)$orderData['total_amount'];
                if ($verified_amount >= ($expected_amount - 0.05)) {
                    $is_valid = true;
                    $order_id = (int)$orderData['id'];

                    // Replay attack prevention: check if this val_id was already used on another order
                    if (!empty($val_id)) {
                        $replayCheck = $pdo->prepare("SELECT id FROM orders WHERE payment_id = ? AND id != ? LIMIT 1");
                        $replayCheck->execute([$val_id, $order_id]);
                        if ($replayCheck->fetch()) {
                            error_log("SSLCommerz REPLAY ATTACK BLOCKED: val_id {$val_id} already consumed on another order!");
                            $is_valid = false;
                        }
                    }

                    // Transaction ID match verification
                    if ($is_valid && !empty($orderData['trx_id']) && !empty($result['tran_id'])) {
                        if ($orderData['trx_id'] !== $result['tran_id']) {
                            error_log("SSLCommerz TRANSACTION MISMATCH: Order TrxID {$orderData['trx_id']} vs Gateway TrxID {$result['tran_id']}");
                            $is_valid = false;
                        }
                    }
                } else {
                    error_log("SSLCommerz amount mismatch for Order #{$orderData['id']}: Paid {$verified_amount}, Expected {$expected_amount}");
                }
            }
        }
    }

    if ($is_valid && $orderData && $order_id > 0) {
        $wasAlreadyPaid = ($orderData['payment_status'] === 'Paid');
        $new_order_status = $is_risk ? 'On Hold' : 'Processing';
        $notes = $orderData['notes'] ?? '';
        if ($is_risk && strpos($notes, 'Risk Level 1') === false) {
            $notes = trim($notes . " | [Risk Level 1: Verify Customer]");
        }

        $updateStmt = $pdo->prepare("UPDATE orders SET payment_status = 'Paid', order_status = ?, payment_id = ?, trx_id = ?, payment_method = 'SSLCommerz', notes = ? WHERE id = ?");
        $updateStmt->execute([$new_order_status, $val_id, $tran_id, $notes, $order_id]);

        $recipient_email = trim($orderData['guest_email'] ?? '');
        if (empty($recipient_email) && !empty($orderData['member_id'])) {
            $mStmt = $pdo->prepare("SELECT email FROM members WHERE id = ?");
            $mStmt->execute([(int)$orderData['member_id']]);
            $recipient_email = trim($mStmt->fetchColumn() ?: '');
        }

        if (!$wasAlreadyPaid && !empty($recipient_email)) {
            try {
                require_once __DIR__ . '/../includes/notification_helper.php';
                $notif_data = [
                    'name' => $orderData['guest_name'],
                    'invoice_no' => $orderData['invoice_no'],
                    'amount' => $verified_amount ?: $orderData['total_amount'],
                    'address' => $orderData['shipping_address']
                ];

                // Fetch first item info to include in notification email
                $itemInfoStmt = $pdo->prepare("SELECT oi.preorder_id, 
                                                      COALESCE(b.title, po.title) as title, 
                                                      COALESCE(b.title_en, po.title_en) as title_en, 
                                                      COALESCE(b.author, po.author) as author, 
                                                      COALESCE(b.author_en, po.author_en) as author_en 
                                               FROM order_items oi 
                                               LEFT JOIN books b ON oi.book_id = b.id 
                                               LEFT JOIN pre_orders po ON oi.preorder_id = po.id 
                                               WHERE oi.order_id = ? LIMIT 1");
                $itemInfoStmt->execute([$order_id]);
                $itemInfo = $itemInfoStmt->fetch(PDO::FETCH_ASSOC);
                if ($itemInfo) {
                    $notif_data['book_title'] = $itemInfo['title'];
                    $notif_data['book_title_en'] = $itemInfo['title_en'] ?: $itemInfo['title'];
                    $notif_data['book_author'] = $itemInfo['author'];
                    $notif_data['book_author_en'] = $itemInfo['author_en'] ?: $itemInfo['author'];
                    if (!empty($itemInfo['preorder_id'])) {
                        $notif_data['is_preorder'] = true;
                    }
                }

                send_notification($recipient_email, 'order_placed', $notif_data);
            } catch (Exception $e) {
                error_log("SSLCommerz success email notification error: " . $e->getMessage());
            }
        }
    }

    // PRG Redirect to GET request on same-site URL (preserves customer session cookie!)
    $redirect_url = 'success.php?order_id=' . urlencode($order_id ?: $order_raw) . '&tran_id=' . urlencode($tran_id) . '&val_id=' . urlencode($val_id) . '&card_type=' . urlencode($card_type) . ($is_valid ? '&status=success' : '&status=pending');
    header("Location: " . $redirect_url, true, 303);
    exit();
}

// --- Step 2: Render GET request with full styles and preserved session ---
$order_param = trim($_GET['order_id'] ?? '');
$tran_id     = trim($_GET['tran_id'] ?? '');
$val_id      = trim($_GET['val_id'] ?? '');
$card_type   = trim($_GET['card_type'] ?? '');
$status_req  = trim($_GET['status'] ?? '');

$orderData = null;
if (!empty($order_param)) {
    if (is_numeric($order_param)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? OR invoice_no = ?");
        $stmt->execute([(int)$order_param, $order_param]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_no = ?");
        $stmt->execute([$order_param]);
    }
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$orderData && !empty($tran_id)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE trx_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$tran_id]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper functions for Bengali formatting on receipt
if (!function_exists('toBanglaDigits')) {
    function toBanglaDigits($num) {
        $bn_digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace(range(0, 9), $bn_digits, (string)$num);
    }
}

if (!function_exists('formatBnDateReceipt')) {
    function formatBnDateReceipt($date_str) {
        if (!$date_str) return 'N/A';
        $timestamp = strtotime($date_str);
        $months_bn = [
            'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি', 'March' => 'মার্চ',
            'April' => 'এপ্রিল', 'May' => 'মে', 'June' => 'জুন',
            'July' => 'জুলাই', 'August' => 'আগস্ট', 'September' => 'সেপ্টেম্বর',
            'October' => 'অক্টোবর', 'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর'
        ];
        $day = toBanglaDigits(date('d', $timestamp));
        $en_month = date('F', $timestamp);
        $month = $months_bn[$en_month] ?? $en_month;
        $year = toBanglaDigits(date('Y', $timestamp));
        $time = toBanglaDigits(date('h:i', $timestamp)) . ' ' . (date('A', $timestamp) === 'AM' ? 'পূর্বাহ্ণ' : 'অপরাহ্ণ');
        return "{$day} {$month} {$year}, {$time}";
    }
}

if (!function_exists('formatBnReleaseMonth')) {
    function formatBnReleaseMonth($date_str) {
        if (!$date_str) return 'সেপ্টেম্বর ২০২৬';
        $timestamp = strtotime($date_str);
        $months_bn = [
            'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি', 'March' => 'মার্চ',
            'April' => 'এপ্রিল', 'May' => 'মে', 'June' => 'জুন',
            'July' => 'জুলাই', 'August' => 'আগস্ট', 'September' => 'সেপ্টেম্বর',
            'October' => 'অক্টোবর', 'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর'
        ];
        $en_month = date('F', $timestamp);
        $month = $months_bn[$en_month] ?? $en_month;
        $year = toBanglaDigits(date('Y', $timestamp));
        return "{$month} {$year}";
    }
}

if (!function_exists('getReceiptBookCoverUrl')) {
    function getReceiptBookCoverUrl($cover_img, $is_po, $path_prefix = '../') {
        if (empty($cover_img)) {
            return $path_prefix . 'assets/img/logo.webp';
        }
        if (strpos($cover_img, 'http') === 0) {
            return $cover_img;
        }
        $clean = trim($cover_img);
        if ($is_po) {
            return $path_prefix . 'assets/img/preorders/' . $clean;
        }
        return $path_prefix . 'admin/assets/book-images/' . $clean;
    }
}

// Fetch order items if order was found
$order_items = [];
if ($orderData) {
    $stmt = $pdo->prepare("SELECT oi.*, 
                                  COALESCE(b.title, po.title) as book_title, 
                                  COALESCE(b.author, po.author) as book_author, 
                                  COALESCE(b.cover_image, po.cover_image) as cover_image,
                                  po.release_date as po_release_date,
                                  po.slug as po_slug,
                                  po.is_hot_deal as po_is_hot_deal
                           FROM order_items oi 
                           LEFT JOIN books b ON oi.book_id = b.id 
                           LEFT JOIN pre_orders po ON oi.preorder_id = po.id 
                           WHERE oi.order_id = ?");
    $stmt->execute([$orderData['id']]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if this order is a Pre-order
$is_preorder = false;
$preorder_item = null;
foreach ($order_items as $item) {
    if (!empty($item['preorder_id'])) {
        $is_preorder = true;
        if (!$preorder_item) {
            $preorder_item = $item;
        }
    }
}
if (!$is_preorder && !empty($orderData['notes']) && stripos($orderData['notes'], 'pre-order') !== false) {
    $is_preorder = true;
}
if (!$is_preorder && !empty($orderData['invoice_no']) && strpos($orderData['invoice_no'], 'PRE-') === 0) {
    $is_preorder = true;
}

// Fetch pre-order release date if available
$release_date_str = '';
if ($is_preorder) {
    if (!empty($preorder_item['po_release_date'])) {
        $release_date_str = $preorder_item['po_release_date'];
    } elseif (!empty($preorder_item['preorder_id'])) {
        $poCheck = $pdo->prepare("SELECT release_date FROM pre_orders WHERE id = ?");
        $poCheck->execute([$preorder_item['preorder_id']]);
        $release_date_str = $poCheck->fetchColumn() ?: '';
    } else {
        $poCheck = $pdo->query("SELECT release_date FROM pre_orders ORDER BY id DESC LIMIT 1");
        $release_date_str = $poCheck->fetchColumn() ?: '';
    }
}

// Customer information resolution
$customer_name = !empty($orderData['guest_name']) ? $orderData['guest_name'] : '';
$customer_phone = !empty($orderData['guest_phone']) ? $orderData['guest_phone'] : '';
$customer_email = !empty($orderData['guest_email']) ? $orderData['guest_email'] : '';
if (empty($customer_name) && !empty($orderData['member_id'])) {
    $mStmt = $pdo->prepare("SELECT full_name, phone, email FROM members WHERE id = ?");
    $mStmt->execute([(int)$orderData['member_id']]);
    $mRow = $mStmt->fetch(PDO::FETCH_ASSOC);
    if ($mRow) {
        $customer_name = $customer_name ?: $mRow['full_name'];
        $customer_phone = $customer_phone ?: $mRow['phone'];
        $customer_email = $customer_email ?: $mRow['email'];
    }
}

$is_paid = ($orderData && $orderData['payment_status'] === 'Paid');
$is_risk = ($orderData && strpos($orderData['notes'] ?? '', 'Risk Level 1') !== false);

// IDOR / Access Authorization Check
$is_authorized = true;
if ($orderData) {
    if (!empty($orderData['member_id'])) {
        $logged_user = $_SESSION['user_id'] ?? null;
        if ($logged_user != $orderData['member_id']) {
            if (empty($tran_id) || $orderData['trx_id'] !== $tran_id) {
                $is_authorized = false;
            }
        }
    }
}

// Correct path prefix for assets, tailwind-config, style.css, script.js
$path_prefix = '../';
$inv_prefix = !empty($orderData['invoice_no']) ? $orderData['invoice_no'] . ' - ' : '';
$page_title = $is_paid 
    ? ($is_preorder ? $inv_prefix . 'প্রি-অর্ডার ক্যাশ রসিদ | অন্ত্যমিল' : $inv_prefix . 'অফিসিয়াল ক্যাশ রসিদ | অন্ত্যমিল') 
    : 'পেমেন্ট স্ট্যাটাস | অন্ত্যমিল';

include __DIR__ . '/../includes/header.php';
?>

<!-- Confetti Canvas Container -->
<?php if ($is_paid && $is_authorized): ?>
<div id="confetti-container" class="fixed inset-0 pointer-events-none z-50 overflow-hidden"></div>
<?php endif; ?>

<div class="receipt-page-bg">
    <div class="receipt-wrap">

        <?php if (!$is_authorized): ?>
        <!-- Unauthorized -->
        <div class="unauth-card">
            <div class="unauth-icon">✕</div>
            <h2>অননুমোদিত অনুরোধ</h2>
            <p>এই অর্ডারের তথ্য দেখতে আপনার অ্যাকাউন্টে লগইন করুন।</p>
            <a href="../login/index.php" class="btn-primary">লগইন পেজে যান</a>
            <a href="../index.php" class="btn-secondary">হোম পেজ</a>
        </div>

        <?php else: ?>

        <!-- ======= SCREEN CARD ======= -->
        <div class="screen-only">
            <div class="receipt-card">

                <?php if ($is_paid): ?>

                <!-- Status Icon -->
                <div class="status-hero">
                    <div class="status-icon-wrap <?php echo $is_preorder ? 'icon-gold' : 'icon-green'; ?>">
                        <?php if ($is_preorder): ?>
                            <span>★</span>
                        <?php else: ?>
                            <span>✓</span>
                        <?php endif; ?>
                    </div>
                    <h1 class="status-title">
                        <?php echo $is_preorder ? 'প্রি-বুকিং সম্পন্ন!' : 'পেমেন্ট সফল হয়েছে!'; ?>
                    </h1>
                    <p class="status-sub">
                        <?php echo $is_preorder
                            ? 'আপনার প্রি-অর্ডার বুকিং নিশ্চিত হয়েছে। বই প্রকাশের সাথে সাথে অগ্রাধিকার ভিত্তিতে পৌঁছে দেওয়া হবে।'
                            : 'ধন্যবাদ! পেমেন্ট সফল। শীঘ্রই আপনার বইগুলো পাঠানো হবে।'; ?>
                    </p>
                </div>

                <!-- Meta Strip -->
                <div class="meta-strip">
                    <div class="meta-item">
                        <span class="meta-label">ইনভয়েস</span>
                        <span class="meta-val mono"><?php echo htmlspecialchars($orderData['invoice_no'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">তারিখ</span>
                        <span class="meta-val"><?php echo formatBnDateReceipt($orderData['order_date'] ?? date('Y-m-d H:i:s')); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">পেমেন্ট</span>
                        <span class="meta-val"><?php echo htmlspecialchars(!empty($card_type) ? $card_type : 'SSLCommerz'); ?></span>
                    </div>
                </div>

                <!-- Items -->
                <div class="section-block">
                    <p class="section-label"><?php echo $is_preorder ? 'প্রি-অর্ডারকৃত বই' : 'অর্ডারকৃত বইসমূহ'; ?></p>
                    <?php if ($is_preorder && $preorder_item): ?>
                        <div class="item-row">
                            <div class="item-info">
                                <strong><?php echo htmlspecialchars($preorder_item['book_title'] ?? 'বই'); ?></strong>
                                <span><?php echo htmlspecialchars($preorder_item['book_author'] ?? ''); ?> &nbsp;·&nbsp; ১ কপি</span>
                                <?php if (!empty($release_date_str)): ?>
                                    <span class="release-note">📅 প্রকাশনা: <?php echo formatBnReleaseMonth($release_date_str); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="item-price mono">৳<?php echo number_format((float)($preorder_item['total_price'] ?? $orderData['subtotal']), 2); ?></span>
                        </div>
                    <?php elseif (!empty($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                        <div class="item-row">
                            <div class="item-info">
                                <strong><?php echo htmlspecialchars($item['book_title'] ?? 'বই'); ?></strong>
                                <span><?php echo htmlspecialchars($item['book_author'] ?? ''); ?> &nbsp;·&nbsp; <?php echo $item['quantity']; ?> কপি</span>
                            </div>
                            <span class="item-price mono">৳<?php echo number_format((float)$item['total_price'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Customer + Address -->
                <div class="two-col">
                    <div class="section-block">
                        <p class="section-label">ক্রেতা</p>
                        <p class="info-name"><?php echo htmlspecialchars($customer_name ?: 'সম্মানিত পাঠক'); ?></p>
                        <p class="info-line mono"><?php echo htmlspecialchars($customer_phone ?: ''); ?></p>
                        <?php if (!empty($customer_email)): ?>
                            <p class="info-line"><?php echo htmlspecialchars($customer_email); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="section-block">
                        <p class="section-label">ডেলিভারি ঠিকানা</p>
                        <p class="info-line"><?php echo htmlspecialchars($orderData['shipping_address'] ?? ''); ?></p>
                        <?php $loc = array_filter([$orderData['upazila'] ?? '', $orderData['district'] ?? '', $orderData['division'] ?? '']); ?>
                        <?php if ($loc): ?><p class="info-line"><?php echo implode(', ', $loc); ?></p><?php endif; ?>
                    </div>
                </div>

                <!-- Totals -->
                <div class="totals-block">
                    <div class="total-row">
                        <span>বইয়ের মূল্য</span>
                        <span class="mono">৳<?php echo number_format((float)($orderData['subtotal'] ?? 0), 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>ডেলিভারি চার্জ</span>
                        <span class="mono"><?php $sh = (float)($orderData['shipping_cost'] ?? 0); echo $sh > 0 ? '৳' . number_format($sh, 2) : 'বিনামূল্যে'; ?></span>
                    </div>
                    <div class="total-row grand">
                        <span>সর্বমোট পরিশোধিত</span>
                        <span class="mono grand-amt">৳<?php echo number_format((float)($orderData['total_amount'] ?? 0), 2); ?></span>
                    </div>
                </div>

                <?php else: ?>

                <!-- Pending -->
                <div class="status-hero">
                    <div class="status-icon-wrap icon-amber">⏳</div>
                    <h1 class="status-title">পেমেন্ট যাচাই পেন্ডিং</h1>
                    <p class="status-sub">আপনার পেমেন্টটি গেটওয়ে থেকে নিশ্চিতকরণ প্রক্রিয়াধীন। ড্যাশবোর্ডে শীঘ্রই আপডেট হবে।</p>
                </div>
                <?php if ($orderData): ?>
                <div class="meta-strip">
                    <div class="meta-item">
                        <span class="meta-label">ইনভয়েস</span>
                        <span class="meta-val mono"><?php echo htmlspecialchars($orderData['invoice_no']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">মোট</span>
                        <span class="meta-val mono">৳<?php echo number_format((float)$orderData['total_amount'], 2); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="action-btns">
                    <?php if ($is_paid): ?>
                    <button onclick="window.print()" class="btn-primary">রসিদ PDF / প্রিন্ট</button>
                    <?php endif; ?>
                    <a href="../dashboard/index.php" class="btn-secondary"><?php echo $is_preorder ? 'প্রি-অর্ডার স্ট্যাটাস' : 'আমার ড্যাশবোর্ড'; ?></a>
                    <a href="../index.php" class="btn-ghost">← হোম পেজ</a>
                </div>

            </div>
        </div><!-- /.screen-only -->


        <!-- ======= PRINT / PDF RECEIPT ======= -->
        <div class="print-only">
            <div class="print-sheet">

                <!-- Header -->
                <div class="p-header">
                    <div class="p-brand">
                        <img src="../assets/img/logo.webp" alt="অন্ত্যমিল" class="p-logo" onerror="this.style.display='none'">
                        <div>
                            <div class="p-brand-name">অন্ত্যমিল</div>
                            <div class="p-brand-sub">অনলাইন বুকশপ &amp; প্রকাশনা</div>
                        </div>
                    </div>
                    <div class="p-meta">
                        <div class="p-doc-title"><?php echo $is_preorder ? 'প্রি-অর্ডার রসিদ' : 'পেমেন্ট রসিদ'; ?></div>
                        <table class="p-meta-table">
                            <tr><td>ইনভয়েস:</td><td><strong><?php echo htmlspecialchars($orderData['invoice_no'] ?? 'N/A'); ?></strong></td></tr>
                            <tr><td>তারিখ:</td><td><?php echo formatBnDateReceipt($orderData['order_date'] ?? date('Y-m-d H:i:s')); ?></td></tr>
                            <tr><td>পেমেন্ট:</td><td><?php echo htmlspecialchars(!empty($card_type) ? $card_type : 'SSLCommerz'); ?></td></tr>
                        </table>
                    </div>
                </div>

                <div class="p-divider"></div>

                <!-- Customer & Address -->
                <div class="p-parties">
                    <div>
                        <div class="p-party-label">ক্রেতা</div>
                        <div class="p-party-name"><?php echo htmlspecialchars($customer_name ?: 'সম্মানিত পাঠক'); ?></div>
                        <div class="p-party-line"><?php echo htmlspecialchars($customer_phone ?: ''); ?></div>
                        <?php if (!empty($customer_email)): ?><div class="p-party-line"><?php echo htmlspecialchars($customer_email); ?></div><?php endif; ?>
                    </div>
                    <div>
                        <div class="p-party-label">ডেলিভারি ঠিকানা</div>
                        <div class="p-party-line"><?php echo htmlspecialchars($orderData['shipping_address'] ?? ''); ?></div>
                        <?php if ($loc): ?><div class="p-party-line"><?php echo implode(', ', $loc); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="p-divider"></div>

                <!-- Items Table -->
                <table class="p-items">
                    <thead>
                        <tr>
                            <th style="text-align:left; width:60%">বইয়ের নাম</th>
                            <th style="text-align:left; width:25%">লেখক</th>
                            <th style="text-align:center; width:5%">পরিমাণ</th>
                            <th style="text-align:right; width:10%">মূল্য</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($is_preorder && $preorder_item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($preorder_item['book_title'] ?? 'বই'); ?></strong>
                                <?php if (!empty($release_date_str)): ?><br><small>প্রকাশনা: <?php echo formatBnReleaseMonth($release_date_str); ?></small><?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($preorder_item['book_author'] ?? 'অন্ত্যমিল'); ?></td>
                            <td style="text-align:center">১</td>
                            <td style="text-align:right">৳<?php echo number_format((float)($preorder_item['total_price'] ?? $orderData['subtotal']), 2); ?></td>
                        </tr>
                        <?php elseif (!empty($order_items)): ?>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['book_title'] ?? 'বই'); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['book_author'] ?? ''); ?></td>
                                <td style="text-align:center"><?php echo $item['quantity']; ?></td>
                                <td style="text-align:right">৳<?php echo number_format((float)$item['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="p-totals">
                    <table class="p-totals-table">
                        <tr>
                            <td>বইয়ের মূল্য</td>
                            <td>৳<?php echo number_format((float)($orderData['subtotal'] ?? 0), 2); ?></td>
                        </tr>
                        <tr>
                            <td>ডেলিভারি চার্জ</td>
                            <td><?php $sh = (float)($orderData['shipping_cost'] ?? 0); echo $sh > 0 ? '৳' . number_format($sh, 2) : 'বিনামূল্যে'; ?></td>
                        </tr>
                        <tr class="p-grand">
                            <td><strong>সর্বমোট পরিশোধিত</strong></td>
                            <td><strong>৳<?php echo number_format((float)($orderData['total_amount'] ?? 0), 2); ?></strong></td>
                        </tr>
                    </table>
                </div>

                <!-- Footer -->
                <div class="p-footer">
                    <p>ধন্যবাদ! আপনার কপিটি নিশ্চিত সংরক্ষিত। যেকোনো প্রশ্নে যোগাযোগ করুন।</p>
                    <p>📞 +৮৮০১৩৩০৯৭৫৭৮৭ &nbsp;|&nbsp; 🌐 ontomeel.com &nbsp;|&nbsp; শপ নং ৬, লাবণী রোড, কক্সবাজার</p>
                </div>

            </div>
        </div><!-- /.print-only -->

        <?php endif; ?>

    </div>
</div>

<script>
    try {
        localStorage.removeItem('antyam_cart');
        localStorage.removeItem('antyam_borrow_cart');
    } catch(e) {}

    <?php if ($is_paid): ?>
    function triggerConfetti() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        const colors = ['#cda873', '#10B981', '#FBBF24', '#F59E0B', '#34D399'];
        for (let i = 0; i < 40; i++) {
            const el = document.createElement('div');
            el.style.cssText = `position:absolute;left:${Math.random()*100}vw;top:-20px;width:${Math.random()*8+5}px;height:${Math.random()*10+5}px;background:${colors[Math.floor(Math.random()*colors.length)]};border-radius:2px;opacity:${Math.random()*0.6+0.3};animation:fall ${Math.random()*2+2}s ease-in forwards;animation-delay:${Math.random()*1.5}s`;
            container.appendChild(el);
        }
    }
    document.addEventListener('DOMContentLoaded', triggerConfetti);
    <?php endif; ?>

    window.addEventListener('beforeprint', function() {
        document.title = "<?php echo htmlspecialchars($orderData['invoice_no'] ?? 'Invoice'); ?> - অন্ত্যমিল রসিদ";
    });
</script>

<style>
@keyframes fall {
    0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(105vh) rotate(540deg); opacity: 0; }
}

/* ── PAGE WRAPPER ── */
.receipt-page-bg {
    min-height: 100vh;
    background: #f5f3ef;
    padding: 6rem 1rem 3rem;
    font-family: 'Anek Bangla', 'Hind Siliguri', sans-serif;
}
.receipt-wrap {
    max-width: 640px;
    margin: 0 auto;
}

/* ── UNAUTHORIZED ── */
.unauth-card {
    background: #fff;
    border-radius: 24px;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 4px 24px rgba(0,0,0,.07);
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
.unauth-icon {
    width: 52px; height: 52px;
    background: #fee2e2; color: #dc2626;
    border-radius: 50%;
    font-size: 1.5rem;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto;
}

/* ── SCREEN CARD ── */
.receipt-card {
    background: #ffffff;
    border-radius: 28px;
    padding: 2rem 1.75rem;
    box-shadow: 0 6px 32px rgba(0,0,0,.08);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* Status Hero */
.status-hero { text-align: center; padding-bottom: .5rem; }
.status-icon-wrap {
    width: 60px; height: 60px;
    border-radius: 50%;
    font-size: 1.75rem;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: .75rem;
    font-weight: 900;
}
.icon-green  { background: #dcfce7; color: #16a34a; }
.icon-gold   { background: #fef3c7; color: #b45309; }
.icon-amber  { background: #fff7ed; color: #ea580c; }
.status-title { font-size: 1.45rem; font-weight: 800; color: #111; margin: 0 0 .35rem; }
.status-sub   { font-size: .88rem; color: #6b7280; max-width: 420px; margin: 0 auto; line-height: 1.55; }

/* Meta Strip */
.meta-strip {
    display: flex;
    gap: .75rem;
    background: #f9f7f4;
    border-radius: 14px;
    padding: .9rem 1rem;
    flex-wrap: wrap;
}
.meta-item { flex: 1; min-width: 100px; }
.meta-label { display: block; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: .2rem; }
.meta-val   { font-size: .85rem; font-weight: 600; color: #111; }

/* Section block */
.section-block { display: flex; flex-direction: column; gap: .3rem; }
.section-label  { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin: 0; }

/* Items */
.item-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .5rem;
    padding: .65rem 0;
    border-bottom: 1px solid #f3f4f6;
}
.item-row:last-child { border-bottom: none; }
.item-info { display: flex; flex-direction: column; gap: .15rem; }
.item-info strong { font-size: .9rem; color: #111; font-weight: 700; }
.item-info span  { font-size: .78rem; color: #6b7280; }
.release-note { font-size: .75rem; color: #b45309; font-weight: 600; }
.item-price { font-size: .9rem; font-weight: 700; color: #111; white-space: nowrap; }

/* Two col layout */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 480px) { .two-col { grid-template-columns: 1fr; } }
.info-name { font-size: .95rem; font-weight: 700; color: #111; margin: 0; }
.info-line { font-size: .82rem; color: #4b5563; margin: .1rem 0; }

/* Totals */
.totals-block {
    background: #f9f7f4;
    border-radius: 14px;
    padding: .9rem 1rem;
}
.total-row {
    display: flex;
    justify-content: space-between;
    font-size: .85rem;
    color: #4b5563;
    padding: .25rem 0;
}
.total-row.grand {
    border-top: 1.5px solid #d1d5db;
    margin-top: .4rem;
    padding-top: .6rem;
    font-weight: 800;
    color: #111;
    font-size: 1rem;
}
.grand-amt { color: #b45309; }

/* Buttons */
.action-btns { display: flex; flex-direction: column; gap: .65rem; }
.btn-primary, .btn-secondary, .btn-ghost {
    display: block;
    text-align: center;
    padding: .85rem;
    border-radius: 16px;
    font-size: .88rem;
    font-weight: 700;
    text-decoration: none;
    border: none; cursor: pointer;
    transition: opacity .2s;
}
.btn-primary  { background: #111; color: #fff; }
.btn-primary:hover { opacity: .85; }
.btn-secondary { background: #f3f4f6; color: #374151; }
.btn-secondary:hover { background: #e5e7eb; }
.btn-ghost    { background: transparent; color: #9ca3af; font-size: .8rem; padding: .4rem; }
.btn-ghost:hover { color: #6b7280; }
.mono { font-family: 'Courier New', Courier, monospace; }

/* ── PRINT ONLY (screen hidden) ── */
@media screen {
    .print-only { display: none !important; }
}

/* ── PRINT / PDF ── */
@page {
    size: A4 portrait;
    margin: 14mm 16mm 14mm 16mm;
}
@media print {
    html, body {
        background: #fff !important;
        margin: 0 !important; padding: 0 !important;
        font-family: 'Anek Bangla', 'Hind Siliguri', 'Noto Serif Bengali', Arial, sans-serif !important;
        color: #111 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide everything except the print sheet */
    header, footer, nav, #confetti-container, .screen-only,
    .receipt-page-bg > .receipt-wrap > .screen-only,
    .fixed, .sticky, [id="navbar"], [id="mobile-nav"],
    .action-btns, script { display: none !important; }

    .receipt-page-bg { padding: 0 !important; background: #fff !important; }
    .receipt-wrap    { max-width: 100% !important; margin: 0 !important; }

    .print-only  { display: block !important; }
    .print-sheet {
        width: 100%;
        background: #fff;
        font-size: 11px;
        line-height: 1.4;
        color: #111;
    }

    /* Header */
    .p-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    .p-brand { display: flex; align-items: center; gap: 10px; }
    .p-logo  { width: 42px; height: 42px; object-fit: contain; }
    .p-brand-name { font-size: 20px; font-weight: 800; color: #111; }
    .p-brand-sub  { font-size: 9px; color: #6b7280; font-weight: 600; }
    .p-meta { text-align: right; }
    .p-doc-title  { font-size: 14px; font-weight: 800; margin-bottom: 4px; }
    .p-meta-table { border-collapse: collapse; margin-left: auto; }
    .p-meta-table td { padding: 1px 4px; font-size: 10px; color: #374151; }
    .p-meta-table td:first-child { color: #9ca3af; text-align: right; }
    .p-meta-table td:last-child  { text-align: right; }

    /* Divider */
    .p-divider { border: none; border-top: 1.5px solid #e5e7eb; margin: 8px 0; }

    /* Parties */
    .p-parties {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 8px;
    }
    .p-party-label { font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: 3px; }
    .p-party-name  { font-size: 12px; font-weight: 800; color: #111; }
    .p-party-line  { font-size: 9.5px; color: #374151; }

    /* Items table */
    .p-items { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10.5px; }
    .p-items th {
        background: #f3f4f6 !important;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #374151;
        padding: 5px 6px;
        border: 1px solid #e5e7eb;
    }
    .p-items td {
        padding: 6px;
        border: 1px solid #e5e7eb;
        vertical-align: top;
    }
    .p-items td strong { font-weight: 700; }
    .p-items td small  { font-size: 9px; color: #b45309; }

    /* Totals */
    .p-totals { display: flex; justify-content: flex-end; margin-bottom: 14px; }
    .p-totals-table { border-collapse: collapse; width: 220px; font-size: 10.5px; }
    .p-totals-table td { padding: 3px 6px; color: #374151; }
    .p-totals-table td:last-child { text-align: right; font-family: 'Courier New', monospace; }
    .p-grand td {
        border-top: 1.5px solid #111;
        padding-top: 5px;
        font-size: 12px;
        color: #111;
    }

    /* Footer */
    .p-footer {
        border-top: 1px solid #e5e7eb;
        padding-top: 8px;
        font-size: 9px;
        color: #6b7280;
        text-align: center;
        line-height: 1.5;
    }
    .p-footer p { margin: 1px 0; }
}


<?php include __DIR__ . '/../includes/footer.php'; ?>

