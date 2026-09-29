<?php
// admin/dashboard/members.php
// Dedicated Management Page for All Registered Members & Assigning Membership Plans (White & Cream UI)

require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'members';
$page_title = 'মেম্বার লিস্ট';

// Fetch Overview KPI Counts
$total_members = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$active_paid_members = (int)$pdo->query("SELECT COUNT(*) FROM members WHERE membership_plan IN ('General', 'BookLover', 'Collector') AND (plan_expire_date >= NOW() OR plan_expire_date IS NULL)")->fetchColumn();
$student_members = (int)$pdo->query("SELECT COUNT(*) FROM members WHERE membership_plan = 'Student' OR (student_plan_expire_date IS NOT NULL AND student_plan_expire_date >= NOW())")->fetchColumn();
$free_members = (int)$pdo->query("SELECT COUNT(*) FROM members WHERE membership_plan IS NULL OR membership_plan = 'None' OR membership_plan = ''")->fetchColumn();

$pending_membership_requests = (int)$pdo->query("SELECT COUNT(*) FROM membership_requests WHERE status = 'Pending'")->fetchColumn();
$pending_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Pending'")->fetchColumn();

// Initial SSR Pagination (First 15 records)
$limit = 15;
$page = 1;
$offset = 0;

$stmt = $pdo->query("SELECT * FROM members ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}");
$initial_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_pages = max(1, (int)ceil($total_members / $limit));
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title><?php echo $page_title; ?> - অন্ত্যমিল অ্যাডমিন</title>
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
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-[#f4f1ea] text-stone-700 border border-[#ded8cc]">
                            মোট <?php echo bn_num($total_members); ?> জন সদস্য
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-normal mt-1">সকল নিবন্ধিত মেম্বারদের তালিকা, বিবরণ ও সাবস্ক্রিপশন প্ল্যান অ্যাসাইন করুন।</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-[#f3f0e8] text-stone-700 border border-[#d8d3c7] hover:border-stone-400 rounded-lg text-xs font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>রিফ্রেশ</span>
                    </button>
                </div>
            </div>

            <!-- Toast Container -->
            <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মোট মেম্বার</span>
                        <span class="w-2 h-2 rounded-full bg-stone-400"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($total_members); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">সর্বমোট নিবন্ধিত পাঠক</span>
                </div>

                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">পেইড প্ল্যানধারী</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($active_paid_members); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">জেনারেল / বুকলাভার / কালেক্টর</span>
                </div>

                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">স্টুডেন্ট মেম্বার</span>
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($student_members); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">যাচাইকৃত শিক্ষার্থী মেম্বার</span>
                </div>

                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">ফ্রি মেম্বার</span>
                        <span class="w-2 h-2 rounded-full bg-stone-400"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($free_members); ?>
                    </div>
                    <span class="text-[11px] text-stone-500 font-mono mt-0.5 block">কোনো প্ল্যান সক্রিয় নেই</span>
                </div>
            </div>

            <!-- Filters & Search Controls Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl p-4 space-y-3 shadow-xs">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <!-- Search Input -->
                    <div class="md:col-span-6 relative">
                        <input type="text" id="memberSearchInput" onkeyup="handleMemberSearch()"
                            placeholder="নাম, ফোন নম্বর, ইমেইল অথবা মেম্বার আইডি দিয়ে খুঁজুন..."
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-8 pr-3 py-2 text-xs text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                        <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Plan Filter -->
                    <div class="md:col-span-3">
                        <select id="memberPlanFilter" onchange="handleMemberFilter()"
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-800 focus:outline-none focus:border-stone-800 cursor-pointer">
                            <option value="all">সব প্ল্যান (All Plans)</option>
                            <option value="General">সাধারণ পাঠক (General)</option>
                            <option value="BookLover">নিয়মিত পাঠক (BookLover)</option>
                            <option value="Collector">সাহিত্য অনুরাগী (Collector)</option>
                            <option value="Student">স্টুডেন্ট মেম্বার (Student)</option>
                            <option value="None">ফ্রি অ্যাকাউন্ট (No Plan)</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="md:col-span-3">
                        <select id="memberStatusFilter" onchange="handleMemberFilter()"
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-xs font-mono text-stone-800 focus:outline-none focus:border-stone-800 cursor-pointer">
                            <option value="all">সব স্ট্যাটাস</option>
                            <option value="active">সক্রিয় প্ল্যান (Active)</option>
                            <option value="expired">মেয়াদোত্তীর্ণ (Expired)</option>
                            <option value="free">ফ্রি (Free)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Members Table Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                            <tr>
                                <th class="py-3.5 px-5 text-center w-16 font-bold">#</th>
                                <th class="py-3.5 px-5 font-bold">মেম্বার তথ্য</th>
                                <th class="py-3.5 px-5 font-bold">যোগাযোগ</th>
                                <th class="py-3.5 px-5 font-bold">প্ল্যান টাইপ</th>
                                <th class="py-3.5 px-5 font-bold">মেয়াদকাল</th>
                                <th class="py-3.5 px-5 font-bold">নিবন্ধনের তারিখ</th>
                                <th class="py-3.5 px-5 font-bold text-right">পদক্ষেপ</th>
                            </tr>
                        </thead>
                        <tbody id="membersTableBody" class="divide-y divide-[#e7e3da] text-stone-800 font-sans">
                            <!-- Populated via AJAX / Initial SSR -->
                            <?php if (empty($initial_members)): ?>
                                <tr>
                                    <td colspan="7" class="py-16 text-center text-stone-500 font-mono text-xs">কোনো মেম্বার পাওয়া যায়নি</td>
                                </tr>
                            <?php else:
                                $i = 0;
                                foreach ($initial_members as $member):
                                    $i++;
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
                                    <td class="py-4 px-5 text-center font-mono text-xs text-stone-500">
                                        <?php echo bn_num($i); ?>
                                    </td>
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
                                    <td class="py-4 px-5 font-mono text-xs">
                                        <div class="text-stone-900 font-semibold"><?php echo htmlspecialchars($member['phone'] ?: '—'); ?></div>
                                        <?php if (!empty($member['email'])): ?>
                                            <div class="text-[11px] text-stone-500 truncate max-w-[180px]"><?php echo htmlspecialchars($member['email']); ?></div>
                                        <?php endif; ?>
                                    </td>
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
                                    <td class="py-4 px-5 text-xs font-mono <?php echo $is_expired ? 'text-rose-600 font-semibold' : 'text-stone-600'; ?>">
                                        <?php echo $expire_text; ?>
                                    </td>
                                    <td class="py-4 px-5 text-xs text-stone-500 font-mono">
                                        <?php echo date('d M Y', strtotime($member['created_at'])); ?>
                                    </td>
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
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Container -->
                <div id="membersPaginationContainer"></div>
            </div>
        </main>
    </div>

    <!-- Apply/Assign Membership Modal (White & Cream Theme) -->
    <div id="applyMembershipModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-stone-900/40 backdrop-blur-xs">
        <div class="bg-white border border-[#e7e3da] w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col text-xs font-sans">
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#faf8f5]">
                <div>
                    <h3 class="text-base font-bold text-stone-900">মেম্বারশিপ প্ল্যান অ্যাসাইন / পরিবর্তন</h3>
                    <p class="text-xs text-stone-500 mt-0.5">সদস্যের মেম্বারশিপ প্ল্যান ও মেয়াদ নির্ধারণ করুন।</p>
                </div>
                <button onclick="closeApplyMembershipModal()" class="text-stone-400 hover:text-stone-800 p-1.5 rounded-lg hover:bg-[#eae5db]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="applyPlanForm" onsubmit="handleApplyPlanSubmit(event)" class="p-6 space-y-4">
                <input type="hidden" name="member_id" id="modal_member_id">

                <div class="p-3.5 bg-[#faf8f5] rounded-xl border border-[#e7e3da] space-y-1">
                    <div class="font-bold text-stone-900 text-sm" id="modal_member_name">—</div>
                    <div class="flex items-center gap-2 text-stone-500 font-mono text-xs">
                        <span id="modal_member_code">—</span>
                        <span>•</span>
                        <span id="modal_member_phone">—</span>
                    </div>
                </div>

                <div>
                    <label class="block font-mono text-stone-700 font-bold uppercase mb-1.5">মেম্বারশিপ প্ল্যান নির্বাচন করুন *</label>
                    <select name="plan" id="modal_plan_select" required
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 font-mono text-xs focus:outline-none focus:border-stone-800">
                        <option value="General">সাধারণ পাঠক (General Plan)</option>
                        <option value="BookLover">নিয়মিত পাঠক (BookLover Plan)</option>
                        <option value="Collector">সাহিত্য অনুরাগী (Collector Plan)</option>
                        <option value="Student">স্টুডেন্ট মেম্বার (Student Plan)</option>
                        <option value="None">কোনো প্ল্যান নয় (ফ্রি একাউন্ট)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-mono text-stone-700 font-bold uppercase mb-1.5">মেয়াদের সময়কাল (Duration) *</label>
                    <select name="duration" id="modal_duration_select" required
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 font-mono text-xs focus:outline-none focus:border-stone-800">
                        <option value="1_month">১ মাস (30 Days)</option>
                        <option value="3_months">৩ মাস (90 Days)</option>
                        <option value="6_months">৬ মাস (180 Days)</option>
                        <option value="1_year" selected>১ বছর (365 Days)</option>
                        <option value="lifetime">লাইফটাইম / অনির্দিষ্ট</option>
                    </select>
                </div>

                <div>
                    <label class="block font-mono text-stone-700 font-bold uppercase mb-1.5">মন্তব্য / নোট (ঐচ্ছিক)</label>
                    <input type="text" name="admin_note" placeholder="যেমন: ম্যানুয়াল রিনিউয়াল সম্পন্ন"
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder:text-stone-400 text-xs focus:outline-none focus:border-stone-800">
                </div>

                <div class="pt-4 border-t border-[#e7e3da] flex justify-end gap-3">
                    <button type="button" onclick="closeApplyMembershipModal()" class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-800 rounded-lg font-medium">
                        বাতিল
                    </button>
                    <button type="submit" id="applyPlanSubmitBtn" class="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white font-semibold rounded-lg shadow-xs">
                        প্ল্যান সেভ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let memberState = {
            search: '',
            plan: 'all',
            status: 'all',
            page: 1,
            limit: 15
        };

        function showToast(msg) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `bg-stone-900 text-white px-4 py-3 rounded-lg shadow-2xl text-xs font-mono flex items-center gap-3 transform transition-all duration-300 translate-y-2 opacity-0`;
            toast.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span><span>${msg}</span>`;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 50);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        let memberDebounce;
        function handleMemberSearch() {
            clearTimeout(memberDebounce);
            memberDebounce = setTimeout(() => {
                loadMembers(1);
            }, 300);
        }

        function handleMemberFilter() {
            loadMembers(1);
        }

        function loadMembers(page = 1) {
            const search = document.getElementById('memberSearchInput').value;
            const plan = document.getElementById('memberPlanFilter').value;
            const status = document.getElementById('memberStatusFilter').value;

            memberState.page = page;
            memberState.search = search;
            memberState.plan = plan;
            memberState.status = status;

            const tbody = document.getElementById('membersTableBody');
            tbody.innerHTML = '<tr><td colspan="7" class="py-16 text-center text-stone-500 font-mono text-xs">লোড হচ্ছে...</td></tr>';

            const url = `/admin/dashboard/fetch_members.php?page=${page}&limit=${memberState.limit}&search=${encodeURIComponent(search)}&plan=${encodeURIComponent(plan)}&status=${encodeURIComponent(status)}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        tbody.innerHTML = data.html;
                        document.getElementById('membersPaginationContainer').innerHTML = data.pagination;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center text-rose-500">ডাটা লোড করতে সমস্যা হয়েছে।</td></tr>';
                    }
                })
                .catch(() => {
                    tbody.innerHTML = '<tr><td colspan="7" class="py-12 text-center text-rose-500">সার্ভার সমস্যা।</td></tr>';
                });
        }

        function openApplyMembershipModal(member) {
            document.getElementById('modal_member_id').value = member.id;
            document.getElementById('modal_member_name').innerText = member.name || 'N/A';
            document.getElementById('modal_member_code').innerText = member.membership_id || 'OM-NEW';
            document.getElementById('modal_member_phone').innerText = member.phone || '—';
            
            if (member.plan && member.plan !== 'None') {
                document.getElementById('modal_plan_select').value = member.plan;
            } else {
                document.getElementById('modal_plan_select').value = 'General';
            }

            document.getElementById('applyMembershipModal').classList.remove('hidden');
            document.getElementById('applyMembershipModal').classList.add('flex');
        }

        function closeApplyMembershipModal() {
            document.getElementById('applyMembershipModal').classList.add('hidden');
            document.getElementById('applyMembershipModal').classList.remove('flex');
        }

        function handleApplyPlanSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const btn = document.getElementById('applyPlanSubmitBtn');

            btn.disabled = true;
            btn.textContent = 'সংরক্ষণ হচ্ছে...';

            fetch('/admin/dashboard/apply_member_plan.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('মেম্বারশিপ প্ল্যান সফলভাবে আপডেট করা হয়েছে!');
                    closeApplyMembershipModal();
                    loadMembers(memberState.page);
                } else {
                    alert('ত্রুটি: ' + (data.message || 'প্ল্যান আপডেট ব্যর্থ'));
                }
            })
            .catch(() => alert('সার্ভার সমস্যা।'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'প্ল্যান সেভ করুন';
            });
        }

        // Initialize pagination on load
        window.addEventListener('DOMContentLoaded', () => {
            loadMembers(1);
        });
    </script>
</body>
</html>
