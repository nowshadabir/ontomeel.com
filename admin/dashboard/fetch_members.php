<?php
// admin/dashboard/fetch_members.php
// White & Cream AJAX Endpoint for Member Directory

require_once __DIR__ . '/../../includes/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Check Authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'অনুমতি নেই (Unauthorized)']);
    exit();
}

if (!function_exists('bn_num')) {
    function bn_num($number) {
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($en, $bn, (string)$number);
    }
}

$page   = isset($_GET['page']) && (int)$_GET['page'] >= 1 ? (int)$_GET['page'] : 1;
$limit  = isset($_GET['limit']) && (int)$_GET['limit'] >= 1 && (int)$_GET['limit'] <= 100 ? (int)$_GET['limit'] : 15;
$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$plan   = isset($_GET['plan']) ? trim((string)$_GET['plan']) : '';
$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

// Build WHERE conditions
$where_clauses = ["1=1"];
$params = [];

if (!empty($search)) {
    $clean_search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
    $search_param = '%' . $clean_search . '%';
    $where_clauses[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR membership_id LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($plan) && $plan !== 'all') {
    if ($plan === 'None') {
        $where_clauses[] = "(membership_plan IS NULL OR membership_plan = '' OR membership_plan = 'None')";
    } else {
        $where_clauses[] = "membership_plan = ?";
        $params[] = $plan;
    }
}

if (!empty($status) && $status !== 'all') {
    if ($status === 'active') {
        $where_clauses[] = "(
            (membership_plan = 'Student' AND (student_plan_expire_date IS NULL OR student_plan_expire_date >= NOW()))
            OR (membership_plan IN ('General', 'BookLover', 'Collector') AND (plan_expire_date IS NULL OR plan_expire_date >= NOW()))
        )";
    } elseif ($status === 'expired') {
        $where_clauses[] = "(
            (membership_plan = 'Student' AND student_plan_expire_date IS NOT NULL AND student_plan_expire_date < NOW())
            OR (membership_plan IN ('General', 'BookLover', 'Collector') AND plan_expire_date IS NOT NULL AND plan_expire_date < NOW())
        )";
    } elseif ($status === 'free') {
        $where_clauses[] = "(membership_plan IS NULL OR membership_plan = '' OR membership_plan = 'None')";
    }
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

