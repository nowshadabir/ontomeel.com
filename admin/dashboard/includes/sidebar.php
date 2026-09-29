<?php
// admin/dashboard/includes/sidebar.php
// Clean White & Cream sidebar for all admin pages

if (!isset($pdo)) {
    require_once __DIR__ . '/../../../includes/db_connect.php';
}

if (!isset($current_page)) {
    $current_page = 'overview';
}

// Fetch badge counts if not already provided
if (!isset($total_members)) {
    try {
        $total_members = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
    } catch (Exception $e) {
        $total_members = 0;
    }
}

if (!isset($pending_student_requests)) {
    try {
        $pending_student_requests = (int)$pdo->query("SELECT COUNT(*) FROM student_membership_requests WHERE status = 'Pending'")->fetchColumn();
    } catch (Exception $e) {
        $pending_student_requests = 0;
    }
}

if (!isset($pending_membership_requests)) {
    try {
        $pending_membership_requests = (int)$pdo->query("SELECT COUNT(*) FROM membership_requests WHERE status = 'Pending'")->fetchColumn();
    } catch (Exception $e) {
        $pending_membership_requests = 0;
    }
}
?>
<!-- Sidebar Navigation -->
<aside id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-[#f7f5f0] z-50 transform -translate-x-full transition-transform lg:translate-x-0 overflow-y-auto border-r border-[#e7e3da] shadow-xs">
    <div class="px-6 py-5 border-b border-[#e7e3da] flex items-center justify-between bg-white/60">
        <a href="../../index.php" class="flex items-center gap-2.5">
            <img src="../../assets/img/logo.webp" alt="logo" class="w-8 h-auto">
            <div>
                <span class="font-serif text-lg font-bold tracking-wide text-stone-900 uppercase">অন্ত্যমিল</span>
                <span class="block text-[9px] font-mono text-stone-500 uppercase tracking-widest">Admin Control</span>
            </div>
        </a>
    </div>

    <nav class="px-3 py-5 space-y-1 text-xs font-medium font-anek">
        <!-- 1. Overview -->
        <a href="/admin/overview" id="nav-overview" class="sidebar-link <?php echo $current_page === 'overview' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span>ওভারভিউ</span>
        </a>

        <!-- 2. Orders -->
        <a href="/admin/orders" id="nav-orders" class="sidebar-link <?php echo $current_page === 'orders' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            <span>অর্ডারসমূহ</span>
        </a>

        <!-- 3. Inventory -->
        <a href="/admin/inventory" id="nav-inventory" class="sidebar-link <?php echo $current_page === 'inventory' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            <span>ইনভেন্টরি</span>
        </a>

        <!-- 4. Members -->
        <a href="/admin/members" id="nav-members" class="sidebar-link <?php echo $current_page === 'members' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <span>মেম্বার লিস্ট</span>
            <span class="ml-auto px-2 py-0.5 <?php echo $current_page === 'members' ? 'bg-stone-800 text-stone-200' : 'bg-[#e7e2d6] text-stone-700'; ?> rounded-full text-[10px] font-mono font-medium">
                <?php echo $total_members; ?>
            </span>
        </a>

        <!-- 5. Suggested Books -->
        <a href="/admin/suggested" id="nav-suggested" class="sidebar-link <?php echo $current_page === 'suggested' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
            <span>সাজেস্টেড বই</span>
        </a>

        <!-- 6. Borrows Tracker -->
        <a href="/admin/borrows" id="nav-borrows" class="sidebar-link <?php echo $current_page === 'borrows' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            <span>বরো ট্র্যাকার</span>
        </a>

        <!-- 7. Pre-Orders -->
        <a href="/admin/preorders" id="nav-preorders" class="sidebar-link <?php echo $current_page === 'preorders' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span>প্রি-অর্ডার</span>
        </a>

        <!-- 8. Payments -->
        <a href="/admin/payments" id="nav-payments" class="sidebar-link <?php echo $current_page === 'payments' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
            <span>পেমেন্ট সেটিংস</span>
        </a>

        <!-- 9. Student Memberships -->
        <a href="/admin/student-memberships" id="nav-student-membership" class="sidebar-link <?php echo $current_page === 'student_memberships' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
            <span>স্টুডেন্ট মেম্বারশিপ</span>
            <?php if ($pending_student_requests > 0): ?>
                <span class="ml-auto px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300/80 rounded-full text-[10px] font-mono font-bold">
                    <?php echo $pending_student_requests; ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- 10. Membership Management -->
        <a href="/admin/membership" id="nav-membership" class="sidebar-link <?php echo $current_page === 'membership' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <span>মেম্বারশিপ প্ল্যান</span>
            <?php if ($pending_membership_requests > 0): ?>
                <span class="ml-auto px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300/80 rounded-full text-[10px] font-mono font-bold"><?php echo $pending_membership_requests; ?></span>
            <?php endif; ?>
        </a>

        <!-- 11. Profile -->
        <a href="/admin/profile" id="nav-profile" class="sidebar-link <?php echo $current_page === 'profile' ? 'active text-white font-semibold' : 'text-stone-600 hover:text-stone-900 hover:bg-[#eae5db]'; ?> w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>প্রোফাইল</span>
        </a>

        <!-- 12. Logout -->
        <a href="/admin/logout.php" class="sidebar-link text-stone-600 hover:text-rose-700 hover:bg-rose-50 w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition-all mt-6 pt-4 border-t border-[#e7e3da]">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            <span>লগআউট</span>
        </a>
    </nav>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-stone-900/30 backdrop-blur-xs z-40 lg:hidden hidden"></div>
