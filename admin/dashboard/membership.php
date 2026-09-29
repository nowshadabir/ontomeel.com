<?php
require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'membership';
$page_title = 'মেম্বারশিপ ম্যানেজমেন্ট';

// Fetch Plan Members (only members who have a plan selected)
$active_members_stmt = $pdo->query("
    SELECT id, membership_id, full_name, email, phone, address, membership_plan, plan_expire_date, acc_balance, is_active, created_at 
    FROM members 
    WHERE membership_plan IS NOT NULL AND membership_plan != 'None' AND membership_plan != '' 
    ORDER BY (plan_expire_date >= NOW()) DESC, plan_expire_date DESC
");
$plan_members = $active_members_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Membership Requests & Payment Attempts
$req_stmt = $pdo->query("
    SELECT mr.*, m.full_name, m.email, m.phone, m.membership_id 
    FROM membership_requests mr 
    LEFT JOIN members m ON mr.member_id = m.id 
    ORDER BY mr.created_at DESC
");
$requests = $req_stmt->fetchAll(PDO::FETCH_ASSOC);

$plan_meta = [
    'General' => ['name' => 'সাধারণ পাঠক', 'code' => 'GEN'],
    'BookLover' => ['name' => 'নিয়মিত পাঠক', 'code' => 'BLVR'],
    'Collector' => ['name' => 'সাহিত্য অনুরাগী', 'code' => 'COL']
];

$total_active_subscribers = 0;
$total_expired_subscribers = 0;
$now_time = time();

foreach ($plan_members as $pm) {
    if (!empty($pm['plan_expire_date']) && strtotime($pm['plan_expire_date']) < $now_time) {
        $total_expired_subscribers++;
    } else {
        $total_active_subscribers++;
    }
}

$total_membership_revenue = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM membership_requests WHERE status = 'Confirmed'")->fetchColumn();
$pending_req_count = (int)$pdo->query("SELECT COUNT(*) FROM membership_requests WHERE status = 'Pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title><?php echo $page_title; ?> - অন্তমীল এডমিন</title>
</head>
<body class="bg-[#faf8f5] text-stone-900 font-anek antialiased min-h-screen flex selection:bg-stone-200 selection:text-stone-900">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="flex-1 min-w-0 flex flex-col min-h-screen bg-[#faf8f5] lg:pl-64">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <main class="flex-1 p-6 lg:p-10 space-y-8 max-w-7xl w-full mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#e7e3da] pb-6">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-stone-900"><?php echo $page_title; ?></h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-[#f4f1ea] text-stone-700 border border-[#e7e3da]">
                            <?php echo bn_num($total_active_subscribers); ?> জন সক্রিয় প্ল্যানধারী
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-light mt-1">সদস্যদের সক্রিয় মেম্বারশিপ প্ল্যান, মেয়াদ ও রিকোয়েস্ট হিস্ট্রি পরিচালনা করুন।</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="/admin/members" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-[#f4f1ea] text-stone-700 border border-[#d8d3c7] rounded-lg text-xs font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>সকল সদস্য তালিকা</span>
                    </a>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">সক্রিয় মেম্বার</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2"><?php echo bn_num($total_active_subscribers); ?></div>
                    <span class="text-[11px] text-stone-400 font-mono mt-0.5 block">বর্তমানে প্ল্যান সক্রিয় রয়েছে</span>
                </div>
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মেয়াদোত্তীর্ণ প্ল্যান</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-amber-600 mt-2"><?php echo bn_num($total_expired_subscribers); ?></div>
                    <span class="text-[11px] text-stone-400 font-mono mt-0.5 block">রিনিউয়াল বা নবায়ন প্রয়োজন</span>
                </div>
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মেম্বারশিপ আয়</span>
                        <span class="text-xs font-mono font-bold text-stone-500">BDT</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">৳<?php echo bn_num(number_format($total_membership_revenue)); ?></div>
                    <span class="text-[11px] text-stone-400 font-mono mt-0.5 block">অনুমোদিত সাবস্ক্রিপশন ফি</span>
                </div>
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">রিকোয়েস্ট ও পেন্ডিং</span>
                        <span class="w-2 h-2 rounded-full <?php echo $pending_req_count > 0 ? 'bg-red-500' : 'bg-stone-300'; ?>"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2"><?php echo bn_num(count($requests)); ?></div>
                    <span class="text-[11px] <?php echo $pending_req_count > 0 ? 'text-amber-600 font-semibold' : 'text-stone-400'; ?> font-mono mt-0.5 block">
                        <?php echo $pending_req_count > 0 ? bn_num($pending_req_count) . " টি পেন্ডিং রিকোয়েস্ট" : "সব রিকোয়েস্ট সম্পন্ন"; ?>
                    </span>
                </div>
            </div>

            <!-- Sub-Tabs Navigation -->
            <div class="flex items-center gap-2 border-b border-[#e7e3da] pb-px">
                <button onclick="switchMembershipTab('active-members')" id="tab-btn-active"
                        class="px-4 py-2.5 text-xs font-semibold font-mono border-b-2 border-stone-900 text-stone-900 transition-all flex items-center gap-2">
                    <span>প্ল্যানধারী সদস্যবৃন্দ</span>
                    <span class="px-1.5 py-0.5 bg-[#f4f1ea] text-stone-700 border border-[#d8d3c7] rounded text-[10px]"><?php echo count($plan_members); ?></span>
                </button>
                <button onclick="switchMembershipTab('requests')" id="tab-btn-requests"
                        class="px-4 py-2.5 text-xs font-semibold font-mono border-b-2 border-transparent text-stone-500 hover:text-stone-900 transition-all flex items-center gap-2">
                    <span>রিকোয়েস্ট ও ট্রানজ্যাকশন হিস্ট্রি</span>
                    <?php if ($pending_req_count > 0): ?>
                        <span class="px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] border border-amber-200"><?php echo $pending_req_count; ?></span>
                    <?php else: ?>
                        <span class="px-1.5 py-0.5 bg-[#f4f1ea] text-stone-500 border border-[#d8d3c7] rounded text-[10px]"><?php echo count($requests); ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Subtab 1: Active Plan Members -->
            <div id="subcontent-active" class="space-y-6">
                <!-- Search & Filters -->
                <div class="bg-white border border-[#e7e3da] p-3 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
                    <div class="relative w-full sm:w-80">
                        <input type="text" id="activePlanSearch" onkeyup="filterPlanMembers()" placeholder="সদস্যের নাম, ফোন, আইডি খুঁজুন..."
                               class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-8 pr-3 py-1.5 text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                        <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select id="activePlanTypeFilter" onchange="filterPlanMembers()" class="bg-white border border-[#d8d3c7] rounded-lg px-3 py-1.5 text-xs font-mono text-stone-700 focus:outline-none focus:border-stone-800">
                            <option value="">সকল প্ল্যান (All)</option>
                            <option value="General">সাধারণ পাঠক</option>
                            <option value="BookLover">নিয়মিত পাঠক</option>
                            <option value="Collector">সাহিত্য অনুরাগী</option>
                        </select>
                        <select id="activePlanStatusFilter" onchange="filterPlanMembers()" class="bg-white border border-[#d8d3c7] rounded-lg px-3 py-1.5 text-xs font-mono text-stone-700 focus:outline-none focus:border-stone-800">
                            <option value="">সকল স্ট্যাটাস</option>
                            <option value="active">সক্রিয়</option>
                            <option value="expired">মেয়াদ শেষ</option>
                        </select>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                                <tr>
                                    <th class="py-3.5 px-5 font-semibold">সদস্য ও মেম্বার আইডি</th>
                                    <th class="py-3.5 px-5 font-semibold">যোগাযোগ</th>
                                    <th class="py-3.5 px-5 font-semibold">প্ল্যান টাইপ</th>
                                    <th class="py-3.5 px-5 font-semibold">মেয়াদ</th>
                                    <th class="py-3.5 px-5 font-semibold">ওয়ালেট ব্যালেন্স</th>
                                    <th class="py-3.5 px-5 font-semibold text-right">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                                <?php if (empty($plan_members)): ?>
                                    <tr>
                                        <td colspan="6" class="py-16 text-center text-stone-400">
                                            <p class="text-sm font-medium">কোনো প্ল্যানধারী সদস্য নেই।</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($plan_members as $member):
                                        $plan_key = $member['membership_plan'];
                                        $meta = $plan_meta[$plan_key] ?? ['name' => $plan_key, 'code' => 'CUSTOM'];
                                        $is_expired = false;
                                        $expire_text = "অনির্দিষ্ট";

                                        if (!empty($member['plan_expire_date']) && $member['plan_expire_date'] !== '0000-00-00 00:00:00') {
                                            $exp_timestamp = strtotime($member['plan_expire_date']);
                                            $diff_days = (int)ceil(($exp_timestamp - time()) / 86400);

                                            if ($diff_days < 0) {
                                                $is_expired = true;
                                                $expire_text = date('d M Y', $exp_timestamp) . " (মেয়াদোত্তীর্ণ)";
                                            } else {
                                                $expire_text = date('d M Y', $exp_timestamp) . " ({$diff_days} দিন বাকি)";
                                            }
                                        }

                                        $search_haystack = strtolower(($member['full_name'] ?? '') . ' ' . ($member['phone'] ?? '') . ' ' . ($member['email'] ?? '') . ' ' . ($member['membership_id'] ?? ''));
                                        ?>
                                        <tr class="plan-member-row hover:bg-[#faf8f5] transition-colors"
                                            data-search="<?php echo htmlspecialchars($search_haystack); ?>"
                                            data-plan="<?php echo htmlspecialchars($plan_key); ?>"
                                            data-status="<?php echo $is_expired ? 'expired' : 'active'; ?>">
                                            <td class="py-4 px-5">
                                                <div class="font-medium text-stone-900 text-sm">
                                                    <?php echo htmlspecialchars($member['full_name'] ?: 'N/A'); ?>
                                                </div>
                                                <span class="inline-block text-[11px] font-mono text-stone-400 mt-0.5">
                                                    <?php echo htmlspecialchars($member['membership_id'] ?: 'OM-NEW'); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-5 font-mono text-xs">
                                                <div class="text-stone-800"><?php echo htmlspecialchars($member['phone'] ?: 'N/A'); ?></div>
                                                <div class="text-stone-500 text-[11px] truncate max-w-[180px]"><?php echo htmlspecialchars($member['email'] ?: 'No email'); ?></div>
                                            </td>
                                            <td class="py-4 px-5">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-[#f4f1ea] text-stone-700 border border-[#d8d3c7]">
                                                    <?php echo $meta['name']; ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-5 font-mono text-xs <?php echo $is_expired ? 'text-red-600 font-semibold' : 'text-stone-700'; ?>">
                                                <?php echo $expire_text; ?>
                                            </td>
                                            <td class="py-4 px-5 font-mono text-xs font-medium text-stone-900">
                                                ৳<?php echo bn_num(number_format($member['acc_balance'] ?? 0)); ?>
                                            </td>
                                            <td class="py-4 px-5 text-right font-mono">
                                                <a href="/admin/members" class="px-3 py-1.5 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-700 border border-[#d8d3c7] rounded-lg text-xs transition-colors">
                                                    প্রোফাইল →
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Subtab 2: Requests & Transaction History -->
            <div id="subcontent-requests" class="space-y-6 hidden">
                <!-- Search & Status Filter -->
                <div class="bg-white border border-[#e7e3da] p-3 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
                    <div class="relative w-full sm:w-80">
                        <input type="text" id="requestsSearchInput" onkeyup="filterRequests()" placeholder="নাম, ফোন বা TrxID খুঁজুন..."
                               class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-8 pr-3 py-1.5 text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                        <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto">
                        <button onclick="setRequestStatusFilter('all')" class="rfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-stone-900 text-white border border-stone-900" data-status="all">সকল</button>
                        <button onclick="setRequestStatusFilter('Pending')" class="rfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]" data-status="Pending">পেন্ডিং</button>
                        <button onclick="setRequestStatusFilter('Confirmed')" class="rfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]" data-status="Confirmed">অনুমোদিত</button>
                        <button onclick="setRequestStatusFilter('Cancelled')" class="rfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]" data-status="Cancelled">বাতিল</button>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                                <tr>
                                    <th class="py-3.5 px-5 font-semibold">তারিখ ও আইডি</th>
                                    <th class="py-3.5 px-5 font-semibold">সদস্যের নাম ও যোগাযোগ</th>
                                    <th class="py-3.5 px-5 font-semibold">অনুরোধকৃত প্ল্যান</th>
                                    <th class="py-3.5 px-5 font-semibold">পেমেন্ট মেথড ও TrxID</th>
                                    <th class="py-3.5 px-5 font-semibold">ফি</th>
                                    <th class="py-3.5 px-5 font-semibold">স্ট্যাটাস</th>
                                    <th class="py-3.5 px-5 font-semibold text-right">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="7" class="py-16 text-center text-stone-400">
                                            <p class="text-sm font-medium">কোনো মেম্বারশিপ রিকোয়েস্ট পাওয়া যায়নি।</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $req):
                                        $plan_title = $plan_meta[$req['plan']]['name'] ?? $req['plan'];
                                        $search_req = strtolower(($req['full_name'] ?? '') . ' ' . ($req['phone'] ?? '') . ' ' . ($req['email'] ?? '') . ' ' . ($req['trx_id'] ?? '') . ' req-' . $req['id']);
                                        ?>
                                        <tr class="request-row hover:bg-[#faf8f5] transition-colors"
                                            data-search="<?php echo htmlspecialchars($search_req); ?>"
                                            data-status="<?php echo htmlspecialchars($req['status']); ?>">
                                            <td class="py-4 px-5 font-mono text-xs">
                                                <span class="font-semibold text-stone-900 block">REQ-#<?php echo $req['id']; ?></span>
                                                <span class="text-[11px] text-stone-400"><?php echo date('d M Y, h:i A', strtotime($req['created_at'])); ?></span>
                                            </td>
                                            <td class="py-4 px-5">
                                                <span class="font-medium text-stone-900 text-sm block"><?php echo htmlspecialchars($req['full_name'] ?: 'N/A'); ?></span>
                                                <span class="text-xs text-stone-500 font-mono"><?php echo htmlspecialchars($req['phone']); ?></span>
                                            </td>
                                            <td class="py-4 px-5">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-[#f4f1ea] text-stone-700 border border-[#d8d3c7]">
                                                    <?php echo $plan_title; ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-5 font-mono text-xs">
                                                <span class="font-semibold text-stone-800 block"><?php echo strtoupper($req['payment_method']); ?></span>
                                                <span class="text-stone-500 text-[11px]"><?php echo htmlspecialchars($req['trx_id'] ?: 'N/A'); ?></span>
                                            </td>
                                            <td class="py-4 px-5 font-mono text-xs font-semibold text-stone-900">
                                                ৳<?php echo bn_num(number_format($req['amount'], 2)); ?>
                                            </td>
                                            <td class="py-4 px-5">
                                                <?php if ($req['status'] === 'Confirmed'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Confirmed
                                                    </span>
                                                <?php elseif ($req['status'] === 'Cancelled'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-700 border border-red-200 font-mono">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                        Cancelled
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-5 text-right font-mono">
                                                <?php if ($req['status'] === 'Pending'): ?>
                                                    <div class="flex items-center justify-end gap-1.5">
                                                        <button onclick="updateMembershipRequest(<?php echo $req['id']; ?>, 'confirm')"
                                                                class="px-2.5 py-1 bg-stone-900 hover:bg-stone-800 text-white rounded text-xs font-semibold transition-colors shadow-xs">
                                                            অনুমোদন
                                                        </button>
                                                        <button onclick="updateMembershipRequest(<?php echo $req['id']; ?>, 'cancel')"
                                                                class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded text-xs transition-colors">
                                                            বাতিল
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-stone-400 text-xs">সম্পন্ন</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-stone-900 text-white border border-stone-800 px-4 py-3 rounded-lg shadow-2xl flex items-center gap-3 text-xs font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span id="toast-message">নোটিফিকেশন বার্তা</span>
        </div>
    </div>

    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        function switchMembershipTab(tab) {
            const btnActive = document.getElementById('tab-btn-active');
            const btnReqs = document.getElementById('tab-btn-requests');
            const contentActive = document.getElementById('subcontent-active');
            const contentReqs = document.getElementById('subcontent-requests');

            if (tab === 'active-members') {
                contentActive.classList.remove('hidden');
                contentReqs.classList.add('hidden');
                btnActive.className = "px-4 py-2.5 text-xs font-semibold font-mono border-b-2 border-stone-900 text-stone-900 transition-all flex items-center gap-2";
                btnReqs.className = "px-4 py-2.5 text-xs font-semibold font-mono border-b-2 border-transparent text-stone-500 hover:text-stone-900 transition-all flex items-center gap-2";
            } else {
                contentActive.classList.add('hidden');
                contentReqs.classList.remove('hidden');
                btnReqs.className = "px-4 py-2.5 text-xs font-semibold font-mono border-b-2 border-stone-900 text-stone-900 transition-all flex items-center gap-2";
                btnActive.className = "px-4 py-2.5 text-xs font-semibold font-mono border-b-2 border-transparent text-stone-500 hover:text-stone-900 transition-all flex items-center gap-2";
            }
        }

        function filterPlanMembers() {
            const search = (document.getElementById('activePlanSearch')?.value || '').toLowerCase().trim();
            const planFilter = document.getElementById('activePlanTypeFilter')?.value || '';
            const statusFilter = document.getElementById('activePlanStatusFilter')?.value || '';

            const rows = document.querySelectorAll('.plan-member-row');
            rows.forEach(row => {
                const hay = row.getAttribute('data-search') || '';
                const plan = row.getAttribute('data-plan') || '';
                const status = row.getAttribute('data-status') || '';

                const matchSearch = !search || hay.includes(search);
                const matchPlan = !planFilter || plan === planFilter;
                const matchStatus = !statusFilter || status === statusFilter;

                if (matchSearch && matchPlan && matchStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        let currentRequestsStatusFilter = 'all';

        function setRequestStatusFilter(status) {
            currentRequestsStatusFilter = status;
            document.querySelectorAll('.rfilter-btn').forEach(btn => {
                if (btn.getAttribute('data-status') === status) {
                    btn.className = "rfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-stone-900 text-white border border-stone-900";
                } else {
                    btn.className = "rfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]";
                }
            });
            filterRequests();
        }

        function filterRequests() {
            const search = (document.getElementById('requestsSearchInput')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.request-row');
            rows.forEach(row => {
                const hay = row.getAttribute('data-search') || '';
                const status = row.getAttribute('data-status') || '';

                const matchSearch = !search || hay.includes(search);
                const matchStatus = (currentRequestsStatusFilter === 'all') || (status === currentRequestsStatusFilter);

                if (matchSearch && matchStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function updateMembershipRequest(id, action) {
            if (!confirm(`আপনি কি নিশ্চিত যে এই রিকোয়েস্টটি ${action} করতে চান?`)) return;

            const formData = new FormData();
            formData.append('request_id', id);
            formData.append('action', action);

            fetch('/admin/dashboard/update_membership_request.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(`রিকোয়েস্ট সফলভাবে ${action === 'confirm' ? 'অনুমোদিত' : 'বাতিল'} হয়েছে!`);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert(data.message || 'ত্রুটি হয়েছে।');
                }
            })
            .catch(() => alert('সার্ভার সংযোগে ত্রুটি।'));
        }
    </script>
</body>
</html>