try {
    // Count total records for pagination
    $count_query = "SELECT COUNT(*) FROM members $where_sql";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = (int)$count_stmt->fetchColumn();
    $total_pages = max(1, (int)ceil($total_records / $limit));

    if ($page > $total_pages && $total_records > 0) {
        $page = $total_pages;
    }
    $offset = ($page - 1) * $limit;

    // Fetch records
    $data_query = "SELECT * FROM members $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $data_stmt = $pdo->prepare($data_query);
    $data_stmt->execute($params);
    $members = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    if (empty($members)): ?>
        <tr>
            <td colspan="7" class="py-16 text-center text-stone-500 font-mono text-xs">
                কোনো মেম্বার পাওয়া যায়নি
            </td>
        </tr>
    <?php else:
        $i = 0;
        foreach ($members as $member):
            $i++;
            $serial = $offset + $i;
            $m_id = (int)$member['id'];
            $plan_key = $member['membership_plan'] ?: 'None';

            $is_expired = false;
            $has_plan = false;
            $status_dot = 'bg-stone-400';
            $expire_text = "—";
            $raw_expire_date = '';

            if ($plan_key === 'Student') {
                $has_plan = true;
                $raw_expire_date = $member['student_plan_expire_date'];
            } elseif (in_array($plan_key, ['General', 'BookLover', 'Collector'], true)) {
                $has_plan = true;
                $raw_expire_date = $member['plan_expire_date'];
            }

            if ($has_plan) {
                if (!empty($raw_expire_date) && $raw_expire_date !== '0000-00-00 00:00:00') {
                    $exp_timestamp = strtotime($raw_expire_date);
                    $diff_days = (int)ceil(($exp_timestamp - time()) / 86400);

                    if ($diff_days < 0) {
                        $is_expired = true;
                        $status_dot = 'bg-rose-500';
                        $expire_text = date('d M Y', $exp_timestamp) . " (মেয়াদোত্তীর্ণ)";
                    } else {
                        $status_dot = 'bg-emerald-500';
                        $expire_text = date('d M Y', $exp_timestamp) . " ({$diff_days} দিন বাকি)";
                    }
                } else {
                    $status_dot = 'bg-emerald-500';
                    $expire_text = "অনির্দিষ্ট";
                }
            }

            $plan_names = [
                'General'   => 'সাধারণ পাঠক',
                'BookLover' => 'নিয়মিত পাঠক',
                'Collector' => 'সাহিত্য অনুরাগী',
                'Student'   => 'স্টুডেন্ট মেম্বার',
                'None'      => 'ফ্রি অ্যাকাউন্ট'
            ];

            $pName = $plan_names[$plan_key] ?? 'ফ্রি অ্যাকাউন্ট';
            $membership_code = $member['membership_id'] ?: 'OM-' . str_pad($m_id, 4, '0', STR_PAD_LEFT);
        ?>
        <tr class="hover:bg-[#faf8f5] transition-colors">
            <!-- Serial Number -->
            <td class="py-4 px-5 text-center font-mono text-xs text-stone-500">
                <?php echo bn_num($serial); ?>
            </td>

            <!-- Member Info -->
            <td class="py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#f4f1ea] text-stone-800 border border-[#ded8cc] flex items-center justify-center font-bold text-xs shrink-0 font-mono shadow-xs">
                        <?php echo strtoupper(mb_substr($member['full_name'] ?: 'M', 0, 1, 'UTF-8')); ?>
                    </div>
                    <div>
                        <div class="font-bold text-stone-900 text-sm leading-tight">
                            <?php echo htmlspecialchars($member['full_name'] ?: 'N/A'); ?>
                        </div>
                        <span class="inline-block text-[11px] font-mono text-stone-500 mt-0.5">
                            <?php echo htmlspecialchars($membership_code); ?>
                        </span>
                    </div>
                </div>
            </td>

            <!-- Contact -->
            <td class="py-4 px-5 font-mono text-xs">
                <div class="text-stone-900 font-semibold"><?php echo htmlspecialchars($member['phone'] ?: '—'); ?></div>
                <?php if (!empty($member['email'])): ?>
                    <div class="text-[11px] text-stone-500 truncate max-w-[180px]"><?php echo htmlspecialchars($member['email']); ?></div>
                <?php endif; ?>
            </td>

            <!-- Plan Badge (White & Cream Theme) -->
            <td class="py-4 px-5">
                <?php if ($plan_key !== 'None' && !empty($plan_key)): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-[#f4f1ea] text-stone-800 border border-[#ded8cc]">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $status_dot; ?>"></span>
                        <span><?php echo $pName; ?></span>
                    </span>
                <?php else: ?>
                    <span class="text-xs text-stone-500 font-mono">ফ্রি অ্যাকাউন্ট</span>
                <?php endif; ?>
            </td>

            <!-- Expiration -->
            <td class="py-4 px-5 text-xs font-mono <?php echo $is_expired ? 'text-rose-600 font-semibold' : 'text-stone-600'; ?>">
                <?php echo $expire_text; ?>
            </td>

            <!-- Registered Date -->
            <td class="py-4 px-5 text-xs text-stone-500 font-mono">
                <?php echo date('d M Y', strtotime($member['created_at'])); ?>
            </td>

            <!-- Action -->
            <td class="py-4 px-5 text-right font-mono">
                <button onclick="openApplyMembershipModal(<?php echo htmlspecialchars(json_encode([
                    'id'            => $member['id'],
                    'name'          => $member['full_name'],
                    'phone'         => $member['phone'],
                    'email'         => $member['email'],
                    'membership_id' => $membership_code,
                    'plan'          => $plan_key,
                    'plan_name'     => $pName,
                    'expire_date'   => $raw_expire_date
                ])); ?>)"
                    class="px-3 py-1.5 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 border border-[#d8d3c7] rounded-lg text-xs font-semibold transition-colors cursor-pointer shadow-xs">
                    প্ল্যান পরিবর্তন →
                </button>
            </td>
        </tr>
    <?php endforeach; endif;
    $html = ob_get_clean();

    // Render Pagination
    ob_start();
    $start_rec = $total_records > 0 ? $offset + 1 : 0;
    $end_rec   = min($offset + $limit, $total_records);
    ?>
    <div class="p-4 border-t border-[#e7e3da] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#faf8f5]">
        <span class="text-xs text-stone-500 font-mono">
            দেখানো হচ্ছে <?php echo bn_num($start_rec); ?>-<?php echo bn_num($end_rec); ?> (মোট <?php echo bn_num($total_records); ?> জন)
        </span>

        <?php if ($total_pages > 1): ?>
            <div class="flex items-center gap-1 font-mono text-xs">
                <?php if ($page > 1): ?>
                    <button onclick="loadMembers(<?php echo $page - 1; ?>)"
                        class="px-2.5 py-1 rounded-lg border border-[#d8d3c7] bg-white hover:bg-[#eae5db] text-stone-700 transition-colors cursor-pointer shadow-xs">
                        Prev
                    </button>
                <?php else: ?>
                    <span class="px-2.5 py-1 rounded-lg border border-[#e7e3da] bg-[#faf8f5] text-stone-400 cursor-not-allowed">
                        Prev
                    </span>
                <?php endif; ?>

                <?php
                $range = 2;
                for ($p = 1; $p <= $total_pages; $p++):
                    if ($p == 1 || $p == $total_pages || ($p >= $page - $range && $p <= $page + $range)):
                ?>
                    <button onclick="loadMembers(<?php echo $p; ?>)"
                        class="w-7 h-7 rounded-lg font-mono text-xs transition-all <?php echo $p == $page ? 'bg-stone-900 text-white font-bold shadow-xs' : 'bg-white border border-[#d8d3c7] text-stone-700 hover:bg-[#eae5db]'; ?> cursor-pointer">
                        <?php echo $p; ?>
                    </button>
                <?php elseif ($p == $page - $range - 1 || $p == $page + $range + 1): ?>
                    <span class="w-6 text-center text-stone-400">...</span>
                <?php endif; endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <button onclick="loadMembers(<?php echo $page + 1; ?>)"
                        class="px-2.5 py-1 rounded-lg border border-[#d8d3c7] bg-white hover:bg-[#eae5db] text-stone-700 transition-colors cursor-pointer shadow-xs">
                        Next
                    </button>
                <?php else: ?>
                    <span class="px-2.5 py-1 rounded-lg border border-[#e7e3da] bg-[#faf8f5] text-stone-400 cursor-not-allowed">
                        Next
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    $pagination_html = ob_get_clean();

    echo json_encode([
        'success'       => true,
        'html'          => $html,
        'pagination'    => $pagination_html,
        'total_records' => $total_records,
        'total_pages'   => $total_pages,
        'current_page'  => $page
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'ডাটাবেস ত্রুটি: ' . $e->getMessage()]);
}
