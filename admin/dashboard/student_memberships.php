<?php
// admin/dashboard/student_memberships.php
// Dedicated Management Page for "বইয়ের আনন্দ-পাঠ" Student Memberships (White & Cream UI)

require_once __DIR__ . '/includes/admin_helpers.php';
$current_page = 'student_memberships';
$page_title = 'স্টুডেন্ট মেম্বারশিপ';

// Counts for Badges & Summary
$pending_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Pending'")->fetchColumn();
$approved_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Approved'")->fetchColumn();
$rejected_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Rejected'")->fetchColumn();
$total_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests")->fetchColumn();

// Fetch all student membership requests with member details
$stmt = $pdo->query("
    SELECT sr.*, m.full_name, m.email, m.phone, m.membership_id, m.membership_plan, m.plan_expire_date, m.created_at as member_join_date
    FROM student_membership_requests sr
    JOIN members m ON sr.member_id = m.id
    ORDER BY 
        CASE sr.status 
            WHEN 'Pending' THEN 1 
            WHEN 'Approved' THEN 2 
            WHEN 'Rejected' THEN 3 
            ELSE 4 
        END,
        sr.created_at DESC
");
$student_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                            মোট <?php echo bn_num($total_student_requests); ?> টি আবেদন
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-light mt-1">কক্সবাজার জেলার শিক্ষার্থীদের ফ্রি মেম্বারশিপ আবেদন ও আইডি কার্ড ভেরিফিকেশন।</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-[#f4f1ea] text-stone-700 border border-[#d8d3c7] rounded-lg text-xs font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>রিফ্রেশ</span>
                    </button>
                </div>
            </div>

            <!-- Toast Container -->
            <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">পেন্ডিং আবেদন</span>
                        <span class="w-2 h-2 rounded-full <?php echo $pending_student_requests > 0 ? 'bg-amber-500 animate-pulse' : 'bg-stone-300'; ?>"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($pending_student_requests); ?>
                    </div>
                    <span class="text-[11px] text-stone-400 font-mono mt-0.5 block">যাচাইয়ের অপেক্ষায় রয়েছে</span>
                </div>

                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">অনুমোদিত শিক্ষার্থী</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($approved_student_requests); ?>
                    </div>
                    <span class="text-[11px] text-stone-400 font-mono mt-0.5 block">সক্রিয় স্টুডেন্ট মেম্বারশিপ</span>
                </div>

                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">বাতিলকৃত আবেদন</span>
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($rejected_student_requests); ?>
                    </div>
                    <span class="text-[11px] text-stone-400 font-mono mt-0.5 block">অসম্পূর্ণ বা অবৈধ আইডি কার্ড</span>
                </div>

                <div class="bg-white border border-[#e7e3da] p-5 rounded-xl shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-500">মোট আবেদন</span>
                        <span class="w-2 h-2 rounded-full bg-stone-400"></span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-stone-900 mt-2">
                        <?php echo bn_num($total_student_requests); ?>
                    </div>
                    <span class="text-[11px] text-stone-400 font-mono mt-0.5 block">সর্বমোট প্রাপ্ত আবেদনপত্র</span>
                </div>
            </div>

            <!-- Filters & Search Controls Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl p-4 space-y-3 shadow-xs">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <!-- Status Filter Buttons -->
                    <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto">
                        <button onclick="filterStudentStatus('all', this)" class="sfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-stone-900 text-white border border-stone-900" data-status="all">
                            সকল (<?php echo $total_student_requests; ?>)
                        </button>
                        <button onclick="filterStudentStatus('Pending', this)" class="sfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]" data-status="Pending">
                            পেন্ডিং (<?php echo $pending_student_requests; ?>)
                        </button>
                        <button onclick="filterStudentStatus('Approved', this)" class="sfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]" data-status="Approved">
                            অনুমোদিত (<?php echo $approved_student_requests; ?>)
                        </button>
                        <button onclick="filterStudentStatus('Rejected', this)" class="sfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]" data-status="Rejected">
                            বাতিল (<?php echo $rejected_student_requests; ?>)
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-80">
                        <input type="text" id="studentSearchInput" onkeyup="searchStudentRequests()"
                            placeholder="নাম, ফোন, প্রতিষ্ঠান বা আইডি দিয়ে খুঁজুন..."
                            class="w-full bg-white border border-[#d8d3c7] rounded-lg pl-8 pr-3 py-2 text-xs text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800 font-sans">
                        <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Requests Table Card -->
            <div class="bg-white border border-[#e7e3da] rounded-xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#f4f1ea] text-stone-700 uppercase font-mono text-[11px] tracking-wider border-b border-[#e7e3da]">
                            <tr>
                                <th class="py-3.5 px-5 font-semibold">শিক্ষার্থীর তথ্য</th>
                                <th class="py-3.5 px-5 font-semibold">শিক্ষা প্রতিষ্ঠান ও শ্রেণি</th>
                                <th class="py-3.5 px-5 font-semibold">আইডি কার্ড প্রিভিউ</th>
                                <th class="py-3.5 px-5 font-semibold">আবেদনের তারিখ</th>
                                <th class="py-3.5 px-5 font-semibold">স্ট্যাটাস</th>
                                <th class="py-3.5 px-5 font-semibold text-right">পদক্ষেপ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7e3da] text-stone-800 font-sans" id="studentRequestsTableBody">
                            <?php if (empty($student_requests)): ?>
                                <tr>
                                    <td colspan="6" class="py-16 text-center text-stone-400 font-mono text-xs">
                                        কোনো স্টুডেন্ট মেম্বারশিপ আবেদন পাওয়া যায়নি।
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($student_requests as $req):
                                    $search_token = strtolower(
                                        ($req['full_name'] ?? '') . ' ' .
                                        ($req['phone'] ?? '') . ' ' .
                                        ($req['institution_name'] ?? '') . ' ' .
                                        ($req['student_id_number'] ?? '') . ' ' .
                                        ($req['membership_id'] ?? '')
                                    );
                                    $is_pending = ($req['status'] === 'Pending');
                                    $is_approved = ($req['status'] === 'Approved');
                                    $is_rejected = ($req['status'] === 'Rejected');
                                ?>
                                    <tr class="student-row hover:bg-[#faf8f5] transition-colors"
                                        data-status="<?php echo htmlspecialchars($req['status']); ?>"
                                        data-search="<?php echo htmlspecialchars($search_token); ?>">
                                        
                                        <!-- Student Info -->
                                        <td class="py-4 px-5">
                                            <div class="font-medium text-stone-900 text-sm">
                                                <?php echo htmlspecialchars($req['full_name']); ?>
                                            </div>
                                            <div class="text-xs font-mono text-stone-600 mt-0.5">
                                                <?php echo htmlspecialchars($req['phone']); ?>
                                            </div>
                                            <span class="inline-block text-[11px] font-mono text-stone-400 mt-0.5">
                                                #<?php echo htmlspecialchars($req['membership_id'] ?? 'OM-' . $req['member_id']); ?>
                                            </span>
                                        </td>

                                        <!-- Institution & Class/ID -->
                                        <td class="py-4 px-5">
                                            <div class="text-stone-800 font-medium">
                                                <?php echo htmlspecialchars($req['institution_name'] ?: 'শিক্ষা প্রতিষ্ঠান'); ?>
                                            </div>
                                            <?php if (!empty($req['student_id_number'])): ?>
                                                <div class="text-xs font-mono text-stone-500 mt-0.5">
                                                    রোল/আইডি: <?php echo htmlspecialchars($req['student_id_number']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($req['age'])): ?>
                                                <div class="text-[11px] font-mono text-stone-400 mt-0.5">
                                                    বয়স: <?php echo bn_num((int)$req['age']); ?> বছর
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- ID Card Preview -->
                                        <td class="py-4 px-5">
                                            <?php 
                                             $img_url = '';
                                            if (!empty($req['student_id_image'])) {
                                                $raw_img = ltrim($req['student_id_image'], '/');
                                                $img_url = (strpos($raw_img, 'assets/uploads/student_ids/') === 0) ? '/' . $raw_img : '/assets/uploads/student_ids/' . $raw_img;
                                            }
                                            ?>
                                            <?php if (!empty($img_url)): ?>
                                                <button onclick="previewIdCard('<?php echo htmlspecialchars($img_url); ?>', '<?php echo htmlspecialchars(addslashes($req['full_name'])); ?>')"
                                                    class="flex items-center gap-2 group p-1 bg-[#f4f1ea] hover:bg-[#eae5db] border border-[#d8d3c7] rounded-lg transition-colors cursor-pointer">
                                                    <div class="w-12 h-8 rounded overflow-hidden bg-stone-200 shrink-0">
                                                        <img src="<?php echo htmlspecialchars($img_url); ?>"
                                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                                             alt="ID Card">
                                                    </div>
                                                    <span class="text-[11px] font-mono text-stone-700 pr-2">দেখুন ↗</span>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-stone-400 font-mono text-xs">ইমেজ নেই</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Application Date -->
                                        <td class="py-4 px-5 font-mono text-xs text-stone-500">
                                            <?php echo date('d M Y, h:i A', strtotime($req['created_at'])); ?>
                                        </td>

                                        <!-- Status -->
                                        <td class="py-4 px-5">
                                            <?php if ($is_approved): ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Approved
                                                </span>
                                            <?php elseif ($is_rejected): ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-red-50 text-red-700 border border-red-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    Rejected
                                                </span>
                                                <?php 
                                                $reason_text = $req['rejection_reason'] ?: ($req['admin_notes'] ?? '');
                                                if (!empty($reason_text)): ?>
                                                    <p class="text-[10px] text-stone-500 mt-1 max-w-[150px] truncate" title="<?php echo htmlspecialchars($reason_text); ?>">
                                                        কারণ: <?php echo htmlspecialchars($reason_text); ?>
                                                    </p>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-4 px-5 text-right font-mono">
                                            <?php if ($is_pending): ?>
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button onclick="approveStudentRequest(<?php echo (int)$req['id']; ?>)"
                                                        class="px-2.5 py-1 bg-stone-900 hover:bg-stone-800 text-white font-semibold rounded-lg text-xs transition-colors shadow-xs">
                                                        অনুমোদন
                                                    </button>
                                                    <button onclick="openRejectModal(<?php echo (int)$req['id']; ?>)"
                                                        class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-xs transition-colors">
                                                        বাতিল
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-stone-400 text-xs">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- ID Card Image Preview Modal -->
    <div id="idCardModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-stone-900/50 backdrop-blur-xs">
        <div class="bg-white border border-[#e7e3da] max-w-2xl w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col">
            <div class="p-4 border-b border-[#e7e3da] flex items-center justify-between bg-[#fbf9f5]">
                <h3 id="idCardModalTitle" class="text-sm font-bold text-stone-900 font-mono">—</h3>
                <button onclick="closeIdCardModal()" class="text-stone-400 hover:text-stone-900 p-1 rounded-lg hover:bg-stone-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-[#f4f1ea] min-h-[300px]">
                <img id="idCardModalImage" src="" alt="ID Card Image" class="max-h-[70vh] max-w-full object-contain rounded-lg shadow-sm border border-[#e7e3da]">
            </div>
        </div>
    </div>

    <!-- Rejection Note Modal -->
    <div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-stone-900/50 backdrop-blur-xs">
        <div class="bg-white border border-[#e7e3da] w-full max-w-md rounded-2xl shadow-2xl overflow-hidden text-xs font-sans">
            <div class="p-6 border-b border-[#e7e3da] flex justify-between items-center bg-[#fbf9f5]">
                <h3 class="text-sm font-bold text-stone-900 font-mono uppercase">আবেদন বাতিলের কারণ</h3>
                <button onclick="closeRejectModal()" class="text-stone-400 hover:text-stone-900 p-1 rounded-lg hover:bg-stone-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form onsubmit="handleRejectSubmit(event)" class="p-6 space-y-4">
                <input type="hidden" id="rejectRequestId" name="request_id">
                <div>
                    <label class="block text-stone-600 font-mono mb-1.5">বাতিলের কারণ লিখুন *</label>
                    <textarea id="rejectNote" name="admin_note" rows="3" required placeholder="যেমন: আইডি কার্ডের ছবি স্পষ্ট নয় বা মেয়াদ শেষ।"
                        class="w-full bg-white border border-[#d8d3c7] rounded-lg px-3 py-2 text-stone-900 placeholder-stone-400 focus:outline-none focus:border-stone-800"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-3 border-t border-[#e7e3da]">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-[#f4f1ea] hover:bg-[#eae5db] text-stone-700 border border-[#d8d3c7] rounded-lg">বাতিল</button>
                    <button type="submit" id="rejectSubmitBtn" class="px-5 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg shadow-xs">নিশ্চিত করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStudentFilter = 'all';

        function showToast(msg) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `bg-stone-900 text-white border border-stone-800 px-4 py-3 rounded-lg shadow-2xl text-xs font-mono flex items-center gap-3 transform transition-all duration-300 translate-y-2 opacity-0`;
            toast.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span><span>${msg}</span>`;
            container.appendChild(toast);
            
            setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 50);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function filterStudentStatus(status, btn) {
            currentStudentFilter = status;
            document.querySelectorAll('.sfilter-btn').forEach(b => {
                b.className = 'sfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-[#f4f1ea] text-stone-600 hover:text-stone-900 border border-[#d8d3c7]';
            });
            if (btn) {
                btn.className = 'sfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold font-mono bg-stone-900 text-white border border-stone-900';
            }
            applyStudentFilter();
        }

        function searchStudentRequests() {
            applyStudentFilter();
        }

        function applyStudentFilter() {
            const search = (document.getElementById('studentSearchInput')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.student-row');

            rows.forEach(row => {
                const status = row.getAttribute('data-status') || '';
                const tokens = row.getAttribute('data-search') || '';

                const matchStatus = (currentStudentFilter === 'all') || (status === currentStudentFilter);
                const matchSearch = !search || tokens.includes(search);

                if (matchStatus && matchSearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function previewIdCard(src, name) {
            document.getElementById('idCardModalImage').src = src;
            document.getElementById('idCardModalTitle').innerText = name + ' — আইডি কার্ড';
            document.getElementById('idCardModal').classList.remove('hidden');
            document.getElementById('idCardModal').classList.add('flex');
        }

        function closeIdCardModal() {
            document.getElementById('idCardModal').classList.add('hidden');
            document.getElementById('idCardModal').classList.remove('flex');
        }

        function approveStudentRequest(id) {
            if (!confirm('আপনি কি নিশ্চিত যে এই আবেদনটি অনুমোদন করতে চান? এটি শিক্ষার্থীর অ্যাকাউন্ট ১ বছরের জন্য সক্রিয় করবে।')) return;

            const formData = new FormData();
            formData.append('request_id', id);
            formData.append('action', 'approve');

            fetch('/admin/dashboard/update_student_request.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('আবেদন সফলভাবে অনুমোদিত হয়েছে!');
                    setTimeout(() => location.reload(), 800);
                } else {
                    alert('ত্রুটি: ' + (data.message || 'অনুমোদন ব্যর্থ'));
                }
            })
            .catch(() => alert('সার্ভার সংযোগে ত্রুটি।'));
        }

        function openRejectModal(id) {
            document.getElementById('rejectRequestId').value = id;
            document.getElementById('rejectNote').value = '';
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejectModal').classList.add('flex');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectModal').classList.remove('flex');
        }

        function handleRejectSubmit(e) {
            e.preventDefault();
            const id = document.getElementById('rejectRequestId').value;
            const note = document.getElementById('rejectNote').value;
            const btn = document.getElementById('rejectSubmitBtn');

            btn.disabled = true;
            btn.textContent = 'প্রসেস হচ্ছে...';

            const formData = new FormData();
            formData.append('request_id', id);
            formData.append('action', 'reject');
            formData.append('admin_note', note);

            fetch('/admin/dashboard/update_student_request.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('আবেদন বাতিল করা হয়েছে।');
                    closeRejectModal();
                    setTimeout(() => location.reload(), 800);
                } else {
                    alert('ত্রুটি: ' + (data.message || 'বাতিল ব্যর্থ'));
                }
            })
            .catch(() => alert('সার্ভার সংযোগে ত্রুটি।'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'নিশ্চিত করুন';
            });
        }
    </script>
</body>
</html>
